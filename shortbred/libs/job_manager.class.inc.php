<?php

require_once "job_types.class.inc.php";

class job_manager {

    private $table_name = "";
    private $db = "";
    private $jobs_by_status = array();
    private $jobs_by_id = array();

    function __construct($db, $table_name) {
        $this->table_name = $table_name;
        $this->db = $db;
        $this->get_jobs();
    }

    public function validate_job($id, $key) {
        return isset($this->jobs_by_id[$id]) && $this->jobs_by_id[$id]["key"] == $key;
    }

    private function get_jobs() {
        $table = $this->table_name;
        $col_email = "${table}_email";
        $col_key = "${table}_key";
        $col_id = "${table}_id";
        $col_status = "${table}_status";

        $sql = "SELECT $col_id, $col_key, $col_email, $col_status FROM $table";
        $rows = $this->db->query($sql);

        if (!$rows) {
            return false;
        }

        foreach ($rows as $result) {
            $status = $result[$col_status];
            $email = $result[$col_email];
            $key = $result[$col_key];
            $id = $result[$col_id];

            if (!isset($this->jobs_by_status[$status])) {
                $this->jobs_by_status[$status] = array();
            }

            array_push($this->jobs_by_status[$status], array("email" => $email, "key" => $key, "id" => $id));
            $this->jobs_by_id[$id] = array("email" => $email, "key" => $key);
        }  
        
        return true;
    }

    public function get_new_job_ids() {
        if (!isset($this->jobs_by_status[__NEW__])) {
            return array();
        }

        $ids = array();
        foreach ($this->jobs_by_status[__NEW__] as $job) {
            array_push($ids, $job["id"]);
        }

        return $ids;
    }

    public function get_running_job_ids() {
        if (!isset($this->jobs_by_status[__RUNNING__])) {
            return array();
        }

        $ids = array();
        foreach ($this->jobs_by_status[__RUNNING__] as $job) {
            array_push($ids, $job["id"]);
        }

        return $ids;
    }

    public function get_job_key($job_id) {
        if (!isset($this->jobs_by_id[$job_id])) {
            return "";
        } else {
            return $this->jobs_by_id[$job_id]["key"];
        }
    }
}


?>
