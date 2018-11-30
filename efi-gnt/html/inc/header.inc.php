<?php
include_once("../libs/settings.class.inc.php");

if (!isset($TopLevelUrl))
    $TopLevelUrl = "http://efi.igb.illinois.edu/efi-gnt/";

$title = "Genome Neighborhood Networks Tool";
if (isset($GnnId))
    $title .= ": Job #$GnnId";

if (isset($Is404Page) && $Is404Page)
    $title = "Page Not Found";

if (isset($IsExpiredPage) && $IsExpiredPage)
    $title = "Expired Job";

include_once("../../includes/login_check.inc.php");
include_once("../../html/inc/global_login_button.inc.php");

?>

<!doctype html>
<head>
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/js/jquery-ui-1.12.1/jquery-ui.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/shared.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/global.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/tabs.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/table.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/buttons.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/font-awesome/css/fontawesome-all.min.css">
<?php if (isset($TUTORIAL) && $TUTORIAL) { ?>
    <link rel="stylesheet" type="text/css" href="css/tutorial.css">
<?php } ?>
    <link rel="stylesheet" type="text/css" href="css/gnt.css">
    <link rel="shortcut icon" href="<?php echo $SiteUrlPrefix; ?>/images/favicon_efi.ico" type="image/x-icon">
    <title><?php echo $title; ?></title>

    <script src="<?php echo $SiteUrlPrefix; ?>/js/login.js" type="text/javascript"></script>
    <script src="<?php echo $SiteUrlPrefix; ?>/js/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script src="<?php echo $SiteUrlPrefix; ?>/js/jquery-ui-1.12.1/jquery-ui.js" type="text/javascript"></script>
    <script src="js/main.js" type="text/javascript"></script>
    <script src="js/upload.js" type="text/javascript"></script>
</head>

<body>
<?php
include("../../html/inc/global_nav.inc.php");
?>
    <div id="container">
        <div class="header">
            <div class="header_logo">
                <div class="logo_left"><a href="<?php echo $SiteUrlPrefix; ?>/efi-gnt/"><img src="images/efi-gnn_logo.png" width="250" height="75" alt="Enzyme Function Initiative Logo"></a></div>
                <div class="logo_right"><a href="http://igb.illinois.edu"><img src="<?php echo $SiteUrlPrefix; ?>/images/illinois_igb_full.png" height="75" alt="Institute for Genomic Biology at University of Illinois at Urbana-Champaign logo"></a></div>
            </div>
        </div>

        <div class="content_holder">
            <h1 class="ruled">Genome Neighborhood Network Tool</h1>
<?php if (settings::is_beta_release()) { ?>
            <div class="beta"><h4>BETA</h4></div>
<?php } ?>

