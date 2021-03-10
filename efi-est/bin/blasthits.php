<?php
chdir(dirname(__FILE__));
require_once(__DIR__."/../../conf/settings_paths.inc.php");
require_once(__EST_DIR__."/includes/main.inc.php");
require_once(__EST_DIR__."/libs/job_factory.class.inc.php");

$sapi_type = php_sapi_name();
//If run from command line
if ($sapi_type != 'cli') {
        echo "Error: This script can only be run from the command line.\n";
}
else {
	//functions::log_message("Running " . basename($argv[0]));
	$new_blasts = functions::get_blasts($db,__NEW__);
	if (count($new_blasts)) {
		foreach ($new_blasts as $data) {
			sleep(5);
			$blast_obj = new blast($db,$data['generate_id']);
			$result = $blast_obj->run_job();
		
			if ($result['RESULT']) {
				$msg = "Generate ID: " . $data['generate_id'] .  " - PBS Number: " . $result['PBS_NUMBER'] . " - " . $result['MESSAGE'];
			}
			else {
				$msg = "Generate ID: " . $data['generate_id'] .  " - Error: " . $result['MESSAGE'];
			}
			functions::log_message($msg);
		}
	}
	else {
		$msg = "No New BLAST Jobs";
		//functions::log_message($msg);	
	}
	
}






?>
