<?php
require_once(__DIR__."/../../../init.php");

require_once(__GNT_CONF_DIR__."/settings.inc.php");

$js_version = "";
if (isset($JsVersion))
    $js_version = $JsVersion;

$Title = "EFI - Genome Neighborhood Tool";
if (isset($GnnId))
    $JobId = $GnnId;

$StyleAdditional = array('<link rel="stylesheet" type="text/css" href="css/gnt.css?v='.$js_version.'">');
$JsAdditional = array('<script src="js/main.js?v='.$js_version.'" type="text/javascript"></script>',
    '<script src="js/upload.js?v='.$js_version.'" type="text/javascript"></script>');
$BannerImagePath = "images/efi-gnn_logo.png";

require_once(__BASE_DIR__ . "/html/inc/global_header.inc.php");


