<?php
namespace efi\gnt;

require_once(__DIR__."/../../../init.php");

use \efi\global_settings;
use \efi\gnt\settings;


abstract class job_shared {

    protected $db;
    protected $id;
    protected $status;
    protected $pbs_number;
    private $table;

    public function get_id() { return $this->id; }
    public function get_pbs_number() { return $this->pbs_number; }
    public function get_table_name() { return $this->table; }

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
    
    protected abstract function get_job_status_obj();

    protected function set_job_failed() {
        $this->get_job_status_obj()->failed();
    }
    protected function set_job_completed() {
        $this->get_job_status_obj()->complete();
    }
    protected function set_job_started() {
        $this->get_job_status_obj()->start();
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
        if ($this->get_job_status_obj()->is_expired()) {
            return true;
        } else {
            return false;
        }
    }

    public abstract function process_error();
    public abstract function process_start();
    public abstract function process_finish();
}


