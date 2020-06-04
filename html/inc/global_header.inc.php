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

$IsDisabled = !global_settings::get_website_enabled();
if (!isset($IsAdminUser))
    $IsAdminUser = false;

if ($IsDisabled && !$IsAdminUser) {
    $BannerExtra = "";
    $BannerImagePath = "$SiteUrlPrefix/images/efi_logo.png";
    $title = "EFI Tools are Undergoing Maintenance";
}
if (!isset($IsLoginPage))
    $IsLoginPage = false;
$is_dev_site = global_settings::advanced_options_enabled();

?>

<!doctype html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/js/jquery-ui-1.12.1/jquery-ui.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/buttons.css?v=3">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/global.css?v=2">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/table.css?v=5">
<?php if (isset($LightweightTabs) && $LightweightTabs === true) { ?>
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/tabs.css?v=2">
<?php } ?>
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/jquery-custom.css?v=2">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/font-awesome/css/fontawesome-all.min.css">
<?php foreach ($StyleAdditional as $line) { echo "    $line\n"; } ?>

    <link rel="shortcut icon" href="<?php echo $SiteUrlPrefix; ?>/images/favicon_efi.ico" type="image/x-icon">
    <title><?php echo $title; ?></title>

    <script src="<?php echo $SiteUrlPrefix; ?>/js/login.js?v=2" type="text/javascript"></script>
    <script src="<?php echo $SiteUrlPrefix; ?>/js/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script src="<?php echo $SiteUrlPrefix; ?>/js/jquery-ui-1.12.1/jquery-ui.min.js" type="text/javascript"></script>
<?php if (isset($IncludePlotlyJs)) { ?>
    <script src="<?php echo $SiteUrlPrefix; ?>/js/plotly-1.42.5.min.js" type="text/javascript"></script>
<?php } ?>
<?php foreach ($JsAdditional as $line) { echo "    $line\n"; } ?>

<?php foreach ($HeaderAdditional as $line) { echo "    $line\n"; } ?>
<?php if (!$is_dev_site) { ?>
    <!-- Matomo -->
    <script type="text/javascript">
        var _paq = window._paq || [];
        /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
        _paq.push(['trackPageView']);
        _paq.push(['enableLinkTracking']);
        (function() {
            var u="//www-app.igb.illinois.edu/analytics/";
            _paq.push(['setTrackerUrl', u+'matomo.php']);
            _paq.push(['setSiteId', '3']);
            var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
            g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
        })();
    </script>
    <!-- End Matomo Code -->
<?php } ?>
</head>

<body>
<?php
if (!$IsDisabled || $IsAdminUser)
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
<?php if (global_settings::get_is_beta_release()) { ?>
            <div class="beta"><?php echo global_settings::get_release_status(); ?></div>
<?php } ?>
<?php
if ($IsDisabled && !$IsAdminUser && !$IsLoginPage) {
?>
<div id="update-message" class="update-message">
The Enzyme Function Initiative tools are evolving to improve user experience.

EFI-EST, GNT and CGFP will be unavailable during maintenance on Saturday April 6.

New submissions will not be allowed starting Friday April 5, 4 PM (North American Central Time Zone) until maintenance is complete.
</div>
<?php
    include(__DIR__ . "/global_footer.inc.php");
    exit(0);
}
?>
<?php if ($IsDisabled && !$IsLoginPage) { ?>
            <div class="beta"><big><i class="fas fa-exclamation-triangle"></i><br>The website is currently disabled for non-admin users or users who are not logged in.</big></div>
<?php } ?>


