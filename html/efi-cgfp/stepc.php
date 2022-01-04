<?php 
require_once(__DIR__."/../../init.php");

use \efi\global_functions;
use \efi\table_builder;
use \efi\user_auth;
use \efi\cgfp\settings;
use \efi\cgfp\functions;
use \efi\cgfp\job_manager;
use \efi\cgfp\identify;
use \efi\cgfp\metagenome_db_manager;
use \efi\ui;


// There are two types of examples: dynamic and static.  The static example is a curated
// example pulled into the entry screen.  The dynamic examples are the same as other
// jobs, except they are stored in separate directories/tables.
$is_example = isset($_GET["x"]) ? true : false;

if (!isset($_GET["id"]) || !is_numeric($_GET["id"]) || !isset($_GET["key"])) {
    error404();
//} else {
//    $job_mgr = new job_manager($db, job_types::Identify);
//    if ($job_mgr->get_job_key($_GET["id"]) != $_GET["key"]) {
//        error404();
//    }
}

$user_token = user_auth::get_user_token();
$user_email = "";
if ($user_token) {
    $user_email = user_auth::get_email_from_token($db, $user_token);
}


$id = $_GET["id"];
$key = $_GET["key"];

$ex_param = $is_example ? "&x=1" : "";

$id_query_string = "id=$id&key=$key$ex_param";

$job = new identify($db, $id, $is_example);
if ($job->get_key() != $key) {
    error_404();
}

$status = $job->get_status();

$parent_id = $job->get_parent_id();
$parent_key = "";
if ($parent_id) {
    $job_mgr = new job_manager($db, job_types::Identify);
    $parent_key = $job_mgr->get_job_key($parent_id);
}
$child_data = $job->get_child_jobs();

$is_submittable = ! functions::is_job_sticky($db, $id, $user_email);


$is_failed = false;
$is_running = false;
$is_finished = false;

if ($status == __FAILED__) {
    $ExtraTitle = "Computation Failed";
    $is_failed = true;
} elseif ($status == __RUNNING__) {
    $ExtraTitle = "Computation Still Running";
    $is_running = true;
} elseif ($status == __CANCELLED__) {
    $ExtraTitle = "Computation Cancelled";
    $is_failed = true;
} else {
    $ExtraTitle = "Markers Computation Results";
    $is_finished = true;
}

$filename = $job->get_filename();
$min_seq_len = $job->get_min_seq_len();
$max_seq_len = $job->get_max_seq_len();
$search_type = strtoupper($job->get_search_type());
$ref_db = strtoupper($job->get_ref_db());
$cdhit_sid = $job->get_cdhit_sid();
$cons_thresh = $job->get_consensus_threshold();
$diamond_sens = $job->get_diamond_sensitivity();

$job_name = pathinfo($filename, PATHINFO_FILENAME);

$table_format = "html";
if (isset($_GET["as-table"])) {
    $table_format = "tab";
}
$table = new table_builder($table_format);

$table->add_row("Input filename", $filename);
if ($parent_id)
    $table->add_row_with_html("Identify ID", $id . " (child of <a href=\"stepc.php?id=$parent_id&key=$parent_key\"><u>$parent_id</u></a>)");
else
    $table->add_row("Identify ID", $id);

if ($min_seq_len)
    $table->add_row("Minimum sequence length", $min_seq_len);
if ($max_seq_len)
    $table->add_row("Maximum sequence length", $max_seq_len);
if ($search_type && settings::get_diamond_enabled())
    $table->add_row("Identify search type", $search_type);
if ($ref_db)
    $table->add_row("Reference database", $ref_db);
if ($cdhit_sid)
    $table->add_row("CD-HIT identity for ShortBRED family definition", $cdhit_sid);
if ($cons_thresh)
    $table->add_row("Consensus threshold", $cons_thresh);
if ($diamond_sens && $diamond_sens != "normal") //TODO: fix hardcoded constant
    $table->add_row("DIAMOND sensitivity", $diamond_sens);

$metadata = $job->get_metadata();
ksort($metadata, SORT_NUMERIC);
foreach ($metadata as $order => $row) {
    if ($row[0] == "Number of consensus sequences with hits") //TODO: remove this hack
        continue;
    $val = is_numeric($row[1]) ? number_format($row[1]) : $row[1];
    $table->add_row_with_class($row[0], $val, "stats-row");
}


$mg_db = new metagenome_db_manager();
$mg_db_list = $mg_db->get_metagenome_db_ids();


if ($is_example)
    $q_jobs = array();
else
    $q_jobs = job_manager::get_quantify_jobs($db, $id);


$ExtraCssLinks = array("$SiteUrlPrefix/chosen/chosen.min.css");

$ssnZipFileSize = $job->get_output_ssn_zip_file_size();
$clusterSizesFileSize = $job->get_metadata_cluster_sizes_file_size();
$swissProtClustersFileSize = $job->get_metadata_swissprot_clusters_file_size();
$swissProtSinglesFileSize = $job->get_metadata_swissprot_singles_file_size();

$table_string = $table->as_string();

if (isset($_GET["as-table"])) {
    $table_filename = "${identify_id}_" . global_functions::safe_filename(pathinfo($filename, PATHINFO_FILENAME)) . "_identify_summary.txt";
    functions::send_table($table_filename, $table_string);
    exit(0);
}


$is_enabled = true;
if ($user_token) {
    $IsAdminUser = user_auth::get_user_admin($db, $user_email);
    #$is_sb_enabled = global_settings::get_shortbred_enabled();
    #if ($is_sb_enabled)
    #    $is_enabled = $IsAdminUser || functions::is_shortbred_authorized($db, $user_token);
}
$is_enabled = $is_enabled && !$is_example;


require_once(__DIR__."/inc/header.inc.php");

?>



<h2><?php echo $ExtraTitle; ?></h2>

<?php if ($is_failed) { ?>
<p>There was an error generating the marker data.</p>
<?php } elseif ($is_running) { ?>
<p>The computation is still running.</p>
<?php } else { ?>

<h4 class="job-display">Submitted SSN: <b><?php echo $job_name; ?></b></h4>

<div class="tabs-efihdr tabs">
    <ul>
        <li class="ui-tabs-active"><a href="#info">Submission Summary</a></li>
        <li><a href="#data">Identified Markers</a></li>
        <?php if ($is_enabled) { ?>
        <li><a href="#mg">Select Metagenomes for Marker Quantification</a></li>
        <li><a href="#filter">Resubmit SSN</a></li>
        <?php } ?>
    </ul>

    <div>
        <div id="info">
            <h4>Submission Summary Table</h4>
            <table class="pretty no-stretch" style="border-top: 1px solid #aaa">
                <tbody>
            <?php echo $table_string; ?>
                </tbody>
            </table>
            <div style="float:right"><a href="stepc.php?<?php echo $id_query_string; ?>&as-table=1"><button type="button" class="normal">Download Information</button></a></div>
            <div style="clear:both"></div>

            <?php
            if (count($q_jobs)) {
                echo <<<HTML
                <h4>Existing Quantify Jobs</h4>
                <ul>
HTML;
                foreach ($q_jobs as $q_job) {
                    $qid = $q_job["quantify_id"];
                    $status = $q_job["is_completed"];
                    if ($status) {
                        echo "<li><a href='stepe.php?id=$id&key=$key&quantify-id=$qid'>Quantify Job #$qid</a></li>";
                    } else {
                        echo "<p>Quantify Job #$qid - not completed</p>";
                    }
                }
                echo <<<HTML
</ul>
HTML;
            }
            ?>
        </div>

        
        <div id="data">
            <p>
            Markers that uniquely define clusters in the submitted SSN have been identified.
            </p>
            
            <p>
            Files detailing the identities of the markers and which sequences they represent are available for download.
            </p>

            <h4>SSN With Marker Identification Results</h4>
            <p>
            The SSN submitted has been edited to include the marker ID and type
            and the number of markers that were identified.
            </p>

            <table class="pretty">
                <thead><th></th><th>File</th><th>Size</th></thead>
                <tbody>
                    <?php
                        $types = array();
                        $text = array();
                        $sizes = array();
                        if (!$is_example) {
                            $types = array("ssn-c");
                            $text = array("Download");
                            $sizes = array($job->get_output_ssn_file_size());
                        }
                        array_push($types, "ssn-c-zip");
                        array_push($text, "Download (ZIP)");
                        array_push($sizes, $ssnZipFileSize);
                        make_output_row($id_query_string, $types, $text, "SSN with marker results", $sizes);
                    ?>
                </tbody>
            </table>

            <h4>CGFP Family and Marker Data</h4>
            <p>
            The <b>CD-HIT ShortBRED families by cluster</b> file contains mappings of ShortBRED
            families to SSN cluster number as well as a color that is assigned
            to each unique ShortBRED family. The <b>ShortBRED marker data</b> file
            lists the markers that were identified.
            </p>

            <table class="pretty">
                <thead><th></th><th>File</th><th>Size</th></thead>
                <tbody>
                    <?php
                        make_output_row($id_query_string, "cdhit", "Download", "CD-HIT ShortBRED families by cluster", $job->get_cdhit_file_size());
                        make_output_row($id_query_string, "markers", "Download", "ShortBRED marker data", $job->get_marker_file_size());
                    ?>
                </tbody>
            </table>
        </div>


    <?php if ($is_enabled) { ?>
        <div id="mg">
            <h4>Select Metagenomes from the Human Microbiome Project</h4>
            
            <p>
            The next step in the process is to quantify the occurrences of the previously-identified
            markers against metagenome datasets.
            This tool currently provides access to metagenomes from the <a href="https://commonfund.nih.gov/hmp">NIH
            Human Microbiome Project (HMP)</a>.
            </p>
            
            <form action="" id="quantify-params">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <input type="hidden" name="key" value="<?php echo $key; ?>">
            
            <div class="tabs-efihdr tabs" id="download-tabs">
                <ul class="tab-headers">
            <?php
                $c = 0;
                foreach ($mg_db_list as $mg_db_id) {
                    $ac_cls = $c++ ? "" : "active";
                    $mg_db_name = $mg_db->get_metagenome_db_name($mg_db_id);
                    echo <<<HTML
                    <li class="$ac_cls"><a href="#ds-mgds-$mg_db_id">$mg_db_name</a></li>
HTML;
                }
            ?>
                </ul>
            
                <div>
            <?php
                $c = 0;
                foreach ($mg_db_list as $mg_db_id) {
                    $ac_cls = $c++ ? "" : "active";
                    $mg_list = $mg_db->get_metagenome_list_for_db($mg_db_id);
                    $mg_desc = $mg_db->get_metagenome_db_description($mg_db_id);
                    echo <<<HTML
                    <div id="ds-mgds-$mg_db_id" class="tab $ac_cls">
HTML;
                    output_mg_sel("mgds-$mg_db_id", $mg_list);
                    echo <<<HTML
                        <p>
                        $mg_desc
                        </p>
                        <input type="hidden" name="mgds-$mg_db_id-dt" id="mgds-$mg_db_id-dt" value="$mg_db_id" />
                    </div>
HTML;
                }
            ?>
                </div>
            </div>
            
            <?php if (settings::get_diamond_enabled()) { ?>
                <div class="option-panels" style="margin-top: 20px">
                    <div>
                        <h3>Marker Quantification Option</h3>
                        <div>
                            <span class="input-name">
                                Search type:
                            </span>
                            <span class="input-field">
                            <?php
                                $default_search_type = settings::get_default_quantify_search();
                                $search_usearch = $default_search_type == "USEARCH" ? "selected" : "";
                                $search_diamond = $default_search_type == "DIAMOND" ? "selected" : "";
                            ?>
                                <select name="search-type" id="search-type">
                                    <option <?php echo $search_usearch; ?>>USEARCH</option>
                                    <option <?php echo $search_diamond; ?>>DIAMOND</option>
                                </select>
                                (default: <?php echo $default_search_type; ?>)
                            </span>
                            <div class="input-desc">
                                Select the algorithm for determining marker abundances in the selected metagenomes.
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <p>
            <span class="input-name">Job name:</span>
            <span class="input-field"><input type="text" class="email" name="job-name" id="job-name" value=""></span> (Optional)
            </p>
            
            <p>You will receive an e-mail when your network has been processed.</p>
            
            <div id="error-message" style="color: red"></div>

            <center>
            <?php if ($is_submittable) { ?>
            <button class="dark" type="button" name="submit" id="quantify-submit"
                onclick="submitQuantify('quantify-params', '', 'search-type', 'job-name', 'error-message', '<?php echo $id; ?>', '<?php echo $key; ?>')">Quantify Markers</button>
            <?php } else { // is submittable ?>
            <button class="dark" type="button" name="submit">Quantify Markers (inactive for training jobs)</button>
            <?php } ?>
            </center>
            
            </form>
            
            
        </div>
    <?php } ?>


    <?php if ($is_enabled) { ?>
    <?php     if ($user_email) { ?>
        <div id="filter">
            <p class="p-heading">
            Map previously-computed protein/cluster abundances per metagenome to a different Colored SSN.
            </p>

            <p>
            The new SSN must have been generated using the same EFI-EST job as the original
            SSN uploaded to EFI-CGFP, and segregated with a different alignment score and/or
            is a subset of the initial clusters.
            </p>

            <p>
            This eliminates the need to re-run the identify and quantify steps so the results
            will be obtained in a much shorter period of time.
            </p>
            
            <form name="upload_form" id='upload_form' method="post" action="" enctype="multipart/form-data">
            <div class="primary-input">
                <?php echo ui::make_upload_box("SSN File:", "ssn_file", "progress_bar", "progress_number", "", $SiteUrlPrefix); ?>
                <div>
                    The accepted format is XGMML (or compressed XGMML as zip).
                </div>
            </div>
            
            <p>You will receive an e-mail when your network has been processed.</p>
            
            <div id='ssn_message' style="color: red">
                <?php if (isset($message)) { echo "<h4 class='center'>" . $message . "</h4>"; } ?>
            </div>
            <center>
                <div>
            <?php if ($is_submittable) { ?>
                    <button type="button" id='ssn_submit' name="ssn_submit" class="dark"
                        onclick="uploadAlignmentScoreUpdateSSN('ssn_file','upload_form','progress_number','progress_bar','ssn_message','<?php echo $user_email; ?>',<?php echo $id; ?>,'<?php echo $key; ?>')"
                        >
                            Upload SSN
                    </button>
            <?php } else { // is submittable ?>
                    <button type="button" id='ssn_submit' name="ssn_submit" class="dark">
                        Upload SSN (inactive for training jobs)
                    </button>
            <?php } ?>
                </div>
            </center>
            </form>
        </div>            
    <?php     } ?>
    <?php } ?>
    </div>
</div>
<?php } ?>

<div style="margin-top: 50px"></div>

<script src="<?php echo $SiteUrlPrefix; ?>/vendor/harvesthq/chosen/chosen.jquery.min.js" type="text/javascript"></script>
<script>
$(document).ready(function() {
<?php
    foreach ($mg_db_list as $mg_db_id) {
        echo <<<HTML
    $('#mgds-${mg_db_id}_search').multiselect({
        search: {
            left: '<input type="text" name="ql" class="form-control" placeholder="Search..." />',
            right: '<input type="text" name="qr" class="form-control" placeholder="Search..." />',
        },
        fireSearch: function(value) {
            return value.length > 3;
        },
        keepRenderingSort: true,
    });

HTML;
    }
?>

    $("#job-stats-btn").click(function() {
        $(".stats-row").toggle();
        if ($(this).text() == "Show Job Statistics")
            $(this).text("Hide Job Statistics");
        else
            $(this).text("Show Job Statistics");
    });
    //$(".stats-row").hide();
    $(".tabs").tabs();
    $(".option-panels > div").accordion({
        heightStyle: "content",
        collapsible: true,
        active: false,
    });
});
</script>
<script src="<?php echo $SiteUrlPrefix; ?>/js/custom-file-input.js" type="text/javascript"></script>

<?php require_once(__DIR__."/inc/footer.inc.php"); ?>

<?php

function output_mg_sel($prefix, $mg_list) {
    echo <<<HTML
<div class="row">
    <div class="col-xs-5">
        <b>Available metagenomes</b>:
        <select id="${prefix}_search" name="from[]" multiple="multiple" size="8">
HTML;

    foreach ($mg_list as $mg_id => $mg_name) {
        echo "                <option value=\"$mg_id\">$mg_name - $mg_id</option>\n";
    }

    echo <<<HTML
        </select>
    </div>
    <div class="col-xs-2" style="text-align:center;">
        <div style="margin-top:45px"></div>
        <button type="button" id="${prefix}_search_rightAll" class="light btn-select"><i class="fas fa-angle-double-right"></i></button>
        <button type="button" id="${prefix}_search_rightSelected" class="light btn-select"><i class="fas fa-angle-right"></i></button>
        <button type="button" id="${prefix}_search_leftSelected" class="light btn-select"><i class="fas fa-angle-left"></i></button>
        <button type="button" id="${prefix}_search_leftAll" class="light btn-select"><i class="fas fa-angle-double-left"></i></button>
    </div>
    
    <div class="col-xs-5">
        <b>Metagenomes used for quantification</b>:
        <select name="to[]" id="${prefix}_search_to" class="form-control" size="8" multiple="multiple"></select>
    </div>
</div>

<div>
<center>Use the arrow buttons to select metagenomes for quantification.</center>
</div>
HTML;
}

function make_output_row($id_query_string, $type, $btn_text, $desc, $size) {
    $link_fn = function($arg, $btn_text) use($id_query_string) {
        return "<a href='download_files.php?type=$arg&$id_query_string'><button class='mini'>$btn_text</button></a>\n";
    };

    $link_text = "";
    $size_text = "";
    if (is_array($type)) {
        for ($i = 0; $i < count($type); $i++) {
            $link_text .= $link_fn($type[$i], $btn_text[$i]);
        }
        if (isset($size))
            $size_text = implode(" / ", array_map(function($size){return ($size ? "$size MB" : "--");}, $size));
    } else {
        $link_text = $link_fn($type, $btn_text);
        $size_text = ($size ? "$size MB" : "--");
    }

    echo <<<HTML
                    <tr>
                        <td style='text-align:center;'>$link_text</td>
                        <td>$desc</td>
                        <td style='text-align:center;'>$size_text</td>
                    </tr>
HTML;
}


