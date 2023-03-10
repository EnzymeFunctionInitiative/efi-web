<?php
require_once(__DIR__."/../../init.php");

use \efi\est\stepa;
use \efi\est\analysis;
use \efi\est\functions;
use \efi\sanitize;
use \efi\job_status;


$is_error = false;
$job_obj = false;
$is_generate = true;

$id = sanitize::validate_id("id", sanitize::POST);
$key = sanitize::validate_key("key", sanitize::POST);
$aid = sanitize::validate_id("aid", sanitize::POST);

if ($id === false || $key === false) {
    error_404();
    exit;
}

if ($id !== false && $key !== false) {
    if ($aid !== false && $aid > 0) {
        $job_obj = new analysis($db, $aid);
        $is_generate = false;
    } else {
        $job_obj = new stepa($db, $id);
    }

    $jkey = $job_obj->get_key();
    if ($job_obj->get_key() != $key) {
        $is_error = true;
    } else {
        $is_error = false;
    }

    if (!$is_error)
        $status = new job_status($db, $job_obj);
} else {
    $is_error = true;
}

$request_type = sanitize::post_sanitize_string("rt", "");

$result = array("valid" => false);

if (!$is_error && $request_type !== false && $job_obj !== false && ($request_type == "c" || $request_type == "a")) {
    if ($status->is_running()) { // cancel
        $status->cancel();
    } else {
        $status->archive();
    }
    $result["valid"] = true;
}

echo json_encode($result);


