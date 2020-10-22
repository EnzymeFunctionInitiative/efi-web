<?php

require_once("colorssn_shared.class.inc.php");
require_once("../../libs/global_functions.class.inc.php");

class colorssn extends colorssn_shared {

    private $is_hmm_and_stuff = false;
    private $skip_fasta = false;


    public function __construct($db, $id = 0, $is_example = false) {
        parent::__construct($db, $id, $is_example);
    }

    public function __destruct() {
    }

    
    public function is_hmm_and_stuff_job() { return $this->is_hmm_and_stuff; }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // OVERLOADS

    public function get_create_type() {
        return self::create_type();
    }

    public static function create_type() {
        return "COLORSSN";
    }

    protected function get_run_script_args($out) {
        $parms = parent::get_run_script_args($out);
        if ($this->skip_fasta)
            $parms["--skip-fasta"] = "";
        return $parms;
    }

    protected function load_generate($id) {
        $result = parent::load_generate($id);
        if (! $result) {
            return;
        }

        $this->is_hmm_and_stuff = (isset($result["make_hmm"]) && $result["make_hmm"]) ? true : false;
        $this->skip_fasta = (isset($result["skip_fasta"]) && $result["skip_fasta"] === true) ? true : false;

        return $result;
    }

    public function get_insert_array($data) {
        $insert_array = parent::get_insert_array($data);
        if (isset($data->skip_fasta) && $data->skip_fasta === true)
            $insert_array["skip_fasta"] = true;
        return $insert_array;
    }


    // END OVERLOADS
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function get_metadata() {
        $meta = array();
        $metadata = parent::get_metadata_parent("Color SSN", $meta);
        return $metadata;
    }
}

?>
