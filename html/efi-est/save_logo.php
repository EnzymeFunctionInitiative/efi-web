<?php 
require_once(__DIR__."/../../init.php");

use \efi\est\job_factory;
use \efi\training\example_config;
use \efi\send_file;
use \efi\sanitize;


$id = sanitize::validate_id("id", sanitize::GET);
$key = sanitize::validate_key("key", sanitize::GET);

if ($id === false || $key === false) {
    error_404();
    exit;
}


$is_example = example_config::is_example();
$obj = job_factory::create($db, $id, $is_example); // Creates a color SSN or a cluster analysis job

if ($key !== $obj->get_key()) {
    error_404();
    exit;
}


$logo = sanitize::get_sanitize_string("logo", "");
if (!$logo) {
    error_404();
    exit;
}


$type = sanitize::get_sanitize_string("t", "");

$gtype = "hmm";
if (isset($type)) {
    if ($type == "w")
        $gtype = "weblogo";
    else if ($type == "l")
        $gtype = "length";
    else if ($type == "afa")
        $gtype = "afa";
    else if ($type == "hmm-png")
        $gtype = "hmm_png";
    else if ($type == "hmm")
        $gtype = "hmm";
}


$mime_type = send_file::SEND_FILE_PNG;

$file_prefix = "";
$file_ext = ".png";
$format = "png";
if ($gtype == "hmm_png" || $gtype == "hmm") {
    $graphics = $obj->get_hmm_graphics();
    $file_prefix = "HMM_Cluster";
    if ($gtype == "hmm") {
        $file_ext = ".hmm";
        $format = "hmm";
        $mime_type = send_file::SEND_FILE_BINARY;
    }
} elseif ($gtype == "weblogo") {
    $graphics = $obj->get_weblogo_graphics();
    $file_prefix = "WebLogo_Cluster";
} elseif ($gtype == "length") {
    $graphics = $obj->get_lenhist_graphics();
    $file_prefix = "Length_Histogram";
} elseif ($gtype == "afa") {
    $graphics = $obj->get_alignment_list();
    $file_prefix = "Alignment";
    $file_ext = "";
    $format = "afa";
    $mime_type = send_file::SEND_FILE_BINARY;
}


list($cluster, $seq_type, $quality) = explode("-", $logo);
if (!isset($graphics[$cluster][$seq_type][$quality])) {
    die("$cluster $seq_type $quality");
    exit;
}

// This adds the SSN name on to the front
$filename = $obj->get_output_file_name("${file_prefix}_${cluster}_${seq_type}_${quality}.$format");
$full_path = $obj->get_hmm_file_full_path($graphics[$cluster][$seq_type][$quality]["path"] . $file_ext);

send_file::send($full_path, $filename, $mime_type);


