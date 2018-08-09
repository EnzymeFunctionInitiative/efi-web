
<h3>Quantify Results</h3>

<div class="tutorial_figure">
<img src="tutorial/results.png" width="100%" alt="Image of the identify results page." />
</div>

<p>
When the abundance quantitation is completed, EFI-CGFP sends an e-mail to the 
user.  In the "Previous Jobs" tab, the job name will be changed to a link to 
the "Quantify Results" page.
</p>

<p>
The "Quantify Results" page provides nine downloads:  1) the SSN provided by 
the marker identification job to which a node attribute has been added for 
CD-HIT 85 seed sequence (meta)nodes ("Metagenomes Identified by Markers") that 
identified a match to its markers in one or more metagenome datasets ("SSN with 
quantify results"); 2) the zipped file for the same SSN; 3) a tab-delimited 
text file that provides raw protein abundance for all metagenome datasets; 4) a 
tab-delimited text file that provides raw cluster abundance for all metagenome 
datasets; 5) a tab-delimited text file that provides sum-normalized protein 
abundance for all metagenome datasets; 6) a tab-delimited text file that 
provides sum-normalized cluster abundance for all metagenome datasets; 7) a 
tab-delimited text file that provides average genome size (AGS) normalized 
protein abundance for all metagenome datasets; 8) a tab-delimited text file 
that provides average genome size (AGS) normalized cluster abundance for all 
metagenome datasets; and 9) a tab-delimited text file that identifies the seed 
sequences for the CD-HIT 85 clusters that were generated in the marker 
identification step and the sequences that are contained in each cluster 
("CD-HIT mapping file"; same file as provided in the marker quantification 
downloads).  Sum-normalization converts the raw values in reads per kilobase of 
sequence per million sample reads (RPKM) to relative abundances (sum=1).  
Alternatively, AGS normalized outputs have been converted from raw RPKM to 
counts per microbial genome.
</p>

<p>
In addition to the download files, this page also provides three heat maps that 
are accessed with the buttons (red arrow):
</p>

<ol>
    <li>
        "View Heatmap for Clusters":  a heat map in which each SSN cluster/metagenome hit pair is colored according to average genome abundance.
    </li>
    <li>
        "View Heatmap for Singletons":  a heat map in which each SSN singleton/metagenome hit pair is colored according to average genome abundance.
    </li>
    <li>
        "View Heatmap for Clusters and Singletons":  a heat map in which each SSN cluster or singleton/metagenome hit pair is colored according to average genome abundance.
    </li>
</ol>

<div class="tutorial_figure">
<img src="tutorial/heatmap.png" width="100%" alt="Image of the identify results page." />
</div>

<p>
A subset of clusters/singletons for display can be selected using the text 
input box below the heatmaps, by putting in a numerical range (e.g. 1-50).  The 
heatmaps can be manipulated and downloaded (as png files) using the tools that 
appear above and to the right of the heat map with mouse hovering.
</p>

<p>
The metagenomes are grouped according to body site so that trends/consensus 
across the six body sites can be easily discerned.
</p>

<p>
A list of the metagenomes that were used in the abundance quantification is 
also provided.
</p>

<p>
A button is provided to access the page with the marker identification results.
</p>

