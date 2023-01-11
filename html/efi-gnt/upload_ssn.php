<?php
require_once(__DIR__."/../../init.php");

use \efi\global_settings;
use \efi\gnt\settings;
use \efi\gnt\functions;
use \efi\gnt\gnn;
use \efi\gnt\user_jobs;
use \efi\sanitize;


if (!global_settings::get_website_enabled())
    exit;

$id = 0;
$key = 0;
$message = "";
$valid = 0;
$cookieInfo = "";
$is_sync = 0;

if (empty($_POST) && empty($_FILES) && $_SERVER["CONTENT_LENGTH"] > 0) {
    $valid = 0;
    $display_max_size = ini_get("post_max_size");
    $message = "<br><b>The file was too large.  Please upload a file smaller than $display_max_size.</b>";
} elseif (isset($_POST["submit"])) {

    $valid = 1;

    $email = sanitize::post_sanitize_email("email");

    $file_is_error = (isset($_FILES["file"]["error"]) && $_FILES["file"]["error"] != 0) ? $_FILES["file"]["error"] : 0;
    $file_name = (!$file_is_error && isset($_FILES["file"]) && $_FILES["file"]["name"]) ? $_FILES["file"]["name"] : "";
    $tmp_name = $file_name ? $_FILES["file"]["tmp_name"] : "";
    $file_type = "";

    //Sets default % Co-Occurrence value if nothing was inputted.
    $cooccurrence = sanitize::post_sanitize_num("cooccurrence", settings::get_default_cooccurrence());
    if ($file_name)
        $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    $gnn_parent_id = false;
    $parent_file_name = "";
    $parent_id = sanitize::validate_id("parent_id", sanitize::POST);
    $parent_key = sanitize::validate_key("parent_key", sanitize::POST);
    if ($parent_id !== false && $parent_key !== false) {
        $job_info = functions::verify_gnt_job($db, $parent_id, $parent_key);
        if ($job_info !== false) {
            $gnn_parent_id = $parent_id;
            $parent_file_name = $job_info["filename"];
            if (!$email) // we want to set the email address to the parent job email address if none is specified.
                $email = $job_info["email"];
        }
    }

    $job_name = sanitize::post_sanitize_string("job_name");
    $db_mod = sanitize::post_sanitize_string("db_mod");
    $extra_ram = global_settings::advanced_options_enabled() && sanitize::post_sanitize_flag("extra_ram");

    $est_analysis_id = 0;
    $est_ssn_idx = 0;

    $the_aid = sanitize::validate_id("est-id", sanitize::POST);
    if ($the_aid && !$file_name) {
        $the_key = sanitize::validate_key("est-key", sanitize::POST);
        $the_idx = sanitize::validate_id("est-ssn", sanitize::POST);
        if ($the_aid !== false && $the_key !== false && $the_idx !== false) {
            $job_info = functions::verify_est_job($db, $the_aid, $the_key, $the_idx);
            if ($job_info !== false) {
                $est_file_info = functions::get_est_filename($job_info, $the_aid, $the_idx);
                if ($est_file_info !== false) {
                    $est_analysis_id = $the_aid;
                    $est_ssn_idx = $the_idx;
                    $file_name = $est_file_info["filename"];
                }
            }
        }

        // Check validity of parent id later
        if (!$est_analysis_id && !$gnn_parent_id) {
            $valid = 0;
            $message .= "<br><b>Please select a file to upload</b>";
        }
    }
    elseif ($file_is_error) {
        $valid = 0;
        $message .= "<br><b>Error uploading file: " . functions::get_upload_error($file_is_error) . "</b>";
    }
    elseif (!functions::is_valid_file_type($file_type)) {
        $valid = 0;
        $message .= "<br><b>Invalid filetype ($file_type).</b>";
    }

    if (!$gnn_parent_id && !isset($email)) {
        $valid = 0;
        $message .= "<br><b>Please verify your e-mail address</b>";
    }

    if ((!is_int($cooccurrence)) || ($cooccurrence > 100) || ($cooccurrence < 0)) {
        $valid = 0;
        $message .= "<br><b>Invalid % Co-Occurrence.  It must be an integer between 0 and 100.</b>";
    }

    if ($valid) {
        $nb_size = sanitize::post_sanitize_num("neighbor_size");
        if ($est_analysis_id) {
            $parms = array(
                "email" => $email,
                "size" => $nb_size,
                "cooccurrence" => $cooccurrence,
                "est_id" => $est_analysis_id,
                "est_idx" => $est_ssn_idx,
                "db_mod" => $db_mod,
                "extra_ram" => $extra_ram,
                "filename" => $file_name,
            );
            $gnnInfo = gnn::create($db, $parms);
        } else {
            $parms = array(
                "size" => $nb_size,
                "cooccurrence" => $cooccurrence,
                "job_name" => $job_name,
                "is_sync" => $is_sync,
                "db_mod" => $db_mod,
                "extra_ram" => $extra_ram,
            );

            if ($gnn_parent_id) {
                $parms["parent_id"] = $gnn_parent_id;
                if (!$file_name) {
                    $parms["child_type"] = "filter";
                    $parms["filename"] = $parent_file_name;
                } else {
                    $parms["child_type"] = "upload";
                }
            }
            $parms["email"] = $email;

            if ($file_name && $tmp_name) {
                $parms["tmp_filename"] = $tmp_name;
                $parms["filename"] = $file_name;
            }

            $gnnInfo = gnn::create($db, $parms);
        }
        if ($gnnInfo === false) {
            $valid = false;
        } else {
            $id = $gnnInfo["id"];
            $key = $gnnInfo["key"];
        }
    }
}

// This resets the expiration date of the cookie so that frequent users don't have to login in every X days as long
// as they keep using the app.
if ($valid && settings::get_recent_jobs_enabled() && user_jobs::has_token_cookie()) {
    $cookieInfo = user_jobs::get_cookie_shared(user_jobs::get_user_token());
    $returnData["cookieInfo"] = $cookieInfo;
}

$output = array(
    "valid" => $valid,
    "id" => $id,
    "key" => $key,
    "message" => $message,
    "cookieInfo" => $cookieInfo
);

echo json_encode($output);


