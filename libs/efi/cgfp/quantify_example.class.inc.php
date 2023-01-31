<?php
namespace efi\cgfp;

require_once(__DIR__."/../../../init.php");

use \efi\global_functions;
use \efi\cgfp\functions;
use \efi\cgfp\settings;
use \efi\cgfp\job_shared;
use \efi\file_types;


class quantify_example extends quantify_shared {

    private $example_dir = "";
    private $example_web_path = "";

    public function __construct($db, $example_dir, $example_web_path) {
        parent::__construct($db);
        $this->example_dir = $example_dir;
        $this->example_web_path = $example_web_path;
        $this->load_from_example();
        $this->set_use_prefix(false);
    }

    private function load_from_example() {
        $ex_dir = $this->example_dir;
        $config_file = "$ex_dir/config.txt";

        $fh = fopen($config_file, "r");
        if ($fh === false)
            return;

        while (($data = fgetcsv($fh, 1000, "\t")) !== false) {
            if (isset($data[0]) && $data[0] && $data[0][0] == "#" || count($data) < 2)
                continue; // skip comments

            $key = $data[0];
            $val = $data[1];
            if ($key == "id") {
                $this->id = $val;
            } elseif ($key == "filename") {
                $this->set_filename($val);
            } elseif ($key == "metagenome_ids") {
                $this->metagenome_ids = explode(",", $val);
            } elseif ($key == "identify_ref_db") {
                $this->ref_db = $val;
            } elseif ($key == "identify_search_type") {
                $this->identify_search_type = $val;
            } elseif ($key == "identify_diamond_sens") {
                $this->identify_diamond_sens = $val;
            } elseif ($key == "identify_cdhit_sid") {
                $this->identify_cdhit_sid = $val;
            } elseif ($key == "quantify_search_type") {
                $this->set_search_type($val);
            } elseif ($key == "max_seq_len") {
                $this->set_max_seq_len($val);
            } elseif ($key == "min_seq_len") {
                $this->set_min_seq_len($val);
            } elseif ($key == "mg_db_index") {
                $this->mg_db_index = $val;
            }
        }
        fclose($fh);
    }

    protected function get_extra_metadata($id, $tab_data) {
        return array();
    }

    public function get_identify_output_path($parent_id = 0) {
        return $this->example_dir;
    }

    protected function get_quantify_output_path($parent_id = 0) {
        return $this->example_dir;
    }

    public function get_metadata() {
        $md_file = $this->example_dir . "/metadata.tab";
        $metadata = $this->get_metadata_shared($md_file);
        return $metadata;
    }

    public function get_file_size($file_type) {
        if ($file_type == file_types::FT_sbq_ssn) {
            $file_path = $this->get_ssn_file_path();
            $size = filesize($file_path);
            $mb_size = global_functions::bytes_to_megabytes(filesize($file_path));
            if ($mb_size)
                return $mb_size;
            else
                return "<1";
        } else {
            return $this->get_file_size_base($file_type, self::Quantify);
        }
    }
    public function get_file_path($file_type) {
        if ($file_type == file_types::FT_sbq_ssn) {
            return $this->get_ssn_file_path();
        } else {
            return $this->get_file_path_base($file_type, self::Quantify);
        }
    }
    public function get_file_name($file_type) {
        if ($file_type == file_types::FT_sbq_ssn) {
            return $this->get_filename();
        } else {
            return $this->get_file_name_base($file_type, self::Quantify);
        }
    }
    private function get_ssn_file_path() {
        return $this->example_dir . "/" . $this->get_filename();
    }
}


