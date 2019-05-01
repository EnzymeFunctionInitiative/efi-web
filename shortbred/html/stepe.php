<?php 

// In order to share as much code between the actual results page and the example results page
// and make the example page as full-featured as possible, most of the contents of this file
// are located in parts in the inc/ dir.

require_once("../includes/main.inc.php");
require_once("../libs/job_manager.class.inc.php");


if (!isset($_GET["id"]) || !is_numeric($_GET["id"]) || !isset($_GET["key"])) {
    error500("Unable to find the requested job.");
} else {
    $job_mgr = new job_manager($db, job_types::Identify);
    if ($job_mgr->get_job_key($_GET["id"]) != $_GET["key"]) {
        error500("Unable to find the requested job.");
    }
}

$key = $_GET["key"];
$qid = $_GET["quantify-id"];
$identify_id = $_GET["id"];

// Vars needed by step_vars.inc.php
$table_format = "html";
$id_query_string = "id=$identify_id&key=$key&quantify-id=$qid";
$identify_only_id_query_string = "id=$identify_id&key=$key";
$id_tbl_val = "<a href=\"stepc.php?$id_query_string\"><u>$identify_id</u></a>/$qid";

if (isset($_GET["as-table"])) {
    $table_format = "tab";
}


$ExtraTitle = "Quantify Results";
$job_obj = new quantify($db, $qid);


require_once("inc/stepe_vars.inc.php");

// Set in stepe_vars.inc.php:
//     $table_string
//     $filename

if (isset($_GET["as-table"])) {
    $table_filename = "${identify_id}_q${qid}_" . global_functions::safe_filename(pathinfo($filename, PATHINFO_FILENAME)) . "_summary.txt";
    functions::send_table($table_filename, $table_string);
    exit(0);
}

$JobName = pathinfo($filename, PATHINFO_FILENAME);
$quantify_job_name = $job_obj->get_job_name();

$HeatmapWidth = 900;

include("inc/header.inc.php");

?>

<h2><?php echo $ExtraTitle; ?></h2>


<h4 class="job-display">Submitted SSN: <b><?php echo $JobName; ?></b></h4>
<?php if ($quantify_job_name) { ?><h4 class="job-display">Job Name: <b><?php echo $quantify_job_name; ?></b></h4><?php } ?>

<?php include("inc/stepe_body.inc.php"); ?>


<script>
    $(document).ready(function() {
<?php include("inc/stepe_script.inc.php"); ?>
    });
</script>

<?php include("inc/footer.inc.php"); ?>

