<?php
chdir(dirname(__FILE__));
require_once(__DIR__ . "/../includes/main.inc.php");
require_once(__BASE_DIR__ . "/libs/job_cancels.class.inc.php");


$sapi_type = php_sapi_name();

$is_debug = false;

if ($sapi_type != 'cli') {
    echo "Error: This script can only be run from the command line.\n";
}
else {
    $job_mgr = new job_cancels($db);

    $jobs = $job_mgr->get_cancelable_jobs();
    foreach ($jobs as $job_id) {
        sleep(1);
        print "Trying to cancel $job_id: ";
        $msg = $job_mgr->cancel_job($job_id);
        if ($msg)
            print "ERROR: $msg\n";
        else
            print "OK\n";
    }
}

?>

