<?php

require_once("option_base.class.inc.php");
require_once("../../libs/global_functions.class.inc.php");

class colorssn extends option_base {

    const SEQ_UNIPROT = 1;
    const SEQ_UNIREF50 = 2;
    const SEQ_UNIREF90 = 3;
    const SEQ_DOMAIN = 1;
    const SEQ_NO_DOMAIN = 2;
    const DEFAULT_MIN_SEQ_MSA = 5;

    private $ssn_source_analysis_id;
    private $ssn_source_analysis_idx;
    private $ssn_source_key;
    private $ssn_source_id;
    private $extra_ram = false;
    private $make_hmm = "";
    private $hmm_aa = "";
    private $aa_threshold = 0;
    private $min_seq_msa = 0;
    private $max_seq_msa = 0;
    private $include_fragments = false;


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
            if ($want_ssn)
                $path = "stepe.php?" .
                    "id=" . $this->ssn_source_id . "&" .
                    "key=" . $this->ssn_source_key . "&" .
                    "analysis_id=" . $this->ssn_source_analysis_id;
            else
                $path = "stepc.php?" .
                    "id=" . $this->ssn_source_id . "&" .
                    "key=" . $this->ssn_source_key;
        }
        return $path;
    }
    
    public function get_include_fragments() { return $this->include_fragments; }
    public function get_hmm_options() {
        $opt = $this->make_hmm;
        $opt = str_replace("CR", "Consensus Residue", $opt);
        $opt = str_replace("HMM", "HMM", $opt);
        $opt = str_replace("WEBLOGO", "Weblogo", $opt);
        $opt = str_replace("HIST", "Length Histogram", $opt);
        $parts = explode(",", $opt);
        $opt = implode(", ", $parts);
        $opt = preg_replace("/^\s*,\s*/", "", $opt);
        $count_seq_opt = $this->min_seq_msa != self::DEFAULT_MIN_SEQ_MSA ? "MinNumSeq=" . $this->min_seq_msa : "";
        $count_seq_opt .= $this->max_seq_msa ? ($count_seq_opt ? "; " : "") . "MaxNumSeq=" . $this->max_seq_msa : "";
        if (preg_match("/CR/", $this->make_hmm))
            $opt .= " (AAs=" . $this->hmm_aa . "; Thresholds=" . $this->aa_threshold . "; " . $count_seq_opt . ")";
        elseif ($count_seq_opt)
            $opt .= " ($count_seq_opt)";
        return $opt;
    }


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
        return self::create_type();
    }

    public static function create_type() {
        return "COLORSSN";
    }

    protected function validate($data) {
        $result = new validation_result;

        if (!$this->verify_email($data->email)) {
            $result->errors = true;
            //$result->message .= "<br>Please enter a valid email address</br>";
        }

        if (isset($data->color_ssn_source_id) && isset($data->color_ssn_source_idx)) {
            $sql = "SELECT * FROM analysis WHERE analysis_id = " . $data->color_ssn_source_id;
            $results = $this->db->query($sql);
            if (!$results) {
                $result->errors = true;
                $result->message = "Invalid EST job selected.";
            } elseif (!is_numeric($data->color_ssn_source_idx)) {
                $result->errors = true;
                $result->messages = "Invalid SSN selected.";
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

    protected function get_run_script_args($out) {
        $parms = array();

        $want_clusters_file = true;
        $want_singles_file = false;

        $parms["--queue"] = functions::get_memory_queue();
        $parms["--ssn-in"] = $this->file_helper->get_results_input_file();
        $parms["--ssn-out"] = "\"" . $this->get_colored_xgmml_filename_no_ext() . ".xgmml\"";
        $parms["--map-dir-name"] = "\"" . functions::get_colorssn_map_dir_name() . "\"";
        $parms["--map-file-name"] = "\"" . functions::get_colorssn_map_filename() . "\"";
        $parms["--domain-map-file-name"] = "\"" . functions::get_colorssn_domain_map_filename() . "\"";
        $parms["--out-dir"] = "\"" . $out->relative_output_dir . "\"";
        $parms["--stats"] = "\"" . $this->get_stats_filename() . "\"";
        $parms["--cluster-sizes"] = "\"" . $this->get_cluster_sizes_filename() . "\"";
        $parms["--sp-clusters-desc"] = "\"" . $this->get_swissprot_desc_filename($want_clusters_file) . "\"";
        $parms["--sp-singletons-desc"] = "\"" . $this->get_swissprot_desc_filename($want_singles_file) . "\"";
        if ($this->extra_ram)
            $parms["--extra-ram"] = "";
        if ($this->make_hmm) {
            $parms["--opt-msa-option"] = $this->make_hmm;
            if (preg_match("/CR/", $this->make_hmm)) {
                if ($this->hmm_aa)
                    $parms["--opt-aa-list"] = $this->hmm_aa;
                if ($this->aa_threshold)
                    $parms["--opt-aa-threshold"] = $this->aa_threshold;
            }
            if (preg_match("/CR|HMM|WEBLOGO/", $this->make_hmm)) {
                if ($this->min_seq_msa)
                    $parms["--opt-min-seq-msa"] = $this->min_seq_msa;
                if ($this->max_seq_msa)
                    $parms["--opt-max-seq-msa"] = $this->max_seq_msa;
            }
        }
        if ($this->include_fragments)
            $parms["--include-fragments"] = "";

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
                $file_info = functions::get_ssn_file_info($info, $this->ssn_source_analysis_idx);
                if ($file_info) {
                    $this->file_helper->set_file_source($file_info["full_ssn_path"]);
                }
            }
        }

        $this->extra_ram = (isset($result["extra_ram"]) && $result["extra_ram"] === true);
        $this->make_hmm = (isset($result["make_hmm"]) && $result["make_hmm"]) ? $result["make_hmm"] : "";
        $this->aa_threshold = (isset($result["aa_threshold"]) && $result["aa_threshold"]) ? $result["aa_threshold"] : 0;
        $this->min_seq_msa = (isset($result["min_seq_msa"]) && $result["min_seq_msa"]) ? $result["min_seq_msa"] : self::DEFAULT_MIN_SEQ_MSA;
        $this->max_seq_msa = (isset($result["max_seq_msa"]) && $result["max_seq_msa"]) ? $result["max_seq_msa"] : 0;
        $this->hmm_aa = (isset($result["hmm_aa"]) && $result["hmm_aa"]) ? $result["hmm_aa"] : "";
        $this->include_fragments = (isset($result["include_fragments"]) && $result["include_fragments"] === true);

        $this->file_helper->on_load_generate($id, $result);

        return $result;
    }

    protected function get_email_job_info() {
        $message = parent::get_email_job_info();
        return $message;
    }

    protected function post_insert_action($data, $insert_result_id) {
        $result = parent::post_insert_action($data, $insert_result_id);
        if (!isset($data->color_ssn_source_id) || !isset($data->color_ssn_source_idx)) {
            $result = $this->file_helper->on_post_insert_action($data, $insert_result_id, $result);
        }
        return $result;
    }

    public function get_insert_array($data) {
        $insert_array = parent::get_insert_array($data);
        if (isset($data->color_ssn_source_id) && isset($data->color_ssn_source_idx)) {
            $ainfo = functions::get_analysis_job_info($this->db, $data->color_ssn_source_id);
            if ($ainfo) {
                $sinfo = functions::get_ssn_file_info($ainfo, $data->color_ssn_source_idx);
                if ($sinfo) {
                    $insert_array["generate_color_ssn_source_id"] = $data->color_ssn_source_id;
                    $insert_array["generate_color_ssn_source_idx"] = $data->color_ssn_source_idx;
                    $insert_array["generate_fasta_file"] = $sinfo["filename"];
                }
            }
        } else {
            $insert_array = $this->file_helper->on_append_insert_array($data, $insert_array);
        }
        $insert_array["extra_ram"] = (isset($data->extra_ram) && $data->extra_ram === true);
        $insert_array["make_hmm"] = (isset($data->make_hmm) && preg_match("/^[CRHMWEBLOGIST,]+$/", $data->make_hmm)) ? $data->make_hmm : "";
        $insert_array["aa_threshold"] = (isset($data->aa_threshold) && preg_match("/^[0-9\., ]+$/", $data->aa_threshold)) ? $data->aa_threshold : 0;
        $insert_array["hmm_aa"] = (isset($data->hmm_aa) && preg_match("/^[A-Z, ]+$/", $data->hmm_aa)) ? str_replace(" ", "", $data->hmm_aa) : "";
        $insert_array["min_seq_msa"] = (isset($data->min_seq_msa) && preg_match("/^[0-9\., ]+$/", $data->min_seq_msa)) ? $data->min_seq_msa : self::DEFAULT_MIN_SEQ_MSA;
        $insert_array["max_seq_msa"] = (isset($data->max_seq_msa) && preg_match("/^[0-9\., ]+$/", $data->max_seq_msa)) ? $data->max_seq_msa : 0;
        $insert_array["include_fragments"] = (isset($data->include_fragments) && $data->include_fragments === true);
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

    private function get_web_output_dir() {
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
    private function shared_get_web_path($filename) {
        $rel_path = $this->get_web_output_dir() . "/" . $filename;
        $full_path = $this->get_full_output_dir() . "/" . $filename;
        if (!file_exists($full_path))
            return "";
        return $rel_path;
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
    public function get_weblogo_zip_web_path($type) {
        return $this->get_zip_web_path("WebLogos", $type);
    }
    public function get_msa_zip_web_path($type) {
        return $this->get_zip_web_path("MSAs", $type);
    }
    public function get_lenhist_zip_web_path($type, $seqType) {
        return $this->get_zip_web_path("LenHist_$seqType", $type);
    }
    public function get_hmm_zip_web_path($type) {
        return $this->get_zip_web_path("HMMs", $type);
    }
    private function get_zip_web_path($job_type, $node_type) {
        $filename = $this->get_base_filename() . "_${job_type}_${node_type}.zip";
        return $this->shared_get_web_path($filename);
    }
    public function get_hmm_graphics() {
        return $this->get_logo_graphics_data("hmm_logos");
    }
    public function get_weblogo_graphics() {
        return $this->get_logo_graphics_data("weblogos");
    }
    public function get_lenhist_graphics() {
        return $this->get_logo_graphics_data("histograms");
    }
    public function get_alignment_list() {
        $data = $this->get_logo_graphics_data("alignments");
        if (!empty($data))
            return $data;

        $logo_data = $this->get_weblogo_graphics();
        foreach ($logo_data as $cluster_num => $seq_type_list) {
            foreach ($seq_type_list as $seq_type => $quality_list) {
                foreach ($quality_list as $quality => $ds) {
                    $parts = explode("/", $ds["path"]);
                    $max = count($parts) - 1;
                    if (isset($parts[$max - 1])) {
                        $parts[$max - 1] = "align";
                        $parts[$max] = $parts[$max];
                    }
                    $path = implode("/", $parts);
                    $data[$cluster_num][$seq_type][$quality]["path"] = $path;
                }
            }
        }

        return $data;
    }
    public function get_cr_results_list() {
        $data = $this->get_logo_graphics_data("consensus_residue");
        return $data;
    }
    private function get_logo_graphics_data($graphics_type) {
        $base_dir = $this->get_full_output_dir();
        $full_path = "$base_dir/$graphics_type.txt";
        if (!file_exists($full_path))
            return array();

        $graphics = array();

        $sizes = $this->parse_cluster_sizes();

        $lines = file($full_path);
        foreach ($lines as $line) {
            list($cluster_num, $seq_type, $sub_type, $path) = explode("\t", rtrim($line));
            
            $data = array("path" => $path);
            if (isset($sizes[$cluster_num])) {
                $data["num_seq"] = 0;
                $data["num_uniprot"] = $sizes[$cluster_num]["uniprot"];
                $data["num_uniref90"] = $sizes[$cluster_num]["uniref90"];
                $data["num_uniref50"] = $sizes[$cluster_num]["uniref50"];
            }

            if ($graphics_type == "hmm_logos") {
                $hmm_path = "$base_dir/$path.hmm";
                $hmm_data = self::parse_hmm($hmm_path);
                $data = array_merge($data, $hmm_data);
            }
            $graphics[$cluster_num][$seq_type][$sub_type] = $data;
        }

        return $graphics;
    }
    public function get_graphics_dir() {
        return $this->get_web_output_dir();
    }
    private function parse_cluster_sizes() {
        $filename = $this->get_cluster_sizes_filename();
        $full_path = $this->get_full_output_dir() . "/" . $filename;
        $lines = file($full_path);
        $sizes = array();
        foreach ($lines as $line) {
            $data = explode("\t", rtrim($line));
            $cluster_num = $data[0];
            $uniprot_size = $data[1];
            $uniref90_size = count($data) > 2 ? $data[2] : 0;
            $uniref50_size = count($data) > 3 ? $data[3] : 0;
            $sizes[$cluster_num] = array("uniprot" => $uniprot_size, "uniref90" => $uniref90_size, "uniref50" => $uniref50_size);
        }
        return $sizes;
    }
    // Read HMM length and number of sequences used to generate HMM.
    private static function parse_hmm($path) {
        $fh = fopen($path, "r");
        if (!$fh)
            return array();

        $stats = array();
        while (!feof($fh)) {
            $line = fgets($fh);
            $parts = preg_split('/\s+/', $line);
            if (isset($parts[0]) && $parts[0] === "HMM")
                break;
            if (count($parts) >= 2) {
                if ($parts[0] === "LENG")
                    $stats["length"] = $parts[1];
                elseif ($parts[0] === "NSEQ")
                    $stats["num_seq"] = $parts[1];
            }
        }

        return $stats;
    }


    // File names.
    private function get_stats_filename($no_prefix = false) {
        return $no_prefix ? "stats.txt" : $this->get_base_filename() . "_stats.txt";
    }
    private function get_cluster_sizes_filename($no_prefix = false) {
        return $no_prefix ? "cluster_sizes.txt" : $this->get_base_filename() . "_cluster_sizes.txt";
    }
    private function get_swissprot_desc_filename($want_clusters_file, $no_prefix = false) {
        $name = $want_clusters_file ? "swissprot_clusters_desc.txt" : "swissprot_singletons_desc.txt";
        return $no_prefix ? $name : $this->get_base_filename() . "_$name";
    }
    private function get_colored_ssn_filename() {
        return $this->get_base_filename() . ".xgmml";
    }
    private function get_colored_ssn_zip_filename() {
        return $this->get_base_filename() . ".zip";
    }
    private function get_node_files_zip_filename($domain_type, $seq_type) {
        $type_suffix = $seq_type == self::SEQ_UNIPROT ? "UniProt" : ($seq_type == self::SEQ_UNIREF50 ? "UniRef50" : "UniRef90");
        $dom_suffix = $domain_type == self::SEQ_DOMAIN ? "_Domain" : "";
        return $this->get_base_filename() . "_${type_suffix}${dom_suffix}_IDs.zip";
    }
    private function get_fasta_files_zip_filename($domain_type, $seq_type) {
        $dom_suffix = $domain_type == self::SEQ_DOMAIN ? "_Domain" : "";
        $type_suffix = $seq_type == self::SEQ_UNIREF50 ? "_UniRef50" : ($seq_type == self::SEQ_UNIREF90 ? "_UniRef90" : "");
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



    public function get_ssn_stats() {
        return array();
    }

    public function get_metadata() {
        $metadata = array();

        $db_version = $this->get_db_version();

        $stepe_path = $this->get_parent_path(true);
        $stepc_path = $this->get_parent_path(false);

        array_push($metadata, array("Job Number", $this->get_id()));
        array_push($metadata, array("Input Option", "Color SSN"));
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
        $frags = $this->include_fragments ? "Yes" : "No";
        array_push($metadata, array("Include Fragments", $frags));
        $extra = $this->get_hmm_options();
        if ($extra)
            array_push($metadata, array("Extra Options", $extra));

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

?>
