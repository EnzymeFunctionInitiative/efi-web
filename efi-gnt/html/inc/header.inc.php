<?php
include_once("../libs/settings.class.inc.php");

$Title = "EFI - Genome Neighborhood Tool";
if (isset($GnnId))
    $JobId = $GnnId;

$StyleAdditional = array('<link rel="stylesheet" type="text/css" href="css/gnt.css">');
$JsAdditional = array('<script src="js/main.js" type="text/javascript"></script>',
    '<script src="js/upload.js" type="text/javascript"></script>');
$BannerImagePath = "images/efi-gnn_logo.png";

include_once(__BASE_DIR__ . "/html/inc/global_header.inc.php");

?>

