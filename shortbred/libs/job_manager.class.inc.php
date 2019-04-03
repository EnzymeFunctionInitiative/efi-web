<?php

require_once("job_types.class.inc.php");
require_once(__BASE_DIR__ . "/libs/user_auth.class.inc.php");
require_once(__BASE_DIR__ . "/libs/global_functions.class.inc.php");
require_once("functions.class.inc.php");


class job_manager {

    private $table_name = "";
    private $db = "";
    private $jobs_by_status = array();
    private $jobs_by_id = array();

    public static function init_by_date_range($db, $table_name, $date_range) {
        return new job_manager($db, $table_name, $date_range, NULL, NULL);
    }

    public static function init_by_user($db, $table_name, $user_token) {
        $user_email = user_auth::get_email_from_token($db, $user_token);
        $user_groups = user_auth::get_user_groups($db, $user_token);
        if (!$user_email)
            return NULL;

        return new job_manager($db, $table_name, NULL, $user_email, $user_groups);
    }

    function __construct($db, $table_name, $date_range = NULL, $email = NULL, $groups = NULL) {
        $this->table_name = $table_name;
        $this->db = $db;
        $this->get_jobs($date_range, $email, $groups);
    }
    
    public function validate_job($id, $key) {
        return isset($this->jobs_by_id[$id]) && $this->jobs_by_id[$id]["key"] == $key;
    }

    private function get_jobs($date_range, $email, $groups) {
        $table = $this->table_name;
        $id_table = job_types::Identify;
        $q_table = job_types::Quantify;
        $col_email = "${id_table}_email";
        $col_key = "${id_table}_key";
        $col_id = "${table}_id";
        $col_status = "${table}_status";
        $col_time_created = "${table}_time_created";
        $col_time_started = "${table}_time_started";
        $col_time_completed = "${table}_time_completed";
        $col_filename = "${id_table}_filename";
        $col_mg = "${q_table}_metagenome_ids";
        $col_qid = "${q_table}_${id_table}_id";
        $col_iparams = "${id_table}_params";
        $col_qparams = "${q_table}_params";
        $col_parent_id = "${id_table}_parent_id";
        $col_job_name = "${q_table}_job_name";

        $cols = implode(",", array($col_id, $col_key, $col_email, $col_status, $col_time_created, $col_time_started,
            $col_time_completed, $col_iparams, $col_parent_id));
        if ($table == job_types::Quantify) {
            $cols .= ", $col_qid, $col_qparams";
        }
        //$cols = implode(",", array($col_id, $col_key, $col_email, $col_status, $col_filename, $col_time_created,
        //    $col_time_started, $col_time_completed));
        //if ($table == job_types::Quantify) {
        //    $cols .= ", $col_qid, $col_mg";
        //}

        $where_params = array();
        if ($date_range !== NULL) {
            if (isset($date_range["start"]))
                array_push($where_params, "$col_time_created >= '" . $date_range["start"] . "'");
            if (isset($date_range["end"]))
                array_push($where_params, "$col_time_created <= '" . $date_range["end"] . "'");
        }
        if ($email !== NULL) {
            array_push($where_params, "$col_email = '$email'");
        }

        $q_sql = "";
        $where_sql = "";
        $order_sql = "ORDER BY $col_id";
        if ($table == job_types::Quantify) {
            $q_sql = "JOIN $id_table ON $table.${table}_identify_id = ${id_table}_id";
            $order_sql = "ORDER BY $table.$col_qid";
        }
        if (count($where_params) > 0) {
            $where_sql = "WHERE " . implode(" AND ", $where_params);
        }
        $sql = "SELECT $cols FROM $table $q_sql $where_sql $order_sql";

        $rows = $this->db->query($sql);

        if (!$rows) {
            return false;
        }

        foreach ($rows as $result) {
            $status = $result[$col_status];
            $email = $result[$col_email];
            $key = $result[$col_key];
            $id = $result[$col_id];

            if (!isset($this->jobs_by_status[$status])) {
                $this->jobs_by_status[$status] = array();
            }

            $iparams = global_functions::decode_object($result[$col_iparams]);

            array_push($this->jobs_by_status[$status], array("email" => $email, "key" => $key, "id" => $id));
            $this->jobs_by_id[$id] = array("email" => $email, "key" => $key, "filename" => $iparams[$col_filename],
                                           "time_completed" => $result[$col_time_completed],
                                           "time_started" => $result[$col_time_started],
                                           "time_created" => $result[$col_time_created],
                                           "status" => $status,
                                       );
            if ($table == job_types::Quantify) {
                $this->jobs_by_id[$id]["identify_id"] = $result[$col_qid];
                $qparams = global_functions::decode_object($result[$col_qparams]);
                $this->jobs_by_id[$id]["metagenomes"] = $qparams[$col_mg];
                $this->jobs_by_id[$id]["job_name"] = isset($qparams[$col_job_name]) ? $qparams[$col_job_name] : "";
            }
            if ($result[$col_parent_id])
                $this->jobs_by_id[$id]["parent_id"] = $result[$col_parent_id];
        }  
        
        return true;
    }

    public function get_all_job_ids() {
        $ids = array();
        foreach ($this->jobs_by_id as $id => $job) {
            array_push($ids, $id);
        }
        return $ids;
    }

    public function get_job_by_id($job_id) {
        $id_table = job_types::Identify;
        $job = array();
        if (isset($this->jobs_by_id[$job_id])) {
            $job["id"] = $job_id;
            $job["key"] = $this->jobs_by_id[$job_id]["key"];
            $job["email"] = $this->jobs_by_id[$job_id]["email"];
            $job["filename"] = $this->jobs_by_id[$job_id]["filename"];
            $tco = $this->jobs_by_id[$job_id]["time_completed"];
            $status = $this->jobs_by_id[$job_id]["status"];
            if ($tco && $status == __FINISH__)
                $job["time_completed"] = functions::format_short_date($tco);
            else
                $job["time_completed"] = $status;
            $ts = $this->jobs_by_id[$job_id]["time_started"];
            if ($ts)
                $job["time_started"] = functions::format_short_date($ts);
            else
                $job["time_started"] = "";
            $tcr = $this->jobs_by_id[$job_id]["time_created"];
            if ($tcr)
                $job["time_created"] = functions::format_short_date($tcr);
            else
                $job["time_created"] = "";

            if ($this->table_name == job_types::Quantify) {
                $mg_ids = explode(",", $this->jobs_by_id[$job_id]["metagenomes"]);
                $job["metagenomes"] = $mg_ids;
                $job["identify_id"] = $this->jobs_by_id[$job_id]["identify_id"];
                if (isset($this->jobs_by_id[$job_id]["parent_identify_id"]))
                    $job["parent_identify_id"] = $this->jobs_by_id[$job_id]["parent_identify_id"];
            }
        }
        return $job;
    }

    // $queue_limit - set to true to allow only 1 RUNNING job per non admin-user.
    public function get_new_job_ids($queue_limit = false) {
        if (!isset($this->jobs_by_status[__NEW__])) {
            return array();
        }

        $admin_users = $this->get_admin_users();
        $users = array();
        $ids = array();
        foreach ($this->jobs_by_status[__NEW__] as $job) {
            $id = $job["id"];
            $email = $this->jobs_by_id[$id]["email"];
            if ($queue_limit && !isset($users[$email])) {
                if (isset($admin_users[$email])) {
                    array_push($ids, $id);
                } else {
                    $user_jobs = $this->get_running_jobs_by_user($email);
                    if (count($user_jobs) == 0)
                        array_push($ids, $id);
                    else
                        print "Skipping $id because the user's queue limit has been exceeded.\n";
                    $users[$email] = 1;
                }
            } elseif (!$queue_limit) {
                array_push($ids, $id);
            } else {
                print "Skipping $id because the user's queue limit has been exceeded.\n";
            }
        }

        return $ids;
    }

    private function get_admin_users() {
        $users = user_auth::get_admin_users($this->db);
        return $users;
    }

    public function get_running_job_ids() {
        if (!isset($this->jobs_by_status[__RUNNING__])) {
            return array();
        }

        $ids = array();
        foreach ($this->jobs_by_status[__RUNNING__] as $job) {
            array_push($ids, $job["id"]);
        }

        return $ids;
    }

    public function get_job_key($job_id) {
        if (!isset($this->jobs_by_id[$job_id])) {
            return "";
        } else {
            return $this->jobs_by_id[$job_id]["key"];
        }
    }

    public function get_running_jobs_by_user($email) {
        $id_sql = "SELECT identify_id, quantify_id FROM identify " .
            "LEFT JOIN quantify ON identify_id = quantify_identify_id " .
            "WHERE identify_email = '$email' AND " .
            "(identify_status = 'RUNNING' OR quantify_status = 'RUNNING')";
        //    "(identify_status = 'NEW' OR identify_status = 'RUNNING' OR quantify_status = 'NEW' OR quantify_status = 'RUNNING')";
        $id_rows = $this->db->query($id_sql);
        $ids = array();
        foreach ($id_rows as $row) {
            array_push($ids, $row["identify_id"]);
        }
        return $ids;
    }

    public function get_jobs_by_user($user_token) {
        $email = user_auth::get_email_from_token($this->db, $user_token);

        $start_date = user_auth::get_start_date_window();
        $id_sql = self::get_user_jobs_sql();
        $id_sql .=
            "WHERE identify_email='$email' AND identify_status != '" . __ARCHIVED__ . "' AND " .
            "(identify_time_completed >= '$start_date' OR " .
            "(identify_time_created >= '$start_date' AND (identify_status = 'NEW' OR identify_status = 'RUNNING'))) " .
            "ORDER BY identify_status, identify_time_completed DESC";
        $id_rows = $this->db->query($id_sql);

        $jobs = $this->get_jobs_by_user_shared($id_rows);

        return $jobs;
    }

    public function get_training_jobs($user_token) {
        $email = user_auth::get_email_from_token($this->db, $user_token);
        $user_groups = user_auth::get_user_groups($this->db, $user_token);

        $func = function($val) { return "user_group = '$val'"; };
        $group_clause = implode(" OR ", array_map($func, $user_groups));
        if ($group_clause) {
            $group_clause = "($group_clause)";
        } else {
            return array();
        }

        $sql = self::get_user_jobs_sql($group_clause);
        $sql .= "ORDER BY identify_status, identify_time_completed DESC";
        $id_rows = $this->db->query($sql);

        $jobs = $this->get_jobs_by_user_shared($id_rows);

        return $jobs;
    }

    private static function get_user_jobs_sql($group_clause = "") {
        $sql = "SELECT identify.identify_id, identify_key, identify_time_completed, identify_params, identify_status, identify_parent_id ";
        $sql .= "FROM identify ";
        if ($group_clause) {
            $sql .= 
                "LEFT OUTER JOIN job_group ON identify.identify_id = job_group.identify_id " .
                "WHERE $group_clause AND identify_status = 'FINISH' ";
        }
        return $sql;
    }

    private function get_jobs_by_user_shared($id_rows) {
        $jobs = array();

        foreach ($id_rows as $id_row) {
            $iparams = global_functions::decode_object($id_row["identify_params"]);

            $comp_result = self::get_completed_date_label($id_row["identify_time_completed"], $id_row["identify_status"]);
            $job_name = $iparams["identify_filename"];
            $comp = $comp_result[1];
            $is_completed = $comp_result[0];

            $id_id = $id_row["identify_id"];
            $key = $id_row["identify_key"];
            $parent_id = $id_row["identify_parent_id"];

            if (!$parent_id) {
                $i_job_info = array("id" => $id_id, "key" => $key, "job_name" => $job_name, "is_completed" => $is_completed,
                    "is_quantify" => false, "date_completed" => $comp, "identify_parent_id" => "");
                
                if (isset($iparams["identify_search_type"]) && $iparams["identify_search_type"])
                    $i_job_info["search_type"] = $iparams["identify_search_type"];
                else
                    $i_job_info["search_type"] = "";
                
                if (isset($iparams["identify_ref_db"]) && $iparams["identify_ref_db"])
                    $i_job_info["ref_db"] = $iparams["identify_ref_db"];
                else
                    $i_job_info["ref_db"] = "";
    
                array_push($jobs, $i_job_info);
            }

            if ($parent_id && $id_row["identify_status"] == "NEW") {
                $c_job_info = array("id" => $id_id, "key" => "", "job_name" => "", "is_completed" => false,
                    "is_quantify" => false, "date_completed" => "", "search_type" => "", "ref_db" => "", "identify_parent_id" => $parent_id);
                array_push($jobs, $c_job_info);
                continue;
            } elseif ($parent_id) {
                continue;
            }

            if ($is_completed) {
                $q_sql = "SELECT quantify_id, quantify_identify_id, quantify_time_completed, quantify_status, quantify_params, identify_parent_id, identify_params, identify_key " .
                    "FROM quantify JOIN identify ON quantify_identify_id = identify_id " .
                    "WHERE (quantify_identify_id = $id_id OR identify_parent_id = $id_id) AND quantify_status != '" . __ARCHIVED__ . "'";
                $q_rows = $this->db->query($q_sql);

                foreach ($q_rows as $q_row) {
                    $qparams = global_functions::decode_object($q_row["quantify_params"]);

                    $q_comp_result = self::get_completed_date_label($q_row["quantify_time_completed"], $q_row["quantify_status"]);
                    $q_comp = $q_comp_result[1];
                    $q_is_completed = $q_comp_result[0];
                    $q_id = $q_row["quantify_id"];
                    $job_name = isset($qparams["quantify_job_name"]) ? $qparams["quantify_job_name"] : "";
                    $par_id = "";

                    $mg_ids = explode(",", $qparams["quantify_metagenome_ids"]);
                    if ($q_row["identify_parent_id"]) {
                        $iparams = global_functions::decode_object($q_row["identify_params"]);
                        $q_full_job_name = $iparams["identify_filename"];
                        $q_job_name = $q_full_job_name;
                        $the_id_id = $q_row["quantify_identify_id"];
                        $the_key = $q_row["identify_key"];
                        $par_id = $q_row["identify_parent_id"];
                    } else {
                        $the_id_id = $id_id;
                        $the_key = $key;
                        $q_full_job_name = implode(", ", $mg_ids);
                        if ($job_name) {
                            $q_job_name = $job_name;
                        } elseif (count($mg_ids) > 6) {
                            $q_job_name = implode(", ", array_slice($mg_ids, 0, 5)) . " ...";
                        } else {
                            $q_job_name = $q_full_job_name;
                        }
                    }

                    $q_job_info = array("id" => $the_id_id, "key" => $the_key, "quantify_id" => $q_id, "job_name" => $q_job_name,
                        "is_completed" => $q_is_completed, "is_quantify" => true, "date_completed" => $q_comp,
                        "full_job_name" => $q_full_job_name, "identify_parent_id" => $par_id);

                    if (isset($qparams["quantify_search_type"]) && $qparams["quantify_search_type"])
                        $q_job_info["search_type"] = $qparams["quantify_search_type"];
                    else
                        $q_job_info["search_type"] = "";

                    $q_job_info["ref_db"] = "";
                    array_push($jobs, $q_job_info);
                }
            }
        }

        return $jobs;
    }

    public static function get_quantify_jobs($db, $identify_id) {
        $jobs = array();

        $q_sql = "SELECT quantify_id, quantify_time_completed, quantify_status, quantify_params " .
            "FROM quantify WHERE quantify_identify_id = $identify_id AND quantify_status != '" . __ARCHIVED__ . "'";
        $q_rows = $db->query($q_sql);

        foreach ($q_rows as $q_row) {
            $q_comp_result = self::get_completed_date_label($q_row["quantify_time_completed"], $q_row["quantify_status"]);
            $q_comp = $q_comp_result[1];
            $q_is_completed = $q_comp_result[0];
            $q_id = $q_row["quantify_id"];

            $qparams = global_functions::decode_object($q_row["quantify_params"]);
            $q_job_name = $qparams["quantify_metagenome_ids"];

            array_push($jobs, array("quantify_id" => $q_id, "job_name" => $q_job_name,
                                    "is_completed" => $q_is_completed, "is_quantify" => true, "date_completed" => $q_comp));
        }

        return $jobs;
    }

    // Candidate for refacotring to centralize
    private static function get_completed_date_label($comp, $status) {
        $isCompleted = false;
        if ($status == __FAILED__ || $status == __RUNNING__ || $status == __CANCELLED__) {
            $comp = $status;
        } elseif (!$comp || substr($comp, 0, 4) == "0000" || $status == __NEW__) {
            $comp = "PENDING";
        } else {
            $comp = functions::format_short_date($comp);
            $isCompleted = true;
        }
        return array($isCompleted, $comp);
    }
}


?>
