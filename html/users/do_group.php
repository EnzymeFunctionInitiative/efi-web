<?php
require_once(__DIR__."/../../init.php");

use \efi\users\user_manager;


$result = array("valid" => false, "message" => "");

if (!isset($_POST["action"])) {
    echo json_encode($result);
    exit(0);
}



$action = $_POST["action"];
if ($action == "new") {
    $g_name = "";
    $g_open = "";
    $g_closed = "";

    if (isset($_POST["name"]))
        $g_name = $_POST["name"];
    if (isset($_POST["open"]))
        $g_open = $_POST["open"];
    if (isset($_POST["closed"]))
        $g_closed = $_POST["closed"];

    $g_name = preg_replace("/[^A-Za-z0-9\-_]/", "", $g_name);

    if ($g_name) {
        $create_result = user_manager::create_group($db, $g_name, $g_open, $g_closed);
        $result["valid"] = $create_result;
        if (!$create_result)
            $result["message"] = "That name already exists.";
    } else {
        $result["message"] = "Invalid name.";
    }
} elseif ($action == "toggle") {
    $group = "";

    if (isset($_POST["group"]))
        $group = $_POST["group"];

    if ($group) {
        $toggle_result = user_manager::toggle_group_status($db, $group);
        $result["valid"] = $toggle_result;
        if (!$toggle_result)
            $result["message"] = "Invalid group.";
    } else {
        $result["message"] = "Invalid group.";
    }
}


echo json_encode($result);


