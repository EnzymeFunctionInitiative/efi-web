<?php
namespace efi;
require_once(__DIR__ . "/../../init.php");


class job_status {

    const CURRENT_TIME = 1;
    const ZERO_TIME = 2;

    protected $db;
    protected $id;
    protected $table;
    private $status;

    /**
     * @var accessible and written to by child classes. Contains the job status
     * column name prefix (e.g. "generate").
     */
    protected $col_pfx;
    private function get_col_pfx() { return $this->col_pfx ? ($this->col_pfx . "_") : ""; }
    
    /**
     * Accesses and writes job status information from database.
     *
     * @param object $db database object
     * @param int $id job identifier
     *
     * @return \efi\job_status
     */
    public function __construct($db, $id, $table, $db_col_prefix = "") {
        //if (global_functions::validate_id($id)) {
        //    throw new Exception("Invalid identifier");
        //}
        $this->id = $id;
        $this->table = $table;
        $this->db = $db;
        $this->col_pfx = $db_col_prefix;
    }

    public function load_validate($key) {
        $col = $this->get_col_pfx();
        $table = $this->table;
        $sql = "SELECT ${col}status, ${col}time_created, ${col}time_started, ${col}time_completed, ${col}key, ${col}pbs_number, ${col}params FROM $table WHERE ${col}id = :id";
        $params = array(":id" => $this->id);
        $result = $this->db->query($sql, $params);
        if ($result) {
            $this->load_result_set($result[0]);
            return $key === $this->key;
        } else {
            return false;
        }
    }

    public function cancel() {
        $this->do_cancel();
        $this->mark_job_as_cancelled();
    
    }
    protected function do_cancel() {
        if ($this->get_status() == __RUNNING__ && $this->pbs_number)
            job_cancels::request_job_cancellation($this->db, $this->pbs_number, "est");
    }



    public function archive() {
        $this->mark_job_as_archived();
    }

    private function mark_job_as_cancelled() {
        $this->set_status(__CANCELLED__);
        $this->set_time_completed();
    }
    private function mark_job_as_archived() {
        if ($this->status == __FAILED__)
            $this->set_time_completed("0000-00-00 00:00:00");
        $this->set_status(__ARCHIVED__);
    }



    /**
     * Load status information from a database result set row.
     *
     * @param string $db_result row from the database query
     *
     * @return true if success, false if failure
     */
    private function load_result_set($db_result) {
        $col = $this->get_col_pfx();
        $this->key = $db_result[$col . "key"];
        $this->status = $db_result[$col . "status"];
        $this->time_created = $db_result[$col . "time_created"];
        $this->time_started = $db_result[$col . "time_started"];
        $this->time_completed = $db_result[$col . "time_completed"];
        $this->pbs_number = $db_result[$col . "pbs_number"];
    }

    /**
     * Create job status information in the form of a key value array.
     * In a different class these are added to a monolithic array and
     * stored in the database.
     *
     * @param array $params optional parameters specific to the module.
     *
     * @return array with job parameters.
     */
    public function create($opt_params) {
        $col = $this->get_col_pfx();
        $params = array($col . "status", __NEW__);
        //$params[$col . "_time_started"] = $db_result[$this->col_pfx . "_time_started"];
        //$params[$col . "_time_completed"] = $db_result[$this->col_pfx . "_time_completed"];
        return $params;
    }



    protected function get_status() {
        return $this->status;
    }
    protected function set_status($status) {
        $col = $this->get_col_pfx();
        $sql = "UPDATE " . $this->table . " SET ${col}status = :status WHERE ${col}id = :id";
        $params = array(":status" => $status, ":id" => $this->id);
        $this->db->non_select_query($sql, $params);
        $this->status = $status;
    }
    protected function is_running() {
        return $this->status === __RUNNING__;
    }



    protected static function get_current_datetime() {
        $current_time = date("Y-m-d H:i:s",time());
        return $current_time;
    }
    protected function get_time_completed() {
        return $this->time_completed;
    }
    protected function set_time_completed($time = self::CURRENT_TIME) {
        $table = $this->table;
        if ($time === self::CURRENT_TIME)
            $time = self::get_current_datetime();
        else if ($time === self::ZERO_TIME)
            $time = "0000-00-00 00:00:00";
        $col = $this->get_col_pfx();
        $sql = "UPDATE $table SET ${col}time_completed = :time WHERE ${col}id = :id";
        $params = array(":time" => $time, ":id" => $this->id);
        $sql .= "WHERE ${table}_id='" . $this->get_id() . "' LIMIT 1";
        $this->db->non_select_query($sql);
        $this->time_completed = $time;
    }



    protected function get_id() {
        return $this->id;
    }
}


