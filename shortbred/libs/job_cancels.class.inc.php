<?php

require_once(__DIR__ . "/../../libs/user_auth.class.inc.php");
require_once(__DIR__ . "/../../libs/global_functions.class.inc.php");


class job_cancels {

    const CANCEL_NEW = "NEW";
    const CANCEL_FINISH = "FINISH";

    private $db = "";
    private $jobs = array();

    function __construct($db) {
        $this->db = $db;
        $this->get_jobs();
    }

    public static function request_job_cancellation($db, $job_process_num) {
        $sql = "INSERT INTO job_cancel (job_process_num, cancel_status) VALUES ('$job_process_num', '" . self::CANCEL_NEW . "')";
        $db->non_select_query($sql);
    }

    private function get_jobs() {
        $sql = "SELECT * FROM job_cancel WHERE cancel_status = '" . self::CANCEL_NEW . "'";
        $rows = $this->db->query($sql);

        foreach ($rows as $row) {
            array_push($this->jobs, $row['job_process_num']);
        }
    }

    public function get_cancelable_jobs() {
        return $this->jobs;
    }

    public function cancel_job($job_process_num) {

        $cancel_process_nums = array($job_process_num);

        $sched = settings::get_cluster_scheduler();

        $nums = self::get_job_deps($job_process_num, $sched);
        $cancel_process_nums = array_merge($cancel_process_nums, $nums);

        $exec = "";
        $output = "";
        $exit_status = "";
        if ($sched == "slurm") {
            $cancel_list = implode(" ", $cancel_process_nums);
            $exec = "scancel $cancel_list";
        }

        exec($exec, $output, $exit_status);

        if ($exit_status)
            return implode(",", $output) . " Exit status: $exit_status";

        $sql = "UPDATE job_cancel SET cancel_status = '" . self::CANCEL_FINISH . "' WHERE job_process_num = " . $job_process_num;
        $this->db->non_select_query($sql);

        return "";
    }

    public static function get_job_deps($process_num, $sched) {
        $output = "";
        $exit_status = "";
        $exec = "";

        if ($sched == "slurm") {
            $exec = "squeue --format '%i %j %E' | grep '^$process_num' | cut -d' ' -f3";
        } else {
            $exec = ""; #TODO:
        }

        exec($exec, $output, $exit_status);

        if (count($output) < 1)
            return array();

        $new_nums = array();

        $job_deps = explode(",", $output[0]);
        foreach ($job_deps as $dep) {
            $dep_num = preg_replace("/\D/", "", $dep);
            if (is_numeric($dep_num))
                array_push($new_nums, $dep_num);
        }

        foreach ($new_nums as $num) {
            $nested_nums = self::get_job_deps($num, $sched);
            $new_nums = array_merge($new_nums, $nested_nums);
        }

        return $new_nums;
    }
}


?>
