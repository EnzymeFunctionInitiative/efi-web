<?php


require_once("global_settings.class.inc.php");

class global_functions {

    public static function generate_key() {
        $key = uniqid (rand (),true);
        $hash = sha1($key);
        return $hash;
    }
    
    public static function copy_to_uploads_dir($tmp_file, $uploaded_filename, $id, $prefix = "", $forceExtension = "") {
        $uploads_dir = global_settings::get_uploads_dir();

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
    
    # recursively remove a directory
    public static function rrmdir($dir) {
        foreach(glob($dir . '/*') as $file) {
            if(is_dir($file))
                self::rrmdir($file);
            else
                unlink($file);
        }
        rmdir($dir);
    }

    public static function bytes_to_megabytes($bytes) {
        return number_format($bytes / 1048576, 0);
    }
    
    public static function decode_object($json) {
        $data = json_decode($json, true);
        if (!$data)
            return array();
        else
            return $data;
    }

    public static function encode_object($obj) {
        return json_encode($obj);
    }

    public static function update_results_object_tmpl($db, $prefix, $table, $column, $id, $data) {
        $theCol = "${prefix}_${column}";

        $sql = "SELECT $theCol FROM $table WHERE ${prefix}_id='$id'";
        $result = $db->query($sql);
        if (!$result)
            return NULL;
        $result = $result[0];
        $results_obj = self::decode_object($result[$theCol]);

        foreach ($data as $key => $value)
            $results_obj[$key] = $value;

        $json = self::encode_object($results_obj);

        $sql = "UPDATE $table SET $theCol = '" . $db->escape_string($json) . "'";
        $sql .= " WHERE ${prefix}_id='$id' LIMIT 1";
        $result = $db->non_select_query($sql);

        return $result;
    }

    public static function safe_filename($filename) {
        return preg_replace("([^A-Za-z0-9_\-\.])", "_", $filename);
    }

    public static function format_short_date($comp) {
        return date_format(date_create($comp), "n/j h:i A");
    }

    public static function get_file_retention_start_date() {
        $numDays = global_settings::get_file_retention_days();
        return self::get_prior_date($numDays);
    }

    // This is for cleaning up failed jobs, after the specified number of days.
    public static function get_failed_retention_start_date() {
        $numDays = 14;
        return self::get_prior_date($numDays);
    }

    public static function get_prior_date($numDaysInPast) {
        $dt = new DateTime();
        $pastDt = $dt->sub(new DateInterval("P${numDaysInPast}D"));
        $mysqlDate = $pastDt->format("Y-m-d");
        return $mysqlDate;
    }
}

?>
