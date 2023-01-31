<?php
require_once(__DIR__ . "/vendor/autoload.php");
require_once(__DIR__ . "/conf/settings_paths.inc.php");
require_once(__DIR__ . "/conf/settings_base.inc.php");
require_once(__DIR__ . "/conf/settings_auth.inc.php");

//set_include_path(__DIR__ . "/libs");
//spl_autoload_extensions(".class.inc.php");
//spl_autoload_register();

// Set up autoloader
spl_autoload_register(function ($className) {
    $classFile = str_replace("\\", DIRECTORY_SEPARATOR, $className);
    $filename = __DIR__ . DIRECTORY_SEPARATOR . "libs" . DIRECTORY_SEPARATOR . $classFile . ".class.inc.php";
    if (is_readable($filename)) {
        require_once($filename);
    } else {
        if (defined("__ENABLE_DEBUG__") && __ENABLE_DEBUG__) {
            debug_print_backtrace();
            die("Unable to find $className ($filename does not exist).");
        } else {
            die("***PHP Error/ALR***");
        }
    }
});

require_once(__BASE_DIR__ . "/includes/debug_check.inc.php");

date_default_timezone_set(__TIMEZONE__);
$db = new \IGBIllinois\db(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);

if (defined("__BASE_WEB_PATH__"))
    $SiteUrlPrefix = __BASE_WEB_PATH__;
else
    $SiteUrlPrefix = "";

require_once(__BASE_DIR__ . "/includes/error_helpers.inc.php");

ini_set("upload_max_size", "30M");

