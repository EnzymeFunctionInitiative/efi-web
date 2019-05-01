<?php 
include_once '../includes/main.inc.php';
include_once 'inc/header.inc.php'; 
include_once 'inc/tutorial_nav.inc.php';
?>

<h3>Network File Download</h3>

<p>
The network file download page includes three tables.
</p>

<p>
The first displays a summary of the input chosen, and is used for record 
keeping.
</p>

<p>
<img src="images/tutorial/stepe_net_info_table.png" width="100%" alt="Summary of input for SSN generation" />
<p>

<p>
The following tables contain links to download networks, the representative 
node %ID, the number of nodes, the number of edges, and finally the file size. 
</p>

<p>
<img src="images/tutorial/stepe_net_download.png" width="100%" alt="Download of SSNs" />
</p>

<p>
The top table contains the "full" network created at your specified alignment 
score threshold. By default, this network contains all of the sequences/nodes 
in your input sequence set. However, this frequently results in very large 
files (~ 500 MB and greater) that will open and/or run very slowly, or not at 
all, on most laptop/desktop computers. As a very rough guide, generally 
Cytoscape networks with a few thousand nodes (protein sequences) and less than 
~ 500,000 edges can viewed, although this will depend on your computer. View 
this "full" network whenever possible, because it will provide access to 
annotation information for each node in your dataset. Full networks with 
greater than 10 million edges will not be generated.
</p>

<p>
In cases where the full network file is too large to open, the bottom table 
provides the ability to download “representative node” networks. In a 
representative node (rep node) network, sequences sharing &ge; a specified %ID are 
grouped into the same node using a program called CD-HIT
(<a href="tutorial_references.php">4</a>, <a href="tutorial_references.php">5</a>).
For example, 
90% ID rep node means that each node in the network will contain sequences that 
share &ge; 90% identity over ANY length of their amino acid sequences. The edges 
are drawn as done for a full network, except the longest sequence in the rep 
node is used to determine the alignment score between other rep nodes. For 
example, if your specified alignment score for the network output was 28, then 
edges are only drawn between representative nodes where the representative 
sequences share that alignment score or larger. Rep node networks are 
automatically calculated at 40, 45, 50, 55, 60, 65, 70, 75, 80, 85, 90, 95, and 
100% sequence identity to assure that you will be able to open one or more of 
the networks on your computer. The number of sequences contained within each 
rep node as well as the UniProt IDs for those sequences can be viewed in the 
Cytoscape node attributes panel.
</p>

<p>
Downloaded files are in the xgmml format and can be imported and viewed in 
Cytoscape by choosing File &rarr; Import &rarr; Network and selecting an xgmml file once 
you have started the Cytoscape program. For more information on using 
Cytoscape, please see the tutorials <a href="tutorial_cytoscape.php">here</a>.
</p>


<!--
<p><img src='images/tutorial/Network-File-Download_Figure1.jpg' width='700'></p>
<p>          The top table contains the &ldquo;full&rdquo; network created at your specified alignment score threshold. By default, this network contains all of the sequences/nodes in your input sequence set. However, this frequently results in very large files (~ 500 MB and greater) that will open and/or run very slowly, or not at all, on most laptop/desktop computers. As a very rough guide, generally Cytoscape networks with a few thousand nodes (protein sequences) and less than ~ 500,000 edges can viewed, although this will depend on your computer. View this "full" network whenever possible, because it will provide access to annotation information for each node in your dataset. Full networks with greater than 10 million edges will not be generated.</p>
<p>          In cases where the full network file is too large to open, the bottom table provides the ability to download &ldquo;representative node&rdquo; networks. In a representative node (rep node) network, sequences sharing &ge; a specified %ID are grouped into the same node using a program called CD-HIT (<strong><a href='tutorial_references.php'>4</a>, <a href='tutorial_references.php'>5</a></strong>). For example, 90% ID rep node means that each node in the network will contain sequences that share &ge;90% identity over ANY length of their amino acid sequences. The edges are drawn as done for a full network, except the longest sequence in the rep node is used to determine the alignment score between other rep nodes. For example, if your specified alignment score for the network output was 28, then edges are only drawn between representative nodes where the representative sequences share that alignment score or larger. Rep node networks are automatically calculated at 40, 45, 50, 55, 60, 65, 70, 75, 80, 85, 90, 95, and 100% sequence identity to assure that you will be able to open one or more of the networks on your computer. The number of sequences contained within each rep node as well as the UniProt IDs for those sequences can be viewed in the Cytoscape node attributes panel.</p>
<p>          Downloaded files are in the xgmml format and can be imported and viewed in Cytoscape by choosing File &#8594; Import &#8594; Network and selecting an xgmml file once you have started the Cytoscape program. For more information on using Cytoscape, please see the tutorials <a href="tutorial_cytoscape.php" target="_blank">here</a>. </p>
<p>&nbsp;</p>
<div>
  <div></div>
</div>
-->

<center><a href="tutorial_node_attributes.php"><button type='submit' class='light'>Continue Tutorial</button></a></center>

<?php include_once('inc/tutorial_footer.inc.php'); ?>

<?php include_once('inc/footer.inc.php'); ?>
