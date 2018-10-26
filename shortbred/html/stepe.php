<?php 

require_once "../includes/main.inc.php";
require_once "../libs/job_manager.class.inc.php";
require_once "../libs/quantify.class.inc.php";
require_once "../../libs/table_builder.class.inc.php";


if (!isset($_GET["id"]) || !is_numeric($_GET["id"]) || !isset($_GET["key"])) {
    error404();
} else {
    $job_mgr = new job_manager($db, job_types::Identify);
    if ($job_mgr->get_job_key($_GET["id"]) != $_GET["key"]) {
        error404();
    }
}

$identify_id = $_GET["id"];
$key = $_GET["key"];
$qid = $_GET["quantify-id"];
$id_query_string = "id=$identify_id&key=$key&quantify-id=$qid";
$identify_only_id_query_string = "id=$identify_id&key=$key";


#$q_jobs = job_manager::get_quantify_jobs($db, $identify_id);
$mg_db = new metagenome_db();
$mg_db->load_db();

$ExtraTitle = "Quantify Results for Identify ID $identify_id / Quantify ID $qid";

$job_obj = new quantify($db, $qid);
#$status = $job_obj->get_status();
#
#$is_failed = false;
#$is_running = false;
#$is_finished = false;
#
#if ($status == __FAILED__) {
#    $ExtraTitle = "Computation Failed";
#    $is_failed = true;
#} elseif ($status == __RUNNING__) {
#    $ExtraTitle = "Computation Still Running";
#    $is_running = true;
#} else {
#    $ExtraTitle = "Quantify Computation Results";
#    $is_finished = true;
#}

$table_format = "html";
if (isset($_GET["as-table"])) {
    $table_format = "tab";
}
$table = new table_builder($table_format);

$filename = $job_obj->get_filename();
$id_tbl_val = "<a href=\"stepc.php?$id_query_string\"><u>$identify_id</u></a>/$qid";
$min_seq_len = $job_obj->get_min_seq_len();
$max_seq_len = $job_obj->get_max_seq_len();
$search_type = strtoupper($job_obj->get_search_type());
$ref_db = strtoupper($job_obj->get_ref_db());
$id_search_type = strtoupper($job_obj->get_identify_search_type());
$diamond_sens = $job_obj->get_diamond_sensitivity();
$cdhit_sid = $job_obj->get_identify_cdhit_sid();

$table->add_row("Input filename", $filename);
$table->add_row_with_html("Identify/Quantify ID", $id_tbl_val);
if ($min_seq_len)
    $table->add_row("Minimum sequence length", $min_seq_len);
if ($max_seq_len)
    $table->add_row("Maximum sequence length", $max_seq_len);
if (settings::get_diamond_enabled()) {
    $table->add_row("Identify search type", $id_search_type);
    $table->add_row("Reference database", $ref_db);
    $table->add_row("CD-HIT identity for ShortBRED family definition", $cdhit_sid);
    if ($diamond_sens != "normal") //TODO: fix hardcoded constant
        $table->add_row("DIAMOND sensitivity", $diamond_sens);
    $table->add_row("Quantify search type", $search_type);
}

$metadata = $job_obj->get_metadata();
ksort($metadata, SORT_NUMERIC);
foreach ($metadata as $order => $row) {
    $val = is_numeric($row[1]) ? number_format($row[1]) : $row[1];
    $table->add_row_with_class($row[0], $val, "stats-row");
}


$hm_parm_string = "filename=$filename&search-type=$id_search_type&ref-db=$ref_db&d-sens=$diamond_sens&cdhit-sid=$cdhit_sid";

$use_mean = true;
$mean_cluster_file = $job_obj->get_cluster_file_path($use_mean);
$mean_gn_file = $job_obj->get_genome_normalized_cluster_file_path($use_mean);

$size_data = array(
    "ssn" => $job_obj->get_ssn_file_size(),
    "ssn_zip" => $job_obj->get_ssn_zip_file_size(),
    "protein" => $job_obj->get_protein_file_size(false),
    "cluster" => $job_obj->get_cluster_file_size(false),
    "protein_norm" => $job_obj->get_normalized_protein_file_size(false),
    "cluster_norm" => $job_obj->get_normalized_cluster_file_size(false),
    "cdhit" => $job_obj->get_cdhit_file_size(),
    "markers" => $job_obj->get_marker_file_size(),
);

$gn_file = $job_obj->get_genome_normalized_cluster_file_path();
if (file_exists($gn_file)) {
    $size_data["protein_genome_norm"] = $job_obj->get_genome_normalized_protein_file_size(false);
    $size_data["cluster_genome_norm"] = $job_obj->get_genome_normalized_cluster_file_size(false);
}

if (file_exists($mean_cluster_file)) {
    $size_data["protein_mean"] = $job_obj->get_protein_file_size(true);
    $size_data["cluster_mean"] = $job_obj->get_cluster_file_size(true);
    $size_data["protein_norm_mean"] = $job_obj->get_normalized_protein_file_size(true);
    $size_data["cluster_norm_mean"] = $job_obj->get_normalized_cluster_file_size(true);
    if (file_exists($mean_gn_file)) {
        $size_data["protein_genome_norm_mean"] = $job_obj->get_genome_normalized_protein_file_size(true);
        $size_data["cluster_genome_norm_mean"] = $job_obj->get_genome_normalized_cluster_file_size(true);
    }
}

$clusterSizesFileSize = $job_obj->get_metadata_cluster_sizes_file_size();
$swissProtClustersFileSize = $job_obj->get_metadata_swissprot_clusters_file_size();
$swissProtSinglesFileSize = $job_obj->get_metadata_swissprot_singles_file_size();
if ($clusterSizesFileSize)
    $size_data["meta-cl-size"] = $clusterSizesFileSize;
if ($swissProtClustersFileSize)
    $size_data["meta-sp-cl"] = $swissProtClustersFileSize;
if ($swissProtSinglesFileSize)
    $size_data["meta-sp-si"] = $swissProtSinglesFileSize;
$size_data["mg-info"] = "<1";


$arg_suffix = "";
$run_suffix = "";
$file_types = array(
    "protein" => "q-prot$arg_suffix",
    "cluster" => "q-clust$arg_suffix",
    "protein_norm" => "q-prot$arg_suffix-n",
    "cluster_norm" => "q-clust$arg_suffix-n",
    "protein_genome_norm" => "q-prot$arg_suffix-gn",
    "cluster_genome_norm" => "q-clust$arg_suffix-gn",
    "protein_mean" => "q-prot$arg_suffix-mean",
    "cluster_mean" => "q-clust$arg_suffix-mean",
    "protein_norm_mean" => "q-prot$arg_suffix-n-mean",
    "cluster_norm_mean" => "q-clust$arg_suffix-n-mean",
    "protein_genome_norm_mean" => "q-prot$arg_suffix-gn-mean",
    "cluster_genome_norm_mean" => "q-clust$arg_suffix-gn-mean",
    "ssn" => "ssn-q",
    "ssn_zip" => "ssn-q-zip",
    "cdhit" => "cdhit",
    "markers" => "markers",
    "meta-cl-size" => "meta-cl-size",
    "meta-sp-cl" => "meta-sp-cl",
    "meta-sp-si" => "meta-sp-si",
    "mg-info" => "q-mg-info",
);
$html_labels = array(
    "protein" => "Protein abundance data $run_suffix (median)",
    "cluster" => "Cluster abundance data $run_suffix (median)",
    "protein_norm" => "Normalized protein abundance data $run_suffix (median)",
    "cluster_norm" => "Normalized cluster abundance data $run_suffix (median)",
    "protein_genome_norm" => "Average genome size (AGS) normalized protein abundance data $run_suffix (median)",
    "cluster_genome_norm" => "Average genome size (AGS) normalized cluster abundance data $run_suffix (median)",
    "protein_mean" => "Protein abundance data $run_suffix (mean)",
    "cluster_mean" => "Cluster abundance data $run_suffix (mean)",
    "protein_norm_mean" => "Normalized protein abundance data $run_suffix (mean)",
    "cluster_norm_mean" => "Normalized cluster abundance data $run_suffix (mean)",
    "protein_genome_norm_mean" => "Average genome size (AGS) normalized protein abundance data $run_suffix (mean)",
    "cluster_genome_norm_mean" => "Average genome size (AGS) normalized cluster abundance data $run_suffix (mean)",
    "ssn" => "SSN with quantify results",
    "ssn_zip" => "SSN with quantify results (ZIP)",
    "cdhit" => "CD-HIT ShortBRED families by cluster",
    "markers" => "ShortBRED marker data",
    "meta-cl-size" => "Cluster sizes",
    "meta-sp-cl" => "SwissProt annotations by cluster",
    "meta-sp-si" => "SwissProt annotations by singletons",
    "mg-info" => "Description of selected metagenomes",
);

$dl_ssn_items = array("ssn", "ssn_zip", "mg-info");
$dl_misc_items = array("cdhit", "markers", "meta-cl-size", "meta-sp-cl", "meta-sp-si");
$dl_median_items = array("protein", "cluster", "protein_norm", "cluster_norm", "protein_genome_norm", "cluster_genome_norm");
$dl_mean_items = array("protein_mean", "cluster_mean", "protein_norm_mean", "cluster_norm_mean", "protein_genome_norm_mean", "cluster_genome_norm_mean");


$table_string = $table->as_string();

if (isset($_GET["as-table"])) {
    $table_filename = "${identify_id}_q${qid}_" . global_functions::safe_filename(pathinfo($filename, PATHINFO_FILENAME)) . "_settings.txt";
    functions::send_table($table_filename, $table_string);
    exit(0);
}


require_once "inc/header.inc.php"; 

?>



<h2><?php echo $ExtraTitle; ?></h2>
<p>&nbsp;</p>
<!--
<?php if ($is_failed) { ?>
<p>There was an error generating the marker data.</p>
<?php } elseif ($is_running) { ?>
<p>The computation is still running.</p>
<?php } else { ?>
-->
<!--<p>ShortBRED-Quantify has finished.</p>-->

<!--<p><a href="stepc.php?<?php echo $id_query_string; ?>"><button class="mini" type="button">Return to Identify Results</button></a></p>-->

<h3>Job Information</h3>

<table class="pretty" style="border-top: 1px solid #aaa;">
    <tbody>
        <?php echo $table_string; ?>
    </tbody>
</table>
<div style="float:left"><button type="button" class="mini" id="job-stats-btn">Show Job Statistics</button></div>
<div style="float:right"><a href="stepe.php?<?php echo $id_query_string; ?>&as-table=1"><button type="button" class="mini">Download Info</button></a></div>
<div style="clear:both"></div>

<h3>Downloadable Data</h3>

<div class="tabs" id="download-tabs">
    <ul class="tab-headers">
        <li class="active"><a href="#download-ssn">SSN and CD-HIT Files</a></li>
        <li><a href="#download-median">CGFP Output (using median method)</a></li>
        <li><a href="#download-mean">CGFP Output (using mean method)</a></li>
    </ul>

    <div class="tab-content tab-content-normal">
        <div id="download-ssn" class="tab active">
            <table class="pretty">
                <thead><th></th><th>File</th><th>Size</th></thead>
                <tbody>
<?php outputResultsTable($dl_ssn_items, $id_query_string, $size_data, $file_types, $html_labels); ?>
<?php outputResultsTable($dl_misc_items, $identify_only_id_query_string, $size_data, $file_types, $html_labels); ?>
                </tbody>
            </table>
        </div>
        <div id="download-median" class="tab">
            <table class="pretty">
                <thead><th></th><th>File</th><th>Size</th></thead>
                <tbody>
<?php outputResultsTable($dl_median_items, $id_query_string, $size_data, $file_types, $html_labels); ?>
                </tbody>
            </table>
        </div>
        <div id="download-mean" class="tab">
            <table class="pretty">
                <thead><th></th><th>File</th><th>Size</th></thead>
                <tbody>
<?php outputResultsTable($dl_mean_items, $id_query_string, $size_data, $file_types, $html_labels); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<h3>Heatmaps</h3>

<div class="tabs" id="heatmap-tabs">
    <ul class="tab-headers">
        <li class="active"><a href="#heatmap-clusters">Cluster Heatmap</a></li>
        <li><a href="#heatmap-singletons">Singleton Heatmap</a></li>
        <li><a href="#heatmap-combined">Combined Heatmap</a></li>
    </ul>

    <div class="tab-content">
        <div id="heatmap-clusters" class="tab active">
           <iframe src="heatmap.php?<?php echo "$id_query_string&$hm_parm_string"; ?>&res=c&g=q" width="970" height="840" style="border: none"></iframe>
        </div>

        <div id="heatmap-singletons" class="tab">
            <iframe src="heatmap.php?<?php echo "$id_query_string&$hm_parm_string"; ?>&res=s&g=q" width="970" height="840" style="border: none"></iframe>
        </div>

        <div id="heatmap-combined" class="tab">
            <iframe src="heatmap.php?<?php echo "$id_query_string&$hm_parm_string"; ?>&res=m&g=q" width="970" height="840" style="border: none"></iframe>
        </div>
    </div>
</div>

<br><br>
Metagenomes:
<div style='margin-left: 40px; max-height: 300px; overflow-y: auto'>
<?php
    $mg_data = $job_obj->get_metagenome_data();
    foreach ($mg_data as $row) {
        $mg_id = $row[0];
        $bodysite = $row[1];
        $info = "$mg_id: $bodysite<br>";
//        $mg_info = $mg_db->get_metagenome_data($mg_id);
//        $info = "$mg_id";
//        if ($mg_info["name"]) {
//            $info .= ": " . $mg_info["name"];
//        }
//        if ($mg_info["desc"]) {
//            $info .= " (" . $mg_info["desc"] . ")";
//        }
//        $info .= "<br>";
        echo $info;
    }
?>
</div>

<?php } ?>

<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>

<hr style='margin: 20px 0'>

<script>
$(document).ready(function() {
    var iframes = $('iframe');
    iframes.each(function() {
        var src = $(this).attr('src');
        $(this).data('src', src).attr('src', '');
    });

    var handleTabPress = function(masterId, elemObj) {
        var curAttrValue = elemObj.attr("href");
        var tabPage = $(masterId + " " + curAttrValue);
        tabPage.show().siblings().hide();
        //tabPage.fadeIn(300).show().siblings().hide();
        elemObj.parent("li").addClass("active").siblings().removeClass("active");
        var theFrame = tabPage.children().first();
        if (!theFrame.data("shown")) {
            console.log("Showing again");
            theFrame.attr("src", function() {
                return $(this).data("src");
            });
            theFrame.data("shown", true);
        }
    };

    $("#heatmap-tabs .tab-headers a").on("click", function(e) {
        e.preventDefault();
        handleTabPress("#heatmap-tabs", $(this));
    });
    handleTabPress("#heatmap-tabs", $("#heatmap-tabs .tab-headers .active a").first());
    
    $("#download-tabs .tab-headers a").on("click", function(e) {
        e.preventDefault();
        handleTabPress("#download-tabs", $(this));
    });
    handleTabPress("#download-tabs", $("#download-tabs .tab-headers .active a").first());

    $("#job-stats-btn").click(function() {
        $(".stats-row").toggle();
        if ($(this).text() == "Show Job Statistics")
            $(this).text("Hide Job Statistics");
        else
            $(this).text("Show Job Statistics");
    });
    $(".stats-row").hide();
    
});
</script>

<?php require_once "inc/footer.inc.php"; ?>

<?php

function outputResultsTable($items, $id_query_string, $size_data, $file_types, $html_labels) {

    foreach ($items as $type) {
        $arg = $file_types[$type];
        
        if (!isset($size_data[$type]))
            continue;

        $label = $html_labels[$type];
        echo <<<HTML
        <tr>
            <td style='text-align:center;'>
HTML;

        $size_label = "--";
        $label = $html_labels[$type];
        if (isset($size_data[$type]) && $size_data[$type]) {
            echo "                <a href='download_files.php?type=$arg&$id_query_string'><button class='mini'>Download</button></a>\n";
            $size_label = $size_data[$type] . " MB";
        } else {
            echo "";
        }
    
        echo <<<HTML
            </td>
            <td>$label</td>
            <td style='text-align:center;'>$size_label</td>
        </tr>
HTML;
    }
}



?>

