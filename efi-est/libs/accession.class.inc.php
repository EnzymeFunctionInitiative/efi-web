<?php

require_once('family_shared.class.inc.php');
require_once('option_base.class.inc.php');
require_once('generate_helper.class.inc.php');
require_once('file_helper.class.inc.php');

class accession extends family_shared {


    private $file_helper;
    public $subject = "EFI-EST FASTA";


    private $domain;
    private $domain_family;
    private $domain_region;


    public function __construct($db,$id = 0) {
        $this->file_helper = new file_helper(".txt", $id);
        parent::__construct($db, $id);
    }

    public function __destruct() {
    }


    public function get_domain() { return $this->domain ? "on" : "off"; }
    public function get_domain_family() { return $this->domain ? $this->domain_family : ""; }
    public function get_domain_region_pretty() {
        if ($this->domain_region)
            return $this->domain_region == "nterminal" ? "N-terminal" : ($this->domain_region == "cterminal" ? "C-terminal" : "");
        else
            return "";
    }
    public function get_uploaded_filename() { return $this->file_helper->get_uploaded_filename(); }
    public function get_no_matches_download_path() {
        return functions::get_web_root() . "/" .
            $this->get_no_matches_download_file();
    }
    private function get_no_matches_download_file() {
        return
            functions::get_results_dirname() . "/" .
            $this->get_output_dir() . "/" . 
            $this->get_no_matches_filename(); 
    }
    private function get_no_matches_job_file() {
        return
            "output/" .
            $this->get_no_matches_filename(); 
    }
    private function get_no_matches_filename() {
        return
            $this->get_id() . "_" .
            functions::get_no_matches_filename(); 
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // OVERLOADS

    protected function get_create_type() {
        return self::create_type();
    }

    public static function create_type() {
        return "ACCESSION";
    }

    protected function validate($data) {
        $result = parent::validate($data);

        //if (!$this->verify_fraction($data->fraction)) {
        //    $result->errors = true;
        //    $result->message .= "<br><b>Please enter a valid fraction</b></br>";
        //}
        if ($data->uploaded_filename && !$this->verify_accession_file($data->uploaded_filename)) {
            $result->errors = true;
            $result->message .= "<br><b>Please upload a valid accession ID file.  The file extension must be .txt</b></br>";
        }
        if (!$data->field_input && !$data->uploaded_filename) {
            $result->errors = true;
            $result->message .= "<br><b>Please specify a list of accession IDs and/or upload a valid accession ID file.  The file extension must be .txt.</b></br>";
        }
        if (($data->domain == 'true' || $data->domain == 1) && !$data->domain_family) {
            $result->errors = true;
            $result->message .= "<br><b>If the domain option is selected, a family to be used to retrieve domain extents must be used.";
        }

        return $result;
    }

    //////////////////////////////
    // For file_helper
    protected function load_generate($id) {
        $result = parent::load_generate($id);
        if (! $result) {
            return;
        }

        if (isset($result['generate_domain']) && isset($result['generate_domain_family'])) {
            $this->domain = $result['generate_domain'];
            $this->domain_family = $result['generate_domain_family'];
            if (isset($result['generate_domain_region']))
                $this->domain_region = $result['generate_domain_region'];
        }

        $this->file_helper->on_load_generate($id, $result);

        return $result;
    }

    protected function post_insert_action($data, $insert_result_id) {
        $result = parent::post_insert_action($data, $insert_result_id);
        $result = $this->file_helper->on_post_insert_action($data, $insert_result_id, $result);

        if ($data->field_input) {
            $file = $this->file_helper->get_full_uploaded_path();
            file_put_contents($file, "\n" . $data->field_input . "\n", FILE_APPEND);
        }

        return $result;
    }
    
    public function get_insert_array($data) {
        $insert_array = parent::get_insert_array($data);
        
        if (($data->domain == 'true' || $data->domain == 1) && $data->domain_family) {
            $insert_array['generate_domain_family'] = $data->domain_family;
            $insert_array['generate_domain'] = 1;
            if ($data->domain_region && ($data->domain_region == "nterminal" || $data->domain_region == "cterminal"))
                $insert_array['generate_domain_region'] = $data->domain_region;
        }
        
        // We don't want to override this in case the user specifies a family to use with uniref
        $insert_array = $this->file_helper->on_append_insert_array($data, $insert_array);

        return $insert_array;
    }

    // End for file_helper
    //////////////////////////////
    
    protected function post_output_structure_create() {
        if (!$this->file_helper->copy_file_to_results_dir()) {
            $this->set_status(__FAILED__);
            return 'Accession file did not copy';
        } else {
            return '';
        }
    }

    protected function get_run_script() {
        return "create_generate_job.pl";
    }

    protected function get_run_script_args($out) {
        $parms = parent::get_run_script_args($out);
        $parms["-useraccession"] = $this->file_helper->get_results_input_file();
        $parms["-no-match-file"] = $this->get_no_matches_job_file();
        $parms["-domain"] = $this->get_domain();
        if ($this->get_domain() == "on") {
            $parms["-domain-family"] = $this->domain_family;
            if (global_settings::advanced_options_enabled())
                $parms["-force-domain"] = 1;
            if ($this->domain_region)
                $parms["-domain-region"] = $this->domain_region;
        }
        return $parms;
    }
    
    protected function get_email_job_info() {
        $message = parent::get_email_job_info();

        $upl_file = $this->file_helper->get_uploaded_filename();
        if ($upl_file) {
            $message .= "Uploaded Accession File: $upl_file" . PHP_EOL;
        }

        if (count($this->get_families())) {
            $message .= "Pfam/InterPro Families: " . $this->get_families_comma() . PHP_EOL;
        }

        $message .= "E-Value: " . $this->get_evalue() . PHP_EOL;
        $message .= "Fraction: " . $this->get_fraction() . PHP_EOL;
        //$message .= "Selected Program: " . $this->get_program() . PHP_EOL;

        return $message;
    }

    // END OVERLOADS
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function view_accession_file() {
        $filename = $this->get_id() . $this->file_helper->get_file_extension();
        $full_path = functions::get_uploads_dir() . "/" . $filename;
        $data = file_get_contents($full_path);
        $data_array = preg_split("/(\r\n|\r|\n)/", $data);
        $output = "";
        while (list($var, $val) = each($data_array)) {
            ++$var;
            $val = trim($val);
            $output .= "<br>" . $val;
        }
        return $output;
    }

    private function verify_accession_file($filename) {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $valid = true;
        if (!in_array($ext, functions::get_valid_accession_filetypes())) {
            $valid = false;
        }
        return $valid;
    }
}

?>
