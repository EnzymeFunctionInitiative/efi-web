<?php

require_once(__DIR__ . "/../../conf/settings_base.inc.php");
require_once("Mail.php");
require_once("Mail/mime.php");
require_once("const.class.inc.php");
require_once("functions.class.inc.php");


class bigscape_job {

    const STATUS_NONE = 0;
    const STATUS_RUNNING = __RUNNING__;
    const STATUS_FINISH = __FINISH__;

    private $job_completed = "job.completed";
    private $job_error = "job.error";
    private $job_dir = "bigscape";

    private $time_created;
    private $time_started;
    private $time_completed;
    private $job_status = "";
    private $job_type;
    private $diagram_id;
    private $bigscape_id;
    private $bigscape_status = self::STATUS_NONE; // this represents if the job exists, is pending/running, or is complete
    private $eol = PHP_EOL;
    private $email;
    private $pbs_number;
    private $db_mod = "";

    public function __construct($db, $diagram_id, $job_type) {
        if ($diagram_id && $job_type !== NULL) {
            $this->bigscape_status = $this->load_from_diagram_id($db, $diagram_id, $job_type);
        } elseif ($diagram_id) {
            $this->bigscape_status = $this->load_from_bigscape_id($db, $diagram_id);
        } else {
            $this->bigscape_status = self::STATUS_NONE;
        }

        $this->email = "";
        $this->key = "";
        $this->db = $db;

        if ($this->bigscape_status != self::STATUS_NONE) {
            $checkSql = "";
            $email_field = "";
            $key_field = "";
            if ($this->job_type == DiagramJob::GNN) {
                $checkSql = "SELECT * FROM gnn WHERE gnn_id = " . $this->diagram_id;
                $email_field = "gnn_email";
                $key_field = "gnn_key";
            } else {
                $checkSql = "SELECT * FROM diagram WHERE diagram_id = " . $this->diagram_id;
                $email_field = "diagram_email";
                $key_field = "diagram_key";
            }
    
            // If the job doesn't exist in the GNN or diagram database then don't create it.
            $result = $db->query($checkSql);
            if ($result) {
                $this->email = $result[0][$email_field];
                $this->key = $result[0][$key_field];
            }
        }
    }

    public static function get_jobs($db, $job_status_filter) {
        $sql = "SELECT * FROM bigscape WHERE bigscape_status = \"$job_status_filter\"";
        $result = $db->query($sql);
        $ids = array();
        for ($i = 0; $i < count($result); $i++) {
            array_push($ids, $result[$i]['bigscape_id']);
        }
        return $ids;
    }

    public static function from_job_id($db, $job_id) {
        return new bigscape_job($db, $job_id, NULL);
    }

    public static function from_diagram_id($db, $diagram_id, $diagram_source) {
        return new bigscape_job($db, $diagram_id, $diagram_source);
    }

    public function get_status() {
        return $this->bigscape_status;
    }

    public function is_finished() {
        return $this->bigscape_status == self::STATUS_FINISH;
    }

    public static function create_bigscape_job($db, $diagram_id, $key, $job_type) {
        $checkSql = "";
        if ($job_type == DiagramJob::GNN) {
            $checkSql = "SELECT * FROM gnn WHERE gnn_id = $diagram_id AND gnn_key = \"$key\"";
        } else {
            $checkSql = "SELECT * FROM diagram WHERE diagram_id = $diagram_id AND diagram_key = \"$key\"";
        }

        // If the job doesn't exist in the GNN or diagram database then don't create it.
        $result = $db->query($checkSql);
        if (!$result) {
            return 0;
        }

        $status = __NEW__;
        $insertArray = array(
            "bigscape_diagram_id" => $diagram_id,
            "bigscape_status" => $status,
            "bigscape_job_type" => $job_type
        );
        $result = $db->build_insert("bigscape", $insertArray);

        return $result;
    }

    private function load_from_diagram_id($db, $diagram_id, $job_type) {
        $this->diagram_id = $diagram_id;
        $this->job_type = $job_type;

        $sql = "SELECT * FROM bigscape WHERE bigscape_diagram_id = " . $this->diagram_id . " AND bigscape_job_type = \"" . $this->job_type . "\"";
        $result = $db->query($sql);
        if ($result) {
            $result = $result[0];
            $this->bigscape_id = $result['bigscape_id'];
            $this->time_created = $result['bigscape_time_created'];
            $this->time_started = $result['bigscape_time_started'];
            $this->time_completed = $result['bigscape_time_completed'];
            $this->job_status = $result['bigscape_status'];
            $this->pbs_number = $result['bigscape_pbs_number'];
            if ($this->job_status == __NEW__ || $this->job_status == __RUNNING__)
                return self::STATUS_RUNNING;
            else
                return self::STATUS_FINISH;
        } else {
            return self::STATUS_NONE;
        }
    }

    private function load_from_bigscape_id($db, $bigscape_id) {
        $this->bigscape_id = $bigscape_id;
        $this->job_type = "";

        $sql = "SELECT * FROM bigscape WHERE bigscape_id = " . $this->bigscape_id;
        $result = $db->query($sql);
        if ($result) {
            $result = $result[0];
            $this->diagram_id = $result['bigscape_diagram_id'];
            $this->job_type = $result['bigscape_job_type'];
            $this->time_created = $result['bigscape_time_created'];
            $this->time_started = $result['bigscape_time_started'];
            $this->time_completed = $result['bigscape_time_completed'];
            $this->job_status = $result['bigscape_status'];
            $this->pbs_number = $result['bigscape_pbs_number'];
            if ($this->job_status == __NEW__ || $this->job_status == __RUNNING__)
                return self::STATUS_RUNNING;
            else
                return self::STATUS_FINISH;
        } else {
            return self::STATUS_NONE;
        }
    }

    public function run() {
        if ($this->job_status != __NEW__)
            return false;

        $diagram_file = "";
        $updated_diagram_file = "";
        if ($this->job_type == DiagramJob::GNN) {
            $info = new gnn($this->db, $this->diagram_id);
            $diagram_file = $info->get_diagram_data_file(false);
            $updated_diagram_file = $info->get_diagram_data_file(true); // true for bigscape
        } else {
            $info = new diagram_data_file($this->diagram_id);
            $diagram_file = $info->get_diagram_data_file(false);
            $updated_diagram_file = $info->get_diagram_data_file(true); // true for bigscape
        }

        $main_job_dir = pathinfo($updated_diagram_file, PATHINFO_DIRNAME);
        chdir($main_job_dir);

        $job_dir = "$main_job_dir/" . $this->job_dir;

        $sched = settings::get_cluster_scheduler();
        $queue = settings::get_normal_queue();

        $binary = settings::get_process_bigscape_script();
        $exec = "source /etc/profile\n";
        $exec .= "module load " . settings::get_gnn_module() . "\n";
        if ($this->db_mod)
            $exec .= "module load " . $this->db_mod . "\n";
        else
            $exec .= "module load " . settings::get_efidb_module() . "\n";
        $exec .= $binary;
        $exec .= " -diagram-file \"$diagram_file\"";
        $exec .= " -updated-diagram-file \"$updated_diagram_file\"";
        $exec .= " -cluster-info-file \"$diagram_file.bigscape-clusters\"";
        $exec .= " -bigscape-dir \"$job_dir\"";
        if ($sched)
            $exec .= " -scheduler $sched";
        if ($queue)
            $exec .= " -queue $queue";

        $exit_status = 1;
        $output_array = array();
        $output = exec($exec, $output_array, $exit_status);
        $output = trim(rtrim($output));

        if ($sched == "slurm")
            $pbs_job_number = $output;
        else
            $pbs_job_number = substr($output, 0, strpos($output, "."));

        if (!$exit_status) {
            $update_sql = "UPDATE bigscape " .
                "SET bigscape_status = '" . __RUNNING__ . "', bigscape_pbs_number = $pbs_job_number " .
                "WHERE bigscape_id = " . $this->bigscape_id;
            $this->db->non_select_query($update_sql);
            $this->email_started();
        } else {
            $currentTime = date("Y-m-d H:i:s", time());
            $this->db->non_select_query("UPDATE bigscape SET bigscape_status = '" . __FAILED__ . "', " .
                                        "bigscape_time_completed = '$currentTime' WHERE bigscape_id = " . $this->bigscape_id);
            $this->email_failed();
            error_log("Error: $output");
        }

        return $exit_status == 0;
    }

    public function get_message() {
        return "";
    }

    private function email_failed() {

        $emailTitleBit = "could not complete";
        $emailBody = "";
        $emailBody .= "There was an error running BiG-SCAPE for the job (job #" . $this->bigscape_id . ").";
        $emailBody .= $this->eol . $this->eol;

        $this->email_shared($emailTitleBit, $emailBody);
    }

    private function email_complete() {

        $emailTitleBit = "completed";
        $emailBody = "";
        $emailBody .= "The diagrams have been ordered using BiG-SCAPE and are ready to be viewed at ";
        $emailBody .= "THE_URL" . $this->eol . $this->eol;

        $this->email_shared($emailTitleBit, $emailBody);
    }

    private function email_started() {

        $emailTitleBit = "started";
        $emailBody = "";
        $emailBody .= "BiG-SCAPE has started running to order the diagrams.  You will receive an email when the results are ready.";
        $emailBody .= $this->eol . $this->eol;

        $this->email_shared($emailTitleBit, $emailBody);
    }

    private function email_shared($titleBit, $message) {
        $queryType = functions::get_diagram_id_field($this->job_type);
        $subject = "EFI-GNT - BiG-SCAPE run $titleBit";
        $to = $this->email;
        $from = "EFI GNT <" . settings::get_admin_email() . ">";
        $url = settings::get_web_root() . "/view_diagrams.php";
        $full_url = $url . "?" . http_build_query(array($queryType => $this->diagram_id, 'key' => $this->key));

        $plain_email = "";

        //if ($this->beta) $plain_email = "Thank you for using the beta site of EFI-GNT." . $this->eol;

        //plain text email
        $plain_email .= $message;
        $plain_email .= settings::get_email_footer();

        $html_email = nl2br($plain_email, false);

        $plain_email = str_replace("THE_URL", $full_url, $plain_email);
        $html_email = str_replace("THE_URL", "<a href='" . htmlentities($full_url) . "'>" . $full_url . "</a>", $html_email);

        $message = new Mail_mime(array("eol" => $this->eol));
        $message->setTXTBody($plain_email);
        $message->setHTMLBody($html_email);
        $body = $message->get();
        $extraheaders = array(
            "From" => $from,
            "Subject" => $subject
        );
        $headers = $message->headers($extraheaders);

        $mail = Mail::factory("mail");
        $mail->send($to, $headers, $body);
    }

    public function check_if_job_is_done() {

        $diagram_file = "";
        if ($this->job_type == DiagramJob::GNN) {
            $info = new gnn($this->db, $this->diagram_id);
            $diagram_file = $info->get_diagram_data_file(true);
        } else {
            $info = new diagram_data_file($this->diagram_id);
            $diagram_file = $info->get_diagram_data_file(true); // true for bigscape
        }

        $main_job_dir = pathinfo($diagram_file, PATHINFO_DIRNAME);
        $job_dir = "$main_job_dir/" . $this->job_dir;

        $finish_file_exists = file_exists("$job_dir/" . $this->job_completed);
        $isDone = $finish_file_exists || !$this->check_pbs_running();
        $isError = file_exists("$job_dir/" . $this->job_error) || !$finish_file_exists;

        if ($isDone) {
            $currentTime = date("Y-m-d H:i:s", time());
            
            $status = __FINISH__;
            if ($isError)
                $status = __FAILED__;
            print $this->bigscape_id . " has completed and has status = $status.\n";

            $this->db->non_select_query("UPDATE bigscape SET bigscape_status = '$status', " .
                                        "bigscape_time_completed = '$currentTime' WHERE bigscape_id = " . $this->bigscape_id);

            if ($isError)
                $this->email_failed();
            else
                $this->email_complete();
        }
    }

    private function check_pbs_running() {
        $sched = strtolower(settings::get_cluster_scheduler());
        $jobNum = $this->pbs_number;
        $output = "";
        $exit_status = "";
        $exec = "";
        if ($sched == "slurm")
            $exec = "squeue --job $jobNum 2> /dev/null | grep $jobNum";
        else
            $exec = "qstat $jobNum 2> /dev/null | grep $jobNum";
        exec($exec,$output,$exit_status);
        if (count($output) == 1) {
            return true;
        }
        else {
            return false;
        }
    }

}


?>

