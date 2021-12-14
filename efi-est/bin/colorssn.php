<?php
chdir(dirname(__FILE__));

require_once(__DIR__."/../../init.php");

use \efi\est\functions;
use \efi\est\job_factory;


$sapi_type = php_sapi_name();
//If run from command line
if ($sapi_type != 'cli') {
    echo "Error: This script can only be run from the command line.\n";
}
else {
    //functions::log_message("Running " . basename($argv[0]));
    $new_generate_data = functions::get_colorssns($db,__NEW__);
    if (count($new_generate_data)) {
        foreach ($new_generate_data as $data) {
            sleep(5);			
            $obj = job_factory::create($db, $data['generate_id']);
            $result = $obj->run_job(functions::get_is_debug());
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
        $msg = "No New Colored SSN Jobs";
        //functions::log_message($msg);
    }

}


