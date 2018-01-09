<?php
chdir(dirname(__FILE__));
require_once '../includes/main.inc.php';

$sapi_type = php_sapi_name();
//If run from command line
if ($sapi_type != 'cli') {
        echo "Error: This script can only be run from the command line.\n";
}
else {
	//functions::log_message("Running " . basename($argv[0]));
	$running_jobs = functions::get_blasts($db,__RUNNING__);
	if (count($running_jobs)) {
	        foreach ($running_jobs as $job) {

        	        $blast = new blast($db,$job['generate_id']);
			$finish_file_exists = $blast->check_finish_file();
			$fail_file_exists = $blast->check_fail_file();
			$job_running = $blast->check_pbs_running();
			if ((!$job_running) && ($finish_file_exists)) {
                	        $blast->set_status(__FINISH__);
				$blast->set_time_completed();
        	                $num_seq = $blast->get_num_sequence_from_file();
				$blast->set_num_sequences($num_seq);
                        	$blast->email_complete();
				$msg = "Generate ID: " . $job['generate_id'] . " - Job Completed Successfully";
				functions::log_message($msg);
                	}
			elseif ((!$job_running) && ($fail_file_exists)) {
				$blast->set_status(__FAILED__);
				$blast->set_time_completed();
				$msg = "Generate ID: " . $job['generate_id'] . " - Job Failed";
                                functions::log_message($msg);

	
			}

	        }
	}
	else {
		//functions::log_message('No Running Blast Jobs');	
	}

}






?>
