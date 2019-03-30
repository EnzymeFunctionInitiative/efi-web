<?php
require_once(__DIR__."/../../libs/global_settings.class.inc.php");

if (!isset($LoginText))
    $LoginText = "";
if (!isset($IsAdminUser))
    $IsAdminUser = false;
$isLoggedIn = false;
if (isset($IsLoggedIn))
    $isLoggedIn = $IsLoggedIn;

$UrlPrefix = "";
if (isset($SiteUrlPrefix))
    $UrlPrefix = $SiteUrlPrefix;

$useDashboard = defined("__USE_DASHBOARD__") && __USE_DASHBOARD__;
$IncludeShortBred = global_settings::get_shortbred_enabled();

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
<?php
if ($IsAdminUser) { ?>
<?php
    if (!isset($NoAdmin)) { ?>
                <li><a href="admin/" class="about">Admin</a></li>
<?php } ?>
                <li><a href="<?php echo $UrlPrefix; ?>/users/" class="user-mgmt">User Management</a></li>
<?php } ?>
                <li style="float:right"><?php echo $LoginText; ?></li>
<?php if ($isLoggedIn && $useDashboard) { ?>
                <li style="float:right"><a href="<?php echo $UrlPrefix; ?>/dashboard.php" class="about" title="Consolidated list of jobs across the EFI tools"><i class="fas fa-history"></i> Job History</a></li>
<?php } ?>
            </ul>
        </div>
    </div>
