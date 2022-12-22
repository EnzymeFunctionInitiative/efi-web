<?php
require_once(__DIR__."/../../../init.php");

use \efi\est\settings;

require_once(__EST_CONF_DIR__."/settings.inc.php");

$JsVersion = settings::get_js_version();

require_once(__DIR__."/../../shared/est_taxonomy_header.inc.php");


$Title = "EFI - Enzyme Similarity Tool";
if (isset($EstId))
    $JobId = $EstId;

if (!isset($StyleAdditional)) $StyleAdditional = array();

array_push($StyleAdditional,
    '<link rel="stylesheet" type="text/css" href="css/est.css?v='.$JsVersion.'">',
);

if (!isset($JsAdditional)) $JsAdditional = array();

if (isset($IncludeSubmitJs)) {
    array_push($JsAdditional,
        '<script src="js/submit_app.js?v='.$JsVersion.'" type="text/javascript"></script>',
        '<script src="js/index_helpers.js?v='.$JsVersion.'" type="text/javascript"></script>',
        '<script src="js/nas.js?v='.$JsVersion.'" type="text/javascript"></script>',
    );
}
$BannerImagePath = "images/efiest_logo.png";

require_once(__BASE_DIR__ . "/html/inc/global_header.inc.php");

