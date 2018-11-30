<?php

if (!isset($TopLevelUrl))
    $TopLevelUrl = "http://efi.igb.illinois.edu/$SiteUrlPrefix/shortbred/";

$title = "EFI-CGFP";
if (isset($ExtraTitle) && $ExtraTitle)
    $title = "$title: $ExtraTitle";

if (isset($Is404Page) && $Is404Page)
    $title = "Page Not Found";

if (isset($IsExpiredPage) && $IsExpiredPage)
    $title = "Expired Job";

$extraCssLinkText = "";
if (isset($ExtraCssLinks)) {
    $func = function($value) { return "    <link rel=\"stylesheet\" type=\"text/css\" href=\"$value\">\n"; };
    $extraCssLinkText = implode("", array_map($func, $ExtraCssLinks));
}

include_once("../../includes/login_check.inc.php");
include_once("../../html/inc/global_login_button.inc.php");

?>

<!doctype html>
<head>
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/js/jquery-ui-1.12.1/jquery-ui.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/shared.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/tabs.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/global.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/buttons.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/table.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/font-awesome/css/fontawesome-all.css">
    <link rel="stylesheet" type="text/css" href="css/shortbred.css">
    <link rel="stylesheet" type="text/css" href="css/popup.css">
    <link rel="shortcut icon" href="<?php echo $SiteUrlPrefix; ?>/images/favicon_efi.ico" type="image/x-icon">
<?php echo $extraCssLinkText; ?>

    <title><?php echo $title; ?></title>

    <script src="<?php echo $SiteUrlPrefix; ?>/js/login.js" type="text/javascript"></script>
    <script src="<?php echo $SiteUrlPrefix; ?>/js/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script src="<?php echo $SiteUrlPrefix; ?>/js/jquery-ui-1.12.1/jquery-ui.js" type="text/javascript"></script>
    <script src="js/submit.js" type="text/javascript"></script>
    <script src="js/multiselect.min.js" type="text/javascript"></script>

    <style>
        /* We're not using Bootstrap yet so these styles are meant to mimic the Boostrap classes in a crude way. */
        .row {
            display: flex;
        }

        .col-xs-5 {
            flex: 40%;
            padding: 5px;
        }

        .col-xs-2 {
            flex: 20%;
            padding: 5px;
        }
   
        .row select {
            width: 100%;
        }


    </style>
</head>

<body>
<?php
include("../../html/inc/global_nav.inc.php");
?>
    <div id="container">
        <div class="header">
            <div class="header_logo">
                <div class="logo_left"><a href="<?php echo $TopLevelUrl; ?>"><img src="<?php echo $SiteUrlPrefix; ?>/shortbred/images/efi_cgfp_logo.png" alt="EFI-CGFP Logo" height="75"></a></div>
                <div class="logo_right" style="padding-left: 10px"><a href="http://igb.illinois.edu"><img src="<?php echo $SiteUrlPrefix; ?>/images/illinois_igb_full.png" height="75" alt="Institute for Genomic Biology at University of Illinois at Urbana-Champaign logo"></a></div>
                <div class="logo_right">
                    <a href="https://www.microbialchemist.com/metagenomic-profiling/"><img src="<?php echo $SiteUrlPrefix; ?>/shortbred/images/harvard1.png" height="32" alt="Harvard University logo"></a><br>
                    <a href="http://huttenhower.sph.harvard.edu/shortbred"><img src="<?php echo $SiteUrlPrefix; ?>/shortbred/images/harvard2.png" height="32" alt="Harvard University T.H. Chan School of Public Health logo"></a>
                </div>
            </div>
        </div>

        <div class="content_holder">
            <h1 class="ruled">EFI-CGFP/ShortBRED</h1>
<?php if (settings::is_beta_release()) { ?>
            <div class="beta"><h4>BETA</h4></div>
<?php } ?>

