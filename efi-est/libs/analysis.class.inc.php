<?php

require_once(__DIR__ . "/../includes/main.inc.php");
require_once(__DIR__ . "/est_shared.class.inc.php");

class analysis extends est_shared {

    ////////////////Private Variables//////////

    private $db; //mysql database object
    private $id;
    private $key;
    private $status;
    private $filter_value;
    private $name;
    private $minimum;
    private $maximum;
    private $pbs_number;
    private $blast_id;
    private $generate_id;
    private $filter;
    private $finish_file = "stats.tab.completed";
    private $sequence_file = "sequences.fa";
    private $stats_file = "stats.tab";
    protected $output_dir = "output";
    private $num_pbs_jobs = 16;
    private $filter_sequences;
    private $db_version;
    private $length_overlap;
    private $custom_clustering;
    private $custom_filename = "custom_cluster.txt";
    private $parent_id = 0; // The parent ID of the generate job that this analysis job is associated with
    private $cdhit_opt = "";
    private $job_type = "";
    private $uniref = 0;
    private $db_mod = "";
    private $use_min_edge_attr = false;
    private $use_min_node_attr = false;
    protected $is_sticky = false;

    private $is_example = false;
    private $analysis_table = "analysis";
    private $generate_table = "generate";
    private $ex_data_dir = "";

    ///////////////Public Functions///////////

    public function __construct($db, $id = 0, $is_example = false) {
        parent::__construct($db, "analysis");

        $this->db = $db;

        if ($is_example) {
            $this->is_example = true;
            $this->init_example($id);
        }

        if ($id)
            $this->load_analysis($id);
        $this->beta = global_settings::get_release_status();
    }

    private function init_example($id) {
        $config_file = example_config::get_config_file();
        $config = example_config::get_config($config_file);
        $this->analysis_table = example_config::get_est_analysis_table($config);
        $this->generate_table = example_config::get_est_generate_table($config);
        $this->ex_data_dir = example_config::get_est_data_dir($config);
    }

    public function __destruct() {
    }

    public function get_status() { return $this->status; }
    public function get_id() { return $this->id; }
    public function get_key() { return $this->key; }
    public function get_generate_id() { return $this->generate_id; }
    public function get_filter_value() { return $this->filter_value; }
    public function get_min_length() { return $this->minimum; }
    public function get_max_length() { return $this->maximum; }
    public function get_pbs_number() { return $this->pbs_number; }
    public function get_name() { return $this->name; }
    public function get_filter() { return $this->filter; }
    public function get_finish_file() { return $this->finish_file; }
    public function get_filter_sequences() { return $filter_sequences; }
    public function get_sequence_file() { return $this->sequence_file; }
    public function get_db_version() { return $this->db_version; }
    public function get_output_dir() { return $this->get_generate_id() . "/" . $this->output_dir; }
    public function get_min_option_nice() {
        if ($this->use_min_node_attr && $this->use_min_edge_attr)
            return "Node + Edge";
        elseif ($this->use_min_node_attr)
            return "Node";
        elseif ($this->use_min_edge_attr)
            return "Edge";
        else
            return "";
    }
    public function get_cdhit_method_nice() {
        if ($this->cdhit_opt == "sb")
            return "ShortBRED";
        elseif ($this->cdhit_opt == "est+")
            return "EST/High Accuracy";
        else
            return "EST";
    }
    
    public function set_pbs_number($pbs_number) {
        $sql = "UPDATE analysis SET analysis_pbs_number='" . $pbs_number . "' ";
        $sql .= "WHERE analysis_id='" . $this->get_id() . "'";
        $this->db->non_select_query($sql);
        $this->pbs_number = $pbs_number;
    }


    public function set_num_sequences_post_filter() {
        $num_seq = $this->get_num_sequences_post_filter();

        $sql = "UPDATE analysis ";
        $sql .= "SET analysis_filter_sequences='" . $num_seq . "' ";
        $sql .= "WHERE analysis_id='" . $this->get_id() . " LIMIT 1";
        $result = $this->db->non_select_query($sql);
        if ($result) {
            $this->filter_sequences = $num_seq;
            return true;
        }
        return false;
    }		

    public function get_num_sequences_post_filter() {
        $root_dir = functions::get_results_dir();
        $directory = $root_dir . "/" . $this->get_output_dir() . "/" . $this->get_network_dir();
        $full_path = $directory . "/" . $this->get_sequence_file();
        $num_seq = 0;
        if (file_exists($full_path)) {

            $exec = "cat " . $full_path . " | grep '>' | wc -l";
            $output = exec($exec);
            $output = trim(rtrim($output));
            list($num_seq,) = explode(" ",$output);

        }
        return $num_seq;
    }

    public function set_status($status) {
        $sql = "UPDATE analysis ";
        $sql .= "SET analysis_status='" . $status . "' ";
        $sql .= "WHERE analysis_id='" . $this->get_id() . "' LIMIT 1";
        $result = $this->db->non_select_query($sql);
        if ($result) {
            $this->status = $status;
        }
    }

    public function create($generate_id, $filter_value, $name, $minimum, $maximum, $customFile = "", $cdhitOpt = "", $filter = "eval", $min_node_attr = 0, $min_edge_attr = 0) {
        $errors = false;
        $message = "";		

        $hasCustomClustering = 0;
        if ($customFile && file_exists($customFile)) {
            $uploadDir = functions::get_uploads_dir();
            $destPath = "$uploadDir/custom_cluster_$generate_id.txt";
            move_uploaded_file($customFile, $destPath);
            $hasCustomClustering = 1;
        }

        if (!$this->verify_length($minimum,$maximum)) {
            $message = "<br><b>Please verify the minimum and maximum lengths.</b>";
            $errors = true;
        }
        
        $name = $this->verify_network_name($name);
        if ($name === false) {
            $message .= "<br><b>Please verify the network name.</b>";
            $message .= "<br><b>It can contain only letters, numbers, dash, and underscore.</b>";
            $errors = true;
        }
        
        if (!$hasCustomClustering) {
            if ($filter == "eval" && !$this->verify_evalue($filter_value)) {
                $message .= "<br><b>Please verify the alignment score.</b>";
                $errors = true;
            } elseif ($filter == "pid" && !$this->verify_percent_id($filter_value)) {
                $message .= "<br><b>Please verify the percent identity.</b>";
                $errors = true;
            } elseif ($filter == "bit" && !$this->verify_bit_score($filter_value)) {
                $message .= "<br><b>Please verify the bit score.</b>";
                $errors = true;
            } elseif ($filter == "custom") {
                $message .= "<br><b>The custom cluster input file is invalid.</b>";
                $errors = true;
            } elseif ($filter != "eval" and $filter != "pid" and $filter != "custom" and $filter != "bit") {
                $message .= "<br><b>An unknown filter type was specified.</b>";
                $errors = true;
            }
        }

        if (!$errors) {
            $params = array();
            if ($min_node_attr)
                $params['use_min_node_attr'] = 1;
            if ($min_edge_attr)
                $params['use_min_edge_attr'] = 1;
            $insert_array = array(
                'analysis_generate_id'=>$generate_id,
                'analysis_status' => __NEW__,
                'analysis_evalue'=>$filter_value,
                'analysis_name'=>$name,
                'analysis_min_length'=>$minimum,
                'analysis_max_length'=>$maximum,
                'analysis_filter'=>$filter,
                'analysis_custom_cluster'=>$hasCustomClustering,
            );
            if ($cdhitOpt)
                $insert_array['analysis_cdhit_opt'] = $cdhitOpt;
            $insert_array['analysis_params'] = global_functions::encode_object($params);

            $result = $this->db->build_insert("analysis",$insert_array);
            if ($result) {
                return array('RESULT'=>true,'id'=>$result);
            } else {
                $message = "Unknown database error";
            }
        }
        return array('RESULT'=>!$errors,'MESSAGE'=>$message);
    }

    public function check_pbs_running() {
        $sched = strtolower(functions::get_cluster_scheduler());
        $jobNum = $this->get_pbs_number();
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

    public function check_finish_file() {
        $directory = functions::get_results_dir() . "/".  $this->get_output_dir();
        $directory .= "/" . $this->get_network_dir();
        $full_path = $directory . "/" . $this->get_finish_file();
        return file_exists($full_path);
    }

    public function get_zip_file_size($ssn_file) {
        $results_dir = $this->get_results_dir();
        $file = $results_dir . "/" . $this->get_output_dir();
        $file .= "/" . $this->get_network_dir() . "/" . $ssn_file . ".zip";
        if (file_exists($file))
            return filesize($file);
        else
            return 0;
    }

    public function get_network_stats() {
        $results_dir = $this->get_results_dir();
        $file = $results_dir . "/" . $this->get_output_dir();
        $file .= "/" . $this->get_network_dir() . "/" . $this->stats_file;
        $file_handle = @fopen($file,"r") or die("Error opening $file " . $this->stats_file . "\n");
        $i = 0; 
        $stats_array = array();
        $keys = array('File','Nodes','Edges','Size');
        $row = 0;
        while (($data = fgetcsv($file_handle,0,"\t")) !== FALSE) {

            $stats_row = array();
            if ($row == 1) {
                $result = array_splice($data,1,1);
                $stats_row = array_combine($keys, $data);
            } elseif ($row > 1) {
                $stats_row = array_combine($keys, $data);
            }

            if (count($stats_row)) {
                $raw_file = $stats_row["File"];
                $percent_identity = substr($raw_file, strrpos($raw_file, "-") + 1);
                $percent_identity = substr($percent_identity, 0, strrpos($percent_identity, "_"));
                $percent_identity = str_replace(".", "", $percent_identity);

                $rel_path = $this->get_output_dir() . "/" . $this->get_network_dir() . "/" . $raw_file;
                $stats_row["RelPath"] = $rel_path;
                $stats_row["PctId"] = $row == 1 ? "full" : $percent_identity;

                array_push($stats_array, $stats_row);
            }

            $row++;
        }
        fclose($file_handle);
        return $stats_array;
    }

    private function get_results_dir() {
        if ($this->is_example) {
            return $this->ex_data_dir;
        } else {
            return functions::get_results_dir();
        }
    }

    public function download_network($file) {
        $root_dir = $this->get_results_dir();	
        $directory = $root_dir . "/" . $this->get_output_dir() . "/" . $this->get_network_dir();
        $full_path = $directory . "/" . $file;
        if (file_exists($full_path)) {
            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Content-Type: application-download');
            //header('COntent-Type: text/xml');
            header('Content-Disposition: attachment; filename="' . $file . '"');
            header('Content-Length: ' . filesize($full_path));
            ob_clean();
            readfile($full_path);
        }
        else {
            return false;
        }
    }

    public function get_stats_full_path() {
        $path = functions::get_web_root() . "/results/" . $this->get_output_dir() . "/" . $this->get_network_dir() . "/" . $this->stats_file;
        return $path;
    }
    
    public function run_job($is_debug = false) {
        if ($this->available_pbs_slots()) {

            $sched = functions::get_cluster_scheduler();

            //Setup Directories
            $job_dir = functions::get_results_dir() . "/" . $this->get_generate_id();
            $relative_output_dir = "output";
            $network_dir = $this->get_network_dir();
            $gen_output_dir = "$job_dir/$relative_output_dir";
            $full_output_dir = "$gen_output_dir/$network_dir";

            $parent_dir_opt = "";
            $parent_id = $this->parent_id;
            if ($parent_id > 0) {
                $parent_dir = functions::get_results_dir() . "/$parent_id/$relative_output_dir";
                if (!@file_exists($gen_output_dir)) {
                    mkdir($gen_output_dir, 0755, true); // This job is a parent
                }
                $parent_dir_opt = "-parent-id $parent_id -parent-dir $parent_dir";
            }

            if (@file_exists($full_output_dir)) {
                functions::rrmdir($full_output_dir);
            }
            if ($this->custom_clustering) {
                mkdir($full_output_dir, 0777, true); // recursively create
                $start_path = functions::get_uploads_dir() . "/custom_cluster_" . $this->get_generate_id() . ".txt";
                $end_path = "$full_output_dir/" . $this->custom_filename;
                copy($start_path, $end_path);
            }

            $cdhit_opt = "";
            if ($this->cdhit_opt) {
                if ($this->cdhit_opt == "sb" || $this->cdhit_opt == "est+")
                    $cdhit_opt = $this->cdhit_opt;
            }

            $current_dir = getcwd();
            if (file_exists($job_dir)) {
                chdir($job_dir);
                $exec = "source /etc/profile\n";
                $exec .= "module load " . functions::get_efi_module() . "\n";
                $exec .= "module load " . $this->db_mod . "\n";
                $exec .= "create_analysis_job.pl";
                $exec .= " -maxlen " . $this->get_max_length();
                $exec .= " -minlen " . $this->get_min_length();
                $exec .= " -filter " . $this->get_filter();
                $exec .= " -title " . $this->get_name();
                $exec .= " -minval " . $this->get_filter_value();
                $exec .= " -maxfull " . functions::get_maximum_number_ssn_nodes();
                $exec .= " -tmp " . $relative_output_dir;
                $exec .= " -job-id " . $this->get_generate_id();
                $exec .= " -queue " . functions::get_analyse_queue();
                if ($this->custom_clustering) {
                    $exec .= " -custom-cluster-file " . $this->custom_filename;
                    $exec .= " -custom-cluster-dir " . $network_dir;
                }
                if ($cdhit_opt)
                    $exec .= " -cdhit-opt $cdhit_opt";
                if ($this->uniref)
                    $exec .= " -uniref-version " . $this->uniref;
                if ($this->length_overlap)
                    $exec .= " -lengthdif " . $this->length_overlap . " ";
                if ($sched)
                    $exec .= " -scheduler " . $sched . " ";
                if ($parent_id > 0)
                    $exec .= " " . $parent_dir_opt;
                if ($this->job_type == "FASTA_ID" || $this->job_type == "FASTA")
                    $exec .= " -include-sequences";
                if ($this->use_min_edge_attr)
                    $exec .= " -use-min-edge-attr";
                if ($this->use_min_node_attr)
                    $exec .= " -use-anno-spec";

                $exec .= " 2>&1 ";

                $exit_status = 1;
                $output_array = array();

                functions::log_message($exec);
                $output = exec($exec,$output_array,$exit_status);

                chdir($current_dir);

                $output = trim(rtrim($output));
                if ($sched == "slurm")
                    $pbs_job_number = $output;
                else
                    $pbs_job_number = substr($output,0,strpos($output,"."));
                if ($pbs_job_number && !$exit_status) {
                    $this->set_pbs_number($pbs_job_number);
                    $this->set_time_started();
                    $this->set_status(__RUNNING__);
                    $this->email_started();
                    return array('RESULT'=>true,
                        'PBS_NUMBER'=>$pbs_job_number,
                        'EXIT_STATUS'=>$exit_status,
                        'MESSAGE'=>'Job Successfully Submitted');
                }
                else {
                    return array('RESULT'=>false,
                        'EXIT_STATUS'=>$exit_status,
                        'MESSAGE'=>$output_array[7]);
                }

            }
            else {
                return array('RESULT'=>false,'EXIT_STATUS'=>1,'MESSAGE'=>'Directory ' . $job_dir . ' does not exist');
            }

        }
        else {
            return array('RESULT'=>false,'EXIT_STATUS'=>1,'MESSAGE'=>'Queue is full');
        }
    }

    ///////////////Private Functions///////////


    private function load_analysis($id) {
        $a_table = $this->analysis_table;
        $g_table = $this->generate_table;
        $sql = "SELECT * FROM $a_table INNER JOIN $g_table ON analysis_generate_id = generate_id WHERE analysis_id='" . $id . "' ";
        $sql .= "LIMIT 1";
        $result = $this->db->query($sql);
        if ($result) {
            $this->id = $id;
            $this->generate_id = $result[0]['analysis_generate_id'];
            $this->pbs_number = $result[0]['analysis_pbs_number'];
            $this->filter_value = $result[0]['analysis_evalue'];
            $this->name = $result[0]['analysis_name'];
            $this->minimum = $result[0]['analysis_min_length'];
            $this->maximum = $result[0]['analysis_max_length'];
            $this->time_created = $result[0]['analysis_time_created'];
            $this->status = $result[0]['analysis_status'];
            $this->filter = $result[0]['analysis_filter'];
            $this->time_started = $result[0]['analysis_time_started'];
            $this->time_completed = $result[0]['analysis_time_completed'];
            $this->filter_sequences = $result[0]['analysis_filter_sequences'];
            $this->db_version = functions::decode_db_version($result[0]['generate_db_version']);
            $this->parent_id = $result[0]['generate_parent_id'];
            $this->job_type = $result[0]['generate_type'];
            $this->cdhit_opt = array_key_exists('analysis_cdhit_opt', $result[0]) ? $result[0]['analysis_cdhit_opt'] : "";
            $has_custom = array_key_exists('analysis_custom_cluster', $result[0]) &&
                            $result[0]['analysis_custom_cluster'] == 1;
            $this->custom_clustering = $has_custom;
            $email = $result[0]['generate_email'];
            $key = $result[0]['generate_key'];
            $this->is_sticky = functions::is_job_sticky($this->db, $this->generate_id, $email);
            $this->set_email($email);
            $this->key = $key;

            $gen_params = global_functions::decode_object($result[0]['generate_params']);
            if (isset($gen_params["generate_uniref"]))
                $this->uniref = $gen_params["generate_uniref"];
            else
                $this->uniref = 0;

            $a_params = isset($result[0]['analysis_params']) ? global_functions::decode_object($result[0]['analysis_params']) : array();
            $this->use_min_edge_attr = isset($a_params["use_min_edge_attr"]) && $a_params["use_min_edge_attr"];
            $this->use_min_node_attr = isset($a_params["use_min_node_attr"]) && $a_params["use_min_node_attr"];

            $db_mod = isset($gen_params["generate_db_mod"]) ? $gen_params["generate_db_mod"] : functions::get_efidb_module();
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
        }
    }

    public function get_network_dir() {
        $path = "";
        if ($this->custom_clustering) {
            $path = "custom_cluster_" . $this->get_id();
        } else {
            $path = $this->get_filter() . "-" . $this->get_filter_value();
            $path .= "-" . $this->get_min_length() . "-" . $this->get_max_length();
            if ($this->cdhit_opt == "sb" || $this->cdhit_opt == "est+")
                $path .= "-" . $this->cdhit_opt;
            if ($this->use_min_node_attr)
                $path .= "-minn";
            if ($this->use_min_edge_attr)
                $path .= "-mine";
        }
        return $path;
    }

    private function verify_length($min,$max) {

        $result = true;
        if (!$this->is_integer($min)) {
            $result = false;
        }
        elseif (!$this->is_integer($max)) {
            $result = false;
        }
        elseif ($min >= $max) {
            $result = false;
        }
        return $result;
    }

    private function verify_network_name($name) {
        $result = true;
        if ($name == "") {
            $result = false;
        } else {
            $result = preg_replace('/[^A-Za-z0-9_-]/', '_', $name);
        }
        return $result;
    }

    private function verify_evalue($evalue) {
        $result = true;
        if ($evalue == "") {
            $result = false;
        }
        elseif (!$this->is_integer($evalue)) {
            $result = false;
        }
        return $result;
    }

    private function verify_percent_id($pid) {
        $result = true;
        if ($pid == "") {
            $result = false;
        }
        elseif (!is_numeric($pid)) {
            $result = false;
        }
        return $result;
    }

    private function verify_bit_score($pid) {
        $result = true;
        if ($pid == "") {
            $result = false;
        }
        elseif (!is_numeric($pid)) {
            $result = false;
        }
        return $result;
    }

    private function is_integer($value) {
        if (preg_match('/^\d+$/',$value)) {
            return true;
        }
        return false;
    }

    private function available_pbs_slots() {
        $queue = new queue(functions::get_analyse_queue());
        $num_queued = $queue->get_num_queued();
        $max_queuable = $queue->get_max_queuable();
        $num_user_queued = $queue->get_num_queued(functions::get_cluster_user());
        $max_user_queuable = $queue-> get_max_user_queuable();

        $result = false;
        if ($max_queuable - $num_queued < $this->num_pbs_jobs) {
            $result = false;
            $msg = "Analysis ID: " . $this->get_id() . " - ERROR: Queue " . functions::get_analyse_queue() . " is full.  Number in the queue: " . $num_queued;
        }
        elseif ($max_user_queuable - $num_user_queued < $this->num_pbs_jobs) {
            $result = false;
            $msg = "Analysis ID: " . $this->get_id() . " - ERROR: Number of Queued Jobs for user " . functions::get_cluster_user() . " is full.  Number in the queue: " . $num_user_queued;
        }
        else {
            $result = true;
            $msg = "Analysis ID: " . $this->get_id() . " - Number of queued jobs in queue " . functions::get_analyse_queue() . ": " . $num_queued . ", Number of queued user jobs: " . $num_user_queued;
        }
        functions::log_message($msg);
        return $result;
    }

    private function get_stepa_job_info() {
        $gen_id = $this->get_generate_id();
        $job_type = stepa::get_type_static($this->db, $gen_id);

        switch ($job_type) {
            case "BLAST":
                $stepa = new blast($this->db, $gen_id);
                break;
    
            case "FAMILIES": 
                $stepa = new generate($this->db, $gen_id);
                break;			
    
            case "FASTA":
                $stepa = new fasta($this->db, $gen_id);
                break;
    
            case "ACCESSION":
                $stepa = new accession($this->db, $gen_id);
                break;
    
            case "FASTA_ID":
                $stepa = new fasta($this->db, $gen_id, "E");
                break;

            default:
                $stepa = new stepa($this->db, $gen_id);
                break;
        }

        $message = $stepa->get_email_job_info();
        return $message;
    }
    
    public function is_expired() {
        if (!$this->is_sticky && time() > $this->get_unixtime_completed() + functions::get_retention_secs()) {
            return true;
        } else {
            return false;
        }
    }

    public function get_filter_name() {
        $filter = $this->get_filter();
        $name = "";
        switch ($filter) {
            case "bit":
                $name = "Bit score";
                break;

            case "pid":
                $name = "Percent ID";
                break;

            case "eval":
            default:
                return "E-Value";
        }

        return $name;
    }

    // PARENT EMAIL-RELATED OVERLOADS

    protected function get_email_job_info() {
        $message = "Analysis Job ID: " . $this->get_id() . "\r\n";
        $message .= "Minimum Length: " . $this->get_min_length() . "\r\n";
        $message .= "Maximum Length: " . $this->get_max_length() . "\r\n";
        $message .= "Filter Type: " . $this->get_filter_name() . "\r\n";
        $message .= "Filter Value: " . $this->get_filter_value() . "\r\n";
        $message .= "Network Name: " . $this->get_name() . "\r\n";
        return $message;
    }
    
    protected function get_generate_results_script() {
        return "stepe.php";
    }

    protected function get_email_started_subject() { return "EFI-EST - Your SSN is being finalized"; }
    protected function get_email_started_body() {
        $full_url = functions::get_web_root() . "/" . functions::get_job_status_script();
        $full_url .= "?" . http_build_query(array('id' => $this->get_generate_id(), 'key' => $this->get_key(), 'analysis_id' => $this->get_id()));

        $plain_email = "";
        $plain_email .= "Your SSN is being finalized." . PHP_EOL;
        $plain_email .= "To check on the status of this job go to THE_URL " . PHP_EOL . PHP_EOL;
        $plain_email .= "If no new email is received after 48 hours, please contact us and mention the EFI-EST ";
        $plain_email .= "Job ID that corresponds to this email." . PHP_EOL . PHP_EOL;
        $plain_email .= $this->get_stepa_job_info();

        return array("body" => $plain_email, "url" => $full_url);
    }

    protected function get_email_completion_subject() { return "EFI-EST - Your SSN has now been completed and is available for download"; }
    protected function get_email_completion_body() {
        $web_root = functions::get_web_root();
        $url = $web_root . "/stepe.php";
        $full_url = $url . "?" . http_build_query(array('id' => $this->get_generate_id(), 'key' => $this->get_key(), 'analysis_id' => $this->get_id()));
        $gnt_url = functions::get_gnt_web_root();
        $cgfp_url = functions::get_cgfp_web_root();
        //TODO: move these hardcode constant URLs out to a config file or something
        $est_doi_url = "https://dx.doi.org/10.1016/j.bbapap.2015.04.015";
        $gnt_doi_url = "https://doi.org/10.1016/j.cbpa.2018.09.009";
        //$sci_url = "http://www.sciencedirect.com/science/article/pii/S1570963915001120";

        $plain_email = "";
        $plain_email .= "Your EFI-EST SSN has been generated and is available for download." . PHP_EOL . PHP_EOL;
        $plain_email .= "To access the results, please go to THE_URL" . PHP_EOL;
        $plain_email .= "This data will only be retained for " . global_settings::get_retention_days() . " days." . PHP_EOL . PHP_EOL;
        $plain_email .= "Submission Summary:" . PHP_EOL . PHP_EOL;
        $plain_email .= $this->get_stepa_job_info() . PHP_EOL;
        $plain_email .= $this->get_email_job_info() . PHP_EOL . PHP_EOL;

        $plain_email .= "The coloring utility recently developed will help downstream analysis of your SSN. Try it! ";
        $plain_email .= "It can be found at the bottom of the $web_root/#colorssn page." . PHP_EOL . PHP_EOL;
        $plain_email .= "Have you tried exploring Genome Neighborhood Networks (GNTs) from your favorite SSNs? ";
        $plain_email .= "GNT_URL" . PHP_EOL . PHP_EOL;

        $plain_email .= "A new tool for Computationally-Guided Functional Profiling (EFI-CGFP) has been added! ";
        $plain_email .= "Go to CGFP_URL to use it." . PHP_EOL . PHP_EOL;

        $plain_email .= "Cite us:" . PHP_EOL . PHP_EOL;
        $plain_email .= "John A. Gerlt, Jason T. Bouvier, Daniel B. Davidson, Heidi J. Imker, Boris Sadkhin, David R. ";
        $plain_email .= "Slater, Katie L. Whalen, Enzyme Function Initiative-Enzyme Similarity Tool (EFI-EST): A web tool ";
        $plain_email .= "for generating protein sequence similarity networks, Biochimica et Biophysica Acta (BBA) - Proteins ";
        $plain_email .= "and Proteomics, Volume 1854, Issue 8, 2015, Pages 1019-1037, ISSN 1570-9639, EST_DOI ";
        $plain_email .= PHP_EOL . PHP_EOL;

        $plain_email .= "R&eacute;mi Zallot, Nils Oberg, John A. Gerlt, ";
        $plain_email .= "\"Democratized\" genomic enzymology web tools for functional assignment, ";
        $plain_email .= "Current Opinion in Chemical Biology, Volume 47, 2018, Pages 77-85, GNT_DOI";
        $plain_email .= PHP_EOL . PHP_EOL;

        $url_list = array("THE_URL" => $full_url, "GNT_URL" => $gnt_url, "EST_DOI" => $est_doi_url, "GNT_DOI" => $gnt_doi_url, "CGFP_URL" => $cgfp_url);

        return array("body" => $plain_email, "url" => $url_list, "suppress_job_info" => true);
    }

    protected function get_email_cancelled_subject() { return "EFI-EST - Your SSN creation was cancelled"; }
    protected function get_email_cancelled_body() { return "Your SSN creation was cancelled upon user request."; }

    public function email_failed() {
        $subject = "EFI-EST - SSN finalization failed";

        $plain_email = "";
        $plain_email .= "The SSN finalization failed. Please contact us with the EFI-EST Job ID to determine ";
        $plain_email .= "why this occurred." . PHP_EOL . PHP_EOL;

        $this->email_error($subject, $plain_email);
    }
}

?>
