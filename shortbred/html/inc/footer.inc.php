<?php

$feedbackMessage = "Need help or have suggestions or comments?   Please click here.";

if ((isset($Is404Page) && $Is404Page) || (isset($IsExpiredPage) && $IsExpiredPage)) {
    echo "</div>";
    if (isset($Is404Page) && $Is404Page)
        $feedbackMessage = "Please click here to report this.";
}

?>

<p>
This site uses the CGFP-ShortBRED programs (<a href="https://bitbucket.org/biobakery/cgfp/src">https://bitbucket.org/biobakery/cgfp/src</a>
and <a href="https://huttenhower.sph.harvard.edu/shortbred">https://huttenhower.sph.harvard.edu/shortbred</a>).
</p>
<p>For more information on CGFP-ShortBRED, see
<div style="margin-left: 20px;">
Levin, B. J., Huang, Y. Y., Peck, S. C., Wei, Y., Martínez-del Campo, A., Marks, J. A., Franzosa, E. A., Huttenhower, C., Balskus, E. P.
A prominent glycyl radical enzyme in human gut microbiomes metabolizes trans-4-hydroxy-l-proline.
<i>Science</i> <b>355</b>, eaai8386 (2017).
(DOI: <a href="http://dx.doi.org/10.1126/science.aai8386">10.1126/science.aai8386</a>)
</div>
</p>

<p>For more information on ShortBRED, see
<div style="margin-left: 20px;">
Kaminski J., Gibson M. K., Franzosa E. A., Segata N., Dantas G., Huttenhower C.
High-specificity targeted functional profiling in microbial communities with ShortBRED.
<i>PLoS Comput Biol.</i> 2015 Dec 18;11(12):e1004557. DOI: <a href="https://doi.org/10.1371/journal.pcbi.1004557">10.1371/journal.pcbi.1004557</a>
</div>
</p>

<p>These programs use data computed by <a href="https://github.com/snayfach/MicrobeCensus">MicrobeCensus</a>.
<div style="margin-left: 20px;">
Nayfach, S. and Pollard, K.S.
Average genome size estimation improves comparative metagenomics and sheds light on the functional ecology of the human microbiome.
<i>Genome Biology 2015;16(1):51</i>.
</div>
</p>

<p>
Portions of the metagenome data used on this site come from the <a href="http://hmpdacc.org">Human Microbiome Project</a>.

<div style="margin-left: 20px; margin-bottom: 20px; ">
The Human Microbiome Project Consortium. Structure, function and diversity of the healthy human microbiome. 
<i>Nature</i> <b>486</b>, 207-214 (14 June 2012). DOI: <a href="https://doi.org/10.1038/nature11234">10.1038/nature11234</a>
</div>

<div style="margin-left: 20px;">
The Human Microbiome Project Consortium.
A framework for human microbiome research.
<i>Nature</i> <b>486</b>, 215-221 (14 June 2012). DOI: <a href="https://doi.org/10.1038/nature11209">10.1038/nature11209</a>
</div>
</p>

<hr>

            <p class="suggestions">
                <a href="http://enzymefunction.org/content/sequence-similarity-networks-tool-feedback" target="_blank"><?php echo $feedbackMessage; ?></a>
            </p>
        </div> <!-- content_holder -->

        <div class="footer_container">
            <div class="footer">
                <div class="address inline">
                    Enzyme Function Initiative |
                    1206 W. Gregory Drive Urbana, IL 61801
                    &nbsp;|&nbsp; <a href="mailto:efi@enzymefunction.org">efi@enzymefunction.org</a>
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

