<?php
require_once("../includes/main.inc.php");


$result = array("valid" => false, "message" => "");

if (!isset($_POST["action"])) {
    echo json_encode($result);
    exit(0);
}

$action = $_POST["action"];
if ($action == "new") {
    $email = "";
    $pass1 = "";
    $pass2 = "";
    $group = "";

    if (isset($_POST["email"]))
        $email = $_POST["email"];
    if (isset($_POST["password"]))
        $pass1 = $_POST["password"];
    if (isset($_POST["password-confirm"]))
        $pass2 = $_POST["password-confirm"];
    if (isset($_POST["group"]))
        $group = $_POST["group"];

    $pass_is_valid = true;
    if ($pass1 != $pass2) {
        $result["message"] .= " Passwords don't match.";
        $pass_is_valid = false;
        $result["valid"] = false;
    }

    if ($pass_is_valid) {
        // Validation occurs in create_user
        $create_result = user_manager::create_user($db, $email, $pass1, $group);
        $is_valid = $create_result == user_manager::CREATE_USER_OK;
        if ($create_result & user_manager::CREATE_USER_INVALID_EMAIL) {
            $result["message"] .= " The email address is invalid.";
        }
        if ($create_result & user_manager::CREATE_USER_EXISTS) {
            $result["message"] .= " That account already exists.";
        }
        if ($create_result & user_manager::CREATE_USER_INVALID_GROUP) {
            $result["message"] .= " The group name is invalid or doesn't exist.";
        }
        $result["valid"] = $is_valid;
    }
} elseif ($action == "new-bulk") {
    $users = array();
    $text = $_POST["user-bulk"];
    $lines = preg_split("/\r\n|\n|\r/", $text);

    foreach ($lines as $line) {
        $line = trim($line);
        if (!$line)
            continue;

        $parts = preg_split("/\s+/", $line);
        if (count($parts) == 0)
            continue;

        $user = array();

        if (count($parts) > 0 && $parts[0])
            array_push($user, $parts[0]);
        
        if (count($parts) > 1 && $parts[0])
            array_push($user, $parts[1]);
        else
            array_push($user, "");
        
        if (count($user))
            array_push($users, $user);
    }

    $bulk_results = user_manager::bulk_create_update_user($db, $users);

    //TODO: check output
    $result["valid"] = true;
} elseif ($action == "update-group" || $action == "remove-group") {
    $user_ids = array();
    $user_ids_text = "";
    $user_group = "";

    if (isset($_POST["user-ids"]))
        $user_ids_text = $_POST["user-ids"];
    if (isset($_POST["group"]))
        $user_group = $_POST["group"];

    if ($user_group && $user_ids_text) {
        $user_ids = explode(",", $user_ids_text);
        if (count($user_ids)) {
            $update_result = false;
            if ($action == "update-group")
                $update_result = user_manager::update_user_group($db, $user_ids, $user_group, false);
            elseif ($action == "remove-group")
                $update_result = user_manager::update_user_group($db, $user_ids, $user_group, true);
            $result["valid"] = $update_result;
            if (!$update_result) {
                $result["message"] = "Unknown error occurred.";
            }
        }
    }
} elseif ($action == "reset-pass") {
    $user_ids = array();
    $user_ids_text = "";
    $user_group = "";

    if (isset($_POST["user-ids"]))
        $user_ids_text = $_POST["user-ids"];

    if ($user_ids_text) {
        $user_ids = explode(",", $user_ids_text);
        if (count($user_ids)) {
            $update_result = false;
            user_manager::reset_passwords($db, $user_ids);
            $result["valid"] = true;
            if (!$update_result) {
                $result["message"] = "Unknown error occurred.";
            }
        }
    }
} elseif ($action == "delete-user") {
    $user_ids = array();
    $user_ids_text = "";
    $user_group = "";

    if (isset($_POST["user-ids"]))
        $user_ids_text = $_POST["user-ids"];

    if ($user_ids_text) {
        $user_ids = explode(",", $user_ids_text);
        if (count($user_ids)) {
            $update_result = false;
            user_manager::delete_users($db, $user_ids);
            $result["valid"] = true;
            if (!$update_result) {
                $result["message"] = "Unknown error occurred.";
            }
        }
    }
}


echo json_encode($result);


?>
