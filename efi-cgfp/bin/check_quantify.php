<?php
chdir(dirname(__FILE__));
require_once(__DIR__."/../../init.php");

use \efi\cgfp\job_manager;
use \efi\cgfp\quantify;


$sapi_type = php_sapi_name();

$is_debug = true;

if ($sapi_type != 'cli') {
    echo "Error: This script can only be run from the command line.\n";
}
else {
    $jobManager = new job_manager($db, job_types::Quantify);
    $jobs = $jobManager->get_running_job_ids();
    if (count($jobs)) {
        foreach ($jobs as $jobId) {
            sleep(1);
            $msg = false;
            print "Checking $jobId for completion: ";
            $job = new quantify($db, $jobId, false, $is_debug);
            $job_result = $job->check_if_job_is_done(); // checks if the job is done and handles updating the database and sending email
            if ($job_result === 0) {
                print "failed.\n";
            } elseif ($job_result === 1) {
                print "still running\n";
            } else {
                print "completed\n";
            }
        }
    }
}

