<?php
namespace efi;

require_once(__DIR__."/../../init.php");


class email {

    public static function send_email($to, $from, $subject, $plain_email, $html_email, $from_name = "") {
        $smtp_host = global_settings::get_smtp_host();
        $smtp_port = global_settings::get_smtp_port();

        $mail = new \IGBIllinois\email($smtp_host, $smtp_port);
        $mail->set_to_emails($to);
        $mail->send_email($from, $subject, $plain_email, $html_email, $from_name);
    }
}


