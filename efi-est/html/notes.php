<?php
require_once "../includes/main.inc.php";
require_once(__BASE_DIR__ . "/includes/login_check.inc.php");

require_once "inc/header.inc.php";

?>


<h2>Release Notes</h2>

<!--
<h3>April ZZZ, 2019</h3>

<p>
<?php echo get_db_size_text("2019_02", "73", 146106279, 559228); ?>
</p>
-->

<h3>April 6, 2019</h3>

<p>
The EFI-EST web tool has been enhanced with a tab-style interface.  Contents of the pages are now
grouped into logical tabs and data files can be downloaded without scrolling.  Job history is
now clearer, and color SSN jobs are assigned a unique color.
</p>

<h3>January 30, 2019</h3>

<p>
<?php echo get_db_size_text("2019_01", "72", 139694261, 559077); ?>
</p>

<h3>December 7, 2018</h3>

<p>
<?php echo get_db_size_text("2018_10", "71", 133507323, 558681); ?>
</p>

<p>
Users of the EST can now create GNNs and color SSNs directly from the EST network files download page.
</p>

<p>
In order to speed up computation for large families, UniRef50 or UniRef90 can be used to reduce the
number of sequences that are used to generate the SSNs.

As an example, the full set of sequences in family PF05544 is 10,914 sequences, but if the user
instructs the tool to use UniRef90, 4,198 UniRef90 cluster ID sequences representing the entire set of
sequences with a 90% sequence identity<sup>&dagger;</sup> will be used isntead.  Likewise, if
UniRef50 is used, 552 cluster ID sequences that represent the set of UniRef90 seed sequences with a 50%
sequence identity<sup>&dagger;</sup> will
be used in the computations.  The sequences represented by a cluster ID sequence are listed
as a node attribute in the SSN.  To use UniRef, the user must select the "Use UniRef 50/UniRef 90
cluster ID sequences instead of the full family" option.
</p>

<p>
<sup>&dagger;</sup> In addition to sharing a specific percent sequence identity to the longest
sequence in a cluster of sequences, UniRef seed sequences must also share 80% overlap.
The <a href="https://www.uniprot.org/uniref/">UniRef page at UniProt</a> further
discusses UniRef50 and UniRef90.
</p>

<h3>July 11, 2018</h3>

<p>
The EST database now uses UniProt release 2018_06 and InterPro 69.  UniProt release 2018_06 includes
a total of 116,587,823 entries: 116,030,110 in TrEMBL and 557,713 in SwissProt.
</p>

<p>
This database includes 16,712 Pfam famliies, 34,358 InterPro families, and 604 Pfam clans.
Tables of family sizes are available <a href="family_list.php">here</a>.
</p>

<h3>May 9, 2018</h3>

<p>
The EST database now uses UniProt release 2018_04 and InterPro 68.  UniProt release 2018_04 includes
a total of 115,316,915 entries: 114,759,640 in TrEMBL and 557,275 in SwissProt.
</p>

<p>
This database includes 16,712 Pfam famliies, 33,947 InterPro families, and 604 Pfam clans.
Tables of family sizes are available <a href="family_list.php">here</a>.
</p>

<h3>March 27, 2018</h3>

<p>
The EST database now uses UniProt release 2018_02 and InterPro 67.  UniProt release 2018_02 includes
a total of 109,414,541 entries: 108,857,716 in TrEMBL and 556,825 in SwissProt.
</p>

<p>
This database includes 16,712 Pfam famliies, 33,707 InterPro families, and 604 Pfam clans.
Tables of family sizes are available <a href="family_list.php">here</a>.
</p>

<h3>January 17, 2018</h3>

<p>
The EFI-EST and EFI-GNT tools were updated with the following changes:

<ul>
    <li>Users now can create a login that allows them to see their job history. This
        account is not required to use the EFI tools but it streamlines the process
        for accessing prior job runs.</li>
    <li>The EST and GNT tools are in the process of being integrated so that navigation
        and information flow is streamlined between the sites. The first step in this
        process is to create a global header that allows the user to switch between
        EST and GNT in addition to logging in and out.</li>
    <li>Information on the Pfam families and clans as well as InterPro families is
        now provided on the <a href="family_list.php">family information page</a>.
        The information that is provided includes family ID, name, size, and UniRef90 size.</li>
</ul>

<h3>December 15, 2017</h3>

<p>
The EST database now uses UniProt release 2017_11 and InterPro 66.  UniProt release 2017_11 includes 
a total of 99,261,416 entries:  98,705,220 in TrEMBL and 556,196 in SwissProt.
</p>

<p>
This database includes 16,712 Pfam families, 32,568 InterPro families, and 604 Pfam clans.   Lists of 
the families/clans are available along with the number number of sequences (full and UniRef90) can be 
accessed with the link below.  The reductions in the number of sequences when using UniRef90 cluster ID sequences
are provided; the time required for the BLAST is decreased by the sequence of this reduction.  Use of 
UniRef90 cluster ID sequences also allows SSNs to be generated for larger families/clans (305,000 sequence 
limit).
Tables of family sizes are available <a href="family_list.php">here</a>.
</p>

<p>
Support for Pfam clans has now been added to the Families option.
Pfam clans are collections of multiple Pfam families that define superfamilies.  The sequences in the 
families in a clan are not mutually exclusive.  A list of the families in each clans is available
<a href="family_list.php?filter=pfam-clan">here</a>.
Pfam clans can also be specified in the FASTA and Accession IDs options as supplementary sequences.
</p>

<p></p>

<p class="center"><a href="index.php"><button class="dark">Run EST</button></a></p>

<?php
function get_db_size_text($uniprot, $interpro, $trembl_size, $swissprot_size) {
    $total = $swissprot_size + $trembl_size;
    $total_fmt = number_format($total, 0);
    $swissprot_fmt = number_format($swissprot_size, 0);
    $trembl_fmt = number_format($trembl_size, 0);
    return <<<HTML
The EST database now uses UniProt release $uniprot and InterPro $interpro.  UniProt release $uniprot includes
a total of $total_fmt entries: $trembl_fmt in TrEMBL and $swissprot_fmt in SwissProt.
HTML;
}
?>


<?php require_once("inc/footer.inc.php"); ?>


