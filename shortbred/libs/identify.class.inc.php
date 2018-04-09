<?php

require_once("functions.class.inc.php");
require_once("settings.class.inc.php");
require_once("job_shared.class.inc.php");

// ShortBRED-Identify
class identify extends job_shared {

    private $filename;

    private $is_debug = false;
    private $loaded = false;
    private $db;
    private $error_message = "";

    public function __construct($db, $job_id, $is_debug = false) {
        parent::__construct($db);
        $this->set_id($job_id);
        $this->db = $db;
        $this->is_debug = $is_debug;
        $this->load_job();
    }

    public static function create($db, $email, $tmpFilename, $filename, $jobGroup) {

        // Sanitize filename
        $filename = preg_replace("([^a-zA-Z0-9\-_\.])", '', $filename);

        $key = functions::generate_key();
        $insert_array = array(
            'identify_email' => $email,
            'identify_key' => $key,
            'identify_filename' => $filename,
            'identify_status' => __NEW__,
            'identify_time_created' => self::get_current_time(),
        );
        $result = $db->build_insert('identify', $insert_array);

        if ($result) {	
            global_functions::copy_to_uploads_dir($tmpFilename, $filename, $result);
        } else {
            return false;
        }

        $info = array('id' => $result, 'key' => $key);

        return $info;
    }

    public function run_job() {
        //TODO: run the job here
        
        $result = $this->start_job();
        if ($result === true) {
            if (!$this->is_debug) {
                $this->set_time_started();
                $this->set_status(__RUNNING__);
                $this->email_started();
            } else {
                print "would have sent email started\n";
            }
        } else {
            functions::log_message("Error running job: $result");
            if (!$this->is_debug) {
                $this->email_admin_failure($result); // Don't email the user
                $this->set_status(__FAILED__);
            } else {
                print "would have sent email failure $result\n";
            }
        }
        return $result;
    }

    public function get_message() {
        return $this->error_message;
    }







    private function start_job() {
        $id = $this->get_id();

        $ssn_path = $this->get_full_ssn_path();
        $script = settings::get_identify_script();
        $out_dir = settings::get_output_dir() . "/" . $id;
        $target_ssn_path = $out_dir . "/" . $id . "." . pathinfo($ssn_path, PATHINFO_EXTENSION);

        if (@file_exists($out_dir)) {
            global_functions::rrmdir($out_dir);
        }

        if (!$this->is_debug && !file_exists($out_dir)) {
            mkdir($out_dir);
            chdir($out_dir);
            copy($ssn_path, $target_ssn_path);
        }

        $sched = settings::get_cluster_scheduler();
        $queue = settings::get_normal_queue();

        $exec = "source /etc/profile\n";
        $exec .= "module load " . settings::get_efidb_module() . "\n";
        $exec .= "module load " . settings::get_shortbred_module() . "\n";
        $exec .= "$script";
        $exec .= " -ssn $target_ssn_path";
        $exec .= " -tmpdir " . settings::get_rel_output_dir();
        $exec .= " -job-id " . $id;
        $exec .= " -np " . settings::get_num_processors();
        $exec .= " -queue $queue";
        if ($sched)
            $exec .= " -scheduler $sched";

        if ($this->is_debug) {
            print("Job ID: $id\n");
            print("Exec: $exec\n");
        } else {
            functions::log_message($exec);
        }

        $exit_status = 0;
        $output_array = array();
        $output = "";
        if ($this->is_debug) {
            return true;
        }

        $output = exec($exec, $output_array, $exit_status);
        $output = trim(rtrim($output));

        if ($sched == "slurm") {
            $pbs_job_number = $output;
        } else {
            $pbs_job_number = substr($output, 0, strpos($output, "."));
        }

        if ($pbs_job_number && !$exit_status) {
            if (!$this->is_debug) {
                $this->set_pbs_number($pbs_job_number);
            }  else {
                print "Setting pbs time_started running\n";
            }
            return true;
        } else {
            return "Failed to execute job: exit=$exit_status job=$pbs_job_number exec=$exec";
        }
    }

    private function load_job() {
        $sql = "SELECT * FROM identify WHERE identify_id='" . $this->get_id() . "'";
        $result = $this->db->query($sql);

        if (!$result) {
            return false;
        }

        $result = $result[0];

        $this->load_job_shared($result);

        $this->set_email($result['identify_email']);
        $this->set_key($result['identify_key']);
        $this->filename = $result['identify_filename'];

        $this->loaded = true;
        return true;
    }


    public function check_if_job_is_done() {
        $id = $this->get_id();
        $out_dir = settings::get_output_dir() . "/" . $id;
        $res_dir = $out_dir . "/" . settings::get_rel_output_dir();

        $fasta_failed = "$res_dir/get_fasta.failed";
        $finish_file = "$res_dir/job.completed";

        $is_fasta_error = file_exists($fasta_failed);
        $is_finished = file_exists($finish_file);
        $is_running = $this->is_job_running();

        $result = 1;
        if ($is_fasta_error) {
            $result = 0;
            $this->set_status(__FAILED__);
            $this->email_failure("Unable to find any accession IDs in the SSN.  Is it in the correct format?");
        } elseif (!$is_running && !$is_finished) {
            $result = 0;
            $this->set_status(__FAILED__);
            $this->email_failure();
            $this->email_admin_failure("Job died.");
        } elseif (!$is_running) {
            $result = 2;
            $this->set_status(__FINISH__);
            $this->email_completed();
        }
        // Else the job is still running

        return $result;
    }




    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // EMAIL FUNCTIONS
    //

    protected function get_email_started_subject() {
        $subject = "EFI/ShortBRED - SSN submission received";
        return $subject;
    }

    protected function get_email_started_message() {
        $plain_email = "";
        $plain_email .= "The SSN that is the input needed to run ShortBRED has been received and is ";
        $plain_email .= "being processed." . $this->eol . $this->eol;
        return $plain_email;
    }

    protected function get_email_failure_subject() {
        $subject = "EFI/ShortBRED - Computation failed";
        return $subject;
    }

    protected function get_email_failure_message($result) {
        $plain_email = "";
        $plain_email .= "The ShortBRED computation failed.  ";
        if ($result) {
            $plain_email .= "Reason: $result" . $this->eol . $this->eol;
        }
        $plain_email .= "Please contact us for further assistance." . $this->eol . $this->eol;
        return $plain_email;
    }

    protected function get_email_completed_subject() {
        $subject = "EFI/ShortBRED - ShortBRED computation completed";
        return $subject;
    }

    protected function get_email_completed_message() {
        $plain_email = "";
        $plain_email .= "The ShortBRED computation succeeded." . $this->eol . $this->eol;
        $plain_email .= "To view results, go to THE_URL" . $this->eol . $this->eol;
        return $plain_email;
    }

    protected function get_completed_url() {
        $url = settings::get_web_root() . "/stepc.php";
        return $url;
    }

    protected function get_job_info() {
        $info = parent::get_job_info();
        $message = "Uploaded Filename: " . $this->filename . $this->eol;
        return $info;
    }










    private function get_full_ssn_path() {
        $uploads_dir = settings::get_uploads_dir();
        return $uploads_dir . "/" . $this->get_id() . "." . pathinfo($this->filename, PATHINFO_EXTENSION);
    }

    protected function get_table_name() {
        return "identify";
    }
}

?>
