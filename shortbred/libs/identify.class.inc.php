<?php

require_once("functions.class.inc.php");
require_once("settings.class.inc.php");
require_once("job_shared.class.inc.php");

// ShortBRED-Identify
class identify extends job_shared {

    private $is_debug = false;
    private $loaded = false;
    private $db;
    private $error_message = "";
    //$search_type is defined in job_shared
    private $cdhit_sid = "";
    private $ref_db = "";
    private $cons_thresh = "";
    private $diamond_sens = "";
    private $db_mod = "";
    private $est_id = 0;


    public function get_cdhit_sid() {
        $sid = $this->cdhit_sid;
        if ($sid)
            return $sid;
        else
            return job_shared::DEFAULT_CDHIT_SID;
    }
    public function get_ref_db() {
        return $this->ref_db;
    }
    public function get_consensus_threshold() {
        return $this->cons_thresh;
    }
    public function get_diamond_sensitivity() {
        $sens = $this->diamond_sens;
        if ($sens)
            return $sens;
        else
            return ""; //job_shared::DEFAULT_DIAMOND_SENSITIVITY;
    }



    public function __construct($db, $job_id, $is_debug = false) {
        parent::__construct($db);
        $this->set_id($job_id);
        $this->db = $db;
        $this->is_debug = $is_debug;
        $this->load_job();
    }

    public static function create($db, $email, $tmp_filename, $filename, $create_params) {
        $info = self::create_shared($db, $email, $tmp_filename, $filename, "", false, $create_params);
        return $info;
    }

    // $parent_id > 0 and $true_copy = true makes a true copy of the job in the database (i.e. no
    // identify step is run at all, no SSN is provided).
    //
    // $parent_id > 0 and $true_copy = false creates a new job based
    // on the parent job, but re-runs parts of the identify step with the new SSN. 
    private static function create_shared($db, $email, $tmp_filename, $filename, $parent_id, $true_copy, $create_params) {

        // Sanitize filename
        $filename = preg_replace("([^a-zA-Z0-9\-_\.])", '', $filename);

        $key = functions::generate_key();
        $status = $true_copy ? __FINISH__ : __NEW__;
        
        $insert_array = array(
            'identify_email' => $email,
            'identify_key' => $key,
            'identify_status' => $status,
            'identify_time_created' => self::get_current_time(),
        );
        if ($true_copy && $parent_id)
            $insert_array['identify_copy_id'] = $parent_id;
        elseif ($parent_id)
            $insert_array['identify_parent_id'] = $parent_id;

        $parms_array = array('identify_filename' => $filename);

        if (isset($create_params['min_seq_len'])) {
            $min_seq_len = $create_params['min_seq_len'];
            if (is_numeric($min_seq_len) && $min_seq_len >= 0 && $min_seq_len < 1000000000)
                $parms_array['identify_min_seq_len'] = $create_params['min_seq_len'];
        }

        if (isset($create_params['max_seq_len'])) {
            $max_seq_len = $create_params['max_seq_len'];
            if (is_numeric($max_seq_len) && $max_seq_len >= 0 && $max_seq_len < 1000000000)
                $parms_array['identify_max_seq_len'] = $create_params['max_seq_len'];
        }

        if (isset($create_params['search_type'])) {
            $search_type = strtolower($create_params['search_type']);
            if ($search_type == "diamond" || $search_type == "blast" || $search_type == "v2-blast")
                $parms_array['identify_search_type'] = $search_type;
        }

        if (isset($create_params['ref_db'])) {
            $ref_db = $create_params['ref_db'];
            if ($ref_db == job_shared::REFDB_UNIPROT || $ref_db == job_shared::REFDB_UNIREF50 || $ref_db == job_shared::REFDB_UNIREF90) 
                $parms_array['identify_ref_db'] = $create_params['ref_db'];
        }

        if (isset($create_params['cdhit_sid'])) {
            $cdhit_sid = $create_params['cdhit_sid'];
            if (is_numeric($cdhit_sid) && $cdhit_sid >= 10 && $cdhit_sid <= 100)
                $parms_array['identify_cdhit_sid'] = $cdhit_sid;
        }

        if (isset($create_params['cons_thresh'])) {
            $cons_thresh = $create_params['cons_thresh'];
            if (is_numeric($cons_thresh) && $cons_thresh >= 10 && $cons_thresh <= 100)
                $parms_array['identify_cons_thresh'] = $cons_thresh;
        }

        if (isset($create_params['diamond_sens'])) {
            $diamond_sens = $create_params['diamond_sens'];
            if ($diamond_sens == "normal" || $diamond_sens == "sensitive" || $diamond_sens == "more-sensitive")
                $parms_array['identify_diamond_sens'] = $diamond_sens;
        }

        if (isset($create_params['db_mod'])) {
            $db_mod = $create_params['db_mod'];
            if (preg_match("/^[A-Z0-9]{4}/", $db_mod))
                $parms_array['identify_db_mod'] = $db_mod;
        }

        $est_id = 0;
        if (isset($create_params['est_id'])) {
            $est_id = $create_params['est_id'];
            $parms_array['est_id'] = $est_id;
        }

        $insert_array['identify_params'] = global_functions::encode_object($parms_array);

        $new_id = $db->build_insert('identify', $insert_array);

        if ($new_id) {
            if (!$est_id && (!$parent_id || $tmp_filename)) {
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
        
        $params = global_functions::decode_object($result[0]['identify_params']);
        $search_type = $params['identify_search_type'];

        $info = self::create_shared($db, $email, $tmp_filename, $filename, $parent_id, false, "", $search_type);
        return $info;
    }

    public static function create_copy($db, $email, $parent_id, $parent_key) {
        $sql = "SELECT * FROM identify WHERE identify_id='$parent_id' AND identify_key='$parent_key'";
        $result = $db->query($sql);
        if (!$result)
            return false;
        
        $params = global_functions::decode_object($result[0]['identify_params']);
        $filename = $params['identify_filename'];
        $search_type = $params['identify_search_type'];

        $info = self::create_shared($db, $email, "", $filename, $parent_id, true, "", "", $search_type);
        return $info;
    }

    public static function create_from_est($db, $email, $create_params, $est_id, $est_key) {
        // EST job is verified at this point
        
        $create_params["est_id"] = $est_id;
        $filename = functions::get_est_job_filename($db, $est_id, $est_key);

        $info = self::create_shared($db, $email, "", $filename, "", false, $create_params);
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
            print $this->est_id . " " . $ssn_path . "\n";
            if ((!$parent_id || file_exists($ssn_path)) && !file_exists($out_dir)) {
                mkdir($out_dir);
                chdir($out_dir);
                copy($ssn_path, $target_ssn_path);
            }
        }

        $sched = settings::get_cluster_scheduler();
        $queue = settings::get_normal_queue();
        $memQueue = settings::get_memory_queue();
        $sb_module = settings::get_shortbred_blast_module();
        $search_type = $this->get_search_type();
        $min_seq_len = $this->get_min_seq_len();
        $max_seq_len = $this->get_max_seq_len();
        if ($search_type == "diamond" || $search_type == "v2-blast")
            $sb_module = settings::get_shortbred_diamond_module();

        $exec = "source /etc/profile\n";
        if ($this->db_mod)
            $exec .= "module load " . $this->db_mod . "\n";
        else
            $exec .= "module load " . settings::get_efidb_module() . "\n";
        $exec .= "module load $sb_module\n";
        $exec .= "$script";
        $exec .= " -ssn-in $target_ssn_path";
        $exec .= " -ssn-out-name " . $this->get_ssn_name();
        $exec .= " -cdhit-out-name cdhit.txt";
        $exec .= " -tmpdir " . settings::get_rel_output_dir();
        $exec .= " -job-id " . $id;
        $exec .= " -np " . settings::get_num_processors();
        $exec .= " -queue $queue";
        $exec .= " -mem-queue $memQueue";
        if ($search_type == "diamond")
            $exec .= " -search-type " . $search_type;
        if ($sched)
            $exec .= " -scheduler $sched";
        if ($parent_id)
            $exec .= " -parent-job-id $parent_id";
        if ($min_seq_len)
            $exec .= " -min-seq-len " . $min_seq_len;
        if ($max_seq_len)
            $exec .= " -max-seq-len " . $max_seq_len;
        if ($this->ref_db)
            $exec .= " -ref-db " . $this->ref_db;
        if ($this->cdhit_sid)
            $exec .= " -cdhit-sid " . $this->cdhit_sid;
        if ($this->cons_thresh)
            $exec .= " -cons-thresh " . $this->cons_thresh;
        if ($this->diamond_sens)
            $exec .= " -diamond-sens " . $this->diamond_sens;

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
        
        $params = global_functions::decode_object($result['identify_params']);

        if (isset($result['identify_parent_id'])) {
            $sql = "SELECT identify_params FROM identify WHERE identify_id='" . $result['identify_parent_id'] . "'";
            $parent_result = $this->db->query($sql);
            $params2 = global_functions::decode_object($parent_result[0]['identify_params']);
            if (isset($params2['est_id']))
                unset($params2['est_id']);
            $params = array_merge($params2, $params);
        }

        $this->load_job_shared($result, $params);

        $this->set_email($result['identify_email']);
        $this->set_key($result['identify_key']);
        $this->ref_db = isset($params['identify_ref_db']) ? $params['identify_ref_db'] : "";
        $this->cdhit_sid = isset($params['identify_cdhit_sid']) ? $params['identify_cdhit_sid'] : "";
        $this->cons_thresh = isset($params['identify_cons_thresh']) ? $params['identify_cons_thresh'] : "";
        $this->diamond_sens = ($this->get_search_type() == "diamond" && isset($params['identify_diamond_sens'])) ? $params['identify_diamond_sens'] : "";
        $this->est_id = isset($params['est_id']) ? $params['est_id'] : "";
        
        $db_mod = isset($params['identify_db_mod']) ? $params['identify_db_mod'] : "";
        if ($db_mod) {
            // Get the actual module not the alias.
            $mod_info = global_settings::get_database_modules();
            foreach ($mod_info as $mod) {
                if ($mod[1] == $db_mod) {
                    $db_mod = $mod[0];
                }
            }
        }
        $this->db_mod = $db_mod;

        $this->loaded = true;
        return true;
    }


    public function check_if_job_is_done() {

        $id = $this->get_id();
        $out_dir = settings::get_output_dir() . "/" . $id;
        $res_dir = $out_dir . "/" . settings::get_rel_output_dir();

        $fasta_failed = "$res_dir/get_fasta.failed";
        $clnum_failed = "$res_dir/ssn_cl_num.failed"; // If the input SSN doesn't have "Cluster Number" then it aborts.
        $finish_file = "$res_dir/job.completed";

        $is_fasta_error = file_exists($fasta_failed);
        $is_finished = file_exists($finish_file);
        $is_clnum_error = file_exists($clnum_failed);
        $is_running = $this->is_job_running();

        $result = 1;
        if ($is_fasta_error) {
            $result = 0;
            $this->set_status(__FAILED__);
            $this->set_time_completed();
            $this->email_failure("Unable to find any accession IDs in the SSN.  Is it in the correct format?");
        } elseif ($is_clnum_error) {
            $result = 0;
            $this->set_status(__FAILED__);
            $this->set_time_completed();
            $this->email_failure("The input SSN must have the Cluster Number attribute; run it through the Color SSN "
                                 . "utility or GNT to number the clusters.");
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
        $subject = "EFI-CGFP - SSN submission received";
        return $subject;
    }

    protected function get_email_started_message() {
        $plain_email = "";
        $plain_email .= "The SSN that is the input needed to run ShortBRED has been received and is ";
        $plain_email .= "being processed." . $this->eol . $this->eol;
        return $plain_email;
    }

    protected function get_email_cancelled_subject() {
        $subject = "EFI-CGFP - Job cancelled";
        return $subject;
    }

    protected function get_email_cancelled_message() {
        $plain_email = "";
        $plain_email .= "The ShortBRED-Identify job was cancelled." . $this->eol . $this->eol;
        return $plain_email;
    }

    protected function get_email_failure_subject() {
        $subject = "EFI-CGFP - Computation failed";
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
        $subject = "EFI-CGFP - ShortBRED computation completed";
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
        $message = "Uploaded Filename: " . $this->get_filename() . $this->eol;
        return $info;
    }








    public function get_results_path($parent_id = 0) {
        $id = $parent_id > 0 ? $parent_id : $this->get_id();
        $out_dir = settings::get_output_dir() . "/" . $id;
        $res_dir = $out_dir . "/" . settings::get_rel_output_dir();
        //TODO: get rid of this test in production
        if (!file_exists($res_dir))
            $res_dir = $out_dir . "/" . settings::get_rel_output_dir_legacy();
        return $res_dir;
    }

    public function get_ssn_http_path() {
        $test_path = settings::get_output_dir() . "/" . $this->identify_id . "/" . settings::get_rel_output_dir();
        $res_dir = settings::get_rel_output_dir();
        //TODO: remove for production
        if (!file_exists($test_path))
            $res_dir = settings::get_rel_output_dir_legacy();
        $id = $this->get_id();
        return "$id/$res_dir/" . $this->get_ssn_name();
    }
    
    public function get_output_ssn_zip_http_path() {
        $path = $this->get_ssn_http_path() . ".zip";
        return $path;
    }

    private function get_ssn_name() {
        return self::make_ssn_name($this->get_id(), $this->get_filename());
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
        if ($this->est_id) {
            $dir = global_functions::get_est_job_results_path($this->est_id);
            $file_path = $dir . "/" . $this->get_filename();
            return $file_path;
        } else {
            $uploads_dir = settings::get_uploads_dir();
            return $uploads_dir . "/" . $this->get_id() . "." . pathinfo($this->get_filename(), PATHINFO_EXTENSION);
        }
    }

    public function get_output_ssn_file_size() {
        $path = $this->get_output_ssn_path();
        return self::get_web_filesize($path);
    }

    public function get_output_ssn_zip_file_size() {
        $path = $this->get_output_ssn_zip_file_path();
        return self::get_web_filesize($path);
    }

    public function get_output_ssn_zip_file_path() {
        $path = $this->get_output_ssn_path() . ".zip";
        return $path;
    }

    protected function get_table_name() {
        return job_types::Identify;
    }
    
    public function get_metadata() {

        $parent_metadata = array();

        $parent_id = $this->get_parent_id();
        if ($parent_id) {
            $res_dir = $this->get_results_path($parent_id);
            $meta_file = "$res_dir/metadata.tab";
            if (file_exists($meta_file))
                $parent_metadata = $this->get_metadata_shared($meta_file);
        }

        $job_metadata = array();

        $res_dir = $this->get_results_path();
        $meta_file = "$res_dir/metadata.tab";
        if (file_exists($meta_file))
            $job_metadata = $this->get_metadata_shared($meta_file);

        foreach ($job_metadata as $idx => $data) {
            $parent_metadata[$idx] = $data;
        }

        return $parent_metadata;
    }

    public function get_metadata_swissprot_singles_file_path() {
        $res_dir = $this->get_results_path();
        return $this->get_metadata_swissprot_singles_file_shared($res_dir);
    }

    public function get_metadata_swissprot_clusters_file_path() {
        $res_dir = $this->get_results_path();
        return $this->get_metadata_swissprot_clusters_file_shared($res_dir);
    }

    public function get_metadata_cluster_sizes_file_path() {
        $res_dir = $this->get_results_path();
        return $this->get_metadata_cluster_sizes_file_shared($res_dir);
    }
    
    public function get_cdhit_file_path() {
        $res_dir = $this->get_results_path();
        return $this->get_cdhit_file_shared($res_dir);
    }

    public function get_marker_file_path() {
        $id = $this->get_id();
        $parent_id = $this->get_parent_id();
        if ($parent_id)
            $id = $parent_id;

        $out_dir = settings::get_output_dir() . "/" . $id;
        $res_dir = $out_dir . "/" . settings::get_rel_output_dir();
        //TODO: get rid of this test in production
        if (!file_exists($res_dir))
            $res_dir = $out_dir . "/" . settings::get_rel_output_dir_legacy();

        return $this->get_marker_file_shared($res_dir);
    }

}

?>
