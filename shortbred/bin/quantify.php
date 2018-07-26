<?php
chdir(dirname(__FILE__));
require_once("../includes/main.inc.php");
require_once("../libs/job_manager.class.inc.php");
require_once("../libs/quantify.class.inc.php");

$sapi_type = php_sapi_name();
$is_debug = false;

if ($sapi_type != 'cli') {
    echo "Error: This script can only be run from the command line.\n";
}
else {
    $jobManager = new job_manager($db, job_types::Quantify);
    $queue_limit = true;
    $jobs = $jobManager->get_new_job_ids($queue_limit);
    if (count($jobs)) {
        foreach ($jobs as $jobId) {
            sleep(1);
            $msg = false;

            print "\nProcessing $jobId\n";

            $job = new quantify($db, $jobId, $is_debug);
            $msg = $job->run_job();
            
            if (!$msg) {
                print "Error processing job $jobId:\n";
                print $job->get_message();
            }
        }
    }
}

?>

