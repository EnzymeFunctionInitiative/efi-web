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
        $exec = $binary . " ";
        $exec .= " --id-file \"$input_file\"";
        $exec .= " --output \"$output_file\"";

        $handle = popen($exec . " 2>&1", "r");
        $read = fread($handle, 2096);
        pclose($handle);
        $read = trim(rtrim($read));

        // If $read contains text the command failed.
        if ($read) {
            return array("rt_id" => $id, "status" => false);
        }

        return array("output_file" => $output_file, "rt_id" => $id, "status" => true);
    }
}



