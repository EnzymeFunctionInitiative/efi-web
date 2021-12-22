<?php
namespace efi\users;

require_once(__DIR__."/../../../init.php");

use \efi\global_settings;


class functions {

    public static function send_confirmation_email($to, $user_token, $set_password = false) {
        $subject = "EFI Tools Account Email Verification";
        $from = global_settings::get_admin_email();
        $from_name = "EFI-Tools";
    
        $body = "An account for the EFI Tools website was requested using this email address. If you did not request an account ";
        $body .= "then please ignore this email." . PHP_EOL . PHP_EOL;
        $body .= "Click the link below to activate your account. If there is no link, then copy the address into ";
        $body .= "a web browser address bar." . PHP_EOL . PHP_EOL;
        $body .= "THE_URL";

        $set_password_arg = $set_password ? "sp=1" : "";
        $the_url = global_settings::get_base_web_root() . "/user_account.php?action=confirm&token=$user_token&$set_password_arg";
    
        $plain_body = str_replace("THE_URL", $the_url, $body);
        $html_body = nl2br($body, false);
        $html_body = str_replace("THE_URL", "<a href=\"$the_url\">$the_url</a>", $html_body);

        \efi\email::send_email($to, $from, $subject, $plain_body, $html_body, $from_name);
    }

    public static function send_reset_email($to, $user_token, $warnUser = true) {
        $subject = "EFI Tools Reset Password";
        $from = global_settings::get_admin_email();
        $from_name = "EFI-Tools";

        if ($warnUser) {
            $body = "A password reset request was received for this email address.  If you did not request a password ";
            $body .= "reset then please ignore this email." . PHP_EOL . PHP_EOL;
        } else {
            $body = "An administrator has requested a password reset."  . PHP_EOL . PHP_EOL;
        }
        $body .= "Click the link below to set a new password. If there is no link, then copy the address into ";
        $body .= "a web browser address bar." . PHP_EOL . PHP_EOL;
        $body .= "THE_URL";
    
        $the_url = global_settings::get_base_web_root() . "/user_account.php?action=reset&reset-token=$user_token";
    
        $plain_body = str_replace("THE_URL", $the_url, $body);
        $html_body = nl2br($body, false);
        $html_body = str_replace("THE_URL", "<a href=\"$the_url\">$the_url</a>", $html_body);
    
        \efi\email::send_email($to, $from, $subject, $plain_body, $html_body, $from_name);
    }

}

