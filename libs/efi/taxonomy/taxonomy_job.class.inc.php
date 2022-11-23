<?php

namespace efi\taxonomy;

use \efi\global_functions;
use \efi\global_settings;


class taxonomy_job {

    public static function get_job_info($db, $id, $key, $tree_id, $id_type, $tax_name = "") {
        if (!is_numeric($id))
            return false;
        if (!isset($tax_name))
            $tax_name = "";
        $info = array("full_path" => "", "source_id" => $id, "source_key" => "", "tree_id" => $tree_id, "id_type" => $id_type);
        $sql = "SELECT * FROM generate WHERE generate_id = $id";
        if ($key !== false)
            $sql .= " AND generate_key = " . $db->escape_string($key);
        $results = $db->query($sql);
        if ($results) {
            $results = $results[0];
            $base_est_results = global_settings::get_est_results_dir();
            $est_results_name = "output";
            $est_results_dir = "$base_est_results/$id/$est_results_name";
            $params = global_functions::decode_object($results["generate_params"]);
            $full_path = "$est_results_dir/tax.json";
            $info["full_path"] = $full_path;
            $info["source_key"] = $results["generate_key"];
            $the_name = $params["generate_job_name"] . ($tax_name ? " $tax_name" : "");
            if ($id_type == "uniprot")
                $the_name = "$the_name UniProt";
            else if ($id_type == "uniref90")
                $the_name = "$the_name UniRef90";
            else if ($id_type == "uniref50")
                $the_name = "$the_name UniRef50";
            $info["source_name"] = $the_name;
            $info["filename"] = $params["generate_job_name"];
            return $info;
        }
        return false;
    }
}


