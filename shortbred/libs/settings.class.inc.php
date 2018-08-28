<?php

require_once("../../libs/global_settings.class.inc.php");

class settings extends global_settings {

    public static function get_identify_script() {
        return __IDENTIFY_SCRIPT__;
    }

    public static function get_quantify_script() {
        return __QUANTIFY_SCRIPT__;
    }

    public static function get_valid_file_type() {
        return __VALID_FILE_TYPE__;
    }

    public static function get_default_file_type($filetype) {
        $filetypes = explode(" ", __VALID_FILE_TYPE__);
        return $filetypes[0];
    }

    public static function get_email_footer() {
        return __EMAIL_FOOTER__;
    }

    public static function get_admin_email() {
        return __ADMIN_EMAIL__;
    }

    public static function get_timeout() {
        return __MAX_TIMEOUT__;
    }

    public static function get_cluster_user() {
        return __CLUSTER_USER__;
    }

    public static function get_num_processors() {
        return __NUM_PROCESSORS__;
    }

    public static function get_shortbred_blast_module() {
        return __SHORTBRED_BLAST_MODULE__;
    }

    public static function get_shortbred_diamond_module() {
        return __SHORTBRED_DIAMOND_MODULE__;
    }

    public static function get_efidb_module() {
        return __EFIDB_MODULE__;
    }

    public static function get_uniprot_version() {
        return __UNIPROT_VERSION__;
    }

    public static function get_est_url() {
        return __EST_URL__ ? __EST_URL__ : "#";
    }

    public static function get_metagenome_db_list() {
        if (defined("__METAGENOME_DB_LIST__")) {
            return __METAGENOME_DB_LIST__;
        } else {
            return "";
        }
    }

    public static function get_quantify_rel_output_dir() {
        if (defined("__QUANTIFY_REL_OUTPUT_DIR__")) {
            return __QUANTIFY_REL_OUTPUT_DIR__;
        } else {
            return "";
        }
    }

    public static function get_rel_http_output_dir() {
        if (defined("__HTTP_OUTPUT_DIR__")) {
            return __HTTP_OUTPUT_DIR__;
        } else {
            return "";
        }
    }
}
?>
