<?php

require_once("functions.class.inc.php");
require_once("settings.class.inc.php");
require_once("job_shared.class.inc.php");

// ShortBRED-Identify
class quantify extends job_shared {

    private $is_debug = false;
    private $loaded = false;
    private $db;
    private $error_message = "";
    private $identify_id;
    private $metagenome_ids;


    // job_id represents the analysis id, not the identify id.
    public function __construct($db, $job_id, $is_debug = false) {
        parent::__construct($db);
        $this->set_id($job_id);
        $this->db = $db;
        $this->is_debug = $is_debug;
        $this->load_job();
    }

    public static function create($db, $identify_id, $metagenome_ids) {

        $insert_array = array(
            'quantify_identify_id' => $identify_id,
            'quantify_metagenome_ids' => $metagenome_ids,
            'quantify_status' => __NEW__,
            'quantify_time_created' => self::get_current_time(),
        );
        $result = $db->build_insert('quantify', $insert_array);

        $info = array('id' => $result);

        return $info;
    }

    public function run_job() {
//        //TODO: run the job here
//        
//        $result = $this->start_job();
//        if ($result === true) {
//            if (!$this->is_debug) {
//                $this->set_time_started();
//                $this->set_status(__RUNNING__);
//                $this->email_started();
//            } else {
//                print "would have sent email started\n";
//            }
//        } else {
//            functions::log_message("Error running job: $result");
//            if (!$this->is_debug) {
//                $this->email_admin_failure($result); // Don't email the user
//                $this->set_status(__FAILED__);
//            } else {
//                print "would have sent email failure $result\n";
//            }
//        }
//        return $result;
    }

    public function get_message() {
        return $this->error_message;
    }







    private function start_job() {
        $id = $this->get_id();

        $script = settings::get_quantify_script();
        $id_out_dir = settings::get_output_dir() . "/" . $id;
        $out_dir = $id_out_dir . "/" . settings::get_quantify_rel_output_dir();

        if (@file_exists($out_dir)) {
            global_functions::rrmdir($out_dir);
        }
        chdir($id_out_dir);

        $sched = settings::get_cluster_scheduler();
        $queue = settings::get_normal_queue();

        $exec = "source /etc/profile\n";
        $exec .= "module load " . settings::get_efidb_module() . "\n";
        $exec .= "module load " . settings::get_shortbred_module() . "\n";
        $exec .= "$script";
        $exec .= " -metagenome-db " . settings::get_metagenome_db_list();
        $exec .= " -qu-dir " . settings::get_quantify_rel_output_dir();
        $exec .= " -id-dir " . settings::get_rel_output_dir();
        $exec .= " -metagenome-ids " . implode(",", $this->metagenome_ids);
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
        $sql = "SELECT * FROM quantify WHERE quantify_id='" . $this->get_id() . "'";
        $result = $this->db->query($sql);

        if (!$result) {
            return false;
        }

        $result = $result[0];

        $this->load_job_shared($result);

        $this->identify_id = $result['quantify_identify_id'];
        $mg_ids = $result['quantify_metagenome_ids'];
        $this->metagenome_ids = explode(",", $mg_ids);

        $this->loaded = true;
        return true;
    }


    public function check_if_job_is_done() {
//        $id = $this->get_id();
//        $out_dir = settings::get_output_dir() . "/" . $id;
//        $res_dir = $out_dir . "/" . settings::get_rel_output_dir();
//
//        $fasta_failed = "$res_dir/get_fasta.failed";
//        $finish_file = "$res_dir/job.completed";
//
//        $is_fasta_error = file_exists($fasta_failed);
//        $is_finished = file_exists($finish_file);
//        $is_running = $this->is_job_running();
//
//        $result = 1;
//        if ($is_fasta_error) {
//            $result = 0;
//            $this->set_status(__FAILED__);
//            $this->email_failure("Unable to find any accession IDs in the SSN.  Is it in the correct format?");
//        } elseif (!$is_running && !$is_finished) {
//            $result = 0;
//            $this->set_status(__FAILED__);
//            $this->email_failure();
//            $this->email_admin_failure("Job died.");
//        } elseif (!$is_running) {
//            $result = 2;
//            $this->set_status(__FINISH__);
//            $this->email_completed();
//        }
//        // Else the job is still running
//
//        return $result;
    }




    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // EMAIL FUNCTIONS
    //

    protected function get_email_started_subject() {
        $subject = "EFI/ShortBRED - Quantify marker submission received";
        return $subject;
    }

    protected function get_email_started_message() {
        $plain_email = "";
        $plain_email .= "The markers are being quantified against the selected metagenome(s).";
        $plain_email .= $this->eol . $this->eol;
        return $plain_email;
    }

    protected function get_email_failure_subject() {
        $subject = "EFI/ShortBRED - Quantify marker computation failed";
        return $subject;
    }

    protected function get_email_failure_message($result) {
        $plain_email = "";
        $plain_email .= "The ShortBRED marker quantify computation failed.  ";
        if ($result) {
            $plain_email .= "Reason: $result" . $this->eol . $this->eol;
        }
        $plain_email .= "Please contact us for further assistance." . $this->eol . $this->eol;
        return $plain_email;
    }

    protected function get_email_completed_subject() {
        $subject = "EFI/ShortBRED - ShortBRED quantify marker computation completed";
        return $subject;
    }

    protected function get_email_completed_message() {
        $plain_email = "";
        $plain_email .= "The ShortBRED  quantify marker computation succeeded." . $this->eol . $this->eol;
        $plain_email .= "To view results, go to THE_URL" . $this->eol . $this->eol;
        return $plain_email;
    }

    protected function get_completed_url() {
        $url = settings::get_web_root() . "/stepe.php";
        return $url;
    }

    protected function get_completed_url_params() {
        $query_params = array('id' => $this->identify_id, 'quantify_id' => $this->get_id(), 'key' => $this->get_key());
        return $query_params;
    }

    protected function get_job_info() {
        $info = parent::get_job_info();
        return $info;
    }








    protected function get_table_name() {
        return "quantify";
    }
}

?>
