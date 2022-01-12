<?php
chdir(dirname(__FILE__));

require_once(__DIR__."/../../init.php");

use \efi\est\functions;
use \efi\est\job_cli;
use \efi\est\colorssn;


$sapi_type = php_sapi_name();

if ($sapi_type != 'cli') {
    echo "Error: This script can only be run from the command line.\n";
}
elseif (count($argv) < 2) {
    echo "Error: An argument must be provided, specifying the job type to analyze.\n";
}
else {
    $run_type = $argv[1];
    $running_jobs = job_cli::get_jobs($db, $run_type, __RUNNING__);

    $is_color_job = $run_type == colorssn::create_type();

    foreach ($running_jobs as $job_obj) {
        $nonblastjob_blast_failed_file_exists = job_cli::get_non_blast_failed_file_exists($run_type, $job_obj);
        $blastjob_fail_file_exists = job_cli::get_blast_failed_file_exists($run_type, $job_obj);
        $finish_file_exists = $job_obj->check_finish_file();
        $nonblastjob_bad_input_format_file_exists = job_cli::get_non_blast_bad_input_file_format_file_exists($run_type, $job_obj);
        $job_running = $job_obj->check_pbs_running();

        if (!$job_running && $finish_file_exists) {
            $job_obj->load_num_sequence_from_file();
            $job_obj->finish_job_complete();
            $job_obj->email_complete();
            $msg = "Generate ID: " . $job_obj->get_id() . " - Job Completed Successfully";
            functions::log_message($msg);
        }
        elseif (!$is_color_job) {
            if (!$job_running) {
                if ($nonblastjob_blast_failed_file_exists) {
                    $job_obj->set_num_blast();
                    $job_obj->finish_job_failed();
                    $job_obj->email_number_seq();
                    $msg = "Generate ID: " . $job_obj->get_id() . " - Job Failed - Max Number of Sequences";
                    functions::log_message($msg);
                }
                elseif ($blastjob_fail_file_exists) {
                    $job_obj->set_num_blast();
                    $job_obj->finish_job_failed();
                    $job_obj->email_bad_sequence();
                    $msg = "Generate ID: " . $job_obj->get_id() . " - Job Failed - Invalid Input Sequence";
                    functions::log_message($msg);
                }
                elseif ($nonblastjob_bad_input_format_file_exists) {
                    $job_obj->set_num_blast();
                    $job_obj->finish_job_failed();
                    $job_obj->email_format_error();
                    $msg = "Generate ID: " . $job_obj->get_id() . " - Job Failed - Bad Input File Format";
                    functions::log_message($msg);
                }
            }
        }
        elseif (!$job_running) {
            $job_obj->finish_job_failed();
            $job_obj->email_error_admin();
            $job_obj->email_general_failure();
            $msg = "Generate ID: " . $job_obj->get_id() . " - Job Failed - Error in Pipeline";
            functions::log_message($msg);
        }
    }
}


