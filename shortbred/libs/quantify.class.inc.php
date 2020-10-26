<?php

require_once(__DIR__."/functions.class.inc.php");
require_once(__DIR__."/settings.class.inc.php");
require_once(__DIR__."/quantify_shared.class.inc.php");
require_once(__DIR__."/identify.class.inc.php");


// ShortBRED-Identify
class quantify extends quantify_shared {

    private $is_debug = false;
    private $loaded = false;
    private $db;
    private $error_message = "";


    
    // job_id represents the analysis id, not the identify id.
    public function __construct($db, $job_id, $is_example = false, $is_debug = false) {
        parent::__construct($db, $is_example);
        $this->set_id($job_id);
        $this->db = $db;
        $this->is_debug = $is_debug;

        if ($this->is_example) {
            $this->init_example();
        }

        $this->load_job();
    }

    private function init_example() {
        $config_file = example_config::get_config_file();
        $config = example_config::get_config($config_file);
        $this->q_table = example_config::get_cgfp_quantify_table($config);
        $this->id_table = example_config::get_cgfp_identify_table($config);
        $this->ex_data_dir = example_config::get_cgfp_data_dir($config);
    }

    public static function create($db, $identify_id, $metagenome_ids, $search_type, $mg_db_id = "", $job_name = "") {
        $info = self::create_shared($db, $identify_id, $metagenome_ids, "", $search_type, $mg_db_id, $job_name);
        return $info;
    }

    private static function create_shared($db, $identify_id, $metagenome_ids, $parent_id, $search_type, $mg_db_id, $job_name) {
        $insert_array = array(
            "quantify_identify_id" => $identify_id,
            "quantify_status" => __NEW__,
            "quantify_time_created" => self::get_current_time(),
        );
        if ($parent_id)
            $insert_array["quantify_parent_id"] = $parent_id; // the parent QUANTIFY job ID, not identify

        $params_array = array("quantify_metagenome_ids" => $metagenome_ids);
        if ($search_type) {
            $search_type = strtolower($search_type);
            if ($search_type == "diamond" || $search_type == "usearch")
                $params_array["quantify_search_type"] = $search_type;
        }
        if ($mg_db_id)
            $params_array["quantify_mg_db_id"] = $mg_db_id;
        if ($job_name)
            $params_array["quantify_job_name"] = $job_name;
        $params = global_functions::encode_object($params_array);

        $insert_array["quantify_params"] = $params;

        $result = $db->build_insert("quantify", $insert_array);
        if (!$result)
            return false;

        $info = array("id" => $result);
        return $info;
    }
    
    public static function create_copy($db, $parent_quantify_id, $identify_id) {
        $job = new quantify($db, $parent_quantify_id);
        $mg_id_array = $job->get_metagenome_ids();
        $mg_ids = implode(",", $mg_id_array);
        $search_type = $job->get_search_type();
        $mg_db_id = $job->get_metagenome_db_id();
        $job_name = $job->get_job_name();

        if (!$mg_ids)
            return false;

        $info = self::create_shared($db, $identify_id, $mg_ids, $parent_quantify_id, $search_type, $mg_db_id, $job_name);
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
        print "$id_out_dir\n";

        $sched = settings::get_cluster_scheduler();
        $queue = settings::get_normal_queue();
        $memQueue = settings::get_memory_queue();
        $sb_module = settings::get_shortbred_blast_module();
        $search_type = $this->get_search_type();
        if ($search_type == "diamond")
            $sb_module = settings::get_shortbred_diamond_module();
        $parent_quantify_id = $this->get_parent_id();
        $parent_identify_id = "";
        if ($parent_quantify_id) {
            $sql = "SELECT quantify_identify_id FROM quantify WHERE quantify_id = $parent_quantify_id";
            $result = $this->db->query($sql);
            if ($result) {
                $parent_identify_id = $result[0]["quantify_identify_id"];
            }
        }

        $mg_db_list = metagenome_db_manager::get_valid_dbs();
        if (isset($mg_db_list[$this->mg_db_id]))
            $mg_db = $mg_db_list[$this->mg_db_id];

        $np = settings::get_num_quantify_processors() ? settings::get_num_quantify_processors() : settings::get_num_processors();
        $exec = "source /etc/profile\n";
        $exec .= "module load " . settings::get_efidb_module() . "\n";
        $exec .= "module load $sb_module\n";
        $exec .= "$script";
        $exec .= " -metagenome-db " . $mg_db;
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
        $exec .= " -protein-genome-norm " . self::get_genome_normalized_protein_file_name();
        $exec .= " -cluster-genome-norm " . self::get_genome_normalized_cluster_file_name();
        $exec .= " -np $np";
        $exec .= " -queue $queue";
        $exec .= " -mem-queue $memQueue";
        if ($sched)
            $exec .= " -scheduler $sched";
        if ($parent_quantify_id && $parent_identify_id) {
            $exec .= " -parent-quantify-id $parent_quantify_id";
            $exec .= " -parent-identify-id $parent_identify_id";
        }
        if ($search_type == "diamond")
            $exec .= " -search-type " . $search_type;

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
        $q_table = $this->is_example ? $this->q_table : "quantify";
        $id_table = $this->is_example ? $this->id_table : "identify";

        $sql = "SELECT $q_table.*, identify_email, identify_key, identify_params, identify_parent_id FROM $q_table ";
        $sql .= "JOIN $id_table ON $q_table.quantify_identify_id = $id_table.identify_id WHERE quantify_id='" . $this->get_id() . "'";
        $result = $this->db->query($sql);

        if (!$result) {
            return false;
        }

        $result = $result[0];

        $qparams = global_functions::decode_object($result['quantify_params']);
        $iparams = global_functions::decode_object($result['identify_params']);
        $params = array_merge($qparams, $iparams);

        if (isset($result['identify_parent_id']) && $result['identify_parent_id']) {
            $this->identify_parent_id = $result['identify_parent_id'];
            $sql = "SELECT identify_params FROM $id_table WHERE identify_id='" . $result['identify_parent_id'] . "'";
            $parent_result = $this->db->query($sql);
            $iparams2 = global_functions::decode_object($parent_result[0]['identify_params']);
            $params = array_merge($qparams, $iparams2, $iparams);
        }

        $this->load_job_shared($result, $params);

        $this->identify_id = $result['quantify_identify_id'];
        $this->set_email($result['identify_email']);
        $this->set_key($result['identify_key']);
        
        $mg_ids = $qparams['quantify_metagenome_ids'];
        $this->metagenome_ids = explode(",", $mg_ids);

        $this->mg_db_id = isset($qparams['quantify_mg_db_id']) ? $qparams['quantify_mg_db_id'] : 0;
        $this->job_name = isset($qparams['quantify_job_name']) ? $qparams['quantify_job_name'] : "";

        if (isset($params['identify_ref_db']))
            $this->ref_db = $params['identify_ref_db'];
        else
            $this->ref_db = job_shared::DEFAULT_REFDB;

        $this->identify_search_type = "";
        $this->identify_diamond_sens = "";
        $this->identify_cdhit_sid = "";
        if (settings::get_diamond_enabled()) {
            if (isset($params['identify_search_type']))
                $this->identify_search_type = $params['identify_search_type'];
            if ($this->get_search_type() != "diamond")
                $this->set_search_type("usearch");
            if (isset($params['identify_diamond_sens']))
                $this->identify_diamond_sens = $params['identify_diamond_sens'];
        }
        
        if ($this->identify_search_type != "diamond")
            $this->identify_diamond_sens = "";
        elseif (!$this->identify_diamond_sens)
            $this->identify_diamond_sens = job_shared::DEFAULT_DIAMOND_SENSITIVITY;

        if (isset($params['identify_cdhit_sid']))
            $this->identify_cdhit_sid = $params['identify_cdhit_sid'];
        else
            $this->identify_cdhit_sid = job_shared::DEFAULT_CDHIT_SID;

        $this->loaded = true;
        return true;
    }


    public function check_if_job_is_done() {
        $qid = $this->get_id(); // quantify_id
        $id = $this->identify_id;
        $out_dir = settings::get_output_dir() . "/" . $id;
        $id_dir = $out_dir . "/" . settings::get_rel_output_dir();
        //TODO: remove for production
        if (!file_exists($id_dir))
            $id_dir = $out_dir . "/" . settings::get_rel_output_dir_legacy();
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
    // OVERRIDES
    //

    protected function get_quantify_res_dir() {
        $id = $this->get_id(); # quantify ID
        $res_dir = settings::get_quantify_rel_output_dir();
        return "$res_dir-$id";
    }
    public function get_identify_output_path($parent_id = 0) {
        $id = $parent_id ? $parent_id : $this->identify_id;
        if ($this->is_example)
            $out_dir = $this->ex_data_dir;
        else
            $out_dir = settings::get_output_dir();
        $base_path = "$out_dir/$id";
        $path = $base_path . "/" . settings::get_rel_output_dir();
        return $path;
    }
    protected function get_quantify_output_path() {
        $path = $this->get_identify_output_path() . "/" . $this->get_quantify_res_dir();
        return $path;
    }
    // LOCAL TO JOB
    protected function get_ssn_file_path_shared() {
        $path = $this->get_identify_output_path();
        $q_dir = $this->get_quantify_res_dir();
        $ssn = $this->get_ssn_name();

        $ssn_path = "$path/$q_dir/$ssn";
        if (file_exists($ssn_path))
            return $ssn_path;
        else
            return "$path/$ssn";
    }
    public function get_metadata() {
        $metadata = array();
        if ($this->identify_parent_id) {
            $res_dir = $this->get_identify_output_path($this->identify_parent_id);
            $meta_file = "$res_dir/metadata.tab";
            if (file_exists($meta_file))
                $metadata = $this->get_metadata_shared($meta_file, $this->identify_parent_id);
        }

        $res_dir = $this->get_identify_output_path();
        $meta_file = "$res_dir/metadata.tab";
        $id_metadata = array();
        if (file_exists($meta_file))
            $id_metadata = $this->get_metadata_shared($meta_file, $this->identify_id);

        foreach ($id_metadata as $idx => $data) {
            $metadata[$idx] = $data;
        }

        $q_res_dir = $this->get_quantify_res_dir();
        $q_meta_file = "$res_dir/$q_res_dir/metadata.tab";
        if (file_exists($q_meta_file)) {
            $q_metadata = $this->get_metadata_shared($q_meta_file);
            foreach ($q_metadata as $key => $value) {
                $metadata[$key] = $value;
            }
        }

        #TODO: add quantify metadata

        return $metadata;
    }











    public function get_ssn_http_path() {
        $test_path = settings::get_output_dir() . "/" . $this->identify_id . "/" . settings::get_rel_output_dir();
        $rel_dir = settings::get_rel_output_dir();
        //TODO: remove for production
        if (!file_exists($test_path))
            $rel_dir = settings::get_rel_output_dir_legacy();

        $path = $this->identify_id . "/" . $rel_dir . "/";
        $q_dir = $this->get_quantify_res_dir();

        $ssn = $this->get_ssn_name();
        $local_ssn = $this->get_quantify_output_path() . "/" . $ssn;

        if (file_exists($local_ssn)) {
            return "$path/$q_dir/$ssn";
        } else {
            return "$path/$ssn";
        }
    }
    public function get_zip_ssn_http_path() {
        $path = $this->get_ssn_http_path() . ".zip";
        return $path;
    }

    private function get_ssn_name() {
        $id = $this->identify_id;
        $name = preg_replace("/.zip$/", ".xgmml", $this->get_filename());
        $name = preg_replace("/.xgmml$/", "_quantify.xgmml", $name);
        return "${id}_$name";
    }

    private function get_input_identify_ssn_path() {
        $id_ssn_name = identify::make_ssn_name($this->identify_id, $this->get_filename());
        $path = $this->get_identify_output_path() . "/" .
            $id_ssn_name;
        return $path;
    }
}

?>
