<?php
require_once(__DIR__."/../../conf/settings_paths.inc.php");
require_once(__DIR__."/const.class.inc.php");
require_once(__BASE_DIR__."/libs/global_functions.class.inc.php");

class functions extends global_functions {

    //Possible errors when you upload a file
    private static $upload_errors = array(
        1 => 'The uploaded file exceeds the maximum file size (ini).',
        2 => 'The uploaded file exceeds the maximum file size (form).',
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

    public static function verify_neighborhood_size($nbSize) {
        $max_nbSize = 100;
        $valid = 1;
        if ($nbSize == "") {
            $valid = 0;
        }
        if (!preg_match("/^\d+$/",$nbSize)) {
            $valid = 0;
        }
        if ($nbSize < 1) {
            $valid = 0;
        }
        return $valid;
    }

    public static function verify_evalue($evalue) {
        $max_evalue = 100;
        $valid = 1;
        if ($evalue == "") {
            $valid = 0;
        }
        if (!preg_match("/^\d+$/",$evalue)) {
            $valid = 0;
        }
        if ($evalue > $max_evalue) {
            $valid = 0;
        }
        return $valid;
    }

    public static function remove_blast_header($blast_input) {
        $parts = preg_split('/[\r\n]+/', $blast_input);
        if (preg_match('/^\s*>/', $blast_input))
            $parts = array_slice($parts, 1);
        $blast_input = implode("", $parts);
        return $blast_input;
    }

    public static function verify_blast_input($blast_input) {
        $blast_input = strtolower($blast_input);
        $valid = 1;
        if (!strlen($blast_input)) {
            $valid = 0;
        }
        if (strlen($blast_input) > 65534) {
            $valid = 0;
        }
        if (preg_match('/[^a-z-* \n\t\r]/',$blast_input)) {
            $valid = 0;
        }
        return $valid;
    }

    public static function verify_max_seqs($max_seqs) {
        $valid = 0;
        if ($max_seqs == "") {
            $valid = 0;
        }
        elseif (!preg_match("/^[1-9][0-9]*$/",$max_seqs)) {
            $valid = 0;
        }
        elseif ($max_seqs > settings::get_max_blast_seq()) {
            $valid = 0;
        }
        else {
            $valid = 1;
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

    public static function get_gnn_jobs($db, $status = 'NEW') {
        $sql = "SELECT * ";
        $sql .= "FROM gnn ";
        $sql .= "WHERE gnn_status='" . $status . "' ";
        $sql .= "ORDER BY gnn_time_created ASC ";
        $result = $db->query($sql);
        return $result;
    }

    public static function get_is_debug() {
        return getenv('EFI_DEBUG') ? true : false;
    }

    public static function is_diagram_upload_id_valid($id) {
        // Make sure the ID only contains numbers and letters to prevent attacks.
        $hasInvalidChars = preg_match('/[^A-Za-z0-9]/', $id);
        if ($hasInvalidChars === 1)
            return false;

        return file_exists(self::get_diagram_file_path($id));
    }

    public static function get_diagram_file_name($id) {
        return "$id." . settings::get_diagram_extension();
    }

    public static function get_diagram_file_path($id) {
        $filePath = settings::get_diagram_output_dir() . "/$id/" . self::get_diagram_file_name($id);
        return $filePath;
    }

    public static function verify_gnt_job($db, $gnn_id, $gnn_key) {
        $sql = "SELECT gnn_params, gnn_email FROM gnn WHERE gnn_id = $gnn_id AND gnn_key = '$gnn_key'";
        $result = $db->query($sql);
        if ($result) {
            $params = global_functions::decode_object($result[0]["gnn_params"]);
            return array("filename" => $params["filename"], "email" => $result[0]["gnn_email"]);
        } else {
            return false;
        }
    }

    public static function get_gnn_key($db, $gnn_id) {
        $sql = "SELECT gnn_key FROM gnn WHERE gnn_id = $gnn_id";
        $result = $db->query($sql);
        if ($result)
            return $result[0]["gnn_key"];
        else
            return false;
    }

    public static function get_est_job_info_from_gnn_id($db, $gnn_id) {

        $sql = "SELECT gnn_est_source_id FROM gnn WHERE gnn_id = $gnn_id";
        $result = $db->query($sql);
        if ($result)
            $analysis_id = $result[0]["gnn_est_source_id"];
        else
            return false;

        if (!$analysis_id)
            return false;

        return self::get_est_job_info_from_est_id($db, $analysis_id);
    }

    public static function get_est_job_info_from_est_id($db, $analysis_id) {

        $est_db = settings::get_est_database();
        $sql = "SELECT analysis.*, generate_key FROM $est_db.analysis " .
            "JOIN $est_db.generate ON generate_id = analysis_generate_id " .
            "WHERE analysis_id = $analysis_id";
        $result = $db->query($sql);

        $info = array();

        if ($result) {
            $result = $result[0];
            $info["generate_id"] = $result["analysis_generate_id"];
            $info["analysis_id"] = $result["analysis_id"];
            $info["key"] = $result["generate_key"];
            return $info;
        } else {
            return false;
        }
    }

    public static function sqlite_table_exists($sqliteDb, $tableName) {
        // Check if the table exists
        $checkSql = "SELECT name FROM sqlite_master WHERE type='table' AND name='$tableName'";
        $dbQuery = $sqliteDb->query($checkSql);
        if ($dbQuery->fetchArray()) {
            return true;
        } else {
            return false;
        }
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

    public static function get_verbose_job_type($diagramType) {
        $title = "";
        if ($diagramType == DiagramJob::Uploaded || $diagramType == DiagramJob::UploadedZip)
            $title = "Uploaded diagram data file";
        elseif ($diagramType == DiagramJob::BLAST)
            $title = "Sequence BLAST";
        elseif ($diagramType == DiagramJob::IdLookup || $diagramType == "LOOKUP") // "lookup" is for legacy"
            $title = "Sequence ID lookup";
        elseif ($diagramType == DiagramJob::FastaLookup)
            $title = "FASTA header ID lookup";
        return $title;
    }

    public static function get_diagram_id_field($type) {
        switch ($type) {
            case DiagramJob::BLAST:
            case DiagramJob::IdLookup:
            case DiagramJob::FastaLookup:
                return "direct-id";
            case DiagramJob::Uploaded:
            case DiagramJob::UploadedZip:
                return "upload-id";
            default:
                return "gnn-id";
        }
    }

    public static function is_valid_file_type($filetype) {
        $filetypes = explode(" ", __VALID_FILE_TYPE__);
        return in_array($filetype, $filetypes);
    }

    public static function is_valid_diagram_file_type($filetype) {
        $filetypes = explode(" ", __VALID_DIAGRAM_FILE_TYPE__);
        return in_array($filetype, $filetypes);
    }

    public static function is_valid_id_file_type($filetype) {
        $filetypes = explode(" ", __VALID_ID_FILE_TYPE__);
        return in_array($filetype, $filetypes);
    }

    public static function get_update_message() {
        $msg = 
            "The GNT database has been updated to use UniProt " . 
            settings::get_uniprot_version() . ", and ENA downloaded on " . settings::get_ena_version() . ". ";
//            settings::get_uniprot_version() . " and ENA " . settings::get_ena_version() . ". ";
        //$msg .=
        //    "You now have the ability to register a user account for the purpose of viewing prior " .
        //    "jobs in a summary table. You can also access both EFI-EST and EFI-GNT from the top of each page.";
        return $msg;
    }

    public static function check_sync_key($key) {
        $keys = array();
        if (defined("__SYNC_KEYS__")) {
            $keys = explode(",", __SYNC_KEYS__);
        }

        return in_array($key, $keys);
    }

    public static function dump_gnn_info($gnn, $is_sync = false) {
        $baseUrl = settings::get_web_address();
        
        $ssnFile = $gnn->get_relative_color_ssn();
        $ssnZipFile = $gnn->get_relative_color_ssn_zip_file();
        $gnnFile = $gnn->get_relative_gnn();
        $gnnZipFile = $gnn->get_relative_gnn_zip_file();
        $pfamFile = $gnn->get_relative_pfam_hub();
        $pfamZipFile = $gnn->get_relative_pfam_hub_zip_file();
        $idDataZip = $gnn->get_relative_cluster_data_zip_file();
        $pfamDataZip = $gnn->get_relative_pfam_data_zip_file();
        $allPfamDataZip = $gnn->get_relative_all_pfam_data_zip_file();
        $warningFile = $gnn->get_relative_warning_file();
        $idTableFile = $gnn->get_relative_id_table_file();
        $pfamNoneZip = $gnn->get_relative_pfam_none_zip_file();
        $fastaZip = $gnn->get_relative_fasta_zip_file();
        $coocTableFile = $gnn->get_relative_cooc_table_file();
        $hubCountFile = $gnn->get_relative_hub_count_file();
        $diagramFile = $gnn->get_relative_diagram_data_file();
        $diagramZipFile = $gnn->get_relative_diagram_zip_file();

        $files["ssn"] = $baseUrl . "/" . $ssnFile;
        $files["gnnFile"] = $baseUrl . "/" . $gnnFile;
        $files["pfamFile"] = $baseUrl . "/" . $pfamFile;
        $files["warningFile"] = $baseUrl . "/" . $warningFile;

        if (!$is_sync) {
            $files["ssnZip"] = $baseUrl . "/" . $ssnZipFile;
            $files["gnnZipFile"] = $baseUrl . "/" . $gnnZipFile;
            $files["pfamZipFile"] = $baseUrl . "/" . $pfamZipFile;
            $files["allPfamZipFile"] = $baseUrl . "/" . $allPfamZipFile;
            $files["idDataZip"] = $baseUrl . "/" . $idDataZip;
            $files["pfamDataZip"] = $baseUrl . "/" . $pfamDataZip;
            $files["idTableFile"] = $baseUrl . "/" . $idTableFile;
            $files["pfamNoneZip"] = $baseUrl . "/" . $pfamNoneZip;
            $files["fastaZip"] = $baseUrl . "/" . $fastaZip;
            $files["coocTableFile"] = $baseUrl . "/" . $coocTableFile;
            $files["hubCountFile"] = $baseUrl . "/" . $hubCountFile;
            $files["diagramFile"] = $baseUrl . "/" . $diagramFile;
            $files["diagramZipFile"] = $baseUrl . "/" . $diagramZipFile;
        }

        return $files;
    }

    public static function validate_direct_gnd_file($rs_id, $rs_ver, $key) {
        $matches = array();
        $gnd_file = false;
        if (!preg_match("/^\d+\.\d+$/", $rs_ver))
            return false;
        $superfamily_dir = settings::get_superfamily_dir();
        if (!$superfamily_dir)
            return false;
        $base_dir = "$superfamily_dir/rsam-$rs_ver/gnds";
        $key_path = "$base_dir/gnd.key";
        if (!file_exists($key_path))
            return false;
        $file_key = file_get_contents($key_path);
        if ($file_key !== $key)
            return false;
        
        $result = preg_match("/^(cluster-[\-\d]+):?(\d+)?$/", $rs_id, $matches);
        $dicing = false;
        if ($result) {
            $cluster = $matches[1];
            if (isset($matches[1]) && isset($matches[2]))
                $dicing = $matches[2];
        } else {
            return false;
        }

        $gnd_file = "$base_dir/gnd.sqlite";

        if ($gnd_file === false)
            return false;
        $gnd_file = realpath($gnd_file);
        if (strpos($gnd_file, $base_dir) !== 0 || strpos($gnd_file, $base_dir) === false)
            return false;
        if (!file_exists($gnd_file))
            return false;
        return $gnd_file;
    }
}

