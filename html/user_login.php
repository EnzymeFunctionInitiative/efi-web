<?php
require_once(__DIR__."/../init.php");

require_once(__CONF_DIR__."/settings.inc.php");

use \efi\global_settings;
use \efi\user_auth;



$result = array('valid' => false, 'message' => "", 'cookieInfo' => "");

//TODO: check email address to validate it
//TODO: sanitize input to prevent SQL injection attack

$action = "";
if (!isset($_POST['action'])) {
    if (!isset($_GET['action'])) {
        $result['message'] = "Invalid operation.";
        echo json_encode($result);
        exit(0);
    } else {
        $action = $_GET['action'];
    }
} else {
    $action = $_POST['action'];
}


if ($action == "login") {
    $email = false;
    $password = false;
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'];
    }
    if ($email !== false && $password !== false) {
        $valid = user_auth::validate_user($db, $email, $password);
        if ($valid['valid'] && $valid['cookie']) {
            $result['valid'] = true;
            $result['cookieInfo'] = $valid['cookie'];
        } else {
            $result['message'] = "Invalid password.";
        }
    } else {
        $result['message'] = "Invalid parameters.";
    }
} elseif ($action == "create") {
    $email = false;
    $password = false;
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'];
    }
    if ($email !== false && $password !== false) {
        $listSignup = isset($_POST['mailinglist']) && $_POST['mailinglist'] == "1";
        $createResult = user_auth::create_user($db, $email, $password, $listSignup); // returns false if invalid, otherwise returns the user_id token
        if ($createResult) {
            $result['valid'] = true;
            sendConfirmationEmail($_POST["email"], $createResult);
        } else {
            $result['message'] = "The email address already exists.";
        }
    } else {
        $result['message'] = "Invalid parameters.";
    }
} elseif ($action == "reset") {
    $user_token = false;
    $password = false;
    if (isset($_POST["reset_token"]) && isset($_POST["password"])) {
        $user_token = filter_var($_POST["reset_token"], FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^[a-zA-Z0-9]+$/")));
        $password = $_POST["password"];
    }
    if ($user_token !== false && $password !== false) {
        $valid = user_auth::reset_password($db, $user_token, $password);
        if ($valid) {
            $result['valid'] = true;
        } else {
            $result['message'] = "Invalid request.";
        }
    } else {
        $result['message'] = "Invalid parameters.";
    }
} elseif ($action == "send-reset") {
    $email = false;
    if (isset($_POST["email"])) {
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    }
    if ($email !== false) {
        $userToken = user_auth::check_reset_email($db, $email);
        if ($userToken) {
            sendResetEmail($email, $userToken);
        } // Don't handle the case when the user email doesn't exist; we don't want to notify the user that the email address isn't invalid in case it's an attacker.
        $result['valid'] = true;
    } else {
        $result['message'] = "Invalid parameters.";
    }
} elseif ($action == "change") {
    $email = false;
    $password = false;
    $ols_password = false;
    if (isset($_POST["email"]) && isset($_POST["old-password"]) && isset($_POST["password"])) {
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'];
        $old_password = $_POST['old-password'];
    }
    if ($email !== false && $password !== false && $old_password !== false) {
        $valid = user_auth::change_password($email, $old_password, $password);
        if ($valid) {
            $result['valid'] = true;
        } else {
            $result['message'] = "Invalid password.";
        }
    } else {
        $result['message'] = "Invalid parameters.";
    }
} elseif ($action == "logout") {
    $result['valid'] = true;
    $result['cookieInfo'] = user_auth::get_logout_cookie();
} else {
    $result['message'] = "Invalid operation.";
}

echo json_encode($result);



function sendResetEmail($email, $userToken) {
    $subject = "EFI Tools Reset Password";
    $from = global_settings::get_admin_email();
    $from_name = "EFI-Tools";
    $to = $email;

    $body = "A password reset request was received for this email address.  If you did not request a password ";
    $body .= "reset then please ignore this email." . PHP_EOL . PHP_EOL;
    $body .= "Click the link below to set a new password. If there is no link, then copy the address into ";
    $body .= "a web browser address bar." . PHP_EOL . PHP_EOL;
    $body .= "THE_URL";

    $theUrl = global_settings::get_web_root() . "/user_account.php?action=reset&reset-token=$userToken";

    $plainBody = str_replace("THE_URL", $theUrl, $body);
    $htmlBody = nl2br($body, false);
    $htmlBody = str_replace("THE_URL", "<a href=\"$theUrl\">$theUrl</a>", $htmlBody);

    \efi\email::send_email($to, $from, $subject, $plainBody, $htmlBody, $from_name);
}


function sendConfirmationEmail($email, $userToken) {
    $subject = "EFI Tools Account Email Verification";
    $from = global_settings::get_admin_email();
    $from_name = "EFI-Tools";
    $to = $email;

    $body = "An account for the EFI Tools website was requested using this email address. If you did not request an account ";
    $body .= "then please ignore this email." . PHP_EOL . PHP_EOL;
    $body .= "Click the link below to activate your account. If there is no link, then copy the address into ";
    $body .= "a web browser address bar." . PHP_EOL . PHP_EOL;
    $body .= "THE_URL";

    $theUrl = global_settings::get_web_root() . "/user_account.php?action=confirm&token=$userToken";

    $plainBody = str_replace("THE_URL", $theUrl, $body);
    $htmlBody = nl2br($body, false);
    $htmlBody = str_replace("THE_URL", "<a href=\"$theUrl\">$theUrl</a>", $htmlBody);

    \efi\email::send_email($to, $from, $subject, $plainBody, $htmlBody, $from_name);
}



