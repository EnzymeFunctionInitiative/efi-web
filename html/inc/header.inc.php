<?php
require_once(__DIR__."/../../init.php");

require_once(__CONF_DIR__."/settings.inc.php");


$Title = "Enzyme Function Initiative Tools";
$extraStyle = '<link rel="stylesheet" type="text/css" href="css/main.css">';
if (isset($StyleAdditional))
    array_push($StyleAdditional, $extraStyle);
else
    $StyleAdditional = array($extraStyle);
$JsAdditional = array('<script src="js/main.js" type="text/javascript"></script>');
$BannerImagePath = "images/efi_logo.png";

require_once(__DIR__ . "/global_header.inc.php");

