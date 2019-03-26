<?php
require_once("../libs/functions.class.inc.php");

if (!isset($TopLevelUrl))
    $TopLevelUrl = "http://efi.igb.illinois.edu/efi-est/";

$title = "EFI - Enzyme Similarity Networks Tool";
if (isset($EstId))
    $title .= ": Job #$EstId";

if (isset($Is404Page) && $Is404Page)
    $title = "Page Not Found";

if (isset($IsExpiredPage) && $IsExpiredPage)
    $title = "Expired Job";

include(__BASE_DIR__ . "/html/inc/global_login_button.inc.php");

?>

<!doctype html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/js/jquery-ui-1.12.1/jquery-ui.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/shared.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/global.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/table.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/buttons.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/jquery-custom.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/font-awesome/css/fontawesome-all.min.css">
    <link rel="stylesheet" type="text/css" href="css/est.css">
    <link rel="shortcut icon" href="<?php echo $SiteUrlPrefix; ?>/images/favicon_efi.ico" type="image/x-icon">
    <title><?php echo $title; ?></title>

    <script src="<?php echo $SiteUrlPrefix; ?>/js/login.js" type="text/javascript"></script>
    <script src="<?php echo $SiteUrlPrefix; ?>/js/accordion.js" type="text/javascript"></script>
    <script src="<?php echo $SiteUrlPrefix; ?>/js/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script src="<?php echo $SiteUrlPrefix; ?>/js/jquery-ui-1.12.1/jquery-ui.min.js" type="text/javascript"></script>
<?php if (isset($IncludePlotlyJs)) { ?>
    <script src="<?php echo $SiteUrlPrefix; ?>/js/plotly-1.42.5.min.js" type="text/javascript"></script>
<?php } ?>
<?php if (isset($IncludeSubmitJs)) { ?>
    <script src="js/submit.js" type="text/javascript"></script>
    <script src="js/family_size_helper.js" type="text/javascript"></script>
<?php } else { /* ?>
    <script src="js/submit.js" type="text/javascript"></script>
    <script src="<?php echo $SiteUrlPrefix; ?>/js/custom-file-input.js" type="text/javascript"></script>
    <script src="js/family_size_helper.js" type="text/javascript"></script>
<?php */ } ?>
</head>

<body>
<?php
include("../../html/inc/global_nav.inc.php");
?>
    <div id="container">
        <div class="header">
            <div class="header_logo">
                <div class="logo_left"><a href="<?php echo $SiteUrlPrefix; ?>/efi-est/"><img src="images/efiest_logo.png" width="250" height="75" alt="Enzyme Function Initiative Logo"></a></div>
                <div class="logo_right"><a href="http://igb.illinois.edu"><img src="<?php echo $SiteUrlPrefix; ?>/images/illinois_igb_full.png" height="75" alt="Institute for Genomic Biology at University of Illinois at Urbana-Champaign logo"></a></div>
            </div>
        </div>

        <div class="content_holder">
            <h1 class="ruled"><?php echo $title; ?></h1>
<?php if (global_settings::is_beta_release()) { ?>
            <div class="beta"><h4>BETA</h4></div>
<?php } ?>

