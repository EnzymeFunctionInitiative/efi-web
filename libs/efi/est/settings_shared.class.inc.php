<?php
namespace efi\est;

require_once(__DIR__."/../../../init.php");

require_once(__EST_CONF_DIR__ . "/settings_shared.inc.php");


class settings_shared {

    public static function get_evalue() {
        return __EVALUE__;
    }
    public static function get_ascore_minimum() {
        return __MINIMUM__;
    }
    public static function get_ascore_maximum() {
        return __MAXIMUM__;
    }
    public static function get_default_fraction() {
        return __FRACTION_DEFAULT__;
    }
}

