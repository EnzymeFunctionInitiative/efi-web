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
    $ExtraTitle = "Markers Computation Results";
    $is_finished = true;
}


$hmp_list = array();
if ($is_finished) {
    $hmp_id = new metagenome_db();

    if ($hmp_id->load_db()) {
        $hmp_list = $hmp_id->get_name_list();
    }
}



$ExtraCssLinks = array("$SiteUrlPrefix/chosen/chosen.min.css");

require_once "inc/header.inc.php"; 

?>



<h2><?php echo $ExtraTitle; ?></h2>
<p>&nbsp;</p>

<?php if ($is_failed) { ?>
<p>There was an error generating the marker data.</p>
<?php } elseif ($is_running) { ?>
<p>The computation is still running.</p>
<?php } else { ?>

<p>The markers have been computed.</p>

<!--<p><a href="download.php?type=ssn-c&<?php echo $id_query_string; ?>">Download SSN with markers identified.</a></p>-->

<p><a href="download_files.php?type=markers&<?php echo $id_query_string; ?>">Download marker file.</a></p>


<h3>Select Metagenomes from the Human Metagenome Project</h3>

<p>
The next step in the process is quantifying the occurrence of the markers against a metagenome dataset.
This tool currently provides access to metagenomes from the <a href="https://commonfund.nih.gov/hmp">NIH
Human Microbiome Project</a>.
</p>

<form action="" id="quantify-params">
<input type="hidden" name="id" value="<?php echo $id; ?>">
<input type="hidden" name="key" value="<?php echo $key; ?>">

<select id="hmp-id" name="hmp-id" multiple size="5">
<?php

foreach ($hmp_list as $hmp_id => $hmp_name) {
    echo "<option value=\"$hmp_id\">$hmp_name</option>\n";
}

?>
</select>

<p>&nbsp;</p>
<div id="error-message" style="color: red"></div>

<center>
<button class="dark" type="button" name="submit" id="quantify-submit"
    onclick="submitQuantify('quantify-params', 'hmp-id', 'error-message', '<?php echo $id; ?>', '<?php echo $key; ?>')">Quantify Markers</button>
</center>

</form>

<?php } ?>

<p>&nbsp;</p>
<p>&nbsp;</p>
<p></p>
<p>&nbsp;</p>


<script src="<?php echo $SiteUrlPrefix; ?>/chosen/chosen.jquery.min.js" type="text/javascript"></script>
<script>
$(document).ready(function() {
    $("#hmp-id").chosen({width: "35%", placeholder_text_multiple: "Click to Select Metagenomes"});
});
</script>

<?php require_once "inc/footer.inc.php"; ?>


