
<h3>Quantify Results</h3>

<div class="tutorial_figure">
<img src="tutorial/step_e.png" width="100%" alt="Image of the identify results page." />
</div>

<p>
When the abundance quantitation is completed, EFI-CGFP sends an e-mail to the 
user.  In the "Previous Jobs" tab, the job name will be changed to a link to 
the "Quantify Results" page.
</p>

<p>
When the abundance quantitation is completed, EFI-CGFP sends an e-mail to the user. In the "Previous Jobs" tab, the job name will be changed to a link to the "Quantify Results" page. 
</p>

<p>
A table summarizing the Job Information is provided at the top of the page. 
This information can be downloaded as a text file.
</p>

The next section provides three tabs to access files that can be downloaded:

<p>
<b>SSN and CDHIT files</b>: 1) the SSN provided by the marker identification job to 
which a node attribute has been added for CD-HIT 85 seed sequence (meta)nodes 
("Metagenomes Identified by Markers") that identified a match to its markers in 
one or more metagenome datasets ("SSN with quantify results"); 2) the zipped 
file for the same SSN; 3) a tab-delimited text file ("Marker data") that 
provides the identities of and information about the markers that were 
generated (identical to the file provided on the Identify Results page); and 4) 
a tab-delimited text file that identifies the seed sequences for the CD-HIT 85 
clusters that were generated and the sequences that are contained in each 
cluster (CD-HIT mapping file"; identical to the file provided on the Identify 
Results page).
</p>

<p>
<b>CGFP Output (using median method)</b>: The default is for ShortBRED to report the 
abundance of metagenome hits for CD-HIT clusters using the "median method". The 
numbers of metagenome hits identified by all of the markers for a CD-HIT 
consensus sequence are arranged in increasing numerical order; the value for 
the median marker is used as the abundance. This method assumes that the 
distribution of hits across the markers for CD-HIT consensus sequence is 
uniform (expected if the metagenome sequencing is "deep", i.e., multiple 
coverage). For seed sequences with an even number of markers, the average of 
the two "middle" markers is used as the abundance. 
The files for download include: 1) a tab-delimited text file that provides raw 
protein abundance for all metagenome datasets; 2) a tab-delimited text file 
that provides raw cluster abundance for all metagenome datasets; 3) a 
tab-delimited text file that provides sum-normalized protein abundance for all 
metagenome datasets; 4) a tab-delimited text file that provides sum-normalized 
cluster abundance for all metagenome datasets; 5) a tab-delimited text file 
that provides average genome size (AGS) normalized protein abundance for all 
metagenome datasets; and 6) a tab-delimited text file that provides average 
genome size (AGS) normalized cluster abundance for all metagenome datasets. 
Sum-normalization converts the raw values in reads per kilobase of sequence per 
million sample reads (RPKM) to relative abundances (sum=1). Alternatively, AGS 
normalized outputs have been converted from raw RPKM to counts per microbial 
genome.
</p>

<p>
<b>CGFP Output (using mean method)</b>: In the mean method for reporting abundances, 
the average value the abundances identified by the markers for each CD-HIT 
consensus sequence marker is used to report abundance. This method reports the 
presence of "any" hit for a marker for a seed sequence. An asymmetric 
distribution of hits a seed sequence with multiple markers is expected for 
"false positives", so the mean method should be used with caution.
</p>

<p>
The files for download include: 1) a tab-delimited text file that provides raw 
protein abundance for all metagenome datasets; 2) a tab-delimited text file 
that provides raw cluster abundance for all metagenome datasets; 3) a 
tab-delimited text file that provides sum-normalized protein abundance for all 
metagenome datasets; 4) a tab-delimited text file that provides sum-normalized 
cluster abundance for all metagenome datasets; 5) a tab-delimited text file 
that provides average genome size (AGS) normalized protein abundance for all 
metagenome datasets; and 6) a tab-delimited text file that provides average 
genome size (AGS) normalized cluster abundance for all metagenome datasets. 
Sum-normalization converts the raw values in reads per kilobase of sequence per 
million sample reads (RPKM) to relative abundances (sum=1). Alternatively, AGS 
normalized outputs have been converted from raw RPKM to counts per microbial 
genome.
</p>

<p>
The next section provides three tabs to access the heat maps.
Fields are provided below each heat map to 1) select specific clusters 
(individual cluster numbers separated by commas and/or a range of cluster 
numbers); 2) specify a minimum or maximum value for the abundance.
Also, check boxes 
are provided to select 1) a heat map using the mean method for reporting 
abundances ("use mean"); and 2) a black/white map showing presence/absence of 
"hits" ("display hits only"; easier to see low abundance hits).
Finally, check boxes are provided to filter by body site.
Above and to the right of the heat map tools for download and manipulation the 
heat map can be accessed by hovering and clicking. 
</p>

<div class="tutorial_figure">
<img src="tutorial/step_e_heatmap.png" width="100%" alt="Image of the heatmap." />
<hr>
<img src="tutorial/step_e_heatmap_filters.png" width="100%" alt="Image of the heatmap filter options." />
</div>

<p>
<b>Cluster Heatmap</b>: a heat map in which each SSN cluster/metagenome hit 
pair is colored according to average genome abundance. The metagenomes are 
grouped according to body site so that trends/consensus across the six body 
sites can be easily discerned. The default heat map is calculated using the 
median method to report abundances. 
</p>

<p>
The y-axis lists the SSN cluster numbers for which metagenome hits were 
identified; the x-axis lists the metagenome datasets selected on the Identify 
Results page. A color scale is provided on the right that provides the AGS 
normalize abundance of the number of gene copies for the "hit" per microbial 
genome in the metagenome sample.
</p>

<p>
<b>Singleton Heatmap</b>: a heat map in which each SSN singleton/metagenome hit 
pair is colored according to average genome abundance; the default heat map is 
calculated using the median method of report abundances. 
</p>

<p>
<b>Combined Heatmap</b>: a heat map in which each SSN cluster or singleton/metagenome 
hit pair is colored according to average genome abundance; the default heat map 
is calculated using the median method of report abundances. 
</p>

<p>
Finally, below the heat maps a list of the metagenomes that were used in the 
abundance quantification is provided.
</p>

<p>
A button is provided at the top of the page to access the marker identification 
results.
</p>

