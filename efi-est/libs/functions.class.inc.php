<?php

class functions {


    //Possible errors when you upload a file
    private static $upload_errors = array(
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
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
        $sql .= "AND generate_type='COLORSSN' ";
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
        $sql = "SELECT job_group.generate_id, generate.generate_email FROM job_group " .
            "JOIN generate ON job_group.generate_id = generate.generate_id " .
            "WHERE job_group.generate_id = $generate_id AND generate.generate_email != '$user_email'";
            //"WHERE job_group.generate_id = $generate_id AND job_group.user_group = '$pre_group' AND generate.generate_email != '$user_email'";
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

    public static function add_migrate_job($db, $data) {
        $table_name = "migrate_gnt";

        $job = new analysis($db, $data["analysis_id"]);
        $stats = $job->get_network_stats();

        $rel_path = "";
        for ($i = 0; $i < count($stats); $i++) {
            if ($stats[$i]["PctId"] == $data["network"]) {
                $rel_path = $stats[$i]["RelPath"];
                break;
            }
        }

        if (!$rel_path) {
            return 0;
        }

        $file_path = functions::get_results_dir() . "/" . $rel_path;

        $insert_array = array(
            "migrate_generate_id" => $data["generate_id"],
            "migrate_analysis_id" => $data["analysis_id"],
            "migrate_generate_key" => $data["key"],
            "migrate_email" => $data["email"],
            "migrate_status" => __NEW__,
            "migrate_size" => $data["size"],
            "migrate_cooccurrence" => $data["cooccurrence"],
            "migrate_file" => $file_path,
        );

        $insert_result = $db->build_insert($table_name, $insert_array);

        return $insert_result;
    }

    public static function get_gnt_migrate_info($db, $generate_id, $analysis_id) {
        $sql = "SELECT * FROM migrate_gnt WHERE migrate_generate_id = $generate_id AND migrate_analysis_id = $analysis_id";
        $result = $db->query($sql);
        if ($result) {
            $info = array();
            $result = $result[0];
            $info["status"] = $result["migrate_status"];
            $info["gnn_id"] = $result["migrate_gnn_id"];
            $info["gnn_key"] = $result["migrate_gnn_key"];
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
        return number_format($bytes/1048576,0);

    }

    public static function get_server_name() {
        return $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']) . "/";

    }

    public static function get_retention_days() {
        return __RETENTION_DAYS__;
    }

    public static function get_retention_secs() {
        return self::get_retention_days() * 24 * 60 * 60;
    }
    public static function get_results_dir() {
        return __RESULTS_DIR__;
    }
    public static function get_results_dirname() {
        return __RESULTS_DIRNAME__;
    }
    public static function get_example_dir() {
        return "examples";
    }
    public static function get_evalue() {
        return __EVALUE__;
    }

    public static function get_max_seq($format = 0) {
        if ($format) {
            return number_format(__MAX_SEQ__,0);
        }
        return __MAX_SEQ__;
    }
    public static function get_max_blast_seq($format = 0) {
        if ($format) {
            return number_format(__MAX_BLAST_SEQ__,0);
        }
        return __MAX_BLAST_SEQ__;
    }
    public static function get_default_blast_seq($format = 0) {
        if ($format) {
            return number_format(__DEFAULT_BLAST_SEQ__,0);
        }
        return __DEFAULT_BLAST_SEQ__;
    }

    public static function get_email_footer() {
        return __EMAIL_FOOTER__;
    }

    public static function get_web_root() {
        return __WEB_ROOT__;
    }
    public static function get_gnt_web_root() {
        return __GNT_WEB_ROOT__;
    }
    public static function get_admin_email() {
        return __ADMIN_EMAIL__;
    }
    public static function get_error_admin_email() {
        return __ERROR_ADMIN_EMAIL__;
    }

    public static function website_enabled() {
        return __ENABLE_WEBSITE__; 
    }


    public static function get_efi_module() {
        return __EFI_MODULE__;

    }

    public static function get_efidb_module() {
        return __EFIDB_MODULE__;
    }

    public static function get_efignn_module() {
        return __EFI_GNN_MODULE__;
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

    public static function get_interpro_version() {
        return __INTERPRO_VERSION__;
    }
    public static function get_uniprot_version() {
        return __UNIPROT_VERSION__;
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

    public static function option_a_enabled() {
        return __ENABLE_A__;
    }
    public static function option_b_enabled() {
        return __ENABLE_B__;
    }
    public static function option_c_enabled() {
        return __ENABLE_C__;
    }
    public static function option_d_enabled() {
        return __ENABLE_D__;
    }
    public static function option_e_enabled() {
        return __ENABLE_E__;
    }
    public static function colorssn_enabled() {
        return __ENABLE_COLORSSN__;
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

    public static function get_encoded_db_version() {
        $ver = ((int)str_replace("_", "", functions::get_uniprot_version())) * 10000;
        $ver += (int)functions::get_interpro_version();
        return $ver;
    }

    public static function decode_db_version($ver) {
        if (empty($ver)) {
            return "";
        }

        $ipv = $ver % 1000;
        $upvYear = intval($ver / 1000000);
        $upvMon = intval(intval($ver / 10000) % 100);
        return sprintf("UniProt: %d-%02d / Interpro: %.0f", $upvYear, $upvMon, $ipv);
    }


    public static function safe_filename($filename) {
        return preg_replace("([^A-Za-z0-9_\-\.])", "_", $filename);
    }

    public static function format_job_type($gen_type) {
        if ($gen_type == "BLAST") {
            $gen_type = "Option A";
        } else if ($gen_type == "FAMILIES") {
            $gen_type = "Option B";
        } else if ($gen_type == "ACCESSION") {
            $gen_type = "Option D";
        } else if ($gen_type == "FASTA") {
            $gen_type = "Option C (no FASTA header reading)";
        } else if ($gen_type == "FASTA_ID") {
            $gen_type = "Option C (with FASTA header reading)";
        }
        return $gen_type;
    }

    public static function get_release_status() {
        return defined("__BETA_RELEASE__") && __BETA_RELEASE__ ? (__BETA_RELEASE__ . " ") : "";
    }

    public static function is_beta_release() {
        return defined("__BETA_RELEASE__") && __BETA_RELEASE__ ? true : false;
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

    public static function get_default_neighbor_size() {
        return __DEFAULT_NEIGHBOR_SIZE__;
    }
    public static function get_default_cooccurrence() {
        return __COOCCURRENCE__;
    }
    public static function get_colorssn_map_dir_name() {
        return __COLORSSN_MAP_DIR_NAME__;
    }
    public static function get_colorssn_map_file_name() {
        return __COLORSSN_MAP_FILE_NAME__;
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

    public static function get_convergence_ratio_filename() {
        return __CONVERGENCE_RATIO_FILENAME__;
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

    public static function generate_key() {
        $key = uniqid(rand(), true);
        $hash = sha1($key);
        return $hash;
    }

    public static function get_update_message() {
        $msg = 
            "The EST database has been updated to use UniProt " . 
            self::get_uniprot_version() . " and InterPro " . self::get_interpro_version() . ". ";
        //$msg .=
        //    "You now have the ability to register a user account for the purpose of viewing prior " .
        //    "jobs in a summary table. You can also access both EFI-EST and EFI-GNT from the top of each page.";
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

    public static function get_maximum_full_family_count() {
        return defined("__MAX_FULL_FAMILY_COUNT__") ? __MAX_FULL_FAMILY_COUNT__ : 0;
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
}

?>
