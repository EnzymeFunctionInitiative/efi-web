<?php
namespace efi;
require_once(__DIR__ . "/../../init.php");

use \efi\global_settings;


class global_functions {

    public static function generate_key() {
        $key = uniqid (rand (),true);
        $hash = sha1($key);
        return $hash;
    }
    
    public static function copy_to_uploads_dir($tmp_file, $uploaded_filename, $id, $prefix = "", $forceExtension = "") {
        $uploads_dir = self::get_uploads_dir();

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

        $sql = "UPDATE $table SET $theCol = " . $db->escape_string($json) . "";
        $sql .= " WHERE ${prefix}_id='$id' LIMIT 1";
        $result = $db->non_select_query($sql);

        return $result;
    }

    public static function safe_filename($filename) {
        return preg_replace("([^A-Za-z0-9_\-\.])", "_", $filename);
    }

    public static function format_short_date($comp, $date_only = false) {
        $fmt_str = $date_only ? "n/j" : "n/j h:i A";
        return date_format(date_create($comp), $fmt_str);
    }

    public static function get_file_retention_start_date() {
        $num_days = global_settings::get_file_retention_days();
        return self::get_prior_date($num_days);
    }

    public static function get_archived_retention_start_date() {
        $num_days = global_settings::get_archived_retention_days();
        return self::get_prior_date($num_days);
    }

    // This is for cleaning up failed jobs, after the specified number of days.
    public static function get_failed_retention_start_date() {
        $num_days = 14;
        return self::get_prior_date($num_days);
    }

    public static function get_start_date_window() {
        $num_days = global_settings::get_retention_days();
        return self::get_prior_date($num_days);
    }

    public static function get_prior_date($num_days_in_past) {
        $dt = new DateTime();
        $past_date = $dt->sub(new DateInterval("P${num_days_in_past}D"));
        $mysql_date = $past_date->format("Y-m-d");
        return $mysql_date;
    }

    public static function get_est_results_path() {
        return defined("__EST_RESULTS_DIR__") ? __EST_RESULTS_DIR__ : "";
    }

    public static function get_est_job_results_path($id) {
        $out_dir = self::get_est_job_results_relative_path($id);
        return self::get_est_results_path() . "/$out_dir";
    }

    public static function get_est_job_output_dir() {
        $out_dir = defined("__EST_JOB_RESULTS_DIRNAME__") ? __EST_JOB_RESULTS_DIRNAME__ : "output";
        return $out_dir;
    }

    public static function get_est_job_results_relative_path($id) {
        $out_dir = self::get_est_job_output_dir();
        return "$id/$out_dir";
    }

    public static function get_est_colorssn_filename($id, $filename, $include_ext = true) {
        $suffix = defined("__EST_COLORSSN_SUFFIX__") ? __EST_COLORSSN_SUFFIX__ : "_coloredssn";
        $parts = pathinfo($filename);
        if (substr_compare($parts['filename'], ".xgmml", -strlen(".xgmml")) === 0) { // Ends in .zip
            $parts = pathinfo($parts['filename']);
        }
        $filename = $parts['filename'];
        $ext = $include_ext ? ".xgmml" : "";
        return "${id}_$filename$suffix$ext";
    }

    public static function verify_est_job($db, $est_id, $est_key, $ssn_idx) {
        if (!is_numeric($est_id))
            return false;
        if (!is_numeric($ssn_idx))
            return false;
        $est_key = preg_replace("/[^A-Za-z0-9]/", "", $est_key);

        $est_db = global_settings::get_est_database();
        $sql = "SELECT analysis.*, generate_key FROM $est_db.analysis " .
            "JOIN $est_db.generate ON generate_id = analysis_generate_id " .
            "WHERE analysis_id = $est_id AND generate_key = '$est_key'";
        $result = $db->query($sql);

        $info = array();

        if ($result) {
            $result = $result[0];
            $info["generate_id"] = $result["analysis_generate_id"];
            $info["analysis_id"] = $est_id;
            $info["analysis_dir"] = $result["analysis_filter"] . "-" . 
                                    $result["analysis_evalue"] . "-" .
                                    $result["analysis_min_length"] . "-" .
                                    $result["analysis_max_length"];
            return $info;
        } else {
            return false;
        }
    }

    public static function get_est_filename($job_info, $est_aid, $ssn_idx) {

        $est_gid = $job_info["generate_id"];
        $a_dir = $job_info["analysis_dir"];

        $base_est_results = global_settings::get_est_results_dir();
        $est_results_name = "output";
        $est_results_dir = "$base_est_results/$est_gid/$est_results_name/$a_dir";

        if (!is_dir($est_results_dir)) {
            $est_results_dir = "$est_results_dir-nc";
        }
        if (!is_dir($est_results_dir)) {
            return false;
        }

        $filename = "";

        $stats_file = "$est_results_dir/stats.tab";
        $fh = fopen($stats_file, "r");

        $c = -1; # header
        while (($line = fgets($fh)) !== false) {
            if ($c++ == $ssn_idx) {
                $parts = explode("\t", $line);
                $filename = $parts[0];
                break;
            }
        }

        fclose($fh);

        $info = array();
        if ($filename) {
            $full_path = "$est_results_dir/$filename";
            if (!file_exists($full_path)) {
                $filename = "$filename.zip";
                $full_path = "$full_path.zip";
            }
            $info["filename"] = $filename;
            $info["full_ssn_path"] = $full_path;
            return $info;
        } else {
            return false;
        }
    }

    public static function send_image_file_for_download($filename, $full_path) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Content-Transfer-Encoding: binary');
        header('Connection: Keep-Alive');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($full_path));
        ob_clean();
        readfile($full_path);
    }
}

?>
