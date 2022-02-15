<?php
namespace efi\est;

require_once(__DIR__ . "/../../../init.php");

use \efi\est\functions;


class job_status extends \efi\job_status {

    /**
     * Accesses and writes job status information from database.
     *
     * @param object $db database object
     *
     * @return \efi\job_status
     */
    public function __construct($db, $id) {
        parent::__construct($db, $id, "generate", "generate");
    }

    public function archive() {
        parent::archive();

        // Cancel/archive any child analysis jobs
        $rows = functions::get_analysis_jobs_for_generate($this->db, $this->get_id());

        foreach ($rows as $row) {
            $aid = $row["analysis_id"];
            $job = new analysis_job_status($this->db, $aid);
            if ($job->is_running())
                $job->do_cancel();
            $job->archive();
        }
    }
}


