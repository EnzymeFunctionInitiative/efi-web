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

$id = $_GET["id"];
$key = $_GET["key"];
$qid = $_GET["quantify-id"];
$id_query_string = "id=$id&key=$key&quantify-id=$qid";


$job = new quantify($db, $qid);
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
    $ExtraTitle = "Quantify Computation Results";
    $is_finished = true;
}



require_once "inc/header.inc.php"; 

?>



<h2><?php echo $ExtraTitle; ?></h2>
<p>&nbsp;</p>

<?php if ($is_failed) { ?>
<p>There was an error generating the marker data.</p>
<?php } elseif ($is_running) { ?>
<p>The computation is still running.</p>
<?php } else { ?>

<p>ShortBRED-Quantify has finished.</p>

<p><a href="download_files.php?type=ssn-q&<?php echo $id_query_string; ?>">Download SSN with markers and protein abundance.</a></p>

<p><a href="download_files.php?type=q-prot&<?php echo $id_query_string; ?>">Download abundance data by protein.</a></p>

<p><a href="download_files.php?type=q-clust&<?php echo $id_query_string; ?>">Download abundance data by cluster.</a></p>


<?php } ?>

<p>&nbsp;</p>
<p>&nbsp;</p>
<p></p>
<p>&nbsp;</p>


<?php require_once "inc/footer.inc.php"; ?>


