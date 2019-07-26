
<?php 

require_once '../includes/main.inc.php';
require_once '../libs/settings.class.inc.php';
require_once '../libs/bigscape_job.class.inc.php';



$gnnId = "";
$gnnKey = "";
$cooccurrence = "";
$nbSize = "";
$maxNbSize = 20;
$gnnName = "";
$idKeyQueryString = "";
$windowTitle = "";
$uniprotIdModalFooter = "";
$uniprotIdModalHeader = "";
$uniprotIdModalText = "";
$unmatchedIdModalText = "";
$blastSequence = "";
$jobTypeText = "";

$isBigscapeEnabled = settings::get_bigscape_enabled();
$isInterProEnabled = settings::get_interpro_enabled();
$numDiagrams = settings::get_num_diagrams_per_page();
$isUploadedDiagram = false;
$supportsDownload = true;
$supportsExport = true;
$isDirectJob = false; // This flag indicates if the job is one that generated an arrow diagram from a single sequence BLAST'ed, list of IDs, or a list of FASTA sequences.
$hasUnmatchedIds = false;
$isBlast = false;
$bigscapeStatus = 0; # 0 = no bigscape, 1 = running bigscape, 2 = bigscape completed
$bigscapeType = "";
$showNewFeatures = false;

if ((isset($_GET['gnn-id'])) && (is_numeric($_GET['gnn-id']))) {
    $gnnKey = $_GET['key'];
    $gnnId = $_GET['gnn-id'];
    $gnn = new gnn($db, $gnnId);
    $cooccurrence = $gnn->get_cooccurrence();
    $nbSize = $gnn->get_size();
    $maxNbSize = $gnn->get_max_neighborhood_size();
    $gnnName = $gnn->get_filename();
    $dotPos = strpos($gnnName, ".");
    $gnnName = substr($gnnName, 0, $dotPos);
    
    if ($gnn->get_key() != $_GET['key']) {
        error404();
    }
    elseif (time() < $gnn->get_time_completed() + settings::get_retention_days()) {
        prettyError404("That job has expired and doesn't exist anymore.");
    }

    if ($isBigscapeEnabled)
        $bigscapeType = DiagramJob::GNN;

    $idKeyQueryString = "gnn-id=$gnnId&key=$gnnKey";
    $gnnNameText = "GNN <i>$gnnName</i>";
    $windowTitle = " for GNN $gnnName (#$gnnId)";
}
elseif (isset($_GET['upload-id']) && functions::is_diagram_upload_id_valid($_GET['upload-id'])) {
    $gnnId = $_GET['upload-id'];
    $gnnKey = $_GET['key'];

    $arrows = new diagram_data_file($gnnId);
    $key = diagram_jobs::get_key($db, $gnnId);

    if ($gnnKey != $key) {
        error404();
    }
    elseif (!$arrows->is_loaded()) {
        prettyError404("Oops, something went wrong. Please send us an e-mail and mention the following diagnostic code: $gnnId");
    }

    $gnnName = $arrows->get_gnn_name();
    $cooccurrence = $arrows->get_cooccurrence();
    $nbSize = $arrows->get_neighborhood_size();
    $maxNbSize = $arrows->get_max_neighborhood_size();
    $isDirectJob = $arrows->is_direct_job();

    if ($isBigscapeEnabled)
        $bigscapeType = DiagramJob::Uploaded;

    $idKeyQueryString = "upload-id=$gnnId&key=$gnnKey";
    $isUploadedDiagram = true;
    $gnnNameText = "filename <i>$gnnName</i>";
    $windowTitle = " for uploaded filename $gnnName";
}
elseif (isset($_GET['direct-id']) && functions::is_diagram_upload_id_valid($_GET['direct-id'])) {
    $gnnId = $_GET['direct-id'];
    $gnnKey = $_GET['key'];

    $arrows = new diagram_data_file($gnnId);
    $key = diagram_jobs::get_key($db, $gnnId);

    if ($gnnKey != $key) {
        error404();
    }
    elseif (!$arrows->is_loaded()) {
        error_log($arrows->get_message());
        prettyError404("Oops, something went wrong. Please send us an e-mail and mention the following diagnostic code: $gnnId");
    }

    $gnnName = $arrows->get_gnn_name();
    $isDirectJob = true;
    $isBlast = $arrows->is_job_type_blast();
    $unmatchedIds = $arrows->get_unmatched_ids();
    $uniprotIds = $arrows->get_uniprot_ids();
    $blastSequence = $arrows->get_blast_sequence();
    $jobTypeText = $arrows->get_verbose_job_type();;
    $nbSize = $arrows->get_neighborhood_size();
    $maxNbSize = $arrows->get_max_neighborhood_size();

    if ($isBigscapeEnabled)
        $bigscapeType = DiagramJob::Uploaded;

    $hasUnmatchedIds = count($unmatchedIds) > 0;

    #for ($i = 0; $i < count($uniprotIds); $i++) {
    foreach ($uniprotIds as $upId => $otherId) {
        if ($upId == $otherId)
            $uniprotIdModalText .= "<tr><td>$upId</td><td></td></tr>";
        else
            $uniprotIdModalText .= "<tr><td>$upId</td><td>$otherId</td></tr>";
    }

    for ($i = 0; $i < count($unmatchedIds); $i++) {
        $unmatchedIdModalText .= "<div>" . $unmatchedIds[$i] . "</div>";
    }

    $idKeyQueryString = "direct-id=$gnnId&key=$gnnKey";
    if ($gnnName) {
        $gnnNameText = "<i>$gnnName</i>";
        $windowTitle = " for $gnnName (#$gnnId)";
    } else {
        $gnnNameText = "job #$gnnId";
        $windowTitle = " for job #$gnnId";
    }
}
else {
    error404();
}


$supportsGeneGraphics = isset($_GET["gene-graphics"]);

if ($isBigscapeEnabled) {
    $bss = new bigscape_job($db, $gnnId, $bigscapeType);
    $bigscapeStatus = $bss->get_status();
}

$nbSizeDiv = "";
$cooccurrenceDiv = "";
$jobTypeDiv = "";
$jobIdDiv = "";
$js_version = "?v=3";

if ($isDirectJob) {
    $jobTypeDiv = $jobTypeText ? "<div>Job Type: $jobTypeText</div>" : "";
} else {
    $nbSizeDiv = $nbSize ? "<div>Neighborhood size: $nbSize</div>"  : "";
    $cooccurrenceDiv = $cooccurrence ? "<div>Co-occurrence: $cooccurrence</div>" : "";
}
$jobIdDiv = $gnnId ? "<div>Job ID: $gnnId</div>" : "";

?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">   
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="">
        <meta name="author" content="">

        <title>Genome Neighborhood Diagrams<?php echo $windowTitle; ?></title>

        <!-- Bootstrap core CSS -->
        <link href="<?php echo $SiteUrlPrefix; ?>/bs/css/bootstrap.min.css" rel="stylesheet">
        <link href="<?php echo $SiteUrlPrefix; ?>/bs/css/menu-sidebar.css" rel="stylesheet">
        <link href="<?php echo $SiteUrlPrefix; ?>/font-awesome/css/fontawesome-all.min.css" rel="stylesheet">
        <link rel="shortcut icon" href="images/favicon_efi.ico" type="image/x-icon">


        <!-- Custom styles for this template -->
        <link href="css/diagrams.css" rel="stylesheet">
        <link href="css/alert.css" rel="stylesheet">
<!--
        <script src="js/app.js" type="application/javascript"></script>
        <script src="js/arrows.js" type="application/javascript"></script>
-->
        <style>
            #header-logo { float: left; width: 175px; }
            #header-body { margin-left: 185px; overflow: hidden; height: 70px; }
            #header-body-title  { vertical-align: middle; line-height: normal; padding-left: 15px; }
            /*#header-body-title  { float: left; width: calc(100%-200px); display: inline-block; vertical-align: middle; line-height: normal; width: calc(100%-370px); }*/
            #header-job-info { width: 195px; }
            #header-job-info div { line-height: normal; }
        </style>

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>

    <body>

        <header class="header">
            <table style="width:100%;height:70px">
                <tr>
                    <td style="width: 175px">
                        <a href="index.php"><img
                            src="images/efignt_logo55.png" width="157" height="55" alt="EFI GNT Logo" style="margin-left:10px;" /></a>
                    </td>
                    <td id="header-body-title" class="header-title">
                        Genome Neighborhood Diagrams for <?php echo $gnnNameText; ?>
                    </td>
                    <td id="header-job-info">
                        <?php echo $jobTypeDiv; ?>
                        <?php echo $jobIdDiv; ?>
                        <?php echo $cooccurrenceDiv; ?>
                        <?php echo $nbSizeDiv; ?>
                    </td>
                </tr>
            </table>
        </header>

        <!-- Begin page content -->
        <div id="wrapper" class="">
            <div id="sidebar-wrapper">
                <ul class="sidebar-nav">
                    <li>
                        <i class="fas fa-search" aria-hidden="true"> </i> <span class="sidebar-header">Search</span>
                        <div id="advanced-search-panel">
<?php if ($isDirectJob) { ?>
                            <div style="font-size:0.9em">Input specific UniProt IDs to display only those diagrams.</div>
<?php } else { ?>
                            <div style="font-size:0.9em">Input multiple clusters and/or individual UniProt IDs.</div>
<?php } ?>
                            <textarea id="advanced-search-input"></textarea>
                            <button type="button" class="btn btn-light" id="advanced-search-cluster-button">Query</button>
<?php if ($isDirectJob) { ?>
                            <button type="button" class="btn btn-light" id="advanced-search-reset-button">Reset View</button>
<?php } ?>
                        </div>
                    </li>
                    <li>
                        <div class="initial-hidden">
                            <i class="fas fa-filter" aria-hidden="true"> </i> <span class="sidebar-header">Filtering</span>
                            <div class="filter-cb-div" id="filter-container-tabs">
                                <div class="tooltip-text" id="filter-anno-toggle-text">
                                    <input id="filter-anno-toggle" type="checkbox" />
                                    <label for="filter-anno-toggle"><span>Show SwissProt Annotations</span></label>
                                </div>
                            <!--
                                <div class="filter-tabs">
                                    <button id="filter-tab-pfam" class="filter-tab-active">Pfam</button>
                                    <button id="filter-tab-interpro">InterPro</button>
                                    <button id="filter-tab-swissprot">Annotation</button>
                                </div>
                            -->
                            </div>

<div class="panel-group" id="filter-accordion">
                            <div class="filter-cb-div filter-cb-toggle-div" id="filter-container-toggle">
                                <input id="filter-cb-toggle" type="checkbox" />
                                <label for="filter-cb-toggle"><span id="filter-cb-toggle-text">Show Family Numbers</span></label>
                            </div>
<?php createFamilyAccordionPanel("PFam Families", "pfam"); ?>
<?php createFamilyAccordionPanel("InterPro Families", "interpro"); ?>
<!--
                            <div style="width:100%;height:12em;" class="filter-container" id="filter-container">
                            </div>
-->
                            <!--<div>
                                <input id="filter-cb-toggle-dashes" type="checkbox" />
                                <label for="filter-cb-toggle-dashes"><span id="filter-cb-toggle-dashes-text">Dashed lines</span></label>
                            </div>-->
</div>
                            <button type="button" id="filter-clear"><i class="fas fa-times" aria-hidden="true"></i> Clear Filter</button>
                            <div class="active-filter-list" id="active-filter-list">
                            </div>
                        </div>
                    </li>
                    <li>
                        <div id="window-tools" class="initial-hidden">
                            <i class="fas fa-window-maximize" aria-hidden="true"></i> <span class="sidebar-header">Genome Window</span>
                            <div>
                                <select id="window-size" class="light zoom-btn">
<?php
    for ($i = 1; $i <= $maxNbSize; $i++) {
        $sel = $i == $nbSize ? "selected" : "";
        echo "                                    <option value=\"$i\" $sel>$i</option>\n";
    }
?>
                                </select> genes
                                <button type="button" class="btn btn-default tool-button auto zoom-btn" id="refresh-window">
                                    <i class="fas fa-refresh" aria-hidden="true"></i> Apply
                                </button>
                            </div>
                            <div>
                                <button type="button" class="btn btn-default tool-button zoom-btn" id="scale-zoom-out-large" style="font-size: 1.2em" title="0.25x">
                                    <i class="fas fa-search-minus"></i>
                                </button>
                                <button type="button" class="btn btn-default tool-button zoom-btn" id="scale-zoom-out-small" title="0.888x">
                                    <i class="fas fa-search-minus"></i>
                                </button>
                                Zoom
                                <button type="button" class="btn btn-default tool-button zoom-btn" id="scale-zoom-in-small" title="1.125x">
                                    <i class="fas fa-search-plus" title="1.125x"></i>
                                </button>
                                <button type="button" class="btn btn-default tool-button zoom-btn" id="scale-zoom-in-large" style="font-size: 1.2em" title="4x">
                                    <i class="fas fa-search-plus"></i>
                                </button>
                                <!--Scale factor: 1000 AA=<input type="text" width="5" name="scale-factor" id="scale-factor" value="15" style="line-height: 1.5em; padding: 2px; width: 35px; color: black" title="press enter to apply" />%-->
                            </div>
                        </div>
                    </li>
                    <li>
                        <div id="page-tools" class="initial-hidden">
                            <i class="fas fa-wrench" aria-hidden="true"></i> <span class="sidebar-header">Tools</span>

<?php if ($supportsDownload && !$isUploadedDiagram) { ?>
                            <div>
                                <a id="download-data" href="download_files.php?<?php echo $idKeyQueryString; ?>&type=data-file"
                                    title="Download the data to upload it for future analysis using this tool.">
                                        <button type="button" class="btn btn-default tool-button">
                                            <i class="fas fa-download" aria-hidden="true"></i> Download Data
                                        </button>
                                </a>
                            </div>
<?php } ?>
<?php if ($supportsExport) { ?>
                            <div>
                                <button type="button" class="btn btn-default tool-button" id="save-canvas-button">
                                    <i class="far fa-image" aria-hidden="true"></i> Save as SVG
                                </button>
                            </div>
<?php } ?>
<?php if ($supportsGeneGraphics) { ?>
                            <div>
                                <button type="button" class="btn btn-default tool-button" id="export-gene-graphics-button">
                                    <i class="far fa-image" aria-hidden="true"></i> Save as GeneGraphics
                                </button>
                            </div>
<?php } ?>
                            <div>
                                <a href="view_diagrams.php?<?php echo $idKeyQueryString; ?>" target="_blank">
                                    <button type="button" class="btn btn-default tool-button">
                                        <i class="fas fa-window-restore" aria-hidden="true"></i> New Window
                                    </button>
                                </a>
                            </div>

<?php if ($isDirectJob) {?>
                            <div>
                                <button type="button" class="btn btn-default tool-button" id="show-uniprot-ids">
                                    <i class="far fa-thumbs-up" aria-hidden="true"></i> <?php if (!$isBlast) echo "Recognized"; ?> UniProt IDs
                                </button>
                            </div>
<?php if ($hasUnmatchedIds) { ?>
                            <div>
                                <button type="button" class="btn btn-default tool-button" id="show-unmatched-ids">
                                <i class="fas fa-thumbs-down" aria-hidden="true"></i> Unmatched IDs
                                </button>
                            </div>
<?php } ?>
<?php if ($isBlast) { ?>
                            <div>
                                <button type="button" class="btn btn-default tool-button" id="show-blast-sequence">
                                <i class="fas fa-file-alt" aria-hidden="true"></i> Input Sequence
                                </button>
                            </div>
<?php } ?>
<?php } ?>
<?php if ($isBigscapeEnabled) { ?>
                            <div>
                                <button type="button" class="btn btn-default tool-button" id="run-bigscape-btn" <?php if ($bigscapeStatus == bigscape_job::STATUS_FINISH) echo "data-toggle=\"button\""; ?>>
<?php if ($bigscapeStatus == bigscape_job::STATUS_NONE) { ?>
                                    <i class="fas fa-magic" aria-hidden="true"></i> <span id="run-bigscape-btn-label">Run BiG-SCAPE</span>
<?php } elseif ($bigscapeStatus == bigscape_job::STATUS_RUNNING) { ?>
                                    <i class="fas fa-magic" aria-hidden="true"></i> BiG-SCAPE Pending
<?php } elseif ($bigscapeStatus == bigscape_job::STATUS_FINISH) { ?>
                                    <i class="fas fa-sort-amount-down" aria-hidden="true"></i> <span id="bigscape-ordering-btn-text">Use BLAST Ordering</span>
<?php } ?>
                                </button>
                            </div>

<?php if ($bigscapeStatus == bigscape_job::STATUS_FINISH) { ?>
                            <div>
                                <a href="download_files.php?<?php echo $idKeyQueryString; ?>&type=bigscape"
                                    title="Download the BiG-SCAPE clan data.">
                                        <button type="button" class="btn btn-default tool-button" id="view-bigscape-list-btn">
                                            <i class="fas fa-download" aria-hidden="true"></i> Get BiG-SCAPE Data
                                        </button>
                                </a>
                            </div>
<?php } ?>
<?php } ?>
                            <div>
                                <a href="diagram_tutorial.pdf">
                                <button type="button" class="btn btn-default tool-button" id="show-blast-sequence">
                                <i class="fas fa-file-alt" aria-hidden="true"></i> Tutorial
                                </button>
                                </a>
                            </div>
                            <div>
                                <button type="button" class="btn btn-default tool-button" id="help-modal-button"><i class="fas fa-question"></i> Quick Tips</button>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="container">
                <div id="arrow-container" style="width:100%;height:100%">
                    <br>
                    <svg id="arrow-canvas" width="100%" style="height:70px" viewBox="0 0 10 70" preserveAspectRatio="xMinYMin"></svg>
                    <div style="margin-top:50px;width:100%;position:fixed;bottom:0;height:50px;margin-bottom:100px">
                        <i id="progress-loader" class="fas fa-sync black fa-spin fa-4x fa-fw hidden-placeholder"></i>
                        <span id="loader-message"></span><br>
                        <div class="progress hidden">
                            <div class="progress-bar" style="width: 10%" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" id="progress-bar"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="footer">
            <table style="width:100%;height:60px">
                <tr>
                    <td style="width: 275px;">
                        <img src="images/efi_logo45.png" width="150" height="45" alt="EFI Logo" style="margin-left:45px" />
                    </td>
                    <td>
                        <div class="initial-hidden">
                            <div>
                                Showing <span id="diagrams-displayed-count">0</span> of <span id="diagrams-total-count">0</span> diagrams.
                            </div>
                            <div id="diagram-filter-count-container" style="display: none"> 
                                Number of Diagrams with Selected Families: <span>0</div>
                            </div>
                        </div>
                    </td>
                    <td style="width:250px">
                        <button type="button" class="btn btn-default" id="show-all-arrows-button">Show All</button>
                        <button type="button" class="btn btn-default" id="show-more-arrows-button">Show <?php echo $numDiagrams; ?> More</button>
                    </td>
                </tr>
            </table>
        </footer>

        <div id="alert-msg">Unable to show reqeuested diagrams.</div> 


        <!-- Bootstrap core JavaScript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->

        <script src="js/snap.svg-min.js" content-type="text/javascript"></script>
        <script src="js/Queue.js" content-type="text/javascript"></script>

        <!-- jQuery -->
        <script src="js/jquery-3.2.1.min.js"></script>
        <!-- Bootstrap Core JavaScript -->
        <script src="/bs/js/bootstrap.min.js"></script>

        <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
        <script src="/bs/js/ie10-viewport-bug-workaround.js"></script>
        <script src="js/gnd/color.js<?php echo $js_version; ?>" content-type="text/javascript"></script>
        <script src="js/gnd/control.js<?php echo $js_version; ?>" content-type="text/javascript"></script>
        <script src="js/gnd/data.js<?php echo $js_version; ?>" content-type="text/javascript"></script>
        <script src="js/gnd/filter.js<?php echo $js_version; ?>" content-type="text/javascript"></script>
        <script src="js/gnd/http.js<?php echo $js_version; ?>" content-type="text/javascript"></script>
        <script src="js/gnd/message.js<?php echo $js_version; ?>" content-type="text/javascript"></script>
        <script src="js/gnd/popup.js<?php echo $js_version; ?>" content-type="text/javascript"></script>
        <script src="js/gnd/ui.js<?php echo $js_version; ?>" content-type="text/javascript"></script>
        <script src="js/gnd/vars.js<?php echo $js_version; ?>" content-type="text/javascript"></script>
        <script src="js/gnd/view.js<?php echo $js_version; ?>" content-type="text/javascript"></script>
        <script src="js/gnd/ui-filter.js<?php echo $js_version; ?>" content-type="text/javascript"></script>
        <script src="js/gnd/app-specific.js<?php echo $js_version; ?>" content-type="text/javascript"></script>
        <script src="js/gnd/svg-util.js<?php echo $js_version; ?>" content-type="text/javascript"></script>
        <script type="application/javascript">
            $(document).ready(function() {
                $("#filter-cb-toggle").prop("checked", false);
                $("#filter-anno-toggle").prop("checked", false);
                $("#window-size").val(<?php echo $nbSize; ?>);
                if (checkBrowserSupport()) {

                    var svgCanvasId = "#arrow-canvas";
                    var pfamFilterContainerId = "#filter-container-pfam";
                    var interproFilterContainerId = "#filter-container-interpro";
                    var legendContainerId = "#active-filter-list";
                    var numDiagramsFilteredId = "#diagram-filter-count-container";

                    // Create objects
                    var gndVars = new GndVars();
                    // Initialize constant vars
                    gndVars.setPageSize(200);
                    gndVars.setUrlPath("get_gnd_data.php");
                    gndVars.setAuthString("<?php echo $idKeyQueryString; ?>");
                    gndVars.setWindow(<?php echo $nbSize; ?>);

                    var gndColor = new GndColor();
                    var gndRouter = new GndMessageRouter();
                    var gndHttp = new GndHttp(gndRouter);
                    var popupIds = new GndInfoPopupIds();
                    
                    var gndDb = new GndDb(gndColor);
                    var gndFilter = new GndFilter(gndRouter, gndDb);
                    var gndPopup = new GndInfoPopup(gndRouter, gndDb, popupIds);
                    var gndView = new GndView(gndRouter, gndDb, gndFilter, gndPopup, svgCanvasId);

                    var control = new GndController(gndRouter, gndDb, gndHttp, gndVars, gndView);
                    var filterUi = new GndFilterUi(gndRouter, gndFilter, gndColor, pfamFilterContainerId, interproFilterContainerId, legendContainerId, numDiagramsFilteredId);
                    var ui = new GndUi(gndRouter, control, filterUi);

                    // Add callbacks
                    //gndRouter.addListener(uiFilterUpdate); //TODO
    
                    // Register hooks to UI
                    ui.registerZoom("#scale-zoom-out-large", "#scale-zoom-out-small", "#scale-zoom-in-small", "#scale-zoom-in-large");
                    ui.registerShowMoreBtn("#show-more-arrows-button");
                    ui.registerShowAllBtn("#show-all-arrows-button");
                    ui.registerWindowUpdateBtn("#refresh-window", "#window-size");
                    ui.registerProgressLoader("#progress-loader");
                    ui.registerFilterControl("#filter-cb-toggle");
                    ui.registerFilterClear("#filter-clear");
                    ui.registerFilterAnnotation("#filter-anno-toggle", "#filter-anno-toggle-text");
                    ui.registerFilterFamilyGroup("#filter-accordion-panel-pfam", "#filter-accordion-panel-interpro");
                    ui.registerDiagramCountField("#diagrams-displayed-count", "#diagrams-total-count");
                    ui.registerLoaderMessage("#loader-message");
                    ui.registerProgressBar("#progress-bar");
                    ui.registerSearchBtn("#advanced-search-cluster-button", "#advanced-search-input", "#start-info");
<?php if ($isDirectJob) { ?>
                    ui.registerSearchResetBtn("#advanced-search-reset-button");
<?php } ?>

                    $(".zoom-btn").tooltip({delay: {show: 50}, placement: 'top', trigger: 'hover'});
                    $("#download-data").tooltip({delay: {show: 50}, placement: 'top', trigger: 'hover'});



<?php if (!$isDirectJob) { ?>
                    $("#start-info").show();
<?php } else { ?>
                    ui.initialDirectJobLoad();
                    $("#show-uniprot-ids").click(function(e) {
                        $("#uniprot-ids-modal").modal("show");
                    });
<?php if ($isBlast) { ?>
                    $("#show-blast-sequence").click(function(e) { $("#blast-sequence-modal").modal("show"); });
<?php } ?>
                    
<?php } ?>
                } else {
                    //TODO: nicer message
                    alert("Your browser is not supported.");
                }

<?php if ($hasUnmatchedIds) { ?>
                $("#show-unmatched-ids").click(function(e) {
                        $("#unmatched-ids-modal").modal("show");
                    });
<?php } ?>
<?php if ($isBigscapeEnabled) { ?>
<?php     if ($bigscapeStatus == bigscape_job::STATUS_FINISH) { ?>
                $("#run-bigscape-btn").click(function(e) {
                    //TODO: arrowApp.toggleUseBigscape();
                    //if (arrowApp.isOrderingBigscape()) {
                    //    $("#bigscape-ordering-btn-text").text("Default Ordering");
                    //} else {
                    //    $("#bigscape-ordering-btn-text").text("BiG-SCAPE Ordering");
                    //}
                });
<?php     } elseif ($bigscapeStatus == bigscape_job::STATUS_NONE || $bigscapeStatus == bigscape_job::STATUS_RUNNING) { ?>
                $("#run-bigscape-btn").click(function(e) { $("#run-bigscape-modal").modal("show"); });
<?php         if ($bigscapeStatus == bigscape_job::STATUS_NONE) { // only activate the confirm button if we haven't even started a bigscape run ?>
                $("#run-bigscape-confirm-btn").click(function(e) {
                    var completionHandler = function(status, message) {
                        $("#run-bigscape-body").hide();
                        if (!status) {
                            $("#run-bigscape-error-msg").text(message);
                            $("#run-bigscape-error-msg").show();
                        } else {
                            $("#run-bigscape-confirm-msg").show();
                        }
                        $("#run-bigscape-confirm-btn").hide();
                        $("#run-bigscape-reject-btn").text("Close");
                        $("#run-bigscape-btn-label").text("BiG-SCAPE Pending");
                    };
                    
                    //TODO: arrowApp.runBigscape(<?php echo $gnnId; ?>, "<?php echo $gnnKey; ?>", "<?php echo $bigscapeType; ?>", completionHandler);
                });
<?php         } ?>
<?php     } ?>
<?php } ?>

                $("#help-modal-button").click(function(e) {
                    $("#help-modal").modal("show");
                });

                $(".tooltip-text").tooltip({delay: {show: 50}, placement: 'top', trigger: 'hover'});

                $("#save-canvas-button").click(function(e) {
                    var svg = escape($("#arrow-canvas")[0].outerHTML);
                    var data = filterUi.getLegendSvg();//TODO
                    var legendSvgMarkup = escape(data[1]);
                    
                    var dlForm = $("<form></form>");
                    dlForm.attr("method", "POST");
                    dlForm.attr("action", "download_diagram_image.php");
                    dlForm.append('<input type="hidden" name="type" value="svg">');
                    dlForm.append('<input type="hidden" name="name" value="<?php echo $gnnName; ?>">');
                    dlForm.append('<input type="hidden" name="svg" value="' + svg + '">');
                    dlForm.append('<input type="hidden" name="legend1-svg" value="' + legendSvgMarkup + '">');
                    $("#download-forms").append(dlForm);
                    dlForm.submit();
                });
                $("#export-gene-graphics-button").click(function(e) {
                    var url = control.getUrl(0, control.getMaxIndex());
                    url = url.replace("get_gnd_data.php", "get_gene_graphics.php");
                    window.location = url;
                });
            });

            function showAlertMsg() {
                // Get the snackbar DIV
                var x = document.getElementById("alert-msg");
            
                // Add the "show" class to DIV
                x.className = "show";
            
                // After 3 seconds, remove the show class from DIV
                setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);

                alert("Unable to retrieve the selected diagrams: probably because too many were selected.");
            } 
        </script>

<?php $hideInterPro = $isInterProEnabled ? "" : 'style="display:none"'; ?>
        <div id="info-popup" class="info-popup hidden">
            <div id="copy-info"><i class="far fa-copy"></i></div>
            <div id="info-popup-id">UniProt ID: <a href="https://www.uniprot.org/uniprot" target="_blank"><span class="popup-id"></span></a></div>
            <div id="info-popup-desc">Description: <span class="popup-pfam"></span></div>
            <div id="info-popup-sptr">Annotation Status: <span class="popup-pfam"></span></div>
            <div class="info-popup-group">
                <div class="info-hdr">Pfam</div>
                <div id="info-popup-fam"><span class="popup-pfam"></span></div>
                <div id="info-popup-fam-desc"><span class="popup-pfam"></span></div>
            </div>
            <div class="info-popup-group" <?php echo $hideInterPro; ?>>
                <div class="info-hdr">InterPro</div>
                <div id="info-popup-ipro-fam"><span class="popup-pfam"></span></div>
                <div id="info-popup-ipro-fam-desc"><span class="popup-pfam"></span></div>
            </div>
            <!--    <div id="info-popup-coords">Coordinates: <span class="popup-pfam"></span></div>-->
            <div id="info-popup-seqlen" class="info-popup-group">Sequence Length: <span class="popup-pfam"></span></div>
            <!--    <div id="info-popup-dir">Direction: <span class="popup-pfam"></span></div>-->
            <!--    <div id="info-popup-num">Gene Index: <span class="popup-pfam"></span></div>-->
        </div>

        <div id="start-info">
            <div><i class="fas fa-arrow-left" aria-hidden="true"></i></div>
            <div>Start by entering a cluster number</div>
        </div>
        <div id="download-forms" style="display:none;">
        </div>
<?php if ($isDirectJob) { ?>
        <div id="uniprot-ids-modal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">UniProt IDs Identified</h4>
                    </div>
                    <div class="modal-body" id="uniprot-ids">
<?php echo $uniprotIdModalHeader; ?>
                        <table border="0">
                            <thead>
                                <th width="120px">UniProt ID</th>
                                <th>Query ID</th>
                            </thead>
                            <tbody>
<?php echo $uniprotIdModalText; ?>
<?php echo $uniprotIdModalFooter; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <a href="download_files.php?<?php echo $idKeyQueryString; ?>&type=uniprot"
                            title="Download the list of UniProt IDs that are contained within the diagrams.">
                                <button type="button" class="btn btn-default" id="save-uniprot-ids-btn">Save to File</button>
                        </a>
                            <!--                            onclick='saveDataFn("<?php echo "${gnnId}_${gnnName}_UniProt_IDs.txt" ?>", "uniprot-ids")'>Save to File</button>-->
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>
<?php if ($hasUnmatchedIds) { ?>
        <div id="unmatched-ids-modal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">IDs Detected Without UniProt Match</h4>
                    </div>
                    <div class="modal-body" id="unmatched-ids">
<?php echo $unmatchedIdModalText; ?>
                    </div>
                    <div class="modal-footer">
                        <a href="download_files.php?<?php echo $idKeyQueryString; ?>&type=unmatched"
                            title="Download the list of IDs that were not matched to a UniProt ID.">
                                <button type="button" class="btn btn-default" id="save-unmatched-ids-btn">Save to File</button>
                        </a>
                            <!--                            onclick='saveDataFn("<?php echo "${gnnId}_${gnnName}_Unmatched.txt" ?>", "unmatched-ids")'>Save to File</button>-->
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>
<?php } ?>
<?php if ($isBlast) { ?>
        <div id="blast-sequence-modal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Sequence Used in BLAST</h4>
                    </div>
                    <div class="modal-body" id="blast-sequence">
<?php echo $blastSequence; ?>
                    </div>
                    <div class="modal-footer">
                        <a href="download_files.php?<?php echo $idKeyQueryString; ?>&type=blast"
                            title="Download the list of UniProt IDs that are contained within the diagrams.">
                                <button type="button" class="btn btn-default" id="save-blast-seq-btn">Save to File</button>
                        </a>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>
<?php } ?>
<?php } ?>
<?php if ($isBigscapeEnabled) { ?>
        <div id="run-bigscape-modal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Run BiG-SCAPE</h4>
                    </div>
                    <div class="modal-body">
<?php if ($bigscapeStatus == bigscape_job::STATUS_NONE) { ?>
                        <div id="run-bigscape-body">
                            The <a href="https://git.wageningenur.nl/medema-group/BiG-SCAPE">Biosynthetic Genes
                            Similarity Clustering and Prospecting (BiG-SCAPE)</a> tool can be used to cluster the individual
                            diagrams based on their genomic context.  This can take several hours to complete, depending on
                            the size of the clusters.  If you proceed, you will to notified when the clustering has been
                            completed, and your arrow diagrams will be updated to reflect the new ordering.  You can continue
                            to use the tool as before while BiG-SCAPE is running.  Do you wish to continue?
                        </div>
                        <div id="run-bigscape-confirm-msg" style="display:none">
                            The BiG-SCAPE clustering is currently pending or
                            executing.  You will receive an email when the clustering has begun and completed.
                        </div>
                        <div id="run-bigscape-error-msg" style="display:none">
                            There was an error running BiG-SCAPE.
                        </div>
<?php } elseif ($bigscapeStatus == bigscape_job::STATUS_RUNNING) { ?>
                        <div id="run-bigscape-confirm-msg">
                            The BiG-SCAPE clustering is currently pending or
                            executing.  You will receive an email when the clustering has begun and completed.
                        </div>
<?php } ?>
                    </div>
                    <div class="modal-footer">
                        <div id="run-bigscape-footer">
<?php if ($bigscapeStatus == bigscape_job::STATUS_NONE) { ?>
                            <button type="button" class="btn btn-default" id="run-bigscape-confirm-btn">Yes</button>
                            <button type="button" class="btn btn-default" id="run-bigscape-reject-btn" data-dismiss="modal">No</button>
<?php } elseif ($bigscapeStatus == bigscape_job::STATUS_RUNNING) { ?>
                            <button type="button" class="btn btn-default" id="run-bigscape-reject-btn" data-dismiss="modal">Close</button>
<?php } ?>
                        </div>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>
<?php } ?>
<?php if ($showNewFeatures) { ?>
        <div class="new-features-alert alert alert-success">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            You can now click on an arrow to keep the info box open.  The info box has
            a link to the UniProt page for the protein, as well as a button for copying the
            information in the box onto the clipboard.
        </div>
<?php } ?>
        <div id="help-modal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Tips for Exploring</h4>
                    </div>
                    <div class="modal-body">
                        <p>
                        <div><b>Interactive Filtering</b></div>
                        The mouse can be used to select families to filter.  To
                        do this, press and hold the Ctrl key on the keyboard
                        and click on a protein.  All of the PFam families that
                        are associated with the protein will be highlighted.
                        </p>
                        <p>
                        <div><b>Viewing Metadata</b></div>
                        Moving the mouse over a specific protein will show a
                        popup box containing metadata.  As soon as the mouse is
                        moved away from the protein, the box disappears.  To
                        keep the box open, click on the protein, and the box
                        will remain visible until the mouse is moved over a
                        different protein.
                        </p>
                        <p>
                        <div><b>Copying Metadata</b></div>
                        Clicking the copy <i class="far fa-copy"></i> icon when
                        the metadata popup box is visible will copy the
                        metadata to the clipboard.  This information can be
                        pasted into another document for further use.
                        </p>
                        <p>
                        <div><b>Direct Link to UniProt Data</b></div>
                        The UniProt ID in the metadata popup box is a link that
                        can be used to access the UniProt website for the given protein.
                        </p>
                        <p>
                        <div><b>Changing the Window (Scale)</b></div>
                        By default a maximum of 40 kbp are shown.  This window
                        scale factor can be increased <i class="fas fa-search-minus"></i> or
                        decreased <i class="fas fa-search-plus"
                        title="1.125x"></i> by using the zoom buttons.  All
                        visible diagrams wil be reloaded when using the zoom
                        buttons.
                        </p>
                        <p>
                        <div><b>Changing the Window (Gene)</b></div>
                        The GND explorer can display from 1 to 20 genes on
                        either side of the query gene (center, red).  This can be
                        changed by clicking the "genes" drop down menu in the
                        Genome Window section, and clicking the Apply button.
                        </p>
                        <p>
                        <div><b>Updating the Filter Legend</b></div>
                        Selecting a family filter makes that family, along with its
                        assigned color, appear in a legend box below the "Clear Filter"
                        button.  Individual families can be removed from the legend
                        by moving the mouse over the color box and pressing the X
                        button that appears in the color box.  For InterPro
                        families, the color is not assigned, but the functionality
                        is the same.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>
    </body>
</html>


<?php

function createFamilyAccordionPanel($panelTitle, $idSuffix) {
    echo <<<HTML
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title" data-toggle="collapse" data-parent="#filter-accordion" data-target="#filter-accordion-panel-$idSuffix">
              <span class="accordion-arrow glyphicon glyphicon-triangle-right" aria-hidden="true"></span> <a class="accordion-toggle">$panelTitle</a>
            </h4>
        </div>
        <div id="filter-accordion-panel-$idSuffix" class="panel-collapse collapse">
          <div class="filter-panel panel-body">
                            <div style="width:100%;height:12em;" class="filter-container" id="filter-container-$idSuffix">
                            </div>
          </div>
        </div>
    </div>
HTML;
}

?>

