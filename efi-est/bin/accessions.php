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
	$new_generate_data = functions::get_accessions($db,__NEW__);
	if (count($new_generate_data)) {
        foreach ($new_generate_data as $data) {
			sleep(5);			
			$accession_obj = new accession($db, $data['generate_id']);
			$result = $accession_obj->run_job(functions::get_is_debug());
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
		$msg = "No New PFAM/InterPro Generate Jobs";
		//functions::log_message($msg);
	}

}






?>
