<?php
namespace efi\est;

require_once(__DIR__."/../../../init.php");


class taxonomy_job extends family_shared {

    public $subject = "EFI-EST Pfam/InterPro Taxonomy";
    
    public function __construct($db, $id = 0, $is_example = false) {
        parent::__construct($db, $id, $is_example);
    }

    public function __destruct() {
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // OVERLOADS

    protected function get_create_type() {
        return self::create_type();
    }

    public static function create_type() {
        return "TAXONOMY";
    }

    protected function get_insert_array($data) {
        $insert_array = parent::get_insert_array($data);
        return $insert_array;
    }

    protected function get_run_script_args($out) {
        $parms = parent::get_run_script_args($out);
        $parms["--tax-search-only"] = "";
        return $parms;
    }

    protected function load_generate($id) {
        $result = parent::load_generate($id);
        if (! $result) {
            return;
        }
        return $result;
    }

    protected function validate($data) {
        $result = parent::validate($data);

        if ($data->families == "") {
            $result->errors = true;
            $result->message .= "Please enter valid InterPro and Pfam numbers";
        }

        return $result;
    }

    protected function get_email_job_info() {
        $message = parent::get_email_job_info();
        $message .= "Pfam/InterPro Families: " . $this->get_families_comma() . PHP_EOL;
        if ($this->uniref_version)
            $message .= "Using UniRef" . $this->uniref_version . PHP_EOL;
        
        return $message;
    }

    // END OVERLOADS
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
}

