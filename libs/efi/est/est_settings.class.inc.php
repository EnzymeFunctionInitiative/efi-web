<?php
namespace efi\est;

require_once(__DIR__ . "/../../../init.php");
require_once(__EST_CONF_DIR__ . "/settings.inc.php");


class est_settings {

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
    public static function get_evalue() {
        return __EVALUE__;
    }
    public static function option_e_enabled() {
        return __ENABLE_E__;
    }
}

