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
	$running_jobs = functions::get_families($db,__RUNNING__);
	if (count($running_jobs)) {
		foreach ($running_jobs as $job) {

			$generate = new generate($db,$job['generate_id']);

			$blast_failed_exists = $generate->check_max_blast_failed_file();
			$finish_file_exists = $generate->check_finish_file();
			$job_running = $generate->check_pbs_running();
	
			if ((!$job_running) && ($finish_file_exists)) {
				$generate->set_status(__FINISH__);
	                        $generate->set_time_completed();
				$num_seq = $generate->get_num_sequence_from_file();
				$generate->set_num_sequences($num_seq);
				$generate->email_complete();
				$msg = "Generate ID: " . $job['generate_id'] . " - Job Completed Successfully";
				functions::log_message($msg);
			}
			elseif ((!$job_running) && ($blast_failed_exists)) {
				$generate->set_status(__FAILED__);
				$generate->set_num_blast();
				$generate->set_time_completed();
				$generate->email_number_seq();
				$msg = "Generate ID: " . $job['generate_id'] . " - Job Failed - Max Number of Sequences";
				functions::log_message($msg);
			}
			
	}
	}
	else {
		//functions::log_message("No Running PFAM/Interpro Generate Jobs");
	}
}






?>
