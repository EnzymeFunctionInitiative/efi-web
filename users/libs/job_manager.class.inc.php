<?php

require_once(__DIR__ . "/../../libs/user_auth.class.inc.php");


class job_manager {

    private $db_name = "";
    private $table_name = "";
    private $db = "";
    private $jobs_by_id = array();

    function __construct($db, $db_name, $table_name) {
        $this->table_name = $table_name;
        $this->db_name = $db_name;
        $this->db = $db;
        $this->get_jobs();
    }

    private function get_jobs() {
        $db_name = $this->db_name;
        $table = $this->table_name;
        $col_email = "${table}_email";
        $col_key = "${table}_key";
        $col_id = "${table}_id";
        $col_status = "${table}_status";
        $col_time_created = "${table}_time_created";
        $col_time_started = "${table}_time_started";
        $col_time_completed = "${table}_time_completed";

        $cols = implode(",", array($col_id, $col_key, $col_email, $col_status, $col_time_created,
            $col_time_started, $col_time_completed));

        $order_sql = "ORDER BY $col_id";
        $sql = "SELECT $cols FROM $db_name.$table $order_sql";

        $rows = $this->db->query($sql);

        if (!$rows) {
            return false;
        }

        foreach ($rows as $result) {
            $status = $result[$col_status];
            $email = $result[$col_email];
            $key = $result[$col_key];
            $id = $result[$col_id];

            $this->jobs_by_id[$id] = array("email" => $email, "key" => $key,
                                           "time_completed" => $result[$col_time_completed],
                                           "time_started" => $result[$col_time_started],
                                           "time_created" => $result[$col_time_created],
                                           "status" => $status
                                       );
        }  
        
        return true;
    }

    public function get_all_job_ids() {
        $ids = array();
        foreach ($this->jobs_by_id as $id => $job) {
            array_push($ids, $id);
        }
        return $ids;
    }

    public function get_job_by_id($job_id) {
        $id_table = job_types::Identify;
        $job = array();
        if (isset($this->jobs_by_id[$job_id])) {
            $job["id"] = $job_id;
            $job["key"] = $this->jobs_by_id[$job_id]["key"];
            $job["email"] = $this->jobs_by_id[$job_id]["email"];
            $tco = $this->jobs_by_id[$job_id]["time_completed"];
            if ($tco && $tco != "FAILED")
                $job["time_completed"] = self::format_short_date($tco);
            else
                $job["time_completed"] = $this->jobs_by_id[$job_id]["status"];
            $ts = $this->jobs_by_id[$job_id]["time_started"];
            if ($ts)
                $job["time_started"] = self::format_short_date($ts);
            else
                $job["time_started"] = "";
            $job["time_created"] = $this->jobs_by_id[$job_id]["time_created"];
        }
        return $job;
    }

//    public function get_new_job_ids() {
//        if (!isset($this->jobs_by_status[__NEW__])) {
//            return array();
//        }
//
//        $ids = array();
//        foreach ($this->jobs_by_status[__NEW__] as $job) {
//            array_push($ids, $job["id"]);
//        }
//
//        return $ids;
//    }
//
//    public function get_running_job_ids() {
//        if (!isset($this->jobs_by_status[__RUNNING__])) {
//            return array();
//        }
//
//        $ids = array();
//        foreach ($this->jobs_by_status[__RUNNING__] as $job) {
//            array_push($ids, $job["id"]);
//        }
//
//        return $ids;
//    }
//
//    public function get_job_key($job_id) {
//        if (!isset($this->jobs_by_id[$job_id])) {
//            return "";
//        } else {
//            return $this->jobs_by_id[$job_id]["key"];
//        }
//    }
//
//    public function get_jobs_by_user($user_token) {
//        $email = user_auth::get_email_from_token($this->db, $user_token);
//
//        $start_date = user_auth::get_start_date_window();
//        $id_sql = "SELECT identify_id, identify_key, identify_time_completed, identify_filename, identify_status " .
//            "FROM identify WHERE identify_email='$email' AND (identify_time_completed >= '$start_date' " .
//            "OR (identify_time_created >= '$start_date' AND (identify_status = 'NEW' OR identify_status = 'RUNNING'))) " .
//            "ORDER BY identify_status, identify_time_completed DESC";
//        $id_rows = $this->db->query($id_sql);
//
//        $jobs = array();
//
//        foreach ($id_rows as $id_row) {
//            $comp_result = $this->get_completed_date_label($id_row["identify_time_completed"], $id_row["identify_status"]);
//            $job_name = $id_row["identify_filename"];
//            $comp = $comp_result[1];
//            $is_completed = $comp_result[0];
//
//            $id_id = $id_row["identify_id"];
//            $key = $id_row["identify_key"];
//
//            array_push($jobs, array("id" => $id_id, "key" => $key, "job_name" => $job_name, "is_completed" => $is_completed,
//                                    "is_quantify" => false, "date_completed" => $comp));
//            
//            if ($is_completed) {
//                $q_sql = "SELECT quantify_id, quantify_time_completed, quantify_status, quantify_metagenome_ids " .
//                    "FROM quantify WHERE quantify_identify_id = $id_id";
//                $q_rows = $this->db->query($q_sql);
//
//                foreach ($q_rows as $q_row) {
//                    $q_comp_result = $this->get_completed_date_label($q_row["quantify_time_completed"], $q_row["quantify_status"]);
//                    $q_comp = $q_comp_result[1];
//                    $q_is_completed = $q_comp_result[0];
//                    $q_id = $q_row["quantify_id"];
//
//                    $mg_ids = explode(",", $q_row["quantify_metagenome_ids"]);
//                    $q_full_job_name = implode(", ", $mg_ids);
//                    if (count($mg_ids) > 6) {
//                        $q_job_name = implode(", ", array_slice($mg_ids, 0, 5)) . " ...";
//                    } else {
//                        $q_job_name = $q_full_job_name;
//                    }
//
//                    array_push($jobs, array("id" => $id_id, "key" => $key, "quantify_id" => $q_id, "job_name" => $q_job_name,
//                        "is_completed" => $q_is_completed, "is_quantify" => true, "date_completed" => $q_comp,
//                        "full_job_name" => $q_full_job_name));
//                }
//            }
//        }
//
//        return $jobs;
//    }
//
//    public function get_quantify_jobs($identify_id) {
//        $jobs = array();
//
//        $q_sql = "SELECT quantify_id, quantify_time_completed, quantify_status, quantify_metagenome_ids " .
//            "FROM quantify WHERE quantify_identify_id = $identify_id";
//        $q_rows = $this->db->query($q_sql);
//
//        foreach ($q_rows as $q_row) {
//            $q_comp_result = $this->get_completed_date_label($q_row["quantify_time_completed"], $q_row["quantify_status"]);
//            $q_comp = $q_comp_result[1];
//            $q_is_completed = $q_comp_result[0];
//            $q_id = $q_row["quantify_id"];
//
//            //$q_job_name = implode(", ", explode(",", $q_row["quantify_metagenome_ids"]));
//            $q_job_name = $q_row["quantify_metagenome_ids"];
//
//            array_push($jobs, array("quantify_id" => $q_id, "job_name" => $q_job_name,
//                                        "is_completed" => $q_is_completed, "is_quantify" => true, "date_completed" => $q_comp));
//        }
//
//        return $jobs;
//    }

    // Candidate for refacotring to centralize
    private function get_completed_date_label($comp, $status) {
        $isCompleted = false;
        if ($status == "FAILED") {
            $comp = "FAILED";
        } elseif (!$comp || substr($comp, 0, 4) == "0000") {
            $comp = $status;
            if ($comp == "NEW")
                $comp = "PENDING";
        } else {
            $comp = self::format_short_date($comp);
            $isCompleted = true;
        }
        return array($isCompleted, $comp);
    }

    private static function format_short_date($comp) {
        return date_format(date_create($comp), "n/j h:i A");
    }
}


?>
