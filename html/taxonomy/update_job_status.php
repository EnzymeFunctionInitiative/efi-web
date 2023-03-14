<?php
require_once(__DIR__."/../../init.php");

use \efi\global_functions;
use \efi\sanitize;


$id = sanitize::validate_id("id", sanitize::POST);
$key = sanitize::validate_key("key", sanitize::POST);

if ($id === false || $key === false) {
    echo json_encode(array("valid" => false));
    exit(1);
}

$generate = new \efi\est\stepa($db, $id);

if ($generate->get_key() != $key) {
    echo json_encode(array("valid" => false));
    exit(1);
}

$job = new \efi\job_status($db, $generate);


$request_type = sanitize::post_sanitize_string("rt", false);

if ($request_type == "c") {
    $job->cancel();
} else if ($request_type == "a") {
    $job->archive();
}

$result["valid"] = true;


echo json_encode($result);


