<?php

require_once("settings.class.inc.php");
require_once("Mail.php");
require_once("Mail/mime.php");

abstract class job_shared {

    private $id;
    private $pbs_number;
    private $key;
    private $status;
    private $email;
    private $time_created;
    private $time_started;
    private $time_completed;

    private $db;
    private $beta;
    protected $eol = PHP_EOL;

    function __construct($db) {
        $this->db = $db;
        $this->beta = settings::get_release_status();
    }

    protected abstract function get_table_name();

    public function get_id() {
        return $this->id;
    }
    public function set_id($theId) {
        $this->id = $theId;
    }

    public function get_key() {
        return $this->key;
    }
    public function set_key($newKey) {
        $this->key = $newKey;
    }

    protected function get_pbs_number() {
        return $this->pbs_number;
    }
    protected function set_pbs_number($theNumber) {
        $this->pbs_number = $theNumber;
        $this->update_pbs_number();
    }

    protected function set_status($status) {
        $this->status = $status;
        $this->update_status($status);
    }
    public function get_status() {
        return $this->status;
    }

    protected function set_email($email) {
        $this->email = $email;
    }
    protected function get_email() {
        return $this->email;
    }



    protected function load_job_shared($result) {
        $table = $this->get_table_name();
        $this->status = $result["${table}_status"];
        $this->time_created = $result["${table}_time_created"];
        $this->time_started = $result["${table}_time_started"];
        $this->time_completed = $result["${table}_time_completed"];
        $this->pbs_number = $result["${table}_pbs_number"];
    }




    protected function is_job_running() {
        $sched = settings::get_cluster_scheduler();

        $job_num = $this->pbs_number;
        $output = "";
        $exit_status = "";
        $exec = "";

        if ($sched == "slurm") {
            $exec = "squeue --job $job_num 2> /dev/null | grep $job_num";
        } else {
            $exec = "qstat $job_num 2> /dev/null | grep $job_num";
        }

        exec($exec,$output,$exit_status);

        if (count($output) == 1) {
            return true;
        } else {
            return false;
        }
    }


    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // EMAIL FUNCTIONS
    //
    
    protected function get_job_info() {
        $message = "EFI/ShortBRED Job ID: " . $this->get_id() . $this->eol;
        $message .= "Time Submitted: " . $this->get_time_created() . $this->eol;
        return $message;
    }

    protected abstract function get_email_started_subject();
    protected abstract function get_email_started_message();
    protected abstract function get_email_failure_subject();
    protected abstract function get_email_failure_message($result);
    protected abstract function get_email_completed_subject();
    protected abstract function get_email_completed_message();
    protected abstract function get_completed_url();
    protected function get_completed_url_params() {
        return array();
    }

    protected function email_started() {
        $subject = $this->beta . $this->get_email_started_subject();

        $plain_email = "";
        $plain_email .= $this->get_email_started_message();
        $plain_email .= "Submission Summary:" . $this->eol . $this->eol;
        $plain_email .= $this->get_job_info() . $this->eol . $this->eol;
        $plain_email .= settings::get_email_footer();

        $this->send_email($subject, $plain_email);
    }

    protected function email_failure($result = "") {
        $subject = $this->beta . $this->get_email_failure_subject();

        $plain_email = "";
        $plain_email .= $this->get_email_failure_message($result);
        $plain_email .= "Submission Summary:" . $this->eol . $this->eol;
        $plain_email .= $this->get_job_info() . $this->eol . $this->eol;
        $plain_email .= settings::get_email_footer();

        $this->send_email($subject, $plain_email);
    }

    protected function email_admin_failure($result = "") {
        $subject = $this->beta . $this->get_email_failure_subject();

        $plain_email = "";
        $plain_email .= "FAILED TO START JOB: $result" . $this->eol . $this->eol;
        $plain_email .= $this->get_email_failure_message($result);
        $plain_email .= "Submission Summary:" . $this->eol . $this->eol;
        $plain_email .= $this->get_job_info() . $this->eol . $this->eol;
        $plain_email .= settings::get_email_footer();

        $to = global_settings::get_error_admin_email();
        $this->send_email($subject, $plain_email, "", $to);
    }

    protected function email_completed($result = "") {
        $subject = $this->beta . $this->get_email_completed_subject();

        $url = $this->get_completed_url();
        $params = $this->get_completed_url_params();
        $query_params = array('id'=>$this->get_id(), 'key'=>$this->get_key());
        if (count($params)) {
            $query_params = $params;
        }
        $full_url = $url . "?" . http_build_query($query_params);

        $plain_email = "";
        $plain_email .= $this->get_email_completed_message();
        $plain_email .= "Submission Summary:" . $this->eol . $this->eol;
        $plain_email .= $this->get_job_info() . $this->eol . $this->eol;
        $plain_email .= "These data will only be retained for " . settings::get_retention_days() . " days." . $this->eol . $this->eol;
        $plain_email .= settings::get_email_footer();

        $this->send_email($subject, $plain_email, $full_url);
    }

    private function send_email($subject, $plain_email, $full_url = "", $to = "") {
        if ($this->beta)
            $plain_email = "Thank you for using the beta site of EFI/ShortBRED." . $this->eol . $plain_email;

        if (!$to)
            $to = $this->get_email();
        $from = "EFI/ShortBRED <" . settings::get_admin_email() . ">";

        $html_email = nl2br($plain_email, false);

        if ($full_url) {
            $plain_email = str_replace("THE_URL", $full_url, $plain_email);
            $html_email = str_replace("THE_URL", "<a href='" . htmlentities($full_url) . "'>" . $full_url . "</a>", $html_email);
        }

        $message = new Mail_mime(array("eol" => $this->eol));
        $message->setTXTBody($plain_email);
        $message->setHTMLBody($html_email);
        $body = $message->get();
        $extraheaders = array("From "=> $from, "Subject" => $subject);
        $headers = $message->headers($extraheaders);

        $mail = Mail::factory("mail");
        $mail->send($to, $headers, $body);
    }



    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // TIME FUNCTIONS
    //

    protected static function get_current_time() {
        return date("Y-m-d H:i:s", time());
    }

    protected function get_time_started() {
        return $this->time_started;
    }

    public function get_time_completed() {
        return $this->time_completed;
    }

    protected function get_time_created() {
        return $this->time_created;
    }

    protected function set_time_started() {
        $tableName = $this->get_table_name();
        $current_time = self::get_current_time();
        $sql = "UPDATE ${tableName} SET ${tableName}_time_started='" . $current_time . "' ";
        $sql .= "WHERE ${tableName}_id='" . $this->get_id() . "' LIMIT 1";
        $result = $this->db->non_select_query($sql);
        $this->time_started = $current_time;
    }
    
    protected function set_time_completed() {
        $tableName = $this->get_table_name();
        $current_time = self::get_current_time();
        $sql = "UPDATE ${tableName} SET ${tableName}_time_completed='" . $current_time . "' ";
        $sql .= "WHERE ${tableName}_id='" . $this->get_id() . "' LIMIT 1";
        $result = $this->db->non_select_query($sql);
        $this->time_completed = $current_time;
    }
    
    protected function set_time_created() {
        $tableName = $this->get_table_name();
        $current_time = self::get_current_time();
        $sql = "UPDATE ${tableName} SET ${tableName}_time_created='" . $current_time . "' ";
        $sql .= "WHERE ${tableName}_id='" . $this->get_id() . "' LIMIT 1";
        $result = $this->db->non_select_query($sql);
        $this->time_created = $current_time;
    }



    private function update_status($status) {
        $tableName = $this->get_table_name();
        $sql = "UPDATE ${tableName} ";
        $sql .= "SET ${tableName}_status='" . $status . "' ";
        $sql .= "WHERE ${tableName}_id='" . $this->get_id() . "' LIMIT 1";
        $result = $this->db->non_select_query($sql);
    }

    private function update_pbs_number() {
        $tableName = $this->get_table_name();
        $sql = "UPDATE ${tableName} SET ${tableName}_pbs_number='" . $this->pbs_number . "' ";
        $sql .= "WHERE ${tableName}_id='" . $this->id . "'";
        $this->db->non_select_query($sql);
    }
}

?>
