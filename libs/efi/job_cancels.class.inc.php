<?php
namespace efi;

require_once(__DIR__."/../../init.php");

use \efi\global_settings;


class job_cancels {

    const CANCEL_NEW = "NEW";
    const CANCEL_FINISH = "FINISH";

    private $db = "";
    private $jobs = array();

    function __construct($db) {
        $this->db = $db;
    }

    public function cancel_job($job_process_num) {

        $cancel_process_nums = array($job_process_num);

        $sched = global_settings::get_cluster_scheduler();

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

        return "";
    }

    private static function get_job_deps($process_num, $sched) {
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



