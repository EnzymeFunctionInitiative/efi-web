<?php
require_once(__DIR__."/../../../init.php");

require_once(__TRAINING_CONF_DIR__."/settings.inc.php");

$Title = "EFI - Training Resources";
if (isset($ExtraTitle) && $ExtraTitle)
    $Title = "$Title: $ExtraTitle";

if (!isset($StyleAdditional))
    $StyleAdditional = array();
array_push($StyleAdditional, '<link rel="stylesheet" type="text/css" href="css/training.css?v=1">',
    '<link rel="stylesheet" type="text/css" href="'.$SiteUrlPrefix.'/css/tabs.css?v=2">',
    '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">');

$BannerImagePath = "$SiteUrlPrefix/images/efi_logo.png";

require_once(__BASE_DIR__ . "/html/inc/global_header.inc.php");

