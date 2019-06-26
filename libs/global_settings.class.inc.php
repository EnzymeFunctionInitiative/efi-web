<?php

class global_settings {

    public static function get_uploads_dir() {
        $dir =__UPLOAD_DIR__;
        if (is_dir($dir)) {
            return $dir;
        }
        return false;
    }

    public static function get_output_dir() {
        if (is_dir(__OUTPUT_DIR__)) {
            return __OUTPUT_DIR__;
        }
        return false;
    }

    public static function get_rel_output_dir() {
        return __RELATIVE_OUTPUT_DIR__;		
    }

    public static function get_web_address() {
        return dirname($_SERVER['PHP_SELF']);
    }

    public static function get_web_root() {
        return __WEB_ROOT__;
    }

    public static function get_base_web_root() {
        return __BASE_WEB_ROOT__ . "/" . __BASE_WEB_PATH__;
    }

    public static function get_retention_days() {
        return __RETENTION_DAYS__;
    }

    public static function get_file_retention_days() {
        return __FILE_RETENTION_DAYS__;
    }

    public static function get_shortbred_enabled() {
        if (defined("__ENABLE_SHORTBRED__"))
            return __ENABLE_SHORTBRED__ == true;
        else
            return false;
    }
    
    public static function get_email_footer() {
        return __EMAIL_FOOTER__;
    }

    public static function get_admin_email() {
        return __ADMIN_EMAIL__;
    }
    
    public static function get_error_admin_email() {
        return __ERROR_ADMIN_EMAIL__;
    }

    public static function get_feedback_email() {
        return __FEEDBACK_EMAIL__;
    }

    public static function get_memory_queue() {
        return __MEMORY_QUEUE__;
    }

    public static function get_normal_queue() {
        return __NORMAL_QUEUE__;
    }

    public static function get_cluster_scheduler() {
        return __CLUSTER_SCHEDULER__;
    }

    public static function get_default_group_name() {
        return "DEFAULT";
    }

    public static function get_job_groups_enabled() {
        if (defined("__ENABLE_JOB_GROUPS__"))
            return __ENABLE_JOB_GROUPS__ == true;
        else
            return false;
    }

    public static function get_recent_jobs_enabled() {
        return __ENABLE_RECENT_JOBS__;
    }

    public static function get_website_enabled() {
        return __ENABLE_WEBSITE__; 
    }

    public static function get_release_status() {
        return defined("__BETA_RELEASE__") && __BETA_RELEASE__ ? __BETA_RELEASE__ . " " : "";
    }

    public static function get_is_beta_release() {
        return defined("__BETA_RELEASE__") && __BETA_RELEASE__ ? true : false;
    }

    public static function get_database_modules() {
        $mod_info = array();
        if (defined("__EFIDB_MODULES__")) {
            $mods = explode(",", __EFIDB_MODULES__);
            foreach ($mods as $mod) {
                $parts = explode("|", $mod);
                array_push($mod_info, $parts);
            }
        }
        return $mod_info;
    }

    public static function get_num_job_limit() { // The maximum number of jobs a user can submit in a 24-hour time period.
        return (defined("__NUM_JOB_LIMIT__") && __NUM_JOB_LIMIT__) ? __NUM_JOB_LIMIT__ : 6;
    }
    
    public static function advanced_options_enabled() {
        return defined("__ENABLE_ADVANCED_OPTIONS__") ? __ENABLE_ADVANCED_OPTIONS__ : false;
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
    public static function get_ena_version() {
        return __ENA_VERSION__;
    }
    public static function get_est_version() {
        return defined("__EST_VERSION__") && __EST_VERSION__ ? __EST_VERSION__ : "-";
    }
    public static function get_est_url() {
        return __EST_URL__ ? __EST_URL__ : "#";
    }
    public static function get_gnt_version() {
        return defined("__GNT_VERSION__") && __GNT_VERSION__ ? __GNT_VERSION__ : "-";
    }
    
    public static function get_est_database() {
        return defined("__EFI_EST_DB_NAME__") ? __EFI_EST_DB_NAME__ : "";
    }


}
?>
