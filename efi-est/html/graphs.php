<?php

require_once("../includes/main.inc.php");

if (isset($_GET["type"])) {
    $type = $_GET["type"];
    $is_example = isset($_GET["x"]);
    $stepa = new stepa($db, $_GET["id"], $is_example);
    if ($stepa->get_key() != $_GET["key"]) {
        echo "No EFI-EST Selected. Please go back2";
        exit;
    }
    $stepa->download_graph($type);
} else {
    echo "No EFI-EST Selected. Please go back";
    exit;
}



?>
