<?php
$siteUrlPrefix = "";
if (isset($SiteUrlPrefix))
    $siteUrlPrefix = $SiteUrlPrefix;

$feedback_msg = "Click here to contact us for help, reporting issues, or suggestions.";

if ((isset($Is404Page) && $Is404Page) || (isset($IsExpiredPage) && $IsExpiredPage)) {
    echo "</div>";
    if (isset($Is404Page) && $Is404Page)
        $feedback_msg = "Please click here to report this.";
}

?>

<?php if (global_settings::is_beta_release()) { ?>
            <div class="beta">Beta</div>
<?php } ?>

<?php if (!isset($HideCiteUs)) { ?>
            <p><center>If you use the EFI web tools, please <a href="../#citeus">cite us</a>.</center></p>
<?php } ?>
            <p class="suggestions">
                <a href="http://enzymefunction.org/content/sequence-similarity-networks-tool-feedback"><?php echo $feedback_msg; ?></a>
            </p>
        </div> <!-- content-holder -->

        <div class="footer-container">
            <div class="footer">
                <div class="address inline">
                    Carl R. Woese Institute for Genomic Biology<br>
                    University of Illinois at Urbana-Champaign<br>
                    1206 W. Gregory Drive Urbana, IL 61801<br>
                    <a href="mailto:efi@enzymefunction.org">efi@enzymefunction.org</a>
                </div>
                <div class="footer-logo inline">
                    <img alt="Enzyme Function Initiative logo" src="<?php echo $siteUrlPrefix; ?>/images/efi_logo.png" height="38"></a>
                </div>
                <div class="footer-logo inline">
                    <a href="http://www.nigms.nih.gov/" target="_blank"><img alt="NIGMS" src="<?php echo $siteUrlPrefix; ?>/images/nighnew.png" style="width: 201px; height: 30px;"></a>
                </div>
            </div>

        </div>
    </div> <!-- container -->

<?php include(__DIR__."/global_login.inc.php"); ?>

</body>
</html>

