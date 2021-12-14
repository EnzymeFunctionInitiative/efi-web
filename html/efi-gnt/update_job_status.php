<?php
require_once(__DIR__."/../../init.php");

require_once(__GNT_DIR__."/includes/main.inc.php");

use \efi\gnt\gnn;
use \efi\gnt\diagram;


$is_error = false;
$the_id = "";
$job_obj = false;

if (isset($_POST["id"]) && is_numeric($_POST["id"]) && isset($_POST["key"])) {
    $the_id = $_POST["id"];
    $job_type = (isset($_POST["jt"]) && $_POST["jt"] == "d") ? "d" : "g";
    if ($job_type == "d")
        $job_obj = new diagram_job($db, $the_id);
    else
        $job_obj = new gnn($db, $the_id);

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
            //TODO: job_cancels::request_job_cancellation($db, $pbs_num);
        }
        //TODO: $job_obj->mark_job_as_cancelled();
    } elseif ($request_type == "a") { // archive
        $job_obj->mark_job_as_archived();
    }
    $result["valid"] = true;
}

echo json_encode($result);


?>
