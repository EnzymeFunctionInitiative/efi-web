<?php

require_once "../includes/main.inc.php";
require_once "functions.class.inc.php";
require_once "../../libs/user_auth.class.inc.php";

class user_jobs extends user_auth {

//    const USER_TOKEN_NAME = "efi_token";
//    const EXPIRATION_SECONDS = 2592000; // 30 days

    private $user_token;
    private $user_email = "";
    private $jobs;
    private $analysis_jobs;

//    public static function has_token_cookie() {
//        return isset($_COOKIE[user_jobs::USER_TOKEN_NAME]);
//    }
//
//    public static function get_user_token() {
//        return $_COOKIE[user_jobs::USER_TOKEN_NAME];
//    }

    public function __construct() {
        $this->jobs = array();
        $this->analysis_jobs = array();
    }

//    private static function get_user_table() {
//        $userTable = __MYSQL_AUTH_DATABASE__;
//        if ($userTable)
//            $userTable .= ".";
//        $userTable .= "user_token";
//        return $userTable;
//    }
//
//    public static function validate_user($db, $email, $password) {
//        $output = array('valid' => false, 'cookie' => "");
//
//        $userTable = self::get_user_table();
//        $sql = "SELECT * FROM $userTable WHERE user_email = '$email' AND user_action = 'ACTIVE'";
//        $result = $db->query($sql);
//        if (!$result) // User doesn't exist
//            return $output;
//        $result = $result[0];
//
//        $output['valid'] = self::pass_verify($password, $result['user_password']);
//        if ($output['valid']) {
//            $output['cookie'] = self::get_cookie_shared($result['user_id']);
//        }
//
//        return $output;
//    }
//
//    public static function check_reset_token($db, $token) {
//        $userTable = self::get_user_table();
//        $sql = "SELECT * FROM $userTable WHERE user_id = '$token'";
//        $result = $db->query($sql);
//        if ($result) {
//            return true;
//        } else {
//            return false;
//        }
//    }
//
//    public static function check_reset_email($db, $email) {
//        $userTable = self::get_user_table();
//        $sql = "SELECT * FROM $userTable WHERE user_email = '$email'";
//        $result = $db->query($sql);
//        if ($result) {
//            return $result[0]["user_id"];
//        } else {
//            return false;
//        }
//    }
//
//    public static function create_user($db, $email, $password, $listservSignup) {
//        $userTable = self::get_user_table();
//        $sql = "SELECT user_id FROM $userTable WHERE user_email = '$email'";
//        $result = $db->query($sql);
//        if ($result) // User already exists
//            return false;
//
//        $token = functions::generate_key();
//        $hash = self::pass_crypt($password);
//        $sql = "INSERT INTO $userTable (user_id, user_email, user_password, user_action) VALUES ('$token', '$email', '$hash', 'PENDING')";
//        $result = $db->non_select_query($sql);
//        if ($result)
//            return $token;
//        else
//            return false;
//    }
//
//    public static function change_password($db, $email, $oldPassword, $password) {
//        $userTable = self::get_user_table();
//        $sql = "SELECT user_password FROM $userTable WHERE user_email = '$email'";
//        $result = $db->query($sql);
//        if (!$result) // User doesn't exist
//            return false;
//
//        $hash = $result[0]['user_password'];
//        if (!self::pass_verify($oldPassword, $hash)) // Old password doesn't match
//            return false;
//
//        $hash = self::pass_crypt($password);
//        $sql = "UPDATE $userTable SET user_action = 'ACTIVE', user_password = '$hash' WHERE user_email = '$email'";
//        $result = $db->non_select_query($sql);
//        if ($result) {
//            return true;
//        } else {
//            return false;
//        }
//    }
//
//    public static function reset_password($db, $userToken, $password) {
//        $userTable = self::get_user_table();
//        $sql = "SELECT * FROM $userTable WHERE user_id = '$userToken'";
//        $result = $db->query($sql);
//        if (!$result) // User doesn't exist
//            return false;
//
//        $hash = self::pass_crypt($password);
//        $sql = "UPDATE $userTable SET user_action = 'ACTIVE', user_password = '$hash' WHERE user_id = '$userToken'";
//        $result = $db->non_select_query($sql);
//        if ($result) {
//            return true;
//        } else {
//            return false;
//        }
//    }
//
//    private static function pass_crypt($password) {
//        $hasher = self::get_hasher();
//        $hash = $hasher->HashPassword($password);
//        unset($hasher);
//        return $hash;
//    }
//
//    private static function pass_verify($password, $hash) {
//        if (!$hash)
//            return false;
//        $hasher = self::get_hasher();
//        $ok = $hasher->CheckPassword($password, $hash);
//        unset($hasher);
//        return $ok;
//    }
//
//    private static function get_hasher() {
//        $hash_cost_log2 = 8;
//        $hash_portable = false;
//        $hasher = new PasswordHash($hash_cost_log2, $hash_portable);
//        return $hasher;
//    }
//
//    public static function validate_new_account($db, $token) {
//        $userTable = self::get_user_table();
//        $sql = "SELECT * FROM $userTable WHERE user_id = '$token' AND user_action = 'PENDING'";
//        $result = $db->query($sql);
//        if ($result) { // User alread added but hasn't been validated.
//            $sql = "UPDATE $userTable SET user_action = 'ACTIVE' WHERE user_id = '$token'";
//            $result = $db->non_select_query($sql);
//            if ($result) {
//                return true;
//            } else {
//                return false;
//            }
//        } else {
//            return false;
//        }
//    } 

    public function load_jobs($db, $token) {
//        $userTable = self::get_user_table();
//        
//        $sql = "SELECT user_email FROM $userTable WHERE user_id='" . $this->user_token . "'";
//        $row = $db->query($sql);
//        if (!$row)
//            return;
//
//        $this->user_email = $row[0]["user_email"];
        $this->user_token = $token;
        $this->user_email = self::get_email_from_token($db, $token);
        if (!$this->user_email)
            return;

        $this->load_generate_jobs($db);
        $this->load_analysis_jobs($db);
    }

    private function load_generate_jobs($db) {
        $expDate = self::get_start_date_window();
        $sql = "SELECT generate_id, generate_key, generate_time_completed, generate_status, generate_type, generate_params FROM generate " .
            "WHERE generate_email='" . $this->user_email . "' AND " .
            "(generate_time_completed >= '$expDate' OR (generate_time_created >= '$expDate' AND (generate_status = 'NEW' OR generate_status = 'RUNNING'))) " .
            "ORDER BY generate_status, generate_time_completed DESC";
        $rows = $db->query($sql);

        foreach ($rows as $row) {
            $compResult = $this->get_completed_date_label($row["generate_time_completed"], $row["generate_status"]);
            $jobName = $this->build_job_name($row["generate_params"], $row["generate_type"]);
            $comp = $compResult[1];
            $isCompleted = $compResult[0];
//            $comp = $row["generate_time_completed"];
//            $status = $row["generate_status"];
//            $isCompleted = false;
//            if ($status == "FAILED") {
//                $comp = "FAILED";
//            } elseif (!$comp || substr($comp, 0, 4) == "0000") {
//                $comp = $row["generate_status"]; // "RUNNING";
//                if ($comp == "NEW")
//                    $comp = "PENDING";
//            } else {
//                $comp = date_format(date_create($comp), "n/j h:i A");
//                $isCompleted = true;
//            }

            $id = $row["generate_id"];
            $key = $row["generate_key"];

            array_push($this->jobs, array("id" => $id, "key" => $key,
                    "job_name" => $jobName, "is_completed" => $isCompleted, "is_analysis" => false,
                    "date_completed" => $comp));

            if ($isCompleted) {
                $sql = "SELECT analysis_id, analysis_time_completed, analysis_status, analysis_name, analysis_evalue FROM analysis " .
                    "WHERE analysis_generate_id = $id";
                $arows = $db->query($sql); // Analysis Rows

                foreach ($arows as $arow) {
                    $acompResult = $this->get_completed_date_label($arow["analysis_time_completed"], $arow["analysis_status"]);
                    $acomp = $acompResult[1];
                    $aIsCompleted = $acompResult[0];
                    $aJobName = "AS=" . $arow["analysis_evalue"] . " " . $arow["analysis_name"];

                    array_push($this->jobs, array("id" => $id, "key" => $key, "analysis_id" => $arow["analysis_id"],
                            "job_name" => $aJobName,
                            "is_completed" => $aIsCompleted, "is_analysis" => true, "date_completed" => $acomp));
                }
            }
        }
    }

    private function get_job_label($type) {
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

    private function get_filename($data, $type) {
        if (array_key_exists("generate_fasta_file", $data)) {
            $file = $data["generate_fasta_file"];
            if ($file) {
                return $file;
            } elseif ($type == "FASTA" || $type == "FASTA_ID" || $type == "ACCESSION") {
                return "Text Input";
            } else {
                return "";
            }
        } else {
            return "";
        }
    }

    private function get_families($data, $type) {
        $famStr = "";
        if (array_key_exists("generate_families", $data)) {
            $fams = $data["generate_families"];
            if ($fams) {
                $famParts = explode(",", $fams);
                if (count($famParts) > 2)
                    $fams = $famParts[0] . ", " . $famParts[1] . " ...";
                else
                    $fams = implode(", ", $famParts);
                $famStr = $fams;
            }
        }
        return $famStr;
    }

    private function get_evalue($data) {
        $evalueStr = "";
        if (array_key_exists("generate_evalue", $data)) {
            $evalue = $data["generate_evalue"];
            if ($evalue && $evalue != functions::get_evalue())
                $evalueStr = "E-value=" . $evalue;
        }
        return $evalueStr;
    }

    private function get_fraction($data) {
        $fractionStr = "";
        if (array_key_exists("generate_fraction", $data)) {
            $fraction = $data["generate_fraction"];
            if ($fraction && $fraction != functions::get_fraction())
                $fractionStr = "Fraction=" . $fraction;
        }
        return $fractionStr;
    }

    private function get_uniref_version($data) {
        $unirefStr = "";
        if (array_key_exists("generate_uniref", $data)) {
            if ($data["generate_uniref"])
                $unirefStr = "UniRef " . $data["generate_uniref"];
        }
        return $unirefStr;
    }

    private function build_job_name($json, $type) {
        $data = functions::decode_object($json);
        
        $fileName = $this->get_filename($data, $type);
        $families = $this->get_families($data, $type);
        $evalue = $this->get_evalue($data);
        $fraction = $this->get_fraction($data);
        $uniref = $this->get_uniref_version($data);

        $info = array();
        if ($fileName) array_push($info, $fileName);
        if ($families) array_push($info, $families);
        if ($evalue) array_push($info, $evalue);
        if ($fraction) array_push($info, $fraction);
        if ($uniref) array_push($info, $uniref);
        
        $jobName = $this->get_job_label($type);
        
        $jobInfo = implode("; ", $info);

        if ($jobInfo) {
            $jobName .= " ($jobInfo)";
        }
        return $jobName;
    }

    private function get_completed_date_label($comp, $status) {
        $isCompleted = false;
        if ($status == "FAILED") {
            $comp = "FAILED";
        } elseif (!$comp || substr($comp, 0, 4) == "0000") {
            $comp = $status;
            if ($comp == "NEW")
                $comp = "PENDING";
        } else {
            $comp = date_format(date_create($comp), "n/j h:i A");
            $isCompleted = true;
        }
        return array($isCompleted, $comp);
    }

    private function load_analysis_jobs($db) {
        $expDate = self::get_start_date_window();
        $sql = "SELECT analysis_id, analysis_generate_id, generate_key, analysis_time_completed, analysis_status, generate_type FROM analysis " .
            "LEFT JOIN generate ON analysis_generate_id = generate_id " .
            "WHERE generate_email='" . $this->user_email . "' AND " .
            "(analysis_time_completed >= '$expDate' OR (analysis_time_created >= '$expDate' AND (analysis_status = 'NEW' OR analysis_status = 'RUNNING'))) " .
            "ORDER BY analysis_status, analysis_time_completed DESC";
        $rows = $db->query($sql);

        foreach ($rows as $row) {
            $comp = $row["analysis_time_completed"];
            $status = $row["analysis_status"];
            $isCompleted = false;
            if ($status == "FAILED") {
                $comp = "FAILED";
            } elseif (!$comp || substr($comp, 0, 4) == "0000") {
                $comp = $row["analysis_status"]; // "RUNNING";
                if ($comp == "NEW")
                    $comp = "PENDING";
            } else {
                $comp = date_format(date_create($comp), "n/j h:i A");
                $isCompleted = true;
            }

            $jobName = $row["generate_type"];

            array_push($this->analysis_jobs, array("id" => $row["analysis_generate_id"], "key" => $row["generate_key"],
                    "job_name" => $jobName, "is_completed" => $isCompleted, "analysis_id" => $row["analysis_id"],
                    "date_completed" => $comp));
        }
    }

//    public function save_user($db, $email) {
//        $userTable = self::get_user_table();
//        $this->user_email = $email;
//
//        $sql = "SELECT user_id, user_email FROM $userTable WHERE user_email='" . $this->user_email . "'";
//        $rows = $db->query($sql);
//
//        $isUpdate = false;
//        if ($rows && count($rows) > 0) {
//            $isUpdate = true;
//            $this->user_token = $rows[0]["user_id"];
//        } else {
//            $this->user_token = functions::generate_key();
//        }
//
//        $insert_array = array("user_id" => $this->user_token, "user_email" => $this->user_email);
//        if (!$isUpdate) {
//            $db->build_insert("user_token", $insert_array);
//        }
//
//        return true;
//    }

    public function get_cookie() {
        return self::get_cookie_shared($this->user_token);
    }

//    public static function get_cookie_shared($user_token) {
//        $dom = parse_url(functions::get_web_root(), PHP_URL_HOST);
//        $maxAge = 30 * 86400; // 30 days
//        $tokenField = user_jobs::USER_TOKEN_NAME;
//        $token = $user_token;
//        return "$tokenField=$token;max-age=$maxAge;Path=/";
//    }

//    public function get_start_date_window() {
//        $numDays = functions::get_retention_days();
//        $dt = new DateTime();
//        $pastDt = $dt->sub(new DateInterval("P${numDays}D"));
//        $mysqlDate = $pastDt->format("Y-m-d");
//        return $mysqlDate;
//    }

    public function get_jobs() {
        return $this->jobs;
    }

    public function get_analysis_jobs() {
        return $this->analysis_jobs;
    }

    public function get_email() {
        return $this->user_email;
    }
}

?>

