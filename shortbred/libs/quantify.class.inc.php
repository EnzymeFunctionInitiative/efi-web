<?php

require_once("functions.class.inc.php");
require_once("settings.class.inc.php");
require_once("job_shared.class.inc.php");
require_once("identify.class.inc.php");


// ShortBRED-Identify
class quantify extends job_shared {

    private $is_debug = false;
    private $loaded = false;
    private $db;
    private $error_message = "";
    private $identify_id;
    private $metagenome_ids;
    private $filename;


    
    
    
    public function get_filename() {
        return $this->filename;
    }


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
        $id = $this->identify_id;
        $qid = $this->get_id();

        $script = settings::get_quantify_script();
        $id_out_dir = settings::get_output_dir() . "/" . $id;
        $q_dir = settings::get_quantify_rel_output_dir() . "-$qid";
        $out_dir = $id_out_dir . "/" . $q_dir;
        $target_ssn_path = $this->get_input_identify_ssn_path();

        if ($this->is_debug) {
            print("rmdir $out_dir\n");
            print("cd $id_out_dir\n");
        } else {
            if (@file_exists($out_dir)) {
                global_functions::rrmdir($out_dir);
            }
            chdir($id_out_dir);
        }

        $sched = settings::get_cluster_scheduler();
        $queue = settings::get_normal_queue();

        $exec = "source /etc/profile\n";
        $exec .= "module load " . settings::get_efidb_module() . "\n";
        $exec .= "module load " . settings::get_shortbred_module() . "\n";
        $exec .= "$script";
        $exec .= " -metagenome-db " . settings::get_metagenome_db_list();
        $exec .= " -quantify-dir " . $q_dir;
        $exec .= " -id-dir " . settings::get_rel_output_dir();
        $exec .= " -metagenome-ids " . implode(",", $this->metagenome_ids);
        $exec .= " -job-id " . $qid;
        $exec .= " -ssn-in " . $target_ssn_path;
        $exec .= " -ssn-out-name " . $this->get_ssn_name();
        $exec .= " -protein-file " . self::get_protein_file_name();
        $exec .= " -cluster-file " . self::get_cluster_file_name();
        $exec .= " -protein-norm " . self::get_normalized_protein_file_name();
        $exec .= " -cluster-norm " . self::get_normalized_cluster_file_name();
        $exec .= " -np " . settings::get_num_processors();
        $exec .= " -queue $queue";
        if ($sched)
            $exec .= " -scheduler $sched";

        if ($this->is_debug) {
            print("Identify Job ID: $id\n");
            print("Quantify Job ID: $qid\n");
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
        $sql = "SELECT quantify.*, identify_email, identify_key, identify_filename FROM quantify ";
        $sql .= "JOIN identify ON quantify.quantify_identify_id = identify.identify_id WHERE quantify_id='" . $this->get_id() . "'";
        $result = $this->db->query($sql);

        if (!$result) {
            return false;
        }

        $result = $result[0];

        $this->load_job_shared($result);

        $this->identify_id = $result['quantify_identify_id'];
        $this->filename = $result['identify_filename'];
        $mg_ids = $result['quantify_metagenome_ids'];
        $this->set_email($result['identify_email']);
        $this->set_key($result['identify_key']);
        $this->metagenome_ids = explode(",", $mg_ids);

        $this->loaded = true;
        return true;
    }


    public function check_if_job_is_done() {
        $qid = $this->get_id(); // quantify_id
        $id = $this->identify_id;
        $out_dir = settings::get_output_dir() . "/" . $id;
        $id_dir = $out_dir . "/" . settings::get_rel_output_dir();
        $res_dir = $id_dir . "/" . settings::get_quantify_rel_output_dir() . "-$qid";

        $finish_file = "$res_dir/job.completed";

        $is_finished = file_exists($finish_file);
        $is_running = $this->is_job_running();

        $result = 1; // still running
        if (!$is_running && !$is_finished) {
            $result = 0;
            $this->set_status(__FAILED__);
            $this->set_time_completed();
            $this->email_failure();
            $this->email_admin_failure("Job died.");
        } elseif (!$is_running) {
            $result = 2;
            $this->set_status(__FINISH__);
            $this->set_time_completed();
            $this->email_completed();
        }
        // Else the job is still running

        return $result;
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
        $query_params = array('id' => $this->identify_id, 'quantify-id' => $this->get_id(), 'key' => $this->get_key());
        return $query_params;
    }

    protected function get_job_info() {
        $message = "EFI/ShortBRED Job ID: " . $this->identify_id . $this->eol;
        $message .= "Quantify ID: " . $this->get_id() . $this->eol;
        $message .= "Time Submitted: " . $this->get_time_created() . $this->eol;
        return $message;
    }






    private function get_quantify_res_dir() {
        $id = $this->get_id(); # quantify ID
        $res_dir = settings::get_quantify_rel_output_dir();
        return "$res_dir-$id";
    }
    public function get_identify_output_path() {
        $path = settings::get_output_dir() . "/" . $this->identify_id . "/" . settings::get_rel_output_dir();
        return $path;
    }

    public function get_protein_file_path() {
        $path = $this->get_identify_output_path() . "/" .
            $this->get_quantify_res_dir() . "/" .
            self::get_protein_file_name();
        return $path;
    }
    public function get_cluster_file_path() {
        $path = $this->get_identify_output_path() . "/" .
            $this->get_quantify_res_dir() . "/" .
            self::get_cluster_file_name();
        return $path;
    }

    public function get_merged_protein_file_path() {
        $path = $this->get_identify_output_path() . "/" .
            self::get_protein_file_name();
        return $path;
    }
    public function get_merged_cluster_file_path() {
        $path = $this->get_identify_output_path() . "/" .
            self::get_cluster_file_name();
        return $path;
    }
    public function get_merged_normalized_protein_file_path() {
        $path = $this->get_identify_output_path() . "/" .
            self::get_normalized_protein_file_name();
        return $path;
    }
    public function get_merged_normalized_cluster_file_path() {
        $path = $this->get_identify_output_path() . "/" .
            self::get_normalized_cluster_file_name();
        return $path;
    }

    public static function get_protein_file_name() {
        return "protein_abundance.txt";
    }
    public static function get_normalized_protein_file_name() {
        return "protein_abundance_normalized.txt";
    }
    public static function get_cluster_file_name() {
        return "cluster_abundance.txt";
    }
    public static function get_normalized_cluster_file_name() {
        return "cluster_abundance_normalized.txt";
    }

    public function get_ssn_http_path() {
        $path = 
            $this->identify_id . "/" .
            settings::get_rel_output_dir() . "/" .
            $this->get_ssn_name();
        return $path;
    }
    public function get_zip_ssn_http_path() {
        $path = $this->get_ssn_http_path() . ".zip";
        return $path;
    }

    private function get_ssn_name() {
        $id = $this->identify_id;
        $name = preg_replace("/.zip$/", ".xgmml", $this->filename);
        $name = preg_replace("/.xgmml$/", "_quantify.xgmml", $name);
        return "${id}_$name";
    }
    
    public function get_merged_ssn_zip_file_path() {
        $path = $this->get_merged_ssn_file_path() . ".zip";
        return $path;
    }
    private function get_merged_ssn_file_path() {
        $path = $this->get_identify_output_path() . "/" .
            $this->get_ssn_name();
        return $path;
    }

    public function get_merged_ssn_file_size() {
        $file = $this->get_merged_ssn_file_path();
        if (file_exists($file))
            return filesize($file);
        else
            return 0;
    }
    public function get_merged_ssn_zip_file_size() {
        $file = $this->get_merged_ssn_zip_file_path();
        if (file_exists($file))
            return filesize($file);
        else
            return 0;
    }

    private function get_input_identify_ssn_path() {
        $id_ssn_name = identify::make_ssn_name($this->identify_id, $this->filename);
        $path = $this->get_identify_output_path() . "/" .
            $id_ssn_name;
        return $path;
    }

    protected function get_table_name() {
        return job_types::Quantify;
    }
}

?>
