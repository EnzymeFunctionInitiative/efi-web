<?php
require_once("../includes/main.inc.php");
require_once(__BASE_DIR__ . "/libs/user_auth.class.inc.php");

$id = 0;
$key = 0;
$message = "";
$valid = 0;
$cookie_info = "";

if (empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
    $valid = 0;
    $displayMaxSize = ini_get('post_max_size');
    $message = "<br><b>The file was too large.  Please upload a file smaller than $displayMaxSize.</b>";
} elseif (isset($_POST['submit'])) {

    $valid = 1;
    $file_type = "";
    //Sets default % Co-Occurrence value if nothing was inputted.

    if (isset($_FILES['file'])) {
        $file_type = strtolower(pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION));
    }

    if (!isset($_FILES['file'])) {
        $valid = 0;
        $message .= "<br><b>Please select a file to upload</b>";
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

    $email          = $_POST['email'];
    $update_id      = isset($_POST['update-id']) ? $_POST['update-id'] : "";
    $update_key     = isset($_POST['update-key']) ? $_POST['update-key'] : "";
    $min_seq_len    = isset($_POST['min-seq-len']) ? $_POST['min-seq-len'] : "";
    $max_seq_len    = isset($_POST['max-seq-len']) ? $_POST['max-seq-len'] : "";
    $search_type    = isset($_POST['search-type']) ? $_POST['search-type'] : "";
    $ref_db         = isset($_POST['ref-db']) ? $_POST['ref-db'] : "";
    $cdhit_sid      = isset($_POST['cdhit-sid']) ? $_POST['cdhit-sid'] : "";
    $diamond_sens   = isset($_POST['diamond-sens']) ? $_POST['diamond-sens'] : "";
    $db_mod         = isset($_POST['db-mod']) ? $_POST['db-mod'] : "";

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
            $new_info = identify::create($db, $email, $_FILES['file']['tmp_name'], $_FILES['file']['name'], $create_params);
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
if ($valid && global_settings::is_recent_jobs_enabled() && user_auth::has_token_cookie()) {
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

error_log(print_r($output, true));

echo json_encode($output);

?>
