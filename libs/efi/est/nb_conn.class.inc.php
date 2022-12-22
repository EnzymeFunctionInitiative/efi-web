<?php
namespace efi\est;

require_once(__DIR__."/../../../init.php");

use \efi\global_functions;


// Neighborhood Connectivity
class nb_conn extends colorssn_shared {

    public function __construct($db, $id = 0, $is_example = false) {
        parent::__construct($db, $id, $is_example);
    }

    public function __destruct() {
    }

    
    public function is_hmm_and_stuff_job() { return false; }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // OVERLOADS

    public function get_create_type() {
        return self::create_type();
    }

    public static function create_type() {
        return "NBCONN";
    }

    protected function get_run_script() {
        return "create_nb_conn_job.pl";
    }

    protected function get_run_script_args($out) {
        $parms = parent::get_run_script_args_base($out);
        $name = $this->get_base_filename();
        $parms["--output-name"] = "\"$name\"";
        return $parms;
    }

    protected function load_generate($id) {
        $result = parent::load_generate($id);
        if (! $result) {
            return;
        }
        return $result;
    }

    public function get_insert_array($data) {
        $insert_array = parent::get_insert_array($data);
        return $insert_array;
    }

    public function get_base_filename() {
        $name = parent::get_base_filename();
        return $name .= "_NC";
    }

    // END OVERLOADS
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function get_metadata() {
        $meta = array();
        $metadata = parent::get_metadata_parent("Neighborhood Connectivity", $meta);
        return $metadata;
    }
}

