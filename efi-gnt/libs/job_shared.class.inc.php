<?php
require_once "../includes/main.inc.php";

abstract class job_shared {

    protected $db;
    protected $id;
    protected $time_created;
    protected $time_started;
    protected $time_completed;
    protected $status;
    protected $pbs_number;

    public function get_id() { return $this->id; }
    public function get_time_created() { return $this->time_created; }
    public function get_time_started() { return $this->time_started; }
    public function get_time_completed() { return $this->time_completed; }
    public function get_pbs_number() { return $this->pbs_number; }
    public function get_unixtime_completed() { return strtotime($this->time_completed); }

    public function __construct($db, $id, $db_field_prefix) {
        $this->db = $db;
        $this->db_field_prefix = $db_field_prefix;
        $this->id = $id;
    }

    protected function set_status($status) {
        $table = $this->db_field_prefix;
        $sql = "UPDATE ${table} ";
        $sql .= "SET ${table}_status='" . $status . "' ";
        $sql .= "WHERE ${table}_id='" . $this->get_id() . "' LIMIT 1";
        $result = $this->db->non_select_query($sql);
        if ($result) {
            $this->status = $status;
        }
    }
    
    public function set_time_completed($current_time = false) {
        $table = $this->db_field_prefix;
        if ($current_time === false)
            $current_time = date("Y-m-d H:i:s",time());
        $sql = "UPDATE ${table} SET ${table}_time_completed='" . $current_time . "' ";
        $sql .= "WHERE ${table}_id='" . $this->get_id() . "' LIMIT 1";
        $result = $this->db->non_select_query($sql);
        if ($result) {
            $this->time_completed = $current_time;
        }
    }

    public function mark_job_as_archived() {
        // This marks the job as archived-failed. If the job is archived but the
        // time completed is non-zero, then the job successfully completed.
        if ($this->status == __FAILED__)
            $this->set_time_completed("0000-00-00 00:00:00");
        $this->set_status(__ARCHIVED__);
    }

    public function check_pbs_running() {
        $sched = strtolower(settings::get_cluster_scheduler());
        $jobNum = $this->get_pbs_number();
        $output = "";
        $exit_status = "";
        $exec = "";
        if ($sched == "slurm")
            $exec = "squeue --job $jobNum 2> /dev/null | grep $jobNum";
        else
            $exec = "qstat $jobNum 2> /dev/null | grep $jobNum";
        exec($exec,$output,$exit_status);
        if (count($output) == 1) {
            return true;
        }
        else {
            return false;
        }
    }
    
    public function is_expired() {
        if (time() > $this->get_unixtime_completed() + global_settings::get_retention_secs()) {
            return true;
        } else {
            return false;
        }
    }
}

?>
