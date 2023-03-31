<?php
namespace efi;


class global_settings {

    // These come from the local conf files
    public static function get_output_dir() {
        if (is_dir(__OUTPUT_DIR__)) {
            return __OUTPUT_DIR__;
        }
        return false;
    }

    public static function get_rel_output_dir() {
        return __RELATIVE_OUTPUT_DIR__;		
    }

    public static function get_uploads_dir() {
        $dir =__UPLOAD_DIR__;
        if (is_dir($dir)) {
            return $dir;
        }
        return false;
    }

    public static function get_web_address() {
        return dirname($_SERVER['PHP_SELF']);
    }

    public static function get_server_name() {
        return $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']) . "/";
    }

    public static function get_web_root() {
        return __WEB_ROOT__;
    }

    public static function get_base_web_path() {
	return __BASE_WEB_PATH__;
    }
    public static function get_base_web_root() {
        return __BASE_WEB_ROOT__ . "/" . __BASE_WEB_PATH__;
    }

    public static function get_retention_days() {
        return __RETENTION_DAYS__;
    }
    public static function get_retention_secs() {
        return self::get_retention_days() * 24 * 60 * 60;
    }

    public static function get_file_retention_days() {
        return __FILE_RETENTION_DAYS__;
    }

    public static function get_archived_retention_days() {
        if (defined("__ARCHIVED_RETENTION_DAYS__"))
            return __ARCHIVED_RETENTION_DAYS__;
        else
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

    public static function get_smtp_host() {
        return (defined("__SMTP_HOST__") && __SMTP_HOST__) ? __SMTP_HOST__ : "localhost";
    }
    public static function get_smtp_port() {
        return (defined("__SMTP_PORT__") && __SMTP_PORT__) ? __SMTP_PORT__ : 25;
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
        if (defined("__ENABLE_WEBSITE__"))
            return __ENABLE_WEBSITE__ == true;
        else
            return false;
    }

    public static function get_website_enabled_message() {
        if (!self::get_website_enabled()) {
            if (defined("__ENABLE_WEBSITE_MSG__") && __ENABLE_WEBSITE_MSG__)
                return __ENABLE_WEBSITE_MSG__;
            else
                return "Website is currently under maintenance.  Please check back later.";
        } else {
            return "";
        }
    }

    public static function get_submit_enabled() {
        if (!defined("__ENABLE_SUBMIT__") || (defined("__ENABLE_SUBMIT__") && __ENABLE_SUBMIT__))
            return true;
        else
            return false;
    }

    public static function get_release_status() {
        return (defined("__BETA_RELEASE__") && __BETA_RELEASE__) ? __BETA_RELEASE__ . " " : "";
    }

    public static function get_is_beta_release() {
        return (defined("__BETA_RELEASE__") && __BETA_RELEASE__) ? true : false;
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

    public static function family_resources_enabled() {
        return defined("__ENABLE_FAMILY_RESOURCES__") ? __ENABLE_FAMILY_RESOURCES__ : false;
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
    public static function get_gnt_database() {
        return defined("__EFI_GNT_DB_NAME__") ? __EFI_GNT_DB_NAME__ : "";
    }
    public static function get_cgfp_database() {
        return defined("__EFI_SHORTBRED_DB_NAME__") ? __EFI_SHORTBRED_DB_NAME__ : "";
    }

    public static function get_est_web_path() {
        return defined("__EST_WEB_PATH__") ? __EST_WEB_PATH__ : "efi-est";
    }
    public static function get_gnt_web_path() {
        return defined("__GNT_WEB_PATH__") ? __GNT_WEB_PATH__ : "efi-gnt";
    }
    public static function get_cgfp_web_path() {
        return defined("__CGFP_WEB_PATH__") ? __CGFP_WEB_PATH__ : "efi-cgfp";
    }
    public static function get_web_path($type) {
        $type = strtolower($type);
        if ($type === "est")
            return defined("__EST_WEB_PATH__") ? __EST_WEB_PATH__ : "efi-est";
        else if ($type === "gnt")
            return defined("__GNT_WEB_PATH__") ? __GNT_WEB_PATH__ : "efi-gnt";
        else if ($type === "cgfp")
            return defined("__CGFP_WEB_PATH__") ? __CGFP_WEB_PATH__ : "efi-cgfp";
        else if ($type === "taxonomy")
            return defined("__TAXONOMY_WEB_PATH__") ? __TAXONOMY_WEB_PATH__ : "taxonomy";
        else
            return "";
    }
    
    public static function get_est_results_dir() {
        if (is_dir(__EST_RESULTS_DIR__)) {
            return __EST_RESULTS_DIR__;
        }
        return false;
    }

    public static function get_auth_database() {
        return defined("__MYSQL_AUTH_DATABASE__") ? __MYSQL_AUTH_DATABASE__ : "";
    }

    public static function get_blast_module() {
        return defined("__BLAST_MODULE__") ? __BLAST_MODULE__ : "";
    }

    public static function get_global_citation() {
        return defined("__GLOBAL_CITATION__") ? __GLOBAL_CITATION__ : "";
    }

    public static function get_est_colorssn_suffix() {
        return defined("__EST_COLORSSN_SUFFIX__") ? __EST_COLORSSN_SUFFIX__ : "_coloredssn";
    }
}

