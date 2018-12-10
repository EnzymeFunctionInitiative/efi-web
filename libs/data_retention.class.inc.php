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
        $exp_date = user_auth::get_start_date_window();
        $sql = "SELECT $tname.$idfield, $timefield FROM $tname " .
            "LEFT JOIN job_group ON job_group.$idfield = $tname.$idfield " .
            "WHERE $timefield < '$exp_date' AND job_group.user_group IS NULL " .
            "AND $timefield != '0000-00-00 00:00:00'";

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
