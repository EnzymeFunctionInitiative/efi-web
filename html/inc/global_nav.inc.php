<?php
require_once(__DIR__."/../../libs/global_settings.class.inc.php");

// Global vars that may be used in other includes downstream.
if (!isset($LoginText))
    $LoginText = "";
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
$pub_text = $IsAdminUser ? "P" : "Publications";

?>

    <div class="system-nav-container">
        <div class="system-nav">
            <ul>
<?php if ($use_advanced) { ?>
                <li><a href="<?php echo $UrlPrefix; ?>/" class="about"><?php echo $pub_text; ?></a></li>
<?php } ?>
                <li><a href="<?php echo $UrlPrefix; ?>/" class="about">About</a></li>
                <li><a href="<?php echo $UrlPrefix; ?>/efi-est/" class="est">EFI-EST</a></li>
                <li><a href="<?php echo $UrlPrefix; ?>/efi-gnt/" class="gnt">EFI-GNT</a></li>
<?php if (isset($IncludeShortBred) && $IncludeShortBred) { ?>
                <li><a href="<?php echo $UrlPrefix; ?>/efi-cgfp/" class="shortbred">EFI-CGFP</a></li>
<?php } ?>
<?php
if ($IsAdminUser) { ?>
<?php
    if (!isset($NoAdmin)) { ?>
                <li><a href="admin/" class="about">Admin</a></li>
<?php } ?>
                <li><a href="<?php echo $UrlPrefix; ?>/users/" class="user-mgmt">Users</a></li>
<?php } ?>
                <li style="float:right"><?php echo $LoginText; ?></li>
<?php if ($is_logged_in && $use_dashboard) { ?>
                <li style="float:right"><a href="<?php echo $UrlPrefix; ?>/dashboard.php" class="about" title="Consolidated list of jobs across the EFI tools"><i class="fas fa-history"></i> Job History</a></li>
<?php } ?>
<?php if ($use_training) { ?>
                <li style="float:right"><a href="<?php echo $UrlPrefix; ?>/training/" class="about" title="Training Resources"><i class="fas fa-question"></i> Training</a></li>
<?php } ?>
            </ul>
        </div>
    </div>
