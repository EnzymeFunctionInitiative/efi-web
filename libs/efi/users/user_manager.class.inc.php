<?php
namespace efi\users;

require_once(__DIR__."/../../../init.php");

use \efi\user_auth;


class user_manager {

    const CREATE_USER_OK = 1;
    const CREATE_USER_EXISTS = 2;
    const CREATE_USER_INVALID_GROUP = 4;
    const CREATE_USER_INVALID_EMAIL = 8;
    const CREATE_USER_FAILED = 0;

    const DEFAULT_GROUP = "DEFAULT";

    private $db = "";
    private $users = array();
    private $groups = array();

    public function __construct($db) {
        $this->db = $db;
        $this->load_users();
        $this->load_groups();
    }

    public static function create_group($db, $name, $open, $closed) {
        $table = user_auth::get_master_group_table();

        if ($db->query("SELECT * FROM $table WHERE group_name = \"$name\" LIMIT 1"))
            return false;

        $status = group_status::Active;
        $values = array("group_name" => $name,
            "group_status" => $status);

        if ($open) {
            $dt = date_create($open);
            if ($dt !== FALSE)
                $values["group_time_open"] = self::mysql_date($dt);
        }
        if ($closed) {
            $dt = date_create($closed);
            if ($dt !== FALSE)
                $values["group_time_closed"] = self::mysql_date($dt);
        }

        $result = $db->build_insert($table, $values);
        return true;
    }

    public static function toggle_group_status($db, $group) {
        if (preg_match("/[^A-Za-z0-9\-_]/", $group)) {
            return false;
        }

        $table = user_auth::get_master_group_table();

        $result = $db->query("SELECT * FROM $table WHERE group_name = \"$group\" LIMIT 1");
        if (!$result)
            return false;

        $status = $result[0]["group_status"];

        if ($status == group_status::Active)
            $status = group_status::Inactive;
        else
            $status = group_status::Active;

        $sql = "UPDATE $table SET group_status = \"$status\" WHERE group_name=\"$group\"";
        $db->non_select_query($sql);

        return true;
    }

    public static function create_user($db, $email, $pass, $group) {
        if (preg_match("/[^A-Za-z0-9\-_]/", $group)) {
            return user_manager::CREATE_USER_INVALID_GROUP;
        }
        if (preg_match("/[^A-Za-z0-9\-_@\.\+]/", $email)) {
            return user_manager::CREATE_USER_INVALID_EMAIL;
        }

        $send_email = true;
        $result = self::do_create_user($db, $email, $pass, $group, $send_email);

        //TODO: send email

        return $result;
    }

    // Inputs are already validated.
    private static function do_create_user($db, $email, $pass, $group, $send_email = false) {
        $result = user_manager::CREATE_USER_OK;

        $utable = user_auth::get_user_table();
        $gmtable = user_auth::get_master_group_table();
        $gutable = user_auth::get_user_group_table();

        $user_id_result = $db->query("SELECT user_id FROM $utable WHERE user_email = '$email'");
        $user_id = "";
        if (!$user_id_result) {
            $user_id = user_auth::create_user($db, $email, $pass, false);
        } else {
            $user_id = $user_id_result[0]["user_id"];
            $result = user_manager::CREATE_USER_EXISTS;;
        }

        if ($group && $group != user_manager::DEFAULT_GROUP) {
            $group_result = $db->query("SELECT group_name FROM $gmtable WHERE group_name = \"$group\"");
            if ($group_result) { // group exists
                $sql = "INSERT INTO $gutable (group_name, user_id) VALUES (\"$group\", \"$user_id\")";
                $db->non_select_query($sql);
            } else {
                $result |= user_manager::CREATE_USER_INVALID_GROUP;
            }
        }

        if ($send_email && $result === user_manager::CREATE_USER_OK) {
            $set_password = true;
            functions::send_confirmation_email($email, $user_id, $set_password);
        }

        return $result;
    }

    // Can give multiple users to this function.  If the user already exists, it is ignored.
    // If there are two columns, then the second column is assumed to be the group that the
    // user should be added to.
    public static function bulk_create_update_user($db, $user_list) {
        $results = array();
        
        foreach ($user_list as $user_info) {
            $user_email = "";
            $user_group = "";
            if (count($user_info) > 1) {
                $user_email = $user_info[0];
                $user_group = $user_info[1];
            }

            $send_email = true;
            $result = self::do_create_user($db, $user_email, "", $user_group, $send_email);
            //TODO: log result and continue if it's not OK.
            array_push($results, $result);
        }

        return $results;
    }

    public static function reset_passwords($db, $user_list) {
        $warn_user = false;
        foreach ($user_list as $user_id) {
            if (preg_match("/[^A-Za-z0-9]/", $user_id)) // Skip invalid user IDs
                continue;

            $email = user_auth::get_email_from_token($db, $user_id);
            functions::send_reset_email($email, $user_id, $warn_user);
        }
        return true;
    }

    public static function delete_users($db, $user_list) {
        $utable = user_auth::get_user_table();
        $gutable = user_auth::get_user_group_table();

        foreach ($user_list as $user_id) {
            if (preg_match("/[^A-Za-z0-9]/", $user_id)) // Skip invalid user IDs
                continue;

            $sql = "DELETE FROM $gutable WHERE user_id = \"$user_id\"";
            $db->non_select_query($sql);

            $sql = "DELETE FROM $utable WHERE user_id = \"$user_id\"";
            $db->non_select_query($sql);
        }
        return true;
    }

    public static function update_user_group($db, $user_list, $group, $remove = false) {
        if (preg_match("/[^A-Za-z0-9\-_]/", $group)) {
            return false;
        }

        if ($group == user_manager::DEFAULT_GROUP) {
            return true;
        }

        $gmtable = user_auth::get_master_group_table();
        $gutable = user_auth::get_user_group_table();

        $sql = "SELECT group_name FROM $gmtable WHERE group_name = \"$group\"";
        $group_result = $db->query($sql);
        if (!$group_result) {
            return false; // Group doesn't exist
        }

        foreach ($user_list as $user_id) {
            if (preg_match("/[^A-Za-z0-9]/", $user_id)) // Skip invalid user IDs
                continue;
            if ($remove)
                $sql = "DELETE FROM user_group WHERE user_id = \"$user_id\" AND group_name = \"$group\"";
            else
                $sql = "INSERT INTO user_group (group_name, user_id) VALUES (\"$group\", \"$user_id\")";
            $db->non_select_query($sql);
        }

        return true;
    }

    public static function update_admin_status($db, $user_ids, $is_admin) {
        if (! is_array($user_ids)) {
            return false;
        }

        $user_table = user_auth::get_user_table();
        $admin_flag = $is_admin ? 1 : 0;

        foreach ($user_ids as $user_id) {
            $sql = "UPDATE $user_table SET user_admin = $admin_flag WHERE user_id='$user_id'";
            print($sql);
            $db->non_select_query($sql);
        }

        return true;
    }

    private function load_users() {
        $table = user_auth::get_user_table();
        $sql = "SELECT * FROM $table";
        $results = $this->db->query($sql);

        foreach ($results as $row) {
            $user = array();
            $user["id"] = $row["user_id"];
            $user["email"] = $row["user_email"];
//            $user["group"] = $row["user_group"];
            $user["admin"] = $row["user_admin"];
            $user["status"] = $row["user_action"];
            $user["group"] = array();
            $this->users[$user["id"]] = $user;
        }
    }

    // Call this after load_users()
    private function load_groups() {
        $table = user_auth::get_user_group_table();
        $sql = "SELECT * FROM $table";
        $results = $this->db->query($sql);

        foreach ($results as $row) {
            $user_id = $row["user_id"];
            if (isset($this->users[$user_id]) && $row["group_name"]) {
                array_push($this->users[$user_id]["group"], $row["group_name"]);
            }
        }

        $table = user_auth::get_master_group_table();
        $sql = "SELECT * FROM $table";
        $results = $this->db->query($sql);

        foreach ($results as $row) {
            $group = array();
            $group["name"] = $row["group_name"];
            if ($row["group_time_open"])
                $group["time_open"] = self::format_short_date($row["group_time_open"]);
            else
                $group["time_open"] = "";
            if ($row["group_time_closed"])
                $group["time_closed"] = self::format_short_date($row["group_time_closed"]);
            else
                $group["time_closed"] = "";
            $group["status"] = $row["group_status"];
            $this->groups[$row["group_name"]] = $group;
        }
    }

    public function get_user_ids() {
        $ids = array();
        foreach ($this->users as $user) {
            array_push($ids, $user["id"]);
        }
        return $ids;
    }

    public function get_user($user_id) {
        if (isset($this->users[$user_id])) {
            return $this->users[$user_id];
        } else {
            return array("id" => "", "email" => "", "group" => array(), "admin" => "", "status" => "");
        }
    }

    public function get_group_names() {
        //$names = array(user_manager::DEFAULT_GROUP);
        $names = array();
        foreach ($this->groups as $group) {
            array_push($names, $group["name"]);
        }
        return $names;
    }

    public function get_group($name) {
        if (isset($this->groups[$name])) {
            return $this->groups[$name];
        } else {
            return array("name" => "", "time_open" => "", "time_closed" => "", "status" => "");
        }
    }


    public function get_user_stats($db_name, $table_name) {
        $stats = array();

        $email_col = "${table_name}_email";

        $sql = "SELECT COUNT(*) AS count, $email_col FROM $db_name.$table_name GROUP BY $email_col";
        $results = $this->db->query($sql);
        if (!$results)
            return $stats;

        foreach ($results as $row) {
            $stats[$row[$email_col]] = $row["count"];
        }

        return $stats;
    }


    private static function mysql_date($dt) {
        $dt_str = date_format($dt, "Y-m-d H:i:s");
        return $dt_str;
    }
    private static function format_short_date($comp) {
        return date_format(date_create($comp), "n/j h:i A");
    }
}

