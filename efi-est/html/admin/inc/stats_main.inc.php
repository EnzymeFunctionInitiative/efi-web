<?php 

define("LOCAL_BASE", __DIR__ . "/../../..");
if (file_exists(LOCAL_BASE . "/conf/settings.inc.php")) {
    require_once(LOCAL_BASE . "/conf/settings.inc.php");
}
if (file_exists(LOCAL_BASE . "/libs")) {
    set_include_path(get_include_path() . ':' . LOCAL_BASE . "/libs");
}
if (defined("__PHPEXCEL_SRC__") && file_exists(__PHPEXCEL_SRC__)) {
    set_include_path(get_include_path() . ':' . __PHPEXCEL_SRC__);
}
if (defined("__JPGRAPH_SRC__") && file_exists(__JPGRAPH_SRC__)) {
    set_include_path(get_include_path() . ':' . __JPGRAPH_SRC__);
}
if (defined("__PEAR_SRC__") && file_exists(__PEAR_SRC__)) {
    set_include_path(get_include_path() . ':' . __PEAR_SRC__);
}

function my_autoloader($class_name) {
    if(file_exists(LOCAL_BASE . "/libs/" . $class_name . ".class.inc.php")) {
        require_once $class_name . '.class.inc.php';
    }
}

spl_autoload_register('my_autoloader');

date_default_timezone_set(__TIMEZONE__);
$db = new db(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);

?>
