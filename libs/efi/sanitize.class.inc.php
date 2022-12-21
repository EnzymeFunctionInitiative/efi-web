<?php
namespace efi;


class sanitize {

    const POST = 1;
    const GET = 2;


    public static function sanitize_email($env, $var, $default_val = null) {
        $val = filter_var($email, FILTER_VALIDATE_EMAIL);
        return $val;
    }
    public static function get_sanitize_email($env, $var, $default_val = null) {
        return self::sanitize_email($_GET, $var, $default_val);
    }
    public static function post_sanitize_email($env, $var, $default_val = null) {
        return self::sanitize_email($_POST, $var, $default_val);
    }

    public static function sanitize_num($env, $var, $default_val = null) {
        $result = filter_input(INPUT_POST, $var, FILTER_VALIDATE_INT);
        if ($result === false)
            $result = filter_input(INPUT_POST, $var, FILTER_VALIDATE_FLOAT);

        if ($result === false)
            return $default_val;
        else
            return $result;
    }
    public static function get_sanitize_num($var, $default_val = null) {
        return self::sanitize_num($_GET, $var, $default_val);
    }
    public static function post_sanitize_num($var, $default_val = null) {
        return self::sanitize_num($_POST, $var, $default_val);
    }




    public static function sanitize_string($env, $var, $default_val = null, $custom_re = "") {
        if (!isset($env[$var]))
            return $default_val;
        return self::sanitize_string_val($env[$var], $default_val, $custom_re);
    }
    public static function sanitize_string_val($val, $default_val = null, $custom_re = "") {
        $val = trim($val);

        if (empty($val))
            return $val;

        $regex = "[^A-Za-z0-9_\-]";
        $replace_val = "_";
        if ($custom_re) {
            if ($default_val !== null)
                $replace_val = $default_val;
            $regex = $custom_re;
        }

        $result = preg_replace("/$regex/", $replace_val, $val);
        return $result;
    }
    public static function get_sanitize_string($var, $default_val = null, $custom_re = "") {
        return self::sanitize_string($_GET, $var, $default_val, $custom_re);
    }
    public static function post_sanitize_string($var, $default_val = null, $custom_re = "") {
        return self::sanitize_string($_POST, $var, $default_val, $custom_re);
    }
    public static function get_sanitize_string_crnl($var, $default_val = "", $custom_re = "") {
        return self::sanitize_string($_GET, $var, $default_val, $custom_re);
    }
    public static function post_sanitize_string_crnl($var, $default_val = "", $custom_re = "") {
        return self::sanitize_string($_POST, $var, $default_val, $custom_re);
    }
    public static function get_sanitize_string_relaxed($var, $default_val = null) {
        return self::sanitize_string($_GET, $var, "_", "[^A-Za-z0-9_\-, ]");
    }
    public static function post_sanitize_string_relaxed($var, $default_val = null) {
        //echo "|$var:".$_POST[$var]."|";
        return self::sanitize_string($_POST, $var, $default_val, "[^A-Za-z0-9_\-, ]");
    }
    public static function get_sanitize_key($var) {
        return self::sanitize_string($_GET, $var, null, "[^A-Za-z]");
    }
    public static function post_sanitize_key($var) {
        return self::sanitize_string($_POST, $var, null, "[^A-Za-z]");
    }
    // Allow carriage returns and new lines
    public static function get_sanitize_seq($var) {
        return self::sanitize_string($_GET, $var, "", "[^A-Za-z0-9, \.\|>\r\n]");
    }
    public static function post_sanitize_seq($var) {
        return self::sanitize_string($_POST, $var, "", "[^A-Za-z0-9, \.\|>\r\n]");
    }




    public static function validate_key($var, $method) {
        $env = $method === self::POST ? $_POST : $_GET;
        if (isset($env[$var]))
            $val = $env[$var];
        if (isset($val) && preg_match("/^[A-Za-z0-9]+$/", $val))
            return $val;
        else
            return false;
    }
    public static function validate_id($var, $method) {
        $env = $method === self::POST ? $_POST : $_GET;
        if (isset($env[$var]) && is_numeric($env[$var]))
            return $env[$var];
        else
            return false;
    }




    public static function sanitize_flag($env, $var, $default_val = false) {
        if (!isset($env[$var]))
            return $default_val;
        if ($env[$var] === "true" || $env[$var] === true)
            return true;
        else
            return false;
    }
    public static function get_sanitize_flag($var, $default_val = false) {
        return self::sanitize_flag($_GET, $var, $default_val);
    }
    public static function post_sanitize_flag($var, $default_val = false) {
        return self::sanitize_flag($_POST, $var, $default_val);
    }
    public static function sanitize_is_set($env, $var) {
        if (isset($env[$var]))
            return true;
        else
            return false;
    }
    public static function get_is_set($var) {
        return self::sanitize_is_set($_GET, $var);
    }
    public static function post_is_set($var) {
        return self::sanitize_is_set($_POST, $var);
    }
}


