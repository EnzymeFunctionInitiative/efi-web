<?php

if (!isset($TopLevelUrl))
    $TopLevelUrl = "http://efi.igb.illinois.edu/efi-gnt/";

$title = "ShortBRED Utility";
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
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="css/shortbred.css">
    <link rel="shortcut icon" href="<?php echo $SiteUrlPrefix; ?>/images/favicon_efi.ico" type="image/x-icon">
<?php echo $extraCssLinkText; ?>

    <title><?php echo $title; ?></title>

    <script src="<?php echo $SiteUrlPrefix; ?>/js/login.js" type="text/javascript"></script>
    <script src="<?php echo $SiteUrlPrefix; ?>/js/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script src="<?php echo $SiteUrlPrefix; ?>/js/jquery-ui-1.12.1/jquery-ui.js" type="text/javascript"></script>
    <script src="js/submit.js" type="text/javascript"></script>
</head>

<body>
<?php
include("../../html/inc/global_nav.inc.php");
?>
    <div id="container">
        <div class="header">
            <div class="header_logo">
                A collaboration between the Institute for Genomic Biology (or should we say EFI?)
                and the Huttenhower Lab at the Harvard T.H. Chan School of Public Health.
                We need to make a collaborative logo.
            </div>
        </div>

        <div class="content_holder">
            <h1 class="ruled">ShortBRED</h1>
<?php if (settings::is_beta_release()) { ?>
            <div class="beta"><h4>BETA</h4></div>
<?php } ?>

