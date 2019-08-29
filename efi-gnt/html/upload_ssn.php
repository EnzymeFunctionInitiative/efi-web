<?php
require_once("../includes/main.inc.php");
require_once("../libs/user_jobs.class.inc.php");
$id = 0;
$key = 0;
$message = "";
$valid = 0;
$cookieInfo = "";

if (empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
    $valid = 0;
    $displayMaxSize = ini_get('post_max_size');
    $message = "<br><b>The file was too large.  Please upload a file smaller than $displayMaxSize.</b>";
} elseif (isset($_POST['submit'])) {

    $valid = 1;

    $email = isset($_POST['email']) ? $_POST['email'] : "";
    $file_is_error = (isset($_FILES['file']['error']) && $_FILES['file']['error'] != 0) ? $_FILES['file']['error'] : 0;
    $file_name = (!$file_is_error && isset($_FILES['file']) && $_FILES['file']['name']) ? $_FILES['file']['name'] : "";
    $tmp_name = $file_name ? $_FILES['file']['tmp_name'] : "";
    $file_type = "";

    //Sets default % Co-Occurrence value if nothing was inputted.
    $cooccurrence = settings::get_default_cooccurrence();
    if ($_POST['cooccurrence'] != "")
        $cooccurrence = (int)$_POST['cooccurrence'];
    if ($file_name)
        $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    $parent_file_name = "";
    $gnn_parent_id = 0;
    if (isset($_POST["parent_id"]) && isset($_POST["parent_key"])) {
        $parent_id = $_POST["parent_id"];
        $parent_key = $_POST["parent_key"];
        $job_info = functions::verify_gnt_job($db, $parent_id, $parent_key);
        if ($job_info !== false) {
            $gnn_parent_id = $parent_id;
            $parent_file_name = $job_info['filename'];
            if (!$email) // we want to set the email address to the parent job email address if none is specified.
                $email = $job_info['email'];
        }
    }

    $est_analysis_id = 0;
    $est_file_path = ""; // relative to the EST job dir
    $est_file_Name = "";

    if (!$file_name) {
        if (isset($_POST["est-id"]) && isset($_POST["est-key"]) && isset($_POST["est-ssn"])) {
            $the_aid = $_POST["est-id"];
            $the_key = $_POST["est-key"];
            $the_idx = $_POST["est-ssn"];

            $job_info = functions::verify_est_job($db, $the_aid, $the_key, $the_idx);
            if ($job_info !== false) {
                $est_file_info = functions::get_est_filename($job_info, $the_aid, $the_idx);
                if ($est_file_info !== false) {
                    $est_analysis_id = $job_info["analysis_id"];
                    $est_file_path = $est_file_info["full_ssn_path"];
                    $est_file_name = $est_file_info["filename"];
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
        $message .= "<br><b>Invalid filetype ($file_type).  The file has to be an " . settings::get_valid_file_types() . " filetype.</b>";
    }

    if (!$gnn_parent_id && !functions::verify_email($email)) {
        $valid = 0;
        $message .= "<br><b>Please verify your e-mail address</b>";
    }

    if ((!is_int($cooccurrence)) || ($cooccurrence > 100) || ($cooccurrence < 0)) {
        $valid = 0;
        $message .= "<br><b>Invalid % Co-Occurrence.  It must be an integer between 0 and 100.</b>";
    }

    $job_name = isset($_POST['job_name']) ? $_POST['job_name'] : "";
    $db_mod = isset($_POST['db_mod']) ? $_POST['db_mod'] : "";

    $is_sync = false;
    if ($valid && isset($_POST["sync_key"]) && functions::check_sync_key($_POST["sync_key"])) {
        $is_sync = true;
    }

    if ($valid) {
        if ($est_analysis_id) {
            $gnnInfo = gnn::create_from_est_job($db, $email, $_POST['neighbor_size'], $cooccurrence, $est_file_path, $est_analysis_id, $db_mod);
        } else {
            $parms = array(
                'size' => $_POST['neighbor_size'],
                'cooccurrence' => $cooccurrence,
                'job_name' => $job_name,
                'is_sync' => $is_sync,
                'db_mod' => $db_mod,
            );

            if ($gnn_parent_id) {
                $parms['parent_id'] = $gnn_parent_id;
                if (!$file_name) {
                    $parms['child_type'] = 'filter';
                    $parms['filename'] = $parent_file_name;
                } else {
                    $parms['child_type'] = 'upload';
                }
            }
            $parms['email'] = $email;

            if ($file_name && $tmp_name) {
                $parms['tmp_filename'] = $tmp_name;
                $parms['filename'] = $file_name;
            }

            $gnnInfo = gnn::create4($db, $parms);
        }
        if ($gnnInfo === false) {
            $valid = false;
        } else {
            $id = $gnnInfo['id'];
            $key = $gnnInfo['key'];
        }
    }

    if ($is_sync) {
        $gnn = new gnn($db, $id, $is_sync);
        $result = $gnn->run_gnn_sync(functions::get_is_debug());
        if ($result['RESULT']) {
            $message = functions::dump_gnn_info($gnn, $is_sync);
        } else {
            $valid = false;
            $message = "Unable to run synchronously: " . $result['MESSAGE'];
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
    'valid' => $valid,
    'id' => $id,
    'key' => $key,
    'message' => $message,
    'cookieInfo' => $cookieInfo
);

echo json_encode($output);

?>
