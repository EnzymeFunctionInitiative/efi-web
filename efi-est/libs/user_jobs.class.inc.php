<?php

require_once "../includes/main.inc.php";
require_once "functions.class.inc.php";
require_once "../../libs/user_auth.class.inc.php";

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
        
        $sql = "SELECT COUNT(*) AS count FROM generate WHERE generate_time_created >= '$mysql_date' AND generate_email = '$email'";
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
            $job_name = self::build_job_name($row["generate_params"], $row["generate_type"], $familyLookupFn);
            $comp = $comp_result[1];
            $is_completed = $comp_result[0];
            $id = $row["generate_id"];
            $key = $row["generate_key"];

            $params = functions::decode_object($row["generate_params"]);
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
                    "WHERE analysis_generate_id = $id ";
                if (!$includeFailedAnalysisJobs)
                    $sql .= "AND analysis_status = 'FINISH'";
                $arows = $db->query($sql); // Analysis Rows
    
                foreach ($arows as $arow) {
                    $aid = $arow["analysis_id"];
                    $acomp_result = self::get_completed_date_label($arow["analysis_time_completed"], $arow["analysis_status"]);
                    $acomp = $acomp_result[1];
                    $a_is_completed = $acomp_result[0];
                    $a_min = $arow["analysis_min_length"] == __MINIMUM__ ? "" : "Min=".$arow["analysis_min_length"];
                    $a_max = $arow["analysis_max_length"] == __MAXIMUM__ ? "" : "Max=".$arow["analysis_max_length"];
                    $filter = $arow["analysis_filter"];
                    $filter = $filter == "eval" ? "AS" : ($filter == "pid" ? "%ID" : ($filter == "bit" ? "BS" : "custom"));
                    $a_job_name = "$filter=" . $arow["analysis_evalue"] . " $a_min $a_max Title=<i>" . $arow["analysis_name"] . "</i>";

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

    private static function get_job_label($type) {
        switch ($type) {
        case "FAMILIES":
            return "Families";
        case "FASTA":
            return "FASTA";
        case "FASTA_ID":
            return "FASTA+Headers";
        case "ACCESSION":
            return "Sequence IDs";
        case "COLORSSN":
            return "Color SSN";
        default:
            return $type;
        }
    }

    private static function get_filename($data, $type) {
        if (array_key_exists("generate_fasta_file", $data)) {
            $file = $data["generate_fasta_file"];
            if ($file) {
                return str_replace("_", " ", $file);
            } elseif ($type == "FASTA" || $type == "FASTA_ID" || $type == "ACCESSION") {
                return "Text Input";
            } else {
                return "";
            }
        } else {
            return "";
        }
    }

    private static function get_families($data, $type, $familyLookupFn) {
        $famStr = "";
        if (array_key_exists("generate_families", $data)) {
            $fams = $data["generate_families"];
            if ($fams) {
                $famParts = explode(",", $fams);
                if (count($famParts) > 2)
                    $famParts = array($famParts[0], $famParts[1], "...");
                $fams = implode(", ", array_map($familyLookupFn, $famParts));
                $famStr = $fams;
            }
        }
        return $famStr;
    }

    private static function get_evalue($data) {
        $evalueStr = "";
        if (array_key_exists("generate_evalue", $data)) {
            $evalue = $data["generate_evalue"];
            if ($evalue && $evalue != functions::get_evalue())
                $evalueStr = "E-value=" . $evalue;
        }
        return $evalueStr;
    }

    private static function get_fraction($data) {
        $fractionStr = "";
        if (array_key_exists("generate_fraction", $data)) {
            $fraction = $data["generate_fraction"];
            if ($fraction && $fraction != functions::get_fraction())
                $fractionStr = "Fraction=" . $fraction;
        }
        return $fractionStr;
    }

    private static function get_uniref_version($data) {
        $unirefStr = "";
        if (array_key_exists("generate_uniref", $data)) {
            if ($data["generate_uniref"])
                $unirefStr = "UniRef " . $data["generate_uniref"];
        }
        return $unirefStr;
    }

    private static function get_domain($data) {
        $domainStr = "";
        if (array_key_exists("generate_domain", $data)) {
            if ($data["generate_domain"])
                $domainStr = "Domain=on";
        }
        return $domainStr;
    }

    private static function get_sequence($data) {
        $seqStr = "";
        if (array_key_exists("generate_blast", $data) && $data["generate_blast"]) {
            $seq = $data["generate_blast"];
            $seqLabel = substr($seq, 0, 20);
            $seqTitle = "";
            $pos = 0;
            while ($pos < strlen($seq)) {
                $seqTitle .= substr($seq, $pos, 27) . "\n";
                $pos += 40;
            }
            $seqStr = "Sequence: <span title='$seqTitle'>$seqLabel...</span>";
        }
        return $seqStr;
    }

    private static function get_blast_evalue($data) {
        $info = "";
        if (array_key_exists("generate_blast_evalue", $data) && $data["generate_blast_evalue"]) {
            $info = "e-value=" . $data["generate_blast_evalue"];
        }
        return $info;
    }

    private static function get_max_blast_hits($data) {
        $info = "";
        if (array_key_exists("generate_blast_max_sequence", $data) && $data["generate_blast_max_sequence"]) {
            $info = "max hits=" . $data["generate_blast_max_sequence"];
        }
        return $info;
    }

    private static function build_job_name($json, $type, $familyLookupFn) {
        $data = functions::decode_object($json);

        $newFamilyLookupFn = function($family_id) use($familyLookupFn) {
            $fam_name = $familyLookupFn($family_id);
            return $fam_name ? "$family_id-$fam_name" : $family_id;
        };

        $fileName = self::get_filename($data, $type);
        $families = self::get_families($data, $type, $newFamilyLookupFn);
        $evalue = self::get_evalue($data);
        $fraction = self::get_fraction($data);
        $uniref = self::get_uniref_version($data);
        $domain = self::get_domain($data);
        $sequence = self::get_sequence($data);
        $blastEvalue = self::get_blast_evalue($data);
        $maxHits = self::get_max_blast_hits($data);
        $jobNameField = isset($data["generate_job_name"]) ? $data["generate_job_name"] : "";

        $info = array();
        if ($fileName) array_push($info, $fileName);
        if ($families) array_push($info, $families);
        if ($evalue) array_push($info, $evalue);
        if ($fraction) array_push($info, $fraction);
        if ($uniref) array_push($info, $uniref);
        if ($domain) array_push($info, $domain);
        if ($sequence) array_push($info, $sequence);
        if ($blastEvalue) array_push($info, $blastEvalue);
        if ($maxHits) array_push($info, $maxHits);
        
        $job_name = self::get_job_label($type);

        $jobInfo = "";
        if ($jobNameField) {
            $jobInfo = $jobNameField;
        } else {
            $jobInfo = implode("; ", $info);
        }

        if ($jobInfo) {
            $job_name .= " ($jobInfo)";
        }

        return $job_name;
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

