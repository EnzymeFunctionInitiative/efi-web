<?php
require_once(__DIR__."/../../conf/settings_paths.inc.php");
require_once(__BASE_DIR__ . "/libs/global_settings.class.inc.php");

class functions {


    //Possible errors when you upload a file
    private static $upload_errors = array(
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
        2 => 'The uploaded file exceeds the maximu file size.',
        3 => 'The uploaded file was only partially uploaded.',
        4 => 'No file was uploaded.',
        6 => 'Missing a temporary folder.',
        7 => 'Failed to write file to disk.',
        8 => 'File upload stopped by extension.'
    );

    public static function get_upload_error($value) {
        return self::$upload_errors[$value];

    }

    public static function get_blasts($db,$status = 'NEW') {

        $sql = "SELECT * ";
        $sql .= "FROM generate ";
        $sql .= "WHERE generate_status='" . $status . "' ";
        $sql .= "AND generate_type='BLAST' ";
        $sql .= "ORDER BY generate_time_created ASC ";
        $result = $db->query($sql);
        return $result;
    }

    public static function get_families($db,$status = 'NEW') {

        $sql = "SELECT * ";
        $sql .= "FROM generate ";
        $sql .= "WHERE generate_status='" . $status . "' ";
        $sql .= "AND generate_type='FAMILIES' ";
        $sql .= "ORDER BY generate_time_created ASC ";
        $result = $db->query($sql);
        return $result;
    }

    public static function get_accessions($db,$status = 'NEW') {
        $sql = "SELECT * ";
        $sql .= "FROM generate ";
        $sql .= "WHERE generate_status='" . $status . "' ";
        $sql .= "AND generate_type='ACCESSION' ";
        $sql .= "ORDER BY generate_time_created ASC ";
        $result = $db->query($sql);
        return $result;
    }

    public static function get_colorssns($db,$status = 'NEW') {
        $sql = "SELECT * ";
        $sql .= "FROM generate ";
        $sql .= "WHERE generate_status='" . $status . "' ";
        $sql .= "AND (generate_type='COLORSSN' OR generate_type='CLUSTER' OR generate_type='NBCONN' OR generate_type='CONVRATIO') ";
        $sql .= "ORDER BY generate_time_created ASC ";
        $result = $db->query($sql);
        return $result;
    }

    public static function get_fastas($db,$status = 'NEW') {

        $sql = "SELECT * ";
        $sql .= "FROM generate ";
        $sql .= "WHERE generate_status='" . $status . "' ";
        $sql .= "AND (generate_type='FASTA' OR generate_type='FASTA_ID') ";
        $sql .= "ORDER BY generate_time_created ASC ";
        $result = $db->query($sql);
        return $result;
    }

    public static function get_analysis($db,$status = 'NEW') {

        $sql = "SELECT * ";
        $sql .= "FROM analysis ";
        $sql .= "WHERE analysis_status='" . $status . "' ";
        $sql .= "ORDER BY analysis_time_created ASC ";
        $result = $db->query($sql);
        return $result;
    }

    public static function get_analysis_jobs_for_generate($db, $generate_id, $status = "") {
        $sql = "SELECT * FROM analysis WHERE analysis_generate_id = $generate_id";
        if ($status)
            $sql .= " AND analysis_status = '$status'";
        $result = $db->query($sql);
        return $result;
    }

    public static function get_color_jobs_for_analysis($db, $generate_id, $status = "") {
        //$sql = "SELECT generate_id FROM generate WHERE generate_type = 'COLORSSN' AND generate_params LIKE '%\"generate_color_ssn_source_id\":\"$generate_id\"%'";
        $sql = "SELECT generate_id FROM generate WHERE generate_params LIKE '%\"generate_color_ssn_source_id\":\"$generate_id\"%'";
        $result = $db->query($sql);
        return $result;
    }

    public static function get_color_jobs_for_color_job($db, $generate_id, $status = "") {
        $sql = "SELECT generate_id FROM generate WHERE generate_params LIKE '%\"color_ssn_source_color_id\":\"$generate_id\"%'";
        $result = $db->query($sql);
        return $result;
    }

    public static function get_job_status($db, $generate_id, $analysis_id, $key) {
        $result = array("generate" => "", "analysis" => "", "job_type" => "", "sql" => "");
        if ($analysis_id) {
            $sql = "SELECT A.analysis_status, G.generate_status, G.generate_type ";
            $sql .= "FROM analysis AS A ";
            $sql .= "LEFT JOIN generate AS G ON A.analysis_generate_id = G.generate_id ";
            $sql .= "WHERE A.analysis_id = $analysis_id AND G.generate_id = $generate_id AND G.generate_key = '$key' ";
            $a_result = $db->query($sql);
            if ($a_result) {
                $result["generate"] = $a_result[0]["generate_status"];
                $result["analysis"] = $a_result[0]["analysis_status"];
                $result["job_type"] = $a_result[0]["generate_type"];
            }
            $result["sql"] = $sql;
        }
        else {
            $sql = "SELECT generate_status, generate_type ";
            $sql .= "FROM generate ";
            $sql .= "WHERE generate_id = $generate_id AND generate_key = '$key'";
            $gen_result = $db->query($sql);
            if ($gen_result) {
                $result["generate"] = $gen_result[0]["generate_status"];
                $result["job_type"] = $gen_result[0]["generate_type"];
            }
            $result["sql"] = $sql;
        }
        return $result;
    }

    public static function is_job_sticky($db, $generate_id, $user_email) {
        //$sql = "SELECT generate_parent_id FROM generate WHERE generate_id = $generate_id AND generate_parent_id IS NOT NULL";
        $pre_group = self::get_precompute_group();
        $sql = "SELECT job_group.job_id, generate.generate_email FROM job_group " .
            "JOIN generate ON job_group.job_id = generate.generate_id " .
            //"WHERE job_group.generate_id = $generate_id AND generate.generate_email != '$user_email'";
            "WHERE job_group.job_type = 'EST' AND job_group.job_id = $generate_id AND (generate.generate_email != '$user_email' OR job_group.user_group != '')";
        // users can't create copies of sticky jobs that they own.
        $result = $db->query($sql);
        if ($result)
            return true;
        else
            return false;
    }
    
    // Check if the given ID is already a parent to a job in the user's profile.  If so then we return the ID of the
    // user's job.  This prevents us from creating lots of new generate jobs for each use of the precompute job.
    public static function is_parented($db, $generate_id, $user_email) {
        $sql = "SELECT generate_id FROM generate WHERE generate_parent_id = $generate_id AND generate_email = '$user_email'";
        $result = $db->query($sql);
        if ($result)
            return $result[0]["generate_id"];
        else
            return false;
    }

    public static function get_job_email($db, $generate_id, $analysis_id, $key) {
        $sql = "SELECT generate_email FROM analysis ";
        $sql .= "JOIN generate ON generate.generate_id = analysis.analysis_generate_id ";
        $sql .= "WHERE generate_id = $generate_id AND analysis_id = $analysis_id AND generate_key = '$key'";
        $result = $db->query($sql);
        if ($result)
            return $result[0]["generate_email"];
        else
            return false;
    }

    public static function get_gnt_migrate_info($db, $analysis_id) {
        $db_name = global_settings::get_gnt_database();
        if (!$db_name)
            return false;

        $sql = "SELECT gnn_id, gnn_key FROM $db_name.gnn WHERE gnn_est_source_id = $analysis_id AND gnn_status = '" . __FINISH__ . "'";
        $result = $db->query($sql);
        if ($result) {
            $info = array();
            $result = $result[0];
            $info["gnn_id"] = $result["gnn_id"];
            $info["gnn_key"] = $result["gnn_key"];
            return $info;
        } else {
            return false;
        }
    }




    public static function parse_datetime($datetimeString) {
        return DateTime::createFromFormat("Y-m-d H:i:s", $datetimeString);
    }

    public static function format_datetime($datetimeObject) {
        return $datetimeObject->format("m/d/Y g:i A, T");
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
        $mb = number_format($bytes/1048576,0);
        if ($mb == 0)
            return "<1";
        return $mb;

    }

    public static function get_server_name() {
        return $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']) . "/";

    }

    public static function get_results_dir() {
        return __EST_RESULTS_DIR__; // set in the global conf file
    }
    public static function get_results_dirname() {
        return __RESULTS_DIRNAME__;
    }
    public static function get_results_example_dir() {
        return __RESULTS_EXAMPLE_DIR__; // set in the global conf file
    }
    public static function get_results_example_dirname() {
        return __RESULTS_EXAMPLE_DIRNAME__;
    }
    public static function get_example_dir() {
        return "examples";
    }

    public static function get_web_root() {
        return __WEB_ROOT__;
    }
    public static function get_gnt_web_root() {
        return __GNT_WEB_ROOT__;
    }
    public static function get_cgfp_web_root() {
        return __CGFP_WEB_ROOT__;
    }

    public static function get_efi_module() {
        return __EFI_EST_MODULE__;
    }

    public static function get_efidb_module() {
        return defined("__EFI_DB_MODULE__") ? __EFI_DB_MODULE__ : __EFIDB_MODULE__;
    }

    public static function get_efi_database() {
        $db_mod = self::get_efidb_module();
        
        $exec = "source /etc/profile\nmodule avail $db_mod";
        $output_array = array();
        $exit_status = 1;

        $output = exec($exec, $output_array, $exit_status);
        $base_mod_path = "";
        foreach ($output_array as $line) {
            if (preg_match("/^-----+/", $line)) {
                $line = preg_replace("/^--+/", "", $line);
                $line = preg_replace("/--+$/", "", $line);
                $line = preg_replace("/\s/", "", $line);
                $base_mod_path = $line;
                break;
            }
        }

        $mod_file = "$base_mod_path/$db_mod";
        if (!file_exists($mod_file))
            return "";

        $db_name = "";

        $fh = fopen($mod_file, "r");
        while (($line = fgets($fh)) !== false) {
            $parts = preg_split("/\s+/", $line);
            if (count($parts) >= 3 && $parts[0] == "setenv" && $parts[1] == "EFI_DB") {
                $db_name = $parts[2];
                break;
            }
        }
        fclose($fh);

        return $db_name;
    }

    public static function get_efignn_module() {
        return __EFI_GNT_MODULE__;
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

    public static function get_cluster_user() {
        return __CLUSTER_USER__;
    }

    public static function get_cluster_user_queuable() {
        return __CLUSTER_USER_QUEUABLE__;
    }
    public static function get_cluster_queuable() {
        return __CLUSTER_QUEUABLE__;
    }

    public static function get_cluster_procs() {
        return __CLUSTER_PROCS__;
    }

    public static function get_interpro_version($db_mod_index = -1) {
        if ($db_mod_index < 0 || !defined("__INTERPRO_VERSIONS__")) {
            return __INTERPRO_VERSION__;
        } else {
            $vers = explode(",", __INTERPRO_VERSIONS__);
            if (isset($vers[$db_mod_index]))
                return $vers[$db_mod_index];
            else
                return __INTERPRO_VERSION__;
        }
    }
    public static function get_uniprot_version($db_mod_index = -1) {
        if ($db_mod_index < 0 || !defined("__UNIPROT_VERSIONS__")) {
            return __UNIPROT_VERSION__;
        } else {
            $vers = explode(",", __UNIPROT_VERSIONS__);
            if (isset($vers[$db_mod_index]))
                return $vers[$db_mod_index];
            else
                return __UNIPROT_VERSION__;
        }
    }
    public static function get_est_version() {
        return __EST_VERSION__;
    }

    public static function get_generate_queue() {
        return __GENERATE_QUEUE__;
    }
    public static function get_memory_queue() {
        return __MEMORY_QUEUE__;
    }
    public static function get_analyse_queue() {
        return __ANALYSE_QUEUE__;
    }

    public static function get_interpro_website() {
        return __INTERPRO_WEBSITE__;
    }

    public static function get_uploads_dir() {
        return __UPLOADS_DIR__;
    }

    public static function get_blasthits_processors() {
        return __BLASTHITS_PROCS__;
    }

    public static function get_fraction() {
        return __FRACTION_DEFAULT__;
    }

    public static function get_databases($db) {
        $sql = "SELECT * FROM db_version";
        return $db->query($sql);
    }

    public static function get_encoded_db_version($db_mod = "") {
        $db_mod_index = -1;
        if ($db_mod) {
            $mods = global_settings::get_database_modules();
            for ($i = 0; $i < count($mods); $i++) {
                if (strtoupper($mods[$i][1]) == strtoupper($db_mod)) {
                    $db_mod_index = $i;
                    break;
                }
            }
        }

        $ver = ((int)str_replace("_", "", functions::get_uniprot_version($db_mod_index))) * 10000;
        $ver += (int)functions::get_interpro_version($db_mod_index);
        return $ver;
    }

    public static function decode_db_version($ver) {
        if (empty($ver)) {
            return "";
        }

        $ipv = $ver % 1000;
        $upvYear = intval($ver / 1000000);
        $upvMon = intval(intval($ver / 10000) % 100);
        return sprintf("UniProt: %d-%02d / InterPro: %.0f", $upvYear, $upvMon, $ipv);
    }


    public static function safe_filename($filename) {
        return preg_replace("([^A-Za-z0-9_\-\.])", "_", $filename);
    }

    public static function format_job_type($gen_type) {
        if ($gen_type == "BLAST") {
            $gen_type = "Sequence BLAST (Option A)";
        } else if ($gen_type == "FAMILIES") {
            $gen_type = "Families (Option B)";
        } else if ($gen_type == "ACCESSION") {
            $gen_type = "Accession IDs (Option D)";
        } else if ($gen_type == "FASTA") {
            $gen_type = "FASTA (Option C), no FASTA header reading";
        } else if ($gen_type == "FASTA_ID") {
            $gen_type = "FASTA (Option C), with FASTA header reading";
        } else if ($gen_type == "COLORSSN") {
            $gen_type = "Color SSN";
        } else if ($gen_type == "CLUSTER") {
            $gen_type = "Cluster Analysis";
        } else if ($gen_type == "NBCONN") {
            $gen_type = "Neighborhood Connectivity";
        } else if ($gen_type == "CONVRATIO") {
            $gen_type = "Convergence Ratio";
        }

        return $gen_type;
    }

    public static function add_database($db,$db_date,$interpro,$unipro,$default = 0) {
        $sql = "INSERT INTO db_version(db_version_date,db_version_interpro,db_version_unipro,db_version_default) ";
        $sql .= "VALUES($db_date,$interpro,$unipro,$default)";
        $result = $db->query($sql);
        if ($result) {
            return array('RESULT'=>true,'ID'=>$result,'MESSAGE'=>'Successfully added EFI-EST database');
        }
        return array('RESULT'=>false,'MESSAGE'=>'Error adding EFI-EST database version');

    }
    public static function get_valid_fasta_filetypes() {
        $filetypes = explode(" ",__FASTA_FILETYPES__);
        return $filetypes;
    }
    public static function get_valid_accession_filetypes() {
        $filetypes = explode(" ",__ACCESSION_FILETYPES__);
        return $filetypes;
    }
    public static function get_valid_colorssn_filetypes() {
        $filetypes = explode(" ",__COLORSSN_FILETYPES__);
        return $filetypes;
    }

    public static function get_is_debug() {
        return getenv('EFI_DEBUG') ? true : false;
    }

    public static function get_program_selection_enabled() {
        return __ENABLE_PROGRAM_SELECTION__;
    }

    public static function get_no_matches_filename() {
        return __NO_MATCHES_FILENAME__;
    }

    public static function get_temp_fasta_id_filename() {
        return __TEMP_FASTA_ID_FILENAME__;
    }

    public static function get_colorssn_map_dir_name() {
        return __COLORSSN_MAP_DIR_NAME__;
    }
    public static function get_colorssn_map_filename() {
        return __COLORSSN_MAP_FILE_NAME__;
    }
    public static function get_colorssn_domain_map_filename() {
        return __COLORSSN_DOMAIN_MAP_FILE_NAME__;
    }

    public static function get_accession_counts_filename() {
        return __ACC_COUNT_FILENAME__ ? __ACC_COUNT_FILENAME__ : "";
    }

    public static function sanitize_family($family) {
        return trim($family);
    }

    public static function get_family_type($family) {
        $family = strtolower($family);
        if (substr($family, 0, 2) === "pf")
            return "PFAM";
        else if (substr($family, 0, 3) === "ipr")
            return "INTERPRO";
        else if (substr($family, 0, 3) === "g3d")
            return "GENE3D";
        else if (substr($family, 0, 3) === "ssf")
            return "SSF";
        else if (substr($family, 0, 2) === "cl")
            return "CLAN";
        else
            return "";
    }

    public static function get_job_status_script() {
        return "job_status.php";
    }

    public static function get_user_list_access_key() {
        return __USER_LIST_KEY__;
    }

    public static function get_cluster_scheduler() {
        return __CLUSTER_SCHEDULER__ ? __CLUSTER_SCHEDULER__ : "";
    }

    public static function get_cdhit_stats_filename() {
        return __CDHIT_STATS_FILE__;
    }

    public static function get_update_message() {
        $msg = 
            "The EST database has been updated to use UniProt " . 
            self::get_uniprot_version() . " and InterPro " . self::get_interpro_version() . ". ";
        return $msg;
    }

    public static function get_max_queuable_jobs() {
        return __MAX_QUEUABLE_JOBS__;
    }

    public static function get_max_user_queuable_jobs() {
        return __MAX_USER_QUEUABLE_JOBS__;
    }

    public static function get_use_legacy_graphs() {
        if (defined("__USE_LEGACY_GRAPHS__")) {
            return __USE_LEGACY_GRAPHS__;
        } else {
            return false;
        }
    }

    public static function custom_clustering_enabled() {
        return defined("__ENABLE_CUSTOM_CLUSTERING__") && __ENABLE_CUSTOM_CLUSTERING__ ? true : false;
    }

    public static function option_a_families_enabled() {
        return defined("__ENABLE_OPTION_A_FAMILIES__") && __ENABLE_OPTION_A_FAMILIES__ ? true : false;
    }

    public static function get_maximum_number_ssn_nodes() {
        return defined("__MAX_NUM_SNN_NODES__") ? __MAX_NUM_SNN_NODES__ : 10000000;
    }

    public static function get_default_uniref_version() {
        return defined("__DEFAULT_UNIREF_VERSION__") ? __DEFAULT_UNIREF_VERSION__ : "90";
    }

    public static function get_precompute_group() {
        return defined("__PRECOMPUTE_USER_GROUP__") ? __PRECOMPUTE_USER_GROUP__ : "PRECOMPUTE";
    }

    public static function file_size_graph_enabled() {
        return defined("__FILE_SIZE_GRAPH_ENABLED__") ? __FILE_SIZE_GRAPH_ENABLED__ : false;
    }

    public static function get_analysis_job_info($db, $analysis_id) {
        $sql = "SELECT analysis.*, generate_key FROM analysis LEFT JOIN generate ON analysis_generate_id = generate_id WHERE analysis_id = $analysis_id";
        $result = $db->query($sql);

        $info = array();

        if ($result) {
            $result = $result[0];
            $info["generate_id"] = $result["analysis_generate_id"];
            $info["generate_key"] = $result["generate_key"];
            $info["analysis_id"] = $analysis_id;
            $info["analysis_dir"] = $result["analysis_filter"] . "-" . 
                                    $result["analysis_evalue"] . "-" .
                                    $result["analysis_min_length"] . "-" .
                                    $result["analysis_max_length"];
            return $info;
        } else {
            return false;
        }
    }

    public static function get_analysis_ssn_file_info($info, $ssn_idx) {

        $est_gid = $info["generate_id"];
        $a_dir = $info["analysis_dir"];

        $base_est_results = functions::get_results_dir();
        $est_results_name = "output";
        $est_results_dir = "$base_est_results/$est_gid/$est_results_name/$a_dir";

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
            $info["filename"] = $filename;
            $path = "$est_results_dir/$filename";
            if (!file_exists($path) && self::endsWith(strtolower($path), "xgmml") && file_exists($path . ".zip"))
                $path .= ".zip";
            $info["full_ssn_path"] = $path;
            return $info;
        } else {
            return false;
        }
    }

    public static function endsWith($haystack, $needle) {
        return substr_compare($haystack, $needle, -strlen($needle)) === 0;
    }
}

?>
