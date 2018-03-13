
<?php 

require_once '../includes/main.inc.php';
require_once '../libs/settings.class.inc.php';
require_once '../libs/bigscape_job.class.inc.php';



$gnnId = "";
$gnnKey = "";
$cooccurrence = "";
$nbSize = "";
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
$isUploadedDiagram = false;
$supportsDownload = true;
$supportsExport = true;
$isDirectJob = false; // This flag indicates if the job is one that generated an arrow diagram from a single sequence BLAST'ed, list of IDs, or a list of FASTA sequences.
$hasUnmatchedIds = false;
$isBlast = false;
$bigscapeStatus = 0; # 0 = no bigscape, 1 = running bigscape, 2 = bigscape completed
$bigscapeType = "";

if ((isset($_GET['gnn-id'])) && (is_numeric($_GET['gnn-id']))) {
    $gnnKey = $_GET['key'];
    $gnnId = $_GET['gnn-id'];
    $gnn = new gnn($db, $gnnId);
    $cooccurrence = $gnn->get_cooccurrence();
    $nbSize = $gnn->get_size();
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
else if (isset($_GET['upload-id']) && functions::is_diagram_upload_id_valid($_GET['upload-id'])) {
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

    $gnnName = $arrows->get_name();
    $cooccurrence = $arrows->get_cooccurrence();
    $nbSize = $arrows->get_neighborhood_size();
    $isDirectJob = $arrows->is_direct_job();

    if ($isBigscapeEnabled)
        $bigscapeType = DiagramJob::Uploaded;

    $idKeyQueryString = "upload-id=$gnnId&key=$gnnKey";
    $isUploadedDiagram = true;
    $gnnNameText = "filename <i>$gnnName</i>";
    $windowTitle = " for uploaded filename $gnnName";
}
else if (isset($_GET['direct-id']) && functions::is_diagram_upload_id_valid($_GET['direct-id'])) {
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

    $gnnName = $arrows->get_name();
    $isDirectJob = true;
    $isBlast = $arrows->is_job_type_blast();
    $unmatchedIds = $arrows->get_unmatched_ids();
    $uniprotIds = $arrows->get_uniprot_ids();
    $blastSequence = $arrows->get_blast_sequence();
    $jobTypeText = $arrows->get_verbose_job_type();;
    $nbSize = $arrows->get_neighborhood_size();

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


if ($isBigscapeEnabled) {
    $bss = new bigscape_job($db, $gnnId, $bigscapeType);
    $bigscapeStatus = $bss->get_status();
}

$nbSizeDiv = "";
$cooccurrenceDiv = "";
$jobTypeDiv = "";
$jobIdDiv = "";

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

        <script src="js/app.js" type="application/javascript"></script>
        <script src="js/arrows.js" type="application/javascript"></script>

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
                    <!--<li class="sidebar-brand">
                        <a href="#menu-toggle" id="menu-toggle" style="margin-top:20px;float:right;" >
                            <i class="fas fa-caret-square-o-right fa-toggle-size hidden" id="toggle-icon-right" aria-hidden="true"></i>
                            <i class="fas fa-caret-square-o-left fa-toggle-size" id="toggle-icon-left" aria-hidden="true"></i>
                        </a> 
                    </li>-->
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
                            <i class="fas fa-filter" aria-hidden="true"> </i> <span class="sidebar-header">PFam Filtering</span>
                            <div class="filter-cb-div filter-cb-toggle-div" id="filter-container-toggle">
                                <input id="filter-cb-toggle" type="checkbox" />
                                <label for="filter-cb-toggle"><span id="filter-cb-toggle-text">Show Pfam Numbers</span></label>
                            </div>
                            <div style="width:100%;height:12em;" class="filter-container" id="filter-container">
                            </div>
                            <button type="button" id="filter-clear"><i class="fas fa-times" aria-hidden="true"></i> Clear Filter</button>
                            <!--<div>
                                <input id="filter-cb-toggle-dashes" type="checkbox" />
                                <label for="filter-cb-toggle-dashes"><span id="filter-cb-toggle-dashes-text">Dashed lines</span></label>
                            </div>-->
                            <div class="active-filter-list" id="active-filter-list">
                            </div>
                        </div>
                    </li>
                    <li>
                        <div id="window-tools" class="initial-hidden">
                            <i class="fas fa-window-maximize" aria-hidden="true"></i> <span class="sidebar-header">Genome Window</span>
                            <div>
                                <select id="window-size" class="light">
<?php
    for ($i = 1; $i <= $nbSize; $i++) {
        $sel = $i == $nbSize ? "selected" : "";
        echo "                                    <option value=\"$i\" $sel>$i</option>\n";
    }
?>
                                </select>
                                <button type="button" class="btn btn-default tool-button" id="refresh-window" style="width:auto">
                                    <i class="fas fa-refresh" aria-hidden="true"></i> Apply
                                </button>
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
                                    <i class="fas fa-sort-amount-down" aria-hidden="true"></i> BiG-SCAPE Ordering
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
                    </div>
                </div>
            </div>
        </div>

        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-md-2">
                        <img src="images/efi_logo45.png" width="150" height="45" alt="EFI Logo" style="margin-top:5px" />
                    </div>
                    <div class="col-md-1">
                    </div>
                    <div class="col-md-5">
                        <div class="button-wrapper col-centered initial-hidden">
                            Showing <span id="diagrams-displayed-count">0</span> of <span id="diagrams-total-count">0</span> diagrams.
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="button-wrapper pull-right">
                            <button type="button" class="btn btn-default" id="show-all-arrows-button">Show All</button>
                            <button type="button" class="btn btn-default" id="show-more-arrows-button">Show 20 More</button>
                        </div>
                    </div>
                </div>
            </div>
        </footer>


        <!-- Bootstrap core JavaScript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->

        <script src="js/snap.svg-min.js" content-type="text/javascript"></script>

        <!-- jQuery -->
        <script src="js/jquery-3.2.1.min.js"></script>
        <!-- Bootstrap Core JavaScript -->
        <script src="/bs/js/bootstrap.min.js"></script>

        <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
        <script src="/bs/js/ie10-viewport-bug-workaround.js"></script>
        <script type="application/javascript">
            $(document).ready(function() {
                var popupIds = new PopupIds();
                var arrowDiagrams = new ArrowDiagram("arrow-canvas", "", "arrow-container", popupIds);
                //arrowDiagrams.setJobInfo("<?php echo $idKeyQueryString; ?>");
                var arrowApp = new ArrowApp(arrowDiagrams);
                arrowApp.setQueryString("<?php echo $idKeyQueryString; ?>");

                $("#menu-toggle").click(function(e) {
                        e.preventDefault();
                        $("#wrapper").toggleClass("toggled");
                        $("#filter-container").toggleClass("hidden");
                        $("#toggle-icon-left").toggleClass("hidden");
                        $("#toggle-icon-right").toggleClass("hidden");
                        $("#advanced-search-panel").toggleClass("hidden");
                    });
                $("#filter-cb-toggle").click(function(e) {
                        arrowApp.togglePfamNamesNumbers(this.checked);
                    });

                $("#save-canvas-button").click(function(e) {
                        var svg = escape($("#arrow-canvas")[0].outerHTML);
                        arrowApp.downloadSvg(svg, "<?php echo $gnnName ?>");
                    });

<?php if ($isDirectJob) { ?>
                arrowApp.showDefaultDiagrams();
                $("#advanced-search-reset-button").click(function(e) {
                        arrowApp.showDefaultDiagrams();
                    });
                $("#show-uniprot-ids").click(function(e) {
                        $("#uniprot-ids-modal").modal("show");
                    });
<?php if ($hasUnmatchedIds) { ?>
                $("#show-unmatched-ids").click(function(e) {
                        $("#unmatched-ids-modal").modal("show");
                    });
<?php } ?>
<?php if ($isBlast) { ?>
                $("#show-blast-sequence").click(function(e) { $("#blast-sequence-modal").modal("show"); });
<?php } ?>
<?php } else { ?>
                $("#start-info").show();
<?php } ?>
<?php if ($isBigscapeEnabled) { ?>
<?php if ($bigscapeStatus == bigscape_job::STATUS_FINISH) { ?>
                $("#run-bigscape-btn").click(function(e) {
                    arrowApp.toggleUseBigscape();
                });
<?php } elseif ($bigscapeStatus == bigscape_job::STATUS_NONE || $bigscapeStatus == bigscape_job::STATUS_RUNNING) { ?>
                $("#run-bigscape-btn").click(function(e) { $("#run-bigscape-modal").modal("show"); });
<?php       if ($bigscapeStatus == bigscape_job::STATUS_NONE) { // only activate the confirm button if we haven't even started a bigscape run ?>
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
                    
                    arrowApp.runBigscape(<?php echo $gnnId; ?>, "<?php echo $gnnKey; ?>", "<?php echo $bigscapeType; ?>", completionHandler);
                });
<?php       } ?>
<?php } ?>
<?php } ?>
                arrowApp.setNeighborhoodWindow(<?php echo $nbSize; ?>);
            });
        </script>

        <div id="info-popup" class="info-popup hidden">
            <div id="info-popup-id">UniProt ID: <span class="popup-id"></span></div>
            <div id="info-popup-desc">Description: <span class="popup-pfam"></span></div>
            <div id="info-popup-sptr">Annotation Status: <span class="popup-pfam"></span></div>
            <div id="info-popup-fam">Family: <span class="popup-pfam"></span></div>
            <div id="info-popup-fam-desc">Pfam Desc: <span class="popup-pfam"></span></div>
            <!--    <div id="info-popup-coords">Coordinates: <span class="popup-pfam"></span></div>-->
            <div id="info-popup-seqlen">Sequence Length: <span class="popup-pfam"></span></div>
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
                            The <a href="https://git.wageningenur.nl/medema-group/BiG-SCAPE">Biosynthetic Genese
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
    </body>
</html>


