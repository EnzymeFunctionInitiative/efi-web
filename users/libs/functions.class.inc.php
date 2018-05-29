<?php

require_once(__DIR__."/../includes/main.inc.php");
require_once(__DIR__."/../../libs/global_settings.class.inc.php");
require_once(__DIR__."/../../includes/pear/Mail.php");
require_once(__DIR__."/../../includes/pear/Mail/mime.php");

class functions {

    public static function send_confirmation_email($email, $userToken, $setPassword = false) {
        $subject = "EFI Tools Account Email Verification";
        $from = "EFI-Tools <" . global_settings::get_admin_email() . ">";
    
        $body = "An account for the EFI Tools website was requested using this email address. If you did not request an account ";
        $body .= "then please ignore this email." . PHP_EOL . PHP_EOL;
        $body .= "Click the link below to activate your account. If there is no link, then copy the address into ";
        $body .= "a web browser address bar." . PHP_EOL . PHP_EOL;
        $body .= "THE_URL";

        $setPasswordArg = $setPassword ? "sp=1" : "";
        $theUrl = global_settings::get_base_web_root() . "/user_account.php?action=confirm&token=$userToken&$setPasswordArg";
    
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

    public static function send_reset_email($email, $userToken, $warnUser = true) {
        $subject = "EFI Tools Reset Password";
        $from = "EFI-Tools <" . global_settings::get_admin_email() . ">";

        if ($warnUser) {
            $body = "A password reset request was received for this email address.  If you did not request a password ";
            $body .= "reset then please ignore this email." . PHP_EOL . PHP_EOL;
        } else {
            $body = "An administrator has requested a password reset."  . PHP_EOL . PHP_EOL;
        }
        $body .= "Click the link below to set a new password. If there is no link, then copy the address into ";
        $body .= "a web browser address bar." . PHP_EOL . PHP_EOL;
        $body .= "THE_URL";
    
        $theUrl = global_settings::get_base_web_root() . "/user_account.php?action=reset&reset-token=$userToken";
    
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

}

?>
