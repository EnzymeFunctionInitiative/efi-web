<?php
namespace efi\taxonomy;

require_once(__DIR__."/../../../init.php");

use \efi\user_jobs;
use \efi\taxonomy\taxonomy_ui;


class taxonomy_jobs extends user_jobs {

    public function __construct($db) {
        parent::__construct($db);
    }

    private function get_select_statement() {
        $sql = "SELECT generate_id, generate_key, generate_time_completed, generate_time_started, generate_time_created, generate_status, generate_params, generate_type FROM generate ";
        return $sql;
    }

    protected function load_user_jobs() {
        $email = $this->user_email;
        $exp_date = self::get_start_date_window();

        $sql = $this->get_select_statement();
        $sql .=
            "WHERE generate_email='$email' AND generate_status != 'ARCHIVED' AND generate_is_tax_job = 1 AND " .
            "(generate_time_completed >= '$exp_date' OR generate_status = 'RUNNING' OR generate_status = 'NEW' OR generate_status = 'FAILED') " .
            "ORDER BY generate_status, generate_time_completed DESC";
        $rows = $this->db->query($sql);

        $include_failed_jobs = true;

        $ui = new taxonomy_ui($this->db, $email, $include_failed_jobs);
        $this->jobs = $ui->process_load_rows($rows);
    }
}

