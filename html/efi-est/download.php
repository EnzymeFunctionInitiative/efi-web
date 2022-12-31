<?php
require_once(__DIR__."/../../init.php");

use \efi\global_functions;
use \efi\send_file;
use \efi\est\stepa;
use \efi\est\analysis;
use \efi\est\nb_conn;
use \efi\est\conv_ratio;
use \efi\est\cluster_analysis;
use \efi\est\colorssn;
use \efi\training\example_config;
use \efi\est\download;
use \efi\file_types;
use \efi\sanitize;



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

$file_name = "";
$file_path = "";
$mime_type = send_file::SEND_FILE_BINARY;

if ($dl_type == "ssn" && $aid !== false) {
    $ssn_idx = sanitize::validate_id("idx", sanitize::GET);
    if ($aid === false || $ssn_idx === false) {
        print_error("Invalid IDs specified");
        exit;
    }

    $analysis = new analysis($db, $aid, $is_example);
    validate_key($analysis, $key);

    $file_name = $analysis->get_file_name(file_types::FT_ssn, $ssn_idx);
    $file_path = $analysis->get_file_path(file_types::FT_ssn, $ssn_idx);
    if ($file_name === false || $file_path === false) {
        print_error("Unable to find files for $ssn_idx");
        exit;
    }

} else if ($dl_type == "convratio") {
    $obj = new conv_ratio($db, $id, $is_example);
    validate_key($obj, $key);

    $file_path = "";
    $file_name = "";
    $mime_type = send_file::SEND_FILE_BINARY;
    if ($sub_type == file_types::FT_cr_table) {
        $file_path = $obj->get_file_path(file_types::FT_cr_table);
        $file_name = $obj->get_file_name(file_types::FT_cr_table);
        $mime_type = send_file::SEND_FILE_TABLE;
    } else {
        print_error();
        exit();
    }

} else if ($dl_type == "nbconn") {
    $obj = new nb_conn($db, $id, $is_example);
    validate_key($obj, $key);

    $mime_type = send_file::SEND_FILE_BINARY;
    $file_path = $obj->get_file_path($sub_type);
    $file_name = $obj->get_file_name($sub_type);

    if ($sub_type == file_types::FT_nc_legend) {
        $mime_type = send_file::SEND_FILE_PNG;
    } else if ($sub_type == file_types::FT_nc_table) {
        $mime_type = send_file::SEND_FILE_TABLE;
    } else if ($sub_type != file_types::FT_ssn) {
        print_error();
        exit();
    }

} else if ($dl_type == file_types::FT_blast_evalue) {
    $analysis_id = sanitize::validate_key("analysis_id", sanitize::GET);

    $analysis = new analysis($db, $analysis_id, $is_example);
    validate_key($analysis, $key);

    $file_name = $analysis->get_file_name(file_types::FT_blast_evalue);
    $file_path = $analysis->get_file_path(file_types::FT_blast_evalue);
    $mime_type = send_file::SEND_FILE_TABLE;

} else if ($dl_type == "colorssn" || $dl_type == "ca") {
    if ($dl_type == "colorssn") {
        $obj = new colorssn($db, $id, $is_example);
    } else {
        $obj = new cluster_analysis($db, $id, $is_example);
    }
    validate_key($obj, $key);

    $shared_types = array(
        file_types::FT_ssn => 1, file_types::FT_mapping => 1, file_types::FT_mapping_dom => 1,
        file_types::FT_ids => 1, file_types::FT_dom_ids => 1, file_types::FT_ur90_ids => 1, file_types::FT_ur90_dom_ids => 1, file_types::FT_ur50_ids => 1, file_types::FT_ur50_dom_ids => 1,
        file_types::FT_fasta => 1, file_types::FT_fasta_dom => 1, file_types::FT_fasta_ur90 => 1, file_types::FT_fasta_dom_ur90 => 1, file_types::FT_fasta_ur50 => 1, file_types::FT_fasta_dom_ur50 => 1,
        file_types::FT_sizes => 1, file_types::FT_conv_ratio => 1, file_types::FT_sp_clusters => 1, file_types::FT_sp_singletons => 1,
    );

    if (isset($shared_types[$sub_type])) {
        list($mime_type, $file_path, $file_name) = process_colorssn($obj, $sub_type);
    } else if ($dl_type == "ca") {
        list($mime_type, $file_path, $file_name) = process_cluster_analysis($obj, $sub_type);
    }

} else if ($dl_type == "ca") {
    validate_key($obj, $key);

    list($mime_type, $file_path, $file_name) = process_cluster_analysis($obj, $sub_type);
} else {
    print_error();
    exit();
}


output_file($file_path, $file_name, $mime_type);



function output_file($file_path, $file_name, $mime_type) {
    if (!download::output_file($file_path, $file_name, $mime_type)) {
        print_error();
        exit();
    }
}

function validate_key($obj, $key) {
    if (!download::validate_key($obj, $key)) {
        print_error("efi-est/download.php: Invalid key $key");
        exit();
    }
}

function print_error($error = "") {
    echo "Error";
    if ($error)
        error_log("efi-est/download.php: $error");
}



function process_colorssn($obj, $ft) {
    $file_path = "";
    $file_name = "";
    $mime_type = send_file::SEND_FILE_BINARY;

    if (!$ft) {
    } else {
        $file_path = $obj->get_file_path($ft);
        $file_name = $obj->get_file_name($ft);
    }

    return array($mime_type, $file_path, $file_name);
}


function process_cluster_analysis($obj, $ft) {
    $file_path = "";
    $file_name = "";
    $mime_type = send_file::SEND_FILE_BINARY;

    if ($ft == file_types::FT_cons_res_pos) {
        $aa = sanitize::get_sanitize_string("aa", "");
        $file_path = $obj->get_cons_res_file_path($ft, $aa);
        $file_name = $obj->get_cons_res_file_name($ft, $aa);
        $mime_type = send_file::SEND_FILE_TABLE;
    } else {
        $file_path = $obj->get_file_path($ft);
        $file_name = $obj->get_file_name($ft);
    }

    return array($mime_type, $file_path, $file_name);
}


