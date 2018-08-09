<?php 

require_once "../../libs/ui.class.inc.php";
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

<?php if ($parent_id) { ?>
<p>Parent Job is
    <a href="stepc.php?id=<?php echo "$parent_id&key=$parent_key"; ?>"><button type="button" class="mini"># <?php echo $parent_id; ?></button></a></p>
<?php } else { ?>
<p>The markers have been computed.</p>
<?php } ?>

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
            <td style='text-align:center;'>1 MB</td>
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
<?php if ($is_submittable) { ?>
<button class="dark" type="button" name="submit" id="quantify-submit"
    onclick="submitQuantify('quantify-params', 'search_to', 'error-message', '<?php echo $id; ?>', '<?php echo $key; ?>')">Quantify Markers</button>
<?php } else { // is submittable ?>
<button class="dark" type="button" name="submit">Quantify Markers (inactive for training jobs)</button>
<?php } ?>
</center>

</form>


<?php
if (count($q_jobs)) {

    echo "<hr>";
    echo "<h3>Existing Quantify Jobs</h3>";

    foreach ($q_jobs as $job) {
        $qid = $job["quantify_id"];
        $status = $job["is_completed"];
        if ($status) {
            echo "<p><a href='stepe.php?id=$id&key=$key&quantify-id=$qid'><button type='button' class='mini'>Quantify Job #$qid</button></a></p>";
        } else {
            echo "<p>Quantify Job #$qid - not completed</p>";
        }
    }
}


if (count($child_data)) {
}

?>

<?php if ($user_email) { ?>

<hr>
<h3>Upload SSN with Different Alignment Score</h3>

An SSN using the same EST job but with a different alignment score can be uploaded here.  This will eliminate
the need to re-run the Identify and Quantify steps and return a result quicker.  Submitting an SSN will
create a new job in the ShortBRED website which will function independently of the original job but share the
Identify and Quantify data.

<form name="upload_form" id='upload_form' method="post" action="" enctype="multipart/form-data">
<p>
<?php echo ui::make_upload_box("<b>Select a File to Upload:</b><br>", "ssn_file", "progress_bar", "progress_number", "The acceptable format is uncompressed or zipped xgmml.", $SiteUrlPrefix); ?>

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
    <div><progress id='progress_bar' max='100' value='0'></progress></div>
    <div id="progress_number"></div>
</center>
</form>


<?php   } ?>
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
<script src="<?php echo $SiteUrlPrefix; ?>/js/custom-file-input.js" type="text/javascript"></script>

<?php require_once "inc/footer.inc.php"; ?>


