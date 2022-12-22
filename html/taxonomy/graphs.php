<?php
require_once(__DIR__."/../../init.php");

use \efi\est\functions;
use \efi\est\stepa;
use \efi\training\example_config;
use \efi\est\download;
use \efi\send_file;
use \efi\sanitize;

$is_example = example_config::is_example();

$id = sanitize::validate_id("id", sanitize::GET);
$key = sanitize::validate_key("key", sanitize::GET);

if ($id === false || $key === false) {
    error_404();
    exit;
}

$type = sanitize::get_sanitize_string("type", "");

$mime_type = send_file::SEND_FILE_BINARY;


if ($type == "len_hist") {
    $stepa = new stepa($db, $id, $is_example);
    validate_key($stepa, $key);

    $graph_type = sanitize::get_sanitize_string("gtype", "");
    $info = $stepa->get_graph_info($graph_type);
    if ($info === false) {
        print_error();
        exit();
    }

    output_file($info["file_path"], $info["file_name"], $mime_type);
} else {
    echo "No EFI-EST Selected. Please go back";
    exit;
}


function output_file($file_path, $file_name, $mime_type) {
    if (!download::output_file($file_path, $file_name, $mime_type)) {
        print_error();
        exit();
    }
}

function validate_key($obj, $key) {
    if (!download::validate_key($obj, $key)) {
        print_error();
        exit();
    }
}

function print_error() {
    echo "Error";
}


