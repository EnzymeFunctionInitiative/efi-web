<?php

require_once("colorssn_shared.class.inc.php");
require_once("../../libs/global_functions.class.inc.php");

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
        if ($this->extra_ram)
            $parms["--ram"] = "500gb";
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
    public function get_nc_legend_filename() {
        return $this->get_base_filename() . "_legend.png";
    }
    public function get_nc_legend_full_path() {
        $filename = "legend.png";
        return $this->shared_get_full_path($filename);
    }
    public function get_nc_table_filename() {
        return $this->get_base_filename() . "_table.tab";
    }
    public function get_nc_table_full_path() {
        $filename = "nc.tab";
        return $this->shared_get_full_path($filename);
    }
    public function get_colored_ssn_zip_full_path() {
        $filename = "ssn.zip";
        return $this->shared_get_full_path($filename);
    }

    // END OVERLOADS
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function get_metadata() {
        $meta = array();
        $metadata = parent::get_metadata_parent("Neighborhood Connectivity", $meta);
        return $metadata;
    }
}

