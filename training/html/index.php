<?php
require_once(__DIR__ . "/../includes/main.inc.php");
require_once(__BASE_DIR__ . "/libs/global_settings.class.inc.php");
require_once(__BASE_DIR__ . "/includes/login_check.inc.php");
require_once(__BASE_DIR__ . "/libs/ui.class.inc.php");

$NoAdmin = true;
require_once("inc/header.inc.php");

?>

<div class="tabs-efihdr ui-tabs ui-widget-content" id="main-tabs">
    <ul class="ui-tabs-nav ui-widget-header">
        <li class="ui-tabs-active"><a href="#video">Videos</a></li>
        <li><a href="#tutorial">Tutorials</a></li>
        <li><a href="#workshop">Workshops</a></li>
    </ul>

    <div>
        <div id="video" class="ui-tabs-panel ui-widget-content">
            <h3>Downloading and Viewing SSNs</h3>
            Download a Sequence Similarity Network from the EFI, import it into Cytoscape and apply a layout
            <iframe width="900" height="506" src="https://www.youtube.com/embed/ffLFhrD0-Ns" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

            <h3>Introduction to Cytoscape for SSNs</h3>
            Cytoscape basic orientation for working with Sequence Similarity Networks (SSNs)
            <iframe width="900" height="506" src="https://www.youtube.com/embed/s9epC5gv5Rw" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

            <h3>Viewing and Exporting Key Information</h3>
            Identify key information in a Sequence Similarity Network and create a publication ready Figure
            <iframe width="900" height="506" src="https://www.youtube.com/embed/2O_tWRi4EIM" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

            <h3>Defining Sub-clusters</h3>
            Define what is the minimum Alignment Score Threshold to use for separating subclusters in a SSN
            <iframe width="900" height="506" src="https://www.youtube.com/embed/4oF6iZadwRU" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

            <h3>Adding Metadata</h3>
            Adding custom node attributes to a SSN using the BridgeDB Cytoscape app
            <iframe width="900" height="506" src="https://www.youtube.com/embed/MUxfe7lAxsw" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

            <h3>All Videos in Playlist Format</h3>
            <iframe width="900" height="506" src="https://www.youtube.com/embed/videoseries?list=PLObj-axA0uwl6FEevGXCdOTU6gVXRfUbK" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
        </div>
        <div id="tutorial" class="ui-tabs-panel ui-widget-content">
        </div>
        <div id="workshop" class="ui-tabs-panel ui-widget-content">
        </div>
    </div>
</div>



<script>
    $(document).ready(function() {
        $("#main-tabs").tabs();
    }).tooltip();
</script>

<?php require_once("inc/footer.inc.php"); ?>


