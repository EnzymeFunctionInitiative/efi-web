<?php
require_once(__DIR__."/../../init.php");

use \efi\gnt\gnn;
use \efi\gnt\diagram;
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
}

$request_type = sanitize::post_sanitize_string("rt");

$result = array("valid" => false);

if (!$is_error && $request_type !== false && $job_obj !== false) {
    if ($request_type == "c") { // cancel
        $pbs_num = $job_obj->get_pbs_number();
        $status = $job_obj->get_status();
        if ($pbs_num) { 
            //TODO: job_cancels::request_job_cancellation($db, $pbs_num);
        }
        //TODO: $job_obj->mark_job_as_cancelled();
    } elseif ($request_type == "a") { // archive
        $job_obj->mark_job_as_archived();
    }
    $result["valid"] = true;
}

echo json_encode($result);


