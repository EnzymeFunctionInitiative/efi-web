<?php
namespace efi\gnt;

require_once(__DIR__."/../../../init.php");

use \efi\gnt\settings;
use \efi\training\example_config;


class gnn_example extends gnn {

    private $data_dir = "";
    private $example_id = "";

    public function __construct($db, $id, $is_example_id) {
        parent::__construct($db, $id, false, false);

        $config = example_config::get_example_data($is_example_id);
        $table = example_config::get_gnt_table($config);
        $this->data_dir = example_config::get_gnt_data_dir($config);
        $this->example_id = $is_example_id;

        $this->load_gnn($id, $table);
    }
    
    public function get_rel_output_dir() {
        return __RESULTS_EXAMPLE_DIRNAME__ . "/" . $this->example_id . "/gnt";
    }

    protected function show_summary_details() {
        return false;
    }
    
    public function get_source_info() {
        return false;
    }

    public function get_output_dir($id = 0) {
        if (!$id)
            $id = $this->get_id();
        return $this->data_dir . "/" . $id;
    }
}


