<?php

if (!isset($StyleAdditional)) $StyleAdditional = array();

array_push($StyleAdditional,
    '<link rel="stylesheet" type="text/css" href="../shared/css/table.css?v=3">',
);


if (!isset($JsAdditional)) $JsAdditional = array();
if (isset($IncludeSubmitJs)) {
    array_push($JsAdditional,
        '<script src="../shared/js/index_helpers.js?v=4" type="text/javascript"></script>',
        '<script src="../shared/js/family_size_helper.js?v=7" type="text/javascript"></script>',
        '<script src="../shared/js/id_helper.js?v=1" type="text/javascript"></script>',
        '<script src="'.$SiteUrlPrefix.'/vendor/efiillinois/taxonomy/web/js/taxonomy.js?v=6" type="text/javascript"></script>',
    );
}

