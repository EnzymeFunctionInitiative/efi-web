<?php
namespace efi\est;

require_once(__DIR__."/../../../init.php");

use \efi\global_functions;
use \efi\est\file_helper;


abstract class colorssn_shared extends option_base {

    const SEQ_UNIPROT = 1;
    const SEQ_UNIREF50 = 2;
    const SEQ_UNIREF90 = 3;
    const SEQ_EFIREF70 = 4;
    const SEQ_EFIREF50 = 5;
    const SEQ_DOMAIN = 1;
    const SEQ_NO_DOMAIN = 2;
    const DEFAULT_MIN_SEQ_MSA = 5;

    protected $extra_ram = false;
    protected $use_efiref = false;
    private $ssn_source_analysis_id;
    private $ssn_source_analysis_idx;
    private $ssn_source_key;
    private $ssn_source_id;
    private $color_ssn_source_color_id;


    public function __construct($db, $id = 0, $is_example = false) {
        $this->file_helper = new file_helper(".xgmml", $id);
        parent::__construct($db, $id, $is_example);
    }

    public function __destruct() {
    }

    public function get_uploaded_filename() { return $this->file_helper->get_uploaded_filename(); }

    public function get_parent_path($want_ssn = true) { 
        $path = "";
        if (isset($this->ssn_source_analysis_id) and isset($this->ssn_source_analysis_idx) and
            isset($this->ssn_source_key) and isset($this->ssn_source_id))
        {
            if ($want_ssn) {
                $path = "stepe.php?" .
                    "id=" . $this->ssn_source_id . "&" .
                    "key=" . $this->ssn_source_key;
                $path .= "&analysis_id=" . $this->ssn_source_analysis_id;
            } else {
                $path = "stepc.php?" .
                    "id=" . $this->ssn_source_id . "&" .
                    "key=" . $this->ssn_source_key;
            }
        } else if (isset($this->color_ssn_source_color_id)) {
            $path = "view_colorssn.php?" .
                "id=" . $this->ssn_source_id . "&" .
                "key=" . $this->ssn_source_key;
        }
        return $path;
    }
    
    public function is_hmm_and_stuff_job() { return false; }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // OVERLOADS

    protected function post_output_structure_create() {
        if (!$this->file_helper->copy_file_to_results_dir()) {
            $this->set_status(__FAILED__);
            return 'Colored SSN file did not copy';
        } else {
            return '';
        }
    }

    protected function get_create_type() {
        return "";
    }

    protected function validate($data) {
        $result = new validation_result;

        if (!$this->verify_email($data->email)) {
            $result->errors = true;
            //$result->message .= "<br>Please enter a valid email address</br>";
        }

        if (isset($data->color_ssn_source_id) && isset($data->color_ssn_source_idx) && is_numeric($data->color_ssn_source_id) && is_numeric($data->color_ssn_source_idx)) {
            $sql = "SELECT * FROM analysis WHERE analysis_id = " . $data->color_ssn_source_id;
            $results = $this->db->query($sql);
            if (!$results) {
                $result->errors = true;
                $result->message = "Invalid EST job selected.";
            } elseif (!is_numeric($data->color_ssn_source_idx)) {
                $result->errors = true;
                $result->messages = "Invalid SSN selected.";
            }
        } elseif (isset($data->color_ssn_source_color_id) && is_numeric($data->color_ssn_source_color_id)) {
            $sql = "SELECT * FROM generate WHERE generate_id = " . $data->color_ssn_source_color_id;
            $results = $this->db->query($sql);
            if (!$results) {
                $result->errors = true;
                $result->message = "Invalid Color EST job selected.";
            }
        } elseif (!$this->verify_colorssn_file($data->uploaded_filename)) {
            $result->errors = true;
            $result->message .= "Please upload a valid XGMML (zipped or unzipped) file.  The file extension must be .xgmml or .zip";
        }


        return $result;
    }

    protected function get_run_script() {
        return "make_colorssn_job.pl";
    }

    protected function get_email_started_body() {
        $plain_email = "The SSN has been uploaded and is being colored and analyzed." . PHP_EOL . PHP_EOL;
        $plain_email .= "To check on the status of this job, go to THE_URL" . PHP_EOL . PHP_EOL;
        $plain_email .= "If no new email is received after 48 hours, please contact us and mention the EFI-EST ";
        $plain_email .= "Job ID that corresponds to this email." . PHP_EOL . PHP_EOL;
        
        $full_url = functions::get_web_root() . "/" . functions::get_job_status_script();
        $full_url = $full_url . "?" . http_build_query(array('id' => $this->get_id(), 'key' => $this->get_key()));

        return array("body" => $plain_email, "url" => $full_url);
    }

    protected function get_email_completion_subject() { return "EFI-EST - SSN colored"; }
    protected function get_email_completion_body() {
        $plain_email = "The SSN has been colored and analyzed. To view it, please go to THE_URL" . PHP_EOL . PHP_EOL;
        
        $full_url = functions::get_web_root() . "/" . $this->get_generate_results_script();
        $full_url = $full_url . "?" . http_build_query(array('id' => $this->get_id(), 'key' => $this->get_key()));

        return array("body" => $plain_email, "url" => $full_url);
    }

    protected function get_run_script_args_base($out) {
        $parms = array();
        $parms["--queue"] = functions::get_memory_queue();
        $parms["--ssn-in"] = $this->file_helper->get_results_input_file();
        return $parms;
    }
    protected function get_run_script_args($out) {
        $parms = $this->get_run_script_args_base($out);

        $want_clusters_file = true;
        $want_singles_file = false;

        $parms["--ssn-out"] = "\"" . $this->get_colored_xgmml_filename_no_ext() . ".xgmml\"";
        $parms["--map-dir-name"] = "\"" . functions::get_colorssn_map_dir_name() . "\"";
        $parms["--map-file-name"] = "\"" . functions::get_colorssn_map_filename() . "\"";
        $parms["--domain-map-file-name"] = "\"" . functions::get_colorssn_domain_map_filename() . "\"";
        $parms["--out-dir"] = "\"" . $out->relative_output_dir . "\"";
        $parms["--stats"] = "\"" . $this->get_stats_filename() . "\"";
        $parms["--cluster-sizes"] = "\"" . $this->get_cluster_sizes_filename() . "\"";
        $parms["--conv-ratio"] = "\"" . $this->get_convergence_ratio_filename() . "\"";
        $parms["--sp-clusters-desc"] = "\"" . $this->get_swissprot_desc_filename($want_clusters_file) . "\"";
        $parms["--sp-singletons-desc"] = "\"" . $this->get_swissprot_desc_filename($want_singles_file) . "\"";
        if ($this->extra_ram)
            $parms["--extra-ram"] = $this->extra_ram;
        if (!global_settings::advanced_options_enabled())
            $parms["--cleanup"] = "";
        if ($this->use_efiref !== false) {
            $parms["--efiref-ver"] = $this->use_efiref;
            $parms["--efiref-db"] = "/igbgroup/n-z/noberg/dev/mmseq/efi/efiref.sqlite";
        }

        return $parms;
    }

    protected function load_generate($id) {
        $result = parent::load_generate($id);
        if (! $result) {
            return;
        }

        if (isset($result["generate_color_ssn_source_id"]) && isset($result["generate_color_ssn_source_idx"])) {
            $this->ssn_source_analysis_id = $result["generate_color_ssn_source_id"];
            $this->ssn_source_analysis_idx = $result["generate_color_ssn_source_idx"];

            $info = functions::get_analysis_job_info($this->db, $this->ssn_source_analysis_id);
            if ($info) {
                $this->ssn_source_key = $info["generate_key"];
                $this->ssn_source_id = $info["generate_id"];
                $file_info = functions::get_analysis_ssn_file_info($info, $this->ssn_source_analysis_idx);
                if ($file_info) {
                    $this->file_helper->set_file_source($file_info["full_ssn_path"]);
                }
            }
        } else if (isset($result["color_ssn_source_color_id"])) {
            $this->color_ssn_source_color_id = $result["color_ssn_source_color_id"];
            $file_info = self::get_source_color_ssn_info($this->db, $this->color_ssn_source_color_id, false);
            if ($file_info !== false) {
                $this->file_helper->set_file_source($file_info["full_path"]);
                $this->ssn_source_key = $file_info["generate_key"];
                $this->ssn_source_id = $file_info["generate_id"];
            }
        }

        $this->extra_ram = (isset($result["extra_ram"]) && is_numeric($result["extra_ram"])) ? $result["extra_ram"] : false;
        $this->use_efiref = (isset($result["efiref"]) && is_numeric($result["efiref"])) ? $result["efiref"] : false;

        $this->file_helper->on_load_generate($id, $result);

        return $result;
    }
    public static function get_source_color_ssn_info($db, $id, $key) {
        if (!is_numeric($id))
            return false;
        $info = array("full_path" => "", "generate_id" => $id, "generate_key" => "");
        $sql = "SELECT * FROM generate WHERE generate_id = $id";
        if ($key !== false)
            $sql .= " AND generate_key = " . $db->escape_string($key);
        $results = $db->query($sql);
        if ($results) {
            $results = $results[0];
            $base_est_results = functions::get_results_dir();
            $est_results_name = "output";
            $est_results_dir = "$base_est_results/$id/$est_results_name";
            $params = global_functions::decode_object($results["generate_params"]);
            $filename = global_functions::get_est_colorssn_filename($id, $params["generate_fasta_file"], false);
            $full_path = "$est_results_dir/$filename.zip";
            $info["full_path"] = $full_path;
            $info["generate_key"] = $results["generate_key"];
            $info["filename"] = $params["generate_fasta_file"];
            return $info;
        }
        return false;
    }

    protected function get_email_job_info() {
        $message = parent::get_email_job_info();
        return $message;
    }

    protected function post_insert_action($data, $insert_result_id) {
        $result = parent::post_insert_action($data, $insert_result_id);
        if ((!isset($data->color_ssn_source_id) || !isset($data->color_ssn_source_idx)) && !isset($data->color_ssn_source_color_id)) {
            $result = $this->file_helper->on_post_insert_action($data, $insert_result_id, $result);
        }
        return $result;
    }

    public function get_insert_array($data) {
        $insert_array = parent::get_insert_array($data);
        if (isset($data->color_ssn_source_id) && isset($data->color_ssn_source_idx)) {
            $ainfo = functions::get_analysis_job_info($this->db, $data->color_ssn_source_id);
            if ($ainfo) {
                $sinfo = functions::get_analysis_ssn_file_info($ainfo, $data->color_ssn_source_idx);
                if ($sinfo) {
                    $insert_array["generate_color_ssn_source_id"] = $data->color_ssn_source_id;
                    $insert_array["generate_color_ssn_source_idx"] = $data->color_ssn_source_idx;
                    $insert_array["generate_fasta_file"] = $sinfo["filename"];
                }
            }
        } else if (isset($data->color_ssn_source_color_id)) {
            $file_info = self::get_source_color_ssn_info($this->db, $data->color_ssn_source_color_id, false);
            if ($file_info) {
                $insert_array["color_ssn_source_color_id"] = $data->color_ssn_source_color_id;
                $insert_array["generate_fasta_file"] = $file_info["filename"];
            }
        } else {
            $insert_array = $this->file_helper->on_append_insert_array($data, $insert_array);
        }
        $insert_array["extra_ram"] = (isset($data->extra_ram) && is_numeric($data->extra_ram)) ? $data->extra_ram : false;
        if (isset($data->efiref) && is_numeric($data->efiref))
            $insert_array["efiref"] = $data->efiref;
        return $insert_array;
    }

    protected function additional_exec_modules() {
        return "module load " . functions::get_efignn_module() . "\n";
    }

    protected function get_generate_results_script() {
        return "view_coloredssn.php";
    }

    // END OVERLOADS
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // FILE NAME/PATH ACCESSORS

    protected function get_web_output_dir() {
        $dir = $this->is_example ? functions::get_results_example_dirname() : functions::get_results_dirname();
        return $dir . "/" . $this->get_output_dir();
    }
    public function get_full_output_dir() {
        $dir = $this->is_example ? functions::get_results_example_dir() : functions::get_results_dir();
        return $dir . "/" . $this->get_output_dir();
    }
    public function get_base_filename() {
        $id = $this->get_id();
        $filename = $this->get_uploaded_filename();
        return global_functions::get_est_colorssn_filename($id, $filename, false);
    }
    protected function shared_get_web_path($filename) {
        $rel_path = $this->get_web_output_dir() . "/" . $filename;
        $full_path = $this->shared_get_full_path($filename);
        if ($full_path)
            return $rel_path;
        else
            return "";
    }
    protected function shared_get_full_path($filename) {
        $full_path = $this->get_full_output_dir() . "/" . $filename;
        if (!file_exists($full_path))
            return "";
        return $full_path;
    }
    public function get_file_size($web_path) {
        if ($this->is_example) {
            $dir = functions::get_results_example_dir();
            $chop = functions::get_results_example_dirname();
            $web_path = substr($web_path, strlen($chop)+1);
            $full_path = "$dir/$web_path";
        } else {
           $dir = functions::get_results_dir();
            $full_path = "$dir/../$web_path";
        }
        if (!file_exists($full_path))
            return 0;
        return round(filesize($full_path) / 1048576, 5);
    }

    // Relative for HTTP paths
    public function get_stats_web_path() {
        $filename = $this->get_stats_filename();
        $path = $this->shared_get_web_path($filename);
        if (!$path) {
            $filename = $this->get_stats_filename(true);
            $path = $this->shared_get_web_path($filename);
        }
        return $path;
    }
    public function get_cluster_sizes_web_path() {
        $filename = $this->get_cluster_sizes_filename();
        $path = $this->shared_get_web_path($filename);
        if (!$path) {
            $filename = $this->get_cluster_sizes_filename(true);
            $path = $this->shared_get_web_path($filename);
        }
        return $path;
    }
    public function get_convergence_ratio_web_path() {
        $filename = $this->get_convergence_ratio_filename();
        $path = $this->shared_get_web_path($filename);
        if (!$path) {
            $filename = $this->get_convergence_ratio_filename(true);
            $path = $this->shared_get_web_path($filename);
        }
        return $path;
    }
    public function get_swissprot_desc_web_path($want_clusters_file) {
        $filename = $this->get_swissprot_desc_filename($want_clusters_file);
        $path = $this->shared_get_web_path($filename);
        if (!$path) {
            $filename = $this->get_swissprot_desc_filename($want_clusters_file, true);
            $path = $this->shared_get_web_path($filename);
        }
        return $path;
    }
    public function get_colored_ssn_web_path() {
        $filename = $this->get_colored_ssn_filename();
        return $this->shared_get_web_path($filename);
    }
    public function get_colored_ssn_zip_web_path() {
        $filename = $this->get_colored_ssn_zip_filename();
        return $this->shared_get_web_path($filename);
    }
    public function get_colored_ssn_full_path() {
        $filename = $this->get_colored_ssn_filename();
        return $this->shared_get_full_path($filename);
    }
    public function get_colored_ssn_zip_full_path() {
        $filename = $this->get_colored_ssn_zip_filename();
        return $this->shared_get_full_path($filename);
    }
    public function get_node_files_zip_web_path($domain_type, $seq_type) {
        $filename = $this->get_node_files_zip_filename($domain_type, $seq_type);
        return $this->shared_get_web_path($filename);
    }
    public function get_fasta_files_zip_web_path($domain_type, $seq_type) {
        $filename = $this->get_fasta_files_zip_filename($domain_type, $seq_type);
        return $this->shared_get_web_path($filename);
    }
    public function get_table_file_web_path($want_domain = false) {
        $filename = $this->get_table_file_filename($want_domain);
        return $this->shared_get_web_path($filename);
    }

    // File names.
    private function get_stats_filename($no_prefix = false) {
        return $no_prefix ? "stats.txt" : $this->get_base_filename() . "_stats.txt";
    }
    protected function get_convergence_ratio_filename($no_prefix = false) {
        return $no_prefix ? "conv_ratio.txt" : $this->get_base_filename() . "_convergence_ratio.txt";
    }
    protected function get_cluster_sizes_filename($no_prefix = false) {
        return $no_prefix ? "cluster_sizes.txt" : $this->get_base_filename() . "_cluster_sizes.txt";
    }
    private function get_swissprot_desc_filename($want_clusters_file, $no_prefix = false) {
        $name = $want_clusters_file ? "swissprot_clusters_desc.txt" : "swissprot_singletons_desc.txt";
        return $no_prefix ? $name : $this->get_base_filename() . "_$name";
    }
    private function get_node_files_zip_filename($domain_type, $seq_type) {
        $type_suffix = self::get_type_suffix($seq_type);
        if (!$type_suffix)
            $type_suffix = "UniProt";
        $type_suffix = "_$type_suffix";
        $dom_suffix = $domain_type == self::SEQ_DOMAIN ? "_Domain" : "";
        return $this->get_base_filename() . "${type_suffix}${dom_suffix}_IDs.zip";
    }
    private static function get_type_suffix($seq_type) {
        $type_suffix =
            $seq_type == self::SEQ_UNIREF50 ? "UniRef50" : 
                (
                    $seq_type == self::SEQ_UNIREF90 ? "UniRef90" :
                    (
                        $seq_type == self::SEQ_EFIREF50 ? "EfiRef50" : 
                        (
                            $seq_type == self::SEQ_EFIREF70 ? "EfiRef70" : ""
                        )
                    )
                )
                ;
        return $type_suffix;
    }
    private function get_fasta_files_zip_filename($domain_type, $seq_type) {
        $dom_suffix = $domain_type == self::SEQ_DOMAIN ? "_Domain" : "";
        $type_suffix = self::get_type_suffix($seq_type);
        if ($type_suffix)
            $type_suffix = "_$type_suffix";
        return $this->get_base_filename() . "_FASTA$type_suffix$dom_suffix.zip";
    }
    private function get_table_file_filename($want_domain = false) {
        $name = $want_domain ? functions::get_colorssn_domain_map_filename() : functions::get_colorssn_map_filename();
        return $this->get_base_filename() . "_" . $name;
    }

    private function get_stats_file_path() {
        $filename = $this->get_full_output_dir() . "/" . $this->get_stats_filename();
        if (!file_exists($filename))
            $filename = $this->get_full_output_dir() . "/" . $this->get_stats_filename(false);
        return file_exists($filename) ? $filename : "";
    }
    
    public function get_colored_xgmml_filename_no_ext() {
        $info = pathinfo($this->get_colored_ssn_filename());
        return $info["filename"];
    }
    public function get_colored_ssn_filename() {
        return $this->get_base_filename() . ".xgmml";
    }
    public function get_colored_ssn_zip_filename() {
        return $this->get_base_filename() . ".zip";
    }



    public function get_ssn_stats() {
        return array();
    }

    protected function get_metadata_parent($input_option, $child_metadata) {
        $metadata = array();

        $db_version = $this->get_db_version();

        $stepe_path = $this->get_parent_path(true);
        $stepc_path = $this->get_parent_path(false);
        
        array_push($metadata, array("Job Number", $this->get_id()));
        array_push($metadata, array("Input Option", $input_option));
        if (!$this->is_example) {
            if ($stepe_path) {
                $gid = $this->ssn_source_id;
                $aid = $this->ssn_source_analysis_id;
                array_push($metadata, array("Original SSN Job Number", "$gid/$aid (<a href='$stepc_path' title='EST $gid'>Original Dataset</a> | <a href='$stepe_path' title='EST Analysis ID $aid'>SSN Download</a>)"));
            }
            array_push($metadata, array("Time Started -- Finished", global_functions::format_short_date($this->time_started) . " -- " .
                global_functions::format_short_date($this->time_completed)));
        }
        array_push($metadata, array("Uploaded Filename", $this->get_uploaded_filename()));
        if (!empty($db_version))
            array_push($metadata, array("Database Version", $db_version));
        foreach ($child_metadata as $item) {
            array_push($metadata, $item);
        }

        $stats_file = $this->get_stats_file_path();
        if (!$stats_file)
            return $metadata;

        $fh = fopen($stats_file, "r");

        while (($line = fgets($fh)) !== false) {
            $line = trim($line);
            $parts = explode("\t", $line);
            if (!$line || count($parts) < 2)
                continue;
            $val = is_numeric($parts[1]) ? number_format($parts[1]) : $parts[1];
            array_push($metadata, array($parts[0], $val));
        }

        fclose($fh);

        return $metadata;
    }

    private function verify_colorssn_file($filename) {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $valid = true;
        if (!in_array($ext, functions::get_valid_colorssn_filetypes())) {
            $valid = false;
        }
        return $valid;
    }
}

