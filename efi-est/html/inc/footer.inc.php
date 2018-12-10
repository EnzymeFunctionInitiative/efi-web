<?php

$feedbackMessage = "Need help or have suggestions or comments?   Please click here.";

if ((isset($Is404Page) && $Is404Page) || (isset($IsExpiredPage) && $IsExpiredPage)) {
    echo "</div>";
    if (isset($Is404Page) && $Is404Page)
        $feedbackMessage = "Please click here to report this.";
}

?>

            <p class="suggestions">
                <a href="http://enzymefunction.org/content/sequence-similarity-networks-tool-feedback" target="_blank"><?php echo $feedbackMessage; ?></a>
            </p>
        </div> <!-- content_holder -->

        <div class="footer_container">
            <div class="footer">
                <div class="address inline">
                    Carl R. Woese Institute for Genomic Biology<br>
                    University of Illinois at Urbana-Champaign<br>
                    1206 W. Gregory Drive Urbana, IL 61801<br>
                    <a href="mailto:efi@enzymefunction.org">efi@enzymefunction.org</a>
                </div>
                <div class="footer_logo inline">
                    <img alt="Enzyme Function Initiative logo" src="images/efi_logo.png" height="38"></a>
                </div>
                <div class="footer_logo inline">
                    <a href="http://www.nigms.nih.gov/" target="_blank"><img alt="NIGMS" src="<?php echo $SiteUrlPrefix; ?>/images/nighnew.png" style="width: 201px; height: 30px;"></a>
                </div>
            </div>
        </div>
    </div> <!-- container -->

<?php include("../../html/inc/global_login.inc.php"); ?>

</body>
</html>

