<?php
require_once "../libs/user_jobs.class.inc.php";
require_once "../../libs/ui.class.inc.php";
require_once "../includes/main.inc.php";

$user_email = "Enter your e-mail address";

$IsLoggedIn = false;
$showPreviousJobs = false;
$gnnJobs = array();
$diagramJobs = array();
$IsAdminUser = false;
$trainingJobs = array();
$max_debug = false;

if (settings::is_recent_jobs_enabled() && user_jobs::has_token_cookie()) {
    $token = user_jobs::get_user_token();
    if (isset($_GET["max_debug"])) {
        $max_debug = true;
    }
    $userJobs = new user_jobs();
    $userJobs->load_jobs($db, $token);
    $gnnJobs = $userJobs->get_jobs();
    $diagramJobs = $userJobs->get_diagram_jobs();
    $trainingJobs = $userJobs->get_training_jobs();
    $jobEmail = $userJobs->get_email();
    if ($jobEmail)
        $user_email = $jobEmail;
    $showPreviousJobs = count($gnnJobs) > 0 || count($diagramJobs) > 0 || count($trainingJobs) > 0;
    if ($user_email)
        $IsLoggedIn = $user_email;
    $IsAdminUser = $userJobs->is_admin();
}

$default_seq = settings::get_default_blast_seq();
$max_seq = settings::get_max_blast_seq();
$default_evalue = settings::get_default_evalue();


$est_id = "";
$est_key = "";
$est_file_name = "";
$est_file_index = "";
$submit_est_args = "";
if (isset($_GET["est-id"]) && isset($_GET["est-key"]) && isset($_GET["est-ssn"])) {
    $the_aid = $_GET["est-id"];
    $the_key = $_GET["est-key"];
    $the_idx = $_GET["est-ssn"];

    $job_info = functions::verify_est_job($db, $the_aid, $the_key, $the_idx);
    if ($job_info !== false) {
        $est_file_info = functions::get_est_filename($job_info, $the_aid, $the_idx);
        if ($est_file_info !== false) {
            $est_id = $job_info["generate_id"];
            $est_key = $the_key;
            $est_file_index = $the_idx;
            $est_file_name = $est_file_info["filename"];

            $submit_est_args = "'$the_aid','$the_key','$the_idx'";
        }
    }
}


$use_advanced_options = global_settings::advanced_options_enabled();

$updateMessage = 
//    '<div class="new_feature"></div>' .
    "The <a href=\"../efi-cgfp/\">Computationally-Guided Functional Profiling tool (EFI-CGFP)</a> is now available.<br><br>" .
    functions::get_update_message();

require_once "inc/header.inc.php";

?>


<p></p>
<p>
The EFI-Genome Neighborhood Tool (EFI-GNT) allows the exploration of the physical association of genes on genomes, i.e. 
gene clustering. EFI-GNT enables a user to retrieve, display, and interact with genome neighborhood information for 
large datasets of sequences.
</p>

<div id="update-message" class="update_message initial-hidden">
<?php if (isset($updateMessage)) echo $updateMessage; ?>
</div>

A listing of new features and other information pertaining to GNT is available on the <a href="notes.php">release notes page</a>. 

<div class="tabs-efihdr tabs">
    <ul class="tab-headers">
<?php if ($showPreviousJobs) { ?>
        <li <?php if (!$est_id) echo "class=\"ui-tabs-active\""; ?>><a href="#jobs">Previous Jobs</a></li>
<?php } ?>
        <li <?php if ($est_id) echo "class=\"ui-tabs-active\""; ?>><a href="#create">Create GNN</a></li>
        <li><a href="#create-diagrams">Retrieve Neighborhoods</a></li>
        <li><a href="#diagrams">View Saved Diagrams</a></li>
        <li <?php if (! $showPreviousJobs) echo "class=\"active\""; ?>><a href="#tutorial">Tutorial</a></li>
    </ul>

    <div class="tab-content">
<?php if ($showPreviousJobs) { ?>
        <div id="jobs" class="tab <?php echo $est_id ? "" : "active"; ?>">
<?php } ?>
<?php if (count($gnnJobs) > 0) { ?>
            <h4>GNN Jobs</h4>
            <table class="pretty_nested" style="table-layout:fixed">
                <thead>
                    <th class="id-col">ID</th>
                    <th>Filename</th>
                    <th class="date-col">Date Completed</th>
                </thead>
                <tbody>
<?php
$lastBgColor = "#eee";
for ($i = 0; $i < count($gnnJobs); $i++) {
    $key = $gnnJobs[$i]["key"];
    $id = $gnnJobs[$i]["id"];
    $name = $gnnJobs[$i]["filename"];
    $dateCompleted = $gnnJobs[$i]["completed"];
    $isActive = $dateCompleted == "PENDING" || $dateCompleted == "RUNNING";

    $linkStart = $isActive ? "" : "<a href=\"stepc.php?id=$id&key=$key\">";
    $linkEnd = $isActive ? "" : "</a>";
    $linkStart .= "<span title='$id'>";
    $linkEnd = "</span>" . $linkEnd;
    $idText = "$linkStart${id}$linkEnd";

    $nameStyle = "";
    if ($gnnJobs[$i]["is_child"]) {
        $idText = "";
        $nameStyle = "style=\"padding-left: 50px;\"";
    } else {
        if ($lastBgColor == "#eee")
            $lastBgColor = "#fff";
        else
            $lastBgColor = "#eee";
    }

    echo <<<HTML
                    <tr style="background-color: $lastBgColor">
                        <td>$idText</td>
                        <td $nameStyle>$linkStart${name}$linkEnd</td>
                        <td>$dateCompleted <div style="float:right" class="archive-btn" data-type="gnn" data-id="$id" data-key="$key" title="Archive Job"><i class="fas fa-trash-alt"></i></div></td>
                    </tr>
HTML;
}
?>
                </tbody>
            </table>
<?php } ?>
            
<?php if (count($diagramJobs) > 0) { ?>
            <h4>Diagram Jobs</h4>
            <table class="pretty" style="table-layout:fixed">
                <thead>
                    <th class="id-col">ID</th>
                    <th class="name-col">Job Name</th>
                    <th class="type-col">Job Type</th>
                    <th class="date-col">Date Completed</th>
                </thead>
                <tbody>
<?php
for ($i = 0; $i < count($diagramJobs); $i++) {
    $key = $diagramJobs[$i]["key"];
    $id = $diagramJobs[$i]["id"];
    $name = $diagramJobs[$i]["filename"];
    $dateCompleted = $diagramJobs[$i]["completed"];
    $isActive = $dateCompleted == "PENDING" || $dateCompleted == "RUNNING";

    $idField = $diagramJobs[$i]["id_field"];
    $jobType = $diagramJobs[$i]["verbose_type"];
    $linkStart = $isActive ? "" : "<a href=\"view_diagrams.php?$idField=$id&key=$key\">";
    //$linkStart = $isActive ? "" : "<a href=\"stepc.php?id=$id&key=$key\">";
    $linkEnd = $isActive ? "" : "</a>";

    echo <<<HTML
                    <tr>
                        <td>$linkStart${id}$linkEnd</td>
                        <td>$linkStart${name}$linkEnd</td>
                        <td>$linkStart${jobType}$linkEnd</td>
                        <td>$dateCompleted <div style="float:right" class="archive-btn" data-type="diagram" data-id="$id" data-key="$key" title="Archive Job"><i class="fas fa-trash-alt"></i></div></td>
                    </tr>
HTML;
}
?>
                </tbody>
            </table>
<?php } ?>



<?php if (count($trainingJobs) > 0) { ?>
            <h4>Training Jobs</h4>
            <table class="pretty_nested" style="table-layout:fixed">
                <thead>
                    <th class="id-col">ID</th>
                    <th>Filename</th>
                    <th class="date-col">Date Completed</th>
                </thead>
                <tbody>
<?php
$lastBgColor = "#eee";
for ($i = 0; $i < count($trainingJobs); $i++) {
    $key = $trainingJobs[$i]["key"];
    $id = $trainingJobs[$i]["id"];
    $name = $trainingJobs[$i]["filename"];
    $dateCompleted = $trainingJobs[$i]["completed"];
    $isActive = $dateCompleted == "PENDING" || $dateCompleted == "RUNNING";

    $linkStart = $isActive ? "" : "<a href=\"stepc.php?id=$id&key=$key\">";
    $linkEnd = $isActive ? "" : "</a>";
    $linkStart .= "<span title='$id'>";
    $linkEnd = "</span>" . $linkEnd;
    $idText = "$linkStart${id}$linkEnd";

    $nameStyle = "";
    if ($trainingJobs[$i]["is_child"]) {
        $idText = "";
        $nameStyle = "style=\"padding-left: 50px;\"";
    } else {
        if ($lastBgColor == "#eee")
            $lastBgColor = "#fff";
        else
            $lastBgColor = "#eee";
    }

    if (array_key_exists("diagram", $trainingJobs[$i]))
        $linkStart = "<a href=\"view_diagrams.php?upload-id=$id&key=$key\">";

    echo <<<HTML
                    <tr style="background-color: $lastBgColor">
                        <td>$idText</td>
                        <td $nameStyle>$linkStart${name}$linkEnd</td>
                        <td>$dateCompleted</td>
                    </tr>
HTML;
}
?>
                </tbody>
            </table>
<?php } ?>




<?php if ($showPreviousJobs) { ?>
        </div>
<?php } ?>

        <div id="create" class="tab <?php echo $est_id ? "ui-tabs-active" : ""; ?>">
            <p>
                Genome Neighborhood Networks (GNNs), a colored Sequence Similarity Network (SSN), summary
                tables, sets of IDs and sequences per cluster are created for the submitted SSN.
            </p>

            <form name="upload_form" id="upload_form" method="post" action="" enctype="multipart/form-data">
                <div class="primary-input">
                <?php echo ui::make_upload_box("Select a File to Upload:", "ssn_file", "progress_bar", "progress_number", "", "", $est_file_name); ?>
                    The submitted SSN must have been generated using any of the EST options except the
                    FASTA without header reading option.
                    The SSNs generated with these Options can be modified in Cytoscape.
                    The acceptable format is uncompressed or zipped XGMML.
                </div>
    
                <?php add_neighborhood_size_setting(false); ?>
                <?php add_cooccurrence_setting(false); ?>
<!--
                <div class="option-panels gnn-options">
                    <div>
                        <?php add_neighborhood_size_setting(); ?>
                    </div>
                    <div>
                        <?php add_cooccurrence_setting(); ?>
                    </div>
                    <?php if ($use_advanced_options) { ?>
                    <div>
                        <?php add_dev_site_options("db_mod"); ?>
                    </div>
                    <?php } ?>
                </div>
-->

                <?php add_submit_button("gnn", "ssn_email", "ssn_message", "Generate GNN"); ?>
            </form>
        </div>

        <div id="create-diagrams" class="tab">
            <div style="margin-bottom: 10px;">Clicking on the headers below provides access to various ways of generating genomic network diagrams.</div>
            <div class="tabs-efihdr tabs">
                <ul class="tab-headers">
                    <li class="ui-tabs-active"><a href="#diagram-blast">Single Sequence BLAST</a></li>
                    <li><a href="#diagram-seqid">Sequence ID Lookup</a></li>
                    <li><a href="#diagram-fasta">FASTA Sequence Lookup</a></li>
                </ul>
                <div class="tab-content">
                <div id="diagram-blast" class="tab ui-tabs-active">
                    <p>
                    The provided sequence is used as the query for a BLAST search of the UniProt database.
                    The retrieved sequences are used to generate genomic neighborhood diagrams. 
                    </p>

                    <form name="create_diagrams" id="create_diagram_form" method="post" action="">
                        <input type="hidden" id="option-a-option" name="option" value="a">
                        <textarea class="options" id="option-a-input" name="option-a-input"></textarea>

                        <div class="option-panels">
                            <div>
                            </div>
                        </div>
                        <div class="create-job-options">
                            <table>
<?php add_job_title_option("option-a-title"); ?>
                            <tr>
                                <td><label for="max-seqs">Maximum Blast Sequences:</label></td>
                                <td>
                                     <input type="text" id="option-a-max-seqs" class="small" name="max-seqs" value="<?php echo $default_seq; ?>">
                                </td>
                                <td>
                                    Maximum number of sequences retrieved (&le; <?php echo $max_seq; ?>;
                                    default: <?php echo $default_seq; ?>)
                                </td>
                            </tr>
                            <tr>
                                 <td>E-Value:</td>
                                 <td>
                                     <input type="text" class="small" id="option-a-evalue" name="evalue" value="<?php echo $default_evalue; ?>">
                                 </td>
                                 <td>
                                     Negative log of e-value for all-by-all BLAST (&ge; 1; default: <?php echo $default_evalue; ?>)
                                 </td>
                            </tr>
<?php add_window_option("option-a-nb-size"); ?>
<?php add_database_option("option-a-db-mod"); ?>
                            </table>
                        </div>

                        <?php add_submit_button("diagram_blast", "option-a-email", "option-a-message", "Submit"); ?>
                    </form>
                </div>

                <div id="diagram-seqid" class="tab">
                    <p>
                    The genomic neighborhoods are retreived for the UniProt, NCBI, EMBL-EBI ENA, and PDB identifiers
                    that are provided in the input box below.  Not all identifiers may exist in the EFI-GNT database so
                    the results will only include diagrams for sequences that were identified.
                    </p>

                    <form name="create_diagrams" id="create_diagram_form" method="post" action="create_diagram.php">
                        <input type="hidden" id="option-d-option" name="option" value="d">
                        <textarea class="options" id="option-d-input" name="input"></textarea>

                        <div style="margin-bottom: 20px">
                            <?php echo ui::make_upload_box(
                                        "Alternatively, a file containing a list of IDs can be uploaded:",
                                        "option-d-file", "option-d-progress-bar", "option-d-progress-number",
                                        "The acceptable format is text."); ?>
                        </div>

                        <div class="create-job-options">
                            <table>
<?php add_job_title_option("option-d-title"); ?>
<?php add_window_option("option-d-nb-size"); ?>
<?php add_database_option("option-d-db-mod"); ?>
                            </table>
                        </div>
    
                        <?php add_submit_button("diagram_id", "option-d-email", "option-d-message", "Submit"); ?>
                    </form>
                </div>

                <div id="diagram-fasta" class="tab">
                    <p>
                    The genomic neighborhoods are retreived for the UniProt, NCBI, EMBL-EBI ENA, and PDB identifiers
                    that are identified in the FASTA <b>headers</b>.  Not all identifiers may exist in the EFI-GNT database so
                    the results will only include diagrams for sequences that were identified.
                    </p>

                    <form name="create_diagrams" id="create_diagram_form" method="post" action="create_diagram.php">
                        <input type="hidden" id="option-c-option" name="option" value="c">
                        <textarea class="options" id="option-c-input" name="input"></textarea>

                        <div style="margin-bottom: 20px">
                            <?php echo ui::make_upload_box(
                                        "Alternatively, a file containing FASTA headers and sequences can be uploaded:",
                                        "option-c-file", "option-c-progress-bar", "option-c-progress-number",
                                        "The acceptable format is text."); ?>
                        </div>

                        <div class="create-job-options">
                            <table>
<?php add_job_title_option("option-c-title"); ?>
<?php add_window_option("option-c-nb-size"); ?>
<?php add_database_option("option-c-db-mod"); ?>
                            </table>
                        </div>
    
                        <?php add_submit_button("diagram_fasta", "option-c-email", "option-c-message", "Submit"); ?>
                    </form>
                </div>
                </div><!-- class="tab-content" -->
            </div>
        </div>

        <div id="diagrams" class="tab">
            <form name="upload_diagram_form" id="upload_diagram_form" method="post" action="" enctype="multipart/form-data">
                <p>
                    <?php echo ui::make_upload_box("Select a File to Upload:", "diagram_file", "progress_bar_diagram", "progress_number_diagram", "The acceptable format is sqlite."); ?>
                </p>
    
                <?php add_submit_button("diagram_upload", "diagram_email", "diagram_message", "Upload Diagram Data"); ?>
            </form> 
        </div>

        <div id="tutorial" class="tab <?php if (!$showPreviousJobs) echo "ui-tabs-active"; ?>">
            <h3>EFI-Genome Neighborhood Tool Overview</h3>
    
            <p>
            Although other tools allow comparison of gene neighborhoods among multiple prokaryotic genomes to allow inference of 
            phylogenetic relationships, e.g., IMG (<a href="https://img.jgi.doe.gov" target="_blank">https://img.jgi.doe.gov</a>)
            and  PATRIC (<a href="https://www.patricbrc.org" target="_blank">https://www.patricbrc.org</a>), EFI-GNT enables 
            comparison of the genome neighborhoods for clusters of similar protein sequences in order to facilitate the assignment 
            of function within protein families and superfamilies.
            </p>
    
            <p>
            EFI-GNT is focused on placing protein families and superfamilies into a context. A sequence similarity network (SSN) 
            with defined protein clusters is used as an input. Each sequence within a SSN is used as a query for interrogation of 
            its genome neighborhood.
            </p>
    
            <h3>EFI-GNT acceptable input</h3>
    
            <p>
            The sequence datasets are generated from an SSN produced by the EFI-Enzyme Similarity Tool (EFI-EST). Acceptable 
            SSNs are generated for an entire Pfam and/or InterPro protein family (from Option B of EFI-EST), a focused region of a 
            family (from Option A of EFI-EST), a set of protein sequence that can be identified from FASTA headers (from option C of 
            EFI-EST with header reading) or a list of recognizable UniProt and/or NCBI IDs (from option D of EFI-EST). A manually 
            modified SSN within Cytoscape that originated from any of the EST options is also acceptable. SSNs that have been 
            colored using the "Color SSN Utility" of EFI-EST and that originated from any of acceptable Options are also acceptable.
            </p>
    
            <h3>Principle of GNT analysis</h3>
    
            <p>
            Protein encoding genes that are neighbors of input queries (within a defined window on either side) are collected from 
            sequence files for bacterial (prokaryotic and archaeal) and fungal genomes in the European Nucleotide Archive (ENA) 
            database. The co-occurrence frequencies of the identified neighboring sequences with the input queries are calculated as 
            well as the absolute values of the distances in open reading frames (orfs) between the queries and neighbors. The 
            calculated information is provided as Genome Neighborhood Networks (GNNs), in addition to a colored version of the input 
            SSN that aids analysis of the GNNs.
            </p>
    
            <h3>EFI-GNT output</h3>
    
            <p>
            EFI-GNT generates two formats of the Genome Neighborhood Network (GNN) as well as a colored version of the input SSN 
            that aids analysis of the GNNs.
            </p>
    
            <p>
            The UniProt accession IDs for the queries and the neighbors, the Pfam families for the neighbors, and both the 
            query-neighbor distances (in orfs) and co-occurrence frequencies are provided in the GNNs. The GNNs and colored SSN are 
            downloaded, visualized, and analyzed using Cytoscape.
            </p>
    
            <p>
            The user can use Cytoscape to filter the GNNs for a range of query-neighbor distances and/or co-occurrence frequencies 
            to enable the identification of functionally related proteins/enzymes, with shorter distances and great co-occurrence 
            frequencies suggesting functional linkage in a metabolic pathway. With the identities of the Pfam families for the 
            neighbors, the user may be able to infer the in vitro enzymatic activities of the queries and neighbors and predict the 
            reactions in the metabolic pathway in which they participate.
            </p>
    
            <center>
                <p><img src="images/tutorial/intro_figure_1.jpg"></p>
                <p><i>Figure 1:</i> Examples of colored SSN (left) and a hub-and-spoke cluster from a GNN (right).</p>
            </center>
    
    
    
            <p class="center"><a href="tutorial.php"><button class="light">Continue Tutorial</button></a></p>
    
        </div>
    </div> <!-- tab-content -->
</div> <!-- tabs -->


<div align="center">
    <?php if (settings::is_beta_release()) { ?>
    <h4><b><span style="color: red">BETA</span></b></h4>
    <?php } ?>

    <p>
    UniProt Version: <b><?php echo settings::get_uniprot_version(); ?></b><br>
    ENA Version: <b><?php echo settings::get_ena_version(); ?></b><br>
    EFI-GNT Version: <b><?php echo settings::get_gnt_version(); ?></b>
    </p>
</div>

<div id="archive-confirm" title="Archive the job?" style="display: none">
<p>
<span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>
This job will be permanently removed from your list of jobs.
</p>    
</div>

<script>
    $(document).ready(function() {

        $(".advanced-toggle").click(function () {
            $header = $(this);
            //getting the next element
            $content = $header.next();
            //open up the content needed - toggle the slide- if visible, slide up, if not slidedown.
            $content.slideToggle(100, function () {
                if ($content.is(":visible")) {
                    $header.find("i.fa").addClass("fa-minus-square");
                    $header.find("i.fa").removeClass("fa-plus-square");
                } else {
                    $header.find("i.fa").removeClass("fa-minus-square");
                    $header.find("i.fa").addClass("fa-plus-square");
                }
            });
        
        });

        $(".archive-btn").click(function() {
            var id = $(this).data("id");
            var key = $(this).data("key");
            var jobType = $(this).data("type");
            var requestType = "archive";

            $("#archive-confirm").dialog({
                resizable: false,
                height: "auto",
                width: 400,
                modal: true,
                buttons: {
                    "Archive Job": function() {
                        requestJobUpdate(id, key, requestType, jobType);
                        $( this ).dialog("close");
                    },
                    Cancel: function() {
                        $( this ).dialog("close");
                    }
                }
            });
        });

        $(".option-panels > div").accordion({
            heightStyle: "content",
            collapsible: true,
            active: false,
        });
        $(".gnn-options > div").accordion("option", {active: 0});
        $(".tabs").tabs();
    });
</script>
<script src="js/custom-file-input.js" type="text/javascript"></script>

<?php

function make_db_mod_option($db_modules, $form_id) {
}


function add_cooccurrence_setting($use_header = true) {
    $default_cooc = settings::get_default_cooccurrence();
    $title = "Co-occurrence Percentage Lower Limit";
    
    if ($use_header)
        echo <<<HTML
<h3>$title</h3>
HTML;

    $title = $use_header ? "Cooccurrence" : "<b>$title</b>";

    echo <<<HTML
<div>
    $title:
    <input type="text" id="cooccurrence" name="cooccurrence" maxlength="3" size="4">
    <div class="left-margin-70">
        This option allows to filter the neighboring pFAMs with a co-occurrence percentage lower than the set value.
        The default value is $default_cooc and valid values are 0-100.
    </div>
</div>
HTML;
}


function add_neighborhood_size_setting($use_header = true) {
    $neighbor_size_html = "";
    $default_neighbor_size = settings::get_default_neighbor_size();
    for ($i=3;$i<=20;$i++) {
        if ($i == $default_neighbor_size)
            $neighbor_size_html .= "<option value='" . $i . "' selected='selected'>" . $i . "</option>";
        else
            $neighbor_size_html .= "<option value='" . $i . "'>" . $i . "</option>";
    }

    $title = "Neighbhorhood Size";
    if ($use_header)
        echo <<<HTML
<h3>$title</h3>
HTML;

    $title = $use_header ? "Size" : "<b>$title</b>";

    echo <<<HTML
<div>
    $title:
    <select name="neighbor_size" id="neighbor_size" class="bigger">
        <?php echo $neighbor_size_html; ?>
    </select>
    <div class="left-margin-70">
        With a value of $default_neighbor_size the PFAM families for $default_neighbor_size
        genes located upstream and for $default_neighbor_size genes
        located downstream of sequences in the SNN will be collected and displayed.
        The default value is $default_neighbor_size.
    </div>
</div>
HTML;
}


function add_dev_site_options($option_id, $use_header = true) {
    if ($use_header)
        echo <<<HTML
<h3>Dev Site Options</h3>
HTML;
    echo <<<HTML
<div>
    <div>
        Database version:
HTML;
    add_db_mod_html($option_id);
    echo <<<HTML
    </div>
</div>
HTML;
}


function add_db_mod_html($option_id) {
    $db_modules = global_settings::get_database_modules();
    if (count($db_modules) < 2)
        return;
    
    echo <<<HTML
        <select name="$option_id" id="$option_id" class="bigger">
HTML;
    foreach ($db_modules as $mod) {
        $mod_name = $mod[1];
        echo "            <option value=\"$mod_name\">$mod_name</option>\n";
    }

    echo <<<HTML
        </select>
HTML;
}


function add_database_option($db_id) {
    global $use_advanced_options;
    if (!$use_advanced_options)
        return;
    echo <<<HTML
                                <tr>
                                    <td>Database version:</td>
                                    <td>
HTML;
    add_db_mod_html($db_id);
    echo <<<HTML
                                    </td>
                                    <td>
                                    </td>
                                </tr>
HTML;
}


function add_window_option($nb_id) {
    $default_nb_size = settings::get_default_neighborhood_size();
    echo <<<HTML
                                <tr>
                                    <td>Neighborhood window size:</td>
                                    <td>
                                        <input type="text" id="$nb_id" class="small" name="nb-size" value="$default_nb_size">
                                    </td>
                                    <td>
                                        Number of neighbors to retrieve on either side of the query sequence for each BLAST result
                                        (default: $default_nb_size)
                                    </td>
                                </tr>
HTML;
}


function add_job_title_option($title_id) {
    return <<<HTML
                                <tr>
                                    <td>Optional job title:</td>
                                    <td>
                                        <input type="text" class="small" id="$title_id" name="title" value="">
                                    </td>
                                    <td></td>
                                </tr>
HTML;
}


function add_blast_options() {
}


function add_submit_button($type, $email_id, $message_id, $btn_text) {
    global $message;
    if (!isset($message))
        $message = "";
    global $user_email;
    echo <<<HTML
<p>
    E-mail address: 
    <input name="$email_id" id="$email_id" type="text" value="$user_email" class="email" onfocus="if(!this._haschanged){this.value=''};this._haschanged=true;"><br>
    When the file has been uploaded and processed, you will receive an e-mail containing a link
    to download the data.
</p>
<div id="$message_id" style="color: red">
    $message
</div>
<center>
    <div><button type="button" id="ssn_submit" name="ssn_submit" class="dark"
HTML;
    $type = "get_submit_${type}_code";
    $js_code = $type();
    echo <<<HTML
            $js_code
            >$btn_text</button>
    </div>
</center>
HTML;
}


function get_submit_diagram_upload_code() {
    return <<<HTML
                                onclick="uploadDiagramFile('diagram_file','upload_diagram_form','progress_number_diagram',
                                    'progress_bar_diagram','diagram_message','diagram_email','diagram_submit')"
HTML;
}


function get_submit_diagram_fasta_code() {
    return <<<HTML
                                            onclick="submitOptionCForm('create_diagram.php', 'option-c-option', 'option-c-input',
                                                'option-c-title', 'option-c-email', 'option-c-nb-size', 'option-c-file',
                                                'option-c-progress-number', 'option-c-progress-bar', 'option-c-message',
                                                'option-c-db-mod');"
HTML;
}


function get_submit_diagram_id_code() {
    return <<<HTML
                                            onclick="submitOptionDForm('create_diagram.php', 'option-d-option', 'option-d-input',
                                                'option-d-title', 'option-d-email', 'option-d-nb-size', 'option-d-file',
                                                'option-d-progress-number', 'option-d-progress-bar', 'option-d-message',
                                                'option-d-db-mod');"
HTML;
}


function get_submit_diagram_blast_code() {
    return <<<HTML
                                            onclick="submitOptionAForm('create_diagram.php', 'option-a-option', 'option-a-input',
                                                                       'option-a-title', 'option-a-evalue', 'option-a-max-seqs',
                                                                       'option-a-email', 'option-a-nb-size', 'option-a-message',
                                                                       'option-a-db-mod');"
HTML;
}


function get_submit_gnn_code() {
    global $submit_est_args;
    global $est_id;
    if ($est_id) {
        return <<<HTML
                            onclick="submitEstJob('upload_form','ssn_message','ssn_email','ssn_submit',$submit_est_args)"
HTML;
    } else {
        return <<<HTML
                            onclick="uploadSsn('ssn_file','upload_form','progress_number','progress_bar','ssn_message','ssn_email','ssn_submit')"
HTML;
    }
}


?>

<?php require_once("inc/footer.inc.php"); ?>


