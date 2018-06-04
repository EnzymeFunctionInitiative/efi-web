<?php 

require_once "../includes/main.inc.php";
require_once "../libs/job_manager.class.inc.php";
require_once "../libs/identify.class.inc.php";
require_once "../../libs/table_builder.class.inc.php";


if (!isset($_GET["id"]) || !is_numeric($_GET["id"]) || !isset($_GET["key"])) {
    error404();
} else {
    $job_mgr = new job_manager($db, job_types::Identify);
    if ($job_mgr->get_job_key($_GET["id"]) != $_GET["key"]) {
        error404();
    }
}

$id = $_GET["id"];
$key = $_GET["key"];
$id_query_string = "id=$id&key=$key";


$job = new identify($db, $id);
$status = $job->get_status();

$is_failed = false;
$is_running = false;
$is_finished = false;

if ($status == __FAILED__) {
    $ExtraTitle = "Computation Failed";
    $is_failed = true;
} elseif ($status == __RUNNING__) {
    $ExtraTitle = "Computation Still Running";
    $is_running = true;
} else {
    $ExtraTitle = "Markers Computation Results for ID $id";
    $is_finished = true;
}


$filename = $job->get_filename();

$hmp_list = array();
if ($is_finished) {
    $hmp_id = new metagenome_db();

    if ($hmp_id->load_db()) {
        $hmp_list = $hmp_id->get_name_list();
    }
}


$q_jobs = job_manager::get_quantify_jobs($db, $id);


$ExtraCssLinks = array("$SiteUrlPrefix/chosen/chosen.min.css");

$zipFileExists = file_exists($job->get_output_ssn_zip_file_path());
$ssnZipFileSize = $zipFileExists ? global_functions::bytes_to_megabytes($job->get_output_ssn_zip_file_size()) : "";
$ssnFileSize = global_functions::bytes_to_megabytes($job->get_output_ssn_file_size());

require_once "inc/header.inc.php"; 

?>



<h2><?php echo $ExtraTitle; ?></h2>
<p>&nbsp;</p>

<?php if ($is_failed) { ?>
<p>There was an error generating the marker data.</p>
<?php } elseif ($is_running) { ?>
<p>The computation is still running.</p>
<?php } else { ?>

<p>Input filename: <?php echo $filename; ?></>

<p>The markers have been computed.</p>

<table width="100%" border="1">
    <thead>
        <th></th>
        <th>File</th>
        <th>Size</th>
    </thead>
    <tbody>
        <tr>
            <td style='text-align:center;'><a href="download_files.php?type=ssn-c&<?php echo $id_query_string; ?>"><button class="mini">Download</button></a></td>
            <td>SSN with marker results</td>
            <td style='text-align:center;'><?php echo $ssnFileSize; ?> MB</td>
        </tr>
<?php if ($zipFileExists) { ?>
        <tr>
            <td style='text-align:center;'><a href="download_files.php?type=ssn-c-zip&<?php echo $id_query_string; ?>"><button class="mini">Download</button></a></td>
            <td>SSN with marker results (ZIP)</td>
            <td style='text-align:center;'><?php echo $ssnZipFileSize; ?> MB</td>
        </tr>
<?php } ?>
        <tr>
            <td style='text-align:center;'><a href="download_files.php?type=markers&<?php echo $id_query_string; ?>"><button class="mini">Download</button></a></td>
            <td>Marker data</td>
            <td style='text-align:center;'>1MB</td>
        </tr>
        <tr>
            <td style='text-align:center;'><a href="download_files.php?type=cdhit&<?php echo $id_query_string; ?>"><button class="mini">Download</button></a></td>
            <td>CD-HIT mapping file (as table)</td>
            <td style='text-align:center;'>1 MB</td>
        </tr>
    </tbody>
</table>

<?php /*
<p><a href="download_files.php?type=ssn-c&<?php echo $id_query_string; ?>">Download SSN with markers identified.</a></p>

<?php if ($zipFileExists) { ?>
<p><a href="download_files.php?type=ssn-c&<?php echo $id_query_string; ?>">Download SSN with markers identified.</a></p>a
<?php } ?>

<p><a href="download_files.php?type=markers&<?php echo $id_query_string; ?>">Download marker file.</a></p>

<p><a href="download_files.php?type=cdhit&<?php echo $id_query_string; ?>">Download CD-HIT mapping file (formatted as a table).</a></p>
 */ ?>

<h3>Select Metagenomes from the Human Microbiome Project</h3>

<p>
The next step in the process is quantifying the occurrence of the markers against a metagenome dataset.
This tool currently provides access to metagenomes from the <a href="https://commonfund.nih.gov/hmp">NIH
Human Microbiome Project</a>.
</p>

<form action="" id="quantify-params">
<input type="hidden" name="id" value="<?php echo $id; ?>">
<input type="hidden" name="key" value="<?php echo $key; ?>">

<div class="row">
    <div class="col-xs-5">
        <select id="search" name="from[]" multiple="multiple" size="8">
<?php

foreach ($hmp_list as $hmp_id => $hmp_name) {
    echo "                <option value=\"$hmp_id\">$hmp_name</option>\n";
}

?>
        </select>
    </div>
    <div class="col-xs-2" style="text-align:center;">
        <button type="button" id="search_rightAll" class="light btn-select"><i class="fas fa-angle-double-right"></i></button>
        <button type="button" id="search_rightSelected" class="light btn-select"><i class="fas fa-angle-right"></i></button>
        <button type="button" id="search_leftSelected" class="light btn-select"><i class="fas fa-angle-left"></i></button>
        <button type="button" id="search_leftAll" class="light btn-select"><i class="fas fa-angle-double-left"></i></button>
    </div>
    
    <div class="col-xs-5">
        <select name="to[]" id="search_to" class="form-control" size="8" multiple="multiple"></select>
    </div>
</div>

<p>
Metagenomes originate from samples collected from 300 healthy adult men and women 
between the ages of 18 and 40, recruited at Baylor College of Medicine in Houston, 
TX and Washington University in St. Louis, MO. More information about the Sample Collection 
&amp; Participant Guidelines, and methods applied can be found at:
<a href="https://www.hmpdacc.org/hmp/micro_analysis/microbiome_analyses.php">https://www.hmpdacc.org/hmp/micro_analysis/microbiome_analyses.php</a>.
</p>

<p>&nbsp;</p>
<div id="error-message" style="color: red"></div>

<center>
<button class="dark" type="button" name="submit" id="quantify-submit"
    onclick="submitQuantify('quantify-params', 'search_to', 'error-message', '<?php echo $id; ?>', '<?php echo $key; ?>')">Quantify Markers</button>
</center>

</form>


<?php
if (count($q_jobs)) {

    echo "<hr>";
    echo "<h2>Existing Quantify Jobs</h2>";

    foreach ($q_jobs as $job) {
        $qid = $job["quantify_id"];
        echo "<p><a href='stepe.php?id=$id&key=$key&quantify-id=$qid'><button type='button' class='mini'>Quantify Job #$qid</button></a></p>";
    }
}
?>


<?php } ?>

<p>&nbsp;</p>
<p>&nbsp;</p>
<p></p>
<p>&nbsp;</p>


<script src="<?php echo $SiteUrlPrefix; ?>/chosen/chosen.jquery.min.js" type="text/javascript"></script>
<script>
$(document).ready(function() {
    $('#search').multiselect({
        search: {
            left: '<input type="text" name="ql" class="form-control" placeholder="Search..." />',
            right: '<input type="text" name="qr" class="form-control" placeholder="Search..." />',
        },
        fireSearch: function(value) {
            return value.length > 3;
        }
    });
//    $("#hmp-id").chosen({width: "35%", placeholder_text_multiple: "Click to Select Metagenomes"});
});
</script>

<?php require_once "inc/footer.inc.php"; ?>


