
<h3>EFI-CGFP Web Tool Overview</h3>

<p>
The input for EFI-CGFP is a colored sequence similarity network (SSN) for a 
protein family. SSNs that segregate protein families into functional clusters, 
in which each cluster represents a different function, e.g., by separating the 
curated SwissProt-annotated entries into different clusters, are recommended.
</p>

<p>
To obtain SSNs compatible with EFI-CGFP analysis, users need to be familiar 
with both EFI-EST
(<a href="https://efi.igb.illinois.edu/efi-est/">https://efi.igb.illinois.edu/efi-est/</a>)
to generate SSNs for 
protein families and Cytoscape
(<a href="http://www.cytoscape.org/">http://www.cytoscape.org/</a>)
to visualize, 
analyze, and edit SSNs.  Users should also be familiar with the EFI-GNT web 
tool
(<a href="https://efi.igb.illinois.edu/efi-gnt/">https://efi.igb.illinois.edu/efi-gnt/</a>)
that colors SSN, and collects, 
analyzes, and represents genome neighborhoods for bacterial and fungal 
sequences in SSN clusters.
</p>

<p>
EFI-CGFP uses the ShortBRED algorithm described by Huttenhower and colleagues 
in two successive steps: 1) <b>identify</b> markers that are unique to members of 
clusters in the input SSN that share 85% sequence identity using the CD-HIT 
algorithm (CD-HIT 85 clusters) and 2) <b>quantify</b> the marker abundances in 
metagenome datasets.
EFI-CGFP provides heat maps that allow easy identification of the clusters that 
have metagenome hits as well as measures of metagenome abundance (hits per 
microbial genome in the metagenome sample).  EFI-CGFP also outputs several 
additional files, such as tab-delimited text files (can be opened in Excel) 
that provide actual and normalized values of both protein and cluster 
abundances, and an enriched SSN that provides a visual summary of the markers 
that were identified (in the CD-HIT 85 clusters) and the abundance mapping 
results.
</p>


