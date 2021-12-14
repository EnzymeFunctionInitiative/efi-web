<?php
namespace efi;

require_once(__DIR__."/../../init.php");

use \efi\global_functions;
use \efi\global_settings;


class data_retention {

    private $db;
    private $base_dir;

    function __construct($db, $base_dir) {
        $this->db = $db;
        $this->base_dir = $base_dir;
    }

    public function get_expired_jobs($table_name, $job_type) {
        $tname = $table_name;
        $idfield = "${tname}_id";
        $timefield = "${tname}_time_completed";
        $timefield_started = "${tname}_time_started";
        $statusfield = "${tname}_status";
        $exp_date = global_functions::get_file_retention_start_date();
        $archived_exp_date = global_functions::get_archived_retention_start_date();
        $failed_exp_date = global_functions::get_failed_retention_start_date();
        $sql = "SELECT $tname.$idfield, $timefield FROM $tname " .
            "LEFT JOIN job_group ON job_group.job_id = $tname.$idfield " .
            "WHERE (job_group.user_group IS NULL OR job_group.job_type != '$job_type') AND (" . 
            "($timefield < '$exp_date' AND $timefield != '0000-00-00 00:00:00')" .
            " OR " .
            "($timefield IS NULL AND $timefield_started < '$exp_date')" .
            " OR " .
            "($tname.$statusfield = '" . __FAILED__ . "' AND $timefield_started < '$failed_exp_date')" .
            " OR " .
            "($tname.$statusfield = '" . __ARCHIVED__ . "' AND " .
                "($timefield = '0000-00-00 00:00:00' OR " .
                " $timefield_started = '0000-00-00 00:00:00' OR " .
                " $timefield_started < '$archived_exp_date')" .
            ")" .
            ")";# OR
        $results = $this->db->query($sql);

        $jobs = array();

        foreach ($results as $row) {
            $id = $row["$idfield"];
            $dir_path = $this->base_dir . "/" . $id;
            if (file_exists($dir_path)) // may be removed already
                $jobs[$id] = $dir_path;
        }

        return $jobs;
    }
}


