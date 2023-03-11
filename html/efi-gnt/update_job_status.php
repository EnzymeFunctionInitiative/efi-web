<?php
require_once(__DIR__."/../../init.php");

use \efi\gnt\gnn;
use \efi\gnt\diagram_job;
use \efi\sanitize;


$is_error = false;
$id = sanitize::validate_id("id", sanitize::POST);
$key = sanitize::validate_key("key", sanitize::POST);
$job_type = sanitize::post_sanitize_string("jt", "");
$job_obj = false;

if ($id !== false && $key !== false) {
    $job_type = ($job_type == "d") ? "d" : "g";
    if ($job_type == "d")
        $job_obj = new diagram_job($db, $id);
    else
        $job_obj = new gnn($db, $id);

    if ($job_obj->get_key() != $key) {
        $is_error = true;
    } else {
        $is_error = false;
    }

    if (!$is_error)
        $status = new \efi\job_status($db, $job_obj);
}

$request_type = sanitize::post_sanitize_string("rt");

$result = array("valid" => false);

if (!$is_error && $request_type !== false && $job_obj !== false) {
    if ($status->is_running()) { // cancel
        $status->cancel();
    } else {
        $status->archive();
    }
    $result["valid"] = true;
}

echo json_encode($result);


