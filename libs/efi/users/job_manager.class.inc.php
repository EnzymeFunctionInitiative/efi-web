<?php
namespace efi\users;

require_once(__DIR__."/../../../init.php");

use \efi\global_functions;
use \efi\user_auth;


class job_manager {

    private $auth_db_name = "";
    private $est_db_name = "";
    private $gnt_db_name = "";
    private $shortbred_db_name = "";
    private $db = "";
    private $est_jobs = array();
    private $gnt_jobs = array();
    private $shortbred_jobs = array();

    
    function __construct($db, $auth_db_name, $est_db_name, $gnt_db_name, $shortbred_db_name) {
        $this->est_db_name = $est_db_name; // should include db name: efi_est_dev.generate
        $this->gnt_db_name = $gnt_db_name; // should include db name: efi_gnt_dev.generate
        $this->shortbred_db_name = $shortbred_db_name; // should include db name: efi_shortbred_dev.generate
        $this->auth_db_name = $auth_db_name; // user_auth db
        $this->db = $db;
        $this->get_est_jobs();
        $this->get_gnt_jobs();
        $this->get_shortbred_jobs();
    }


    public static function update_job_group($db, $db_name, $job_ids, $user_group, $type, $do_update) {

        $col = "job_id";
        if ($type == "est") {
            $col = "generate_id";
        } elseif ($type == "gnt") {
            $col = "gnn_id";
        } elseif ($type == "shortbred") {
            $col = "identify_id";
        }

        if (!$col)
            return false;

        if (preg_match("/[^A-Za-z0-9_\-]/", $user_group)) // invalid characters in group name
            return false;

        $sql = "SELECT * FROM groups WHERE group_name = \"$user_group\"";
        if (!$db->query($sql)) // Group doesn't exist
            return false;

        foreach ($job_ids as $id) {
            if (!is_numeric($id)) // skip invalid ids
                continue; // Should we die instead?
            $sql = "";
            if ($do_update)
                $sql = "INSERT INTO job_group ($col, job_type, user_group) VALUES ($id, \"$type\", \"$user_group\")";
            else
                $sql = "DELETE FROM job_group WHERE $col = \"$id\" AND job_type = \"$type\" AND user_group = \"$user_group\"";
            $db->non_select_query($sql);
        }

        return true;
    }


    private function get_est_jobs() {
        $auth_db = $this->auth_db_name; 
        $dbn = $this->est_db_name;
        $col_email = "$dbn.generate.generate_email";
        $col_key = "$dbn.generate.generate_key";
        $col_id = "$dbn.generate.generate_id";
        $col_status = "$dbn.generate.generate_status";
        $col_time_created = "$dbn.generate.generate_time_created";
        $col_time_started = "$dbn.generate.generate_time_started";
        $col_time_completed = "$dbn.generate.generate_time_completed";
        $col_params = "$dbn.generate.generate_params";
        $col_type = "$dbn.generate.generate_type";

        $cols = implode(",", array($col_id, $col_key, $col_email, $col_status, $col_time_created,
            $col_time_started, $col_time_completed, $col_params, $col_type));

        $sql = "SELECT $cols FROM $dbn.generate " . 
            "JOIN $auth_db.user_token ON $dbn.generate.generate_email = $auth_db.user_token.user_email " .
//            "JOIN $auth_db.job_group ON $dbn.generate.generate_id = $auth_db.job_group.job_id " .
//            "WHERE $auth_db.job_group.job_type = 'EST'
            "AND $auth_db.user_token.user_admin = 1 ORDER BY $col_id DESC LIMIT 500";

        $rows = $this->db->query($sql);

        if (!$rows) {
            return false;
        }

        $this->est_jobs = $this->get_jobs($rows, "generate", "EST");

        return true;
    }

        
    private function get_jobs($rows, $table, $db_name) {
        $job_data = array();

        foreach ($rows as $result) {
            $status = $result["{$table}_status"];
            $email = $result["{$table}_email"];
            $key = $result["{$table}_key"];
            $id = $result["{$table}_id"];
            $info = "";
            //TODO: fix this hard coded schema stuff
            if ($table == "gnn") {
                $parms = global_functions::decode_object($result["{$table}_params"]);
                $info = substr($parms["filename"], 0, 40);
            } elseif ($table == "identify") {
                $parms = global_functions::decode_object($result["{$table}_params"]);
                $info = substr($parms["{$table}_filename"], 0, 40);
            } else {
                $type = $result["generate_type"];
                $parms = global_functions::decode_object($result["generate_params"]);
                $info = $type;
                if (isset($parms["generate_fasta_file"]) && $parms["generate_fasta_file"])
                    $info .= " file=" . substr($parms["generate_fasta_file"], 0, 40);
                if (isset($parms["generate_families"]) && $parms["generate_families"])
                    $info .=  " family=" . implode(", ", explode(",", $parms["generate_families"]));
            }

            $job_data[$id] = array("email" => $email, "key" => $key,
                                           "time_completed" => $result["{$table}_time_completed"],
                                           "time_started" => $result["{$table}_time_started"],
                                           "time_created" => $result["{$table}_time_created"],
                                           "status" => $status,
                                           "info" => $info,
                                           "groups" => array(),
                                       );
        } 

        foreach ($job_data as $id => $job) {
            $sql = "SELECT * FROM job_group WHERE job_group.job_id = $id AND job_group.job_type = \"$db_name\"";
            $rows = $this->db->query($sql);

            $groups = array();
            foreach ($rows as $row) {
                array_push($groups, $row["user_group"]);
            }
            $job_data[$id]["groups"] = $groups;
        }

        return $job_data;
    }

    
    private function get_gnt_jobs() {
        $auth_db = $this->auth_db_name; 
        $dbn = $this->gnt_db_name;
        $col_email = "$dbn.gnn.gnn_email";
        $col_key = "$dbn.gnn.gnn_key";
        $col_id = "$dbn.gnn.gnn_id";
        $col_status = "$dbn.gnn.gnn_status";
        $col_time_created = "$dbn.gnn.gnn_time_created";
        $col_time_started = "$dbn.gnn.gnn_time_started";
        $col_time_completed = "$dbn.gnn.gnn_time_completed";
        $col_params = "$dbn.gnn.gnn_params";

        $cols = implode(",", array($col_id, $col_key, $col_email, $col_status, $col_time_created,
            $col_time_started, $col_time_completed, $col_params));

        $sql = "SELECT $cols FROM $dbn.gnn " . 
            "JOIN $auth_db.user_token ON $dbn.gnn.gnn_email = $auth_db.user_token.user_email " .
            "WHERE $auth_db.user_token.user_admin = 1 ORDER BY $col_id DESC";

        $rows = $this->db->query($sql);

        if (!$rows) {
            return false;
        }

        $this->gnt_jobs = $this->get_jobs($rows, "gnn", "GNT");

        return true;
    }

    
    private function get_shortbred_jobs() {
        $auth_db = $this->auth_db_name; 
        $dbn = $this->shortbred_db_name;
        $col_email = "$dbn.identify.identify_email";
        $col_key = "$dbn.identify.identify_key";
        $col_id = "$dbn.identify.identify_id";
        $col_status = "$dbn.identify.identify_status";
        $col_time_created = "$dbn.identify.identify_time_created";
        $col_time_started = "$dbn.identify.identify_time_started";
        $col_time_completed = "$dbn.identify.identify_time_completed";
        $col_params = "$dbn.identify.identify_params";

        $cols = implode(",", array($col_id, $col_key, $col_email, $col_status, $col_time_created,
            $col_time_started, $col_time_completed, $col_params));

        $sql = "SELECT $cols FROM $dbn.identify " . 
            "JOIN $auth_db.user_token ON $dbn.identify.identify_email = $auth_db.user_token.user_email " .
            "WHERE $auth_db.user_token.user_admin = 1 ORDER BY $col_id DESC";

        $rows = $this->db->query($sql);

        if (!$rows) {
            return false;
        }

        $this->shortbred_jobs = $this->get_jobs($rows, "identify", "CGFP");

        return true;
    }


    public function get_all_est_job_ids() {
        return $this->get_all_job_ids($this->est_jobs, false);
    }

    public function get_all_gnt_job_ids() {
        return $this->get_all_job_ids($this->gnt_jobs, false);
    }

    public function get_all_shortbred_job_ids() {
        return $this->get_all_job_ids($this->shortbred_jobs, false);
    }

    public function get_grouped_est_job_ids() {
        return $this->get_all_job_ids($this->est_jobs, true);
    }

    public function get_grouped_gnt_job_ids() {
        return $this->get_all_job_ids($this->gnt_jobs, true);
    }

    public function get_grouped_shortbred_job_ids() {
        return $this->get_all_job_ids($this->shortbred_jobs, true);
    }

    private function get_all_job_ids($data, $grouped_only = false) {
        $ids = array();
        foreach ($data as $id => $job) {
            if ($grouped_only && count($data[$id]["groups"]) > 0) {
                array_push($ids, $id);
            } elseif (!$grouped_only) {
                array_push($ids, $id);
            }
        }
        return $ids;
    }




    public function get_est_job_by_id($job_id) {
        return $this->get_job_by_id($job_id, $this->est_jobs);
    }

    public function get_gnt_job_by_id($job_id) {
        return $this->get_job_by_id($job_id, $this->gnt_jobs);
    }

    public function get_shortbred_job_by_id($job_id) {
        return $this->get_job_by_id($job_id, $this->shortbred_jobs);
    }

    private function get_job_by_id($job_id, $data) {
        $job = array();
        if (isset($data[$job_id])) {
            $job["id"] = $job_id;
            $job["key"] = $data[$job_id]["key"];
            $job["email"] = $data[$job_id]["email"];
            $job["status"] = $data[$job_id]["status"];
            $job["group"] = $data[$job_id]["groups"];
            $job["info"] = $data[$job_id]["info"];
            $tco = $data[$job_id]["time_completed"];
            if ($tco && $tco != "FAILED")
                $job["time_completed"] = self::format_short_date($tco);
            else
                $job["time_completed"] = $data[$job_id]["status"];
            $ts = $data[$job_id]["time_started"];
            if ($ts)
                $job["time_started"] = self::format_short_date($ts);
            else
                $job["time_started"] = "";
            $job["time_created"] = $data[$job_id]["time_created"];
        }
        return $job;
    }

    // Candidate for refacotring to centralize
    private function get_completed_date_label($comp, $status) {
        $isCompleted = false;
        if ($status == "FAILED") {
            $comp = "FAILED";
        } elseif (!$comp || substr($comp, 0, 4) == "0000") {
            $comp = $status;
            if ($comp == "NEW")
                $comp = "PENDING";
        } else {
            $comp = self::format_short_date($comp);
            $isCompleted = true;
        }
        return array($isCompleted, $comp);
    }

    private static function format_short_date($comp) {
        return date_format(date_create($comp), "n/j h:i A");
    }
}


