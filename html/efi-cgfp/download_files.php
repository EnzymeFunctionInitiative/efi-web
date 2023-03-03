<?php
require_once(__DIR__."/../../init.php");

use \efi\cgfp\settings;
use \efi\cgfp\identify;
use \efi\cgfp\quantify;
use \efi\cgfp\quantify_example;
use \efi\training\example_config;
use \efi\sanitize;
use \efi\file_types;
use \efi\send_file;


$is_error = true;
$is_expired = false;
$the_id = "";
$q_id = "";
$job = NULL;

// There are two types of examples: dynamic and static.  The static example is a curated
// example pulled into the entry screen.  The dynamic examples are the same as other
// jobs, except they are stored in separate directories/tables.
$is_example = example_config::is_example();
$is_static_example = sanitize::get_sanitize_string("example");

$the_id = sanitize::validate_id("id", sanitize::GET);
$key = sanitize::validate_key("key", sanitize::GET);
$q_id = sanitize::validate_id("quantify-id", sanitize::GET);
$type = sanitize::get_sanitize_string("type", "");

if ($is_static_example) {
    $example_dir = settings::get_example_dir();
    $example_web_path = settings::get_example_web_path();
    if (file_exists($example_dir) && $example_web_path) {
        $job = new quantify_example($db, $example_dir, $example_web_path);
        $is_error = false;
    }
    $q_id = isset($_GET["identify"]) ? 0 : 1;
} elseif ($the_id !== false && $key !== false) {
    if ($q_id !== false) {
        $job = new quantify($db, $q_id, $is_example);
    } else {
        $job = new identify($db, $the_id, $is_example);
    }

    if ($key === false || $job->get_key() != $key) {
        $is_error = true;
    } elseif (!$is_example && $job->is_expired()) {
        $is_error = true;
        $is_expired = true;
    } else {
        $is_error = false;
    }
}

if ($is_error) {
    error404();
}

if ($type !== false) {
    $prefix = "${the_id}_${q_id}_";
    if ($is_example)
        $prefix = "";

    $is_valid_type = $job->get_is_valid_type($type);

    if ($is_example)
        $rel_out_dir = settings::get_rel_example_http_output_dir() . "/$is_example/cgfp";
    else
        $rel_out_dir = settings::get_rel_http_output_dir();

    if ($type == file_types::FT_sbq_meta_info) {
        $file_name = $job->get_file_name(file_types::FT_sbq_meta_info);
        $text_data = $job->get_metagenome_info_as_text();
        $is_error = send_file::send_text($text_data, $file_name);
    } else if ($is_valid_type) {
        $file_path = $job->get_file_path($type);
        $file_name = $job->get_file_name($type);
        if ($file_path !== false) {
            $is_error = ! send_file::send($file_path, $file_name);
        } else {
            error_log("Unable to send ft=$type, file path not found (fn=$file_name)");
            $is_error = true;
        }
    } else {
        error_log("Invalid download type given ft=$type");
        $is_error = true;
    }
}

if ($is_error) {
    error404();
}






