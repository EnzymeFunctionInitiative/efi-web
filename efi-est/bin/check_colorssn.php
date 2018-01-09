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
    $running_jobs = functions::get_colorssns($db,__RUNNING__);
    if (count($running_jobs)) {
        foreach ($running_jobs as $job) {

            $colorssn = new colorssn($db,$job['generate_id']);

            $finish_file_exists = $colorssn->check_finish_file();
            $job_running = $colorssn->check_pbs_running();

            if ((!$job_running) && ($finish_file_exists)) {
                $colorssn->set_status(__FINISH__);
                $colorssn->set_time_completed();
                $num_seq = $colorssn->get_num_sequence_from_file();
                $colorssn->set_num_sequences($num_seq);
                $colorssn->email_complete();
                $msg = "Generate ID: " . $job['generate_id'] . " - Job Completed Successfully";
                functions::log_message($msg);
            }
        }
    }
    else {
        //functions::log_message("No Running PFAM/Interpro Generate Jobs");
    }
}






?>
