<?php
require_once(__DIR__."/../../init.php");

use \efi\gnt\bigscape_job;


$message = "";

$valid = 1;

if (!isset($_POST["id"]) || !isset($_POST["key"]) || !isset($_POST["type"])) {
    $valid = 0;
    $message .= "Invalid request input.";
}

if ($valid) {
    $key = $_POST["key"];
    $id = $_POST["id"];
    $type = $_POST["type"];
    $valid = bigscape_job::create_bigscape_job($db, $id, $key, $type); // returns false if the key/id isn't valid
}

$returnData = array(
    "valid" => $valid,
    "message" => $message,
);


echo json_encode($returnData);


