<?php
require_once(__DIR__."/../../init.php");

use \efi\gnt\bigscape_job;
use \efi\sanitize;


$message = "";

$valid = 1;

$id = sanitize::post_sanitize_num("id");
$key = sanitize::post_sanitize_key("key");
$type = sanitize::post_sanitize_string("type");


if (!isset($id) || !isset($key) || !isset($type)) {
    $valid = 0;
    $message .= "Invalid request input.";
}

if ($valid) {
    $valid = bigscape_job::create_bigscape_job($db, $id, $key, $type); // returns false if the key/id isn't valid
}

$returnData = array(
    "valid" => $valid,
    "message" => $message,
);


echo json_encode($returnData);


