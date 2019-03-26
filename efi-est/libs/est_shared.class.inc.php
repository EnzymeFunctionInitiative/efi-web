<?php

require_once(__DIR__ . "/../includes/main.inc.php");
require_once("Mail.php");
require_once("Mail/mime.php");
require_once(__BASE_DIR__ . "/libs/user_auth.class.inc.php");
require_once(__BASE_DIR__ . "/libs/global_functions.class.inc.php");
require_once(__BASE_DIR__ . "/libs/global_settings.class.inc.php");

abstract class est_shared {

    private $table;
    private $db;

    protected $email;
    protected $time_created;
    protected $time_completed;
    protected $time_started;
    protected $eol = PHP_EOL;
    protected $beta;

    function __construct($db, $table) {
        $this->db = $db;
        $this->table = $table;
        $this->beta = global_settings::get_release_status();
    }

    public function get_time_created() { return $this->time_created; }
    public function get_time_started() { return $this->time_started; }
    public function get_time_completed() { return $this->time_completed; }
    public function get_time_completed_formatted() { return functions::format_datetime(functions::parse_datetime($this->time_completed)); }
    public function get_time_period() { $window = global_functions::format_short_date($this->get_time_started()) . " -- " . global_functions::format_short_date($this->get_time_completed()); return $window; }
    public function get_unixtime_completed() { return strtotime($this->time_completed); }
    
    public function get_email() { return $this->email; }
    public function set_email($email) { $this->email = $email; }

    protected function set_time_started() {
        $table = $this->table;
        $current_time = self::get_current_datetime();
        $sql = "UPDATE ${table} SET ${table}_time_started='" . $current_time . "' ";
        $sql .= "WHERE ${table}_id='" . $this->get_id() . "' LIMIT 1";
        $this->db->non_select_query($sql);
        $this->time_started = $current_time;
    }

    protected function set_time_completed($current_time = false) {
        $table = $this->table;
        if ($current_time === false)
            $current_time = self::get_current_datetime();
        $sql = "UPDATE ${table} SET ${table}_time_completed='" . $current_time . "' ";
        $sql .= "WHERE ${table}_id='" . $this->get_id() . "' LIMIT 1";
        $this->db->non_select_query($sql);
        $this->time_completed = $current_time;
    }

    private static function get_current_datetime() {
        $current_time = date("Y-m-d H:i:s",time());
        return $current_time;
    }

    public abstract function get_status();
    public abstract function set_status($status);

    public function mark_job_as_archived() {
        // This marks the job as archived-failed. If the job is archived but the
        // time completed is non-zero, then the job successfully completed.
        if ($this->get_status() == __FAILED__)
            $this->set_time_completed("0000-00-00 00:00:00");
        $this->set_status(__ARCHIVED__);
    }

    public function mark_job_as_cancelled() {
        $this->set_status(__CANCELLED__);
        $this->set_time_completed();
        $this->email_cancelled();
    }

    public function finish_job_complete() {
        $this->set_status(__FINISH__);
        $this->set_time_completed();
    }

    public function finish_job_failed() {
        $this->set_status(__FAILED__);
        $this->set_time_completed();
    }

    public abstract function get_key();
    public abstract function get_id();
    protected abstract function get_generate_results_script();


    protected abstract function get_email_job_info();
    protected abstract function get_email_started_subject();
    protected abstract function get_email_started_body();
    protected abstract function get_email_completion_subject();
    protected abstract function get_email_completion_body();
    protected abstract function get_email_cancelled_subject();
    protected abstract function get_email_cancelled_body();


    public function email_started() {
        $subject = $this->beta . $this->get_email_started_subject();
        $body_data = $this->get_email_started_body();
        $this->send_email($subject, $body_data);
    }

    public function email_complete() {
        $subject = $this->beta . $this->get_email_completion_subject();
        $body_data = $this->get_email_completion_body();
        $extra_footer = "These data will only be retained for " . functions::get_retention_days() . " days." . PHP_EOL . PHP_EOL;
        $this->send_email($subject, $body_data, "", $extra_footer);
    }

    protected function email_cancelled() {
        $subject = $this->beta . $this->get_email_cancelled_subject();
        $body_data = $this->get_email_cancelled_body();
        $this->send_email($subject, $body_data);
    }

    public function email_error_admin() {
        $to = functions::get_error_admin_email();
        if (!$to)
            return;

        $subject = $this->beta . "EFI-EST - Pipeline error";
        $from = "EFI-EST <" .functions::get_admin_email() . ">";

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
            $plain_email = "Thank you for using the EFI beta site." . PHP_EOL . $plain_email;
        $plain_email .= $body;
        $plain_email .= "Submission Summary:" . PHP_EOL . PHP_EOL;
        $plain_email .= $this->get_email_job_info() . PHP_EOL . PHP_EOL;
        $plain_email .= $extra_footer;
        $plain_email .= functions::get_email_footer();

        $html_email = nl2br($plain_email, false);
        
        if (!$to)
            $to = $this->get_email();
        $from = "EFI EST <" . functions::get_admin_email() . ">";

        if ($full_url) {
            if (!is_array($full_url))
                $full_url = array("THE_URL" => $full_url);
            foreach ($full_url as $url_key => $url) {
                $plain_email = str_replace($url_key, $url, $plain_email);
                $html_email = str_replace($url_key, "<a href=\"" . htmlentities($url) . "\">" . $url. "</a>", $html_email);
            }
        }

        $message = new Mail_mime(array("eol" => PHP_EOL));
        $message->setTXTBody($plain_email);
        $message->setHTMLBody($html_email);
        $body = $message->get();
        $extraheaders = array("From" => $from, "Subject" => $subject);
        $headers = $message->headers($extraheaders);

        $mail = Mail::factory("mail");
        $mail->send($to, $headers, $body);
    }
}

?>
