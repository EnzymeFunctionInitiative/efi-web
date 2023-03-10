<?php
require_once(__DIR__."/../../init.php");

use \efi\cgfp\job_cancels;
use \efi\cgfp\identify;
use \efi\cgfp\quantify;


$is_error = false;
$the_id = "";
$q_id = "";
$job_obj = false;

if (isset($_POST["id"]) && is_numeric($_POST["id"]) && isset($_POST["key"])) {
    $the_id = $_POST["id"];
    if (isset($_POST["quantify-id"]) && is_numeric($_POST["quantify-id"])) {
        $q_id = $_POST["quantify-id"];
        $job_obj = new quantify($db, $q_id);
    } else {
        $job_obj = new identify($db, $the_id);
    }

    if ($job_obj->get_key() != $_POST["key"]) {
        $is_error = true;
    } else {
        $is_error = false;
    }

    if (!$is_error)
        $status = new \efi\job_status($db, $job_obj);
}

$request_type = isset($_POST["rt"]) ? $_POST["rt"] : false;

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


