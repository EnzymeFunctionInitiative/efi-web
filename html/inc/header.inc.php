<?php

$Title = "Enzyme Function Initiative Tools";
$extraStyle = '<link rel="stylesheet" type="text/css" href="css/main.css">';
if (isset($StyleAdditional))
    array_push($StyleAdditional, $extraStyle);
else
    $StyleAdditional = array($extraStyle);
$JsAdditional = array('<script src="js/main.js" type="text/javascript"></script>');
$BannerImagePath = "images/efi_logo.png";

include_once(__DIR__ . "/global_header.inc.php");

?>

