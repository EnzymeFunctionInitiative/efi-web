<?php
require_once(__DIR__."/../../init.php");

use \efi\global_settings;
use \efi\gnt\settings;
use \efi\gnt\functions;
use \efi\gnt\diagram_jobs;
use \efi\gnt\user_jobs;


if (!global_settings::get_website_enabled())
    exit;

$id = 0;
$key = 0;
$message = "";
$valid = 0;
$cookieInfo = "";

$valid = 1;
$file_type = "";

if (isset($_FILES['file'])) {
    $file_type = strtolower(pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION));
}

if (!isset($_FILES['file'])) {
    $valid = 0;
    $message .= "<br><b>Please select a file to upload</b>";
}
elseif (isset($_FILES['file']['error']) && ($_FILES['file']['error'] != 0)) {
    $valid = 0;
    $message .= "<br><b>Error uploading file: " . functions::get_upload_error($_FILES['file']['error']) . "</b>";
}
elseif (!functions::is_valid_diagram_file_type($file_type)) {
    $valid = 0;
    $message .= "<br><b>Invalid filetype ($file_type).  The file has to be an " . settings::get_valid_diagram_file_types() . " filetype.</b>";
}

$email = sanitize::post_sanitize_email("email");
if (!isset($email)) {
    $valid = 0;
    $message .= "<br><b>Please verify your e-mail address</b>";
}
$jobGroup = sanitize::post_sanitize_string("job-group", "");

if ($valid) {
    $arrowInfo = diagram_jobs::create_file($db, $email, $_FILES['file']['tmp_name'], $_FILES['file']['name'], $jobGroup);
    if ($arrowInfo === false) {
        $valid = false;
    } else {
        $id = $arrowInfo['id'];
        $key = $arrowInfo['key'];
    }
}

// This resets the expiration date of the cookie so that frequent users don't have to login in every X days as long
// as they keep using the app.
if ($valid && settings::get_recent_jobs_enabled() && user_jobs::has_token_cookie()) {
    $cookieInfo = user_jobs::get_cookie_shared(user_jobs::get_user_token());
    $returnData["cookieInfo"] = $cookieInfo;
}

echo json_encode(array(
    'valid' => $valid,
    'id' => $id,
    'key' => $key,
    'message' => $message,
    'cookieInfo' => $cookieInfo
));


