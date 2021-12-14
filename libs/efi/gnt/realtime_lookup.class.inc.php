<?php
namespace efi\gnt;

require_once(__DIR__."/../../../init.php");

use \efi\global_functions;
use \efi\gnt\settings;


class realtime_lookup {

    public static function get_job_file($id) {

        if (!preg_match("/^[a-zA-Z0-9]+$/", $id))
            return array("rt_id" => $id, "status" => false);

        $target_dir = settings::get_realtime_output_dir() . "/$id";
        $target_file = "$target_dir/results.sqlite";

        if (!file_exists($target_file))
            return array("rt_id" => $id, "status" => false);

        return array("output_file" => $target_file, "rt_id" => $id, "status" => true);
    }

        
    public static function execute_job($ids) {

        if (!is_array($ids)) {
            $ids = trim($ids);
            $ids = strtoupper($ids);
            $items = preg_split("/[\n\r ,]+/", $ids);
            $ids = $items;
        }
        $ids = implode("\n", $ids);

        $id = global_functions::generate_key();
        $target_dir = settings::get_realtime_output_dir() . "/$id";
        mkdir($target_dir);

        $input_file = "$target_dir/idlist.txt";
        $output_file = "$target_dir/results.sqlite";

        file_put_contents($input_file, $ids);

        $binary = settings::get_realtime_script();
        $exec = "source /etc/profile\n";
        $exec .= "module load " . settings::get_gnn_module() . "\n";
        $exec .= "module load " . settings::get_efidb_module() . "\n";
        $exec .= $binary . " ";
        $exec .= " --id-file \"$input_file\"";
        $exec .= " --output \"$output_file\"";

        $exit_status = 1;
        $output_array = array();
        $output = exec($exec, $output_array, $exit_status);
        $output = trim(rtrim($output));

        if ($exit_status) {
            return array("rt_id" => $id, "status" => false);
        }

        return array("output_file" => $output_file, "rt_id" => $id, "status" => true);
    }
}



