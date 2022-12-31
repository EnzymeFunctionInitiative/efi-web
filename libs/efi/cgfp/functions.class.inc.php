<?php
namespace efi\cgfp;

require_once(__DIR__."/../../../init.php");

use \efi\global_settings;
use \efi\global_functions;
use \efi\user_auth;
use \efi\cgfp\settings;


class functions extends global_functions {

    //Possible errors when you upload a file
    private static $upload_errors = array(
        1 => 'The uploaded file was too large (system settings).',
        2 => 'The uploaded file too large.',
        3 => 'The uploaded file was only partially uploaded.',
        4 => 'No file was uploaded.',
        6 => 'Server processing failed (temp).',
        7 => 'Server processing failed (write).',
        8 => 'Invalid file extension.'
    );

    public static function get_upload_error($value) {
        return self::$upload_errors[$value];

    }

    public static function log_message($message) {
        $current_time = date('Y-m-d H:i:s');
        $full_msg = $current_time . ": " . $message . "\n";
        if (self::log_enabled()) {
            file_put_contents(self::get_log_file(),$full_msg,FILE_APPEND | LOCK_EX);
        }
        echo $full_msg;
    }

    public static function get_log_file() {
        $log_file = __LOG_FILE__;
        if (!$log_file) {
            touch($log_file);
        }
        return $log_file;
    }

    public static function log_enabled() {
        return __ENABLE_LOG__;
    }

    public static function get_shortbred_jobs($db, $status = 'NEW') {
        $sql = "SELECT * ";
        $sql .= "FROM identify ";
        $sql .= "WHERE identify_status='$status' ";
        $sql .= "ORDER BY identify_time_created ASC ";
        $result = $db->query($sql);
        return $result;
    }

    public static function get_cgfp_applications($db) {
        $sql = "SELECT * FROM applications";
        $results = $db->query($sql);

        $apps = array();
        foreach ($results as $row) {
            $info = array(
                "email" => $row["app_email"],
                "name" => $row["app_name"],
                "institution" => $row["app_institution"],
                "body" => $row["app_body"],
            );
            array_push($apps, $info);
        }

        return $apps;
    }

    public static function add_cgfp_application($db, $name, $email, $institution, $description) {
        $data = array(
            "app_name" => $name,
            "app_email" => $email,
            "app_institution" => $institution,
            "app_body" => $description
        );

        $db->build_insert("applications", $data);
    }

    public static function get_is_debug() {
        return getenv('EFI_DEBUG') ? true : false;
    }

    public static function generate_key() {
        $key = uniqid(rand(), true);
        $hash = sha1($key);
        return $hash;
    }

    public static function is_valid_file_type($filetype) {
        $filetypes = explode(" ", settings::get_valid_file_types());
        return in_array($filetype, $filetypes);
    }

    public static function get_update_message() {
        $msg =
            "The EFI-CGFP database has been updated to use UniProt " . 
            settings::get_uniprot_version() . ".";
        return $msg;
    }

    public static function is_job_sticky($db, $identify_id, $user_email) {
        $sql = "SELECT job_group.job_id, identify.identify_email FROM job_group " .
            "JOIN identify ON job_group.job_id = identify.identify_id " .
            "WHERE job_group.job_type = 'CGFP' AND job_group.job_id = $identify_id AND identify.identify_email != '$user_email'";
        $result = $db->query($sql);
        if ($result)
            return true;
        else
            return false;
    }

    public static function is_shortbred_authorized($db, $user_id) {
        $sb_group = settings::get_shortbred_group();
        if (!$sb_group)
            return false;

        $groups = user_auth::get_user_groups($db, $user_id);
        return in_array($sb_group, $groups);
    }

    public static function get_est_job_filename($db, $est_id, $est_key = null) {
        if (!is_numeric($est_id))
            return false;

        //$est_key is allowed to be null, because this function can be called from an internal function to retrieve the filename only
        $key_where = isset($est_key) ? " AND generate_key = '$est_key'" : "";

        $est_db = global_settings::get_est_database();
        $sql = "SELECT generate_params FROM $est_db.generate WHERE generate_id = $est_id $key_where";
        $result = $db->query($sql);
        if ($result) {
            $params = global_functions::decode_object($result[0]["generate_params"]);
            $filename = isset($params["generate_fasta_file"]) ? $params["generate_fasta_file"] :
                (isset($params["file"]) ? $params["file"] : "");

            // Get the file name only, for display purposes.  If $est_key, then this is
            // going to be used for the full path to the original source EST file.  The original 
            // 'safe' EST file will be used as the source for the identify job.
            $filename = global_functions::get_est_colorssn_filename($est_id, $filename);
            return $filename;
        } else {
            return false;
        }
    }
}


