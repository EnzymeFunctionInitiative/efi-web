<?php
namespace efi\gnt;

require_once(__DIR__."/../../../init.php");

use \efi\global_settings;
use \efi\global_functions;
use \efi\job_list_ui;


class gnt_job_ui extends job_list_ui {

    private $this->child_jobs = array();

    public function __construct($db, $email, $include_failed_jobs) {
        parent::__construct($db, "taxonomy", $email, $include_failed_jobs, "hl-tax");
        $this->child_jobs = array();
    }

    protected function get_job_name($filename, $params) {
        $job_name = "<span class='job-name'>$filename</span><br><span class='job-metadata'>Neighborhood Size=" . $params["neighborhood_size"] . " Co-occurrence=" . $params["cooccurrence"] . "</span>";
        return $job_name;
    }

    protected function get_extra_job_info($row, $params) {
        $the_table = $this->table_name;

        $is_child = false;
        if (isset($row["${the_table}_parent_id"]) && $row["${the_table}_parent_id"]) {
            // Get parent email address and if it's not the same as the current email address then treat this job
            // as a normal job.
            $sql = "SELECT ${the_table}_email FROM gnn WHERE ${the_table}_id = " . $row["${the_table}_parent_id"];
            $parent_row = $this->db->query($sql);
            $is_child = !$parent_row || $parent_row[0]["${the_table}_email"] == $this->email || !$this->email;
        }

        $info = false;
        if ($is_child) {
            $info = array("is_child" => true);
            $parent_id = $row["${the_table}_parent_id"];
            $info["parent_id"] = $parent_id;
            if (isset($this->child_jobs[$parent_id]))
                array_push($this->child_jobs[$parent_id], $job_info);
            else
                $this->child_jobs[$parent_id] = array($job_info);
        }

        if ($is_child)
            return $info;
        else
            return false;
    }

    protected function post_process_job_list($jobs) {
        for ($i = 0; $i < count($jobs); $i++) {
            $id = $jobs[$i]["id"];
            if (isset($this->child_jobs[$id])) {
                array_splice($jobs, $i+1, 0, $this->child_jobs[$id]);
            }
        }
        return $jobs;
    }

    protected function get_url($id, $key, $job) {
        $base_url_dir = global_settings::get_gnt_web_path();
        $url = "${base_url_dir}stepc.php?id=$id&key=$key";
        if (array_key_exists("diagram", $jobs[$i]))
            $url = "${base_url_dir}view_diagrams.php?upload-id=$id&key=$key$example_arg";
        return $url;
    }
    
    protected function check_for_indent($job) {
        return (isset($job["is_child"]) && $job["is_child"]);
    }

}


