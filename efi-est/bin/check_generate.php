<?php
chdir(dirname(__FILE__));
require_once '../includes/main.inc.php';

$sapi_type = php_sapi_name();
//If run from command line
if ($sapi_type != 'cli') {
    echo "Error: This script can only be run from the command line.\n";
}
elseif (count($argv) < 2) {
    echo "Error: An argument must be provided, specifying the job type to analyze.\n";
}
else {
    $runType = $argv[1];
    $running_jobs = getJobs($runType, $db);

    $is_color_job = $runType == colorssn::create_type();

    foreach ($running_jobs as $job) {
        $jobObj = getJobObject($runType, $job['generate_id'], $db);

        $nonblastjob_blast_failed_file_exists = getNonBlastFailedFileExists($runType, $jobObj);
        $blastjob_fail_file_exists = getBlastFailedFileExists($runType, $jobObj);
        $finish_file_exists = $jobObj->check_finish_file();
        $job_running = $jobObj->check_pbs_running();

        if (!$job_running && $finish_file_exists) {
            $jobObj->set_status(__FINISH__);
            $jobObj->set_time_completed();
            $num_seq = $jobObj->get_num_sequence_from_file();
            $jobObj->set_num_sequences($num_seq);
            $jobObj->email_complete();
            $msg = "Generate ID: " . $job['generate_id'] . " - Job Completed Successfully";
            functions::log_message($msg);
        }
        elseif (!$is_color_job && !$job_running && $nonblastjob_blast_failed_file_exists) {
            $jobObj->set_status(__FAILED__);
            $jobObj->set_num_blast();
            $jobObj->set_time_completed();
            $jobObj->email_number_seq();
            $msg = "Generate ID: " . $job['generate_id'] . " - Job Failed - Max Number of Sequences";
            functions::log_message($msg);
        }
        elseif (!$is_color_job && !$job_running && $blastjob_fail_file_exists) {
            $jobObj->set_status(__FAILED__);
            $jobObj->set_num_blast();
            $jobObj->set_time_completed();
            $jobObj->email_number_seq();
            $msg = "Generate ID: " . $job['generate_id'] . " - Job Failed - Max Number of Sequences";
            functions::log_message($msg);
        }
        elseif (!$job_running) {
            $jobObj->set_status(__FAILED__);
            $jobObj->email_error_admin();
            $msg = "Generate ID: " . $job['generate_id'] . " - Job Failed - Error in Pipeline";
            functions::log_message($msg);
        }
    }
}




function getJobObject($runType, $jobId, $db) {
    switch ($runType) {
        case accession::create_type():
            return new accession($db, $jobId);
        case blast::create_type():
            return new blast($db, $jobId);
        case fasta::create_type():
            return new fasta($db, $jobId);
        case generate::create_type():
            return new generate($db, $jobId);
        default:
            return NULL;
    }
}

function getNonBlastFailedFileExists($runType, $jobObj) {
    if ($runType != blast::create_type())
        return $jobObj->check_max_blast_failed_file();
    else
        return false;
}

function getBlastFailedFileExists($runType, $jobObj) {
    if ($runType == blast::create_type()) 
        return $jobObj->check_fail_file();
    else
        return false;
}

function getJobs($runType, $db) {
    switch ($runType) {
        case accession::create_type():
            return functions::get_accessions($db,__RUNNING__);
        case blast::create_type():
            return functions::get_blasts($db,__RUNNING__);
        case fasta::create_type():
            return functions::get_fastas($db,__RUNNING__);
        case generate::create_type():
            return functions::get_families($db,__RUNNING__);
        default:
            return array();
    }
}

?>
