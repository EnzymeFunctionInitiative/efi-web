<?php
require_once(__DIR__."/../../init.php");

use \efi\est\job_status;
use \efi\global_functions;
use \efi\sanitize;


$is_error = false;
$id = sanitize::validate_id("id", sanitize::POST);
$key = sanitize::validate_key("key", sanitize::POST);

if ($id === false || $key === false) {
    echo json_encode(array("valid" => false));
    exit(1);
}

$job = new job_status($db, $id);

if (!$job->load_validate($key)) {
    echo json_encode(array("valid" => false));
    die("HI");
    exit(1);
}


$request_type = sanitize::post_sanitize_string("rt", false);

if ($request_type == "c") {
    $job->cancel();
} else if ($request_type == "a") {
    $job->archive();
}

$result["valid"] = true;


echo json_encode($result);


