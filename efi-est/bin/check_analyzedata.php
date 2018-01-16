<?php
chdir(dirname(__FILE__));
require_once '../includes/main.inc.php';

$sapi_type = php_sapi_name();
//If run from command line
if ($sapi_type != 'cli') {
    echo "Error: This script can only be run from the command line.\n";
}
else {
    $running_jobs = functions::get_analysis($db,__RUNNING__);
    if (count($running_jobs)) {
        foreach ($running_jobs as $job) {
            $jobObj = new analysis($db,$job['analysis_id']);
            $pbs_running = $jobObj->check_pbs_running();
            if ((!$pbs_running) && ($jobObj->check_finish_file())) {
                $jobObj->set_status(__FINISH__);
                $jobObj->set_time_completed();
                $jobObj->email_complete();
                $msg = "Analaysis ID: " . $job['analysis_id'] . " - Job Completed Successfully";
                functions::log_message($msg);	
            }
            elseif ((!$pbs_running) && (!$jobObj->check_finish_file())) {
                $jobObj->set_status(__FAILED__);
                $jobObj->set_time_completed();
                $jobObj->email_failed();
                $msg = "Analaysis ID: " . $job['analysis_id'] . " - Job Failed";
                functions::log_message($msg);
            }
        }
    }
}

?>
