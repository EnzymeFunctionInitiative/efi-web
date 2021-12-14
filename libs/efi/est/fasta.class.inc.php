<?php
namespace efi\est;

require_once(__DIR__."/../../../init.php");

use \efi\est\file_helper;
use \efi\est\functions;
use \efi\est\family_shared;


class fasta extends family_shared {


    private $userdat_file = "output.dat";
    private $file_helper;
    private $option;
    public $subject = "EFI-EST FASTA";

    
    public function __construct($db, $id = 0, $option = "C", $is_example = false) {
        $this->file_helper = new file_helper(".fasta", $id);
        $this->option = $option;
        parent::__construct($db, $id, $is_example);
    }

    public function __destruct() {
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // ACCESSOR FUNCTIONS SPECIFIC TO FASTA

    public function get_fasta_file() { return $this->get_id() . $this->file_helper->get_file_extension(); }
    public function get_uploaded_filename() { return $this->file_helper->get_uploaded_filename(); }
    public function get_userdat_file() { return $this->userdat_file; }
    public function get_userdat_file_path() {
        return functions::get_results_dir() . "/" . $this->get_id() . "/output/" . $this->get_userdat_file();
    }
    public function get_full_fasta_file_path() {
        return functions::get_results_dir() . "/" . $this->get_id() . "/" . $this->get_fasta_file();
    }

    // END ACCESSOR FUNCTIONS SPECIFIC TO FASTA
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // OVERLOADS

    //////////////////////////////
    // For file_helper
    protected function load_generate($id) {
        $result = parent::load_generate($id);
        if (! $result) {
            return;
        }

        $this->file_helper->on_load_generate($id, $result);

        return $result;
    }

    protected function post_insert_action($data, $insert_result_id) {
        $result = parent::post_insert_action($data, $insert_result_id);
        $result = $this->file_helper->on_post_insert_action($data, $insert_result_id, $result);

        if (!$result->errors && $this->option == "E") {
        }

        if ($data->field_input) {
            $file = $this->file_helper->get_full_uploaded_path();
            file_put_contents($file, "\n\n\n\n\n" . $data->field_input . "\n", FILE_APPEND);
        }

        return $result;
    }
    
    public function get_insert_array($data) {
        $insert_array = parent::get_insert_array($data);
        $insert_array = $this->file_helper->on_append_insert_array($data, $insert_array);
        if (isset($data->include_all_seq) && $data->include_all_seq === true)
            $insert_array['include_all_seq'] = true;
        return $insert_array;
    }

    // End for file_helper
    //////////////////////////////
    
    protected function get_create_type() {
        return self::create_type($this->option);
    }

    public static function create_type($optionType = "") {
        if ($optionType == "E")
            return "FASTA_ID";
        else
            return "FASTA";
    }

    protected function validate($data) {
        $result = parent::validate($data);

        if (!$this->verify_fraction($data->fraction)) {
            $result->errors = true;
            $result->message .= "<br><b>Please enter a valid fraction</b></br>";
        }
        if ($data->uploaded_filename && !$this->verify_fasta($data->uploaded_filename)) {
            $result->errors = true;
            $result->message .= "<br><b>Please upload a valid fasta file.  The file extension must be .txt, .fasta, or .fa</b></br>";
        } else if (!$data->field_input && !$data->uploaded_filename) {
            $result->errors = true;
            $result->message .= "<br><b>Please input FASTA sequences or upload a valid fasta file.  The file extension must be .txt, .fasta, or .fa</b></br>";
        }

        return $result;
    }

    protected function post_output_structure_create() {
        if (!$this->file_helper->copy_file_to_results_dir()) {
            return "Unable to move uploaded file to the result directory.";
        }
        return '';
    }

    protected function get_run_script_args($out) {
        $parms = parent::get_run_script_args($out);

        // This works because as a part of the import process the fasta file is copied to the results directory.
        $parms["-userfasta"] = "\"" . $this->get_full_fasta_file_path() . "\"";
        if ($this->option == "E") {
            $parms["-use-fasta-headers"] = "";
        } else {
            $parms["-userdat"] = $out->relative_output_dir . "/" . $this->get_userdat_file();
        }

        return $parms;
    }

    protected function get_email_job_info() {
        $message = parent::get_email_job_info();

        $upl_file = $this->file_helper->get_uploaded_filename();
        if ($upl_file) {
            $message .= "Uploaded Fasta File: $upl_file" . PHP_EOL;
        }

        if (count($this->get_families())) {
            $message .= "Pfam/InterPro Families: ";
            $message .= $this->get_families_comma() . PHP_EOL;
        }

        $message .= "E-Value: " . $this->get_evalue() . PHP_EOL;
        $message .= "Fraction: " . $this->get_fraction() . PHP_EOL;
        //$message .= "Selected Program: " . $this->get_program() . PHP_EOL;

        return $message;
    }

    // END OVERLOADS
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // FUNCTIONS SPECIFIC TO FASTA
    
    public function view_fasta_file() {
        $filename = $this->get_id() . $this->file_helper->get_file_extension();
        $full_path = functions::get_uploads_dir() . "/" . $filename;
        $data = file_get_contents($full_path);
        $data_array = explode("\n", $data);
        $output = "";
        while (list($var, $val) = each($data_array)) {
            ++$var;
            $val = trim($val);
            $output .= "<br>" . $val;
        }
        return $output;

    }
    
    public function download_fasta_file() {
        $filename = $this->get_id() . $this->file_helper->get_file_extension();
        $full_path = functions::get_uploads_dir() . "/" . $filename;
        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary"); 
        header("Content-disposition: attachment; filename=\"" . basename($full_path) . "\""); 
        readfile($full_path);

    }

    private function verify_fasta($filename) {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $valid = true;
        if (!in_array($ext,functions::get_valid_fasta_filetypes())) {
            $valid = false;	
        }
        return $valid;
    }

    // END FUNCTIONS SPECIFIC TO FASTA
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

