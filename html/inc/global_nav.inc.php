
<?php
$IncludeShortBred = false;
if (!isset($LoginText))
    $LoginText = "";

$UrlPrefix = "";
if (isset($SiteUrlPrefix))
    $UrlPrefix = $SiteUrlPrefix;

?>

    <div class="system-nav-container">
        <div class="system-nav">
            <ul>
                <li><a href="<?php echo $UrlPrefix; ?>/" class="about">About</a></li>
                <li><a href="<?php echo $UrlPrefix; ?>/efi-est/" class="est">EFI-EST</a></li>
                <li><a href="<?php echo $UrlPrefix; ?>/efi-gnt/" class="gnt">EFI-GNT</a></li>
<?php if (isset($IncludeShortBred) && $IncludeShortBred) { ?>
                <li><a href="<?php echo $UrlPrefix; ?>/shortbred/" class="shortbred">ShortBRED-CGFP</a></li>
<?php } ?>
                <li style="float:right"><?php echo $LoginText; ?></li>
            </ul>
        </div>
    </div>
