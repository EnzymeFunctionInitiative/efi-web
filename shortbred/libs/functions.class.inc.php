<?php

require_once(__DIR__ . "/../../libs/global_functions.class.inc.php");
require_once(__DIR__ . "/../../libs/user_auth.class.inc.php");

class functions extends global_functions {

    //Possible errors when you upload a file
    private static $upload_errors = array(
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
        3 => 'The uploaded file was only partially uploaded.',
        4 => 'No file was uploaded.',
        6 => 'Missing a temporary folder.',
        7 => 'Failed to write file to disk.',
        8 => 'File upload stopped by extension.',
    );

    public static function verify_email($email) {
        $email = strtolower($email);
        $hostname = "";
        if (strpos($email,"@")) {
            list($prefix,$hostname) = explode("@",$email);
        }

        $valid = 1;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $valid = 0;
        }
        elseif (($hostname != "") && (!checkdnsrr($hostname,"MX"))) {
            $valid = 0;
        }
        return $valid;

    }

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

    public static function copy_to_uploads_dir($tmp_file, $uploaded_filename, $id, $prefix = "", $forceExtension = "") {
        $uploads_dir = settings::get_uploads_dir();

        // By this time we have verified that the uploaded file is valid. Now we need to retain the
        // extension in case the file is a zipped file.
        if ($forceExtension)
            $file_type = $forceExtension;
        else
            $file_type = strtolower(pathinfo($uploaded_filename, PATHINFO_EXTENSION));
        $filename = $prefix . $id . "." . $file_type;
        $full_path = $uploads_dir . "/" . $filename;
        if (is_uploaded_file($tmp_file)) {
            if (move_uploaded_file($tmp_file,$full_path)) { return $filename; }
        }
        else {
            if (copy($tmp_file,$full_path)) { return $filename; }
        }
        return false;
    }

    public static function is_valid_file_type($filetype) {
        $filetypes = explode(" ", __VALID_FILE_TYPE__);
        return in_array($filetype, $filetypes);
    }

    public static function get_update_message() {
        return "";
    }

    public static function is_job_sticky($db, $identify_id, $user_email) {
        $sql = "SELECT job_group.identify_id, identify.identify_email FROM job_group " .
            "JOIN identify ON job_group.identify_id = identify.identify_id " .
            "WHERE job_group.identify_id = $identify_id AND identify.identify_email != '$user_email'";
        $result = $db->query($sql);
        if ($result)
            return true;
        else
            return false;
    }
    
    public static function send_table($table_filename, $table_string) {
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $table_filename . '"');
        header('Content-Length: ' . strlen($table_string));
        ob_clean();
        echo $table_string;
    }

    public static function get_mg_db_info() {
        return self::get_mg_db_info_filtered(array());
    }

    public static function get_mg_db_info_filtered($bodysites) { // Array to filter for specific body sites
        $mg_dbs = settings::get_metagenome_db_list();
    
        $mg_db_list = explode(",", $mg_dbs);
    
        $info = array("site" => array(), "gender" => array());
    
        foreach ($mg_db_list as $mg_db) {
            $fh = fopen($mg_db, "r");
            if ($fh === false)
                continue;
    
            $meta = self::get_mg_metadata($mg_db);
    
            while (($data = fgetcsv($fh, 1000, "\t")) !== false) {
                if (isset($data[0]) && $data[0] && $data[0][0] == "#")
                    continue; // skip comments
    
                $mg_id = $data[0];
    
                $pos = strpos($data[1], "-");
                $site = trim(substr($data[1], $pos+1));
                $site = str_replace("_", " ", $site);
    
                if (count($bodysites) == 0 || in_array($site, $bodysites)) {
                    $info["site"][$mg_id] = $site;
                    $info["gender"][$mg_id] = $data[2];
                    $info["color"][$mg_id] = isset($meta[$site]) ? $meta[$site]["color"] : "";
                    $info["order"][$mg_id] = isset($meta[$site]) ? $meta[$site]["order"] : "";
                }
            }
    
            fclose($fh);
        }
    
        return $info;
    }
    
    public static function get_mg_metadata($mg_db) {
    
        $info = array(); # map site to color
    
        $scheme_file = "$mg_db.metadata";
        if (file_exists($scheme_file)) {
            $fh = fopen($scheme_file, "r");
            if ($fh === false)
                return false;
        } else {
            return false;
        }
    
        while (($data = fgetcsv($fh, 1000, "\t")) !== false) {
            if (isset($data[0]) && $data[0] && $data[0][0] == "#")
                continue; // skip comments
    
            $site = str_replace("_", " ", $data[0]);
            $color = $data[1];
            $order = isset($data[2]) ? $data[2] : 0;
            $info[$site] = array('color' => $color, 'order' => $order);
        }
    
        fclose($fh);
    
        return $info;
    }

    public static function is_shortbred_authorized($db, $user_id) {
        $sb_group = settings::get_shortbred_group();
        if (!$sb_group)
            return false;

        $groups = user_auth::get_user_groups($db, $user_id);
        return in_array($sb_group, $groups);
    }
}
?>
