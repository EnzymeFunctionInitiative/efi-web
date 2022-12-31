<?php
namespace efi\gnt;

require_once(__DIR__."/../../../init.php");

use \efi\global_settings;


require_once(__GNT_CONF_DIR__."/settings.inc.php");


class settings extends global_settings {

    public static function get_default_neighbor_size() {
        return __DEFAULT_NEIGHBOR_SIZE__;
    }

    public static function get_gnn_script() {
        return __GNN_SCRIPT__;
    }
    
    public static function get_sync_gnn_script() {
        return __SYNC_GNN_SCRIPT__;
    }

    public static function get_process_diagram_script() {
        return __PROCESS_DIAGRAM_SCRIPT__;
    }

    public static function get_process_bigscape_script() {
        return __PROCESS_BIGSCAPE_SCRIPT__;
    }

    public static function get_uploads_dir() {
        $dir = __UPLOAD_DIR__;
        if (is_dir($dir)) {
            return $dir;
        }
        return false;
    }

    public static function get_gnd_uploads_dir() {
        $dir = __GND_UPLOAD_DIR__;
        if (is_dir($dir))
            return $dir;
        return false;
    }

    public static function get_valid_file_type($type = "") {
        return $type == "diagram" ? __VALID_DIAGRAM_FILE_TYPE__ : ($type == "id" ? __VALID_ID_FILE_TYPE__ : __VALID_FILE_TYPE__);
    }

    public static function get_default_file_type($type = "") {
        $filetypes = explode(" ", self::get_valid_filetype($type));
        return $filetypes[0];
    }

    public static function get_output_dir() {
        if (is_dir(__OUTPUT_DIR__)) {
            return __OUTPUT_DIR__;
        }
        return false;
    }

    public static function get_sync_output_dir() {
        if (defined("__SYNC_OUTPUT_DIR__") && is_dir(__SYNC_OUTPUT_DIR__))
            return __SYNC_OUTPUT_DIR__;
        else
            return false;
    }

    public static function get_rel_sync_output_dir() {
        if (defined("__RELATIVE_SYNC_OUTPUT_DIR__"))
            return __RELATIVE_SYNC_OUTPUT_DIR__;
        else
            return false;
    }

    public static function get_diagram_output_dir() {
        if (is_dir(__DIAGRAM_OUTPUT_DIR__))
            return __DIAGRAM_OUTPUT_DIR__;
        return false;
    }

    public static function get_rel_output_dir() {
        return __RELATIVE_OUTPUT_DIR__;
    }

    public static function get_rel_example_output_dir() {
        return __RELATIVE_EXAMPLE_OUTPUT_DIR__;
    }

    public static function get_rel_diagram_output_dir() {
        return __RELATIVE_DIAGRAM_OUTPUT_DIR__;
    }

    public static function get_web_address() {
        return dirname($_SERVER['PHP_SELF']);
    }

    public static function get_cgfp_web_root() {
        return defined("__CGFP_WEB_ROOT__") ? __CGFP_WEB_ROOT__ : "";
    }

    public static function get_retention_days() {
        return __RETENTION_DAYS__;
    }

    public static function get_default_cooccurrence() {
        return __COOCCURRENCE__;
    }

    public static function get_gnn_module() {
        return __EFI_GNT_MODULE__;
    }
    public static function get_efidb_module() {
        return defined("__EFI_DB_MODULE__") ? __EFI_DB_MODULE__ : __EFIDB_MODULE__;
    }

    public static function get_valid_diagram_file_types() {
        $filetypes = explode(" ", __VALID_DIAGRAM_FILE_TYPE__);
        return $filetypes[0];
    }

    public static function get_diagram_extension() {
        return "sqlite";
    }

    public static function get_diagram_upload_prefix() {
        return defined("__DIAGRAM_UPLOAD_PREFIX__") ? __DIAGRAM_UPLOAD_PREFIX__ : "";
    }

    public static function get_default_evalue() {
        return __DEFAULT_EVALUE__;
    }

    public static function get_max_blast_seq() {
        return __MAX_NUM_BLAST_SEQ__;
    }

    public static function get_default_blast_seq() {
        return __DEFAULT_NUM_BLAST_SEQ__;
    }

    public static function get_default_neighborhood_size() {
        return __DEFAULT_NEIGHBORHOOD_SIZE__;
    }

    public static function get_cluster_scheduler() {
        return __CLUSTER_SCHEDULER__;
    }

    public static function get_bigscape_enabled() {
        return defined("__ENABLE_BIGSCAPE__") && __ENABLE_BIGSCAPE__ ? true : false;
    }

    public static function get_interpro_enabled() {
        return defined("__ENABLE_INTERPRO__") ? __ENABLE_INTERPRO__ : false;
    }

    public static function get_num_diagrams_per_page() {
        return defined("__NUM_DIAGRAMS_PER_PAGE__") ? __NUM_DIAGRAMS_PER_PAGE__ : 50;
    }

    public static function get_superfamily_dir() {
        return defined("__SUPERFAMILY_DIR__") ? __SUPERFAMILY_DIR__ : "";
    }

    public static function get_realtime_output_dir() {
        return defined("__RT_OUTPUT_DIR__") ? __RT_OUTPUT_DIR__ : "";
    }

    public static function get_realtime_script() {
        return defined("__RT_GNN_SCRIPT__") ? __RT_GNN_SCRIPT__ : "";
    }

    public static function get_js_version() {
        return defined("__JS_VERSION__") ? __JS_VERSION__ : "";
    }

}


