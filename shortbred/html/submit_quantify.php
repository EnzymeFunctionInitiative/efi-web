<?php

require_once("../includes/main.inc.php");
require_once("../libs/job_manager.class.inc.php");
require_once("../libs/quantify.class.inc.php");

$id = 0;
$quantify_id = 0;
$key = 0;
$message = "";
$valid = 0;

if (isset($_POST["key"]) && isset($_POST["id"]) && isset($_POST["hmp-ids"])) {

    $key = $_POST["key"];
    $id = $_POST["id"];
    $hmp_ids = $_POST["hmp-ids"];
    $search_type = isset($_POST["search-type"]) ? $_POST["search-type"] : "";
    $dataset_type = isset($_POST["dataset-type"]) ? $_POST["dataset-type"] : "";
    $job_name = isset($_POST["job-name"]) ? $_POST["job-name"] : "";

    if ($hmp_ids) {
        $valid = 1;
    }

    if ($valid) {
        $job_mgr = new job_manager($db, job_types::Identify);
    
        if (!$job_mgr->validate_job($id, $key)) {
            $valid = 0;
            $message .= "<b>Invalid job</b>";
        }
    }

    if ($valid) {
        $new_info = quantify::create($db, $id, $hmp_ids, $search_type, $dataset_type, $job_name);
        if ($new_info === false) {
            $valid = 0;
        } else {
            $quantify_id = $new_info["id"];
        }
    }
}

$output = array(
    "valid" => $valid,
    "quantify_id" => $quantify_id,
    "message" => $message,
);

echo json_encode($output);

?>
