<?php
require_once(__DIR__."/../../init.php");

use \efi\est\functions;
use \efi\est\stepa;
use \efi\training\example_config;


if (isset($_GET["type"])) {
    $type = $_GET["type"];
    $is_example = example_config::is_example();
    $stepa = new stepa($db, $_GET["id"], $is_example);
    if ($stepa->get_key() != $_GET["key"]) {
        echo "No EFI-EST Selected. Please go back";
        exit;
    }
    $stepa->download_graph($type);
} else {
    echo "No EFI-EST Selected. Please go back";
    exit;
}


