<?php
require_once(__DIR__ . "/../../init.php");

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

<?php if (\efi\global_settings::get_is_beta_release()) { ?>
            <div class="beta"><?php echo \efi\global_settings::get_release_status(); ?></div>
<?php } ?>

<?php if (!$IsDisabled || $IsAdminUser) { // set in global_header ?>
<?php if (!isset($HideCiteUs)) { ?>
            <!--
            <p>
            Please cite your use of the EFI tools:<br><br>
            R&eacute;mi Zallot, Nils Oberg, and John A. Gerlt, <b>The EFI Web Resource for Genomic Enzymology Tools: Leveraging Protein, Genome, and Metagenome Databases to Discover Novel Enzymes and Metabolic Pathways</b>. Biochemistry 2019 58 (41), 4169-4182. <a href="https://doi.org/10.1021/acs.biochem.9b00735">https://doi.org/10.1021/acs.biochem.9b00735</a>
            </p>
            -->
<?php } ?>
<?php if (!isset($HideFeedback)) {?>
            <p class="suggestions">
                <a href="<?php echo $siteUrlPrefix; ?>/feedback.php"><?php echo $feedback_msg; ?></a>
            </p>
<?php } ?>
<?php } ?>
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
<?php include(__DIR__."/clipboard_footer.inc.php"); ?>

</body>
</html>

