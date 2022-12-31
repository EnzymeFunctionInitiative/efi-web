<?php

require_once(__DIR__."/../../init.php");

use \efi\job_cancels;
use \efi\est\stepa;
use \efi\est\analysis;
use \efi\est\functions;
use \efi\sanitize;


$is_error = false;
$job_obj = false;
$is_generate = true;

$id = sanitize::validate_id("id", sanitize::POST);
$key = sanitize::validate_key("key", sanitize::POST);
$aid = sanitize::validate_id("aid", sanitize::POST);

if ($id === false || $key === false) {
    error_404();
    exit;
}

if ($id !== false && $key !== false) {
    if ($aid !== false && $aid > 0) {
        $job_obj = new analysis($db, $aid);
        $is_generate = false;
    } else {
        $job_obj = new stepa($db, $id);
    }

    $jkey = $job_obj->get_key();
    if ($job_obj->get_key() != $key) {
        $is_error = true;
    } else {
        $is_error = false;
    }
}

$request_type = sanitize::post_sanitize_string("rt", "");

$result = array("valid" => false);

if (!$is_error && $request_type !== false && $job_obj !== false && ($request_type == "c" || $request_type == "a")) {
    $status = $job_obj->get_status();

    if ($status == __RUNNING__) { // cancel
        $pbs_num = $job_obj->get_pbs_number();

        if ($pbs_num)
            job_cancels::request_job_cancellation($db, $pbs_num, "est");

        $job_obj->mark_job_as_cancelled();
    } else { // archive NEW, FAILED.
        if ($is_generate) {
            $ajobs = functions::get_analysis_jobs_for_generate($db, $id);
    
            foreach ($ajobs as $row) {
                $anum = $row["analysis_id"];
                $a_obj = new analysis($db, $anum);
                $pbs_num = $a_obj->get_pbs_number();
                if ($pbs_num && $a_obj->get_status() === __RUNNING__)
                    job_cancels::request_job_cancellation($db, $pbs_num, "est");
                $a_obj->mark_job_as_archived();

                // Hide any color SSN jobs
                $cjobs = functions::get_color_jobs_for_analysis($db, $anum);
                foreach ($cjobs as $crow) {
                    $c_obj = new stepa($db, $crow["generate_id"]);
                    $c_obj->mark_job_as_archived();
                    $crjobs = functions::get_color_jobs_for_color_job($db, $crow["generate_id"]);
                    foreach ($crjobs as $crrow) {
                        $cr_job = new stepa($db, $crrow["generaet_id"]);
                        $cr_job->mark_job_as_archived();
                    }
                }
            }
        } else {
            $cjobs = functions::get_color_jobs_for_analysis($db, $aid);
            foreach ($cjobs as $crow) {
                $c_obj = new stepa($db, $crow["generate_id"]);
                $c_obj->mark_job_as_archived();
                $crjobs = functions::get_color_jobs_for_color_job($db, $crow["generate_id"]);
                foreach ($crjobs as $crrow) {
                    $cr_job = new stepa($db, $crrow["generate_id"]);
                    $cr_job->mark_job_as_archived();
                }
            }
        }

        $job_obj->mark_job_as_archived();
    }
    $result["valid"] = true;
}

echo json_encode($result);


