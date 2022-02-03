<?php 
require_once(__DIR__."/../../init.php");

use \efi\est\stepa;

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die();
}

$job = new stepa($db, $_GET["id"], $is_example);
$job_id = $generate->get_id();
$job_key = $_GET["key"];

if ($generate->get_key() != $_GET["key"]) {
    die();
}

$gen_type = $job->get_type();
if ($gen_type != "TAXONOMY") {
    die();
}

$has_tax_data = $job->has_tax_data();


echo $job->get_raw_taxonomy_data();

