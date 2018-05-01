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


$q_jobs = $job_mgr->get_quantify_jobs($identify_id);
$mg_db = new metagenome_db();
$mg_db->load_db();

$ExtraTitle = "Quantify Results for Identify ID $identify_id / Quantify ID $qid";

$job = new quantify($db, $qid);
#$status = $job->get_status();
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


$filename = $job->get_filename();

$ssnFileSize = global_functions::bytes_to_megabytes($job->get_merged_ssn_file_size());
$protFileSize = $ssnFileSize ? "<1" : 0; // files are small
$clustFileSize = $ssnFileSize ? "<1" : 0; // files are small

$zipFilePath = $job->get_merged_ssn_zip_file_path();
$zipFileExists = file_exists($zipFilePath);
$ssnZipFileSize = $zipFileExists ? global_functions::bytes_to_megabytes($job->get_merged_ssn_zip_file_size()) : "0";

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

<p>Input filename: <?php echo $filename; ?></>

<table width="100%" border="1">
    <thead>
        <th></th>
        <th>File</th>
        <th>Size</th>
    </thead>
    <tbody>
        <tr>
            <td style='text-align:center;'>
                <?php if ($ssnFileSize) { ?>
                <a href="download_files.php?type=ssn-q&<?php echo $id_query_string; ?>"><button class="mini">Download</button></a>
                <?php } ?>
            </td>
            <td>SSN with quantify results</td>
            <td style='text-align:center;'><?php if ($ssnFileSize) echo $ssnFileSize; else echo "--"; ?> MB</td>
        </tr>
<?php if ($zipFileExists) { ?>
        <tr>
            <td style='text-align:center;'><a href="download_files.php?type=ssn-q-zip&<?php echo $id_query_string; ?>"><button class="mini">Download</button></a></td>
            <td>SSN with quantify results (ZIP)</td>
            <td style='text-align:center;'><?php echo $ssnZipFileSize; ?> MB</td>
        </tr>
<?php } ?>
        <tr>
            <td style='text-align:center;'>
                <?php if ($protFileSize) { ?>
                <a href="download_files.php?type=q-prot-m&<?php echo $id_query_string; ?>"><button class="mini">Download</button></a>
                <?php } ?>
            </td>
            <td>Protein abundance data for all runs</td>
            <td style='text-align:center;'><?php if ($protFileSize) echo $protFileSize; else echo "--"; ?> MB</td>
        </tr>
        <tr>
            <td style='text-align:center;'>
                <?php if ($clustFileSize) { ?>
                <a href="download_files.php?type=q-clust-m&<?php echo $id_query_string; ?>"><button class="mini">Download</button></a></td>
                <?php } ?>
            <td>Cluster/protein abundance data for all runs</td>
            <td style='text-align:center;'><?php if ($clustFileSize) echo $clustFileSize; else echo "--"; ?> MB</td>
        </tr>
        <tr>
            <td style='text-align:center;'>
                <?php if ($protFileSize) { ?>
                <a href="download_files.php?type=q-prot-m-n&<?php echo $id_query_string; ?>"><button class="mini">Download</button></a>
                <?php } ?>
            </td>
            <td>Normalized protein abundance data for all runs</td>
            <td style='text-align:center;'><?php if ($protFileSize) echo $protFileSize; else echo "--"; ?> MB</td>
        </tr>
        <tr>
            <td style='text-align:center;'>
                <?php if ($clustFileSize) { ?>
                <a href="download_files.php?type=q-clust-m-n&<?php echo $id_query_string; ?>"><button class="mini">Download</button></a>
                <?php } ?>
            </td>
            <td>Normalized cluster/protein abundance data for all runs</td>
            <td style='text-align:center;'><?php if ($clustFileSize) echo $clustFileSize; else echo "--"; ?> MB</td>
        </tr>
    </tbody>
</table>


<h3>Individual Quantify Run Results</h3>


<?php
foreach ($q_jobs as $job) {
    $mgs = explode(",", $job["job_name"]);
    $is_plural = count($mgs) > 1 ? "s" : "";
    $job_name = implode(", ", $mgs);

    $show_results = false;
    $status = $job["date_completed"];
    $nice_status = "";
    if ($status == "FAILED") {
        $nice_status = "Job failed.";
    } elseif ($status == "PENDING") {
        $nice_status = "Job pending execution.";
    } elseif ($status == "RUNNING") {
        $nice_status = "Job is currently running.";
    } else {
        $nice_status = "Job completed successfully.";
        $show_results = true;
    }

    $inactive_color = "#aaa";
    $hdr_color = $show_results ? "" : "color: $inactive_color";
    $border_color = $show_results ? "black" : $inactive_color;

    echo "<h4 style='border-bottom: 1px solid $hdr_color; $hdr_color'>Metagenome$is_plural: $job_name</h4>";

    if (!$show_results) {
        echo "<div style=\"color: #ddd\">";
    }

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

    echo "<br>Status: <b>$nice_status</b><br>";

    if ($show_results) {
?>
    <ul>
        <li><a href="download_files.php?type=q-prot&<?php echo $id_query_string . "&quantify-id=" . $job["quantify_id"]; ?>">Download abundance data by protein.</a></li>
        <li><a href="download_files.php?type=q-clust&<?php echo $id_query_string . "&quantify-id=" . $job["quantify_id"]; ?>">Download abundance data by cluster.</a></li>
    </ul>
<?php 
    } else {
        echo "</div>";
    }
}
?>

<!--
<p><a href="download_files.php?type=ssn-q&<?php echo $id_query_string; ?>">Download SSN with markers and protein abundance.</a></p>
<p><a href="download_files.php?type=q-prot&<?php echo $id_query_string; ?>">Download abundance data by protein.</a></p>
<p><a href="download_files.php?type=q-clust&<?php echo $id_query_string; ?>">Download abundance data by cluster.</a></p>
-->

<?php } ?>

<p>&nbsp;</p>
<p>&nbsp;</p>
<p></p>
<p>&nbsp;</p>


<?php require_once "inc/footer.inc.php"; ?>


