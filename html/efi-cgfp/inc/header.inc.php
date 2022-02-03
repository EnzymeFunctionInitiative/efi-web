<?php
require_once(__DIR__."/../../../init.php");

require_once(__CGFP_CONF_DIR__."/settings.inc.php");

$Title = "EFI - Computationally-Guided Functional Profiling";
if (isset($ExtraTitle) && $ExtraTitle)
    $title = "$Title: $ExtraTitle";

$StyleAdditional = array('<link rel="stylesheet" type="text/css" href="css/shortbred.css?v=2">',
    '<link rel="stylesheet" type="text/css" href="css/popup.css?v=2">');
if (isset($ExtraCssLinks)) {
    $func = function($value) { return "<link rel=\"stylesheet\" type=\"text/css\" href=\"$value\">\n"; };
    $StyleAdditional = array_merge($StyleAdditional, array_map($func, $ExtraCssLinks));
}
$JsAdditional = array('<script src="js/submit.js?v=3" type="text/javascript"></script>',
    '<script src="js/multiselect.min.js" type="text/javascript"></script>');

$BannerImagePath = "images/efi_cgfp_logo.png";
$BannerExtra = <<<HTML
                <div class="logo-right">
                    <a href="https://www.microbialchemist.com/metagenomic-profiling/"><img src="images/harvard1.png" height="32" alt="Harvard University logo"></a><br>
                    <a href="http://huttenhower.sph.harvard.edu/efi-cgfp"><img src="images/harvard2.png" height="32" alt="Harvard University T.H. Chan School of Public Health logo"></a>
                </div>
HTML;

include_once(__BASE_DIR__ . "/html/inc/global_header.inc.php");

