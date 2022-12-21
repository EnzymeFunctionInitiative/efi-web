<?php 
require_once(__DIR__."/../../init.php");

use \efi\cgfp\job_manager;
use \efi\cgfp\job_types;
use \efi\sanitize;

$id = sanitize::validate_id("id", sanitize::GET);
$key = sanitize::validate_key("key", sanitize::GET);
$qid = sanitize::validate_id("quantify-id", sanitize::GET);

if ($id === false || $key === false || $qid === false) {
    error_404();
    exit;
} else {
    $job_mgr = new job_manager($db, job_types::Identify);
    if ($job_mgr->get_job_key($id) != $key) {
        error500("Unable to find the requested job.");
    }
}

$ExtraTitle = "Submission Confirmed";
require_once(__DIR__."/inc/header.inc.php");

?>



<h2>Submission Confirmed</h2>
<p>&nbsp;</p>
<p>An e-mail will be sent when ShortBRED has started and completed quantifying the markers against the selected metagenomes.</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p></p>
<p>&nbsp;</p>


<?php require_once(__DIR__."/inc/footer.inc.php"); ?>


