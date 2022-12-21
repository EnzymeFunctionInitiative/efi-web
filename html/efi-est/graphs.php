<?php
require_once(__DIR__."/../../init.php");

use \efi\est\functions;
use \efi\est\stepa;
use \efi\est\analysis;
use \efi\training\example_config;
use \efi\est\download;
use \efi\sanitize;
use \efi\send_file;


$type = isset($_GET["type"]) ? $_GET["type"] : "";
$type = download::validate_file_type($type);
$gen_id = sanitize::validate_id("id", sanitize::GET);
$analysis_id = sanitize::validate_id("aid", sanitize::GET);
$key = sanitize::validate_key("key", sanitize::GET);
$is_example = example_config::is_example();

$mime_type = send_file::SEND_FILE_BINARY;

if ($key === false) {
    echo "No EFI-EST Selected. Please go back (K)";
    exit(1);
}


if ($type === "stepc") {
    $graph = sanitize::get_sanitize_string("graph", "");
    $small = false;
    if (isset($_GET["preview"]))
        $small = true;

    $stepa = new stepa($db, $gen_id, $is_example);
    validate_key($stepa, $key);

    $info = $stepa->get_graph_info($graph);
    if ($info === false) {
        print_error();
        exit(1);
    }

    $file_path = $info["file_path"];
    if ($small && isset($info["small_path"]))
        $file_path = $info["small_path"];
    output_file($file_path, $info["file_name"], $mime_type);

} else if ($type === "nc") {
    $analysis = new analysis($db, $analysis_id, $is_example);
    validate_key($analysis, $key);

    $graph_type = sanitize::get_sanitize_string("atype", "");
    $ssn_idx = sanitize::get_sanitize_num("net", -1);

    $info = $analysis->get_graph_info($graph_type, $ssn_idx);
    if ($info === false) {
        print_error();
        exit(1);
    }

    output_file($info["file_path"], $info["file_name"], $mime_type);
} else {
    echo "No EFI-EST Selected. Please go back";
    exit;
}



function output_file($file_path, $file_name, $mime_type) {
    if (!download::output_file($file_path, $file_name, $mime_type)) {
        print_error();
        exit(1);
    }
}

function validate_key($obj, $key) {
    if (!download::validate_key($obj, $key)) {
        print_error();
        exit(1);
    }
}

function print_error() {
    echo "Error";
}


