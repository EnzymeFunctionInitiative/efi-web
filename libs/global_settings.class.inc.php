<?php

class global_settings {

    public static function get_default_neighbor_size() {
        return __DEFAULT_NEIGHBOR_SIZE__;
    }

    public static function get_gnn_script() {
        return __GNN_SCRIPT__;
    }

    public static function get_process_diagram_script() {
        return __PROCESS_DIAGRAM_SCRIPT__;
    }

    public static function get_uploads_dir() {
        $dir =__UPLOAD_DIR__;
        if (is_dir($dir)) {
            return $dir;
        }
        return false;
    }

    public static function get_valid_file_type() {
        return __VALID_FILE_TYPE__;
    }

    public static function get_default_file_type($filetype) {
        $filetypes = explode(" ", __VALID_FILE_TYPE__);
        return $filetypes[0];
    }

    public static function get_output_dir() {
        if (is_dir(__OUTPUT_DIR__)) {
            return __OUTPUT_DIR__;
        }
        return false;
    }

    public static function get_diagram_output_dir() {
        if (is_dir(__DIAGRAM_OUTPUT_DIR__))
            return __DIAGRAM_OUTPUT_DIR__;
        return false;
    }

    public static function get_legacy_output_dir() {
        if (is_dir(__LEGACY_OUTPUT_DIR__)) {
            return __LEGACY_OUTPUT_DIR__;
        }
        return false;
    }

    public static function get_rel_output_dir() {
        return __RELATIVE_OUTPUT_DIR__;		
    }

    public static function get_legacy_rel_output_dir() {
        return __LEGACY_RELATIVE_OUTPUT_DIR__;		
    }

    public static function get_web_address() {
        return dirname($_SERVER['PHP_SELF']);
    }

    public static function get_web_root() {
        return __WEB_ROOT__;
    }

    public static function get_retention_days() {
        return __RETENTION_DAYS__;
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

}
?>
