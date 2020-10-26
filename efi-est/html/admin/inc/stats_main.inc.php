<?php 

define("LOCAL_BASE", __DIR__ . "/../../..");
if (file_exists(LOCAL_BASE . "/conf/settings.inc.php")) {
    require_once(LOCAL_BASE . "/conf/settings.inc.php");
}
if (file_exists(LOCAL_BASE . "/libs")) {
    set_include_path(get_include_path() . ':' . LOCAL_BASE . "/libs");
}

require_once(__BASE_DIR__ . "/libs/database.class.inc.php");

require_once(__BASE_DIR__ . "/vendor/autoload.php");


date_default_timezone_set(__TIMEZONE__);
$db = new database(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);

?>
