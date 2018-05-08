<?php
require_once "../includes/main.inc.php";
require_once("../../includes/login_check.inc.php");

require_once "inc/header.inc.php";

?>


<h2>Release Notes</h2>

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
accessed with the link below.  The reductions in the number of sequences when using UniRef90 seed sequences 
are provided; the time required for the BLAST is decreased by the sequence of this reduction.  Use of 
UniRef90 seed sequences also allows SSNs to be generated for larger families/clans (305,000 sequence 
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


<?php require_once("inc/footer.inc.php"); ?>


