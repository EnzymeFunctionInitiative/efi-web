<?php

$js_version = "";
if (isset($JsVersion))
    $js_version = $JsVersion;

if (!isset($StyleAdditional))
    $StyleAdditional = array();

array_push($StyleAdditional,
    '<link rel="stylesheet" type="text/css" href="../shared/css/table.css?v='.$js_version.'">',
);


if (!isset($JsAdditional)) $JsAdditional = array();

if (isset($IncludeSubmitJs) || isset($IncludeTaxonomyJs)) {
    array_push($JsAdditional,
        '<script src="../shared/js/taxonomy_filter.js?v='.$js_version.'" type="text/javascript"></script>',
        '<script src="'.$SiteUrlPrefix.'/vendor/efiillinois/taxonomy/web/js/taxonomy.js?v='.$js_version.'" type="text/javascript"></script>',
        '<script src="../js/form.js?v='.$js_version.'" type="text/javascript"></script>',
    );
}

if (isset($IncludeSubmitJs)) {
    array_push($JsAdditional,
        '<script src="../shared/js/archive.js?v='.$js_version.'" type="text/javascript"></script>',
        '<script src="../shared/js/family_size_helper.js?v='.$js_version.'" type="text/javascript"></script>',
        '<script src="../shared/js/id_helper.js?v='.$js_version.'" type="text/javascript"></script>',
        '<script src="../shared/js/file_helper.js?v='.$js_version.'" type="text/javascript"></script>',
        '<script src="../shared/js/archive.js?v='.$js_version.'" type="text/javascript"></script>',
        '<script src="../js/form.js?v='.$js_version.'" type="text/javascript"></script>',
    );
}

if (isset($IncludeSunburstJs) && $IncludeSunburstJs) {
    array_push($JsAdditional,
        '<script src="' . $SiteUrlPrefix . '/vendor/efiillinois/sunburst/web/js/sunburst-chart.min.js?v='.$js_version.'"></script>',
        '<script src="' . $SiteUrlPrefix . '/vendor/efiillinois/sunburst/web/js/sunburst-helpers.js" type="text/javascript"></script>',
        '<script src="' . $SiteUrlPrefix . '/vendor/efiillinois/sunburst/web/js/sunburst-taxonomy2.js?v='.$js_version.'" type="text/javascript"></script>',
        '<script src="' . $SiteUrlPrefix . '/vendor/efiillinois/sunburst/web/js/progress.js" type="text/javascript"></script>',
        '<script src="' . $SiteUrlPrefix . '/shared/js/sunburst.js?v='.$js_version.'" type="text/javascript"></script>',
    );
    array_push($StyleAdditional,
        '<link rel="stylesheet" type="text/css" href="' . $SiteUrlPrefix . '/vendor/efiillinois/sunburst/web/css/sunburst.css?v='.$js_version.'">',
        '<link rel="stylesheet" type="text/css" href="' . $SiteUrlPrefix . '/shared/css/sunburst.css?v='.$js_version.'">',
    );
}


