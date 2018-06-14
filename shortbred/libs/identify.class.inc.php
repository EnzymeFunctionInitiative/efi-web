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


    public function get_filename() {
        return $this->filename;
    }



    public function __construct($db, $job_id, $is_debug = false) {
        parent::__construct($db);
        $this->set_id($job_id);
        $this->db = $db;
        $this->is_debug = $is_debug;
        $this->load_job();
    }

    public static function create($db, $email, $tmp_filename, $filename, $jobGroup) {
        $info = self::create_shared($db, $email, $tmp_filename, $filename, "", false);
        return $info;
    }

    // $parent_id > 0 and $true_copy = true makes a true copy of the job in the database (i.e. no
    // identify step is run at all, no SSN is provided).
    //
    // $parent_id > 0 and $true_copy = false creates a new job based
    // on the parent job, but re-runs parts of the identify step with the new SSN. 
    private static function create_shared($db, $email, $tmp_filename, $filename, $parent_id, $true_copy) {

        // Sanitize filename
        $filename = preg_replace("([^a-zA-Z0-9\-_\.])", '', $filename);

        $key = functions::generate_key();
        
        $insert_array = array(
            'identify_email' => $email,
            'identify_key' => $key,
            'identify_filename' => $filename,
            'identify_status' => $status,
            'identify_time_created' => self::get_current_time(),
        );
        if ($true_copy && $parent_id)
            $insert_array['identify_copy_id'] = $parent_id;
        elseif ($parent_id)
            $insert_array['identify_parent_id'] = $parent_id;

        $new_id = $db->build_insert('identify', $insert_array);

        if ($new_id) {
            if (!$parent_id || $tmp_filename) {
                global_functions::copy_to_uploads_dir($tmp_filename, $filename, $new_id);
            }
        } else {
            return false;
        }

        $info = array('id' => $new_id, 'key' => $key);

        return $info;
    }

    private static function get_create_args($email, $filename, $parent_id, $true_copy) {
    }

    public static function create_update_ssn($db, $email, $tmp_filename, $filename, $parent_id, $parent_key) {
        $sql = "SELECT * FROM identify WHERE identify_id='$parent_id' AND identify_key='$parent_key'";
        $result = $db->query($sql);
        if (!$result)
            return false;
        $info = self::create_shared($db, $email, $tmp_filename, $filename, $parent_id, false);
        return $info;
    }

    public static function create_copy($db, $email, $parent_id, $parent_key) {
        $sql = "SELECT * FROM identify WHERE identify_id='$parent_id' AND identify_key='$parent_key'";
        $result = $db->query($sql);
        if (!$result)
            return false;
        
        $filename = $result[0]["identify_filename"];

        $info = self::create_shared($db, $email, "", $filename, $parent_id, true);
        return $info;
    }

    public function run_job() {
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

        $parent_id = $this->get_parent_id();

        if (!$this->is_debug) {
            if ((!$parent_id || file_exists($ssn_path)) && !file_exists($out_dir)) {
                mkdir($out_dir);
                chdir($out_dir);
                copy($ssn_path, $target_ssn_path);
            }
        }

        $sched = settings::get_cluster_scheduler();
        $queue = settings::get_normal_queue();

        $exec = "source /etc/profile\n";
        $exec .= "module load " . settings::get_efidb_module() . "\n";
        $exec .= "module load " . settings::get_shortbred_module() . "\n";
        $exec .= "$script";
        $exec .= " -ssn-in $target_ssn_path";
        $exec .= " -ssn-out-name " . $this->get_ssn_name();
        $exec .= " -cdhit-out-name cdhit.txt";
        $exec .= " -tmpdir " . settings::get_rel_output_dir();
        $exec .= " -job-id " . $id;
        $exec .= " -np " . settings::get_num_processors();
        $exec .= " -queue $queue";
        if ($sched)
            $exec .= " -scheduler $sched";
        if ($parent_id)
            $exec .= " -parent-job-id $parent_id";

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
            $this->set_time_completed();
            $this->email_failure("Unable to find any accession IDs in the SSN.  Is it in the correct format?");
        } elseif (!$is_running && !$is_finished) {
            $result = 0;
            $this->set_status(__FAILED__);
            $this->set_time_completed();
            $this->email_failure();
            $this->email_admin_failure("Job died.");
        } elseif (!$is_running) {
            $result = 2;

            $parent_id = $this->get_parent_id();
            if ($parent_id) {
                $this->create_quantify_jobs_from_parent();
            }

            $this->set_status(__FINISH__);
            $this->set_time_completed();
            $this->email_completed();
        }
        // Else the job is still running

        return $result;
    }


    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // PARENT JOB FUNCTIONS
    //

    private function create_quantify_jobs_from_parent() {
        $parent_id = $this->get_parent_id();
        if (!$parent_id)
            return;
        $id = $this->get_id();

        $q_jobs = job_manager::get_quantify_jobs($this->db, $parent_id);

        foreach ($q_jobs as $job) {
            $q_id = $job["quantify_id"];
            quantify::create_copy($this->db, $q_id, $id, $parent_id);
        }
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








    public function get_marker_file_path() {
        $id = $this->get_id();
        $parent_id = $this->get_parent_id();
        if ($parent_id)
            $id = $parent_id;
        $out_dir = settings::get_output_dir() . "/" . $id;
        $res_dir = $out_dir . "/" . settings::get_rel_output_dir();
        return "$res_dir/markers.faa";
    }

    public function get_results_path() {
        $id = $this->get_id();
        $out_dir = settings::get_output_dir() . "/" . $id;
        $res_dir = $out_dir . "/" . settings::get_rel_output_dir();
        return $res_dir;
    }

    public function get_ssn_http_path() {
        $id = $this->get_id();
        $res_dir = settings::get_rel_output_dir();
        return "$id/$res_dir/" . $this->get_ssn_name();
    }
    
    public function get_output_ssn_zip_http_path() {
        $path = $this->get_ssn_http_path() . ".zip";
        return $path;
    }

    public function get_cdhit_file_path() {
        $id = $this->get_id();
        $out_dir = settings::get_output_dir() . "/" . $id;
        $res_dir = $out_dir . "/" . settings::get_rel_output_dir();
        return "$res_dir/cdhit.txt";
    }

    private function get_ssn_name() {
        return self::make_ssn_name($this->get_id(), $this->filename);
    }

    public static function make_ssn_name($id, $filename) {
        $name = preg_replace("/.zip$/", ".xgmml", $filename);
        $name = preg_replace("/.xgmml$/", "_markers.xgmml", $name);
        return "${id}_$name";
    }

    public function get_output_ssn_path() {
        $path = $this->get_results_path() . "/" . $this->get_ssn_name();
        return $path;
    }

    public function get_full_ssn_path() {
        $uploads_dir = settings::get_uploads_dir();
        return $uploads_dir . "/" . $this->get_id() . "." . pathinfo($this->filename, PATHINFO_EXTENSION);
    }

    public function get_output_ssn_file_size() {
        $path = $this->get_output_ssn_path();
        if (file_exists($path))
            return filesize($path);
        else
            return 0;
    }

    public function get_output_ssn_zip_file_size() {
        $path = $this->get_output_ssn_zip_file_path();
        if (file_exists($path))
            return filesize($path);
        else
            return 0;
    }

    public function get_output_ssn_zip_file_path() {
        $path = $this->get_output_ssn_path() . ".zip";
        return $path;
    }

    protected function get_table_name() {
        return job_types::Identify;
    }
}

?>
