<?php
require_once(__DIR__."/../../../conf/settings_paths.inc.php");
require_once(__EST_DIR__."/libs/functions.class.inc.php");

$Title = "EFI - Enzyme Similarity Tool";
if (isset($EstId))
    $JobId = $EstId;

if (!isset($StyleAdditional))
    $StyleAdditional = array();
array_push($StyleAdditional, '<link rel="stylesheet" type="text/css" href="css/est.css?v=3">', '<link rel="stylesheet" type="text/css" href="css/table.css?v=3">');

if (!isset($JsAdditional))
    $JsAdditional = array();
if (isset($IncludeSubmitJs)) {
    array_push($JsAdditional, '<script src="js/submit.js?v=20" type="text/javascript"></script>',
        '<script src="js/family_size_helper.js?v=3" type="text/javascript"></script>',
        '<script src="js/nas.js?v=1" type="text/javascript"></script>');
}
$BannerImagePath = "images/efiest_logo.png";

require_once(__BASE_DIR__ . "/html/inc/global_header.inc.php");

?>

