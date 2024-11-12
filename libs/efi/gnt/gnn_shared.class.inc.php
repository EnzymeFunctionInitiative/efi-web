<?php
namespace efi\gnt;

require_once(__DIR__."/../../../init.php");

use \efi\global_settings;
use \efi\global_functions;
use \efi\gnt\arrow_api;


abstract class gnn_shared extends arrow_api {

    public function __construct($db, $id, $db_field_prefix, $is_example) {
        parent::__construct($db, $id, $db_field_prefix, $is_example);
    }

    public static function create($db, $parms) {
        if (!isset($parms["email"]))
            return false;
        if (!isset($parms["size"]))
            return false;
        if (!isset($parms["cooccurrence"]))
            return false;
        if (!isset($parms["filename"]) && !isset($parms["job_name"]))
            return false;

        if (!isset($parms["tmp_filename"]))
            $parms["tmp_filename"] = "";
        if (!isset($parms["job_name"]))
            $parms["job_name"] = "";
        if (!isset($parms["est_id"]))
            $parms["est_id"] = 0;
        if (!isset($parms["est_idx"]))
            $parms["est_idx"] = 0;
        if (!isset($parms["is_sync"]))
            $parms["is_sync"] = false;
        if (!isset($parms["db_mod"]))
            $parms["db_mod"] = "";
        if (!isset($parms["parent_id"]))
            $parms["parent_id"] = 0;
        if (!isset($parms["child_type"]))
            $parms["child_type"] = "";
        if (!isset($parms["extra_ram"]))
            $parms["extra_ram"] = false;

        return self::create_shared($db, $parms);
    }

    // For jobs originating from EST, we save the full path to the SSN into the filename field.
    // When the job processing workflow starts, we set the filename field in the db to be just the filename.
    private static function create_shared($db, $parms) {
        //$email, $size, $cooccurrence, $tmp_filename, $filename, $est_job_id, $job_name, $is_sync) {

        // Sanitize the filename
        $est_job_id = $parms["est_id"];
        //$filename = "";
        //if ($est_job_id)
        //    $filename = $parms["job_name"];
        //else
            $filename = preg_replace("([\._]{2,})", "", preg_replace("([^a-zA-Z0-9\-_\.])", "", $parms["filename"]));

        $job_name = preg_replace("/[^A-Za-z0-9 \-_?!#\$%&*()\[\],\.<>:;{}]/", "_", $parms["job_name"]);
        $gnn_status = $parms["is_sync"] ? __FINISH__ : __NEW__;

        $result = false;
        $key = global_functions::generate_key();

        $insert_array = array(
            "gnn_email" => $parms["email"],
            "gnn_key" => $key,
            "gnn_status" => $gnn_status,
        );
        $params_array = array(
            "neighborhood_size" => $parms["size"],
            "filename" => $filename,
            "cooccurrence" => $parms["cooccurrence"],
        );

        if ($est_job_id) {
            $insert_array["gnn_est_source_id"] = $est_job_id;
            $params_array["ssn_idx"] = $parms["est_idx"];
        }
        if ($parms["parent_id"] && $parms["child_type"]) {
            $insert_array["gnn_parent_id"] = $parms["parent_id"];
            $insert_array["gnn_child_type"] = $parms["child_type"];
        }

        if ($parms["job_name"])
            $params_array["job_name"] = $parms["job_name"];
        if ($parms["db_mod"] && preg_match("/^[A-Z0-9]{4}$/", $parms["db_mod"]))
            $params_array["db_mod"] = $parms["db_mod"];
        if ($parms["extra_ram"]) // true/false
            $params_array["extra_ram"] = $parms["extra_ram"];

        $insert_array["gnn_params"] = global_functions::encode_object($params_array);

        $new_id = $db->build_insert("gnn",$insert_array);
        $file_result = true;
        if (!$est_job_id && (!$parms["child_type"] || $parms["child_type"] != "filter")) {
            if ($new_id) {	
                if (global_functions::copy_to_uploads_dir($parms["tmp_filename"], $filename, $new_id) === false)
                    $file_result = false;
            } else {
                $file_result = false;
            }
        }

        if ($new_id && !$file_result) {
            $db->non_select_query("DELETE FROM gnn WHERE gnn_id = :id", array(":id" => $new_id));
            return false;
        }

        \efi\job_status::insert_new_manual($db, $new_id, "gnn");
        $info = array("id" => $new_id, "key" => $key);

        return $info;
    }
}


