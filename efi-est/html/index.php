<?php

const RT_GENERATE = 1;
const RT_COLOR = 2;
const RT_ANALYSIS = 3;
const RT_NESTED_COLOR = 4;

require_once("../includes/main.inc.php");
require_once("../libs/user_jobs.class.inc.php");
require_once("../libs/est_ui.class.inc.php");
require_once(__BASE_DIR__ . "/libs/global_settings.class.inc.php");
require_once(__BASE_DIR__ . "/includes/login_check.inc.php");
require_once(__BASE_DIR__ . "/libs/ui.class.inc.php");

$userEmail = "Enter your e-mail address";

$showJobsTab = false;
$showTrainingJobsTab = false;
$jobs = array();
$tjobs = array(); // training jobs
$userGroups = array();
$IsAdminUser = false;
if (global_settings::get_recent_jobs_enabled() && user_auth::has_token_cookie()) {
    $userJobs = new user_jobs();
    $userJobs->load_jobs($db, user_auth::get_user_token());
    $jobs = $userJobs->get_jobs();
    $tjobs = $userJobs->get_training_jobs();
    $userEmail = $userJobs->get_email();
    $userGroups = $userJobs->get_groups();
    $IsAdminUser = $userJobs->is_admin();
    $showJobsTab = has_jobs($jobs) || has_jobs($tjobs);
    $showTrainingJobsTab = count($tjobs) > 0;
}
$showTrainingJobsTab = false; // currently we don't want it to be displayed since we put the training jobs below the previous jobs.

$max_full_family = number_format(functions::get_maximum_full_family_count(), 0);

$useAdvancedFamilyInputs = global_settings::advanced_options_enabled();

$db_modules = global_settings::get_database_modules();

$updateMessage =
    "The EFI web tool interface has been updated to improve user experience.<br>" .
    "<b>All functions remain unchanged.</b><br><br>" . 
    "<small>" . functions::get_update_message() . "</small>";


$IncludeSubmitJs = true;
require_once "inc/header.inc.php";

?>


<p></p>
<p>
A sequence similarity network (SSN) allows for visualization of relationships among 
protein sequences. In SSNs, the most related proteins are grouped together in 
clusters.
The Enzyme Similarity Tool (EFI-EST) makes it possible to easily generate SSNs.
<a href="http://www.cytoscape.org/">Cytoscape</a> is used to explore SSNs.
</p>


<div id="update-message" class="update-message">
<?php if (isset($updateMessage)) echo $updateMessage; ?>
</div>


<p>
A listing of new features and other information pertaining to EST is available on the
<a href="notes.php">release notes</a> page.
</p>

<p>
<a href="https://www.ebi.ac.uk/interpro/search/sequence-search">InterProScan sequence search</a> can be used to
find matches within the InterPro database for a given sequence.
</p>

<p>
Information on Pfam families and clans and InterPro family sizes is available on
the <a href="family_list.php">Family Information page</a>.
</p>

<div class="tabs-efihdr ui-tabs ui-widget-content" id="main-tabs"> <!-- style="display:none">-->
    <ul class="ui-tabs-nav ui-widget-header">
<?php if ($showJobsTab) { ?>
        <li class="ui-tabs-active"><a href="#jobs">Previous Jobs</a></li>
<?php } ?>
<?php if ($showTrainingJobsTab) { ?>
        <li><a href="#tjobs">Training</a></li>
<?php } ?>
<?php if (functions::option_a_enabled()) { ?>
        <li><a href="#optionAtab" title="Option A">Sequence BLAST</a></li>
<?php } ?>
<?php if (functions::option_b_enabled()) { ?>
        <li><a href="#optionBtab" title="Option B">Families</a></li> <!-- Pfam and/or InterPro families</a></li>-->
<?php } ?>
<?php if (functions::option_c_enabled()) { ?>
        <li><a href="#optionCtab" title="Option C">FASTA</a></li>
<?php } ?>
<?php if (functions::option_d_enabled()) { ?>
        <li><a href="#optionDtab" title="Option D">Accession IDs</a></li>
<?php } ?>
<?php if (functions::option_e_enabled()) { ?>
        <li><a href="#optionEtab" title="Option E">OptE</a></li>
<?php } ?>
<?php if (functions::colorssn_enabled()) { ?>
        <li><a href="#colorssntab">Color SSNs</a></li>
<?php } ?>
        <li <?php echo ($showJobsTab ? "" : 'class="ui-tabs-active"') ?>><a href="#tutorial">Tutorial</a></li>
    </ul>

    <div>
<?php if ($showJobsTab) { ?>
        <div id="jobs" class="ui-tabs-panel ui-widget-content">

<?php /*
            <h3>Precomputed Option B Jobs</h3>
            Precomputed jobs for selected families are available 
            <a href="precompute.php">here</a>.
            <!--<a href="precompute.php"><button type="button" class="mini">Precomputed Option B Jobs</button></a>-->
*/?>
            <h4>EST Jobs</h4>
<?php 
    $show_archive = true;
    output_job_list($jobs, $show_archive);

    if (has_jobs($tjobs)) {
        echo "            <h4>Training Resources</h4>\n";
        output_job_list($tjobs);
    }
?>
         </div>
<?php
} ?>

<?php if ($showTrainingJobsTab) {
    echo "        <div id=\"tjobs\" class=\"tab\">\n";
    output_job_list($tjobs);
    echo "        </div>\n";
} ?>

<?php if (functions::option_a_enabled()) { ?>
        <div id="optionAtab" class="ui-tabs-panel ui-widget-content">
            <p class="p-heading">
            Generate a SSN for a single protein and its closest homologues in the UniProt database.
            </p>

            <p>
            The input sequence is used as the query for a search of the UniProt database using BLAST.
            Sequences that are similar to the query in UniProt are retrieved.
            <?php add_blast_calc_desc(); ?>
            </p>

            <form name="optionAform" id="optionAform" method="post" action="" enctype="multipart/form-data">
                <div class="primary-input">
                    <div class="secondary-name">
                        Query Sequence:
                    </div>
                    <textarea id="blast-input" name="blast-input"></textarea>
                    Input a single <b>protein sequence</b> only.
            The default maximum number of  retrieved sequences is
            <?php echo number_format(functions::get_default_blast_seq(),0); ?>.
                </div>

                <div class="option-panels">
                    <div>
                        <h3>BLAST Retrieval Options</h3>
                        <div>
                            <div>
                                <span class="input-name">
                                    UniProt BLAST query e-value:
                                </span><span class="input-field">
                                    <input type="text" class="small" id="blast-evalue" name="blast-evalue" size="5"
                                        value="<?php echo functions::get_evalue(); ?>">
                                    Negative log of e-value for retrieving similar sequences (&ge; 1; default: <?php echo functions::get_evalue(); ?>)
                                </span>
                                <div class="input-desc">
                                    Input an alternative e-value for BLAST to retrieve sequences from the
                                    UniProt database. We suggest using a larger e-value
                                    (smaller negative log) for retrieving homologues if the query
                                    sequence is short and a smaller e-value (larger negative log) if there
                                    is no need to retrieve divergent homologues.
                                </div>
                            </div>
                            <div>
                                <span class="input-name">
                                    Maximum number of sequences retrieved:
                                </span><span class="input-field">
                                    <input type="text" id="blast-max-seqs" class="small" name="blast-max-seqs" value="<?php  echo functions::get_default_blast_seq(); ?>" size="5">
                                    (&le; <?php echo number_format(functions::get_max_blast_seq()); ?>,
                                    default: <?php echo number_format(functions::get_default_blast_seq()); ?>)
                                </span>
                            </div>
                        </div>
                    </div>
    
                    <div>
                        <?php add_family_input_option("opta"); ?>
                    </div>

                    <div>
                        <?php add_ssn_calc_option("opta") ?>
                    </div>

                    <?php if ($useAdvancedFamilyInputs) { ?>
                    <div>
                        <?php add_dev_site_option("opta", $db_modules); ?>
                    </div>
                    <?php } ?>
                </div>

                <?php add_submit_html("opta", "optAoutputIds", $userEmail); ?>
            </form>
        </div>
<?php } ?>

<?php if (functions::option_b_enabled()) { ?>
        <div id="optionBtab" class="ui-tabs-panel ui-widget-content">
            <p class="p-heading">
            Generate a SSN for a protein family.
            </p>

            <p>
            The sequences from the Pfam families, InterPro families, and/or Pfam clans (superfamilies) input are retrieved.
            <?php add_blast_calc_desc(); ?>
            </p>

            <form name="optionBform" id="optionBform" method="post" action="">
                <?php add_family_input_option_family_only("optb"); ?>

                <div class="option-panels">
                    <div>
                        <?php add_domain_option("optb"); ?>
                    </div>
                    <div>
                        <h3>Protein Family Option</h3>
                        <?php echo get_fraction_html("optb"); ?>
                    </div>
                    <div>
                        <?php add_ssn_calc_option("optb") ?>
                    </div>
                    <?php if ($useAdvancedFamilyInputs) { ?>
                    <div>
                        <?php add_dev_site_option("optb", $db_modules, get_advanced_seq_html("optb")); ?>
                    </div>
                    <?php } ?>
                    <?php if (!$useAdvancedFamilyInputs) { ?>
                        <input type="hidden" id="seqid-optb" value="">
                        <input type="hidden" id="length-overlap-optb" value="">
                    <?php } ?>
                </div>

                <?php add_submit_html("optb", "optBoutputIds", $userEmail); ?>
            </form>
        </div>
<?php } ?>

<?php    if (functions::option_c_enabled()) { ?>
        <div id="optionCtab" class="ui-tabs-panel ui-widget-content">
            <p class="p-heading">
            Generate a SSN from provided sequences. 
            </p>

            <p>
            <?php add_blast_calc_desc(); ?>
            </p>

            <p>
            Input a list of protein sequences in FASTA format or upload a FASTA-formatted sequence file.
            </p>
            
            <form name="optionCform" id="optionCform" method="post" action="">
                <div class="primary-input">
                    <div class="secondary-name">
                        Sequences:
                    </div>
                    <textarea id="fasta-input" name="fasta-input"></textarea>
                    <div>
                        <input type="checkbox" id="fasta-use-headers" name="fasta-use-headers" value="1"> <label for="fasta-use-headers"><b>Read FASTA headers</b></label><br>
                        When selected, recognized UniProt or Genbank identifiers from FASTA headers are used to retrieve
                        node attributes from the UniProt database.
                    </div>
                    <?php echo ui::make_upload_box("FASTA File:", "fasta-file", "progress-bar-fasta", "progress-num-fasta"); ?>
                </div>

                <div class="option-panels">
                    <div>
                        <?php add_family_input_option("optc"); ?>
                    </div>
                    <div>
                        <?php add_ssn_calc_option("optc") ?>
                    </div>
                    <?php if ($useAdvancedFamilyInputs) { ?>
                    <div>
                        <?php add_dev_site_option("optc", $db_modules); ?>
                    </div>
                    <?php } ?>
                </div>

                <?php add_submit_html("optc", "optCoutputIds", $userEmail); ?>
            </form>
        </div>
<?php    } ?>

<?php    if (functions::option_d_enabled()) { ?>
        <div id="optionDtab" class="ui-tabs-panel ui-widget-content">
            <p class="p-heading">
            Generate a SSN from a list of UniProt, UniRef, NCBI, or Genbank IDs.
            </p>

            <p>
            <?php add_blast_calc_desc(); ?>
            </p>

            <form name="optionDform" id="optionDform" method="post" action="">
                <div class="tabs tabs-efihdr" id="optionD-src-tabs">
                    <ul class="tab-headers">
                        <li class="ui-tabs-active"><a href="#optionD-source-uniprot">Use UniProt IDs</a></li>
                        <li><a href="#optionD-source-uniref">Use UniRef50 or UniRef90 Cluster IDs</a></li>
                    </ul>
                    <div class="tab-content" style="min-height: 250px">
                        <div id="optionD-source-uniprot" class="tab ui-tabs-active">
                            <p>
                            Input a list of UniProt, NCBI, or Genbank (protein) accession IDs, or upload a text
                            file.
                            </p>
                            <div class="primary-input">
                                <div class="secondary-name">
                                    Accession IDs:
                                </div>
                                <textarea id="accession-input-uniprot" name="accession-input-uniprot"></textarea>
                                <div>
<?php echo ui::make_upload_box("Accession ID File:", "accession-file-uniprot", "progress-bar-accession-uniprot", "progress-num-accession-uniprot"); ?>
                                </div>
                            </div>
                        </div>
                        <div id="optionD-source-uniref" class="ui-tabs-panel ui-widget-content">
                            <p>
                            Input a list of UniRef50 or UniRef90 cluster accession IDs, or upload a text
                            file.
                            </p>
                            <div class="primary-input">
                                <div class="secondary-name">
                                    Accession IDs:
                                </div>
                                <textarea id="accession-input-uniref" name="accession-input-uniref"></textarea>
                                <div>
<?php echo ui::make_upload_box("Accession ID File:", "accession-file-uniref", "progress-bar-accession-uniref", "progress-num-accession-uniref"); ?>
                                </div>
                                <div id="accession-seq-type-container" style="margin-top:15px">
                                    <span class="input-name">Input accession IDs are:</span>
                                    <select id="accession-seq-type">
                                        <option value="uniref90">UniRef90 cluster IDs</option>
                                        <option value="uniref50">UniRef50 cluster IDs</option>
                                    </select>
                                    <a class="question" title="
                                        The list of sequences that is put into
                                        the tool will be end up being the node IDs, and node attributes with the UniRef clusters
                                        will be included in the output SSN.">?</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="option-panels">
                    <div>
                        <?php add_domain_option("optd", true, $useAdvancedFamilyInputs); ?>
                    </div>

                    <div>
                        <?php add_family_input_option("optd"); ?>
                    </div>
                    <div>
                        <?php add_ssn_calc_option("optd") ?>
                    </div>
                    <?php if ($useAdvancedFamilyInputs) { ?>
                    <div>
                        <?php add_dev_site_option("optd", $db_modules); ?>
                    </div>
                    <?php } ?>
                </div>

                <?php add_submit_html("optd", "optDoutputIds", $userEmail); ?>
            </form>
        </div>
<?php    } ?>

<?php if (functions::option_e_enabled()) { ?>
        <div id="optionEtab" class="ui-tabs-panel ui-widget-content">
            <form name="optionEform" id="optionEform" method="post" action="">
                <?php add_family_input_option_family_only("opte"); ?>

                <div class="option-panels">
                    <div>
                        <?php add_domain_option("opte"); ?>
                    </div>
                    <div>
                        <?php add_ssn_calc_option("opte") ?>
                    </div>
                    <div>
                        <h3>Protein Family Option</h3>
                        <?php echo get_fraction_html("opte"); ?>
                    </div>
                    <div>
                        <?php add_dev_site_option("opte", $db_modules, get_advanced_seq_html("opte")); ?>
                    </div>
                </div>
    
                <?php add_submit_html("opte", "optEoutputIds", $userEmail); ?>
            </form>
        </div>
<?php } ?>

<?php    if (functions::colorssn_enabled()) { ?>
        <div id="colorssntab" class="ui-tabs-panel ui-widget-content">
            <p>
                <b>Clusters in the submitted SSN are identified, numbered and colored.</b>
                Summary tables, sets of IDs and sequences per cluster are provided.
            </p>

            <form name="colorSsnForm" id="colorSsnform" method="post" action="">
                <div class="primary-input">
<?php echo ui::make_upload_box("SSN File:", "colorssn-file", "progress-bar-colorssn", "progress-num-colorssn"); ?>
                    <div>
                        A Cytoscape-edited SNN can serve as input.
                        The accepted format is XGMML (or compressed XGMML as zip).
                    </div>
                </div>

                <?php if ($useAdvancedFamilyInputs) { ?>
                <div class="option-panels">
                    <div>
                        <h3>Dev Site Options</h3>
                        <div>
                            <div>
                                <span class="input-name">
                                    Extra RAM:
                                </span><span class="input-field">
                                    <input type="checkbox" id="colorssn-extra-ram" name="colorssn-extra-ram" value="1">
                                    <label for="colorssn-extra-ram">Check to use additional RAM (800GB) [default: off (350GB)]</label>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
                <?php add_submit_html("colorssn", "", $userEmail); ?>
            </form>
        </div>
<?php    } ?>

        <div id="tutorial" class="tab <?php echo (!$showJobsTab ? "ui-tabs-active" : "") ?>">

            <h3>Overview of possible inputs for EFI-EST</h3>
            
            <p>
            The EFI - Enzyme Similarity Tool (EFI-EST) is a service for the generation of SSNs.
            Four options are available to generate SSNs.
            A utility to enhance SSN interpretation is also available.
            </p>
            
            <ul>
                <li><b>Sequence BLAST (Option A): Single sequence query</b>.  The provided sequence is used as 
                    the query for a BLAST search of the UniProt database. The retrieved sequences 
                    are used to generate the SSN.
                    <p class="indentall">Option A allows exploration of local sequence-function space for the query 
                    sequence. By default, 
                    <?php echo functions::get_default_blast_seq(1); ?> sequences are collected.
                    This allows a small "full" SSN to be generated and viewed with Cytoscape.
                    This for local high resolution SSNs.
                    </p>
                </li>
                
                <li><b>Families (Option B): Pfam and/or InterPro families; Pfam clans (superfamilies)</b>.
                    Defined protein families are used to generate the SSN.
                    <p class="indentall">
                    Option B allows exploration of sequence-function space from defined 
                    protein families. A limit of <?php echo functions::get_max_seq(1); ?> 
                    sequences is imposed. Generation of a SSN for more than one family is allowed.
                    Using UniRef90 and UniRef50 databases allows the creation of SSNs for very large
                    Pfam and/or InterPro families, but at lower resolution.
                    </p>
                </li>
                
                <li><b>FASTA (Option C): User-supplied FASTA file.</b>
                    A SSN is generated from a set of defined sequences.
                    
                    <p class="indentall">
                    Option C allows generation of a SSN for a provided set of FASTA 
                    formatted sequences. By default, EST cannot associate the provided sequences
                    with sequences in the UniProt database, and only two node attributes are 
                    provided for the SSNs generated: the number of residues as the "Sequence 
                    Length", and the FASTA header as the "Description". 
                    An option allows the FASTA headers to be read and if Uniprot or NCBI 
                    identifiers are recognized, the corresponding Uniprot information will be 
                    presented as node attributes.
                    </p>
                </li>
                
                <li><b>Accession IDs (Option D): List of UniProt and/or NCBI IDs.</b>
                    The SSN is generated after 
                    fetching the information from the corresponding databases.
                    
                    <p class="indentall">
                    Option D allows for a list of UniProt IDs, NCBI IDs, and/or 
                    NCBI GI numbers (now "retired"). UniProt IDs are used to retrieve sequences and 
                    annotation information from the UniProt database. When recognized, NCBI IDs and 
                    GI numbers are used to retrieve the "equivalent" UniProt IDs and information. 
                    Sequences with NCBI IDs that cannot be recognized will not be included in the 
                    SSN and a "no match" file listing these IDs is available for download.
                    </p>
                </li>
                
                <li><b>Color SSNs: Utility for the identification and coloring of independent clusters within a 
                    SSN.</b>
                    
                    <p class="indentall">
                    Independent clusters in the uploaded SSN are identified, numbered and colored. 
                    Summary tables, sets of IDs and sequences per clusters are 
                    provided. A Cytoscape-edited SNN can serve as input for this utility.
                    </p>
                </li>
            </ul>
            
            <h3>Recommended Reading</h3>
            <p>
                <a href="https://www.sciencedirect.com/science/article/pii/S1367593118300802">'Democratized'
                    genomic enzymology web tools for functional assignment</a>
                    R Zallot, NO Oberg, JA Gerlt - Current opinion in chemical biology, 2018 - Elsevier
            </p>
            <p>
                <a href="https://pubs.acs.org/doi/abs/10.1021/acs.biochem.7b00614">Genomic enzymology:
                    Web tools for leveraging protein family sequence–function space and genome context to discover novel functions</a>
                    JA Gerlt - Biochemistry, 2017 - ACS Publications
            </p>
            <p>
                <a href="https://www.sciencedirect.com/science/article/pii/S1570963915001120">Enzyme function
                    initiative-enzyme similarity tool (EFI-EST): A web tool for generating protein sequence similarity networks</a>
                Gerlt JA, Bouvier JT, Davidson DB, Imker HJ, Sadkhin B, Slater DR, Whalen KL.
                - Biochimica Et Biophysica Acta (BBA)-Proteins and Proteomics, 2015 - Elsevier
            </p>

            <p class="center"><a href="tutorial.php"><button class="light" type="button">Proceed to the tutorial</button></a></p>

        </div>
    </div> <!-- tab-content -->
</div> <!-- tabs -->


<div align="center">
    <p>
    UniProt Version: <b><?php echo global_settings::get_uniprot_version(); ?></b><br>
    InterPro Version: <b><?php echo global_settings::get_interpro_version(); ?></b><br>
    </p>
</div>

<script>
    var AutoCheckedUniRef = false;
    var FamilySizeOk = true;
    var familySizeHelper = new FamilySizeHelper();

    var optAinputIds = getInputIds("opta");
    var optAoutputIds = getOutputIds("opta");
    var optBinputIds = getInputIds("optb");
    var optBoutputIds = getOutputIds("optb");
    var optCinputIds = getInputIds("optc");
    var optCoutputIds = getOutputIds("optc");
    var optDinputIds = getInputIds("optd");
    var optDoutputIds = getOutputIds("optd");
    var optEinputIds = getInputIds("opte");
    var optEoutputIds = getOutputIds("opte");

    $(document).ready(function() {
        $("#main-tabs").tabs();
        $("#optionD-src-tabs").tabs();

        resetForms();
        $("#optionD-src-tabs").data("source", "uniprot");

        $(".tabs .tab-headers a").on("click", function(e) {
            var curAttrValue = $(this).attr("href");
            if (curAttrValue.substr(0, 15) == "#optionD-source") {
                var source = curAttrValue.substr(16);
                $("#optionD-src-tabs").data("source", source);
            }
        });

        familySizeHelper.setupFamilyInput("opta", optAinputIds, optAoutputIds);
        familySizeHelper.setupFamilyInput("optb", optBinputIds, optBoutputIds);
        familySizeHelper.setupFamilyInput("optc", optCinputIds, optCoutputIds);
        familySizeHelper.setupFamilyInput("optd", optDinputIds, optDoutputIds);
        familySizeHelper.setupFamilyInput("opte", optEinputIds, optEoutputIds);

        $(".cb-use-uniref").click(function() {
            if (this.checked) {
//                $(".fraction").val("1").prop("disabled", true);
                $(".fraction").val("1");
            } else {
//                $(".fraction").prop("disabled", false);
            }
        });

        $(".fraction").on("input", function() {
            if (this.value != "1") {
                $(".cb-use-uniref").prop("checked", false);
            }
        });
        
        $(".archive-btn").click(function() {
            var id = $(this).data("id");
            var key = $(this).data("key");
            var aid = $(this).data("analysis-id");
            var requestType = "archive";
            var jobType = "generate";

            if (!aid)
                aid = 0;

            $("#archive-confirm").dialog({
                resizable: false,
                height: "auto",
                width: 400,
                modal: true,
                buttons: {
                    "Archive Job": function() {
                        requestJobUpdate(id, aid, key, requestType, jobType);
                        $( this ).dialog("close");
                    },
                    Cancel: function() {
                        $( this ).dialog("close");
                    }
                }
            });
        });

        $("#fasta-file").on("change", function(e) {
            var fileName = '';
            fileName = e.target.value.split( '\\' ).pop();
            if (fileName && !$("#job-name-optc").val())
                $("#job-name-optc").val(fileName);
        });

        $("#accession-file-uniref").on("change", function(e) {
            var fileName = '';
            fileName = e.target.value.split( '\\' ).pop();
            if (fileName) {
                if (fileName.includes("UniRef50"))
                    $("#accession-seq-type").val("uniref50");
                else if (fileName.includes("UniRef90"))
                    $("#accession-seq-type").val("uniref90");
                else if (fileName.includes("UniProt"))
                    $("#accession-seq-type").val("uniprot");
            }
            if (fileName && !$("#job-name-optd").val())
                $("#job-name-optd").val(fileName);
        });

        $("#accession-file-uniprot").on("change", function(e) {
            var fileName = '';
            fileName = e.target.value.split( '\\' ).pop();
            if (fileName && !$("#job-name-optd").val())
                $("#job-name-optd").val(fileName);
        });

        $("#domain-optd").change(function() {
            var status = $(this).prop("checked");
            var disableFamilyInput = status;
            $("#accession-input-domain-family").prop("disabled", !status);
            $(".accession-input-domain-region").prop("disabled", !status);
            familySizeHelper.setDisabledState("optd", disableFamilyInput);
            if (disableFamilyInput)
                familySizeHelper.resetInput("optd");
            if (status)
                $("#accession-input-domain-region-domain").prop("checked", true);
            else
                $("#accession-input-domain-region-domain").prop("checked", false);
        });

        $(".option-panels > div").accordion({
            heightStyle: "content",
                collapsible: true,
                active: false,
        });
    });

    function resetForms() {
        for (i = 0; i < document.forms.length; i++) {
            document.forms[i].reset();
        }
        document.getElementById("accession-input-domain-family").disabled = true;
        document.getElementById("accession-input-domain-region-nterminal").disabled = true;
        document.getElementById("accession-input-domain-region-domain").disabled = true;
        document.getElementById("accession-input-domain-region-cterminal").disabled = true;
    }
</script>
<script src="<?php echo $SiteUrlPrefix; ?>/js/custom-file-input.js" type="text/javascript"></script>

<div id="family-warning" class="hidden" title="UniRef Family Warning">
<div style="color:red;" id="family-warning-size-info">
The family(ies) selected has <span id="family-warning-total-size"> </span> proteins&mdash;this is greater than
the maximum allowed (<?php echo $max_full_family; ?>). To reduce computing time and the size of
output SSN, UniRef<span class="family-warning-uniref">90</span> cluster ID sequences will automatically be used.
</div>

<p>In UniRef<span class="family-warning-uniref">90</span>, sequences that share
&ge;<span class="family-warning-uniref">90</span>% sequence identity over 80% of the sequence 
length are grouped together and represented by an accession ID known as the cluster ID. The output 
SSN is equivalent a to <span class="family-warning-uniref">90</span>% Representative Node
Network with each node corresponding to a UniRef cluster ID, and for which the node attribute
"UniRef<span class="family-warning-uniref">90</span> Cluster IDs" lists 
all the sequences represented by a node. UniRef<span class="family-warning-uniref">90</span>
SSNs are compatible with the Color SSN utility as well as the EFI-GNT tool.
</p>

<p>Press Ok to continue with UniRef<span class="family-warning-uniref">90</span>.</p>

<?php /* ?>
    The family(ies) selected has <span id="family-warning-total-size"> </span> 
    proteins<span id="family-warning-fraction-size"></span>, which is greater
    than the maximum allowed (<?php echo $max_full_family; ?>) for full family
    inclusion.  UniRef<span class="family-warning-uniref">90</span> cluster IDs will automatically be 
    used instead of the full number of proteins in the family(ies).  Press OK to continue with UniRef<span class="family-warning-uniref">90</span>
    or Cancel to enter a different family, or option.
<?php */ ?>
</div>

<div id="archive-confirm" title="Archive the job?" style="display: none">
<p>
<span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>
This job will be permanently removed from your list of jobs.
</p>    
</div>

<?php require_once('inc/footer.inc.php'); ?>

<?php

function make_db_mod_option($db_modules, $option) {
    if (count($db_modules) < 2)
        return "";

    $id = "db-mod-$option";
    echo <<<HTML
<div>
    <span class="input-name">
        Database version:
    </span><span class="input-field">
        <select name="$id" id="$id">
HTML;

    foreach ($db_modules as $mod) {
        $mod_name = $mod[1];
        echo "            <option value=\"$mod_name\">$mod_name</option>\n";
    }

    echo <<<HTML
        </select>
    </span>
</div>
HTML;
}


function add_submit_html($option_id, $js_id_name, $user_email) {
    $js_fn = "submitOptionForm('$option_id', familySizeHelper, $js_id_name)";
    if ($option_id == "colorssn")
        $js_fn = "submitColorSsnForm()";

    if ($option_id != "colorssn")
        echo <<<HTML
<div style="margin-top: 35px">
    <span class="input-name">
        Job name:
    </span><span class="input-field">
        <input type="text" class="email" name="job-name-$option_id" id="job-name-$option_id" value=""> (required)
    </span>
</div>
HTML;
    echo <<<HTML
<div>
    <span class="input-name">
        E-mail address:
    </span><span class="input-field">
        <input name="email" id="email-$option_id" type="text" value="$user_email" class="email"
            onfocus='if(!this._haschanged){this.value=""};this._haschanged=true;' value="">
    </span>
    <p>
    You will be notified by e-mail when your submission has been processed.
    </p>
</div>

<div id="message-$option_id" style="color: red" class="error_message">
</div>
<center>
    <div><button type="button" class="dark" onclick="$js_fn">Submit Analysis</button></div>
</center>
HTML;
}


function get_advanced_seq_html($option_id) {
    $addl_html = <<<HTML
<div>
    <span class="input-name">
        Sequence identity: 
    </span><span class="input-field">
        <input type="text" class="small" id="seqid-$option_id" name="seqid-$option_id" value="1">
        Sequence identity (&le; 1; default: 1)
    </span>
</div>
<div>
    <span class="input-name">
        Sequence length overlap:
    </span><span class="input-field">
        <input type="text" class="small" id="length-overlap-$option_id" name="length-overlap-$option_id" value="1">
        Sequence length overlap (&le; 1; default: 1)
    </span>
</div>
HTML;
    if ($option_id == "opte") {
        $addl_html .= <<<HTML
<div>
    Minimum Sequence Length: <input type="text" class="small" id="min-seq-len-$option_id" name="min-seq-len-$option_id" value="">
</div>
<div>
    Maximum Sequence Length: <input type="text" class="small" id="max-seq-len-$option_id" name="max-seq-len-$option_id" value="">
</div>
<div>
    Do not demultiplex:
    <input type="checkbox" id="demux-$option_id" name="demux-$option_id" value="1">
    Check to prevent a demultiplex to expand cd-hit clusters (default: demultiplex)
</div>
HTML;
    }
    return $addl_html;
}


function add_dev_site_option($option_id, $db_modules, $extra_html = "") {
    echo <<<HTML
<h3>Dev Site Options</h3>
<div>
    <div>
        <span class="input-name">
            CPUx2:
        </span><span class="input-field">
            <input type="checkbox" id="cpu-x2-$option_id" name="cpu-x2-$option_id" value="1">
            <label for="cpu-x2-$option_id">Check to use two times the number of processors (default: off)</label>
        </span>
    </div>
HTML;
    make_db_mod_option($db_modules, $option_id);
    if (functions::get_program_selection_enabled()) {
        echo <<<HTML
    <div>
        <span class="input-name">
            Select Program to use:
        </span><span class="input-field">
        <select name="program-$option_id" id="program-$option_id">
            <option value="BLAST">BLAST</option>
            <option value="BLAST+">BLAST+</option>
            <option selected="selected" value="DIAMOND">Diamond</option>
        	<option value="DIAMONDSENSITIVE">Diamond Sensitive</option>
        </select>
        </span>
    </div>
HTML;
    }
    echo <<<HTML
    $extra_html
</div>
HTML;
}


function add_ssn_calc_option($option_id) {
    $default_evalue = functions::get_evalue();
    echo <<<HTML
<h3>SSN Edge Calculation Option</h3>
<div>
    <span class="input-name">
        E-Value:
    </span><span class="input-field">
        <input type="text" class="small" id="evalue-$option_id" name="evalue-$option_id" size="5" value="$default_evalue">
        Negative log of e-value for all-by-all BLAST (&ge;1; default $default_evalue)
    </span>
    <div class="input-desc">
        Input an alternative e-value for BLAST to calculate similarities between sequences defining edge values.
        Default parameters are permissive and are used to obtain edges even between sequences that share low similarities.
        We suggest using a larger e-value (smaller negative log) for short sequences.
    </div>
</div>
HTML;
}


function add_blast_calc_desc() {
    echo <<<HTML
            An all-by-all BLAST is performed to obtain the similarities between sequence pairs to
            calculate edge values to generate the SSN.
HTML;
}


function add_domain_option($option_id, $specify_family = false, $use_advanced_options = false) {
    $option_text = $specify_family ? "Options" : "Option";
    echo <<<HTML
<h3>Family Domain Boundary $option_text</h3>
<div>
    <div>
        Pfam and InterPro databases define domain boundaries for members of their families.
    </div>
    <div>
        <span class="input-name">
            Domain:
        </span><span class="input-field">
            <input type="checkbox" id="domain-$option_id" name="domain-$option_id" value="1" class="bigger">
            <label for="domain-$option_id">Sequences trimmed to the domain boundaries defined by the input family will be used for the calculations.</label>
        </span>
    </div>
HTML;
    if ($specify_family) { // Option D only
        echo <<<HTML
    <div>
        <span class="input-name">
            Family:
        </span><span class="input-field">
            <input type="text" name="accession-input-domain-family" id="accession-input-domain-family" style="width: 100px" disabled />
            Use domain boundaries from the specified family (enter only one family).
        </span>
HTML;
        if ($use_advanced_options) {
            echo <<<HTML
        <div>
            <input type="radio" id="accession-input-domain-region-nterminal" name="accession-input-domain-region" value="nterminal" class="accession-input-domain-region">
            <label for="accession-input-domain-region-nterminal">N-Terminal</label>
            <input type="radio" id="accession-input-domain-region-domain" name="accession-input-domain-region" value="domain" class="accession-input-domain-region">
            <label for="accession-input-domain-region-domain">Domain</label>
            <input type="radio" id="accession-input-domain-region-cterminal" name="accession-input-domain-region" value="cterminal" class="accession-input-domain-region">
            <label for="accession-input-domain-region-cterminal">C-Terminal</label>
        </div>
HTML;
        }
        echo <<<HTML
    </div>
HTML;
    }
    echo <<<HTML
</div>
HTML;
}


function add_family_input_option_family_only($option_id) {
    return add_family_input_option_base($option_id, false, "");
}


function add_family_input_option($option_id) {
    return add_family_input_option_base($option_id, true, get_fraction_html($option_id));
}


function add_family_input_option_base($option_id, $include_intro, $fraction_html) {
    $max_full_family = number_format(functions::get_maximum_full_family_count(), 0);

    if ($include_intro) {
        $option_text = $fraction_html ? "Options" : "Option";
        echo <<<HTML
<h3>Protein Family Addition $option_text</h3>
<div>
    <div>
        Add sequences belonging to Pfam and/or InterPro families to the sequences used to generate the SSN.
    </div>
    <div class="secondary-input">
        <div class="secondary-name">
            Familes:
        </div>
        <div class="secondary-field">
HTML;
    // Don't include intro
    } else {
        echo <<<HTML
<div>
    <div class="primary-input">
        <div class="secondary-name">
            Pfam and/or InterPro Families:
        </div>
        <div>
HTML;
    }

    echo <<<HTML
            <input type="text" id="families-input-$option_id" name="families-input-$option_id">
        </div>
HTML;

    if ($include_intro) {
        echo <<<HTML
    </div>
    <div class="input-desc">
HTML;
    }

    echo <<<HTML
        <div>
            <input type="checkbox" id="use-uniref-$option_id" class="cb-use-uniref bigger" value="1">
            <label for="use-uniref-$option_id">Use <select id="uniref-ver-$option_id" name="uniref-ver-$option_id" class="bigger"><option value="90">UniRef90</option><option value="50">UniRef50</option></select> cluster ID sequences instead of the full family</label>
            <div style="margin-top: 10px">
HTML;
    echo est_ui::make_pfam_size_box("family-size-container-$option_id", "family-count-table-$option_id");
    echo <<<HTML
            </div>
        </div>
        <div>
            The input format is a single family or comma/space separated list of families.
            Families should be specified as PFxxxxx (five digits),
            IPRxxxxxx (six digits) or CLxxxx (four digits) for Pfam clans.
        </div>
    </div>
    <div>
        The EST provides access to the UniRef90 and UniRef50 databases to allow the creation
        of SSNs for very large Pfam and/or InterPro families. For families that contain 
        more than $max_full_family sequences, the SSN <b>will be</b> generated 
        using the UniRef50 or UniRef90 databases. In UniRef90, sequences that share &ge;90% sequence identity 
        over 80% of the sequence length are grouped together and represented by a 
        sequence known as the cluster ID. UniRef50 is similar except that the
        sequence identity is &ge;50%. If one of the UniRef databases is used,
        the output SSN is equivalent to a 90% (for UniRef90) or 50% (for UniRef50)
        Representative Node Network with each node corresponding to a UniRef cluster ID; in this
        case an additional node attribute is provided which lists all
        of the sequences represented by the UniRef node.
    </div>
$fraction_html
</div>
HTML;
}


function get_fraction_html($option_id) {
    $default_fraction = functions::get_fraction(); 
    return <<<HTML
    <div>
        <span class="input-name">
            Fraction:
        </span><span class="input-field">
            <input type="text" class="small fraction" id="fraction-$option_id" name="fraction-$option_id" value="$default_fraction" size="5">
            <a class="question" title="Either fraction or UniRef can be used, not both.">?</a>
            Reduce the number of sequences used to a fraction of the full family size (&ge; 1; default:
            $default_fraction)
        </span>
        <div class="input-desc">
            Selects every Nth sequence in the family; the sequences are assumed to be
            added randomly to UniProt, so the selected sequences are assumed to be a
            representative sampling of the family. This allows reduction of the size of the SSN.
        </div>
    </div>
HTML;
}


function output_job_list($jobs, $show_archive = false) {
    echo <<<HTML
            <table class="pretty-nested" style="table-layout:fixed">
                <thead>
                    <th class="id-col">ID</th>
                    <th>Job Name</th>
                    <th class="date-col">Date Completed</th>
                </thead>
                <tbody>
HTML;

    $order = $jobs["order"];
    $cjobs = $jobs["color_jobs"];
    $gjobs = $jobs["generate_jobs"];

    $get_bg_color = new bg_color_toggle();

    for ($i = 0; $i < count($order); $i++) {
        $id = $order[$i];

        if (isset($gjobs[$id])) {
            output_generate_job($id, $gjobs[$id], $get_bg_color, $show_archive);
        } elseif (isset($cjobs[$id])) {
            output_top_color_job($id, $cjobs[$id], $get_bg_color, $show_archive);
        }
    }
    echo <<<HTML
                </tbody>
            </table>
HTML;
}


function has_jobs($jobs) {
    return (isset($jobs["order"]) && count($jobs["order"]) > 0);
}


function output_top_color_job($id, $job, $get_bg_color, $show_archive) {
    $bg_color = $get_bg_color->get_color();
    $link_class = "hl-color";
    $html = output_colorssn_row($id, $job, $bg_color, $show_archive);
    echo $html;
}


function output_generate_job($id, $job, $get_bg_color, $show_archive) {
    $bg_color = $get_bg_color->get_color();
    $link_class = "hl-est";
    $html = output_generate_row($id, $job, $bg_color, $show_archive);
    echo $html;

    foreach ($job["analysis_jobs"] as $ajob) {
        $html = output_analysis_row($id, $job["key"], $ajob, $bg_color);
        echo $html;
        if (isset($ajob["color_jobs"])) {
            foreach ($ajob["color_jobs"] as $cjob) {
                $html = output_nested_colorssn_row($cjob["id"], $cjob, $bg_color);
                echo $html;
            }
        }
    }
}


function get_script($row_type) {
    switch ($row_type) {
    case RT_GENERATE:
        return "stepc.php";
    case RT_ANALYSIS:
        return "stepe.php";
    case RT_COLOR:
    case RT_NESTED_COLOR:
        return "view_coloredssn.php";
    default:
        return "";
    }
}

function get_link_class($row_type) {
    switch ($row_type) {
    case RT_COLOR:
    case RT_NESTED_COLOR:
        return "hl-color";
    default:
        return "hl-est";
    }
}

function output_generate_row($id, $job, $bg_color, $show_archive) {
    return output_row(RT_GENERATE, $id, NULL, $job["key"], $job, $bg_color, $show_archive);
}

function output_colorssn_row($id, $job, $bg_color, $show_archive) {
    return output_row(RT_COLOR, $id, NULL, $job["key"], $job, $bg_color, $show_archive);
}

function output_nested_colorssn_row($id, $job, $bg_color) {
    return output_row(RT_NESTED_COLOR, $id, NULL, $job["key"], $job, $bg_color, true);
}

function output_analysis_row($id, $key, $job, $bg_color) {
    return output_row(RT_ANALYSIS, $id, $job["analysis_id"], $key, $job, $bg_color, true);
}

// $aid = NULL to not output an analysis (nested) job
function output_row($row_type, $id, $aid, $key, $job, $bg_color, $show_archive) {
    $script = get_script($row_type);
    $link_class = get_link_class($row_type);

    $name = $job["job_name"];
    $date_completed = $job["date_completed"];
    $is_completed = $job["is_completed"];

    $link_start = "";
    $link_end = "";
    $name_style = "";
    $data_aid = "";
    $archive_icon = "fa-stop-circle cancel-btn";
    if ($is_completed) {
        $aid_param = $row_type == RT_ANALYSIS ? "&analysis_id=$aid" : "";
        $link_start = "<a href='$script?id=$id&key=${key}${aid_param}' class='$link_class'>";
        $link_end = "</a>";
        $archive_icon = "fa-trash-alt";
    } elseif ($date_completed == __FAILED__) {
        $archive_icon = "fa-trash-alt";
    }
    $id_text = "$link_start${id}$link_end";

    if ($row_type == RT_ANALYSIS) {
        $name_style = "style=\"padding-left: 35px;\"";
        $id_text = "";
        $data_aid = "data-analysis-id='$aid'";
    } elseif ($row_type == RT_NESTED_COLOR) {
        $name_style = "style=\"padding-left: 70px;\"";
        $id_text = "";
    }
    $name = "<span title='$id'>$name</span>";

    $status_update_html = "";
    if ($show_archive)
        $status_update_html = "<div style='float:right' class='archive-btn' data-type='gnn' data-id='$id' data-key='$key' $data_aid title='Archive Job'><i class='fas $archive_icon'></i></div>";

    return <<<HTML
                    <tr style="background-color: $bg_color">
                        <td>$id_text</td>
                        <td $name_style>$link_start${name}$link_end</td>
                        <td>$date_completed $status_update_html</td>
                    </tr>
HTML;
}


class bg_color_toggle {

    private $last_bg_color = "#eee";

    // Return the color and then toggle it.
    public function get_color() {
        if ($this->last_bg_color == "#fff")
            $this->last_bg_color = "#eee";
        else
            $this->last_bg_color = "#fff";
        return $this->last_bg_color;
    }
}

?>

