<?php
require_once(__DIR__."/../../init.php");

use \efi\gnt\settings;
use \efi\gnt\gnn;
use \efi\training\example_config;
use \efi\sanitize;
use \efi\file_types;
use \efi\send_file;


// This is for GNT files


$dl_type = sanitize::get_sanitize_string("dl", "");
$id = sanitize::validate_id("id", sanitize::GET);
$key = sanitize::validate_key("key", sanitize::GET);
$aid = sanitize::validate_id("aid", sanitize::GET);
$sub_type = sanitize::get_sanitize_string("ft", "");

if (!$dl_type || ($id === false && $aid === false) || $key === false) {
    print_error();
    exit();
}

$is_example = example_config::is_example();


$is_error = false;
$db_file = "";
$arrows = NULL;
$is_gnn = false;


$ok_file_types = array(
    file_types::FT_sizes => 1,
    file_types::FT_sp_clusters => 1,
    file_types::FT_sp_singletons => 1,
    file_types::FT_gnn_ssn => 1,
    file_types::FT_ssn_gnn => 1,
    file_types::FT_pfam_gnn => 1,
    file_types::FT_pfam_nn => 1,
    file_types::FT_gnn_nn => 1,
    file_types::FT_cooc_table => 1,
    file_types::FT_hub_count => 1,
    file_types::FT_gnd => 1,
    file_types::FT_pfam_dom => 1,
    file_types::FT_split_pfam_dom => 1,
    file_types::FT_all_pfam_dom => 1,
    file_types::FT_split_all_pfam_dom => 1,
    file_types::FT_gnn_mapping => 1,
    file_types::FT_gnn_mapping_dom => 1,
);


$mime_type = send_file::SEND_FILE_BINARY;

if ($dl_type == "gnn") {
    if (isset($ok_file_types[$sub_type])) {
        $gnn = new gnn($db, $id, false, true, $is_example);
        validate_key($gnn, $key);

        $file_name = $gnn->get_file_name($sub_type);
        $file_path = $gnn->get_file_path($sub_type);
    
        if ($file_name === false || $file_path === false) {
            print_error();
            exit(1);
        }
    
        if (substr($file_name, -3) != "zip")
            $mime_type = send_file::SEND_FILE_TABLE;
    
        output_file($file_path, $file_name, $mime_type);
    } else {
        print_error();
        exit(1);
    }
} else {
    print_error();
    exit(1);
}



function output_file($file_path, $file_name, $mime_type) {
    if ($file_path && filesize($file_path)) {
        return send_file::send($file_path, $file_name, $mime_type);
    } else {
        return false;
    }
}

function validate_key($obj, $key) {
    $obj_key = $obj->get_key();
    if ($obj_key != $key) {
        print_error();
        exit();
    }
}

function print_error() {
    echo "Error";
}

