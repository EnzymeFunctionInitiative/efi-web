<?php

require_once("global_settings.class.inc.php");
require_once("global_functions.class.inc.php");
require_once("user_auth.class.inc.php");

class data_retention {

    private $db;
    private $base_dir;

    function __construct($db, $base_dir) {
        $this->db = $db;
        $this->base_dir = $base_dir;
    }

    public function get_expired_jobs($table_name) {
        $tname = $table_name;
        $idfield = "${tname}_id";
        $timefield = "${tname}_time_completed";
        $timefield_started = "${tname}_time_started";
        $statusfield = "${tname}_status";
        $exp_date = global_functions::get_file_retention_start_date();
        $failed_exp_date = global_functions::get_failed_retention_start_date();
        $sql = "SELECT $tname.$idfield, $timefield FROM $tname " .
            "LEFT JOIN job_group ON job_group.$idfield = $tname.$idfield " .
            "WHERE job_group.user_group IS NULL AND (" . 
            "($timefield < '$exp_date' AND $timefield != '0000-00-00 00:00:00')" .
            " OR " .
            "($timefield IS NULL AND $timefield_started < '$exp_date')" .
            " OR " .
            "($tname.$statusfield = '" . __FAILED__ . "' AND $timefield_started < '$failed_exp_date')" .
            " OR " .
            "($tname.$statusfield = '" . __ARCHIVED__ . "' AND ($timefield = '0000-00-00 00:00:00' OR $timefield_started = '0000-00-00 00:00:00'))" .
            ")";# OR
        $results = $this->db->query($sql);

        $jobs = array();

        foreach ($results as $row) {
            $id = $row["$idfield"];
            $dir_path = $this->base_dir . "/" . $id;
            #print "$id $dir_path   " . $row["${tname}_time_completed"] . "\n";
            if (file_exists($dir_path)) // may be removed already
                $jobs[$id] = $dir_path;
        }

        return $jobs;
    }
}

?>
