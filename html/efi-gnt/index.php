<?php
require_once(__DIR__."/../../conf/settings_paths.inc.php");
require_once(__GNT_DIR__."/includes/main.inc.php");
require_once(__BASE_DIR__ . "/libs/ui.class.inc.php");
require_once(__GNT_DIR__."/libs/user_jobs.class.inc.php");
require_once(__GNT_DIR__."/libs/settings.class.inc.php");
require_once(__GNT_DIR__."/libs/gnt_ui.class.inc.php");

$user_email = "Enter your e-mail address";

$IsLoggedIn = false;
$showPreviousJobs = false;
$gnnJobs = array();
$diagramJobs = array();
$IsAdminUser = false;
$trainingJobs = array();
$max_debug = false;

if (settings::get_recent_jobs_enabled() && user_jobs::has_token_cookie()) {
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
$est_file_name = "";
$submit_est_args = "";
if (isset($_GET["est-id"]) && isset($_GET["est-key"]) && isset($_GET["est-ssn"])) {
    $the_aid = $_GET["est-id"];
    $the_key = $_GET["est-key"];
    $the_idx = $_GET["est-ssn"];

    $job_info = global_functions::verify_est_job($db, $the_aid, $the_key, $the_idx);
    if ($job_info !== false) {
        $est_file_info = global_functions::get_est_filename($job_info, $the_aid, $the_idx);
        if ($est_file_info !== false) {
            $est_id = $job_info["generate_id"];
            $est_file_name = $est_file_info["filename"];

            $submit_est_args = "'$the_aid','$the_key','$the_idx'";
        }
    }
}


$use_advanced_options = global_settings::advanced_options_enabled();

$updateMessage = 
    'The ENA database that is used for GNT was last updated in April 2020. We are actively working on updating this database to a more current version.<br>' .
    //"The \"From the Bench\" article describing the tools and their use is available on the " . 
    //"<i class='fas fa-question'></i> <b>Training</b> page.<br>" .
    //"Access to videos about the use of Cytoscape for interacting with SSNs is also available on the same page.<br>" .
    "<small>" . functions::get_update_message() . "</small>";

require_once(__DIR__."/inc/header.inc.php");

?>


<p>
EFI-GNT allows exploration of the genome neighborhoods for sequence similarity
network (SSN) clusters in order to facilitate the 
assignment of function within protein families and superfamilies.
</p>

<p>
In <b>GNT Submission</b>, each sequence within a SSN is used as a query for interrogation of its genome neighborhood.
A colored SSN identifying 
clusters, Genome Neighborhood Networks (GNNs) providing statistical analysis of 
neighboring Pfam families, Genome Neighborhood Diagrams (GNDs), sets of IDs and 
sequences per cluster and additional files are created. For the <b>Retrieve
Neighorhood Diagrams</b> option, only GNDs will be created.
</p>


<div id="update-message" class="update-message initial-hidden">
<div class="new-feature"></div>
<?php if (isset($updateMessage)) echo $updateMessage; ?>
</div>

<p>
A listing of new features and other information pertaining to GNT is available on the <a href="notes.php">release notes page</a>. 
</p>

<div class="tabs-efihdr tabs">
    <ul class="tab-headers">
<?php if ($showPreviousJobs) { ?>
        <li <?php if (!$est_id) echo "class=\"ui-tabs-active\""; ?>><a href="#jobs">Previous Jobs</a></li>
<?php } ?>
        <li <?php if ($est_id) echo "class=\"ui-tabs-active\""; ?>><a href="#create">GNT Submission</a></li>
        <li><a href="#create-diagrams">Retrieve Neighborhood Diagrams</a></li>
        <li><a href="#diagrams">View Saved Diagrams</a></li>
        <li <?php if (! $showPreviousJobs) echo "class=\"ui-tabs-active\""; ?>><a href="#tutorial">Tutorial</a></li>
    </ul>

    <div class="tab-content">
<?php if ($showPreviousJobs) { ?>
        <div id="jobs" class="tab <?php echo $est_id ? "" : "active"; ?>">
<!--
            <div style="margin-top: 10px">
            <div style="float: right">
                <a href="#gnd"><button class="mini"><i class="fas fa-angle-double-down"></i> Retrieve Neighborhood Jobs</button></a>
                <a href="#training"><button class="mini"><i class="fas fa-angle-double-down"></i> Training Resources</button></a>
            </div>
            <div style="float: left">
            <h4>GNT Jobs</h4>
            </div>
            <div style="clear: both"></div>
            </div>
-->

<?php if (count($gnnJobs) > 0) { ?>
            <h4>GNT Jobs</h4>
            <table class="pretty-nested" style="table-layout:fixed">
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
            <a name="gnd"></a>
            <h4>Retrieved Genomic Neighborhood Diagrams</h4>
            <table class="pretty" style="table-layout:fixed">
                <thead>
                    <th class="id-col">ID</th>
                    <th class="name-col">Job Name</th>
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

    $version = isset($diagramJobs[$i]["db_version"]) ? $diagramJobs[$i]["db_version"] : 0;
    $script = $version >= 3 ? "view_diagrams_v3.php" : "view_diagrams.php";

    $idField = $diagramJobs[$i]["id_field"];
    $jobType = $diagramJobs[$i]["verbose_type"];
    $linkStart = $isActive ? "" : "<a href=\"$script?$idField=$id&key=$key\">";
    $linkEnd = $isActive ? "" : "</a>";

    echo <<<HTML
                    <tr>
                        <td>$linkStart${id}$linkEnd</td>
                        <td>$linkStart<span class='job-name'>$name</span><br><span class='job-metadata'>$jobType</span>$linkEnd</td>
                        <td>$dateCompleted <div style="float:right" class="archive-btn" data-type="diagram" data-id="$id" data-key="$key" title="Archive Job"><i class="fas fa-trash-alt"></i></div></td>
                    </tr>
HTML;
}
?>
                </tbody>
            </table>
<?php } ?>



<?php
if (count($trainingJobs) > 0) {
    gnt_ui::output_job_list($trainingJobs);
}
?>




        </div>
<?php } ?>

        <div id="create" class="tab <?php echo $est_id ? "ui-tabs-active" : ""; ?>">
            <p>
            In a submitted SSN, each sequence is considered as a query. Information 
            associated with protein encoding genes that are neighbors of input queries 
            (within a defined window on either side) are collected from sequence files for 
            bacterial (prokaryotic and archaeal) and fungal genomes in the European 
            Nucleotide Archive (ENA) database.
            The neighboring genes are sorted into 
            neighbor Pfam families. For each cluster, the co-occurrence frequencies of the 
            identified neighboring Pfam families with the input queries are calculated. 
            </p>

            <form name="upload_form" id="upload_form" method="post" action="" enctype="multipart/form-data">
                <div class="primary-input">
                <?php echo ui::make_upload_box("SSN File:", "ssn_file", "progress_bar", "progress_number", "", "", $est_file_name); ?>
                    SSNs generated by EFI-EST are compatible with GNT analysis (with the exception 
                    of SSNs from the FASTA sequences without the "Read FASTA header" option), even 
                    when they have been modified in Cytoscape.
                    The accepted format is XGMML (or compressed XGMML as zip).
                </div>

                <p>
                If a SSN that contains UniRef sequences is uploaded to the GNT,
                the resulting GNDs will also include GNDs for UniRef90
                cluster IDs that group together UniProt sequences by 90% sequence identiy.
                For networks that also contain UniRef50 sequences, GNDs
                will also include UniProt sequences that are grouped by 50% sequence identity.
                </p>
    
                <?php gnt_ui::add_neighborhood_size_setting(false); ?>
                <?php gnt_ui::add_cooccurrence_setting(false); ?>
                <?php gnt_ui::add_advanced_options(); ?>
                <?php add_submit_button("gnn", "ssn_email", "ssn_message", "Generate GNN"); ?>
            </form>
        </div>

        <div id="create-diagrams" class="tab">
            <div style="margin-bottom: 10px;">Clicking on the headers below provides access to various ways of generating genomic neighborhood diagrams.</div>
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
                    <p>
                    <b>
                    If the Sequence Database is set to UniRef90, the resulting GNDs will also include GNDs for UniRef90
                    cluster IDs that group together UniProt sequences by 90% sequence identiy.  For UniRef50, the GNDs
                    will also include UniProt sequences that are grouped by 50% sequence identity.  Any of the
                    "Exclude Fragments" options will exclude UniProt-defined sequence fragments.
                    </b>
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
                                <td><b><label for="max-seqs">Maximum BLAST Sequences:</label></b></td>
                                <td>
                                     <input type="text" id="option-a-max-seqs" class="small" name="max-seqs" value="<?php echo $default_seq; ?>">
                                </td>
                                <td>
                                    Maximum number of sequences retrieved (&le; <?php echo $max_seq; ?>;
                                    default: <?php echo $default_seq; ?>)
                                </td>
                            </tr>
                            <tr>
                                 <td><b>E-Value:</b></td>
                                 <td>
                                     <input type="text" class="small" id="option-a-evalue" name="evalue" value="<?php echo $default_evalue; ?>">
                                 </td>
                                 <td>
                                     Negative log of e-value for all-by-all BLAST (&ge; 1; default: <?php echo $default_evalue; ?>)
                                 </td>
                            </tr>
<?php add_window_option("option-a-nb-size"); ?>
<?php add_database_option("option-a-db-mod"); ?>
<?php add_sequence_type_option("option-a-seq-type"); ?>
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
                    <p>
                    <b>
                    If the Sequence Database is set to UniRef90, the resulting GNDs will also include GNDs for UniRef90
                    cluster IDs that group together UniProt sequences by 90% sequence identiy.  For UniRef50, the GNDs
                    will also include UniProt sequences that are grouped by 50% sequence identity.  Any of the
                    "Exclude Fragments" options will exclude UniProt-defined sequence fragments.
                    </b>
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
<?php add_sequence_type_option("option-d-seq-type"); ?>
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
                <b>Upload a saved diagram data file for visualization.</b>

                </p>

                <p>
                    <?php echo ui::make_upload_box("Select a File to Upload:", "diagram_file", "progress_bar_diagram", "progress_number_diagram", "The acceptable format is sqlite."); ?>
                </p>
    
                <?php add_submit_button("diagram_upload", "diagram_email", "diagram_message", "Upload Diagram Data"); ?>
            </form> 
        </div>

        <div id="tutorial" class="tab <?php if (!$showPreviousJobs) echo "ui-tabs-active"; ?>">
            <h3>EFI-Genome Neighborhood Tool Overview</h3>
    
            <p>
            The EFI-GNT (EFI Genome Neighborhood Tool)
            is focused on placing protein families and superfamilies into a genomic context. A sequence similarity network (SSN) 
            is used as an input. Each sequence within a SSN is used as a query for interrogation of 
            its genome neighborhood.
            </p>
    
            <p>
            EFI-GNT enables 
            exploration of the genome neighborhoods for sequences in SSN clusters 
            in order to facilitate their assignment of function.
            </p>
    
            <h3>EFI-GNT Acceptable Input</h3>
    
            <p>
            EFI-GNT is compatible with SSN generated by the EFI-Enzyme Similarity Tool
            (EFI-EST). Acceptable SSNs are generated for an entire Pfam and/or InterPro
            protein family (EFI-EST option B), a focused region of a family (option A), a
            set of protein sequence that can be identified from FASTA headers (from option
            C with “Header Reading” activated) or a list of recognizable UniProt and/or
            NCBI IDs (from option D). SSNs manually modified within Cytoscape are accepted.
            SSNs that have been colored using the "Color SSN Utility" are also accepted.
            SSNs generated from FASTA sequences (option C) without the "Read Header" option
            activated are not accepted.
            </p>
    
            <h3>Principle of GNT Analysis</h3>
            <p>
            EFI-GNT provides statistical analysis, per SSN cluster, of genome context
            for bacterial, archeal and fungal sequences, in order to identify possible
            functional linkage. Sequences from the SSN analyzed are used as query for
            retrieval of their genome neighborhood. The user specifies the neighborhood
            size (&plusmn;N orfs from the SSN query) and minimum query-neighbor co-occurrence
            frequency for the outputs. 
            </p>

            <h3>EFI-GNT Output</h3>
    
            <p>
            EFI-GNT identifies each SSN cluster and assigns it a unique color. A colored SSN is
            produced. It then interrogates the European
            <a href="https://www.sciencedirect.com/topics/chemistry/nucleotide">Nucleotide</a>
            Archive (ENA; <a href="https://www.ebi.ac.uk/ena">https://www.ebi.ac.uk/ena</a>)
            to obtain the genome contexts of each sequence,
            sorts neighbors into Pfam families, and provides three specific outputs.
            Firstly, a GNN network in which each SSN cluster is a hub node with its spoke
            nodes identified neighboring Pfam families (for identifying candidates for
            pathway enzymes); secondly, a GNN network in which each neighbor Pfam family is
            a hub node with its spoke nodes that SSN clusters that identify this Pfam as a
            neighbor (for identifying divergent clusters that are orthologues); and
            thirdly, genome neighborhood diagrams (GNDs) for visual representations of the
            neighborhoods for the sequences in each SSN cluster (for visual inspection of
            <a href="https://www.sciencedirect.com/topics/biochemistry-genetics-and-molecular-biology/synteny">synteny</a>
            and the presence/absence of functionally linked proteins).
            
            <h3>Direct Genomic Neighborhood Diagrams (GND) Generation</h3>
            <p>
            The "Retrieve neighborhood diagrams" allows exploring of neighboring genes for
            specific queries. You can submit a single sequence that is used as the query
            for a BLAST search of the UniProt database. The retrieved sequences are used to
            generate GNDs. GNDs can be generated from a provided list of IDs or even from
            FASTA sequences, by collecting IDs from FASTA headers. 
            </p>

            <center>
                <p><img src="images/tutorial/intro_figure_1.jpg"></p>
                <p><i>Figure 1:</i> Examples of colored SSN (left) and a hub-and-spoke cluster from a GNN (right).</p>
            </center>
    
            <h3>Recommended Reading</h3>
            
            <p>
            R&eacute;mi Zallot, Nils Oberg, John A. Gerlt, <b>"Democratized" genomic enzymology web 
            tools for functional assignment</b>, Current Opinion in Chemical Biology, Volume 
            47, 2018, Pages 77-85,
            <a href="https://doi.org/10.1016/j.cbpa.2018.09.009">https://doi.org/10.1016/j.cbpa.2018.09.009</a>
            </p>
            
            <p>
            John A. Gerlt,
            <b>Genomic enzymology: Web tools for leveraging protein family sequence–function space and genome context to discover novel functions</b>,
            Biochemistry, 2017 - ACS Publications
            </p>

            <p class="center"><a href="tutorial.php"><button class="light">Continue Tutorial</button></a></p>
    
        </div>
    </div> <!-- tab-content -->
</div> <!-- tabs -->


<div align="center">
    <p>
    UniProt Version: <b><?php echo settings::get_uniprot_version(); ?></b><br>
    InterPro Version: <b><?php echo settings::get_interpro_version(); ?></b><br>
    ENA Version: <b><?php echo settings::get_ena_version(); ?></b>
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

function add_dev_site_options($option_id, $use_header = true) {
    if ($use_header)
        echo <<<HTML
<h3>Dev Site Options</h3>
HTML;
    echo <<<HTML
<div>
    <div>
        <span class="input-name">Database version:</span>
        <span class="input-field">
HTML;
    add_db_mod_html($option_id);
    echo <<<HTML
        </span>
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
                                    <td><b>Database version:</b></td>
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


function add_sequence_type_option($seq_id) {
    echo <<<HTML
                                <tr>
                                    <td><b>Sequence database:</b></td>
                                    <td>
<select id="$seq_id" name="seq-type">
<option value="uniprot" selected>UniProt</option>
<option value="uniprot-nf">UniProt - Exclude fragments</option>
<option value="uniref50">UniRef50</option>
<option value="uniref50-nf">UniRef50 - Exclude fragments</option>
<option value="uniref90">UniRef90</option>
<option value="uniref90-nf">UniRef90 - Exclude fragments</option>
</select>
                                    </td>
                                    <td>
Sequence database for retrieving sequences (default: UniProt)
                                    </td>
                                </tr>
HTML;
}


function add_window_option($nb_id) {
    $default_nb_size = settings::get_default_neighborhood_size();
    echo <<<HTML
                                <tr>
                                    <td><b>Neighborhood window size:</b></td>
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
    echo <<<HTML
                                <tr>
                                    <td><b>Optional job title:</b></td>
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
    <span class="input-name">
        E-mail address: 
    </span>
    <span class="input-field">
        <input name="$email_id" id="$email_id" type="text" value="$user_email" class="email" onfocus="if(!this._haschanged){this.value=''};this._haschanged=true;">
    </span>
</p>
<p>You will receive an e-mail when your network has been processed.</p>
<div id="$message_id" class="error-message"> 
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
                                                'option-d-db-mod', 'option-d-seq-type');"
HTML;
}


function get_submit_diagram_blast_code() {
    return <<<HTML
                                            onclick="submitOptionAForm('create_diagram.php', 'option-a-option', 'option-a-input',
                                                                       'option-a-title', 'option-a-evalue', 'option-a-max-seqs',
                                                                       'option-a-email', 'option-a-nb-size', 'option-a-message',
                                                                       'option-a-db-mod', 'option-a-seq-type');"
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

<?php require_once(__DIR__."/inc/footer.inc.php"); ?>


