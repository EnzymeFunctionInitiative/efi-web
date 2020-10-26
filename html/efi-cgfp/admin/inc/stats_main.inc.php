<?php 
require_once(__DIR__."/../../../../conf/settings_paths.inc.php");

if (file_exists(__CGFP_DIR__ . "/conf/settings.inc.php")) {
    require_once(__CGFP_DIR__ . "/conf/settings.inc.php");
}
if (file_exists(__CGFP_DIR__ . "/libs")) {
    set_include_path(get_include_path() . ':' . __CGFP_DIR__ . "/libs");
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
    if(file_exists(__CGFP_DIR__ . "/libs/" . $class_name . ".class.inc.php")) {
        require_once $class_name . '.class.inc.php';
    }
}

spl_autoload_register('my_autoloader');

require_once(__BASE_DIR__ . "/libs/database.class.inc.php");

date_default_timezone_set(__TIMEZONE__);
$db = new database(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);

?>
