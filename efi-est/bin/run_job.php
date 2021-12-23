<?php
chdir(dirname(__FILE__));

require_once(__DIR__."/../../init.php");

use \efi\est\job_cli;


$sapi_type = php_sapi_name();

if ($sapi_type != 'cli') {
    echo "Error: This script can only be run from the command line.\n";
}
elseif (count($argv) < 2) {
    echo "Error: An argument must be provided, specifying the job type to analyze.\n";
}
else {
    $job_type = $argv[1];
    $job_list = job_cli::get_jobs($db, $job_type, __NEW__);
    if (count($job_list)) {
        foreach ($job_list as $job) {
            sleep(5);            
            $result = $job->run_job();
            if ($result['RESULT']) {
                $msg = "Generate ID: " . $job->get_id() .  " - PBS Number: " . $result['PBS_NUMBER'] . " - " . $result['MESSAGE'];
            }
            else {
               $msg = "Generate ID: " . $job->get_id() .  " - Error: " . $result['MESSAGE'];
            }
        }
    }
    else {
        $msg = "No New PFAM/InterPro Generate Jobs";
    }
}

