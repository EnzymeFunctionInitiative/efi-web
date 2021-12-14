<?php
namespace efi\est;
require_once(__DIR__ . "/../../../init.php");


class job_status extends \efi\job_status {

    /**
     * Accesses and writes job status information from database.
     *
     * @param object $db database object
     *
     * @return \efi\job_status
     */
    public function __construct($db, $id) {
        parent::__construct($db, $id, "generate");
        $this->id = $id;
        $this->db = $db;
    }

    /**
     * Load status information from a database result set row.
     *
     * @param array $db_result row from the database query
     *
     * @return true if success, false if failure
     */
    public function load($db_result) {
        parent::load($db_result);
        //TODO: load the parameters from the database.
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
    public function create($opt_params = array()) {
        $params = parent::create($opt_params);
        return $params;
    }
}


