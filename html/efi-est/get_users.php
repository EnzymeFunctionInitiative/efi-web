<?php
require_once(__DIR__."/../../conf/settings_paths.inc.php");

use \efi\est\functions;


$accessKey = functions::get_user_list_access_key();

if (!array_key_exists("key", $_GET) || $_GET["key"] != $accessKey) {
    echo json_encode(array('status'=>'FAIL'));
    exit;
}

$startTime = "1900-01-01 00:00";
$endTime = "2200-12-31 23:59";
$optIn = 1;

if (array_key_exists("start_time", $_GET))
    $startTime = $_GET["start_time"];
if (array_key_exists("end_time", $_GET))
    $endTime = $_GET["end_time"];
if (array_key_exists("opt_in", $_GET))
    $optIn = $_GET["opt_in"];

$optInSql = "";
if ($optIn)
    $optInSql = "JOIN email_status AS E ON G.generate_email = E.email";

$sql = "SELECT DISTINCT G.generate_email FROM generate AS G $optInSql WHERE G.generate_time_created >= '$startTime' AND G.generate_time_created <= '$endTime' ORDER BY G.generate_email";
$db_result = $db->query($sql);

$emails = array();
$status = "SUCCESS";

if ($db_result) {
    foreach ($db_result as $row) {
        $email = $row['generate_email'];
        if (!in_array($email, $emails)) {
            array_push($emails, $email);
        }
    }
}

echo json_encode(array(
    'status'=>$status,
    'start_time'=>$startTime,
    'end_time'=>$endTime,
    'emails'=>$emails
));


