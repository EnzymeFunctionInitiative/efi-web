<?php
require_once(__DIR__."/../../conf/settings_paths.inc.php");
require_once(__CGFP_DIR__ . "/conf/settings_shared.inc.php");

class cgfp_settings {

    public static function get_default_ref_db() {
        return defined("__DEFAULT_DB__") ? __DEFAULT_DB__ : "uniref90";
    }
    
    public static function get_default_cdhit_id() {
        return defined("__DEFAULT_CDHIT__") ? __DEFAULT_CDHIT__ : 85;
    }
    
    public static function get_default_identify_search() {
        return defined("__DEFAULT_IDENTIFY_SEARCH__") ? __DEFAULT_IDENTIFY_SEARCH__ : "DIAMOND";
    }
    
    public static function get_default_quantify_search() {
        return defined("__DEFAULT_QUANTIFY_SEARCH__") ? __DEFAULT_QUANTIFY_SEARCH__ : "USEARCH";
    }
    
}
?>
