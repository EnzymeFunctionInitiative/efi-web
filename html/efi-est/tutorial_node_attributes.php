<?php 
require_once(__DIR__."/../../conf/settings_paths.inc.php");
require_once(__EST_DIR__."/includes/main.inc.php");
require_once(__DIR__."/inc/header.inc.php");
require_once(__DIR__."/inc/tutorial_nav.inc.php");
?>

	<h3>Node Attributes</h3>
<p>
A major advantage of sequence similarity networks is the ability to include 
pertinent information for each individual protein (such as species, annotation, 
length, PDB deposition, etc.).  This information is included as “Node 
Attributes” which are searchable and sortable within the data panel in a 
sequence similarity network displayed in Cytoscape (see Figure 3 <a 
href='tutorial.php#ex3'>here</a>).
</p>

<p>
Also notice that if you right click (control+click on Mac) on any node in a 
network open in Cytoscape, you will get a sub-menu to carry out node-specific 
actions or access external links via LinkOut, such as to UniProtKB.
</p>

<p><img src='images/tutorial/node_attributes_Figure_1.png' width='700'></p>

<p>
Note that the EFI-EST web server uses data available in the UniProtKB database 
to populate node attribute fields.  Therefore, only information that is stored 
in UniProtKB is included in EFI-EST networks.  
Additional node attributes can be added within Cytoscape by using the BridgeDB
add-on.  Adding your own node attributes 
is useful for mapping annotations or other information that you have at hand, 
which is otherwise not available in UniProtKB.
</p>

<p>An introduction on how to use Cytoscape can be found <a href='tutorial_cytoscape.php'>here</a>.</p>

<p>
Difference node attributes are available for the various EFI-EST options:
</p>

<ul>
<li><a href="#default">Node attributes for EFI-EST Options A-D, with FASTA header reading for Option C</a></li>
<li><a href="#colorssn">Node attributes for EFI-EST Color SSN utility if an SSN was generated using
Options A-D, with FASTA header reading for Option C</a></li>
<li><a href="#nofasta">Node attributes for EFI-EST Option C without FASTA header reading and
Color SSN utility if Option C without FASTA header reading was used as input</a></li>
</ul>



<a name="default"></a>

<h4>Node Attributes for EFI-EST Options A-D</h4>

<table class="attributes">
<thead>
<th>Node Attribute</th>
<th>Description - Options A, B, C with FASTA header reading, D</th>
</thead>
<tbody>
<tr>
    <td>Name</td>
    <td>Full network - UniProt accession; Rep Node network - UniProt accession for the longest sequence in the representative node (seed sequence for CD-Hit)</td>
</tr>
<tr>
    <td>Shared name</td>
    <td>Full network - UniProt accession; Rep Node network - UniProt accession for the longest sequence in the representative node (seed sequence for CD-Hit)</td>
</tr>
<tr>
    <td>Number of IDs in Rep Node<sup>1</sup></td>
    <td>Number of UniProt IDs in the representative node</td>
</tr>
<tr>
    <td>List of IDs in Rep Node<sup>1</sup></td>
    <td>List of UniProt IDs in the representative node</td>
</tr>
<tr>
    <td>Sequence Source</td>
    <td>Options B, C, and D, “USER” if from user-supplied file, “FAMILY” if from user-specified Pfam/InterPro family, “USER+FAMILY” if from both</td>
</tr>
<tr>
    <td>Query IDs</td>
    <td>Options C and D, Input Query ID(s) that identified a UniProt match in the idmapping file</td>
</tr>
<tr>
    <td>Other IDs</td>
    <td>Option C, headers for FASTA sequences that could not identify a UniProt match in the idmapping file</td>
</tr>
<tr>
    <td>Cluster Number</td>
    <td>Number assigned to cluster, in order of decreasing number of sequences in the clusters (“999999” for singletons)</td>
</tr>
<tr>
    <td>Cluster Sequence Count</td>
    <td>Number of sequences in the cluster</td>
</tr>
<tr>
    <td>Node.fillColor</td>
    <td>Unique color assigned to cluster, in hexadecimal</td>
</tr>
<tr>
    <td>Organism</td>
    <td>organism genus/genera and species, from UniProt taxonomy.xml</td>
</tr>
<tr>
    <td>Taxonomy ID</td>
    <td>NCBI taxonomy identifier(s), from UniProt </td>
</tr>
<tr>
    <td>UniProt Annotation Stastus</td>
    <td>SwissProt - manually annotated; TrEMBL - automatically annotated; from UniProt</td>
</tr>
<tr>
    <td>Description</td>
    <td>protein name(s)/annotation(s), from UniProtKB</td>
</tr>
<tr>
    <td>SwissProt Description</td>
    <td>protein name(s)/annotation(s), from UniProtKB for SwissProt reviewed entries</td>
</tr>
<tr>
    <td>Sequence Length</td>
    <td>number(s) of amino acid residues, from UniProt</td>
</tr>
<tr>
    <td>Gene name</td>
    <td>gene name(s)</td>
</tr>
<tr>
    <td>NCBI IDs</td>
    <td>RefSeq/GenBank IDs and GI numbers, from UniProt idmapping</td>
</tr>
<tr>
    <td>Superkingdom</td>
    <td>domain of life of the organism, from UniProt taxonomy.xml</td>
</tr>
<tr>
    <td>Kingdom</td>
    <td>kingdom of the organism, from UniProt taxonomy.xml</td>
</tr>
<tr>
    <td>Phylum</td>
    <td>Phylogenetic phylum of the organism, from UniProt taxonomy.xml</td>
</tr>
<tr>
    <td>Class</td>
    <td>Phylogenetic class of the organism, from UniProt taxonomy.xml</td>
</tr>
<tr>
    <td>Order</td>
    <td>Phylogenetic order of the organism, from UniProt taxonomy.xml</td>
</tr>
<tr>
    <td>Family</td>
    <td>Phylogenetic family of the organism, from UniProt taxonomy.xml</td>
</tr>
<tr>
    <td>Genus</td>
    <td>Phylogenetic genus of the organism, from UniProt taxonomy.xml</td>
</tr>
<tr>
    <td>Species</td>
    <td>Phylogenetic species of the organism, from UniProt taxonomy.xml</td>
</tr>
<tr>
    <td>EC</td>
    <td>EC number, from UniProt</td>
</tr>
<tr>
    <td>PFAM</td>
    <td>Pfam family, from UniProt</td>
</tr>
<tr>
    <td>IPRO</td>
    <td>InterPro family, from UniProt</td>
</tr>
<tr>
    <td>PDB</td>
    <td>Protein Data Bank entry, from UniProt</td>
</tr>
<tr>
    <td>BRENDA ID</td>
    <td>BRENDA Database ID, from UniProt</td>
</tr>
<tr>
    <td>CAZY Name</td>
    <td>Carbohydrate-Active enZYmes (CAZy) family name(s), from UniProt</td>
</tr>
<tr>
    <td>GO Term</td>
    <td>Gene Ontology classification(s), from UniProt</td>
</tr>
<tr>
    <td>KEGG ID</td>
    <td>KEGG Database ID, from UniProt</td>
</tr>
<tr>
    <td>PATRIC ID</td>
    <td>PATRIC Database ID, from UniProt</td>
</tr>
<tr>
    <td>STRING ID</td>
    <td>STRING Database ID, from UniProt</td>
</tr>
<tr>
    <td>HMP Body Site</td>
    <td>location(s) of organism(s) in/on the body, if human microbiome organism, spreadsheet from HMP </td>
</tr>
<tr>
    <td>HMP Oxygen</td>
    <td>oxygen requirement(s), if human microbiome organism, spreadsheet from HMP</td>
</tr>
<tr>
    <td>P01 gDNA</td>
    <td>availability of gDNA(s) at EFI Protein Core, custom</td>
</tr>
</tbody>
</table>
<div>1 - These attributes are present only in Rep Node Networks</div>



<a name="colorssn"></a>

<h4>Node Attributes for EFI-EST Color SSN Utility</h4>

<table class="attributes">
<thead>
<th>Node Attribute</th>
<th>Description - Options A, B, C with FASTA header reading, D</th>
</thead>
<tbody>
<tr>
    <td>Name</td>
    <td>Full network - UniProt accession; Rep Node network - UniProt accession for the longest sequence in the representative node (seed sequence for CD-Hit)</td>
</tr>
<tr>
    <td>Shared name</td>
    <td>Full network - UniProt accession; Rep Node network - UniProt accession for the longest sequence in the representative node (seed sequence for CD-Hit)</td>
</tr>
<tr>
    <td>Number of IDs in Rep Node1</td>
    <td>Number of UniProt IDs in the representative node</td>
</tr>
<tr>
    <td>List of IDs in Rep Node1</td>
    <td>List of UniProt IDs in the representative node</td>
</tr>
<tr>
    <td>Sequence Source</td>
    <td>Options B, C, and D, “USER” if from user-supplied file, “FAMILY” if from user-specified Pfam/InterPro family, “USER+FAMILY” if from both</td>
</tr>
<tr>
    <td>Query IDs</td>
    <td>Options C and D, Input Query ID(s) that identified a UniProt match in the idmapping file</td>
</tr>
<tr>
    <td>Other IDs</td>
    <td>Option C, headers for FASTA sequences that could not identify a UniProt match in the idmapping file</td>
</tr>
<tr>
    <td>Cluster Number</td>
    <td>Number assigned to cluster, in order of decreasing number of sequences in the clusters (“999999” for singletons)</td>
</tr>
<tr>
    <td>Cluster Sequence Count</td>
    <td>Number of sequences in the cluster</td>
</tr>
<tr>
    <td>Node.fillColor</td>
    <td>Unique color assigned to cluster, in hexadecimal</td>
</tr>
<tr>
    <td>Organism</td>
    <td>organism genus/genera and species, from UniProt taxonomy.xml</td>
</tr>
<tr>
    <td>Taxonomy ID</td>
    <td>NCBI taxonomy identifier(s), from UniProt </td>
</tr>
<tr>
    <td>UniProt Annotation Stastus</td>
    <td>SwissProt - manually annotated; TrEMBL - automatically annotated; from UniProt</td>
</tr>
<tr>
    <td>Description</td>
    <td>protein name(s)/annotation(s), from UniProtKB</td>
</tr>
<tr>
    <td>SwissProt Description</td>
    <td>protein name(s)/annotation(s), from UniProtKB for SwissProt reviewed entries</td>
</tr>
<tr>
    <td>Sequence Length</td>
    <td>number(s) of amino acid residues, from UniProt</td>
</tr>
<tr>
    <td>Gene name</td>
    <td>gene name(s)</td>
</tr>
<tr>
    <td>NCBI IDs</td>
    <td>RefSeq/GenBank IDs and GI numbers, from UniProt idmapping</td>
</tr>
<tr>
    <td>Superkingdom</td>
    <td>domain of life of the organism, from UniProt taxonomy.xml</td>
</tr>
<tr>
    <td>Kingdom</td>
    <td>kingdom of the organism, from UniProt taxonomy.xml</td>
</tr>
<tr>
    <td>Phylum</td>
    <td>Phylogenetic phylum of the organism, from UniProt taxonomy.xml</td>
</tr>
<tr>
    <td>Class</td>
    <td>Phylogenetic class of the organism, from UniProt taxonomy.xml</td>
</tr>
<tr>
    <td>Order</td>
    <td>Phylogenetic order of the organism, from UniProt taxonomy.xml</td>
</tr>
<tr>
    <td>Family</td>
    <td>Phylogenetic family of the organism, from UniProt taxonomy.xml</td>
</tr>
<tr>
    <td>Genus</td>
    <td>Phylogenetic genus of the organism, from UniProt taxonomy.xml</td>
</tr>
<tr>
    <td>Species</td>
    <td>Phylogenetic species of the organism, from UniProt taxonomy.xml</td>
</tr>
<tr>
    <td>EC</td>
    <td>EC number, from UniProt</td>
</tr>
<tr>
    <td>PFAM</td>
    <td>Pfam family, from UniProt</td>
</tr>
<tr>
    <td>IPRO</td>
    <td>InterPro family, from UniProt</td>
</tr>
<tr>
    <td>PDB</td>
    <td>Protein Data Bank entry, from UniProt</td>
</tr>
<tr>
    <td>BRENDA ID</td>
    <td>BRENDA Database ID, from UniProt</td>
</tr>
<tr>
    <td>CAZY Name</td>
    <td>Carbohydrate-Active enZYmes (CAZy) family name(s), from UniProt</td>
</tr>
<tr>
    <td>GO Term</td>
    <td>Gene Ontology classification(s), from UniProt</td>
</tr>
<tr>
    <td>KEGG ID</td>
    <td>KEGG Database ID, from UniProt</td>
</tr>
<tr>
    <td>PATRIC ID</td>
    <td>PATRIC Database ID, from UniProt</td>
</tr>
<tr>
    <td>STRING ID</td>
    <td>STRING Database ID, from UniProt</td>
</tr>
<tr>
    <td>HMP Body Site</td>
    <td>location(s) of organism(s) in/on the body, if human microbiome organism, spreadsheet from HMP </td>
</tr>
<tr>
    <td>HMP Oxygen</td>
    <td>oxygen requirement(s), if human microbiome organism, spreadsheet from HMP</td>
</tr>
<tr>
    <td>P01 gDNA</td>
    <td>availability of gDNA(s) at EFI Protein Core, custom</td>
</tr>
</tbody>
</table>
<div>1 - These attributes are present only in Rep Node Networks</div>




<a name="nofasta"></a>
<h4>Node Attributes for EFI-EST Option C and Color SSN Utility without FASTA header reading</h4>
<table class="attributes">
<thead>
<th>Node Attribute</th>
<th>Description - Option C without FASTA header reading</th>
</thead>
<tbody>
<tr>
    <td>Name</td>
    <td>zzznnn, where nnn = number of the sequence in FASTA file</td>
</tr>
<tr>
    <td>Shared Name</td>
    <td>zzznnn, where nnn = number of the sequence in FASTA file</td>
</tr>
<tr>
    <td>Description</td>
    <td>FASTA Header </td>
</tr>
<tr>
    <td>Sequence Length</td>
    <td>Length of sequence in FASTA entry</td>
</tr>
</tbody>
</table>








<!--


<p><b>Rep Node Network Node Attributes</b></p>

<p><b>ACC (variable, list)</b> – UniProt accession(s) for the protein(s) 
<br><b>CAZY  (variable, list)</b> – CAZy family name(s) for the protein(s) 
<br><b>CLASS  (variable, list)</b> – Phylogenetic class(es) of the organism(s)
<br><b>Cluster Size (variable)</b> – number of proteins in the rep node
<br><b>Description  (variable, list)</b> – protein name(s)/annotation(s) in UniProtKB
<br><b>Domain  (variable, list)</b> – domain of life to which the organism(s) belong(s)
<br><b>EC (variable, list)</b> – the EC number(s) for the protein(s)
<br><b>EFI_ID  (6 digit, starting with 5, list)</b> – target ID(s) for the protein(s) from EFI-DB
<br><b>FAMILY  (variable, list)</b> – Phylogenetic family(ies) of the organism(s)
<br><b>GDNA  (true or false, list)</b> – availability of gDNA(s) from the AECOM Protein Core
<br><b>GENUS  (variable, list)</b> – Phylogenetic genus(i) of the organism(s)
<br><b>GI  (variable, list)</b> – GI numbers mapped to the protein(s)
<br><b>GN (variable, list)</b> – gene name(s) for the protein(s) 
<br><b>GO (variable, list)</b> – Gene Ontology classification(s) for the protein(s)
<br><b>HMP_Body_Site  (body site, list)</b> – if human microbiome species, the location(s) of the species in/on the body 
<br><b>HMP_Oxygen  (oxygen requirement, list)</b> – if human microbiome species, the oxygen requirement(s)
<br><b>IPRO (variable, list)</b> – InterPro family(ies) into which the protein(s) has been classified
<br><b>name (variable, list)</b> – UniProt accession for the longest sequence in the rep node
<br><b>ORDER  (variable, list)</b> – Phylogenetic order of the organism(s)
<br><b>Organism  (variable, list)</b> – organism genus(i) and species 
<br><b>PDB  (4 character, list)</b> – deposition code(s) for structures deposited in the Protein Data Bank
<br><b>PFAM  (variable, list)</b> – Pfam family(ies) into which the protein(s) has(have) been classified
<br><b>PHYLUM  (variable, list)</b> – Phylogenetic phylum(a) of the organism(s)
<br><b>Sequence_Length (variable, list)</b> – number(s) of amino acid residues in the protein(s)
<br><b>Shared name</b> – UniProt accession for the longest sequence in the rep node
<br><b>SPECIES  (variable, list)</b> – Phylogenetic species of the organism(s)
<br><b>STATUS  (unreviewed or reviewed, list)</b> – indicates if the annotation(s) were generated automatically and are found in TrEMBL (unreviewed) or manually annotated and are found in Swiss-Prot (reviewed)
<br><b>Taxonomy_ID  (variable, list)</b> – NCBI taxonomic identifier(s) for the organism(s)
<br><b>Uniprot_ID (variable, list)</b> – UniProt ID(s) for the protein(s)
<br><b>Swis-Prot reviewed entries (variable, list)</b> - Protein name/annotation in UniProtKB for SwissProt reviewed entries.

<p><b>Full Network Node Attributes</b></p>
<p><b>ACC (variable)</b> – UniProt accession for the protein
<br><b>CAZY  (variable)</b> – CAZy family name(s) for the protein
<br><b>CLASS  (variable)</b> – Phylogenetic class of the organism
<br><b>Description  (variable)</b> – protein name (annotation) in UniProtKB
<br><b>Domain  (variable)</b> – domain of life to which the organism belongs
<br><b>EC (variable)</b> – the EC number for the protein
<br><b>EFI_ID  (6 digit, starting with 5)</b> – target ID from EFI-DB
<br><b>FAMILY  (variable)</b> – Phylogenetic family of the organism
<br><b>GDNA  (true or false)</b> – availability of gDNA from the AECOM Protein Core
<br><b>GENUS  (variable)</b> – Phylogenetic genus of the organism
<br><b>GI  (variable)</b> – GI numbers mapped to the protein
<br><b>GN (variable)</b> – gene name for the protein
<br><b>GO (variable)</b> – Gene Ontology classification for the protein
<br><b>HMP_Body_Site  (body site)</b> – if a human microbiome species, the location of the species in/on the body 
<br><b>HMP_Oxygen  (oxygen requirement)</b> – if a human microbiome species, the oxygen requirement
<br><b>IPRO (variable)</b> – InterPro family(ies) into which the protein has been classified
<br><b>name (variable)</b> – UniProt accession
<br><b>ORDER  (variable)</b> – Phylogenetic order of the organism
<br><b>Organism  (variable)</b> – organism genus and species 
<br><b>PDB  (4 character)</b> – deposition code(s) for structures deposited in the Protein Data Bank
<br><b>PFAM  (variable)</b> – Pfam family(ies) into which the protein has been classified
<br><b>PHYLUM  (variable)</b> – Phylogenetic phylum of the organism
<br><b>SEQ  (variable)</b> – amino acid sequence of the protein
<br><b>Sequence_Length (variable)</b> – number of amino acid residues in the protein
<br><b>Shared name</b> – UniProt accession for the protein
<br><b>SPECIES  (variable)</b> – Phylogenetic species of the organism
<br><b>STATUS  (unreviewed or reviewed) </b> - indicates if the annotation was generated automatically and was found in TrEMBL (unreviewed) or manually annotated and found in Swiss-Prot (reviewed)
<br><b>Taxonomy_ID  (variable)</b> – NCBI taxonomic identifier for the organism
<br><b>Uniprot_ID (variable)</b> – UniProt ID for the protein
<br><b>Swis-Prot reviewed entries (variable)</b> - Protein name/annotation in UniProtKB for SwissProt reviewed entries.
<div>
  <div></div>
</div>
-->

<p>&nbsp;</p>

<center><a href="tutorial_cytoscape.php"><button type='submit' class='light'>Continue Tutorial</button></a></center>


<?php include_once('inc/tutorial_footer.inc.php'); ?>

<?php include_once('inc/footer.inc.php'); ?>
