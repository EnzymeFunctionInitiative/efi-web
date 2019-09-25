
<?php 

require_once(__DIR__ . "/../includes/main.inc.php");
require_once(__DIR__ . "/../libs/settings.class.inc.php");
require_once(__DIR__ . "/../libs/bigscape_job.class.inc.php");
require_once(__BASE_DIR__ . "/training/libs/example_config.class.inc.php");


$is_example = isset($_GET["x"]) ? true : false;

$gnn_id = "";
$gnn_key = "";
$cooccurrence = "";
$nb_size = "";
$max_nb_size = 20;
$gnn_name = "";
$id_key_query_string = "";
$window_title = "";
$uniprot_id_modal_footer = "";
$uniprot_id_modal_header = "";
$uniprot_id_modal_text = "";
$unmatched_id_modal_text = "";
$blast_seq = "";
$job_type_text = "";

$is_bigscape_enabled = settings::get_bigscape_enabled() && !$is_example;
$is_interpro_enabled = settings::get_interpro_enabled();
$num_diagrams = settings::get_num_diagrams_per_page();
$is_uploaded_diagram = false;
$supports_download = true;
$supports_export = true;
$is_direct_job = false; // This flag indicates if the job is one that generated an arrow diagram from a single sequence BLAST'ed, list of IDs, or a list of FASTA sequences.
$has_unmatched_ids = false;
$is_blast = false;
$bigscape_status = 0; # 0 = no bigscape, 1 = running bigscape, 2 = bigscape completed
$bigscape_type = "";
$show_new_features = false;

if ((isset($_GET['gnn-id'])) && (is_numeric($_GET['gnn-id']))) {
    $gnn_key = $_GET['key'];
    $gnn_id = $_GET['gnn-id'];

    if ($is_example)
        $gnn = new gnn_example($db, $gnn_id);
    else
        $gnn = new gnn($db, $gnn_id);
    $cooccurrence = $gnn->get_cooccurrence();
    $nb_size = $gnn->get_size();
    $max_nb_size = $gnn->get_max_neighborhood_size();
    $gnn_name = $gnn->get_filename();
    $dot_pos = strpos($gnn_name, ".");
    $gnn_name = substr($gnn_name, 0, $dot_pos);
    
    if ($gnn->get_key() != $_GET['key']) {
        error_404();
    }
    elseif (time() < $gnn->get_time_completed() + settings::get_retention_days()) {
        pretty_error_404("That job has expired and doesn't exist anymore.");
    }

    if ($is_bigscape_enabled)
        $bigscape_type = DiagramJob::GNN;

    $id_key_query_string = "gnn-id=$gnn_id&key=$gnn_key";
    if ($is_example)
        $id_key_query_string .= "&x=1";
    $gnn_name_text = "GNN <i>$gnn_name</i>";
    $window_title = " for GNN $gnn_name (#$gnn_id)";
}
elseif (isset($_GET['upload-id']) && functions::is_diagram_upload_id_valid($_GET['upload-id'])) {
    $gnn_id = $_GET['upload-id'];
    $gnn_key = $_GET['key'];

    $arrows = new diagram_data_file($gnn_id);
    $key = diagram_jobs::get_key($db, $gnn_id);

    if ($gnn_key != $key) {
        error_404();
    }
    elseif (!$arrows->is_loaded()) {
        pretty_error_404("Oops, something went wrong. Please send us an e-mail and mention the following diagnostic code: $gnn_id");
    }

    $gnn_name = $arrows->get_gnn_name();
    $cooccurrence = $arrows->get_cooccurrence();
    $nb_size = $arrows->get_neighborhood_size();
    $max_nb_size = $arrows->get_max_neighborhood_size();
    $is_direct_job = $arrows->is_direct_job();

    if ($is_bigscape_enabled)
        $bigscape_type = DiagramJob::Uploaded;

    $id_key_query_string = "upload-id=$gnn_id&key=$gnn_key";
    $is_uploaded_diagram = true;
    $gnn_name_text = "filename <i>$gnn_name</i>";
    $window_title = " for uploaded filename $gnn_name";
}
elseif (isset($_GET['direct-id']) && functions::is_diagram_upload_id_valid($_GET['direct-id'])) {
    $gnn_id = $_GET['direct-id'];
    $gnn_key = $_GET['key'];

    $arrows = new diagram_data_file($gnn_id);
    $key = diagram_jobs::get_key($db, $gnn_id);

    if ($gnn_key != $key) {
        error404();
    }
    elseif (!$arrows->is_loaded()) {
        error_log($arrows->get_message());
        pretty_error_404("Oops, something went wrong. Please send us an e-mail and mention the following diagnostic code: $gnn_id");
    }

    $gnn_name = $arrows->get_gnn_name();
    $is_direct_job = true;
    $is_blast = $arrows->is_job_type_blast();
    $unmatched_ids = $arrows->get_unmatched_ids();
    $uniprot_ids = $arrows->get_uniprot_ids();
    $blast_seq = $arrows->get_blast_sequence();
    $job_type_text = $arrows->get_verbose_job_type();;
    $nb_size = $arrows->get_neighborhood_size();
    $max_nb_size = $arrows->get_max_neighborhood_size();

    if ($is_bigscape_enabled)
        $bigscape_type = DiagramJob::Uploaded;

    $has_unmatched_ids = count($unmatched_ids) > 0;

    #for ($i = 0; $i < count($uniprot_ids); $i++) {
    foreach ($uniprot_ids as $upId => $otherId) {
        if ($upId == $otherId)
            $uniprot_id_modal_text .= "<tr><td>$upId</td><td></td></tr>";
        else
            $uniprot_id_modal_text .= "<tr><td>$upId</td><td>$otherId</td></tr>";
    }

    for ($i = 0; $i < count($unmatched_ids); $i++) {
        $unmatched_id_modal_text .= "<div>" . $unmatched_ids[$i] . "</div>";
    }

    $id_key_query_string = "direct-id=$gnn_id&key=$gnn_key";
    if ($gnn_name) {
        $gnn_name_text = "<i>$gnn_name</i>";
        $window_title = " for $gnn_name (#$gnn_id)";
    } else {
        $gnn_name_text = "job #$gnn_id";
        $window_title = " for job #$gnn_id";
    }
}
else {
    error404();
}


if ($is_bigscape_enabled) {
    $bss = new bigscape_job($db, $gnn_id, $bigscape_type);
    $bigscape_status = $bss->get_status();
    $bigscape_btn_icon = $bigscape_status === bigscape_job::STATUS_FINISH ? "fa-sort-amount-down" : "fa-magic";
    $bigscape_btn_text = $bigscape_status === bigscape_job::STATUS_FINISH ? "Use BiG-SCAPE Synteny" : 
        ($bigscape_status === bigscape_job::STATUS_RUNNING ? "Big-SCAPE Pending" : "Run BiG-SCAPE");
    $bigscape_modal_close_text = $bigscape_status === bigscape_job::STATUS_RUNNING ? "Close" : "No";
}

$nb_size_div = "";
$cooc_div = "";
$job_type_div = "";
$job_id_div = "";
$js_version = "?v=4";

if ($is_direct_job) {
    $job_type_div = $job_type_text ? "<div>Job Type: $job_type_text</div>" : "";
} else {
    $nb_size_div = $nb_size ? "<div>Neighborhood size: $nb_size</div>"  : "";
    $cooc_div = $cooccurrence ? "<div>Co-occurrence: $cooccurrence</div>" : "";
}
$job_id_div = $gnn_id ? "<div>Job ID: $gnn_id</div>" : "";

?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">   
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="">
        <meta name="author" content="">

        <title>Genome Neighborhood Diagrams<?php echo $window_title; ?></title>

        <!-- Bootstrap core CSS -->
        <link href="<?php echo $SiteUrlPrefix; ?>/bs/css/bootstrap.min.css" rel="stylesheet">
        <link href="<?php echo $SiteUrlPrefix; ?>/bs/css/menu-sidebar.css" rel="stylesheet">
        <link href="<?php echo $SiteUrlPrefix; ?>/font-awesome/css/fontawesome-all.min.css" rel="stylesheet">
        <link rel="shortcut icon" href="images/favicon_efi.ico" type="image/x-icon">


        <!-- Custom styles for this template -->
        <link href="css/diagrams.css?v=2" rel="stylesheet">
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
                        Genome Neighborhood Diagrams for <?php echo $gnn_name_text; ?>
                    </td>
                    <td id="header-job-info">
                        <?php echo $job_type_div; ?>
                        <?php echo $job_id_div; ?>
                        <?php echo $cooc_div; ?>
                        <?php echo $nb_size_div; ?>
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
<?php if ($is_direct_job) { ?>
                            <div style="font-size:0.9em">Input specific UniProt IDs to display only those diagrams.</div>
<?php } else { ?>
                            <div style="font-size:0.9em">Input multiple clusters and/or individual UniProt IDs.</div>
<?php } ?>
                            <textarea id="advanced-search-input"></textarea>
                            <button type="button" class="btn btn-light" id="advanced-search-cluster-button">Query</button>
<?php if ($is_direct_job) { ?>
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
<?php create_family_accordion_panel("PFam Families", "pfam"); ?>
<?php create_family_accordion_panel("InterPro Families", "interpro"); ?>
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
    for ($i = 1; $i <= $max_nb_size; $i++) {
        $sel = $i == $nb_size ? "selected" : "";
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
                            <div class="btn-group" style="width: 100%; margin-button: 30px">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                    Export Data <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a href="#" id="export-gene-graphics-button"><i class="far fa-image" aria-hidden="true"></i> Export to Gene Graphics</a></li>
<?php if (!$is_example && $supports_download && !$is_uploaded_diagram) { ?>
                                    <li><a id="download-data" href="download_files.php?<?php echo $id_key_query_string; ?>&type=data-file"
                                        title="Download the data to upload it for future analysis using this tool.">
                                                <i class="fas fa-download" aria-hidden="true"></i> Download Data as SQLite
                                            </a></li>
<?php } ?>
                                </ul>
                            </div>

<?php if ($supports_export) { ?>
                            <div>
                                <button type="button" class="btn btn-default tool-button" id="save-canvas-button">
                                    <i class="far fa-image" aria-hidden="true"></i> Save as SVG
                                </button>
                            </div>
<?php } ?>
                            <div>
                                <a href="view_diagrams_v3.php?<?php echo $id_key_query_string; ?>" target="_blank">
                                    <button type="button" class="btn btn-default tool-button">
                                        <i class="fas fa-window-restore" aria-hidden="true"></i> New Window
                                    </button>
                                </a>
                            </div>

<?php if ($is_direct_job) {?>
                            <div>
                                <button type="button" class="btn btn-default tool-button" id="show-uniprot-ids">
                                    <i class="far fa-thumbs-up" aria-hidden="true"></i> <?php if (!$is_blast) echo "Recognized"; ?> UniProt IDs
                                </button>
                            </div>
<?php if ($has_unmatched_ids) { ?>
                            <div>
                                <button type="button" class="btn btn-default tool-button" id="show-unmatched-ids">
                                <i class="fas fa-thumbs-down" aria-hidden="true"></i> Unmatched IDs
                                </button>
                            </div>
<?php } ?>
<?php if ($is_blast) { ?>
                            <div>
                                <button type="button" class="btn btn-default tool-button" id="show-blast-sequence">
                                <i class="fas fa-file-alt" aria-hidden="true"></i> Input Sequence
                                </button>
                            </div>
<?php } ?>
<?php } ?>
<?php if ($is_bigscape_enabled) { ?>
                            <div>
                                <button type="button" class="btn btn-default tool-button" id="run-bigscape-btn" <?php if ($bigscape_status === bigscape_job::STATUS_FINISH) echo "data-toggle=\"button\""; ?>>
                                    <i class="fas <?php echo $bigscape_btn_icon; ?>"></i> <span id="run-bigscape-btn-text"><?php echo $bigscape_btn_text; ?></span>
                                </button>
                            </div>

<?php if ($bigscape_status === bigscape_job::STATUS_FINISH) { ?>
                            <div>
                                <a href="download_files.php?<?php echo $id_key_query_string; ?>&type=bigscape"
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
                        <i id="progress-error" class="fas fa-exclamation-circle black fa-4x fa-fw hidden-placeholder"></i>
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
                        <button type="button" class="btn btn-default" id="show-more-arrows-button">Show <?php echo $num_diagrams; ?> More</button>
                    </td>
                </tr>
            </table>
        </footer>

        <div id="alert-msg">Unable to show reqeuested diagrams.</div> 


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
        <script src="js/bigscape.js?v=2" content-type="text/javascript"></script>
        <script type="application/javascript">
            $(document).ready(function() {
                $("#filter-cb-toggle").prop("checked", false);
                $("#filter-anno-toggle").prop("checked", false);
                $("#window-size").val(<?php echo $nb_size; ?>);
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
                    gndVars.setAuthString("<?php echo $id_key_query_string; ?>");
                    gndVars.setWindow(<?php echo $nb_size; ?>);

                    var gndColor = new GndColor();
                    var gndRouter = new GndMessageRouter();
                    var gndHttp = new GndHttp(gndRouter);
                    var popupIds = new GndInfoPopupIds();
                    var bigscape = new BigScape(<?php echo $gnn_id; ?>, "<?php echo $gnn_key; ?>", "<?php echo $bigscape_type; ?>", "<?php echo $bigscape_status; ?>");
                    
                    var gndDb = new GndDb(gndColor);
                    var gndFilter = new GndFilter(gndRouter, gndDb);
                    var gndPopup = new GndInfoPopup(gndRouter, gndDb, popupIds);
                    var gndView = new GndView(gndRouter, gndDb, gndFilter, gndPopup, svgCanvasId);

                    var control = new GndController(gndRouter, gndDb, gndHttp, gndVars, gndView, bigscape);
                    var filterUi = new GndFilterUi(gndRouter, gndFilter, gndColor, pfamFilterContainerId, interproFilterContainerId, legendContainerId, numDiagramsFilteredId);
                    var ui = new GndUi(gndRouter, control, filterUi);
<?php if ($is_bigscape_enabled) { ?>
                    ui.registerBigScape(bigscape, "#run-bigscape-btn", "#run-bigscape-btn-text", "#run-bigscape-modal", "#run-bigscape-confirm", "#run-bigscape-reject");
<?php } ?>

                    // Add callbacks
                    //gndRouter.addListener(uiFilterUpdate); //TODO

                    // Register hooks to UI
                    ui.registerZoom("#scale-zoom-out-large", "#scale-zoom-out-small", "#scale-zoom-in-small", "#scale-zoom-in-large");
                    ui.registerShowMoreBtn("#show-more-arrows-button");
                    ui.registerShowAllBtn("#show-all-arrows-button");
                    ui.registerWindowUpdateBtn("#refresh-window", "#window-size");
                    ui.registerProgressLoader("#progress-loader");
                    ui.registerErrorLoader("#progress-error");
                    ui.registerFilterControl("#filter-cb-toggle");
                    ui.registerFilterClear("#filter-clear");
                    ui.registerFilterAnnotation("#filter-anno-toggle", "#filter-anno-toggle-text");
                    ui.registerFilterFamilyGroup("#filter-accordion-panel-pfam", "#filter-accordion-panel-interpro");
                    ui.registerDiagramCountField("#diagrams-displayed-count", "#diagrams-total-count");
                    ui.registerLoaderMessage("#loader-message");
                    ui.registerProgressBar("#progress-bar");
                    ui.registerSearchBtn("#advanced-search-cluster-button", "#advanced-search-input", "#start-info");
<?php if ($is_direct_job) { ?>
                    ui.registerSearchResetBtn("#advanced-search-reset-button");
<?php } ?>

                    $(".zoom-btn").tooltip({delay: {show: 50}, placement: 'top', trigger: 'hover'});
                    $("#download-data").tooltip({delay: {show: 50}, placement: 'top', trigger: 'hover'});



<?php if (!$is_direct_job) { ?>
                    $("#start-info").show();
<?php } else { ?>
                    ui.initialDirectJobLoad();
                    $("#show-uniprot-ids").click(function(e) {
                        $("#uniprot-ids-modal").modal("show");
                    });
<?php if ($is_blast) { ?>
                    $("#show-blast-sequence").click(function(e) { $("#blast-sequence-modal").modal("show"); });
<?php } ?>
                    
<?php } ?>
                } else {
                    //TODO: nicer message
                    alert("Your browser is not supported.");
                }

<?php if ($has_unmatched_ids) { ?>
                $("#show-unmatched-ids").click(function(e) {
                        $("#unmatched-ids-modal").modal("show");
                    });
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
                    dlForm.append('<input type="hidden" name="name" value="<?php echo str_replace("'", "\\'", $gnn_name); ?>">');
                    dlForm.append('<input type="hidden" name="svg" value="' + svg + '">');
                    dlForm.append('<input type="hidden" name="legend1-svg" value="' + legendSvgMarkup + '">');
                    $("#download-forms").append(dlForm);
                    dlForm.submit();
                });
                $("#export-gene-graphics-button").click(function(e) {
                    var url = control.getUrl(0, control.getMaxViewIndex());
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

<?php $hide_interpro = $is_interpro_enabled ? "" : 'style="display:none"'; ?>
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
            <div class="info-popup-group" <?php echo $hide_interpro; ?>>
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
<?php if ($is_direct_job) { ?>
        <div id="uniprot-ids-modal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">UniProt IDs Identified</h4>
                    </div>
                    <div class="modal-body" id="uniprot-ids">
<?php echo $uniprot_id_modal_header; ?>
                        <table border="0">
                            <thead>
                                <th width="120px">UniProt ID</th>
                                <th>Query ID</th>
                            </thead>
                            <tbody>
<?php echo $uniprot_id_modal_text; ?>
<?php echo $uniprot_id_modal_footer; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <a href="download_files.php?<?php echo $id_key_query_string; ?>&type=uniprot"
                            title="Download the list of UniProt IDs that are contained within the diagrams.">
                                <button type="button" class="btn btn-default" id="save-uniprot-ids-btn">Save to File</button>
                        </a>
                            <!--                            onclick='saveDataFn("<?php echo "${gnn_id}_${gnn_name}_UniProt_IDs.txt" ?>", "uniprot-ids")'>Save to File</button>-->
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>
<?php if ($has_unmatched_ids) { ?>
        <div id="unmatched-ids-modal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">IDs Detected Without UniProt Match</h4>
                    </div>
                    <div class="modal-body" id="unmatched-ids">
<?php echo $unmatched_id_modal_text; ?>
                    </div>
                    <div class="modal-footer">
                        <a href="download_files.php?<?php echo $id_key_query_string; ?>&type=unmatched"
                            title="Download the list of IDs that were not matched to a UniProt ID.">
                                <button type="button" class="btn btn-default" id="save-unmatched-ids-btn">Save to File</button>
                        </a>
                            <!--                            onclick='saveDataFn("<?php echo "${gnn_id}_${gnn_name}_Unmatched.txt" ?>", "unmatched-ids")'>Save to File</button>-->
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>
<?php } ?>
<?php if ($is_blast) { ?>
        <div id="blast-sequence-modal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Sequence Used in BLAST</h4>
                    </div>
                    <div class="modal-body" id="blast-sequence">
<?php echo $blast_seq; ?>
                    </div>
                    <div class="modal-footer">
                        <a href="download_files.php?<?php echo $id_key_query_string; ?>&type=blast"
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
<?php if ($is_bigscape_enabled) { ?>
        <div id="run-bigscape-modal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Run BiG-SCAPE</h4>
                    </div>
                    <div class="modal-body">
                        <div>
<?php if ($bigscape_status === bigscape_job::STATUS_NONE) { ?>
                            The <a href="https://git.wageningenur.nl/medema-group/BiG-SCAPE">Biosynthetic Genes
                            Similarity Clustering and Prospecting (BiG-SCAPE)</a> tool can be used to cluster the individual
                            diagrams based on their genomic context.  This can take several hours to complete, depending on
                            the size of the clusters.  If you proceed, you will to notified when the clustering has been
                            completed, and your arrow diagrams will be updated to reflect the new ordering.  You can continue
                            to use the tool as before while BiG-SCAPE is running.  Do you wish to continue?
<?php } else { ?>
                            The BiG-SCAPE clustering is currently pending or
                            executing.  You will receive an email when the clustering has begun and completed.
<?php } ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div id="run-bigscape-footer">
<?php if ($bigscape_status === bigscape_job::STATUS_NONE) { ?>
                            <button type="button" class="btn btn-default" class="btn-confirm" id="run-bigscape-confirm">Yes</button>
<?php } ?>
                            <button type="button" class="btn btn-default" class="btn-reject" data-dismiss="modal"><?php echo $bigscape_modal_close_text; ?></button>
                        </div>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>
<?php } ?>
<?php if ($show_new_features) { ?>
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
                        <p>
                        <div><b>Data Export</b></div>
                        <?php if ($supports_download && !$is_uploaded_diagram) { ?>
                        It is possible to export a file that provides the data used to generate the diagrams.
                        The data file format is SQLite, and it can be uploaded to the EFI-GNT
                        tool and viewed again in the future via the <i>View Saved Diagrams</i>
                        option.
                        For advanced use, the file can also be opened with
                        <a href="https://sqlitebrowser.org/" target="_blank">DB Browser for SQLite</a>
                        to manually extract data.
                        <?php } ?>
                        </p>
                        <p>
                        The currently displayed diagrams can be exported to the Gene Graphics
                        format and displayed using the
                        <a href="https://katlabs.cc/genegraphics/" target="_blank">Gene Graphics</a>
                        comparative genomics visualization tool.
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

function create_family_accordion_panel($panelTitle, $idSuffix) {
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

