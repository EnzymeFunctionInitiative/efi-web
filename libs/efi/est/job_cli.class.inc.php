<?php
namespace efi\est;

require_once(__DIR__."/../../../init.php");

use \efi\est\functions;
use \efi\est\job_factory;
use \efi\est\blast;


class job_cli {

    public static function get_job_object($db, $job_id) {
        return job_factory::create($db, $job_id);
    }

    public static function get_non_blast_failed_file_exists($run_type, $job_obj) {
        if ($run_type != blast::create_type())
            return $job_obj->check_max_blast_failed_file();
        else
            return false;
    }

    public static function get_non_blast_bad_input_file_format_file_exists($run_type, $job_obj) {
        if ($run_type != blast::create_type())
            return $job_obj->check_bad_input_format_file();
        else
            return false;
    }

    public static function get_blast_failed_file_exists($run_type, $job_obj) {
        if ($run_type == blast::create_type()) 
            return $job_obj->check_fail_file();
        else
            return false;
    }

    public static function get_jobs($db, $run_type, $status) {
        $job_list = array();
        $job_ids = functions::get_job_ids($db, $run_type, $status);
        for ($i = 0; $i < count($job_ids); $i++) {
            $job = job_factory::create($db, $job_ids[$i]["generate_id"]);
            array_push($job_list, $job);
        }
        return $job_list;
    }
}


