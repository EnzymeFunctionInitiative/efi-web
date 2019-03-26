<?php 

require_once("../includes/main.inc.php");
require_once("../libs/job_manager.class.inc.php");
require_once("../libs/identify.class.inc.php");
require_once(__BASE_DIR__ . "/libs/ui.class.inc.php");
require_once(__BASE_DIR__ . "/libs/table_builder.class.inc.php");


if (!isset($_GET["id"]) || !is_numeric($_GET["id"]) || !isset($_GET["key"])) {
    error404();
} else {
    $job_mgr = new job_manager($db, job_types::Identify);
    if ($job_mgr->get_job_key($_GET["id"]) != $_GET["key"]) {
        error404();
    }
}

$user_token = user_auth::get_user_token();
$user_email = "";
if ($user_token) {
    $user_email = user_auth::get_email_from_token($db, $user_token);
}


$id = $_GET["id"];
$key = $_GET["key"];
$id_query_string = "id=$id&key=$key";


$job = new identify($db, $id);
$status = $job->get_status();

$parent_id = $job->get_parent_id();
$parent_key = "";
if ($parent_id) {
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
    $ExtraTitle = "Markers Computation Results for ID $id";
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


$q_jobs = job_manager::get_quantify_jobs($db, $id);


$ExtraCssLinks = array("$SiteUrlPrefix/chosen/chosen.min.css");

$ssnZipFileSize = $job->get_output_ssn_zip_file_size();
$clusterSizesFileSize = $job->get_metadata_cluster_sizes_file_size();
$swissProtClustersFileSize = $job->get_metadata_swissprot_clusters_file_size();
$swissProtSinglesFileSize = $job->get_metadata_swissprot_singles_file_size();

$dl_data = array();
array_push($dl_data, array('ssn-c', "SSN with marker results", $job->get_output_ssn_file_size()));
if ($ssnZipFileSize)
    array_push($dl_data, array('ssn-c-zip', "SSN with marker results (ZIP)", $ssnZipFileSize));
array_push($dl_data, array('markers', "ShortBRED marker data", $job->get_marker_file_size()));
array_push($dl_data, array('cdhit', "CD-HIT ShortBRED families by cluster", $job->get_cdhit_file_size()));
if ($clusterSizesFileSize)
    array_push($dl_data, array('meta-cl-size', "Cluster sizes", $clusterSizesFileSize));
if ($swissProtClustersFileSize)
    array_push($dl_data, array('meta-sp-cl', "SwissProt annotations by cluster", $swissProtClustersFileSize));
if ($swissProtSinglesFileSize)
    array_push($dl_data, array('meta-sp-si', "SwissProt annotations by singletons", $swissProtSinglesFileSize));

$table_string = $table->as_string();

if (isset($_GET["as-table"])) {
    $table_filename = "${identify_id}_" . global_functions::safe_filename(pathinfo($filename, PATHINFO_FILENAME)) . "_identify_settings.txt";
    functions::send_table($table_filename, $table_string);
    exit(0);
}


$is_enabled = false;
if ($user_token) {
    $IsAdminUser = user_auth::get_user_admin($db, $user_email);
    $is_sb_enabled = global_settings::get_shortbred_enabled();
    if ($is_sb_enabled)
        $is_enabled = $IsAdminUser || functions::is_shortbred_authorized($db, $user_token);
}


require_once "inc/header.inc.php"; 

?>



<h2><?php echo $ExtraTitle; ?></h2>

<?php if ($is_failed) { ?>
<p>There was an error generating the marker data.</p>
<?php } elseif ($is_running) { ?>
<p>The computation is still running.</p>
<?php } else { ?>

<h4>Job Name: <b><?php echo $job_name; ?></b></h4>

<div class="tabs-efihdr tabs">
    <ul>
        <li><a href="#info">Job Information</a></li>
        <li><a href="#data">Marker Data</a></li>
        <li class="ui-tabs-active"><a href="#mg">Metagenome Selection</a></li>
        <li><a href="#filter">Resubmit SSN</a></li>
    </ul>

    <div>
        <div id="info">
            <table class="pretty" style="border-top: 1px solid #aaa">
                <tbody>
            <?php echo $table_string; ?>
                </tbody>
            </table>
            <!--<div style="display: flex; justify-content: flex-end"><a href="stepc.php?<?php echo $id_query_string; ?>&as-table=1"><button type="button" class="mini">Download Info</button></a></div>-->
            <!--<div style="float:left"><button type="button" class="mini" id="job-stats-btn">Show Job Statistics</button></div>-->
            <div style="float:right"><a href="stepc.php?<?php echo $id_query_string; ?>&as-table=1"><button type="button" class="mini">Download Information</button></a></div>
            <div style="clear:both"></div>

            <?php
            if (count($q_jobs)) {
                echo <<<HTML
                <hr>
                <h3>Existing Quantify Jobs</h3>
                <ul>
HTML;
                foreach ($q_jobs as $job) {
                    $qid = $job["quantify_id"];
                    $status = $job["is_completed"];
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
            <table class="pretty">
                <thead>
                    <th></th>
                    <th>File</th>
                    <th>Size</th>
                </thead>
                <tbody>
            <?php
            foreach ($dl_data as $dl_row) {
                $type = $dl_row[0];
                $desc = $dl_row[1];
                $size = $dl_row[2] ? $dl_row[2] . " MB" : "--";
                echo <<<HTML
                    <tr>
                        <td style='text-align:center;'><a href="download_files.php?type=$type&$id_query_string"><button class="mini">Download</button></a></td>
                        <td>$desc</td>
                        <td style='text-align:center;'>$size</td>
                    </tr>
HTML;
            }
            ?>
                </tbody>
            </table>
        </div>


        <div id="mg">
        <?php
        if ($is_enabled) {
        ?>
            <h4>Select Metagenomes from the Human Microbiome Project</h4>
            
            <p>
            The next step in the process is quantifying the occurrence of the markers against a metagenome dataset.
            This tool currently provides access to metagenomes from the <a href="https://commonfund.nih.gov/hmp">NIH
            Human Microbiome Project (HMP)</a> and HMP2 datasets.
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
            
                <div class="tab-content tab-content-normal">
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
                        <h3>Database Search Options</h3>
                        <div>
                            Sequence search type:
                            <select name="search_type" id="search_type"><option>USEARCH</option><option>DIAMOND</option></select>
                        </div>
                    </div>
                </div>
            <?php } ?>
            
            <p>&nbsp;</p>
            <div id="error-message" style="color: red"></div>
            
            <center>
            <?php if ($is_submittable) { ?>
            <button class="dark" type="button" name="submit" id="quantify-submit"
                onclick="submitQuantify('quantify-params', '', 'search_type', 'error-message', '<?php echo $id; ?>', '<?php echo $key; ?>')">Quantify Markers</button>
            <?php } else { // is submittable ?>
            <button class="dark" type="button" name="submit">Quantify Markers (inactive for training jobs)</button>
            <?php } ?>
            </center>
            
            </form>
            
            
        <?php } ?>
        </div>


    <?php if ($user_email) { ?>
        <div id="filter">
            <p>
            An SSN using the same EST job but with a different alignment score can be uploaded here.
            </p>
            
            <form name="upload_form" id='upload_form' method="post" action="" enctype="multipart/form-data">
            <div class="primary-input">
                <?php echo ui::make_upload_box("Select a File to Upload:", "ssn_file", "progress_bar", "progress_number", "The acceptable format is uncompressed or zipped xgmml.", $SiteUrlPrefix); ?>
                <div>
                    This will eliminate
                    the need to re-run the Identify and Quantify steps and return a result quicker.  Submitting an SSN will
                    create a new job in the ShortBRED website which will function independently of the original job but share the
                    Identify and Quantify data.
                </div>
            </div>
            
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
    <?php   } ?>
<?php } ?>

<div style="margin-top: 50px"></div>

<script src="<?php echo $SiteUrlPrefix; ?>/chosen/chosen.jquery.min.js" type="text/javascript"></script>
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

/*
    $('#hmp2mg_search').multiselect({
        search: {
            left: '<input type="text" name="ql" class="form-control" placeholder="Search..." />',
            right: '<input type="text" name="qr" class="form-control" placeholder="Search..." />',
        },
        fireSearch: function(value) {
            return value.length > 3;
        }
    });

    $('#hmp2mt_search').multiselect({
        search: {
            left: '<input type="text" name="ql" class="form-control" placeholder="Search..." />',
            right: '<input type="text" name="qr" class="form-control" placeholder="Search..." />',
        },
        fireSearch: function(value) {
            return value.length > 3;
        }
    });
*/

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

<?php require_once "inc/footer.inc.php"; ?>

<?php

function output_mg_sel($prefix, $mg_list) {
    echo <<<HTML
<div class="row">
    <div class="col-xs-5">
        <select id="${prefix}_search" name="from[]" multiple="multiple" size="8">
HTML;

    foreach ($mg_list as $mg_id => $mg_name) {
        echo "                <option value=\"$mg_id\">$mg_name - $mg_id</option>\n";
    }

    echo <<<HTML
        </select>
    </div>
    <div class="col-xs-2" style="text-align:center;">
        <button type="button" id="${prefix}_search_rightAll" class="light btn-select"><i class="fas fa-angle-double-right"></i></button>
        <button type="button" id="${prefix}_search_rightSelected" class="light btn-select"><i class="fas fa-angle-right"></i></button>
        <button type="button" id="${prefix}_search_leftSelected" class="light btn-select"><i class="fas fa-angle-left"></i></button>
        <button type="button" id="${prefix}_search_leftAll" class="light btn-select"><i class="fas fa-angle-double-left"></i></button>
    </div>
    
    <div class="col-xs-5">
        <select name="to[]" id="${prefix}_search_to" class="form-control" size="8" multiple="multiple"></select>
    </div>
</div>
HTML;
}

?>

