<?php
namespace efi\est;

require_once(__DIR__."/../../../init.php");

use \efi\global_functions;


// Neighborhood Connectivity
class conv_ratio extends colorssn_shared {

    private $ascore = "";

    public function __construct($db, $id = 0, $is_example = false) {
        parent::__construct($db, $id, $is_example);
    }

    public function __destruct() {
    }

    public function get_ascore() { return $this->ascore; }
    public function is_hmm_and_stuff_job() { return false; }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // OVERLOADS

    public function get_create_type() {
        return self::create_type();
    }

    public static function create_type() {
        return "CONVRATIO";
    }

    protected function get_run_script() {
        return "create_cluster_conv_ratio_job.pl";
    }

    protected function get_run_script_args($out) {
        $parms = parent::get_run_script_args_base($out);
        $name = $this->get_base_filename();
        $parms["--ascore"] = $this->ascore;
        return $parms;
    }

    protected function load_generate($id) {
        $result = parent::load_generate($id);
        if (! $result) {
            return;
        }
        $this->ascore = isset($result["ascore"]) ? $result["ascore"] : 0;
        return $result;
    }

    public function get_insert_array($data) {
        $insert_array = parent::get_insert_array($data);
        $insert_array["ascore"] = $data->ascore;
        return $insert_array;
    }

    public function get_base_filename() {
        $name = parent::get_base_filename();
        return $name .= "_CR";
    }

    // END OVERLOADS
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function get_metadata() {
        $meta = array();
        array_push($meta, array("Alignment Score", $this->ascore));
        $metadata = parent::get_metadata_parent("Convergence Ratio", $meta);
        return $metadata;
    }
}

