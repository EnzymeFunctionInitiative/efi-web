<?php

require_once('option_base.class.inc.php');

class colorssn extends option_base {

    private $neighborhood_size = 10;
    private $cooccurrence = 50;

    public $subject = "EFI-EST Colored SSN Utility";

    public function __construct($db, $id = 0) {
        $this->file_helper = new file_helper(".xgmml", $id);
        parent::__construct($db, $id);
    }

    public function __destruct() {
    }


    public function get_uploaded_filename() { return $this->file_helper->get_uploaded_filename(); }
    public function get_neighborhood_size() { return $this->neighborhood_size; }
    public function get_cooccurrence() { return $this->cooccurrence; }
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

        //if (!$data->cooccurrence) {
        //    $data->coocurrence = 20;
        //}
        //if (!$this->verify_cooccurrence($data->cooccurrence)) {
        //    $result->errors = true;
        //    $result->message .= "<br><b>Please enter a valid co-occurrence lower limit</b></br>" . $data->cooccurrence . "  " . $data->evalue;
        //}
        if (!$this->verify_colorssn_file($data->uploaded_filename)) {
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

        //$parms["-queue"] = functions::get_generate_queue();
        $parms["-queue"] = functions::get_memory_queue();
        $parms["-ssn-in"] = $this->file_helper->get_results_input_file();
        $parms["-ssn-out"] = "\"" . $this->get_colored_xgmml_filename_no_ext() . ".xgmml\"";
        //$parms["-nb-size"] = $this->neighborhood_size;
        //$parms["-cooc"] = $this->cooccurrence;
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
        
        $this->file_helper->on_load_generate($id, $result);
        //HACK: This is a bit of a hack we are doing to avoid having to create new fields in the database.
        $this->cooccurrence = $result['generate_fraction'];
        $this->neighborhood_size = $result['generate_evalue'];

        return $result;
    }

    public function get_job_info($eol = "\r\n") {
        $message = "EFI-EST Job ID: " . $this->get_id() . $eol;
        $message .= "Computation Type: Color SSN" . $eol;
        return $message;
    }

    protected function post_insert_action($data, $insert_result_id) {
        $result = parent::post_insert_action($data, $insert_result_id);
        $result = $this->file_helper->on_post_insert_action($data, $insert_result_id, $result);
        return $result;
    }

    public function get_insert_array($data) {
        $insert_array = parent::get_insert_array($data);
        $insert_array = $this->file_helper->on_append_insert_array($data, $insert_array);
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

    private function verify_cooccurrence($cooccurrence) {
        return ($cooccurrence >= 1 && $cooccurrence <= 100);
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
