<?php 

require_once "../includes/main.inc.php";
require_once "../libs/job_manager.class.inc.php";


if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['key'])) {
    error500("Unable to find the requested job.");
} else {
    $job_mgr = new job_manager($db, job_types::Identify);
    if ($job_mgr->get_job_key($_GET['id']) != $_GET['key']) {
        error500("Unable to find the requested job.");
    }
}

$ExtraTitle = "Submission Confirmed";
require_once "inc/header.inc.php"; 

?>



<h2>Submission Confirmed</h2>
<p>&nbsp;</p>
<p>An e-mail will be sent when ShortBRED has started and completed generating the markers.</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p></p>
<p>&nbsp;</p>


<?php require_once "inc/footer.inc.php"; ?>


