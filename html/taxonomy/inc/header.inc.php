<?php
require_once(__DIR__."/../../../init.php");

require_once(__EST_CONF_DIR__."/settings.inc.php");

require_once(__DIR__."/../../shared/est_taxonomy_header.inc.php");

$Title = "EFI - Taxonomy Tool";
if (isset($EstId))
    $JobId = $EstId;

if (!isset($StyleAdditional))
    $StyleAdditional = array();
array_push($StyleAdditional,
    '<link rel="stylesheet" type="text/css" href="css/taxonomy.css?v=4">',
);

if (!isset($JsAdditional))
    $JsAdditional = array();
if (isset($IncludeSubmitJs)) {
    array_push($JsAdditional,
        '<script src="js/submit_app.js?v=5" type="text/javascript"></script>',
        '<script src="js/index_helpers.js?v=1" type="text/javascript"></script>',
    );
}
$BannerImagePath = "../efi-est/images/efiest_logo.png";

require_once(__BASE_DIR__ . "/html/inc/global_header.inc.php");

