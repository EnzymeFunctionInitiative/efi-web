<?php

require_once("../includes/main.inc.php");
require_once("../libs/job_manager.class.inc.php");
require_once("../libs/quantify.class.inc.php");

$id = 0;
$quantify_id = 0;
$key = 0;
$message = "";
$valid = 0;

if (isset($_POST['key']) && isset($_POST['id']) && isset($_POST['hmp-ids'])) {

    $key = $_POST['key'];
    $id = $_POST['id'];
    $hmp_ids = $_POST['hmp-ids'];
    $search_type = isset($_POST['search-type']) ? $_POST['search-type'] : "";

    if ($hmp_ids) {
        $valid = 1;
    }

    if ($valid) {
        $job_mgr = new job_manager($db, job_types::Identify);
    
        if (!$job_mgr->validate_job($id, $key)) {
            $valid = 0;
            $message .= "<b>Invalid job</b>";
        }
    }

    if ($valid) {
        $new_info = quantify::create($db, $id, $hmp_ids, $search_type);
        if ($new_info === false) {
            $valid = 0;
        } else {
            $quantify_id = $new_info['id'];
        }
    }
}

//// This resets the expiration date of the cookie so that frequent users don't have to login in every X days as long
//// as they keep using the app.
//if ($valid && global_settings::is_recent_jobs_enabled() && user_auth::has_token_cookie()) {
//    $cookieInfo = user_auth::get_cookie_shared(user_auth::get_user_token());
//    $returnData["cookieInfo"] = $cookieInfo;
//}

$output = array(
    'valid' => $valid,
    'quantify_id' => $quantify_id,
    'message' => $message,
    //'cookieInfo' => $cookieInfo
);

echo json_encode($output);

?>
