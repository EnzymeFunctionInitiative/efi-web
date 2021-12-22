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
}

$request_type = isset($_POST["rt"]) ? $_POST["rt"] : false;

$result = array("valid" => false);

if (!$is_error && $request_type !== false && $job_obj !== false) {
    if ($request_type == "c") { // cancel
        $pbs_num = $job_obj->get_pbs_number();
        $status = $job_obj->get_status();
        if ($pbs_num) { 
            job_cancels::request_job_cancellation($db, $pbs_num, "cgfp");
        }
        $job_obj->mark_job_as_cancelled();
    } elseif ($request_type == "a") { // archive
        $job_obj->mark_job_as_archived();
    }
    $result["valid"] = true;
}

echo json_encode($result);


