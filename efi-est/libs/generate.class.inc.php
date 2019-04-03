<?php

require_once('family_shared.class.inc.php');

class generate extends family_shared {

    private $domain;
    public $subject = "EFI-EST Pfam/Interpro";
    
    public function __construct($db,$id = 0) {
        parent::__construct($db, $id);
    }

    public function __destruct() {
    }

    public function get_domain() { return $this->domain ? "on" : "off"; }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // OVERLOADS

    protected function get_create_type() {
        return self::create_type();
    }

    public static function create_type() {
        return "FAMILIES";
    }

    protected function get_insert_array($data) {
        $insert_array = parent::get_insert_array($data);

        $domain_bool = 0;
        if ($data->domain == 'true' || $data->domain == 1)
            $domain_bool = 1;

        $insert_array['generate_domain'] = $domain_bool;

        return $insert_array;
    }

    protected function get_run_script_args($out) {
        $parms = parent::get_run_script_args($out);
        $parms["-domain"] = $this->get_domain();
        if (global_settings::advanced_options_enabled()) // Dev site
            $parms["-force-domain"] = 1;
        return $parms;
    }

    protected function load_generate($id) {
        $result = parent::load_generate($id);
        if (! $result) {
            return;
        }

        $this->domain = $result['generate_domain'];

        return $result;
    }

    protected function validate($data) {
        $result = parent::validate($data);

        if (!$this->verify_fraction($data->fraction)) {
            $result->errors = true;
            $result->message .= "<br><b>Please enter a valid fraction</b></br>";
        }

        return $result;
    }

    protected function get_email_job_info() {
        $message = parent::get_email_job_info();
        $message .= "Pfam/Interpro Families: " . $this->get_families_comma() . PHP_EOL;
        $message .= "E-Value: " . $this->get_evalue() . PHP_EOL;
        $message .= "Fraction: " . $this->get_fraction() . PHP_EOL;
        $message .= "Enable Domain: " . $this->get_domain() . PHP_EOL;
        if ($this->uniref_version)
            $message .= "Using UniRef " . $this->uniref_version . PHP_EOL;
        //$message .= "Selected Program: " . $this->get_program() . PHP_EOL;
        
        return $message;
    }

    // END OVERLOADS
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
}
?>
