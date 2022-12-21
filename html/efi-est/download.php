<?php
require_once(__DIR__."/../../init.php");

use \efi\global_functions;
use \efi\send_file;
use \efi\est\nb_conn;
use \efi\est\conv_ratio;
use \efi\est\stepa;
use \efi\est\analysis;
use \efi\training\example_config;
use \efi\est\download;
use \efi\file_types;
use \efi\sanitize;



$dl_type = sanitize::get_sanitize_string("dl", "");
$dl_type = download::validate_file_type($dl_type);
$id = sanitize::validate_id("id", sanitize::GET);
$key = sanitize::validate_key("key", sanitize::GET);
$aid = sanitize::validate_id("aid", sanitize::GET);
$sub_type = sanitize::get_sanitize_string("ft", "");

if (!$dl_type || ($id === false && $aid === false) || $key === false) {
    print_error();
    exit();
}

$is_example = example_config::is_example();

if ($dl_type == "ssn" && $aid !== false) {
    $ssn_idx = sanitize::validate_id("idx", sanitize::GET);
    if ($aid === false || $ssn_idx === false) {
        print_error();
        exit;
    }

    $analysis = new analysis($db, $aid, $is_example);
    validate_key($analysis, $key);

    $file_name = $analysis->get_file_name(file_types::FT_ssn, $ssn_idx);
    $file_path = $analysis->get_file_path(file_types::FT_ssn, $ssn_idx);
    if ($file_name === false || $file_path === false) {
        print_error();
        exit;
    }

    $mime_type = send_file::SEND_FILE_BINARY;
    output_file($file_path, $file_name, $mime_type);
} else if ($dl_type == "convratio") {
    $obj = new conv_ratio($db, $id, $is_example);
    validate_key($obj, $key);

    $file_path = "";
    $file_name = "";
    $mime_type = send_file::SEND_FILE_BINARY;
    if ($sub_type === "tab") {
        $file_path = $obj->get_file_path(file_types::FT_cr_table);
        $file_name = $obj->get_file_name(file_types::FT_cr_table);
        $mime_type = send_file::SEND_FILE_TABLE;
    }

    output_file($file_path, $file_name, $mime_type);
} else if ($dl_type === "nbconn") {
    $obj = new nb_conn($db, $id, $is_example);
    validate_key($obj, $key);

    $file_path = "";
    $file_name = "";
    $mime_type = send_file::SEND_FILE_BINARY;
    if (!$sub_type || $sub_type === "ssn") {
        $file_path = $obj->get_file_path(file_types::FT_ssn);
        $file_name = $obj->get_file_name(file_types::FT_ssn);
        //$mime_type = send_file::SEND_FILE_ZIP;
    } else if ($sub_type === "legend") {
        $file_path = $obj->get_file_path(file_types::FT_nc_legend);
        $file_name = $obj->get_file_name(file_types::FT_nc_legend);
        $mime_type = send_file::SEND_FILE_PNG;
    } else if ($sub_type === "nc") {
        $file_path = $obj->get_file_path(file_types::FT_nc_table);
        $file_name = $obj->get_file_name(file_types::FT_nc_table);
        $mime_type = send_file::SEND_FILE_TABLE;
    } else {
        print_error();
        exit();
    }

    output_file($file_path, $file_name, $mime_type);
} else if ($dl_type === "blasthits") {
    $analysis_id = sanitize::validate_key("analysis_id", sanitize::GET);
	$stepa = new stepa($db, $id, $is_example);
    validate_key($stepa, $key);

    $analysis = new analysis($db, $analysis_id, $is_example);
    $file_name = $analysis->get_file_name(file_types::FT_blast_evalue);
    $full_path = $analysis->get_file_path(file_types::FT_blast_evalue);
    output_file($full_path, $file_name, send_file::SEND_FILE_TABLE);
} else {
    print_error();
    exit();
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


