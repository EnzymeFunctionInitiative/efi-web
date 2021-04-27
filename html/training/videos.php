<?php
require_once(__DIR__."/../../conf/settings_paths.inc.php");
require_once(__TRAINING_DIR__ . "/includes/main.inc.php");
require_once(__BASE_DIR__ . "/libs/global_settings.class.inc.php");
require_once(__BASE_DIR__ . "/includes/login_check.inc.php");
require_once(__BASE_DIR__ . "/libs/ui.class.inc.php");

$NoAdmin = true;
$ExtraTitle = "Videos";
require_once(__DIR__."/inc/header.inc.php");

$has_advanced_options = global_settings::advanced_options_enabled();

?>

<?php
$active_tab = "videos";
include("inc/tab_header.inc.php");
?>
<div id="videos" class="<?php echo $tab_class; ?>">

<p>
<a href="https://cytoscape.org/" target="_blank">Cytoscape</a> is used to visualize and analyze sequence similarity networks (SSNs) 
generated across the EFI website. Below, you will find videos that demonstrate 
the use of Cytoscape to analyze SSNs and explore their contents. The videos 
presented on this page are also accessible on the 
<a href="https://www.youtube.com/channel/UCShNWZLlJYevN2TlypX71ng" target="_blank">EFI YouTube channel</a>.
</p>



<h3 class="extra-margin">Downloading and initial loading of a SSN in Cytoscape</h3>
<p>
SSNs generated on the EFI website are provided in the XGMML file 
format (.xgmml extension). A SSN needs to be downloaded to the user's computer, 
and then loaded into Cytoscape. In Cytoscape a layout needs to be applied. The 
SSN is then ready for exploration.
</p>
<iframe width="900" height="506" src="https://www.youtube.com/embed/ffLFhrD0-Ns" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>



<h3 class="extra-margin">SSN exploration in Cytoscape: overview</h3>
<p>
Once a SSN is loaded in Cytoscape and a layout is applied,
sequences forming SSN clusters can be explored. The following video
provides an overview of Cytoscape use to explore the information
present in SSNs.
</p>
<p>
In a Sequence Similarity Network (SSN), Nodes represent protein
sequences (or sets of protein sequences). Edges connecting Nodes
are only present if the similarity between the Node sequences is
higher than a specified threshold (the Alignment Score Threshold)
defined when the SSN is created. Cytoscape has a "Table panel",
used to view the node and edge attributes within a SSN, as well as
a "Control panel" that can be used to "Select" (search) for
specific information.
</p>
<iframe width="900" height="506" src="https://www.youtube.com/embed/s9epC5gv5Rw" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>



<h3 class="extra-margin">Accessing key information and exporting a publication ready figure</h3>
<p>
SSNs generated using the EFI website contain a wealth of data that
can be explored. This video shows how to identify the key
information that is SwissProt annotations for sequences contained
in the SSN to evaluate it and create a publication ready figure.
</p>
<p>
In the Cytoscape "Control panel", the general style of networks can
be edited. The default styling can be changed to create images that
are suited as figures for publications. In this video the user will
see how to export such an image. Additionally, the user will learn
how to use SwissProt-annotated sequences for the basis of SSN
analysis; how to locate SwissProt-annotated nodes using the
"Select" function and how to ensure that these nodes are visible
using a depth parameter; how to view multiple SwissProt-annotated
nodes; how to locate and add text annotations to the network; how
to save a Cytoscape session for later use; and how to export a
final publication-ready figure.
</p>
<iframe width="900" height="506" src="https://www.youtube.com/embed/2O_tWRi4EIM" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>



<h3 class="extra-margin">Sub-clusters: choosing the minimal Alignment Score Threshold that would separate sub-clusters into clusters; Editing SSNs and the Color Utility</h3>
<p>
The Alignment Score Threshold is initially chosen may not segregate
the SSN into isofunctional clusters. This video shows how to use
daughter networks to explore clusters that contain more than one
SwissProt-annotated node, and to determine the minimal Alignment
Score Threshold that would allow the SwissProt-annotated nodes to
separate into their own clusters. Additionally, the user learns how
to edit networks and apply a new Alignment Score Threshold.
Finally, the Color SSN utility is demonstrated.
</p>
<iframe width="900" height="506" src="https://www.youtube.com/embed/4oF6iZadwRU" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>



<h3 class="extra-margin">Adding Custom Node Attributes to a SSN</h3>
<p>
<a href="https://www.bridgedb.org/" target="_blank">BridgeDB</a>
is a Cytoscape application allowing to add custom node attributes
to SSNs. This video shows how to install the BridgeDB Cytoscape app
and add a custom color attribute to nodes in a SSN using a simple
two-column text file. 
The first column contains IDs corresponding to those in the SSN and
the second colum containing the specific color for the node. The
user is also introduced to the "Passthrough mapping" function in
the Cytoscape style panel which is used for mapping the custom
color attribute to node color.
</p>
<iframe width="900" height="506" src="https://www.youtube.com/embed/MUxfe7lAxsw" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>



</div>
<?php include("inc/tab_footer.inc.php"); ?>



<script>
    $(document).ready(function() {
    }).tooltip();
</script>

<?php require_once(__DIR__."/inc/footer.inc.php"); ?>


