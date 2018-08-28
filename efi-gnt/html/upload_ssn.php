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
    $file_type = "";
    //Sets default % Co-Occurrence value if nothing was inputted.

    $cooccurrence = settings::get_default_cooccurrence();
    if ($_POST['cooccurrence'] != "") {
        $cooccurrence = (int)$_POST['cooccurrence'];
    }
    if (isset($_FILES['file'])) {
        $file_type = strtolower(pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION));
    }

    $est_analysis_id = "";
    $est_file_path = ""; // relative to the EST job dir
    $est_file_Name = "";
    if (!isset($_FILES['file'])) {
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

        if (!$est_analysis_id) {
            $valid = 0;
            $message .= "<br><b>Please select a file to upload</b>";
        }
    }
    elseif (isset($_FILES['file']['error']) && ($_FILES['file']['error'] != 0)) {
        $valid = 0;
        $message .= "<br><b>Error uploading file: " . functions::get_upload_error($_FILES['file']['error']) . "</b>" . $_POST['MAX_FILE_SIZE'];
    }
    elseif (!functions::is_valid_file_type($file_type)) {
        $valid = 0;
        $message .= "<br><b>Invalid filetype ($file_type).  The file has to be an " . settings::get_valid_file_types() . " filetype.</b>";
    }

    if (!functions::verify_email($_POST['email'])) {
        $valid = 0;
        $message .= "<br><b>Please verify your e-mail address</b>";
    }

    if ((!is_int($cooccurrence)) || ($cooccurrence > 100) || ($cooccurrence < 0)) {
        $valid = 0;
        $message .= "<br><b>Invalid % Co-Occurrence.  It must be an integer between 0 and 100.</b>";
    }

    $email = $_POST['email'];
    $job_name = isset($_POST['job_name']) ? $_POST['job_name'] : "";

    $is_sync = false;
    if ($valid && isset($_POST["sync_key"]) && functions::check_sync_key($_POST["sync_key"])) {
        $is_sync = true;
    }

    if ($valid) {
        if ($est_analysis_id) {
            $gnnInfo = gnn::create_from_est_job($db, $email, $_POST['neighbor_size'], $cooccurrence, $est_file_path, $est_analysis_id);
        } else {
            $gnnInfo = gnn::create3(
                $db, $email, $_POST['neighbor_size'], $cooccurrence,
                $_FILES['file']['tmp_name'], $_FILES['file']['name'],
                $job_name, $is_sync
            );
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
if ($valid && settings::is_recent_jobs_enabled() && user_jobs::has_token_cookie()) {
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

error_log(print_r($output, true));

echo json_encode($output);

?>
