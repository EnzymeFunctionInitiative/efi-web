<?php
require_once "../includes/main.inc.php";
require_once "inc/header.inc.php";

?>


<h2>Release Notes</h2>

<h3>December 15, 2017</h3>

<p>
The EST database now uses UniProt release 2017_11 and InterPro 66.  UniProt release 2017_11 includes 
a total of 99.261,416 entries:  98,705,220 in TrEMBL and 556,196 in SwissProt.
</p>

<p>
This database includes 16,712 Pfam families, 32,568 InterPro families, and 604 Pfam clans.   Lists of 
the families/clans are available along with the number number of sequences (full and UniRef90) can be 
accessed with the links.  The reductions in the number of sequences when using UniRef90 seed sequences 
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


