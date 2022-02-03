<?php
namespace efi\taxonomy;

require_once(__DIR__."/../../../init.php");

use \efi\global_settings;
use \efi\global_functions;
use \efi\job_list_ui;


class taxonomy_ui extends job_list_ui {

    // For GNT, when we implement it

    public function __construct($db, $email, $include_failed_jobs) {
        parent::__construct($db, "taxonomy", $email, $include_failed_jobs, "tax-hl");
    }

    protected function get_job_name($filename, $params) {
        $job_name = "<span class='job-name'>$filename</span><br>" . "</span>";
        return $job_name;
    }

    protected function get_extra_job_info($row, $params) {
        return false;
    }

    protected function post_process_job_list($jobs) {
        return false;
    }

    protected function get_url($id, $key, $job) {
        $base_url_dir = global_settings::get_web_path("taxonomy");
        return "${base_url_dir}stepc.php?id=$id&key=$key";
    }
    
    protected function check_for_indent($job) {
        return false;
    }
}


