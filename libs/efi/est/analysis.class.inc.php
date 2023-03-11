<?php
namespace efi\est;

require_once(__DIR__."/../../../init.php");

use \efi\global_settings;
use \efi\global_functions;
use \efi\est\job_factory;
use \efi\est\queue;
use \efi\training\example_config;
use \efi\file_types;


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
    private $sequence_file = "sequences.fa";
    private $stats_file = "stats.tab";
    protected $output_dir = "output";
    private $num_pbs_jobs = 16;
    private $filter_sequences;
    private $db_version;
    private $custom_clustering;
    private $custom_filename = "custom_cluster.txt";
    private $parent_id = 0; // The parent ID of the generate job that this analysis job is associated with
    private $job_type = "";
    private $uniref = 0;
    private $db_mod = "";
    private $use_min_edge_attr = false;
    private $use_min_node_attr = false;
    private $include_all_seq = false;
    private $compute_nc = false;
    private $build_repnode = true;
    private $tax_search;
    private $tax_search_hash;
    private $tax_search_name;
    private $remove_fragments = false;

    private $analysis_table = "analysis";
    private $generate_table = "generate";
    private $ex_data_dir = "";

    private $net_stats;
    private $debug_params = array();
    private $debug_results = array();

    private $job_status_obj;


    ///////////////Public Functions///////////

    public function __construct($db, $id = 0, $is_example = false) {
        parent::__construct($db, "analysis", $is_example);

        $this->db = $db;

        if ($is_example) {
            $this->init_example($is_example);
        }

        if ($id)
            $this->load_analysis($id);
        $this->beta = global_settings::get_release_status();
        $this->job_status_obj = new \efi\job_status($db, $this);
    }

    private function init_example($id) {
        $config = example_config::get_example_data($id);
        $this->analysis_table = example_config::get_est_analysis_table($config);
        $this->generate_table = example_config::get_est_generate_table($config);
        $this->ex_data_dir = example_config::get_est_data_dir($config);
    }

    public function __destruct() {
    }

    protected function get_job_status_obj() { return $this->job_status_obj; }

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
    public function get_filter_sequences() { return $filter_sequences; }
    private function get_sequence_file() { return $this->sequence_file; }
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
        return "EST";
    }
    public function get_compute_nc() { return $this->compute_nc; }
    public function get_build_repnode() { return $this->build_repnode; }

    public function get_time_period() { return $this->get_job_status_obj()->get_time_period(); }
    
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

    private function get_num_sequences_post_filter() {
        $analysis_dir = $this->get_network_output_path();
        $full_path = $analysis_dir . "/" . $this->get_sequence_file();
        $num_seq = 0;
        if (file_exists($full_path)) {
            $exec = "cat " . $full_path . " | grep '>' | wc -l";
            $output = exec($exec);
            $output = trim(rtrim($output));
            list($num_seq,) = explode(" ",$output);
        }
        return $num_seq;
    }

    //public function create($generate_id, $filter_value, $name, $minimum, $maximum, $customFile = "", $cdhitOpt = "", $filter = "eval", $min_node_attr = 0, $min_edge_attr = 0, $compute_nc = false, $build_repnode = true, $tax_search = false, $tax_search_name = "", $remove_fragments = false) {
    public function create($params) {
        $generate_id = $params["job_id"];
        $filter_value = $params["filter_value"];
        $name = $params["network_name"];
        $minimum = $params["min"];
        $maximum = $params["max"];
        $customFile = $params["custom_file"];
        $cdhitOpt = $params["cdhit_opt"];
        $filter = $params["filter"];
        $min_node_attr = $params["min_node_attr"];
        $min_edge_attr = $params["min_edge_attr"];
        $compute_nc = $params["compute_nc"];
        $build_repnode = $params["build_repnode"];
        $tax_search = $params["tax_search"];
        $tax_search_name = $params["tax_name"];
        $remove_fragments = $params["remove_fragments"];

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
            if ($compute_nc)
                $params['compute_nc'] = true;
            if ($remove_fragments)
                $params['remove_fragments'] = true;
            if (!$build_repnode) $params['build_repnode'] = false;
            if ($tax_search !== false) {

                $the_search = "";
                $tax_search_id = "";

                if ($tax_search_name) {
                    $parts = explode("|", $tax_search_name);

                    if (count($parts) > 1) {
                        $the_search = "PREDEFINED:" . $parts[0];
                        $tax_search_id = $parts[0];
                        $this->tax_search_name = $parts[1];
                    } else {
                        $the_search = implode(";", $tax_search);
                        $this->tax_search_name = $parts[0];
                    }

                    $params['tax_search_name'] = $tax_search_name;
                } else {
                    $the_search = implode(";", $tax_search);
                }
                $params['tax_search'] = $the_search;

                $tax_search_hash = $this->get_tax_search_hash($tax_search, $tax_search_name, $tax_search_id);
                $this->tax_search_hash = $tax_search_hash;
                $params['tax_search_hash'] = $tax_search_hash;
            }
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

            $new_id = $this->db->build_insert("analysis",$insert_array);
            $this->get_job_status_obj()->insert_new($new_id);
            if ($new_id) {
                return array('RESULT'=>true,'id'=>$new_id);
            } else {
                $message = "Unknown database error";
            }
        }
        return array('RESULT'=>!$errors,'MESSAGE'=>$message);
    }

    private function get_tax_search_hash($tax_search, $tax_name = "", $tax_name_id = "") {
        // $tax_search is an array
        $str = "";
        if ($tax_name or $tax_name_id) {
            if ($tax_name_id) {
                $str = $tax_name_id;
            } else {
                $words = preg_split('/[ ]+/', $tax_name);
                foreach ($words as $word) {
                    $str .= substr($word, 0, 3);
                }
            }
        } else {
            for ($i = 0; $i < count($tax_search); $i++) {
                $parts = explode(":", $tax_search[$i]);
                $str .= substr($parts[0], 0, 1) . substr($parts[1], 0, 1);
            }
        }
        $str = preg_replace("/[^A-Za-z0-9]/", "", $str);
        return $str;
    }

    public function check_pbs_running() {
        $sched = strtolower(functions::get_cluster_scheduler());
        $jobNum = $this->get_pbs_number();
        $output = "";
        $exit_status = "";
        $exec = "";
        if (!$jobNum)
            return false;
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

    public function get_num_networks() {
        if (!isset($this->net_stats)) {
            if ($this->load_network_stats() === false)
                return false;
        }
        return count($this->net_stats);
    }

    public function get_network_stats($ssn_idx) {
        if (!isset($this->net_stats)) {
            if ($this->load_network_stats() === false)
                return false;
        }
        if (!isset($this->net_stats[$ssn_idx]))
            return false;
        $stats = $this->net_stats[$ssn_idx];
        $info = array("file" => $stats["File"], "nodes" => $stats["Nodes"], "edges" => $stats["Edges"], "size" => $stats["Size"], "pid" => $stats["PctId"]);
        $info["zip_file"] = isset($stats["ZipFile"]) ? $stats["ZipFile"] : false;
        $info["zip_size"] = isset($stats["ZipSize"]) ? $stats["ZipSize"] : false;
        return $info;
    }

    private function load_network_stats() {
        $this->net_stats = array();

        $results_dir = $this->get_network_output_path();
        $file = $results_dir . "/" . $this->stats_file;
        if (!file_exists($file)) {
            error_log("Unable to read network file stats $file; it doesn't exist");
            return false;
        }
        $file_handle = fopen($file, "r");
        if (!is_resource($file_handle)) {
            error_log("Unable to read network file stats $file");
            return false;
        }

        $i = 0; 
        $keys = array("File", "Nodes", "Edges", "Size");
        $row = 0;

        while (($data = fgetcsv($file_handle, 0, "\t")) !== FALSE) {
            $stats_row = array();
            if ($row == 1) {
                $result = array_splice($data, 1, 1);
                $stats_row = array_combine($keys, $data);
            } elseif ($row > 1) {
                $stats_row = array_combine($keys, $data);
            }

            if (count($stats_row)) {
                $raw_file = $stats_row["File"];
                $percent_identity = substr($raw_file, strrpos($raw_file, "-") + 1);
                $percent_identity = substr($percent_identity, 0, strrpos($percent_identity, "_"));
                $percent_identity = str_replace(".", "", $percent_identity);

                $stats_row["PctId"] = $row == 1 ? "full" : $percent_identity;

                $zip_file = $raw_file . ".zip";
                if (file_exists("$results_dir/$zip_file")) {
                    $stats_row["ZipFile"] = $zip_file;
                    $stats_row["ZipSize"] = filesize($file);
                }

                array_push($this->net_stats, $stats_row);
            }

            $row++;
        }

        fclose($file_handle);

        return true;
    }

    private function get_results_dir() {
        if ($this->is_example) {
            return $this->ex_data_dir;
        } else {
            return functions::get_results_dir();
        }
    }

    private function get_zip_file_size($ssn_file) {
        $results_dir = $this->get_network_output_path();
        $file = "$results_dir/$ssn_file.zip";
        if (file_exists($file))
            return filesize($file);
        else
            return 0;
    }











    public function get_file_name($type, $ssn_idx = -1) {
        $file_name = false;
        if ($type === file_types::FT_blast_evalue) {
            $id = $this->get_generate_id();
            $file_name = "${id}_" . $this->get_name() . "_blast.tab";
        } else if ($ssn_idx >= 0) {
            $stats = $this->get_network_stats($ssn_idx);
            if ($stats === false) {
                return false;
            }
            $file_name = $stats["file"];
            if ($type == file_types::FT_nc_legend)
                $file_name = str_replace(".xgmml", "_nc.png", $file_name);
            else if ($stats["zip_file"])
                $file_name = $stats["zip_file"];
        }
        return $file_name;
    }
    public function get_file_path($type, $ssn_idx = -1) {
        $file_path = false;
        if ($type === file_types::FT_blast_evalue) {
            $results_dir = $this->get_network_output_path();
            $file_path = "$results_dir/" . blast::EVALUE_OUTPUT_FILE;
        } else if ($ssn_idx >= 0) {
            $stats = $this->get_network_stats($ssn_idx);
            if ($stats === false) {
                return false;
            }

            $results_dir = $this->get_network_output_path();

            // Check new naming convention first.
            $fname = $type == file_types::FT_nc_legend ? "nc_$ssn_idx.png" : "ssn_$ssn_idx.zip";
            $file_path = "$results_dir/$fname";
            if (!file_exists($file_path)) {
                $ext = $stats["zip_file"] ? "xgmml.zip" : "xgmml";
                $fname = $stats["zip_file"] ? $stats["zip_file"] : $stats["file"];
                if ($type == file_types::FT_nc_legend)
                    $fname = str_replace(".$ext", "_nc.png", $fname);
                $file_path = "$results_dir/$fname";
            }
        }
        if (!file_exists($file_path))
            return false;
        else
            return $file_path;
    }

    public function get_graph_info($type, $ssn_idx) {
        $info = array("file_name" => false, "file_path" => false);
        if ($type == "NC") {
            $stats = $this->get_network_stats($ssn_idx);
            if ($stats === false) {
                return false;
            }

            $file_name = $this->get_file_name($ssn_idx, true);
            $file_path = $this->get_file_path($ssn_idx, true);

            if (file_exists($file_path)) {
                $info["file_name"] = $file_name;
                $info["file_path"] = $file_path;
            } else {
                return false;
            }
        } else {
            return false;
        }
        return $info;
    }
















    public function run_job($is_debug = false) {
        if ($this->available_pbs_slots()) {

            $sched = functions::get_cluster_scheduler();

            //Setup Directories
            $gen_output_dir = $this->get_generate_path();
            $full_output_dir = $this->get_network_output_path();

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
                if ($this->tax_search) {
                    $exec .= " --tax-search \"" . $this->tax_search . "\"";
                    $exec .= " --tax-search-hash " . $this->tax_search_hash;
                }
                if ($this->custom_clustering) {
                    $exec .= " -custom-cluster-file " . $this->custom_filename;
                    $network_dir = $this->get_legacy_network_dir();
                    $exec .= " -custom-cluster-dir " . $network_dir;
                }
                if ($this->uniref)
                    $exec .= " -uniref-version " . $this->uniref;
                if ($sched)
                    $exec .= " -scheduler " . $sched . " ";
                if ($parent_id > 0)
                    $exec .= " " . $parent_dir_opt;
                if ($this->job_type == "FASTA_ID" || $this->job_type == "FASTA") {
                    $exec .= " -include-sequences";
                    if ($this->include_all_seq)
                        $exec .= " -include-all-sequences";
                }
                if ($this->use_min_edge_attr)
                    $exec .= " -use-min-edge-attr";
                if ($this->use_min_node_attr)
                    $exec .= " -use-anno-spec";
                if ($this->compute_nc)
                    $exec .= " -compute-nc";
                if (!$this->build_repnode)
                    $exec .= " -no-repnode";
                if ($this->remove_fragments)
                    $exec .= " --remove-fragments";

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
                    $this->get_job_status_obj()->start();
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
            $this->status = $result[0]['analysis_status'];
            $this->filter = $result[0]['analysis_filter'];
            $this->filter_sequences = $result[0]['analysis_filter_sequences'];
            $this->db_version = functions::decode_db_version($result[0]['generate_db_version']);
            $this->parent_id = $result[0]['generate_parent_id'];
            $this->job_type = $result[0]['generate_type'];
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
            // Handle BLAST job database
            else if (isset($gen_params["blast_db_type"]) && substr($gen_params["blast_db_type"], 0, 6) === "uniref")
                $this->uniref = substr($gen_params["blast_db_type"], 6);
            else
                $this->uniref = 0;
            $this->include_all_seq = (isset($gen_params["include_all_seq"]) && $gen_params["include_all_seq"]) ? true : false;

            $a_params = isset($result[0]['analysis_params']) ? global_functions::decode_object($result[0]['analysis_params']) : array();
            $this->debug_params = $a_params;
            $this->use_min_edge_attr = isset($a_params["use_min_edge_attr"]) && $a_params["use_min_edge_attr"];
            $this->use_min_node_attr = isset($a_params["use_min_node_attr"]) && $a_params["use_min_node_attr"];
            $this->compute_nc = (isset($a_params["compute_nc"]) && $a_params["compute_nc"]) ? true : false;
            if (isset($a_params["build_repnode"]) && !$a_params["build_repnode"]) {
                $this->build_repnode = false;
            }
            if (isset($a_params["tax_search"]) && $a_params["tax_search"] && isset($a_params["tax_search_hash"]) && $a_params["tax_search_hash"]) {
                $this->tax_search = isset($a_params["tax_search"]) ? $a_params["tax_search"] : "";
                $this->tax_search_hash = isset($a_params["tax_search_hash"]) ? $a_params["tax_search_hash"] : "";
                $this->tax_search_name = isset($a_params["tax_search_name"]) ? $a_params["tax_search_name"] : "";
            }
            if (isset($a_params["remove_fragments"]) && $a_params["remove_fragments"])
                $this->remove_fragments = true;
            else
                $this->remove_fragments = false;

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

    protected function get_debug_param_values() {
        return $this->debug_params;
    }
    protected function get_debug_result_values() {
        return array();
    }

    private function get_generate_path() {
        $generate_path = $this->get_results_dir() . "/" . $this->get_output_dir();
        return $generate_path;
    }

    private function get_network_output_path() {
        $generate_path = $this->get_generate_path();
        $new_path = "$generate_path/" . $this->get_id();
        if (is_dir($new_path)) {
            return $new_path;
        } else {
            $legacy_name = $this->get_legacy_network_dir();
            return "$generate_path/$legacy_name";
        }
    }

    private function get_legacy_network_dir() {
        $path = "";
        if ($this->custom_clustering) {
            $path = "custom_cluster_" . $this->get_id();
        } else {
            $path = $this->get_filter() . "-" . $this->get_filter_value();
            $path .= "-" . $this->get_min_length() . "-" . $this->get_max_length();
            if ($this->use_min_node_attr)
                $path .= "-minn";
            if ($this->use_min_edge_attr)
                $path .= "-mine";
            if ($this->compute_nc)
                $path .= "-nc";
            if ($this->tax_search)
                $path .= "-" . $this->tax_search_hash;
            if ($this->remove_fragments)
                $path .= "-nf";
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
                $stepa = new family($this->db, $gen_id);
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

    public function get_tax_search_formatted() {
        $tax_row = "";
        if ($this->tax_search) {
            if ($this->tax_search_name) {
                $tax_row = $this->tax_search_name;
            } else {
                $tax_cats = explode(";", $this->tax_search);
                for ($i = 0; $i < count($tax_cats); $i++) {
                    $parts = explode(":", $tax_cats[$i]);
                    $name = ucfirst($parts[0]);
                    $search = $parts[1];
                    if ($tax_row)
                        $tax_row .= ", ";
                    $tax_row .= "$name: $search";
                }
            }
        }
        return $tax_row;
    }

    public function get_remove_fragments() {
        return (isset($this->remove_fragments) && $this->remove_fragments);
    }

    public static function get_percent_identity($fname) {
        $percent_identity = substr($fname, strrpos($fname,'-') + 1);
        $sep_char = "_";
        $percent_identity = substr($percent_identity, 0, strrpos($percent_identity, $sep_char));
        $percent_identity = str_replace(".","",$percent_identity);
        return $percent_identity;
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
        $est_doi_url = "https://doi.org/10.1021/acs.biochem.9b00735";
        $gnt_doi_url = "https://doi.org/10.1016/j.cbpa.2018.09.009";
        $biochem_doi_url = "https://dx.doi.org/10.1016/j.bbapap.2015.04.015";
        //$sci_url = "http://www.sciencedirect.com/science/article/pii/S1570963915001120";

        $plain_email = "";
        $plain_email .= "Your EFI-EST SSN has been generated and is available for download." . PHP_EOL . PHP_EOL;
        $plain_email .= "To access the results, please go to THE_URL" . PHP_EOL;
        $plain_email .= "This data will only be retained for " . global_settings::get_retention_days() . " days." . PHP_EOL . PHP_EOL;
        $plain_email .= "Submission Summary:" . PHP_EOL . PHP_EOL;
        $plain_email .= $this->get_stepa_job_info() . PHP_EOL;
        $plain_email .= $this->get_email_job_info() . PHP_EOL . PHP_EOL;

        $plain_email .= 'The Color SSN utility can aid downstream analysis of your SSN.  You can transfer the SSN to the Color SNN utility using the "Transfer To" menu on the "Network Files" tab of the "Download Network Files" page.   Or, you can upload the SSN xgmml file to the "Color SSN" utility that can be found in the "SSN Utilities" tab of the EFI-EST home page.';
        $plain_email .= PHP_EOL . PHP_EOL;
        $plain_email .= 'You can generate Genome Neighborhood Networks (GNTs) and Genome Neighborhood Diagrams (GNDs) for the clusters in your SSN using EFI-GNT.  The SSN can be transferred to EFI-GNT using the "Transfer To" menu on the Network Files tab of the "Download Network Files" page.  Or, you can upload the SSN xgmml file on the "GNT Submission" tab of the EFI-GNT home page.';
        $plain_email .= PHP_EOL . PHP_EOL;
        $plain_email .= 'You can use the Computationally-Guided Functional Profiling tool (EFI-CGFP) to determine the abundance of your clusters in human metagenomes.   The SSN can be transferred from the Color SSN utility (a Color SSN is the required input) using the "Run CGFP on Color SSN" button.   Or, you can upload the SSN xgmml file on the "Run CGFP/Shortbred "tab on the EFI-CGFP home page.';
        $plain_email .= PHP_EOL . PHP_EOL;

        $plain_email .= "Cite us:" . PHP_EOL . PHP_EOL;
        $plain_email .= global_settings::get_global_citation();
        $plain_email .= PHP_EOL . PHP_EOL;

        //$plain_email .= "John A. Gerlt, Jason T. Bouvier, Daniel B. Davidson, Heidi J. Imker, Boris Sadkhin, David R. ";
        //$plain_email .= "Slater, Katie L. Whalen, Enzyme Function Initiative-Enzyme Similarity Tool (EFI-EST): A web tool ";
        //$plain_email .= "for generating protein sequence similarity networks, Biochimica et Biophysica Acta (BBA) - Proteins ";
        //$plain_email .= "and Proteomics, Volume 1854, Issue 8, 2015, Pages 1019-1037, ISSN 1570-9639, EST_DOI ";
        //$plain_email .= PHP_EOL . PHP_EOL;

        //$plain_email .= "R&eacute;mi Zallot, Nils Oberg, John A. Gerlt, ";
        //$plain_email .= "\"Democratized\" genomic enzymology web tools for functional assignment, ";
        //$plain_email .= "Current Opinion in Chemical Biology, Volume 47, 2018, Pages 77-85, GNT_DOI";
        //$plain_email .= PHP_EOL . PHP_EOL;

        $url_list = array("THE_URL" => $full_url, "GNT_URL" => $gnt_url, "EST_DOI" => $est_doi_url, "GNT_DOI" => $gnt_doi_url, "CGFP_URL" => $cgfp_url, "BIOCHEM_DOI" => $biochem_doi_url);

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

    public function process_start() {
        $this->get_job_status_obj()->start();
        $this->email_started();
    }

    public function process_error() {
        $this->get_job_status_obj()->failed();
        $this->email_failed();
        $msg = "Analaysis ID: " . $this->get_id() . " - Job Failed";
        functions::log_message($msg);
    }

    public function process_finish() {
        $this->get_job_status_obj()->complete();
        $this->email_complete();
        $msg = "Analaysis ID: " . $this->get_id() . " - Job Completed Successfully";
        functions::log_message($msg);	
    }
}

