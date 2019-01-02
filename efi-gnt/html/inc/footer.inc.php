<?php

$feedbackMessage = "Need help or have suggestions or comments?   Please click here.";

if ((isset($Is404Page) && $Is404Page) || (isset($IsExpiredPage) && $IsExpiredPage)) {
    echo "</div>";
    if (isset($Is404Page) && $Is404Page)
        $feedbackMessage = "Please click here to report this.";
}

?>
            
            <p><center>If you use the EFI web tools, please <a href="../#citeus">cite us</a>.</center></p>

            <p class="suggestions">
                <a href="http://enzymefunction.org/content/sequence-similarity-networks-tool-feedback" target="_blank"><?php echo $feedbackMessage; ?></a>
            </p>
        </div> <!-- content_holder -->

        <div class="footer_container">
<?php include("../../html/inc/shared_footer.inc.php"); ?>
        </div>
    </div> <!-- container -->

<?php include("../../html/inc/global_login.inc.php"); ?>

</body>
</html>

