<?php
require_once("../libs/functions.class.inc.php");

$Title = "EFI - Enzyme Similarity Tool";
if (isset($EstId))
    $JobId = $EstId;

$StyleAdditional = array('<link rel="stylesheet" type="text/css" href="css/est.css">');
$JsAdditional = array();
if (isset($IncludeSubmitJs)) {
    array_push($JsAdditional, '<script src="js/submit.js" type="text/javascript"></script>',
        '<script src="js/family_size_helper.js" type="text/javascript"></script>');
}
$BannerImagePath = "images/efiest_logo.png";

include_once(__BASE_DIR__ . "/html/inc/global_header.inc.php");

?>

