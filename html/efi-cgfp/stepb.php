<?php 
require_once(__DIR__."/../../conf/settings_paths.inc.php");
require_once(__CGFP_DIR__."/includes/main.inc.php");
require_once(__CGFP_DIR__."/libs/job_manager.class.inc.php");


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


<?php require_once(__DIR__."/inc/footer.inc.php"); ?>


