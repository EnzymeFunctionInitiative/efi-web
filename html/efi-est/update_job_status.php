<?php
require_once(__DIR__."/../../conf/settings_paths.inc.php");
require_once(__EST_DIR__."/includes/main.inc.php");
require_once(__BASE_DIR__."/libs/job_cancels.class.inc.php");

$is_error = false;
$the_id = "";
$job_obj = false;
$is_generate = true;

if (isset($_POST["id"]) && is_numeric($_POST["id"]) && isset($_POST["key"])) {
    $the_id = $_POST["id"];
    if (isset($_POST["aid"]) && is_numeric($_POST["aid"]) && $_POST["aid"] > 0) {
        $job_obj = new analysis($db, $_POST["aid"]);
        $is_generate = false;
    } else {
        $job_obj = new stepa($db, $the_id);
    }

    if ($job_obj->get_key() != $_POST["key"]) {
        $is_error = true;
    } else {
        $is_error = false;
    }
}

$request_type = isset($_POST["rt"]) ? $_POST["rt"] : false;

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
            $ajobs = functions::get_analysis_jobs_for_generate($db, $the_id, __RUNNING__);
    
            foreach ($ajobs as $row) {
                $anum = $row["analysis_id"];
                $a_obj = new analysis($db, $anum);
                $pbs_num = $a_obj->get_pbs_number();
                if ($pbs_num)
                    job_cancels::request_job_cancellation($db, $pbs_num, "est");
                $a_obj->mark_job_as_archived();
            }
        }

        $job_obj->mark_job_as_archived();
    }
    $result["valid"] = true;
}

echo json_encode($result);


?>
