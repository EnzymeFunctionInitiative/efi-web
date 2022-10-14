<?php
require_once(__DIR__."/../../init.php");

use \efi\global_functions;
use \efi\send_file;
use \efi\est\nb_conn;
use \efi\est\conv_ratio;
use \efi\est\stepa;
use \efi\est\analysis;



$dl_type = isset($_GET["dl"]) ? $_GET["dl"] : "";
$dl_type = validate_download_type($dl_type);
$id = (isset($_GET["id"]) && is_numeric($_GET["id"])) ? $_GET["id"] : false;
$key = (isset($_GET["key"]) && !preg_match("/[^A-Za-z0-9]/", $_GET["key"])) ? $_GET["key"] : false;

if (!$dl_type || !$id || !$key) {
    print_error();
    exit();
}


if (isset($_POST['download_network'])) {
	$analysis_id = $_POST['analysis_id'];
	$stepa = new stepa($db,$_POST['id']);
    if ($stepa->get_key() != $_POST['key']) {
        print_error();
        exit;
    }

    if ($dl_type === "BLASTHITS") {
        $analysis = new analysis($db,$analysis_id);
        $file_info = $analysis->get_blast_evalue_file();
        output_file($file_info["path"], $file_info["name"], SEND_FILE_TABLE);
    } else {
        $file = $_POST['file'];
        if (global_functions::is_safe_filename($file)) {
            $analysis = new analysis($db,$analysis_id);
        	$analysis->download_network($file);
        } else {
            print_error();
            exit();
        }
    }
} else if ($dl_type == "CONVRATIO") {
    $obj = new conv_ratio($db, $id);
    validate_key($obj, $key);

    $file_path = "";
    $file_name = "";
    $mime_type = SEND_FILE_BINARY;
    $sub_type = isset($_GET["ft"]) ? $_GET["ft"] : "";
    if ($sub_type === "tab") {
        $file_path = $obj->get_cr_table_full_path();
        $file_name = $obj->get_cr_table_filename();
        $mime_type = SEND_FILE_TABLE;
    }

    output_file($file_path, $file_name, $mime_type);
} else if ($dl_type === "NBCONN") {
    $obj = new nb_conn($db, $id);
    validate_key($obj, $key);

    $file_path = "";
    $file_name = "";
    $mime_type = SEND_FILE_BINARY;
    $sub_type = isset($_GET["ft"]) ? $_GET["ft"] : "";
    if (!$sub_type || $sub_type === "ssn") {
        $file_path = $obj->get_colored_ssn_zip_full_path();
        $file_name = $obj->get_colored_ssn_zip_filename();
        //$mime_type = SEND_FILE_ZIP;
    } else if ($sub_type === "legend") {
        $file_path = $obj->get_nc_legend_full_path();
        $file_name = $obj->get_nc_legend_filename();
        $mime_type = SEND_FILE_PNG;
    } else if ($sub_type === "nc") {
        $file_path = $obj->get_nc_table_full_path();
        $file_name = $obj->get_nc_table_filename();
        $mime_type = SEND_FILE_TABLE;
    }

    output_file($file_path, $file_name, $mime_type);
} else if ($dl_type === "BLASTHITS") {
    $analysis_id = $_GET['analysis_id'];
	$stepa = new stepa($db, $id);
    validate_key($stepa, $key);

    $analysis = new analysis($db, $analysis_id);
    $file_info = $analysis->get_blast_evalue_file();
    output_file($file_info["full_path"], $file_info["name"], SEND_FILE_TABLE);
} else {
    print_error();
    exit();
}




function output_file($file_path, $file_name, $mime_type) {
    if ($file_path && filesize($file_path)) {
        send_file::send($file_path, $file_name, $mime_type);
    } else {
        print_error();
        exit();
    }
}

function validate_download_type($type) {
    if ($type === "NBCONN" || $type === "CONVRATIO" || $type === "BLASTHITS")
        return $type;
    else
        return false;
}

function validate_key($obj, $key) {
    if ($obj->get_key() !== $key) {
        print_error();
        exit();
    } else {
        return true;
    }
}

function print_error() {
    echo "No EFI-EST Selected. Please go back";
}


