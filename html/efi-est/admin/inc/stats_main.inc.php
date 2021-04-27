<?php 
require_once(__DIR__."/../../../../conf/settings_paths.inc.php");

if (file_exists(__EST_DIR__ . "/conf/settings.inc.php")) {
    require_once(__EST_DIR__ . "/conf/settings.inc.php");
}
if (file_exists(__EST_DIR__ . "/libs")) {
    set_include_path(get_include_path() . ':' . __EST_DIR__ . "/libs");
}

require_once(__BASE_DIR__ . "/libs/database.class.inc.php");

require_once(__BASE_DIR__ . "/vendor/autoload.php");

if (defined("__BASE_WEB_PATH__"))
    $SiteUrlPrefix = __BASE_WEB_PATH__;
else
    $SiteUrlPrefix = "";

date_default_timezone_set(__TIMEZONE__);
$db = new database(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);
