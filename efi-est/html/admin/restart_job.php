<?php
require_once '../includes/stats_main.inc.php';

if (!array_key_exists("job-id", $_GET) || !is_numeric($_GET["job-id"])) {
    header("HTTP/1.0 500 Internal Server Error");
    exit(1);
}

$job_id = $_GET["job-id"];

$db->query("UPDATE generate SET generate_status = 'NEW' WHERE generate_id = $job_id");

echo json_encode(array(
    'id'=>$job_id,
    'status'=>'SUCCESS'
));

?>

