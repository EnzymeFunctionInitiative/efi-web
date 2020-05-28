<?php

require_once("global_settings.class.inc.php");
require_once("global_functions.class.inc.php");
require_once("PasswordHash.php");

class group_status {
    const Active = "ACTIVE";
    const Inactive = "INACTIVE";
}

class user_auth {

    const USER_TOKEN_NAME = "efi_token";
    const EXPIRATION_SECONDS = 2592000; // 30 days

    public static function has_token_cookie() {
        return isset($_COOKIE[user_auth::USER_TOKEN_NAME]);
    }

    public static function get_user_token() {
        return $_COOKIE[user_auth::USER_TOKEN_NAME];
    }

    public static function get_logout_cookie() {
        return self::get_cookie_shared_date("*", "-1101987");
    }

    public static function get_user_table() {
        $userTable = __MYSQL_AUTH_DATABASE__;
        if ($userTable)
            $userTable = "`$userTable`.";
        $userTable .= "`user_token`";
        return $userTable;
    }

    public static function get_master_group_table() {
        $userTable = __MYSQL_AUTH_DATABASE__;
        if ($userTable)
            $userTable = "`$userTable`.";
        $userTable .= "`groups`";
        return $userTable;
    }

    public static function get_user_group_table() {
        $userTable = __MYSQL_AUTH_DATABASE__;
        if ($userTable)
            $userTable = "`$userTable`.";
        $userTable .= "`user_group`";
        return $userTable;
    }

    public static function validate_user($db, $email, $password) {
        $output = array('valid' => false, 'cookie' => "");

        $userTable = self::get_user_table();
        $sql = "SELECT * FROM $userTable WHERE user_email = '$email' AND user_action = 'ACTIVE'";
        $result = $db->query($sql);
        if (!$result) // User doesn't exist
            return $output;
        $result = $result[0];

        $output['valid'] = self::pass_verify($password, $result['user_password']);
        if ($output['valid']) {
            $output['cookie'] = self::get_cookie_shared($result['user_id']);
        }

        return $output;
    }

    public static function check_reset_token($db, $token) {
        $userTable = self::get_user_table();
        $sql = "SELECT * FROM $userTable WHERE user_id = '$token'";
        $result = $db->query($sql);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public static function check_reset_email($db, $email) {
        $userTable = self::get_user_table();
        $sql = "SELECT * FROM $userTable WHERE user_email = '$email'";
        $result = $db->query($sql);
        if ($result) {
            return $result[0]["user_id"];
        } else {
            return false;
        }
    }

    public static function create_user($db, $email, $password, $listservSignup) {
        $userTable = self::get_user_table();
        $sql = "SELECT user_id FROM $userTable WHERE user_email = '$email'";
        $result = $db->query($sql);
        if ($result) // User already exists
            return false;

        $token = self::generate_key();
        $hash = self::pass_crypt($password);
        $sql = "INSERT INTO $userTable (user_id, user_email, user_password, user_action) VALUES ('$token', '$email', '$hash', 'PENDING')";
        $result = $db->non_select_query($sql);
        if ($result)
            return $token;
        else
            return false;
    }

    public static function change_password($db, $email, $oldPassword, $password) {
        $userTable = self::get_user_table();
        $sql = "SELECT user_password FROM $userTable WHERE user_email = '$email'";
        $result = $db->query($sql);
        if (!$result) // User doesn't exist
            return false;

        $hash = $result[0]['user_password'];
        if (!self::pass_verify($oldPassword, $hash)) // Old password doesn't match
            return false;

        $hash = self::pass_crypt($password);
        $sql = "UPDATE $userTable SET user_action = 'ACTIVE', user_password = '$hash' WHERE user_email = '$email'";
        $result = $db->non_select_query($sql);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public static function reset_password($db, $userToken, $password) {
        $userTable = self::get_user_table();
        $sql = "SELECT * FROM $userTable WHERE user_id = '$userToken'";
        $result = $db->query($sql);
        if (!$result) // User doesn't exist
            return false;

        $hash = self::pass_crypt($password);
        $sql = "UPDATE $userTable SET user_action = 'ACTIVE', user_password = '$hash' WHERE user_id = '$userToken'";
        $result = $db->non_select_query($sql);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    private static function pass_crypt($password) {
        $hasher = self::get_hasher();
        $hash = $hasher->HashPassword($password);
        unset($hasher);
        return $hash;
    }

    private static function pass_verify($password, $hash) {
        if (!$hash)
            return false;
        $hasher = self::get_hasher();
        $ok = $hasher->CheckPassword($password, $hash);
        unset($hasher);
        return $ok;
    }

    private static function get_hasher() {
        $hash_cost_log2 = 8;
        $hash_portable = false;
        $hasher = new PasswordHash($hash_cost_log2, $hash_portable);
        return $hasher;
    }

    public static function validate_new_account($db, $token) {
        $userTable = self::get_user_table();
        $sql = "SELECT * FROM $userTable WHERE user_id = '$token' AND user_action = 'PENDING'";
        $result = $db->query($sql);
        if ($result) { // User alread added but hasn't been validated.
            $sql = "UPDATE $userTable SET user_action = 'ACTIVE' WHERE user_id = '$token'";
            $result = $db->non_select_query($sql);
            if ($result) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    } 

    public static function get_cookie_shared($user_token) {
        $maxAge = 30 * 86400; // 30 days
        return self::get_cookie_shared_date($user_token, $maxAge);
    }

    public static function get_cookie_shared_date($user_token, $maxAge) {
        $dom = parse_url(global_settings::get_web_root(), PHP_URL_HOST);
        $tokenField = user_auth::USER_TOKEN_NAME;
        $token = $user_token;
        return "$tokenField=$token;max-age=$maxAge;Path=/";
    }

    public static function get_start_date_window() {
        $numDays = global_settings::get_retention_days();
        return global_functions::get_prior_date($numDays);
//        $dt = new DateTime();
//        $pastDt = $dt->sub(new DateInterval("P${numDays}D"));
//        $mysqlDate = $pastDt->format("Y-m-d");
//        return $mysqlDate;
    }
    
    public static function generate_key() {
        $key = uniqid(rand(), true);
        $hash = sha1($key);
        return $hash;
    }

    public static function get_email_from_token($db, $token) {
        $userTable = self::get_user_table();

        $sql = "SELECT user_email FROM $userTable WHERE user_id='$token'";
        $row = $db->query($sql);
        if ($row)
            return $row[0]["user_email"];
        else
            return "";
    }

    public static function get_token_from_email($db, $email) {
        $userTable = self::get_user_table();

        $sql = "SELECT user_id FROM $userTable WHERE user_email = '$email'";
        $row = $db->query($sql);
        if ($row)
            return $row[0]["user_id"];
        else
            return "";
    }

    public static function get_user_admin($db, $email) {
        $userTable = self::get_user_table();

        $sql = "SELECT user_admin FROM $userTable WHERE user_email='$email'";
        $row = $db->query($sql);
        if ($row)
            return $row[0]["user_admin"] == 1;
        else
            return false;
    }

    public static function get_admin_users($db) {
        $userTable = self::get_user_table();
        $users = array();
        $sql = "SELECT user_email FROM $userTable WHERE user_admin = 1";
        $results = $db->query($sql);
        foreach ($results as $row) {
            $users[$row["user_email"]] = 1;
        }
        return $users;
    }

    public static function get_user_groups($db, $user_id) {
        $userTable = self::get_user_group_table();
        $mtable = self::get_master_group_table();

        $activeStatus = group_status::Active;

        $sql = "SELECT $userTable.group_name FROM $userTable " .
            "JOIN $mtable ON $userTable.group_name = $mtable.group_name " .
            "WHERE user_id='$user_id' AND " .
            "$mtable.group_status='$activeStatus' AND " .
            "(CURRENT_TIMESTAMP >= $mtable.group_time_open OR $mtable.group_time_open IS NULL) AND " .
            "(CURRENT_TIMESTAMP <= $mtable.group_time_closed OR $mtable.group_time_closed IS NULL)";
        $rows = $db->query($sql);

        $result = array();
        foreach ($rows as $row) {
            array_push($result, $row["group_name"]);
        }
        return $result;
    }
}

?>
