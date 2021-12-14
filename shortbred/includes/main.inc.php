<?php
require_once(__DIR__."/../../init.php");

require_once(__DIR__."/../../conf/settings_paths.inc.php");

if (defined("__BASE_WEB_PATH__"))
    $SiteUrlPrefix = __BASE_WEB_PATH__;
else
    $SiteUrlPrefix = "";

require_once(__BASE_DIR__ . "/includes/error_helpers.inc.php");
