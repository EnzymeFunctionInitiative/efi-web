<?php
namespace efi;
require_once(__DIR__ . "/../../init.php");


class job_status {
_
    /**
     * @var accessible and written to by child classes. Contains the job status
     * column name prefix (e.g. "generate").
     */
    protected $col_pfx;
    
    /**
     * Accesses and writes job status information from database.
     *
     * @param object $db database object
     * @param int $id job identifier
     *
     * @return \efi\job_status
     */
    public function __construct($db, $id, $db_col_prefix) {
        $this->id = $id;
        $this->db = $db;
        $this->col_pfx = $db_col_prefix;
    }

    /**
     * Load status information from a database result set row.
     *
     * @param string $db_result row from the database query
     *
     * @return true if success, false if failure
     */
    public function load($db_result) {
        $this->status = $db_result[$this->col_pfx . "_status"];
        $this->time_created = $db_result[$this->col_pfx . "_time_created"];
        $this->time_started = $db_result[$this->col_pfx . "_time_started"];
        $this->time_completed = $db_result[$this->col_pfx . "_time_completed"];
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
        $params = array($this->col_pfx . "_status", __NEW__);
        //$params[$this->col_pfx . "_time_started"] = $db_result[$this->col_pfx . "_time_started"];
        //$params[$this->col_pfx . "_time_completed"] = $db_result[$this->col_pfx . "_time_completed"];
        return $params;
    }
}


