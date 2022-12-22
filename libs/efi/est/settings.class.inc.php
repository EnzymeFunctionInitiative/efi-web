<?php
namespace efi\est;

require_once(__DIR__."/../../../init.php");

require_once(__EST_CONF_DIR__ . "/settings.inc.php");

use \efi\est\settings_shared;


class settings extends settings_shared {

    public static function get_max_seq($format = 0) {
        if ($format) {
            return number_format(__MAX_SEQ__, 0);
        } else {
            return __MAX_SEQ__;
        }
    }
    public static function get_max_blast_seq($format = 0) {
        if ($format) {
            return number_format(__MAX_BLAST_SEQ__, 0);
        } else {
            return __MAX_BLAST_SEQ__;
        }
    }
    public static function get_default_blast_seq($format = 0) {
        if ($format) {
            return number_format(__DEFAULT_BLAST_SEQ__, 0);
        } else {
            return __DEFAULT_BLAST_SEQ__;
        }
    }
    public static function get_maximum_full_family_count($format = 0) {
        if ($format) {
            return number_format(__MAX_FULL_FAMILY_COUNT__, 0);
        } else {
            return __MAX_FULL_FAMILY_COUNT__;
        }
    }
    public static function option_e_enabled() {
        return __ENABLE_E__;
    }
    public static function get_update_message() {
        return defined("__UPDATE_MESSAGE__") ? __UPDATE_MESSAGE__ : "";
    }

    public static function get_create_repnode_networks() {
        return defined("__DEFAULT_CREATE_REPNODE_NETWORKS__") ? __DEFAULT_CREATE_REPNODE_NETWORKS__ : true;
    }

    public static function get_js_version() {
        return defined("__JS_VERSION__") ? __JS_VERSION__ : "";
    }

}

