
<h3>Marker Quantification/Metagenome Abundance</h3>


<div class="tutorial_figure">
<img src="tutorial/quantify.png" width="100%" alt="Image of the identify results page." />
</div>

<p>
When the marker identification step is completed, EFI-CGFP sends an e-mail to 
the user.  In the "Previous Jobs" tab, the job name will be changed to a link 
to the "Markers Computation Results" page.  This page provides four downloads:  
1) the input Colored SSN to which node attributes have been added containing 
the type and number of markers that were generated ("SSN with marker results"); 
2) the zipped file for the same SSN; 3) a tab-delimited text file ("Marker 
data") that provides the identities of and information about the markers that 
were generated; and 4) a tab-delimited text file that identifies the seed 
sequences for the CD-HIT 85 clusters that were generated and the sequences that 
are contained in each cluster ("CD-HIT mapping file").
</p>

<p>
The identify job SSN adds four additional node attributes to the input SSN:  1) 
"Seed Sequence" with the accession ID if the (meta)node is/contains the seed 
(longest) sequence for a CD-HIT 85 cluster; 2) "Seed Sequence Cluster(s)" with 
the accession ID of the Seed Sequence with which the accession ID(s) was(were) 
grouped with CD-HIT 85; 3) "Marker Types" for the markers identified in the 
seed sequences (True, Quasi, or Junction; see the ShortBRED article for 
details); and 4) "Number of Markers" with the number of markers identified in 
the seed sequence.
</p>

<p>
This page enables the user to select the Human Microbiome Project (HMP) 
metagenome datasets from a library of 380 datasets for healthy adult men and 
women from six body sites [stool, buccal mucosa (lining of cheek and mouth), 
supragingival plaque (tooth plaque), anterior nares (nasal cavity), tongue 
dorsum (surface), and posterior fornix (vagina)].  Subsets of the library can 
be selected using the "Search" boxes.
</p>

<p>
After the datasets are selected (red arrow; transferred from the left panel to 
the right panel), metagenome abundance is initiated with the "Quantify Marker" 
button (blue arrow).  On the "Previous Jobs" tab, the quantify job will appear 
with a list of the selected metagenomes.  (For the GRE family and the complete 
set of HMP metagenome datasets, quantitation of protein abundance takes ~15 
hrs.)
</p>

<p>
The "Existing Quantify Jobs" button is provided to access the pages for the 
metagenome quantification jobs/results generated with the markers.
</p>

<div class="tutorial_figure">
<img src="tutorial/quantify_upload.png" width="100%" alt="Image of the button for uploading new SSNs for the same identify results." />
</div>

<p>
In addition, after both the Identify and Quantify steps have been completed for 
an input SSN, this page allows the user to upload a different Colored SSN and 
map the existing protein/cluster abundances to the new SSN (using the "Upload 
SSN with Different Alignment Score" section; red arrow).  The new SSN must have 
been generated using the same EFI-EST job as the original SSN uploaded to 
EFI-CGFP and segregated with a different alignment score and/or a subset of the 
clusters.  This upload eliminates the need to re-run the Identify and Quantify 
steps so the results will be obtained in a much shorter period of time (minutes 
instead of days!).  Submitting an SSN will create a new job in the "Previous 
Jobs" panel (indicated as "Child" of the initial marker 
identification/metagenome quantification job) which functions independently of 
the original job but shares the Identify and Quantify data.  The results for 
the "Child" job will include heat maps for the clusters and singletons in the 
uploaded SSN as well as the SSN with marker and metagenome hit node attributes 
(next section).
</p>

