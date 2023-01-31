<?php
namespace efi\est;

require_once(__DIR__."/../../../init.php");

use \efi\global_functions;
use \efi\global_settings;
use \efi\est\file_helper;
use \efi\sanitize;
use \efi\file_types;


abstract class colorssn_shared extends option_base {

    const SEQ_UNIPROT = 1;
    const SEQ_UNIREF50 = 2;
    const SEQ_UNIREF90 = 3;
    const SEQ_EFIREF70 = 4;
    const SEQ_EFIREF50 = 5;
    const SEQ_DOMAIN = 1;
    const SEQ_NO_DOMAIN = 2;
    const DEFAULT_MIN_SEQ_MSA = 5;

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

        if (!$data->email) {
            $result->errors = true;
            //$result->message .= "<br>Please enter a valid email address</br>";
        }

        // This is where we create a color SSN from an analysis job
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

        // This where we create another color SSN job from a color SSN
        } elseif (isset($data->color_ssn_source_color_id) && is_numeric($data->color_ssn_source_color_id)) {
            $sql = "SELECT * FROM generate WHERE generate_id = " . $data->color_ssn_source_color_id;
            $results = $this->db->query($sql);
            if (!$results) {
                $result->errors = true;
                $result->message = "Invalid Color EST job selected.";
            }

        // File upload
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

        //$parms["--ssn-out"] = "\"" . $this->get_colored_xgmml_filename_no_ext() . ".xgmml\"";
        //$parms["--map-dir-name"] = "\"" . functions::get_colorssn_map_dir_name() . "\"";
        //$parms["--map-file-name"] = "\"" . functions::get_colorssn_map_filename() . "\"";
        //$parms["--domain-map-file-name"] = "\"" . functions::get_colorssn_domain_map_filename() . "\"";
        //$parms["--out-dir"] = "\"" . $out->relative_output_dir . "\"";
        //$parms["--stats"] = "\"" . $this->get_stats_filename() . "\"";
        //$parms["--cluster-sizes"] = "\"" . $this->get_cluster_sizes_filename() . "\"";
        //$parms["--conv-ratio"] = "\"" . $this->get_convergence_ratio_filename() . "\"";
        //$parms["--sp-clusters-desc"] = "\"" . $this->get_swissprot_desc_filename($want_clusters_file) . "\"";
        //$parms["--sp-singletons-desc"] = "\"" . $this->get_swissprot_desc_filename($want_singles_file) . "\"";
        $parms["--ssn-out"] = "\"" . file_types::suffix(file_types::FT_ssn) . ".xgmml\"";
        $parms["--map-dir-name"] = "\"" . functions::get_colorssn_map_dir_name() . "\"";
        $parms["--map-file-name"] = "\"" . file_types::suffix(file_types::FT_mapping) . "\"";
        $parms["--domain-map-file-name"] = "\"" . file_types::suffix(file_types::FT_mapping_dom) . "\"";
        $parms["--out-dir"] = "\"" . $out->relative_output_dir . "\"";
        $parms["--stats"] = "\"" . file_types::suffix(file_types::FT_stats) . "\"";
        $parms["--cluster-sizes"] = "\"" . file_types::suffix(file_types::FT_cluster_sizes) . "\"";
        $parms["--conv-ratio"] = "\"" . file_types::suffix(file_types::FT_conv_ratio) . "\"";
        $parms["--sp-clusters-desc"] = "\"" . file_types::suffix(file_types::FT_sp_clusters) . "\"";
        $parms["--sp-singletons-desc"] = "\"" . file_types::suffix(file_types::FT_sp_singletons) . "\"";
        if (!global_settings::advanced_options_enabled())
            $parms["--cleanup"] = "";

        return $parms;
    }

    protected function load_generate($id) {
        $result = parent::load_generate($id);
        if (! $result) {
            return;
        }

        if (isset($result["generate_color_ssn_source_id"]) && isset($result["generate_color_ssn_source_idx"]) && isset($result["generate_color_ssn_source_key"])) {
            $this->ssn_source_analysis_id = $result["generate_color_ssn_source_id"];
            $this->ssn_source_analysis_idx = $result["generate_color_ssn_source_idx"];
            $this->ssn_source_key = $result["generate_color_ssn_source_key"];

            $info = global_functions::verify_est_job($this->db, $this->ssn_source_analysis_id, $this->ssn_source_key, $this->ssn_source_analysis_idx);
            if ($info) {
                $this->ssn_source_id = $info["generate_id"];
                $file_info = global_functions::get_est_filename($info, $this->ssn_source_analysis_idx);
                if ($file_info) {
                    $this->file_helper->set_file_source($file_info["full_ssn_path"]);
                }
            }
        // This where we create another color SSN job from a color SSN
        } else if (isset($result["color_ssn_source_color_id"])) {
            $this->color_ssn_source_color_id = $result["color_ssn_source_color_id"];
            $file_info = self::get_source_color_ssn_info($this->db, $this->color_ssn_source_color_id, false);
            if ($file_info !== false) {
                $this->file_helper->set_file_source($file_info["full_path"]);
                $this->ssn_source_key = $file_info["generate_key"];
                $this->ssn_source_id = $file_info["generate_id"];
            }
        }

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
            $est_results_name = global_functions::get_job_results_dir_name();
            $est_results_dir = "$base_est_results/$id/$est_results_name";
            $params = global_functions::decode_object($results["generate_params"]);
            $filename = global_functions::get_est_colorssn_filename($id, $params["generate_fasta_file"], false);
            $info["generate_key"] = $results["generate_key"];
            $info["filename"] = $params["generate_fasta_file"];

            //OLD: $full_path = "$est_results_dir/$filename.zip";

            // Because we have legacy and new file naming conventions, we use glob.  In a future release
            // this will be removed to use the proper file format.
            $suffix = global_settings::get_est_colorssn_suffix();
            $fp = "";
            if (file_exists("$est_results_dir/ssn.xgmml")) {
                $fp = "$est_results_dir/ssn.xgmml";
            } else if (file_exists("$est_results_dir/ssn.zip")) {
                $fp = "$est_results_dir/ssn.zip";
            } else {
                $colorssn_files = glob("$est_results_dir/*$suffix.zip");
                if (count($colorssn_files) == 0)
                    return false;
                $fp = $colorssn_files[0];
            }
            $info["full_path"] = $fp;

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
        if (isset($data->color_ssn_source_id) && isset($data->color_ssn_source_idx) && isset($data->color_ssn_source_key)) {
            $ainfo = global_functions::verify_est_job($this->db, $data->color_ssn_source_id, $data->color_ssn_source_key, $data->color_ssn_source_idx);
            if ($ainfo) {
                $sinfo = global_functions::get_est_filename($ainfo, $data->color_ssn_source_idx);
                if ($sinfo) {
                    $insert_array["generate_color_ssn_source_id"] = $data->color_ssn_source_id;
                    $insert_array["generate_color_ssn_source_idx"] = $data->color_ssn_source_idx;
                    $insert_array["generate_color_ssn_source_key"] = $data->color_ssn_source_key;
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

    // This works as follows:
    //   Internally the basic SSN is named ssn.zip
    //   The other internal files are named FILE_TYPE
    //   UniProt_IDs.zip
    //   cluster_sizes.txt
    //   ...
    //   
    // Public:
    //   JOBID_UPLOADED_FILE_NAME_coloredssn.zip
    //   JOBID_UPLOADED_FILE_NAME_coloredssn_UniProt_IDs.zip
    //   JOBID_UPLOADED_FILE_NAME_coloredssn_cluster_sizes.txt
    //   ...

    // Return the filename that will be sent to the user.  Input is the type (e.g. cluster_sizes, hmm stuff, etc)
    // Return will look like JOBID_UPLOADED_FILENAME_coloredssn_$file_type_name
    public function get_output_file_name($file_name) {
        $file_name = $this->get_base_filename() . $file_name;
        return $file_name;
    }

    // Return the base file name that will be sent to the user (e.g. JOBID_UPLOADED_FILENAME_coloredssn)
    protected function get_base_filename() {
        $id = $this->get_id();
        $filename = $this->get_uploaded_filename();
        return global_functions::get_est_colorssn_filename($id, $filename, false);
    }

    // Get the pretty name
    public function get_file_name($type) {
        $ext = file_types::ext($type);
        if ($ext === false)
            return false;
        $suffix = file_types::suffix($type);
        if ($suffix === false)
            return false;
        $name = $this->get_base_filename();
        if ($type !== file_types::FT_ssn)
            $name .= "_$suffix.$ext";
        else
            $name .= ".$ext";
        return $name; 
    }
    public function get_file_path($type) {
        $ext = file_types::ext($type);
        if ($ext === false)
            return false;
        $name = file_types::suffix($type);
        if ($name === false)
            return false;
        $base_dir = $this->get_full_output_dir();

        // Try the simple naming convention first
        $file_path = "$base_dir/$name.$ext";

        // Then try legacy file naming convention if the new convention doesn't exist
        if (!file_exists($file_path)) {
            $file_name = $this->get_file_name($type);
            $file_path = "$base_dir/${file_name}";
        }

        if (!file_exists($file_path))
            return false;
        return $file_path;
    }


    // This returns the file path for HMM file types.  $file_name is safe.
    public function get_hmm_file_full_path($file_name) {
        $full_path = $this->get_full_output_dir() . "/" . $file_name;
        return $full_path;
    }

    public function get_results_file_info($type) {
        $name = $this->get_file_name($type);
        if ($name === false)
            return false;
        $path = $this->get_file_path($type);
        if ($path === false)
            return false;
        $size = $this->format_size(filesize($path));
        return array("file_name" => $name, "file_path" => $path, "file_size" => $size);
    }

    public function get_full_output_dir() {
        $dir = $this->is_example ? $this->ex_data_dir : functions::get_results_dir();
        return $dir . "/" . $this->get_output_dir();
    }

    public function get_file_size($type) {
        $file_path = $this->get_file_path($type);
        if (!file_exists($file_path))
            return 0;
        $size = filesize($file_path);
        return $this->format_size($size);
    }
    private function format_size($size) {
        return round($size / 1048576, 5);
    }


    public function get_colored_xgmml_filename_no_ext() {
        return $this->get_base_filename();
    }

    public function get_has_results() {
        $all_nodes_file = $this->get_full_output_dir() . "/" . functions::get_colorssn_map_dir_name() . "/uniprot-nodes/cluster_All_UniProt_IDs.txt";
        if (!file_exists($all_nodes_file))
            return null;
        // Contains a zzz
        if (strpos(file_get_contents($all_nodes_file), "zzz") !== false)
            return false;
        else
            return true;
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

        $stats_file = $this->get_file_path(file_types::FT_stats);
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

