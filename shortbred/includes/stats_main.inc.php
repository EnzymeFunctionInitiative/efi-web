<?php 

if (file_exists(__DIR__."/../libs")) {
    set_include_path(get_include_path() . ':'.__DIR__.'/../libs');
}
if (file_exists(__DIR__."/../../libs")) {
    set_include_path(get_include_path() . ':'.__DIR__.'/../../libs');
}
if (file_exists(__DIR__."/../../../libs")) {
    set_include_path(get_include_path() . ':'.__DIR__.'/../../../libs');
}
if (file_exists(__DIR__."/PHPExcel/Classes")) {
    set_include_path(get_include_path() . ':'.__DIR__.'/PHPExcel/Classes');
}
if (file_exists(__DIR__."/../../includes/pear")) {
    set_include_path(get_include_path() . ':'.__DIR__.'/../../includes/pear');
}
if (file_exists(__DIR__."/../conf/settings.inc.php")) {
    require_once __DIR__.'/../conf/settings.inc.php';
}

function my_autoloader($class_name) {
    if(file_exists(__DIR__."/../../libs/" . $class_name . ".class.inc.php")) {
        require_once $class_name . '.class.inc.php';
    }
    elseif (file_exists("../libs/" . $class_name . ".class.inc.php")) {
        require_once $class_name . '.class.inc.php';
    }

}

spl_autoload_register('my_autoloader');

date_default_timezone_set(__TIMEZONE__);
$db = new db(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);

?>
