<?php

require_once "../includes/main.inc.php";
require_once "functions.class.inc.php";
require_once "const.class.inc.php";
require_once "../../libs/user_auth.class.inc.php";

class user_jobs extends user_auth {

    private $user_token;
    private $user_email = "";
    private $jobs;
    private $diagram_jobs;

    public function __construct() {
        $this->jobs = array();
        $this->diagram_jobs = array();
    }

    public function load_jobs($db, $token) {
        $this->user_token = $token;
        $this->user_email = self::get_email_from_token($db, $token);
        if (!$this->user_email)
            return;

        $this->load_gnn_jobs($db);
        $this->load_diagram_jobs($db);
    }

    private function load_gnn_jobs($db) {
        $expDate = self::get_start_date_window();
        $sql = "SELECT gnn_id, gnn_key, gnn_filename, gnn_time_completed, gnn_status, gnn_size, gnn_cooccurrence FROM gnn " .
            "WHERE gnn_email='" . $this->user_email . "' AND " .
            "(gnn_time_completed >= '$expDate' OR gnn_status = 'RUNNING' OR gnn_status = 'NEW' OR gnn_status = 'FAILED')" .
            "ORDER BY gnn_status, gnn_time_completed DESC";
        $rows = $db->query($sql);

        foreach ($rows as $row) {
            $comp = $row["gnn_time_completed"];
            if (substr($comp, 0, 4) == "0000") {
                $comp = $row["gnn_status"]; // "RUNNING";
                if ($comp == "NEW")
                    $comp = "PENDING";
            } else {
                $comp = date_format(date_create($comp), "n/j h:i A");
            }
            $jobName = "N=" . $row["gnn_size"] . " Cooc=" . $row["gnn_cooccurrence"] . " Submission=<i>" . $row["gnn_filename"] . "</i>";
            array_push($this->jobs, array("id" => $row["gnn_id"], "key" => $row["gnn_key"], "filename" => $jobName,
                                          "completed" => $comp));
        }
    }

    private function load_diagram_jobs($db) {
        $expDate = self::get_start_date_window();
        $sql = "SELECT * FROM diagram WHERE diagram_email='" . $this->user_email . "' AND " .
            "(diagram_time_completed >= '$expDate' OR diagram_status='RUNNING' OR diagram_status = 'NEW') " . 
            "ORDER BY diagram_time_completed DESC, diagram_id DESC";
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

    public function get_email() {
        return $this->user_email;
    }
}

?>

