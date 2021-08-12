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
        die("Unable to find $className ($filename does not exist).");
    }
});


