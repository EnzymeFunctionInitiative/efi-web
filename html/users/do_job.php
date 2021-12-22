<?php
require_once(__DIR__."/../../init.php");

use \efi\job_manager;


$result = array("valid" => false, "message" => "");

if (!isset($_POST["action"])) {
    echo json_encode($result);
    exit(0);
}

$action = $_POST["action"];

if ($action == "update-group" || $action == "remove-group") {
    $job_ids = array();
    $job_ids_text = "";
    $user_group = "";
    $type = "";

    if (isset($_POST["job-ids"]))
        $job_ids_text = $_POST["job-ids"];
    if (isset($_POST["group"]))
        $user_group = $_POST["group"];
    if (isset($_POST["type"]))
        $type = $_POST["type"];

    if ($user_group && $job_ids_text && $type) {

        $db_name = "";
        if ($type == "est") {
            $db_name = __EFI_EST_DB_NAME__;
        } elseif ($type == "gnt") {
            $db_name = __EFI_GNT_DB_NAME__;
        } elseif ($type == "shortbred") {
            $db_name = __EFI_SHORTBRED_DB_NAME__;
            $type = "CGFP";
        }
        $type = strtoupper($type);

        $job_ids = explode(",", $job_ids_text);
        if (count($job_ids)) {
            $update_result = false;
            $do_update = $action == "update-group" ? true : false;
            
            $update_result = job_manager::update_job_group($db, $db_name, $job_ids, $user_group, $type, $do_update);
            
            $result["valid"] = $update_result;
            if (!$update_result) {
                $result["message"] = "Unknown error occurred.";
            }
        }
    }
}


echo json_encode($result);


