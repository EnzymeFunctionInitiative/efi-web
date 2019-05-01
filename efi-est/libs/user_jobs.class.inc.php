<?php

require_once(__DIR__ . "/../includes/main.inc.php");
require_once(__DIR__ . "/functions.class.inc.php");
require_once(__DIR__ . "/est_user_jobs_shared.class.inc.php");
require_once(__BASE_DIR__ . "/libs/user_auth.class.inc.php");
require_once(__BASE_DIR__ . "/libs/global_functions.class.inc.php");

class user_jobs extends user_auth {

    private $user_token = "";
    private $user_email = "";
    private $user_groups = array();
    private $is_admin = false;
    private $jobs = array();
    private $training_jobs = array();
    private $analysis_jobs = array();

    public function __construct() {
    }

    public function load_jobs($db, $token) {
        $this->user_token = $token;
        $this->user_email = self::get_email_from_token($db, $token);
        if (!$this->user_email)
            return;

        $this->user_groups = self::get_user_groups($db, $this->user_token);
        array_unshift($this->user_groups, global_settings::get_default_group_name());
        $this->is_admin = self::get_user_admin($db, $this->user_email);

        $this->load_generate_jobs($db);
        $this->load_analysis_jobs($db);
        $this->load_training_jobs($db);
    }

    public static function load_jobs_for_group($db, $group_name, $show_family_names = false) {
        if (!$group_name)
            return array();

        $group_clause = "user_group = '$group_name'";
        $sql = self::get_group_select_statement($group_clause);
        $rows = $db->query($sql);

        $family_lookup_fn = function($family_id) use($db) { return self::lookup_family_name($db, $family_id); };

        $includeAnalysisJobs = false;
        $jobs = self::process_load_generate_rows($db, $rows, $includeAnalysisJobs, false, $family_lookup_fn);
        
        return $jobs;
    }

    // Returns true if the user has exceeded their 24-hr limit
    public static function check_for_job_limit($db, $email) {
        $is_admin = self::get_user_admin($db, $email);
        if ($is_admin)
            return false;

        $limit_period = 24; // hours
        $dt = new DateTime();
        $past_dt = $dt->sub(new DateInterval("PT${limit_period}H"));
        $mysql_date = $past_dt->format("Y-m-d H:i:s");
        
        $sql = "SELECT COUNT(*) AS count FROM generate WHERE generate_time_created >= '$mysql_date' AND generate_email = '$email' AND (generate_status = '" . __RUNNING__ . "' OR generate_status = '" . __NEW__ . "')";
        $results = $db->query($sql);

        $num_job_limit = global_settings::get_num_job_limit();
        if (count($results) && $results[0]["count"] >= $num_job_limit)
            return true;
        else
            return false;
    }

    private static function get_select_statement() {
        $sql = "SELECT generate.generate_id, generate_key, generate_time_completed, generate_status, generate_type, generate_params FROM generate ";
        return $sql;
    }

    private static function get_group_select_statement($group_clause) {
        $group_clause .= " AND";
        $sql = self::get_select_statement() .
            "LEFT OUTER JOIN job_group ON generate.generate_id = job_group.generate_id " .
            "WHERE $group_clause generate_status = 'FINISH' " .
            "ORDER BY generate_status, generate_time_completed DESC";
        return $sql;
    }

    private function load_generate_jobs($db) {
        $email = $this->user_email;
        $expDate = self::get_start_date_window();

        $sql = self::get_select_statement() .
            "WHERE (generate_email = '$email') AND generate_status != 'ARCHIVED' AND " .
            "(generate_time_completed >= '$expDate' OR (generate_time_created >= '$expDate' AND (generate_status = 'NEW' OR generate_status = 'RUNNING' OR generate_status = 'FAILED'))) " .
            "ORDER BY generate_status, generate_time_completed DESC";
        $rows = $db->query($sql);

        $familyLookupFn = function($family_id) {};
        $includeFailedAnalysisJobs = true;
        $includeAnalysisJobs = true;
        $this->jobs = self::process_load_generate_rows2($db, $rows, $includeAnalysisJobs, $includeFailedAnalysisJobs, $familyLookupFn);
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

        $familyLookupFn = function($family_id) {};
        $includeFailedAnalysisJobs = false;
        $includeAnalysisJobs = true;
        $this->training_jobs = self::process_load_generate_rows2($db, $rows, $includeAnalysisJobs, $includeFailedAnalysisJobs, $familyLookupFn);
    }

    private static function process_load_generate_rows2($db, $rows, $includeAnalysisJobs, $includeFailedAnalysisJobs, $familyLookupFn) {
        $jobs = array();

        $map_fn = function ($row) { return $row["generate_id"]; };
        $id_order = array_map($map_fn, $rows);

        // First process all of the color SSN jobs.  This allows us to link them to SSN jobs.
        $child_color_jobs = array();
        $color_jobs = array();
        foreach ($rows as $row) {
            if ($row["generate_type"] != "COLORSSN")
                continue;

            $comp_result = self::get_completed_date_label($row["generate_time_completed"], $row["generate_status"]);
            $job_name = self::build_job_name($row["generate_params"], $row["generate_type"], $familyLookupFn, $row["generate_id"]);
            $comp = $comp_result[1];
            $is_completed = $comp_result[0];
            $id = $row["generate_id"];
            $key = $row["generate_key"];

            $params = global_functions::decode_object($row["generate_params"]);
            if (isset($params["generate_color_ssn_source_id"]) && $params["generate_color_ssn_source_id"]) {
                $aid = $params["generate_color_ssn_source_id"];
                $color_job = array("id" => $id, "key" => $key, "job_name" => $job_name, "is_completed" => $is_completed, "date_completed" => $comp);
                if (isset($child_color_jobs[$aid]))
                    array_push($child_color_jobs[$aid], $color_job);
                else
                    $child_color_jobs[$aid] = array($color_job);
            } else {
                $color_jobs[$id] = array("key" => $key, "job_name" => $job_name, "is_completed" => $is_completed, "date_completed" => $comp);
            }
        }

        $colors_to_remove = array(); // these are the generate_id that will need to be removed from $id_order, since they are now attached to an analysis job
        // Process all non Color SSN jobs.  Link analysis jobs to generate jobs and color SSN jobs to analysis jobs.
        foreach ($rows as $row) {
            if ($row["generate_type"] == "COLORSSN")
                continue;

            $comp_result = self::get_completed_date_label($row["generate_time_completed"], $row["generate_status"]);
            $job_name = self::build_job_name($row["generate_params"], $row["generate_type"], $familyLookupFn);
            $comp = $comp_result[1];
            $is_completed = $comp_result[0];
            $id = $row["generate_id"];
            $key = $row["generate_key"];
            
            $job_data = array("key" => $key, "job_name" => $job_name, "is_completed" => $is_completed, "date_completed" => $comp);

            $analysis_jobs = array();
            if ($is_completed && $includeAnalysisJobs) {
                $sql = "SELECT analysis_id, analysis_time_completed, analysis_status, analysis_name, analysis_evalue, analysis_min_length, analysis_max_length, analysis_filter FROM analysis " .
                    "WHERE analysis_generate_id = $id AND analysis_status != 'ARCHIVED' ";
                if (!$includeFailedAnalysisJobs)
                    $sql .= "AND analysis_status = 'FINISH'";
                $arows = $db->query($sql); // Analysis Rows
    
                foreach ($arows as $arow) {
                    $aid = $arow["analysis_id"];
                    $acomp_result = self::get_completed_date_label($arow["analysis_time_completed"], $arow["analysis_status"]);
                    $acomp = $acomp_result[1];
                    $a_is_completed = $acomp_result[0];
                    //$a_min = $arow["analysis_min_length"] == __MINIMUM__ ? "" : "Min=".$arow["analysis_min_length"];
                    //$a_max = $arow["analysis_max_length"] == __MAXIMUM__ ? "" : "Max=".$arow["analysis_max_length"];
                    //$filter = $arow["analysis_filter"];
                    //$filter = $filter == "eval" ? "AS" : ($filter == "pid" ? "%ID" : ($filter == "bit" ? "BS" : "custom"));
                    ////$a_job_name = "$filter=" . $arow["analysis_evalue"] . " $a_min $a_max Title=<i>" . $arow["analysis_name"] . "</i>";
                    //$a_job_name = "<span class='job-name'>" . $arow["analysis_name"] . "</span><br><span class='job-metadata'>SSN Threshold=" . $arow["analysis_evalue"];
                    //if ($a_min)
                    //    $a_job_name .= " $a_min";
                    //elseif ($a_max)
                    //    $a_job_name .= " $a_max";
                    //$a_job_name .= "</span>";
                    $a_job_name = est_user_jobs_shared::build_analyze_job_name($arow);

                    $a_job = array("analysis_id" => $aid, "job_name" => $a_job_name, "is_completed" => $a_is_completed, "date_completed" => $acomp);
                    if (isset($child_color_jobs[$aid])) {
                        $a_job["color_jobs"] = $child_color_jobs[$aid];
                        foreach ($child_color_jobs[$aid] as $cjob) {
                            $colors_to_remove[$cjob["id"]] = 1;
                        }
                        unset($child_color_jobs[$aid]);
                    }
                    array_push($analysis_jobs, $a_job);
                }
            }

            $job_data["analysis_jobs"] = $analysis_jobs;
            $jobs[$id] = $job_data;
        }

        foreach ($child_color_jobs as $aid => $infos) {
            foreach ($infos as $info) {
                $color_jobs[$info["id"]] = $info;
            }
        }

        // Remove color jobs that have been attached to an analysis job.
        $id_order_new = array();
        for ($i = 0; $i < count($id_order); $i++) {
            if (!isset($colors_to_remove[$id_order[$i]]))
                array_push($id_order_new, $id_order[$i]);
        }

        $retval = array("generate_jobs" => $jobs, "color_jobs" => $color_jobs, "order" => $id_order_new);
        return $retval;
    }

    private static function process_load_generate_rows($db, $rows, $includeAnalysisJobs, $includeFailedAnalysisJobs, $familyLookupFn) {
        $jobs = array();

        foreach ($rows as $row) {
            $compResult = self::get_completed_date_label($row["generate_time_completed"], $row["generate_status"]);
            $jobName = self::build_job_name($row["generate_params"], $row["generate_type"], $familyLookupFn);
            $comp = $compResult[1];
            $isCompleted = $compResult[0];

            $id = $row["generate_id"];
            $key = $row["generate_key"];

            array_push($jobs, array("id" => $id, "key" => $key,
                    "job_name" => $jobName, "is_completed" => $isCompleted, "is_analysis" => false,
                    "date_completed" => $comp, "is_colorssn" => $row["generate_type"] == "COLORSSN"));

            if ($isCompleted && $includeAnalysisJobs) {
                $sql = "SELECT analysis_id, analysis_time_completed, analysis_status, analysis_name, analysis_evalue, analysis_min_length, analysis_max_length, analysis_filter FROM analysis " .
                    "WHERE analysis_generate_id = $id ";
                if (!$includeFailedAnalysisJobs)
                    $sql .= "AND analysis_status = 'FINISH'";
                $arows = $db->query($sql); // Analysis Rows

                foreach ($arows as $arow) {
                    $acompResult = self::get_completed_date_label($arow["analysis_time_completed"], $arow["analysis_status"]);
                    $acomp = $acompResult[1];
                    $aIsCompleted = $acompResult[0];
                    $aMin = $arow["analysis_min_length"] == __MINIMUM__ ? "" : "Min=".$arow["analysis_min_length"];
                    $aMax = $arow["analysis_max_length"] == __MAXIMUM__ ? "" : "Max=".$arow["analysis_max_length"];
                    $filter = $arow["analysis_filter"];
                    $filter = $filter == "eval" ? "AS" : ($filter == "pid" ? "%ID" : ($filter == "bit" ? "BS" : "custom"));

                    $aJobName = "$filter=" . $arow["analysis_evalue"] . " $aMin $aMax Title=<i>" . $arow["analysis_name"] . "</i>";

                    array_push($jobs, array("id" => $id, "key" => $key, "analysis_id" => $arow["analysis_id"],
                            "job_name" => $aJobName,
                            "is_completed" => $aIsCompleted, "is_analysis" => true, "date_completed" => $acomp));
                }
            }
        }

        return $jobs;
    }

    private static function build_job_name($json, $type, $familyLookupFn, $job_id = 0) {
        $data = global_functions::decode_object($json);

        return est_user_jobs_shared::build_job_name($data, $type, $familyLookupFn, $job_id);
    }

    private static function lookup_family_name($db, $family_id) {
        $sql = "SELECT short_name FROM family_info WHERE family = '$family_id'";
        $rows = $db->query($sql);
        if ($rows) {
            return $rows[0]["short_name"];
        } else {
            return "";
        }
    }

    private static function get_completed_date_label($comp, $status) {
        $is_completed = false;
        if ($status == "FAILED") {
            $comp = "FAILED";
        } elseif (!$comp || substr($comp, 0, 4) == "0000" || $status == "RUNNING") {
            $comp = $status;
            if ($comp == "NEW")
                $comp = "PENDING";
        } else {
            $comp = date_format(date_create($comp), "n/j h:i A");
            $is_completed = true;
        }
        return array($is_completed, $comp);
    }

    private function load_analysis_jobs($db) {
        $expDate = self::get_start_date_window();
        $func = function($val) { return "user_group = '$val'"; };
        $email = $this->user_email;
        $group_clause = "";

        $sql = "SELECT analysis_id, analysis_generate_id, generate_key, analysis_time_completed, analysis_status, generate_type FROM analysis " .
            "LEFT JOIN generate ON analysis_generate_id = generate_id " .
            "WHERE (generate_email = '$email' $group_clause) AND " .
            "(analysis_time_completed >= '$expDate' OR (analysis_time_created >= '$expDate' AND (analysis_status = 'NEW' OR analysis_status = 'RUNNING'))) " .
            "ORDER BY analysis_status, analysis_time_completed DESC";
        $rows = $db->query($sql);

        foreach ($rows as $row) {
            $comp = $row["analysis_time_completed"];
            $status = $row["analysis_status"];
            $is_completed = false;
            if ($status == "FAILED") {
                $comp = "FAILED";
            } elseif (!$comp || substr($comp, 0, 4) == "0000") {
                $comp = $row["analysis_status"]; // "RUNNING";
                if ($comp == "NEW")
                    $comp = "PENDING";
            } else {
                $comp = date_format(date_create($comp), "n/j h:i A");
                $is_completed = true;
            }

            $job_name = $row["generate_type"];

            array_push($this->analysis_jobs, array("id" => $row["analysis_generate_id"], "key" => $row["generate_key"],
                    "job_name" => $job_name, "is_completed" => $is_completed, "analysis_id" => $row["analysis_id"],
                    "date_completed" => $comp));
        }
    }

    public function get_cookie() {
        return self::get_cookie_shared($this->user_token);
    }

    public function get_jobs() {
        return $this->jobs;
    }

    public function get_training_jobs() {
        return $this->training_jobs;
    }

    public function get_analysis_jobs() {
        return $this->analysis_jobs;
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

