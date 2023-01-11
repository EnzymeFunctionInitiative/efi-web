<?php
namespace efi\gnt;

require_once(__DIR__."/../../../init.php");

use \efi\global_settings;
use \efi\gnt\settings;


abstract class job_shared {

    protected $db;
    protected $id;
    protected $time_created;
    protected $time_started;
    protected $time_completed;
    protected $status;
    protected $pbs_number;
    private $table;

    public function get_id() { return $this->id; }
    public function get_time_created() { return $this->time_created; }
    public function get_time_started() { return $this->time_started; }
    public function get_time_completed() { return $this->time_completed; }
    public function get_pbs_number() { return $this->pbs_number; }
    public function get_unixtime_completed() { return strtotime($this->time_completed); }
    public function get_table() { return $this->table; }

    public function __construct($db, $id, $table) {
        $this->db = $db;
        $this->table = $table;
        $this->id = $id;
    }

    protected function set_status($status) {
        $table = $this->table;
        $sql = "UPDATE ${table} ";
        $sql .= "SET ${table}_status='" . $status . "' ";
        $sql .= "WHERE ${table}_id='" . $this->get_id() . "' LIMIT 1";
        $result = $this->db->non_select_query($sql);
        if ($result) {
            $this->status = $status;
        }
    }
    
    public function set_time_completed($current_time = false) {
        if ($current_time === false)
            $current_time = date("Y-m-d H:i:s", time());
        $result = $this->set_time($current_time, "time_completed");
        if ($result)
            $this->time_completed = $current_time;
    }
    public function set_time_started() {
        $the_time = date("Y-m-d H:i:s", time());
        $this->time_started = $this->set_time($the_time, "time_started");
    }
    private function set_time($the_time, $field) { 
        $table = $this->table;
        $sql = "UPDATE ${table} SET ${table}_${field} = :the_time WHERE ${table}_id = :id";
        $result = $this->db->non_select_query($sql, array(":the_time" => $the_time, ":id" => $this->get_id()));
        return $result;
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

    public abstract function process_error();
    public abstract function process_start();
    public abstract function process_finish();
}


