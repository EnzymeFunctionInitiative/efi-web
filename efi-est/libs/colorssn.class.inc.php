<?php

require_once('option_base.class.inc.php');

class colorssn extends option_base {


    private $ssn_source_id;
    private $ssn_source_idx; 


    public function __construct($db, $id = 0) {
        $this->file_helper = new file_helper(".xgmml", $id);
        parent::__construct($db, $id);
    }

    public function __destruct() {
    }


    public function get_uploaded_filename() { return $this->file_helper->get_uploaded_filename(); }
    public function get_colored_xgmml_filename_no_ext() {
        $parts = pathinfo($this->get_uploaded_filename());
        if (substr_compare($parts['filename'], ".xgmml", -strlen(".xgmml")) === 0) {
            $parts = pathinfo($parts['filename']);
        }
        return $this->get_id() . "_" . $parts['filename'] . "_coloredssn";
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

        $parms["-queue"] = functions::get_memory_queue();
        $parms["-ssn-in"] = $this->file_helper->get_results_input_file();
        $parms["-ssn-out"] = "\"" . $this->get_colored_xgmml_filename_no_ext() . ".xgmml\"";
        $parms["-map-dir-name"] = "\"" . functions::get_colorssn_map_dir_name() . "\"";
        $parms["-map-file-name"] = "\"" . functions::get_colorssn_map_file_name() . "\"";
        $parms["-out-dir"] = "\"" . $out->relative_output_dir . "\"";

        return $parms;
    }

    protected function load_generate($id) {
        $result = parent::load_generate($id);
        if (! $result) {
            return;
        }

        if (isset($result["generate_color_ssn_source_id"]) && isset($result["generate_color_ssn_source_idx"])) {
            $this->ssn_source_id = $result["generate_color_ssn_source_id"];
            $this->ssn_source_idx = $result["generate_color_ssn_source_idx"];
            $info = functions::get_analysis_job_info($this->db, $this->ssn_source_id);
            if ($info) {
                $file_info = functions::get_ssn_file_info($info, $this->ssn_source_idx);
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
