<?php
namespace efi\est;

require_once(__DIR__."/../../../init.php");

use \efi\global_settings;
use \efi\global_functions;


abstract class est_shared {

    private $table;
    private $db;

    protected $email;
    protected $eol = PHP_EOL;
    protected $beta;
    protected $is_sticky = false;
    protected $is_example = false;

    function __construct($db, $table, $is_example) {
        $this->db = $db;
        $this->table = $table;
        $this->beta = global_settings::get_release_status();
        $this->is_example = $is_example;
    }


    public function get_table_name() { return $this->table; }
    
    public function get_email() { return $this->email; }
    public function set_email($email) { $this->email = $email; }

    public abstract function get_key();
    public abstract function get_id();
    protected abstract function get_generate_results_script();

    public abstract function process_error();
    public abstract function process_start();
    public abstract function process_finish();


    protected abstract function get_email_job_info();
    protected abstract function get_email_started_subject();
    protected abstract function get_email_started_body();
    protected abstract function get_email_completion_subject();
    protected abstract function get_email_completion_body();
    protected abstract function get_email_cancelled_subject();
    protected abstract function get_email_cancelled_body();

    protected abstract function get_job_status_obj();


    public function email_started() {
        $subject = $this->beta . $this->get_email_started_subject();
        $body_data = $this->get_email_started_body();
        $this->send_email($subject, $body_data);
    }

    public function email_complete() {
        $subject = $this->beta . $this->get_email_completion_subject();
        $body_data = $this->get_email_completion_body();
        $extra_footer = "These data will only be retained for " . global_settings::get_retention_days() . " days." . PHP_EOL . PHP_EOL;
        $this->send_email($subject, $body_data, "", $extra_footer);
    }

    protected function email_cancelled() {
        $subject = $this->beta . $this->get_email_cancelled_subject();
        $body_data = $this->get_email_cancelled_body();
        $this->send_email($subject, $body_data);
    }

    public function email_error_admin() {
        $to = global_settings::get_error_admin_email();
        if (!$to)
            return;

        $subject = $this->beta . "EFI-EST - Pipeline error";
        $from = "EFI-EST <" . global_settings::get_admin_email() . ">";

        $body_data = "There was an error in the pipeline and the job failed before completing." . PHP_EOL;
        $this->send_email($subject, $body_data, $to);
    }

    public function email_error($subject, $body_data) {
        $subject = $this->beta . $subject;
        $this->send_email($subject, $body_data);
    }

    private function send_email($subject, $body_data, $to = "", $extra_footer = "") {

        $body = is_array($body_data) ? $body_data["body"] : $body_data;
        $full_url = is_array($body_data) ? $body_data["url"] : "";

        $plain_email = "";
        if ($this->beta)
            $plain_email = "Thank you for using the EFI " . $this->beta . " site." . PHP_EOL . $plain_email;
        $plain_email .= $body;
        $plain_email .= "Submission Summary:" . PHP_EOL . PHP_EOL;
        $plain_email .= $this->get_email_job_info() . PHP_EOL . PHP_EOL;
        $plain_email .= $extra_footer;
        $plain_email .= global_settings::get_email_footer();

        $html_email = nl2br($plain_email, false);
        
        if (!$to)
            $to = $this->get_email();
        $from = global_settings::get_admin_email();
        $from_name = "EFI EST";

        if (!$to || !$from) {
            print("Missing email address $to | $from\n");
            return;
        }

        if ($full_url) {
            if (!is_array($full_url))
                $full_url = array("THE_URL" => $full_url);
            foreach ($full_url as $url_key => $url) {
                $plain_email = str_replace($url_key, $url, $plain_email);
                $html_email = str_replace($url_key, "<a href=\"" . htmlentities($url) . "\">" . $url. "</a>", $html_email);
            }
        }

        \efi\email::send_email($to, $from, $subject, $plain_email, $html_email, $from_name);
    }
    
    public function is_expired() {
        if (!$this->is_example && !$this->is_sticky && $this->get_job_status_obj()->is_expired()) {
            return true;
        } else {
            return false;
        }
    }

    public function get_time_completed() {
        return $this->get_job_status_obj()->get_time_completed();
    }
    public function get_time_completed_formatted() {
        return $this->get_job_status_obj()->get_time_completed_formatted();
    }
}


