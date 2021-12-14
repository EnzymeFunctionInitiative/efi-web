<?php
chdir(dirname(__FILE__));

require_once(__DIR__."/../../init.php");

use \efi\est\functions;
use \efi\est\analysis;


$sapi_type = php_sapi_name();
//If run from command line
if ($sapi_type != 'cli') {
    echo "Error: This script can only be run from the command line.\n";
}
else {
    $running_jobs = functions::get_analysis($db,__RUNNING__);
    if (count($running_jobs)) {
        foreach ($running_jobs as $job) {
            $job_obj = new analysis($db,$job['analysis_id']);
            $pbs_running = $job_obj->check_pbs_running();
            if ((!$pbs_running) && ($job_obj->check_finish_file())) {
                $job_obj->finish_job_complete();
                $job_obj->email_complete();
                $msg = "Analaysis ID: " . $job['analysis_id'] . " - Job Completed Successfully";
                functions::log_message($msg);	
            }
            elseif ((!$pbs_running) && (!$job_obj->check_finish_file())) {
                $job_obj->finish_job_failed();
                $job_obj->email_failed();
                $msg = "Analaysis ID: " . $job['analysis_id'] . " - Job Failed";
                functions::log_message($msg);
            }
        }
    }
}


