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


$filename = $job_obj->get_filename();

$ssnFileSize = global_functions::bytes_to_megabytes($job_obj->get_merged_ssn_file_size());
$protFileSize = $ssnFileSize ? "<1" : 0; // files are small
$clustFileSize = $ssnFileSize ? "<1" : 0; // files are small
$normProtFileSize = $ssnFileSize ? "<1" : 0; // files are small
$normClustFileSize = $ssnFileSize ? "<1" : 0; // files are small
$genomeNormProtFileSize = $ssnFileSize ? "<1" : 0; // files are small
$genomeNormClustFileSize = $ssnFileSize ? "<1" : 0; // files are small

$zipFilePath = $job_obj->get_merged_ssn_zip_file_path();
$zipFileExists = file_exists($zipFilePath);
$ssnZipFileSize = $zipFileExists ? global_functions::bytes_to_megabytes($job_obj->get_merged_ssn_zip_file_size()) : "0";

$size_data = array(
    "ssn" => $ssnFileSize,
    "ssn_zip" => $ssnZipFileSize,
    "protein" => $protFileSize,
    "cluster" => $clustFileSize,
    "protein_norm" => $normProtFileSize,
    "cluster_norm" => $normClustFileSize,
);

$gn_file = $job_obj->get_merged_genome_normalized_cluster_file_path();
if (file_exists($gn_file)) {
    $size_data["protein_genome_norm"] = $genomeNormProtFileSize;
    $size_data["cluster_genome_norm"] = $genomeNormClustFileSize;
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

<p><a href="stepc.php?<?php echo $id_query_string; ?>"><button class="mini" type="button">Return to Identify Results</button></a></p>

<p>Input filename: <?php echo $filename; ?></>

<?php $addl_html = <<<HTML
        <tr>
            <td style='text-align:center;'><a href="download_files.php?type=cdhit&id=$identify_id&key=$key"><button class="mini">Download</button></a></td>
            <td>CD-HIT mapping file (as table)</td>
            <td style='text-align:center;'>1 MB</td>
        </tr>
HTML;
?>
<?php outputResultsTable(false, $id_query_string, $size_data, $addl_html); ?>

<br><button class="heatmap-button mini" type="button" style="margin-top: 20px">View Heatmap for Clusters</button>
<div id="heatmap-clusters" style="display: none;">
<iframe src="heatmap.php?<?php echo $id_query_string; ?>&res=c&g=q" width="970" height="780" style="border: none"></iframe>
</div>

<br><button class="heatmap-button mini" type="button" style="margin-top: 20px">View Heatmap for Singletons</button>
<div id="heatmap-singletons" style="display: none;">
<iframe src="heatmap.php?<?php echo $id_query_string; ?>&res=s&g=q" width="970" height="780" style="border: none"></iframe>
</div>

<br><button class="heatmap-button mini" type="button" style="margin-top: 20px">View Heatmap for Clusters and Singltetons</button>
<div id="heatmap-merged" style="display: none;">
<iframe src="heatmap.php?<?php echo $id_query_string; ?>&res=m&g=q" width="970" height="780" style="border: none"></iframe>
</div>

<br><br>
Metagenomes:
<div style='margin-left: 40px; max-height: 300px; overflow-y: auto'>
<?php
    $mgs = $job_obj->get_metagenome_ids();
    foreach ($mgs as $mg_id) {
        $mg_info = $mg_db->get_metagenome_data($mg_id);
        $info = "$mg_id";
        if ($mg_info["name"]) {
            $info .= ": " . $mg_info["name"];
        }
        if ($mg_info["desc"]) {
            $info .= " (" . $mg_info["desc"] . ")";
        }
        $info .= "<br>";
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
    
    $('.heatmap-button').click(function() {
        $header = $(this);
        //getting the next element
        $content = $header.next();
        //open up the content needed - toggle the slide- if visible, slide up, if not slidedown.
        $content.slideToggle(100, function () {
            if ($content.is(":visible")) {
                $header.find("i.fas").addClass("fa-minus-square");
                $header.find("i.fas").removeClass("fa-plus-square");
            } else {
                $header.find("i.fas").removeClass("fa-minus-square");
                $header.find("i.fas").addClass("fa-plus-square");
            }
        });
        $content.children().attr('src', function() {
            return $(this).data('src');
        });
    });
    
    iframes.each(function() {
        var src = $(this).attr('src');
        $(this).data('src', src).attr('src', '');
    });
});
</script>

<?php require_once "inc/footer.inc.php"; ?>

<?php

function outputResultsTable($is_global, $id_query_string, $size_data, $addl_html = "") {

    $arg_suffix = $is_global ? "-m" : "";
    $run_suffix = $is_global ? "for all runs" : "";

    echo <<<HTML
<table width="100%" border="1">
    <thead>
        <th></th>
        <th>File</th>
        <th>Size</th>
    </thead>
    <tbody>
HTML;

#    if ($is_global) {
        $file_type = array(
            "ssn" => "ssn-q",
            "ssn_zip" => "ssn-q-zip",
        );
        $html_label = array(
            "ssn" => "SSN with quantify results",
            "ssn_zip" => "SSN with quantify results (ZIP)",
        );

        foreach ($file_type as $type => $arg) {
            echo <<<HTML
        <tr>
            <td style='text-align:center;'>
HTML;

            $label = $html_label[$type];
            $size_label = "--";
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
#    }


    $file_type = array(
        "protein" => "q-prot$arg_suffix",
        "cluster" => "q-clust$arg_suffix",
        "protein_norm" => "q-prot$arg_suffix-n",
        "cluster_norm" => "q-clust$arg_suffix-n",
        "protein_genome_norm" => "q-prot$arg_suffix-gn",
        "cluster_genome_norm" => "q-clust$arg_suffix-gn",
    );
    $html_label = array(
        "protein" => "Protein abundance data $run_suffix",
        "cluster" => "Cluster abundance data $run_suffix",
        "protein_norm" => "Normalized protein abundance data $run_suffix",
        "cluster_norm" => "Normalized cluster abundance data $run_suffix",
        "protein_genome_norm" => "Average genome size (AGS) normalized protein abundance data $run_suffix",
        "cluster_genome_norm" => "Average genome size (AGS) normalized cluster abundance data $run_suffix",
    );

    foreach ($file_type as $type => $arg) {
        if (!isset($size_data[$type]))
            continue;

        $label = $html_label[$type];
        echo <<<HTML
        <tr>
            <td style='text-align:center;'>
HTML;

        $size_label = "--";
        $label = $html_label[$type];
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

    echo <<<HTML
$addl_html
    </tbody>
</table>
HTML;
}

?>

