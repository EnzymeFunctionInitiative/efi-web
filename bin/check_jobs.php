<?php
chdir(dirname(__FILE__));
require_once(__DIR__."/../init.php");



const JOB_GENERATE = "generate";
const JOB_ANALYSIS = "analysis";
const JOB_GNN = "gnn";
const JOB_GND = "diagram";

$sapi_type = php_sapi_name();

if ($sapi_type != 'cli') {
    echo "Error: This script can only be run from the command line.\n";
}
elseif (count($argv) < 2) {
    echo "Error: An argument must be provided, specifying the job type to analyze.\n";
}
else {
    $run_type = $argv[1];

    $finished_jobs = get_job_info($db, $run_type);
    if ($finished_jobs === false) {
        print "No available jobs for $run_type\n";
        exit(0);
    }

    foreach ($finished_jobs as $job_id => $job_status) {
        process_job($db, $run_type, $job_id, $job_status);
    }
}






function get_job_info($db, $type) {

    $sql = "SELECT * FROM job_info AS J LEFT JOIN $type AS T ON J.job_info_id = T.${type}_id WHERE J.job_info_type = :type"
        . " AND T.${type}_status != '" . __FINISH__ . "'"
        . " AND T.${type}_status != '" . __FAILED__ . "'"
        . " AND T.${type}_status != '" . __CANCELLED__ . "'"
        . " AND T.${type}_status != '" . __ARCHIVED__ . "'"
        ;
    $results = $db->query($sql, array(":type" => $type));

    if (!$results) {
        return false;
    }

    $F = function($field) use ($type) { return "${type}_$field"; };
    $J = function($field) { return "job_info_$field"; };

    $info = array();
    for ($i = 0; $i < count($results); $i++) {
        $job_id = $results[$i][$J("id")];
        $j_status = $results[$i][$J("status")];
        $t_status = $results[$i][$F("status")];
        print "$job_id $j_status $t_status\n";
        if ($j_status == __RUNNING__ && $t_status == __NEW__) {
            $info[$job_id] = __RUNNING__;
        } else if ($j_status == __FAILED__) {
            $info[$job_id] = __FAILED__;
        } else if ($j_status == __FINISH__) {
            $info[$job_id] = __FINISH__;
        }
    }

    return $info;
}


function process_job($db, $type, $id, $status) {
    $job = get_job($db, $type, $id);
    if (!$job)
        die("Unable to process job $type $id; couldn't find it in the DB");

    if ($status == __RUNNING__) {
        $job->process_start();
    } else if ($status == __FAILED__) {
        $job->process_error();
    } else if ($status == __FINISH__) {
        $job->process_finish();
    }
}


function get_job($db, $type, $id) {
    if ($type == JOB_GENERATE) {
        return \efi\est\job_factory::create($db, $id);
    } else if ($type == JOB_ANALYSIS) {
        return new \efi\est\analysis($db, $id);
    } else if ($type == JOB_GNN) {
        return new \efi\gnt\gnn($db, $id);
    } else if ($type == JOB_GND) {
        return new \efi\gnt\diagram_job($db, $id);
    }
}



