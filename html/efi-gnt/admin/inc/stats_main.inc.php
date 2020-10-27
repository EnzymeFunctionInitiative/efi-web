<?php 
require_once(__DIR__."/../../../../conf/settings_paths.inc.php");
require_once(__GNT_DIR__ . "/conf/settings.inc.php");
require_once(__BASE_DIR__ . "/libs/database.class.inc.php");

if (defined("__BASE_WEB_PATH__"))
    $SiteUrlPrefix = __BASE_WEB_PATH__;
else
    $SiteUrlPrefix = "";

date_default_timezone_set(__TIMEZONE__);
$db = new database(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);
