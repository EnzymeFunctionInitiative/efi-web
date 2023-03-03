<?php
require_once(__DIR__."/../../init.php");

use \efi\global_settings;
use \efi\cgfp\settings;
use \efi\cgfp\functions;
use \efi\cgfp\identify;
use \efi\user_auth;
use \efi\sanitize;

// settings class isn't used, so we get an error when loading global_settings because this file isn't loaded.
require_once(__CGFP_CONF_DIR__."/settings.inc.php");

if (!global_settings::get_website_enabled())
    exit;

$id = 0;
$key = 0;
$message = "";
$valid = 0;
$cookie_info = "";

if (empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
    $valid = 0;
    $displayMaxSize = ini_get('post_max_size');
    $message = "The file was too large.  Please upload a file smaller than $displayMaxSize.";
} elseif (isset($_POST['submit'])) {

    $valid = 1;
    $file_type = "";
    //Sets default % Co-Occurrence value if nothing was inputted.

    $est_id = sanitize::validate_id("est-id", sanitize::POST);
    $est_key = sanitize::validate_key("est-key", sanitize::POST);

    if (isset($_FILES['file'])) {
        $file_type = strtolower(pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION));
    }

    if ($est_id !== false && $est_key !== false) {
        if (!functions::get_est_job_filename($db, $est_id, $est_key)) {
            $valid = 0;
            $message .= "Invalid EST job.";
        }
    } else {
        if (!isset($_FILES['file'])) {
            $valid = 0;
            $message .= "Please select a file to upload";
        } elseif (isset($_FILES['file']['error']) && ($_FILES['file']['error'] != 0)) {
            $valid = 0;
            $message .= "Error uploading file: " . functions::get_upload_error($_FILES['file']['error']);
        } elseif (!$est_id && !functions::is_valid_file_type($file_type)) {
            $valid = 0;
            $message .= "Invalid filetype ($file_type).  The file has to be an " . settings::get_valid_file_types() . " filetype.";
        }
    }

    $email = sanitize::post_sanitize_email("email"); // returns null if not valid
    if (!$email) {
        $valid = 0;
        $message .= "Please verify your e-mail address";
    }

    $update_id      = sanitize::post_sanitize_string("update-id", "");
    $update_key     = sanitize::post_sanitize_string("update-key", "");
    $min_seq_len    = sanitize::post_sanitize_num("min-seq-len", "");
    $max_seq_len    = sanitize::post_sanitize_num("max-seq-len", "");
    $search_type    = sanitize::post_sanitize_string("search-type", "");
    $ref_db         = sanitize::post_sanitize_string("ref-db", "");
    $cdhit_sid      = sanitize::post_sanitize_string("cdhit-sid", "");
    $diamond_sens   = sanitize::post_sanitize_string("diamond-sens", "");
    $db_mod         = sanitize::post_sanitize_string("db-mod", "");

    if ($valid) {
        if ($update_id && $update_key) {
            $new_info = identify::create_update_ssn($db, $email, $_FILES['file']['tmp_name'], $_FILES['file']['name'], $update_id, $update_key);
        } else {
            $create_params = array(
                'min_seq_len' => $min_seq_len,
                'max_seq_len' => $max_seq_len,
                'search_type' => $search_type,
                'ref_db' => $ref_db,
                'cdhit_sid' => $cdhit_sid,
                'diamond_sens' => $diamond_sens,
                'db_mod' => $db_mod,
            );
            if ($est_id && $est_key) {
                $new_info = identify::create_from_est($db, $email, $create_params, $est_id, $est_key);
            } else {
                $new_info = identify::create($db, $email, $_FILES['file']['tmp_name'], $_FILES['file']['name'], $create_params);
            }
        }

        if ($new_info === false) {
            $valid = false;
        } else {
            $id = $new_info['id'];
            $key = $new_info['key'];
        }
    }
}

// This resets the expiration date of the cookie so that frequent users don't have to login in every X days as long
// as they keep using the app.
if ($valid && global_settings::get_recent_jobs_enabled() && user_auth::has_token_cookie()) {
    $cookie_info = user_auth::get_cookie_shared(user_auth::get_user_token());
    $return_data["cookie_info"] = $cookie_info;
}

$output = array(
    'valid' => $valid,
    'id' => $id,
    'key' => $key,
    'message' => $message,
    'cookie_info' => $cookie_info
);


echo json_encode($output);


