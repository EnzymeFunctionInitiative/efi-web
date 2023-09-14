<?php
namespace efi;
require_once(__DIR__ . "/../../init.php");

//define("DEBUG",true);

class job_status {

    const CURRENT_TIME = 1;
    const ZERO_TIME = 2;

    private $db;
    private $id;
    private $table;
    private $status;
    private $time_created;
    private $time_started;
    private $time_completed;
    private $pbs_number;
    private $col_pfx;
    private $info_type;


    /**
     * Accesses and writes job status information from database.
     *
     * @param object $db database object
     * @param int $id job identifier
     *
     * @return \efi\job_status
     */
    public function __construct($db, $job) {
        $this->db = $db;
        $this->id = $job->get_id();
        if (!isset($this->id))
            $this->id = 0; // new job
        $this->table = $job->get_table_name();
        $this->info_type = self::find_info_type($this->table);
        $this->col_pfx = $this->table . "_";
        $this->load_status();
    }
    private static function find_info_type($table) {
        if ($table == "gnd")
            return "diagram";
        else
            return $table;
    }
    //public function __construct($db, $id, $table, $db_col_prefix = "") {
    //    //if (global_functions::validate_id($id)) {
    //    //    throw new Exception("Invalid identifier");
    //    //}
    //    $this->id = $id;
    //    $this->table = $table;
    //    $this->db = $db;
    //    $this->col_pfx = $db_col_prefix;
    //}



    public function cancel() {
        $this->update_child_cancels();
        $this->set_status(__CANCELLED__);
        $this->set_time_completed(self::CURRENT_TIME);
    }



    public function complete() {
        $this->set_status(__FINISH__);
        $this->set_time_completed();
    }



    public function start() {
        $this->set_status(__RUNNING__);
        $this->set_time_started();
    }



    public function archive() {
        $this->update_child_cancels();
        if ($this->status == __FAILED__)
            $this->set_time_completed("0000-00-00 00:00:00");
        $this->set_status(__ARCHIVED__);
    }



    public function failed() {
        $this->set_time_completed("0000-00-00 00:00:00");
        $this->set_status(__FAILED__);
    }



    public function get_time_created() {
        return $this->time_created;
    }
    public function get_time_completed() {
        return $this->time_completed;
    }
    public function get_time_started() {
        return $this->time_started;
    }
    public function get_time_completed_formatted() {
        return \efi\est\functions::format_datetime(\efi\est\functions::parse_datetime($this->time_completed));
    }
    public function get_time_period() {
        $window = global_functions::format_short_date($this->get_time_started()) . " -- " . global_functions::format_short_date($this->get_time_completed());
        return $window;
    }
    public function get_unixtime_completed() {
        return strtotime($this->time_completed);
    }
    public static function get_current_datetime() {
        $current_time = date("Y-m-d H:i:s",time());
        return $current_time;
    }



    /** job_info table updates *******************************************************************/

    public static function insert_new_manual($db, $job_id, $job_type) {
        $info = array("job_info_type" => $job_type, "job_info_id" => $job_id, "job_info_status" => __NEW__);
        if (!defined("DEBUG"))
            $db->build_insert("job_info", $info);
    }



    public function insert_new($job_id) {
        $info = array("job_info_type" => $this->info_type, "job_info_id" => $job_id, "job_info_status" => __NEW__);
        if (!defined("DEBUG"))
            $this->db->build_insert("job_info", $info);
    }



    public function is_running() {
        return $this->status == __RUNNING__;
    }
    public function is_finished() {
        return $this->status == __FINISHED__;
    }
    public function is_archived() {
        return $this->status == __ARCHIVED__;
    }
    public function is_cancelled() {
        return $this->status == __CANCELLED__;
    }
    public function is_failed() {
        return $this->status == __FAILED__;
    }
    public function is_new() {
        return $this->status == __NEW__;
    }



    public function is_expired() {
        if (time() > $this->get_unixtime_completed() + \efi\global_settings::get_retention_secs()) {
            return true;
        } else {
            return false;
        }
    }






    /** PRIVATE ***********************************************************************************/

    /**
     * @var accessible and written to by child classes. Contains the job status
     * column name prefix (e.g. "generate").
     */
    private function get_col_pfx() {
        return $this->col_pfx;
    }


    private function update_child_cancels() {
        if ($this->info_type == "generate") {
            $rows = \efi\est\functions::get_analysis_jobs_for_generate($this->db, $this->id);
            foreach ($rows as $row) {
                $aid = $row["analysis_id"];
                $job = new job_status($this->db, new \efi\est\analysis($this->db, $aid));
                if ($job->is_running())
                    $job->cancel();
                else
                    $job->archive();
            }
        } else if ($this->info_type == "analysis") {
            $cjob_ids = \efi\est\functions::get_color_jobs_for_analysis($this->db, $this->id);
            foreach ($cjob_ids as $crow) {
                $c_status = new job_status($this->db, new \efi\est\stepa($this->db, $crow["generate_id"]));
                if ($c_status->is_running())
                    $c_status->cancel();
                else
                    $c_status->archive();

                $crjob_ids = \efi\est\functions::get_color_jobs_for_color_job($this->db, $crow["generate_id"]);
                foreach ($crjob_ids as $crrow) {
                    $cr_status = new job_status($this->db, new \efi\est\stepa($this->db, $crrow["generate_id"]));
                    if ($cr_status->is_running())
                        $cr_status->cancel();
                    else
                        $cr_status->archive();
                }
            }
        }
    }


    /**
     * Load status information from a database result set row.
     *
     * @param string $db_result row from the database query
     *
     * @return true if success, false if failure
     */
    private function load_status() {
        // New ID
        if (!isset($this->id) || $this->id === 0)
            return;
        $col = $this->get_col_pfx();
        $table = $this->table;
        $sql = "SELECT * FROM $table WHERE ${col}id = :id";
        $db_result = $this->db->query($sql, array(":id" => $this->id));
        if (!$db_result || !isset($db_result[0]))
            throw new \RuntimeException("No ID found $this->id");
        $db_result = $db_result[0];
        $this->status = $db_result[$col . "status"];
        $this->time_created = $db_result[$col . "time_created"];
        $this->time_started = $db_result[$col . "time_started"];
        $this->time_completed = $db_result[$col . "time_completed"];
    }


    private function set_status($status) {
        $col = $this->get_col_pfx();
        $sql = "UPDATE " . $this->table . " SET ${col}status = :status WHERE ${col}id = :id";
        $params = array(":status" => $status, ":id" => $this->id);
        if (!defined("DEBUG"))
            $this->db->non_select_query($sql, $params);
        $this->status = $status;

        $this->set_job_info_status($status);
    }
    private function set_job_info_status($status) {
        $sql = "UPDATE job_info SET job_info_status = :status WHERE job_info_id = :id AND job_info_type = :type";
        $params = array(":status" => $status, ":id" => $this->id, ":type" => $this->info_type);
        if (!defined("DEBUG"))
            $this->db->non_select_query($sql, $params);
    }



    private function set_time_completed($time = self::CURRENT_TIME) {
        $this->set_time_field("time_completed", $time);
        $this->time_completed = $time;
    }
    private function set_time_started($time = self::CURRENT_TIME) {
        $this->set_time_field("time_started", $time);
        $this->time_started = $time;
    }
    private function set_time_created($time = self::CURRENT_TIME) {
        $this->set_time_field("time_created", $time);
        $this->time_created = $time;
    }
    private function set_time_field($time_col, $time) {
        if ($time === self::CURRENT_TIME)
            $time = self::get_current_datetime();
        else if ($time === self::ZERO_TIME)
            $time = "0000-00-00 00:00:00";
        $table = $this->table;
        $col = $this->get_col_pfx();
        $sql = "UPDATE $table SET ${col}${time_col} = :the_tm WHERE ${col}id = :id";
        $params = array(":the_tm" => $time, ":id" => $this->id);
        if (!defined("DEBUG"))
            $this->db->non_select_query($sql, $params);
    }


}


