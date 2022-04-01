<?php

if (!isset($StyleAdditional)) $StyleAdditional = array();

array_push($StyleAdditional,
    '<link rel="stylesheet" type="text/css" href="../shared/css/table.css?v=3">',
);


if (!isset($JsAdditional)) $JsAdditional = array();
if (isset($IncludeSubmitJs)) {
    array_push($JsAdditional,
        '<script src="../shared/js/taxonomy_filter.js?v=6" type="text/javascript"></script>',
        '<script src="../shared/js/archive.js?v=1" type="text/javascript"></script>',
        '<script src="../shared/js/family_size_helper.js?v=7" type="text/javascript"></script>',
        '<script src="../shared/js/id_helper.js?v=1" type="text/javascript"></script>',
        '<script src="../shared/js/file_helper.js?v=1" type="text/javascript"></script>',
        '<script src="../shared/js/archive.js?v=2" type="text/javascript"></script>',
        '<script src="../js/form.js?v=1" type="text/javascript"></script>',
        '<script src="'.$SiteUrlPrefix.'/vendor/efiillinois/taxonomy/web/js/taxonomy.js?v=6" type="text/javascript"></script>',
    );
}

if (isset($IncludeSunburstJs) && $IncludeSunburstJs) {
    array_push($JsAdditional,
        '<script src="' . $SiteUrlPrefix . '/vendor/efiillinois/sunburst/web/js/sunburst-chart.min.js?v=1"></script>',
        '<script src="' . $SiteUrlPrefix . '/vendor/efiillinois/sunburst/web/js/sunburst-helpers.js" type="text/javascript"></script>',
        '<script src="' . $SiteUrlPrefix . '/vendor/efiillinois/sunburst/web/js/sunburst-taxonomy2.js?v=10" type="text/javascript"></script>',
        '<script src="' . $SiteUrlPrefix . '/vendor/efiillinois/sunburst/web/js/progress.js" type="text/javascript"></script>',
    );
    array_push($StyleAdditional,
        '<link rel="stylesheet" type="text/css" href="' . $SiteUrlPrefix . '/vendor/efiillinois/sunburst/web/css/sunburst.css?v=2">',
    );
}


