<?php
require_once(__DIR__."/../../init.php");

use \efi\global_settings;


// Global vars that may be used in other includes downstream.
if (!isset($IsAdminUser))
    $IsAdminUser = false;
$UrlPrefix = "";
if (isset($SiteUrlPrefix))
    $UrlPrefix = $SiteUrlPrefix;
$IncludeShortBred = global_settings::get_shortbred_enabled();

// vars that are local to this file
$is_logged_in = false;
if (isset($IsLoggedIn))
    $is_logged_in = $IsLoggedIn;
$use_dashboard = defined("__USE_DASHBOARD__") && __USE_DASHBOARD__;
$use_training = defined("__USE_TRAINING__") && __USE_TRAINING__;
$use_advanced = global_settings::advanced_options_enabled();
$show_family_resources = global_settings::family_resources_enabled();
$pub_text = $IsAdminUser ? "P" : "Publications";


$button_title = '<i class="fas fa-sign-in-alt"></i> Sign In';
$button_action = "#";
if (isset($IsLoggedIn) && $IsLoggedIn) {
    $button_title = '<i class="fas fa-sign-out-alt"></i> ' . $IsLoggedIn;
    $button_action = "#";
}


//$login_text = <<<HTML
//<div class="btn-group">
//    <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" title="$button_title"><i class="fas fa-bars"></i></button>
//    <div class="dropdown-menu dropdown-menu-right">
//        <button class="dropdown-item" type="button">$button_action</button>
//HTML;
//if (isset($IsLoggedIn) && $IsLoggedIn) {
//    $login_text .= <<<HTML
//        <button class="dropdown-item" type="button"><a href="$UrlPrefix/dashboard.php" class="about" title="Consolidated list of jobs across the EFI tools"><i class="fas fa-history"></i> Job History</a></button>
//HTML;
//}
//$login_text .= <<<HTML
//    </div>
//</div>
//HTML;

$login_text = <<<HTML
<div class="dropdown">
    <button class="dropbtn"><i class="fas fa-bars"></i></button>
    <div class="dropdown-content">
HTML;
if (isset($IsLoggedIn) && $IsLoggedIn) {
    $login_text .= <<<HTML
        <a class="about" id="logout-menu" href="$button_action">$button_title</a>
        <a class="about" id="logout-menu" href="$UrlPrefix/dashboard.php" title="Consolidated list of jobs across the EFI tools"><i class="fas fa-history"></i> Job History</a>
HTML;
} else {
    $login_text .= <<<HTML
        <a class="about" id="login-menu" href="$button_action">$button_title</a>
HTML;
}
$login_text .= <<<HTML
    </div>
</div>
HTML;

?>

    <div class="system-nav-container">
        <div class="system-nav">
            <ul>
                <li><a href="<?php echo $UrlPrefix; ?>/" class="about">About</a></li>
                <li><a href="<?php echo $UrlPrefix; ?>/efi-est/" class="est">EFI-EST</a></li>
                <li><a href="<?php echo $UrlPrefix; ?>/efi-gnt/" class="gnt">EFI-GNT</a></li>
<?php if (isset($IncludeShortBred) && $IncludeShortBred) { ?>
                <li><a href="<?php echo $UrlPrefix; ?>/efi-cgfp/" class="shortbred">EFI-CGFP</a></li>
<?php } ?>
                <li><a href="<?php echo $UrlPrefix; ?>/taxonomy/" class="taxonomy">Taxonomy</a></li>
<?php if ($show_family_resources) { ?>
                <li><a href="<?php echo $UrlPrefix; ?>/family_resources.php" class="fam-info">Superfamily Resources</a></li>
<?php } ?>
<?php
if ($IsAdminUser) { ?>
<?php
    if (!isset($NoAdmin)) { ?>
                <li><a href="admin/" class="about">Admin</a></li>
<?php } ?>
                <li><a href="<?php echo $UrlPrefix; ?>/users/" class="user-mgmt">Users</a></li>
<?php } ?>
                <li style="float:right"><?php echo $login_text; ?></li>
<?php if ($use_training) { ?>
                <li style="float:right"><a href="<?php echo $UrlPrefix; ?>/training/" class="about" title="Training Resources"><i class="fas fa-question"></i> Training</a></li>
<?php } ?>
            </ul>
            <div class="clear"></div>
        </div>
    </div>
