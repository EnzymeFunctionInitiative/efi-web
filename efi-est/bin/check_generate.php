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
elseif (count($argv) < 2) {
    echo "Error: An argument must be provided, specifying the job type to analyze.\n";
}
else {
    $run_type = $argv[1];
    $running_jobs = get_jobs($run_type, $db);

    $is_color_job = $run_type == colorssn::create_type();

    foreach ($running_jobs as $job) {
        $job_obj = get_job_object($run_type, $job['generate_id'], $db);

        $nonblastjob_blast_failed_file_exists = get_non_blast_failed_file_exists($run_type, $job_obj);
        $blastjob_fail_file_exists = get_blast_failed_file_exists($run_type, $job_obj);
        $finish_file_exists = $job_obj->check_finish_file();
        $nonblastjob_bad_input_format_file_exists = get_non_blast_bad_input_file_format_file_exists($run_type, $job_obj);
        $job_running = $job_obj->check_pbs_running();

        if (!$job_running && $finish_file_exists) {
            $job_obj->load_num_sequence_from_file();
            $job_obj->finish_job_complete();
            $job_obj->email_complete();
            $msg = "Generate ID: " . $job['generate_id'] . " - Job Completed Successfully";
            functions::log_message($msg);
        }
        elseif (!$is_color_job && !$job_running && $nonblastjob_blast_failed_file_exists) {
            $job_obj->set_num_blast();
            $job_obj->finish_job_failed();
            $job_obj->email_number_seq();
            $msg = "Generate ID: " . $job['generate_id'] . " - Job Failed - Max Number of Sequences";
            functions::log_message($msg);
        }
        elseif (!$is_color_job && !$job_running && $blastjob_fail_file_exists) {
            $job_obj->set_num_blast();
            $job_obj->finish_job_failed();
            $job_obj->email_bad_sequence();
            $msg = "Generate ID: " . $job['generate_id'] . " - Job Failed - Invalid Input Sequence";
            functions::log_message($msg);
        }
        elseif (!$is_color_job && !$job_running && $nonblastjob_bad_input_format_file_exists) {
            $job_obj->set_num_blast();
            $job_obj->finish_job_failed();
            $job_obj->email_format_error();
            $msg = "Generate ID: " . $job['generate_id'] . " - Job Failed - Bad Input File Format";
            functions::log_message($msg);
        }
        elseif (!$job_running) {
            $job_obj->finish_job_failed();
            $job_obj->email_error_admin();
            $job_obj->email_general_failure();
            $msg = "Generate ID: " . $job['generate_id'] . " - Job Failed - Error in Pipeline";
            functions::log_message($msg);
        }
    }
}




function get_job_object($run_type, $jobId, $db) {
    switch ($run_type) {
        case accession::create_type():
            return new accession($db, $jobId);
        case blast::create_type():
            return new blast($db, $jobId);
        case fasta::create_type():
            return new fasta($db, $jobId);
        case generate::create_type():
            return new generate($db, $jobId);
        default:
            return job_factory::create($db, $jobId);
    }
}

function get_non_blast_failed_file_exists($run_type, $job_obj) {
    if ($run_type != blast::create_type())
        return $job_obj->check_max_blast_failed_file();
    else
        return false;
}

function get_non_blast_bad_input_file_format_file_exists($run_type, $job_obj) {
    if ($run_type != blast::create_type())
        return $job_obj->check_bad_input_format_file();
    else
        return false;
}

function get_blast_failed_file_exists($run_type, $job_obj) {
    if ($run_type == blast::create_type()) 
        return $job_obj->check_fail_file();
    else
        return false;
}

function get_jobs($run_type, $db) {
    switch ($run_type) {
        case accession::create_type():
            return functions::get_accessions($db,__RUNNING__);
        case blast::create_type():
            return functions::get_blasts($db,__RUNNING__);
        case fasta::create_type():
            return functions::get_fastas($db,__RUNNING__);
        case generate::create_type():
            return functions::get_families($db,__RUNNING__);
        case colorssn::create_type():
            return functions::get_colorssns($db,__RUNNING__);
        default:
            return array();
    }
}

?>
