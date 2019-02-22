<?php

require_once "../includes/main.inc.php";
require_once "functions.class.inc.php";
require_once "const.class.inc.php";
require_once "../../libs/user_auth.class.inc.php";

class user_jobs extends user_auth {

    private $user_token = "";
    private $user_email = "";
    private $jobs = array();
    private $diagram_jobs = array();
    private $training_jobs = array();
    private $is_admin = false;
    private $user_jobs = array();
    private $user_groups = array();

    public function __construct() {
    }

    public function load_jobs($db, $token) {
        $this->user_token = $token;
        $this->user_email = self::get_email_from_token($db, $token);
        if (!$this->user_email)
            return;

        $this->user_groups = self::get_user_groups($db, $this->user_token);
        array_unshift($this->user_groups, settings::get_default_group_name());
        $this->is_admin = self::get_user_admin($db, $this->user_email);

        $this->load_gnn_jobs($db);
        $this->load_diagram_jobs($db);
        $this->load_training_jobs($db);
    }

    private static function get_select_statement() {
        $sql = "SELECT gnn.gnn_id, gnn_key, gnn_filename, gnn_time_completed, gnn_status, gnn_size, gnn_cooccurrence, gnn_params FROM gnn ";
        return $sql;
    }

    private static function get_group_select_statement($group_clause) {
        $group_clause .= " AND";
        $sql = self::get_select_statement() .
            "LEFT OUTER JOIN job_group ON gnn.gnn_id = job_group.gnn_id " .
            "WHERE $group_clause gnn_status = 'FINISH' " .
            "ORDER BY gnn_status, gnn_time_completed DESC";
        return $sql;
    }

    private function load_gnn_jobs($db) {
        $email = $this->user_email;
        $expDate = self::get_start_date_window();

        $sql = self::get_select_statement();
        $sql .=
            "WHERE gnn_email='$email' AND gnn_status != 'ARCHIVED' AND " .
            "(gnn_time_completed >= '$expDate' OR gnn_status = 'RUNNING' OR gnn_status = 'NEW' OR gnn_status = 'FAILED') " .
            "ORDER BY gnn_status, gnn_time_completed DESC";
        $rows = $db->query($sql);

        $includeFailedJobs = true;
        $this->jobs = self::process_load_rows($db, $rows, $includeFailedJobs, $email);
    }

    private function load_training_jobs($db) {
        $func = function($val) { return "user_group = '$val'"; };
        $group_clause = implode(" OR ", array_map($func, $this->user_groups));
        if ($group_clause) {
            $group_clause = "($group_clause)";
        } else {
            $this->training_jobs = array();
            return;
        }

        $sql = self::get_group_select_statement($group_clause);
        $rows = $db->query($sql);

        $includeFailedJobs = false;
        $this->training_jobs = self::process_load_rows($db, $rows, $includeFailedJobs, "");
    }
 
    private static function process_load_rows($db, $rows, $includeFailedJobs, $email) {
        $jobs = array();
        $indexMap = array();

        $childJobs = array();

        $idx = 0;
        foreach ($rows as $row) {
            $comp = $row["gnn_time_completed"];
            if (substr($comp, 0, 4) == "0000") {
                $comp = $row["gnn_status"]; // "RUNNING";
                if ($comp == "NEW")
                    $comp = "PENDING";
            } else {
                $comp = date_format(date_create($comp), "n/j h:i A");
            }
            $filename = pathinfo($row["gnn_filename"], PATHINFO_BASENAME);
            $params = global_functions::decode_object($row["gnn_params"]);
            $jobName = "N=" . $row["gnn_size"] . " Cooc=" . $row["gnn_cooccurrence"] . " Submission=<i>" . $filename . "</i>";

            $id = $row["gnn_id"];
            $jobInfo = array("id" => $id, "key" => $row["gnn_key"], "filename" => $jobName, "completed" => $comp, "is_child" => false);

            $isChild = false;
            if (isset($params["gnn_parent_id"]) && $params["gnn_parent_id"]) {
                // Get parent email address and if it's not the same as the current email address then treat this job
                // as a normal job.
                $sql = "SELECT gnn_email FROM gnn WHERE gnn_id = " . $params["gnn_parent_id"];
                $parentRow = $db->query($sql);
                $isChild = !$parentRow || $parentRow[0]["gnn_email"] == $email || !$email;  // !$email is true for training jobs
            }

            if ($isChild) {
                $parentId = $params["gnn_parent_id"];
                $jobInfo["is_child"] = true;
                $jobInfo["parent_id"] = $parentId;
                if (isset($childJobs[$parentId]))
                    array_push($childJobs[$parentId], $jobInfo);
                else
                    $childJobs[$parentId] = array($jobInfo);
#                if (isset($indexMap[$parentId])) {
#                    var_dump($jobs);
#                    array_splice($jobs, $indexMap[$parentId], 0, $jobInfo);
#                    var_dump($jobs);
#                    die();
#                } else {
#                    die("$parentId doesn't exist");
#                    array_push($jobs, $jobInfo);
#                }
            } else {
                array_push($jobs, $jobInfo);
                $indexMap[$id] = $idx;
                $idx++;
            }
        }

        for ($i = 0; $i < count($jobs); $i++) {
            $id = $jobs[$i]["id"];
            if (isset($childJobs[$id])) {
                array_splice($jobs, $i+1, 0, $childJobs[$id]);
            }
        }

        return $jobs;
    }

    private function load_diagram_jobs($db) {
        $func = function($val) { return "user_group = '$val'"; };
        $email = $this->user_email;
        $groupClause = implode(" OR ", array_map($func, $this->user_groups));
        if ($groupClause)
                $groupClause = "OR $groupClause";
        $expDate = self::get_start_date_window();

        $sql = "SELECT diagram.diagram_id, diagram_key, diagram_title, diagram_status, diagram_type, diagram_time_completed FROM diagram " .
            "LEFT OUTER JOIN job_group ON diagram.diagram_id = job_group.diagram_id " .
            "WHERE (diagram_email='$email' $groupClause) AND " .
            "(diagram_time_completed >= '$expDate' OR diagram_status='RUNNING' OR diagram_status = 'NEW') " . 
            "ORDER BY diagram_time_completed DESC, diagram.diagram_id DESC";
        $rows = $db->query($sql);

        foreach ($rows as $row) {
            $title = "";
            if ($row["diagram_title"])
                $title = $row["diagram_title"];
            else
                $title = "<i>Untitled</i>";
            
            $theDate = $row["diagram_time_completed"];
            if (substr($theDate, 0, 4) == "0000") {
                $theDate = $row["diagram_status"]; // "RUNNING";
                if ($theDate == "NEW")
                    $theDate = "PENDING";
            } else {
                $theDate = date_format(date_create($theDate), "n/j h:i A");
            }

            $isDirect = $row["diagram_type"] != DiagramJob::Uploaded && $row["diagram_type"] != DiagramJob::UploadedZip;
            $idField = functions::get_diagram_id_field($row["diagram_type"]);

            array_push($this->diagram_jobs, array("id" => $row["diagram_id"], "key" => $row["diagram_key"],
                "filename" => $title, "completed" => $theDate, "id_field" => $idField,
                "verbose_type" => functions::get_verbose_job_type($row["diagram_type"])));
        }
    }

    public function save_user($db, $email) {
        $this->user_email = $email;

        $sql = "SELECT user_id, user_email FROM user_token WHERE user_email='" . $this->user_email . "'";
        $rows = $db->query($sql);

        $isUpdate = false;
        if ($rows && count($rows) > 0) {
            $isUpdate = true;
            $this->user_token = $rows[0]["user_id"];
        } else {
            $this->user_token = functions::generate_key();
        }

        $insert_array = array("user_id" => $this->user_token, "user_email" => $this->user_email);
        if (!$isUpdate) {
            $db->build_insert("user_token", $insert_array);
        }

        return true;
    }

    public function get_jobs() {
        return $this->jobs;
    }

    public function get_diagram_jobs() {
        return $this->diagram_jobs;
    }

    public function get_training_jobs() {
        return $this->training_jobs;
    }

    public function get_email() {
        return $this->user_email;
    }

    public function is_admin() {
        return $this->is_admin;
    }

    public function get_groups() {
        return $this->user_groups;
    }
}

?>

