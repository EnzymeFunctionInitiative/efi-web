<?php 
require_once(__DIR__."/../../init.php");

include_once("inc/header.inc.php");
include_once("inc/tutorial_nav.inc.php");
?>

<h3>An Introduction to Using Cytoscape for EFI Networks</h3>


<p>
The following tutorial has been modified from the Cytoscape Wiki to specifically address working with networks created by the Enzyme Function Initative (EFI). To view Cytoscape 3's extensive tutorial pages, please go <a href="https://github.com/cytoscape/cytoscape-tutorials/wiki">here</a>.
</p>

<h4>Contents of this Tutorial</h4>

<ol>
  <li><a href="#download">Download Cytoscape</a></li>
  <li><a href="#initial">Initial Steps</a></li>
  <li><a href="#selecting">Selecting Nodes</a></li>
  <li><a href="#attributes">Node Attributes Data Panel</a></li>
  <li><a href="#searching">Searching</a></li>
  <li><a href="#styles">Changing Visual Styles</a></li>
  <li><a href="#filtering">Filtering a Network </a></li>
  <li><a href="#saving">Saving Sessions </a></li>
  <li><a href="#opening">Opening a Session File</a></li>
</ol>

<a name="download"></a>
<h4>1. Download Cytoscape</h4>

<p>
Go to <a href="http://www.cytoscape.org/download.html">http://www.cytoscape.org/download.html</a> and download a copy of Cytoscape. Check that your machine is running an up-to-date and correct (32-bit or 64-bit to match your Windows OS) version of Java Runtime Environment, to ensure successful installation of Cytoscape.
</p>

<a name="initial"></a>
<h4>2. Initial steps</h4>

<p>
Before starting, you will need a dataset in the form of an xgmml file. Here is an example file for you to begin with if you do not have one already. Launch Cytoscape. You should see a window that looks like this:
</p>

<img src="images/tutorial/Cytoscape_1.png" alt="Cytoscape Start Screen" width="100%" />
<br><i>Figure 1</i>. Cytoscape Start Screen

<p>
Load your network file into Cytoscape by selecting File &rarr; Import &rarr; Network &rarr; File... and navigating to the location of your .xgmml file. Once loaded, click OK on the Import Network popup window. The example file consists of 497 edges observed between 60 proteins, which you can see in the left-hand pane.
</p>

<p>
Now your screen should look like this:
</p>

<img src="images/tutorial/Cytoscape_2.png" alt="Network Load Unformatted Example" width="100%" />
<br><i>Figure 2</i>. Network Load Unformatted Example

<p>
The initial view is not informative, but this is normal. Under the Layout menu, select yFiles Layouts &rarr; Organic. After a brief calculation, your screen should look like this:
</p>

<img src="images/tutorial/Cytoscape_3.png" alt="Organic Layout Example" width="100%" />
<br><i>Figure 3</i>. Organic Layout Example
 
<a name="selecting"></a>
<h4>3. Selecting nodes</h4>

<p>
On the Cytoscape canvas (the blue window showing the network graphic), you can select single nodes by clicking with the mouse or multiple nodes by holding shift and clicking. You may also click and hold in the white space to drag out a rectangle which will select multiple nodes within a given area. Notice that if you right click (control+click on Mac) on any node, you will get a sub-menu to carry out node-specific actions or access external links, such as External Links &rarr; Sequences and Proteins &rarr; UniProt.
</p>

<a name="attributes"></a>
<h4>4. Node Attributes Data Panel</h4>

<p>
Notice that at the very bottom of this window is the Data Panel. It looks like this:
</p>

<img src="images/tutorial/Cytoscape_4.png" alt="Node Table Panel Example" width="100%" />
<br><i>Figure 4</i>. Node Table Panel Example

<p>
Select a few nodes and you will see protein-specific information ("Node Attributes") appear in the data table. You can use the icons on the upper left to choose which attributes to display. As shortcuts, the third icon and the fourth icon will display all or none of the attributes, respectively. Double click (single click on Windows) the top of any column to sort the attributes in numerical or alphabetical order. You may also detach the data panel by clicking the icon in the upper right hand corner.
</p>
 
<a name="searching"></a>
<h4>5. Searching</h4>

<p>
You can search by Node/Edge Attributes using the Select tab at the top of the Control Panel. Click the "plus sign" button and select "Column Filter". From the "Choose column..." dropdown menu, select the node or edge attribute by which you wish to filter (in the below example, Node: organism). At the bottom of the Control Panel, be sure to select "Apply Automatically" in order to watch your selection criteria filter the nodes and thus Table Panel information in real time. Type into the empty field your search criteria (example, Streptomyces) and the network will filter accordingly.
</p>

<img src="images/tutorial/Cytoscape_5.png" alt="Cytoscape Network Searching Example" width="100%" />
<br><i>Figure 5</i>. Cytoscape Network Searching Example

<p>
A node that matches the search will be highlighted (default, yellow) in the network and also appear in the Table Panel.
</p>
 
<a name="styles"></a>
<h4>6. Changing Visual Styles</h4>

<p>
There are many options for changing the network visually. Begin by selecting the Style tab of the Control Panel. By default the Style tab displays, and allows you to change basic visual properties such as node shape, line thickness, and backgroud color. While you can adjust the visual properties of any feature, below is the basic process of changing node Fill Color. 
</p>

<ol>
  <li>Within the Style tab properties list, click the arrow icon next to Fill Color to display a dropdown menu of options. </li>
  <li>The Fill Color dropdown menu now shows two options; select the Table Panel Column that you want to color by from the Column dropdown menu (e.g. organism).</li>
  <li>Below the Column option is Mapping Type; click the adjacent dropdown menu to select between Discrete Mapping and Passthrough Mapping. Discrete Mapping will allow you to select a different color for each node. Set Mapping Type to Discrete Mapping.</li>
  <li>A list of variables from the selected Table Panel Column will appear. Double-click the space next to each variable and then click the small icon with the ellipsis that appears. Select the color you prefer for that variable. For example, all nodes containing Streptomyces are colored lime green in the example network below.</li>
</ol>

<img src="images/tutorial/Cytoscape_6.png" alt="Cytoscape Node Coloring Example" width="100%" />
<br><i>Figure 6</i>. Cytoscape Node Coloring Example

<a name="filtering"></a>
<h4>7. Filtering a Network</h4>

<p>
There may be times when you want to view a network at an e-value more stringent than the one you used to generate the network. This can be done within Cytoscape instead of having to re-compute the network altogether.
</p>

<ol>
  <li>Open the network in Cytoscape and make a copy of the network: File &rarr; New &rarr; Network &rarr; Clone Current Network </li>
  <li>Open the Select tab of the Control Panel </li>
  <li>Delete previous filters by clicking the "X" to the left of the filter criteria </li>
  <li>Add a new filter by clicking the "plus sign" button, then select Column Filter</li>
  <li>Configure filter by selecting "Edge: -log<sub>10</sub>(<i>E</i>)" from the dropdown menu</li>
  <li>Set the upper and lower limits of the filter by moving the left and right arrows along the scroll bar - note: you cannot get any less stringent than the e-value at which the network was originally calculated (ie. leave the lower limit), but you can achieve greater stringency by increasing the upper limit</li>
  <li>If the Apply Automatically option is selected, the edges that do not satisfy the e-value stringency will turn red</li>
  <li>Delete red edges: Edit &rarr; Delete Selected Nodes and Edges </li>
  <li>Re-do the layout: Layout &rarr; yFiles &rarr; Organic </li>
</ol>

<p>
ALTERNATIVELY - if you deselect the "Apply Automatically" option, you can input the "-log<sub>10</sub>(<i>E</i>)" lower and upper limits manually with finer resolution and greater accuracy than using the scroll bar.
</p>

<a name="saving"></a>
<h4>8. Saving Sessions</h4>

<p>
Cytoscape can save all workspace states, including networks, attributes, visual styles, properties, and window sizes, into a session file (.cys). To save as a session, click the Save Session "floppy disk" icon on the toolbar and a .cys file will be saved.
</p>
 
<a name="opening"></a>
<h4>9. Opening a Session File</h4>

<p>
To open the session file, click the Open Session "file folder" icon on the toolbar. A warning pop-up window will be shown. Click OK and select a session file. By doing this, everything will be restored automatically from the file.
</p>

<center><a href="tutorial_references.php"><button type='submit' class='light'>Continue Tutorial</button></a></center>


<?php include_once("inc/tutorial_footer.inc.php"); ?>

<?php include_once("inc/footer.inc.php"); ?>

