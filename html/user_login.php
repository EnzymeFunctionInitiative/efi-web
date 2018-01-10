<?php
require_once '../includes/main.inc.php';
require_once '../libs/user_auth.class.inc.php';
require_once '../includes/PasswordHash.php';
require_once '../includes/pear/Mail.php';
require_once '../includes/pear/Mail/mime.php';


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
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $valid = user_auth::validate_user($db, $_POST['email'], $_POST['password']);
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
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $listSignup = isset($_POST['mailinglist']) && $_POST['mailinglist'] == "1";
        $createResult = user_auth::create_user($db, $_POST['email'], $_POST['password'], $listSignup); // returns false if invalid, otherwise returns the user_id token
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
    if (isset($_POST["reset_token"]) && isset($_POST["password"])) {
        $valid = user_auth::reset_password($db, $_POST["reset_token"], $_POST["password"]);
        if ($valid) {
            $result['valid'] = true;
        } else {
            $result['message'] = "Invalid request.";
        }
    } else {
        $result['message'] = "Invalid parameters.";
    }
} elseif ($action == "send-reset") {
    if (isset($_POST["email"])) {
        $userToken = user_auth::check_reset_email($db, $_POST["email"]);
        if ($userToken) {
            sendResetEmail($_POST["email"], $userToken);
        } // Don't handle the case when the user email doesn't exist; we don't want to notify the user that the email address isn't invalid in case it's an attacker.
        $result['valid'] = true;
    } else {
        $result['message'] = "Invalid parameters.";
    }
} elseif ($action == "change") {
    if (isset($_POST["email"]) && isset($_POST["old-password"]) && isset($_POST["password"])) {
        $valid = user_auth::change_password($_POST["email"], $_POST["old-password"], $_POST["password"]);
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
    $from = "EFI-Tools <" . global_settings::get_admin_email() . ">";

    $body = "A password reset request was received for this email address.  If you did not request a password ";
    $body .= "reset then please ignore this email." . PHP_EOL . PHP_EOL;
    $body .= "Click the link below to set a new password. If there is no link, then copy the address into ";
    $body .= "a web browser address bar." . PHP_EOL . PHP_EOL;
    $body .= "THE_URL";

    $theUrl = global_settings::get_web_root() . "/user_account.php?action=reset&reset-token=$userToken";

    $plainBody = str_replace("THE_URL", $theUrl, $body);
    $htmlBody = nl2br($body, false);
    $htmlBody = str_replace("THE_URL", "<a href=\"$theUrl\">$theUrl</a>", $htmlBody);

    $message = new Mail_mime(array("eol" => PHP_EOL));
    $message->setTXTBody($plainBody);
    $message->setHTMLBody($htmlBody);
    $body = $message->get();
    $extraHeaders = array("From" => $from, "Subject" => $subject);
    $headers = $message->headers($extraHeaders);

    $mail = Mail::factory("mail");
    $mail->send($email, $headers, $body);
    unset($mail);
    unset($message);
}


function sendConfirmationEmail($email, $userToken) {
    $subject = "EFI Tools Account Email Verification";
    $from = "EFI-Tools <" . global_settings::get_admin_email() . ">";

    $body = "An account for the EFI Tools website was requested using this email address. If you did not request an account ";
    $body .= "then please ignore this email." . PHP_EOL . PHP_EOL;
    $body .= "Click the link below to activate your account. If there is no link, then copy the address into ";
    $body .= "a web browser address bar." . PHP_EOL . PHP_EOL;
    $body .= "THE_URL";

    $theUrl = global_settings::get_web_root() . "/user_account.php?action=confirm&token=$userToken";

    $plainBody = str_replace("THE_URL", $theUrl, $body);
    $htmlBody = nl2br($body, false);
    $htmlBody = str_replace("THE_URL", "<a href=\"$theUrl\">$theUrl</a>", $htmlBody);

    $message = new Mail_mime(array("eol" => PHP_EOL));
    $message->setTXTBody($plainBody);
    $message->setHTMLBody($htmlBody);
    $body = $message->get();
    $extraHeaders = array("From" => $from, "Subject" => $subject);
    $headers = $message->headers($extraHeaders);

    $mail = Mail::factory("mail");
    $mail->send($email, $headers, $body);
    unset($mail);
    unset($message);
}


?>


