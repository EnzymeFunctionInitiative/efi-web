<?php
require_once(__DIR__."/../../init.php");

require_once(__DIR__."/inc/header.inc.php");

?>


<h2>Release Notes</h2>

<h3>June 19, 2019</h3>

<p>
The genome network diagram (GND) explorer has been updated.  InterPro families can now
be added as genome filters, and SwissProt annotations can be highlighted. The ability
to zoom in and out by scale (rather than genome window) is also present.
</p>

<h3>April 6, 2019</h3>

<p>
The GNT user interface has been updated.  The results page contains a tab-style interface
that logically groups results on the page and allows download of SSN and GNNs without
having to scroll the page past the submission information.
</p>

<h3>December 10, 2018</h3>

<p>
Users now have the ability to regenerate GNNs from an existing GNT job (only applicable to
GNT jobs started after 12/10/2018).  This allows the user to use a different neighborhood
size or cooccurrence to generate a GNN from an existing job, saving computation time.
</p>

<p>
The genomic neighborhood diagram viewer has the capability to graphically filter diagrams
by Pfam. The users can press the Ctrl button on the keyboard and click on an arrow and all
arrows with that particular family will be highlighted.
Users can now submit GNT jobs directly from the EST download page.
</p>

<h3>Previous Updates</h3>

<p>
To improve usability of the GNT, the various tools have been separated into separate tabs.
</p>

<?php if (settings::get_recent_jobs_enabled()) { ?>
<p>
Recently-ran jobs are now listed on the first tab of the GNT. This feature is enabled
the first time a new job is submitted.
</p>
<?php } ?>

<p>
When a GNN is created, the user now has the option of downloading the genomic neighborhood
diagram data file; this file can be uploaded and visualized using the new "View Saved Diagrams"
feature.
</p>

<p>
The user now has the option of downloading the displayed genomic neighborhood diagrams in SVG
format.  SVG is a vector graphics format that can be edited using Adobe Illustrator or
the free <a href="http://inkscape.org">InkScape editor</a>.  Those programs can be used to  
export publication-quality figures.
</p>

<p>
The ability to retrieve genomic neighborhood diagrams without creating and uploading
an SSN is now available on the "Retrieve Neighbhorhoods" tab. It is now possible to 
run a BLAST against an input sequence and retrieve neighborhoods for the sequences returned 
by the BLAST.  In addition, it is possible to submit FASTA sequences and/or files to retrieve
neighborhoods for the sequences that were detected in the FASTA headers.  Finally, lists
of UniProt or NCBI IDs can be uploaded and neighborhoods will be retrieved for any
recognized IDs.
</p>

<p></p>

<p class="center"><a href="index.php"><button class="dark">Run GNT</button></a></p>


<?php require_once(__DIR__."/inc/footer.inc.php"); ?>


