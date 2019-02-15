<?php

require_once("option_base.class.inc.php");
require_once("../../libs/global_functions.class.inc.php");

class colorssn extends option_base {

    const SEQ_UNIPROT = 1;
    const SEQ_UNIREF50 = 2;
    const SEQ_UNIREF90 = 3;

    private $ssn_source_analysis_id;
    private $ssn_source_analysis_idx;
    private $ssn_source_key;
    private $ssn_source_id;


    public function __construct($db, $id = 0) {
        $this->file_helper = new file_helper(".xgmml", $id);
        parent::__construct($db, $id);
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
            $result->message .= "<br><b>Please upload a valid XGMML (zipped or unzipped) file.  The file extension must be .xgmml or .zip</b></br>";
        }

        return $result;
    }

    protected function get_run_script() {
        return "make_colorssn_job.pl";
    }

    protected function get_started_email_body() {
        $body = "The SSN has been uploaded and is being colored and analyzed." . $this->eol . $this->eol;
        return $body;
    }

    protected function get_completion_email_subject_line() {
        return "SSN colored";
    }

    protected function get_completion_email_body() {
        $body = "The SSN has been colored and analyzed. To view it, please go to THE_URL" . $this->eol . $this->eol;
        return $body;
    }

    protected function get_run_script_args($out) {
        $parms = array();

        $want_clusters_file = true;
        $want_singles_file = false;

        $parms["-queue"] = functions::get_memory_queue();
        $parms["-ssn-in"] = $this->file_helper->get_results_input_file();
        $parms["-ssn-out"] = "\"" . $this->get_colored_xgmml_filename_no_ext() . ".xgmml\"";
        $parms["-map-dir-name"] = "\"" . functions::get_colorssn_map_dir_name() . "\"";
        $parms["-map-file-name"] = "\"" . functions::get_colorssn_map_filename() . "\"";
        $parms["-domain-map-file-name"] = "\"" . functions::get_colorssn_domain_map_filename() . "\"";
        $parms["-out-dir"] = "\"" . $out->relative_output_dir . "\"";
        $parms["-stats"] = "\"" . $this->get_stats_filename() . "\"";
        $parms["-cluster-sizes"] = "\"" . $this->get_cluster_sizes_filename() . "\"";
        $parms["-sp-clusters-desc"] = "\"" . $this->get_swissprot_desc_filename($want_clusters_file) . "\"";
        $parms["-sp-singletons-desc"] = "\"" . $this->get_swissprot_desc_filename($want_singles_file) . "\"";

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

        $this->file_helper->on_load_generate($id, $result);

        return $result;
    }

    public function get_job_info($eol = "\r\n") {
        $message = "EFI-EST Job ID: " . $this->get_id() . $eol;
        $message .= "Computation Type: Color SSN" . $eol;
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
        return functions::get_results_dirname() . "/" . $this->get_output_dir();
    }
    public function get_full_output_dir() {
        return functions::get_results_dir() . "/" . $this->get_output_dir();
    }
    private function get_base_filename() {
        $parts = pathinfo($this->get_uploaded_filename());
        if (substr_compare($parts['filename'], ".xgmml", -strlen(".xgmml")) === 0) {
            $parts = pathinfo($parts['filename']);
        }
        return $this->get_id() . "_" . $parts['filename'] . "_coloredssn";
    }
    private function shared_get_web_path($filename) {
        $rel_path = $this->get_web_output_dir() . "/" . $filename;
        $full_path = $this->get_full_output_dir() . "/" . $filename;
        if (!file_exists($full_path))
            return "";
        return $rel_path;
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
    public function get_node_files_zip_web_path($seq_type) {
        $filename = $this->get_node_files_zip_filename($seq_type);
        return $this->shared_get_web_path($filename);
    }
    public function get_fasta_files_zip_web_path() {
        $filename = $this->get_fasta_files_zip_filename();
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
    private function get_node_files_zip_filename($seq_type) {
        $filename = $seq_type == colorssn::SEQ_UNIPROT ? "UniProt" : ($seq_type == colorssn::SEQ_UNIREF50 ? "UniRef50" : "UniRef90");
        return $this->get_base_filename() . "_${filename}_IDs.zip";
    }
    private function get_fasta_files_zip_filename() {
        return $this->get_base_filename() . "_FASTA.zip";
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
        if ($stepe_path) {
            $gid = $this->ssn_source_id;
            $aid = $this->ssn_source_analysis_id;
            array_push($metadata, array("Original SSN Job Number", "$gid/$aid (<a href='$stepc_path' title='EST $gid'>Original Dataset</a> | <a href='$stepe_path' title='EST Analysis ID $aid'>SSN Download</a>)"));
        }
        array_push($metadata, array("Time Started/Finished", global_functions::format_short_date($this->time_started) . " -- " .
            global_functions::format_short_date($this->time_completed)));
        array_push($metadata, array("Uploaded Filename", $this->get_uploaded_filename()));
        if (!empty($db_version))
            array_push($metadata, array("Database Version", $db_version));

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
            print "Extension: $ext\n";
            $valid = false;
        }
        return $valid;
    }
}

?>
