<?php
chdir(dirname(__FILE__));

require_once(__DIR__."/../../init.php");

use \efi\gnt\bigscape_job;


$sapi_type = php_sapi_name();

if ($sapi_type != 'cli') {
    echo "Error: This script can only be run from the command line.\n";
}
else {
    $jobs = bigscape_job::get_jobs($db, __RUNNING__);
    if (count($jobs)) {
        foreach ($jobs as $jobId) {
            sleep(1);
            $msg = false;
            print "Checking $jobId for bigscape completion\n";
            $job = bigscape_job::from_job_id($db, $jobId);
            $job->check_if_job_is_done();
        }
    }
}

