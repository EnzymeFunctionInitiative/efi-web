<?php
require_once(__DIR__."/../../init.php");

use \efi\gnt\functions;
use \efi\gnt\gnn;
use \efi\sanitize;


// If this is being run from the command line then we parse the command line parameters and put them into _POST so we can use
// that below.
$debug = !isset($_SERVER["HTTP_HOST"]);
if ($debug) {
    $num_args = count($argv);
    $arg_string = "";
    for ($i = 1; $i < $num_args; $i++) {
        if ($i > 1)
            $arg_string .= "&";
        $arg_string .= $argv[$i];
    }
    parse_str($arg_string, $_GET);
}


$show_error = false;
$status = "";
$files = array();

$gnn_id = sanitize::validate_id("id", sanitize::GET);
$key = sanitize::validate_key("key", sanitize::GET);

if ($gnn_id === false || $key === false) {
    $show_error = true;
} else {
    $sql = "SELECT gnn_status FROM gnn WHERE gnn_id = '$gnn_id' AND gnn_key = '$key'";
    $status_result = $db->query($sql);
    if (!$status_result) {
        $show_error = true;
    } else {
        $job_status = $status_result[0]["gnn_status"];
        if ($job_status == __FAILED__) {
            $status = __FAILED__;
        } else if ($job_status == __NEW__) {
            $status = __NEW__;
        } else if ($job_status == __RUNNING__) {
            $status = __RUNNING__;
        } else {
            $status = __FINISH__;
            $gnn = new gnn($db, $gnn_id);
            $files = functions::dump_gnn_info($gnn);
        }
    }
}


$json = array("status" => $status, "valid" => !$show_error, "files" => $files);

echo json_encode($json);


