<?php
require_once(__DIR__."/../../../init.php");


if (!isset($_GET["a"]) || !isset($_GET["job-id"]) || !is_numeric($_GET["job-id"])) {
    header("HTTP/1.0 500 Internal Server Error");
    exit(1);
}

$job_id = $_GET["job-id"];
$a = $_GET["a"];
$job_type = isset($_GET["t"]) ? $_GET["t"] : "";
$table = $job_type == "generate" ? "generate" : ($job_type == "analysis" ? "analysis" : "");

if ($table) {
    if ($a == "unarchive") {
        $db->non_select_query("UPDATE ${table} SET ${table}_status = 'FINISH' WHERE ${table}_id = $job_id");
    } else if ($a == "restart") {
        $db->non_select_query("UPDATE ${table} SET ${table}_status = 'NEW' WHERE ${table}_id = $job_id");
    }
}

echo json_encode(array(
    'id'=>$job_id,
    'status'=>'SUCCESS'
));
