<?php
chdir(dirname(__FILE__));

require_once '../includes/main.inc.php';

$sapi_type = php_sapi_name();
//If run from command line
if ($sapi_type != 'cli') {
        echo "Error: This script can only be run from the command line.\n";
} else {
    $new_analysis = functions::get_analysis($db,__NEW__);
    if (count($new_analysis)) {
        foreach ($new_analysis as $analysis) {
            sleep(5);
            $analysis_obj = new analysis($db,$analysis['analysis_id']);
            $result = $analysis_obj->run_job();
            if ($result['RESULT']) {
                $msg = "Analysis ID: " . $analysis['analysis_id'] .  " - PBS Number: " . $result['PBS_NUMBER'] . " - " . $result['MESSAGE'];
            } else {
                $msg = "Analysis ID: " . $analysis['analysis_id'] .  " - Error: " . $result['MESSAGE'];
            }
            functions::log_message($msg);
        }
    } else {
        $msg = "No New Analaysis";
    }
}

?>
