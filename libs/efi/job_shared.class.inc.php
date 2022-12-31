<?php
namespace efi;

require_once(__DIR__."/../../init.php");



class job_shared {

    const JOB_INFO = "job_info";
    const JOB_STATUS = "job_info_status";
    const JOB_ID = "job_info_id";
    const JOB_TYPE = "job_info_type";
    const JOB_SLURM_ID = "job_info_job_id";
    const JOB_MSG = "job_info_msg";

    public static function insert_new($db, $job_type, $job_id) {
        $info = array(self::JOB_TYPE => $job_type, self::JOB_ID => $job_id, self::JOB_STATUS => __NEW__);
        $db->build_insert(self::JOB_INFO, $info);
    }
}


