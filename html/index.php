<?php
require_once("../includes/main.inc.php");
require_once("../libs/user_auth.class.inc.php");
require_once("../includes/login_check.inc.php");

require_once("inc/header.inc.php");

?>

<p style="margin-top: 30px">
This website contains a collection of tools for creating and interacting with sequence similarity
networks (SSNs) and genome neighborhood networks (GNNs).
These tools originated in the Enzyme Function Initiative, a NIH-funded research
project to develop a sequence / structure-based strategy for facilitating discovery of <i>in vitro</i>
enzymatic and <i>in vivo</i> metabolic / physiological functions of unknown enzymes discovered in
genome projects.  From the initial sequence- or PFam-family-based SSN creation tool, this website has
grown to include the following capabilities:
</p>

<ul>
    <li>SSN creation from <a href="/efi-est/#optionAtab">sequence BLAST</a>, 
        <a href="/efi-est/#optionBtab">PFam or InterPro famil(ies)</a>,
        <a href="/efi-est/#optionCtab">FASTA sequences</a>, and
        <a href="/efi-est/#optionDtab">UniProt and/or NCBI protein accession IDs</a>.
    </li>
    <li><a href="/efi-est/#colorssntab">Existing SSN coloring</a>.</li>
    <li>GNN creation from <a href="/efi-gnt/#create">uploaded SSNs</a>.</li>
    <li>Genome neighborhood diagrams from
        <a href="/efi-gnt/#diagrams">sequence BLAST, protein sequence ID lookup, or
        FASTA sequence lookup</a>.
</ul>

<p></p>

<?php if (isset($IncludeShortBred) && $IncludeShortBred) { ?>
<p style="margin-bottom: 60px">
In addition to the EFI utilities, this website also includes
<a href="/shortbred">high-level access to the ShortBRED program</a> developed by
<a href="https://huttenhower.sph.harvard.edu/shortbred">the Huttenhower Lab at the Harvard T.H.
Chan School of Public Health</a>.
The version of ShortBRED available at this website accepts a SSN as input and develops peptide
markers. The markers can be utilized in additional computations (also available on this website)
to quantify relative abundances of functional gene families in human metagenomes.
</p>
<?php } ?>


<?php require_once("inc/footer.inc.php"); ?>


