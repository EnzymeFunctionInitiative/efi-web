<?php

require_once("../../libs/global_settings.class.inc.php");

class settings extends global_settings {

    public static function get_rel_output_dir_legacy() {
        return __LEGACY_RELATIVE_OUTPUT_DIR__;
    }

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
        return defined("__EFI_DB_MODULE__") ? __EFI_DB_MODULE__ : __EFIDB_MODULE__;
    }

    public static function get_uniprot_version() {
        return __UNIPROT_VERSION__;
    }

    public static function get_est_url() {
        return __EST_URL__ ? __EST_URL__ : "#";
    }

    public static function get_diamond_enabled() {
        return defined("__ENABLE_DIAMOND__") ? __ENABLE_DIAMOND__ : false;
    }

    public static function get_metagenome_db_list() {
        return defined("__METAGENOME_DB_LIST__") ? __METAGENOME_DB_LIST__ : "";
    }

    public static function get_quantify_rel_output_dir() {
        return defined("__QUANTIFY_REL_OUTPUT_DIR__") ? __QUANTIFY_REL_OUTPUT_DIR__ : "";
    }

    public static function get_rel_http_output_dir() {
        return defined("__HTTP_OUTPUT_DIR__") ? __HTTP_OUTPUT_DIR__ : "";
    }

    public static function get_example_dir() {
        return defined("__EXAMPLE_SRC_DIR__") ? __EXAMPLE_SRC_DIR__ : "";
    }

    public static function get_example_web_path() {
        return defined("__EXAMPLE_WEB_PATH__") ? __EXAMPLE_WEB_PATH__ : "";
    }

    public static function get_shortbred_group() {
        return defined("__SHORTBRED_USER_GROUP__") ? __SHORTBRED_USER_GROUP__ : "";
    }

    public static function get_app_email() { // email for sending CGFP applications to
        return defined("__CGFP_APP_EMAIL__") ? __CGFP_APP_EMAIL__ : "";
    }
}
?>
