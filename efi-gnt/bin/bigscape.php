<?php
chdir(dirname(__FILE__));

require_once(__DIR__."/../../init.php");

use \efi\gnt\bigscape_job;


$sapi_type = php_sapi_name();

if ($sapi_type != 'cli') {
    echo "Error: This script can only be run from the command line.\n";
}
else {
    $jobs = bigscape_job::get_jobs($db, __NEW__);
    if (count($jobs)) {
        foreach ($jobs as $jobId) {
            sleep(1);
            $msg = false;
            print "Processing $jobId\n";
            $job = bigscape_job::from_job_id($db, $jobId);
            $msg = $job->run($db);
            if (!$msg) {
                print "Error processing job $jobId:\n";
                print $job->get_message();
            }
        }
    }
}

