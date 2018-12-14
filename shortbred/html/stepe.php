<?php 

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


$ExtraTitle = "Quantify Results for Identify ID $identify_id / Quantify ID $qid";
$job_obj = new quantify($db, $qid);


require_once("inc/stepe_vars.inc.php");

// Set in stepe_vars.inc.php:
//     $table_string
//     $filename

if (isset($_GET["as-table"])) {
    $table_filename = "${identify_id}_q${qid}_" . global_functions::safe_filename(pathinfo($filename, PATHINFO_FILENAME)) . "_settings.txt";
    functions::send_table($table_filename, $table_string);
    exit(0);
}


include("inc/header.inc.php");

?>

<h2><?php echo $ExtraTitle; ?></h2>
<p>&nbsp;</p>


<?php include("inc/stepe_body.inc.php"); ?>


<script>
    $(document).ready(function() {
<?php include("inc/stepe_script.inc.php"); ?>
    });
</script>

<?php include("inc/footer.inc.php"); ?>

