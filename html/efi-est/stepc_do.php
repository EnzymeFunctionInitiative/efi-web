<?php 
require_once(__DIR__."/../../init.php");

use \efi\ui;
use \efi\table_builder;
use \efi\global_settings;
use \efi\user_auth;

use \efi\est\stepa;
use \efi\est\analysis;
use \efi\est\dataset_shared;
use \efi\est\plots;
use \efi\est\functions;
use \efi\est\settings;
use \efi\training\example_config;


if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(array("valid" => false));
    exit;
}

$is_example = example_config::is_example();
$generate = new stepa($db, $_POST['id'], $is_example);
$gen_id = $generate->get_id();
$key = $_POST['key'];

$result = array("valid" => true, "message" => "");
if ($generate->get_key() != $_POST['key']) {
    $result["valid"] = false;
    $result["message"] = "Invalid identifier";
}
if (!$is_example && $generate->is_expired()) {
    $result["valid"] = false;
    $result["message"] = "Job is expired.";
}

if (!$result["valid"]) {
    echo json_encode($result);
    exit;
}




///////////////////////////////////////////////////////////////////////////////////////////////////
// This code handles submission of the form.
foreach ($_POST as $key => $var) {
    if (is_array($var)) {
        foreach ($var as $subkey => $subvar) {
            $val = trim(rtrim($subvar));
            $var[$subkey] = $val;
        }
    } else {
        $val = trim(rtrim($var));
        $_POST[$key] = $val;
    }
}

$min = $_POST['minimum'];
if ($_POST['minimum'] == "") {
    $min = __MINIMUM__;
}
$max = $_POST['maximum'];
if ($_POST['maximum'] == "") {
    $max = __MAXIMUM__;
}

$job_id = $_POST['id'];

$analysis = new analysis($db);

$customFile = "";
if (isset($_FILES['cluster_file']) && (!isset($_FILES['cluster_file']['error']) || $_FILES['cluster_file']['error'] == 0)) {
    $customFile = $_FILES['cluster_file']['tmp_name'];
}

$filter = "";
$filter_value = 0;
if (isset($_POST['filter'])) {
    $filter = $_POST['filter'];
    if ($filter == "eval") {
        $filter_value = $_POST['evalue'];
    } elseif ($filter == "pid") {
        $filter_value = $_POST['pid'];
    } elseif ($filter == "bit") {
        $filter_value = $_POST['bitscore'];
    } elseif ($filter != "custom") {
        $filter = "eval";
    }
} else {
    $filter = "eval";
    $filter_value = $_POST['evalue'];
}

if (user_auth::has_token_cookie()) {
    $email = user_auth::get_email_from_token($db, user_auth::get_user_token());
    if (functions::is_job_sticky($db, $job_id, $email)) {
        $parent_id = $job_id;
        $parent_check = functions::is_parented($db, $job_id, $email);

        if ($parent_check === false) {
            $job_id = stepa::duplicate_job($db, $job_id, $email);
            if ($job_id === false) {
                header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
                die("Unable to create duplicate of $parent_id for $email");
            }
        } else {
            $job_id = $parent_check;
        }
    }
}

$cdhitOpt = "";
if (functions::custom_clustering_enabled() && isset($_POST["cdhit-opt"])) {
    $opt = $_POST["cdhit-opt"];
    if ($opt == "sb" || $opt == "est" || $opt == "est+")
        $cdhitOpt = $opt;
}

$min_node_attr = isset($_POST['min_node_attr']) ? 1 : 0;
$min_edge_attr = isset($_POST['min_edge_attr']) ? 1 : 0;
$compute_nc = isset($_POST['compute_nc']) ? true : false;
$remove_fragments = isset($_POST['remove_fragments']) ? true : false;
$default_build_repnode = settings::get_create_repnode_networks();
$build_repnode = isset($_POST['build_repnode']) ? true : $default_build_repnode;

$tax_search = isset($_POST['tax_search']) ? $_POST['tax_search'] : false;
$tax_name = ($tax_search !== false && isset($_POST['tax_name']) && $_POST['tax_name']) ? $_POST['tax_name'] : "";

$job_result = $analysis->create(
    $job_id,
    $filter_value,
    $_POST['network_name'],
    $min,
    $max,
    $customFile,
    $cdhitOpt,
    $filter,
    $min_node_attr,
    $min_edge_attr,
    $compute_nc,
    $build_repnode,
    $tax_search,
    $tax_name,
    $remove_fragments
);


if ($job_result['RESULT']) {
    $result["valid"] = true;
} else {
    $result["valid"] = false;
    $result["message"] = $job_result['MESSAGE'];
}


echo json_encode($result);


