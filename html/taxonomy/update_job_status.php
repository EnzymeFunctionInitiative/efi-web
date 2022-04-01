<?php
require_once(__DIR__."/../../init.php");

use \efi\est\job_status;
use \efi\global_functions;


$is_error = false;
$id = $_POST["id"];
$key = $_POST["key"];

if (!global_functions::validate_id($id)) {
    echo json_encode(array("valid" => false));
    exit(1);
}

$job = new job_status($db, $id);

if (!$job->load_validate($key)) {
    echo json_encode(array("valid" => false));
    die("HI");
    exit(1);
}


$request_type = isset($_POST["rt"]) ? $_POST["rt"] : false;

if ($request_type == "c") {
    $job->cancel();
} else if ($request_type == "a") {
    $job->archive();
}

$result["valid"] = true;


echo json_encode($result);


