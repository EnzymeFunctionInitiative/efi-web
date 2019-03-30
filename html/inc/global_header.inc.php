<?php

if (!isset($TopLevelUrl))
    $TopLevelUrl = __WEB_ROOT__;
$web_path = __WEB_PATH__;
$title = "EFI";
if (isset($Title))
    $title = $Title;
if (isset($JobId))
    $title .= ": Job #$JobId";
if (isset($Is404Page) && $Is404Page)
    $title = "Page Not Found";
if (isset($IsExpiredPage) && $IsExpiredPage)
    $title = "Expired Job";

include_once(__BASE_DIR__ . "/includes/login_check.inc.php");
include_once(__BASE_DIR__ . "/html/inc/global_login_button.inc.php");

if (!isset($HeaderAdditional))
    $HeaderAdditional = array();
if (!isset($StyleAdditional))
    $StyleAdditional = array();
if (!isset($JsAdditional))
    $JsAdditional = array();
if (!isset($BannerExtra))
    $BannerExtra = "";
if (!isset($BannerImagePath))
    $BannerImagePath = "images/efi_logo.png";


?>

<!doctype html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/js/jquery-ui-1.12.1/jquery-ui.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/buttons.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/global.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/table.css">
<?php if (isset($LightweightTabs) && $LightweightTabs === true) { ?>
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/tabs.css">
<?php } ?>
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/jquery-custom.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/font-awesome/css/fontawesome-all.min.css">
<?php foreach ($StyleAdditional as $line) { echo "    $line\n"; } ?>

    <link rel="shortcut icon" href="<?php echo $SiteUrlPrefix; ?>/images/favicon_efi.ico" type="image/x-icon">
    <title><?php echo $title; ?></title>

    <script src="<?php echo $SiteUrlPrefix; ?>/js/login.js" type="text/javascript"></script>
    <script src="<?php echo $SiteUrlPrefix; ?>/js/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script src="<?php echo $SiteUrlPrefix; ?>/js/jquery-ui-1.12.1/jquery-ui.min.js" type="text/javascript"></script>
<?php if (isset($IncludePlotlyJs)) { ?>
    <script src="<?php echo $SiteUrlPrefix; ?>/js/plotly-1.42.5.min.js" type="text/javascript"></script>
<?php } ?>
<?php foreach ($JsAdditional as $line) { echo "    $line\n"; } ?>

<?php foreach ($HeaderAdditional as $line) { echo "    $line\n"; } ?>
</head>

<body>
<?php
include(__DIR__ . "/global_nav.inc.php");
?>
    <div id="container">
        <div class="header">
            <div class="header-logo">
                <div class="logo-left"><a href="<?php echo $web_path; ?>"><img src="<?php echo $BannerImagePath; ?>" height="75" alt="Enzyme Function Initiative Logo"></a></div>
                <div class="logo-right"><a href="http://igb.illinois.edu"><img src="<?php echo $SiteUrlPrefix; ?>/images/illinois_igb_full.png" height="75" alt="Institute for Genomic Biology at University of Illinois at Urbana-Champaign logo"></a></div>
                <?php echo $BannerExtra; ?>
            </div>
        </div>

        <div class="content-holder">
            <h1 class="ruled"><?php echo $title; ?></h1>
<?php if (global_settings::is_beta_release()) { ?>
            <div class="beta"><h4>BETA</h4></div>
<?php } ?>

