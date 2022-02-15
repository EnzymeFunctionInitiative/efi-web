<?php
namespace efi\est;

require_once(__DIR__ . "/../../../init.php");

use \efi\est\functions;


class analysis_job_status extends \efi\job_status {

    /**
     * Accesses and writes job status information from database.
     *
     * @param object $db database object
     *
     * @return \efi\job_status
     */
    public function __construct($db, $id) {
        parent::__construct($db, $id, "analysis", "analysis");
    }

    public function archive() {
        parent::archive();

        // Hide any child color SSN jobs
        $rows = functions::get_color_jobs_for_analysis($this->db, $this->get_id());
        foreach ($rows as $row) {
            $child_id = $row["generate_id"];
            $job = new \efi\est\job_status($this->db, $child_id);
            $job->archive();

            $child_rows = functions::get_color_jobs_for_color_job($this->db, $child_id);
            foreach ($child_rows as $child_row) {
                $cr_job = new \efi\est\job_status($this->db, $child_row["generate_id"]);
                $cr_job->archive();
            }
        }
    }
}


