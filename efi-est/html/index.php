<?php

const RT_GENERATE = 1;
const RT_COLOR = 2;
const RT_ANALYSIS = 3;
const RT_NESTED_COLOR = 4;

require_once("../includes/main.inc.php");
require_once("../libs/user_jobs.class.inc.php");
require_once("../../includes/login_check.inc.php");
require_once("../libs/est_ui.class.inc.php");
require_once("../../libs/ui.class.inc.php");

$userEmail = "Enter your e-mail address";

$showJobsTab = false;
$showTrainingJobsTab = false;
$jobs = array();
$tjobs = array(); // training jobs
$userGroups = array();
$IsAdminUser = false;
if (global_settings::is_recent_jobs_enabled() && user_auth::has_token_cookie()) {
    $userJobs = new user_jobs();
    $userJobs->load_jobs($db, user_auth::get_user_token());
    $jobs = $userJobs->get_jobs();
    $tjobs = $userJobs->get_training_jobs();
    $userEmail = $userJobs->get_email();
    $userGroups = $userJobs->get_groups();
    $IsAdminUser = $userJobs->is_admin();
    $showJobsTab = count($jobs) > 0 || count($tjobs) > 0;
    $showTrainingJobsTab = count($tjobs) > 0;
}
$showTrainingJobsTab = false; // currently we don't want it to be displayed since we put the training jobs below the previous jobs.

$showJobGroups = $IsAdminUser && global_settings::get_job_groups_enabled();

$maxSeqNum = functions::get_max_seq();
$maxSeqFormatted = number_format($maxSeqNum, 0);

$useAdvancedFamilyInputs = functions::advanced_options_enabled();
$maxFullFamily = number_format(functions::get_maximum_full_family_count(), 0);

$db_modules = global_settings::get_database_modules();

$updateMessage =
//    '<div class="new_feature"></div>' .
    "The <a href=\"../efi-cgfp/\">Computationally-Guided Functional Profiling tool (EFI-CGFP)</a> is now available.<br><br>" .
    functions::get_update_message() .
    "<br>SSNs can now be generated using UniRef90 and UniRef50 databases.";


$IncludeSubmitJs = true;
require_once "inc/header.inc.php";

?>


<p></p>
<p>
A sequence similarity network (SSN) allows researchers to visualize relationships among 
protein sequences. In SSNs, the most related proteins are grouped together in 
clusters.  The Enzyme Similarity Tool (EFI-EST) is a web-tool that allows researchers to
easily generate SSNs that can be visualized in 
<a href="http://www.cytoscape.org/">Cytoscape</a> (<a href="tutorial_references.php">3</a>).
</p>

<div id="update-message" class="update_message initial-hidden">
<?php if (isset($updateMessage)) echo $updateMessage; ?>
</div>

<div class="new_feature"></div>
<p>
The EST provides access to the UniRef90 and UniRef50 databases to allow the creation
of SSNs for very large Pfam and/or InterPro families. For families that contain 
more than <?php echo $maxFullFamily; ?> sequences, the SSN <b>will be</b> generated 
using the UniRef90 database. In UniRef90, sequences that share &ge;90% sequence identity 
over 80% of the sequence length are grouped together and represented by a 
sequence known as the cluster ID. UniRef50 is similar except that the
sequence identity is &ge;50%. If one of the UniRef databases is used,
the output SSN is equivalent to a 90% (for UniRef90) or 50% (for UniRef50)
Representative Node Network with each node corresponding to a UniRef cluster ID; in this
case an additional node attribute is provided which lists all
of the sequences represented by the UniRef node.
</p>
<?php /*
This is done to reduce computing time 
and the size of output SSNs without loosing information: the output SSN is equivalent 
to a 90% Representative Node Network with each node corresponding to a seed sequence,
and for which the node attribute "UniRef90 Cluster IDs" lists all the sequences 
represented by a node so that any sequence in input family can be located by searching 
this node attribute. UniRef90 SSNs are compatible with the Color SSN utility as well 
as the EFI-GNT tool: the UniRef90 groups are automatically expanded when needed.
</p>

<p>
When a family greater than <?php echo $maxFullFamily; ?> in size is selected in
Options B, C, and D, <b>SSNs are now be generated using the 
UniRef90 database</b> in which UniProt sequences that share &ge;90% sequence identity over 80% 
of the sequence length are clustered and represented by a single seed sequence. For most 
families, use of Uniref90 seed sequences decreases the time for the BLAST step by a
factor of &ge;4. The UniRef90 SSNs are analogous to 90% representative node SSNs generated
using all UniProt sequences. The UniRef90 SSNs contain a node attribute "UniRef90 Cluster
IDs" that lists the UniProt IDs is each node and is searchable with Cytoscape, so all 
UniProt IDs in the family can be located. The UniRef90 SSNs are compatible with the 
EFI-GNT tool.
</p>
<?php */ ?>

<p>
A listing of new features and other information pertaining to EST is available on the
<a href="notes.php">release notes</a> page.
</p>

<p>
Information on Pfam families and clans and InterPro family sizes is now available on
the <a href="family_list.php">Family Information page</a>.
</p>

<div class="tabs-efihdr tabs">
    <ul class="tab-headers">
<?php if ($showJobsTab) { ?>
        <li class="active"><a href="#jobs">Previous Jobs</a></li>
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
        <li <?php echo ($showJobsTab ? "" : 'class="active"') ?>><a href="#tutorial">Tutorial</a></li>
    </ul>

    <div class="tab-content">
<?php if ($showJobsTab) { ?>
        <div id="jobs" class="tab active">

<?php /*
            <h3>Precomputed Option B Jobs</h3>
            Precomputed jobs for selected families are available 
            <a href="precompute.php">here</a>.
            <!--<a href="precompute.php"><button type="button" class="mini">Precomputed Option B Jobs</button></a>-->
*/?>
            <h3>User Jobs</h3>
<?php 
    $show_archive = true;
    output_job_list($jobs, $show_archive);

    if (count($tjobs)) {
        echo "            <h3>Training Jobs</h3>\n";
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
        <div id="optionAtab" class="tab">

            <p>
            The input sequence is used as the query for a search of the UniProt database 
            using BLAST. The similarities among the input and retrieved sequences are 
            calculated and used to generate the SSN.  The default maximum number of 
            retrieved sequences is
            <?php echo number_format(functions::get_default_blast_seq(),0); ?>.
            </p>
    
            <form name="optionAform" id="optionAform" method="post" action="" enctype="multipart/form-data">
                <div class="primary-input">
                    <textarea id="blast-input" name="blast-input"></textarea>
                    Input a single <b>protein sequence</b> only.
                </div>

                <div class="option-panels">
                    <div>
                        <h3>BLAST Retrieval Options</h3>
                        <div>
                            <div>
                                UniProt BLAST query e-value:
                                <input type="text" class="small" id="blast-evalue" name="blast-evalue" size="5"
                                    value="<?php echo functions::get_evalue(); ?>">
                                Negative log of e-value for retrieving similar sequences (&ge; 1; default: <?php echo functions::get_evalue(); ?>)
                            </div>
                            <div class="primary-input">
                                Input an alternative e-value for BLAST to retrieve sequences from the
                                UniProt database. We suggest using a larger e-value
                                (smaller negative log) for retreiving homologues if the query
                                sequence is short and a smaller e-value (larger negative log) if there
                                is no need to retrieve divergent homologues.
                            </div>
                            <div>
                                Maximum number of sequences retrieved:
                                <input type="text" id="blast-max-seqs" class="small" name="blast-max-seqs" value="<?php  echo functions::get_default_blast_seq(); ?>" size="5">
                                (&le; <?php echo number_format(functions::get_max_blast_seq()); ?>,
                                default: <?php echo number_format(functions::get_default_blast_seq()); ?>)
                            </div>
                        </div>
                    </div>
    
                    <div>
                        <?php add_family_input_option("opta", $maxFullFamily); ?>
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
        <div id="optionBtab" class="tab">
            <p>
            The sequences from the Pfam families, InterPro families, and/or Pfam clans (superfamilies) are retrieved,
            and then, the similarities between the sequences are calculated and used to generate the SSN.
            Lists of Pfam families, InterPro families, and Pfam clans are included in the <a href="notes.php">release notes</a>.
            <a href="https://www.ebi.ac.uk/interpro/search/sequence-search">InterProScan sequence search</a> can be used to
            find matches within the InterPro database for a given sequence.
            </p>

            <form name="optionBform" id="optionBform" method="post" action="">
                <?php add_family_input_option_family_only("optb", $maxFullFamily); ?>

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
        <div id="optionCtab" class="tab">
            The similarities between the provided sequences will be calculated and used to generate the SSN.
            Input a list of protein sequences in FASTA format with headers, or upload a FASTA file.
            
            <form name="optionCform" id="optionCform" method="post" action="">
                <div class="primary-input">
                    <textarea id="fasta-input" name="fasta-input"></textarea>
                    <div>
                        <input type="checkbox" id="fasta-use-headers" name="fasta-use-headers" value="1"> <label for="fasta-use-headers"><b>Read FASTA headers</b></label><br>
                        When selected, recognized UniProt or Genbank identifiers from FASTA headers are used to retrieve
                        corresponding node attributes from the UniProt database.
                    </div>
                    <?php echo ui::make_upload_box("FASTA File:", "fasta-file", "progress-bar-fasta", "progress-num-fasta"); ?>
                </div>

                <div class="option-panels">
                    <div>
                        <?php add_family_input_option("optc", $maxFullFamily); ?>
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
        <div id="optionDtab" class="tab">
            Generate SSN from a list of UniProt, UniRef, NCBI IDs, or Genbank sequence IDs.
            <form name="optionDform" id="optionDform" method="post" action="">
                <div class="tabs tabs-efihdr" id="optionD-src-tabs">
                    <ul class="tab-headers">
                        <li class="active"><a href="#optionD-source-uniprot">Use UniProt IDs</a></li>
                        <li><a href="#optionD-source-uniref">Use UniRef 50 or 90 Cluster IDs</a></li>
                    </ul>
                    <div class="tab-content" style="min-height: 250px">
                        <div id="optionD-source-uniprot" class="tab active">
                            Input a list of UniProt, NCBI, or Genbank sequence accession IDs, and/or upload a text
                            file containing the accession IDs.
                            <div class="primary-input">
                                <textarea id="accession-input-uniprot" name="accession-input-uniprot"></textarea>
                                <div>
<?php echo ui::make_upload_box("Accession ID File:", "accession-file-uniprot", "progress-bar-accession-uniprot", "progress-num-accession-uniprot"); ?>
                                </div>
                            </div>
                        </div>
                        <div id="optionD-source-uniref" class="tab">
                            Input a list of UniRef50 or UniRef90 cluster accession IDs, and/or upload a text
                            file containing the accession IDs.
                            <div class="primary-input">
                                <textarea id="accession-input-uniref" name="accession-input-uniref"></textarea>
                                <div>
<?php echo ui::make_upload_box("Accession ID File:", "accession-file-uniref", "progress-bar-accession-uniref", "progress-num-accession-uniref"); ?>
                                </div>
                                <div id="accession-seq-type-container" style="margin-top:15px">
                                    Input accession IDs are:
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
                        <?php add_domain_option("optd", true); ?>
                    </div>

                    <div>
                        <?php add_family_input_option("optd", $maxFullFamily); ?>
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
        <div id="optionEtab" class="tab">
            <form name="optionEform" id="optionEform" method="post" action="">
                <?php add_family_input_option_family_only("opte", $maxFullFamily); ?>

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
        <div id="colorssntab" class="tab">
            <div>
                Clusters in the submitted SSN are identified, numbered and colored.
                Summary tables, sets of IDs and sequences per cluster are provided.
            </div>

            <form name="colorSsnForm" id="colorSsnform" method="post" action="">
                <div class="primary-input">
<?php echo ui::make_upload_box("SSN File:", "colorssn-file", "progress-bar-colorssn", "progress-num-colorssn"); ?>
                    <div>
                        A Cytoscape-edited SNN can serve as input for this utility. File can be uncompressed or zipped XGMML.
                    </div>
                </div>

                <div>
                    E-mail address:
                    <input name="email" id="colorssn-email" type="text" value="<?php echo $userEmail; ?>" class="email"
                        onfocus='if(!this._haschanged){this.value=""};this._haschanged=true;'><br>
                    When the sequence has been uploaded and processed, you will receive an e-mail containing a link
                    to analyze the data.
                </div>
    
                <div id="colorssn-message" style="color: red" class="error_message">
                    <?php if (isset($message)) { echo "<h4 class='center'>" . $message . "</h4>"; } ?>
                </div>
                <center>
                    <div><button type="button" class="dark" onclick="submitColorSsnForm()">Submit Analysis</button></div>
                </center>
            </form>
        </div>
<?php    } ?>

        <div id="tutorial" class="tab <?php echo (!$showJobsTab ? "active" : "") ?>">

            <h3>Overview of possible inputs for EFI-EST</h3>
            
            <p>
            The EFI - ENZYME SIMILARITY TOOL (EFI-EST) is a webserver for the generation of 
            SSNs. Four options for user-initiated generation of a SSN are available. In 
            addition, a utility to enhance SSNs interpretation is available.
            </p>
            
            <ul>
                <li><b>Option A: Single sequence query</b>.  The provided sequence is used as 
                    the query for a BLAST search of the UniProt database. The retrieved sequences 
                    are used to generate the SSN.
                    <p class="indentall">Option A allows the user to explore local sequence-function space for the query 
                    sequence. Homologs are collected and used to generate the SSN. By default, 
                    <?php echo functions::get_default_blast_seq(1); ?> sequences are collected
                    as this number often allows a “full” SSN to be generated and viewed with Cytoscape.</p>
                </li>
                
                <li><b>Option B: Pfam and/or InterPro families; Pfam clans (superfamilies)</b>.
                    Defined protein families are used to generate the SSN.
                    <p class="indentall">
                    Option B allows the user to explore sequence-function space from defined 
                    protein families. A limit of <?php echo functions::get_max_seq(1); ?> 
                    sequences is imposed. Generation of a SSN for more than one family is allowed.
                    </p>
                </li>
                
                <li><b>Option C: User-supplied FASTA file.</b>
                    A SSN is generated from a set of defined sequences.
                    
                    <p class="indentall">
                    Option C allows the user to generate a SSN for a provided set of FASTA 
                    formatted sequences. By default, the provided sequences cannot be associated 
                    with sequences in the UniProt database, and only two node attributes are 
                    provided for the SSNs generated: the number of residues as the “Sequence 
                    Length”, and the FASTA header as the “Description”. 
                    </p>
                    
                    <p class="indentall">An option allows the FASTA headers to be read and if Uniprot or NCBI 
                    identifiers are recognized, the corresponding Uniprot information will be 
                    presented as node attributes.
                    </p>
                </li>
                
                <li><b>Option D: List of UniProt and/or NCBI IDs.</b>
                    The SSN is generated after 
                    fetching the information from the corresponding databases.
                    
                    <p class="indentall">
                    Option D allows the user to provide a list of UniProt IDs, NCBI IDs, and/or 
                    NCBI GI numbers (now “retired”). UniProt IDs are used to retrieve sequences and 
                    annotation information from the UniProt database. When recognized, NCBI IDs and 
                    GI numbers are used to retrieve the “equivalent” UniProt IDs and information. 
                    Sequences with NCBI IDs that cannot be recognized will not be included in the 
                    SSN and a “nomatch” file listing these IDs is available for download.
                    </p>
                </li>
                
                <li><b>Utility for the identification and coloring of independent clusters within a 
                    SSN.</b>
                    
                    <p class="indentall">
                    Independent clusters in the uploaded SSN are identified, numbered and colored. 
                    Summary tables, sets of IDs and sequences for specific clusters and are 
                    provided. A manually edited SNN can serve as input for this utility.
                    </p>
                </li>
            </ul>
            
            <p><a href='http://dx.doi.org/10.1016/j.bbapap.2015.04.015'>Please see our recent review in BBA Proteins for examples of EFI-EST use.</a></p>

            <p class="center"><a href="tutorial.php"><button class="light" type="button">Proceed to the tutorial</button></a></p>

        </div>
    </div> <!-- tab-content -->
</div> <!-- tabs -->


<div align="center">
    <?php if (functions::is_beta_release()) { ?>
    <h4><b><span style="color: red">BETA</span></b></h4>
    <?php } ?>

    <p>
    UniProt Version: <b><?php echo functions::get_uniprot_version(); ?></b><br>
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
        resetForms();
        $("#optionD-src-tabs").data("source", "uniprot");

        $(".tabs .tab-headers a").on("click", function(e) {
            var curAttrValue = $(this).attr("href");
            if (curAttrValue.substr(0, 15) == "#optionD-source") {
                var source = curAttrValue.substr(16);
                $("#optionD-src-tabs").data("source", source);
            }
            var filterType = curAttrValue.substr(11);
            $("#filter-type").val(filterType);
            $(".tabs " + curAttrValue).fadeIn(300).show().siblings().hide();
            $(this).parent("li").addClass("active").siblings().removeClass("active");
            e.preventDefault();
        });

        $(".advanced-toggle").click(function () {
            $header = $(this);
            //getting the next element
            $content = $header.next();
            //open up the content needed - toggle the slide- if visible, slide up, if not slidedown.
            $content.slideToggle(100, function () {
                if ($content.is(":visible")) {
                    $header.find("i.fas").addClass("fa-minus-square");
                    $header.find("i.fas").removeClass("fa-plus-square");
                } else {
                    $header.find("i.fas").removeClass("fa-minus-square");
                    $header.find("i.fas").addClass("fa-plus-square");
                }
            });
        
        });

//        $("#create-accordion" ).accordion({
//            icons: { "header": "ui-icon-plus", "activeHeader": "ui-icon-minus" },
//            heightStyle: "content"
//        });

        familySizeHelper.setupFamilyInput("opta", optAinputIds, optAoutputIds);
        familySizeHelper.setupFamilyInput("optb", optBinputIds, optBoutputIds);
        familySizeHelper.setupFamilyInput("optc", optCinputIds, optCoutputIds);
        familySizeHelper.setupFamilyInput("optd", optDinputIds, optDoutputIds);
        familySizeHelper.setupFamilyInput("opte", optEinputIds, optEoutputIds);

        $(".cb-use-uniref").click(function() {
            if (this.checked) {
                $(".fraction").val("1");
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
            var requestType = "archive";
            var jobType = "generate";

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
            var status = $("#accession-input-domain-family").prop("disabled");
            if (status)
                $("#accession-input-domain-family").prop("disabled", false);
            else
                $("#accession-input-domain-family").prop("disabled", true);
        });

        $(".option-panels > div").accordion({
            heightStyle: "content",
                collapsible: true,
                active: false,
        });
        $(".tabs").tabs();
    }).tooltip();

    function resetForms() {
        for (i = 0; i < document.forms.length; i++) {
            document.forms[i].reset();
        }
        document.getElementById("accession-input-domain-family").disabled = true;
    }
</script>
<script src="<?php echo $SiteUrlPrefix; ?>/js/custom-file-input.js" type="text/javascript"></script>

<div id="family-warning" class="hidden" title="UniRef Family Warning">
<div style="color:red;" id="family-warning-size-info">
The family(ies) selected has <span id="family-warning-total-size"> </span> proteins&mdash;this is greater than
the maximum allowed (<?php echo $maxFullFamily; ?>). To reduce computing time and the size of
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
    than the maximum allowed (<?php echo $maxFullFamily; ?>) for full family
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
    $ws = "                    ";
    echo <<<HTML
<div>
$ws Database version:
$ws<select name="$id" id="$id">
HTML;

    foreach ($db_modules as $mod) {
        $mod_name = $mod[1];
        echo "$ws    <option value=\"$mod_name\">$mod_name</option>\n";
    }

    echo <<<HTML
$ws</select>
</div>
HTML;
}


function add_submit_html($option_id, $js_id_name, $user_email) {
    echo <<<HTML
<div style="margin-top: 35px">
    <div>Job name: <input type="text" class="email" name="job-name-$option_id" id="job-name-$option_id" value=""> (required)</div>
</div>
<div>
    E-mail address:
    <input name="email" id="email-$option_id" type="text" value="$user_email" class="email"
        onfocus='if(!this._haschanged){this.value=""};this._haschanged=true;' value=""><br>
    When the sequence has been uploaded and processed, you will receive an e-mail containing a link
    to analyze the data.
</div>

<div id="message-$option_id" style="color: red" class="error_message">
</div>
<center>
    <div><button type="button" class="dark" onclick="submitOptionForm('$option_id', familySizeHelper, $js_id_name)">Submit Analysis</button></div>
</center>
HTML;
}


function get_advanced_seq_html($option_id) {
    $addl_html = <<<HTML
<div>
    Sequence Identity: <input type="text" class="small" id="seqid-$option_id" name="seqid-$option_id" value="1">
    Sequence identity (&le; 1; default: 1)
</div>
<div>
    Sequence Length Overlap:
    <input type="text" class="small" id="length-overlap-$option_id" name="length-overlap-$option_id" value="1">
    Sequence length overlap (&le; 1; default: 1)
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
        CPUx2: <input type="checkbox" id="cpu-x2-$option_id" name="cpu-x2-$option_id" value="1">
        <label for="cpu-x2-$option_id">Check to use two times the number of processors (default: off)</label>
    </div>
HTML;
    make_db_mod_option($db_modules, $option_id);
    if (functions::get_program_selection_enabled()) {
        echo <<<HTML
    <div>
        Select Program to use:
        <select name="program-$option_id" id="program-$option_id">
            <option value="BLAST">Blast</option>
            <option value="BLAST+">Blast+</option>
            <option selected="selected" value="DIAMOND">Diamond</option>
        	<option value="DIAMONDSENSITIVE">Diamond Sensitive</option>
        </select>
    </div
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
    <div>
        E-Value: <input type="text" class="small" id="evalue-$option_id" name="evalue-$option_id" size="5"
            value="$default_evalue">
        Negative log of e-value for all-by-all BLAST (&ge;1; default $default_evalue)
    </div>
    <div class="primary-input">
        Input an alternative e-value for BLAST to calculate the edges.
        We suggest using a larger e-value
        (smaller negative log) for short sequences and a smaller
        e-value (larger negative log) for separating
        divergent homologues in different clusters.
    </div>
</div>
HTML;
}


function add_domain_option($option_id, $specify_family = false) {
    $option_text = $specify_family ? "Options" : "Option";
    echo <<<HTML
<h3>Family Domain Boundaries $option_text</h3>
<div>
    <div>
        Pfam and InterPro databases define domain boundaries for members of their families.
    </div>
    <div>
        Domain:
        <input type="checkbox" id="domain-$option_id" name="domain-$option_id" value="1" class="bigger">
        <label for="domain-$option_id">Sequences trimmed to the domain boundaries defined by the input family will be used for the calculations.</label>
    </div>
HTML;
    if ($specify_family) {
        echo <<<HTML
    <div>
        Family:
        <input type="text" name="accession-input-domain-family" id="accession-input-domain-family" style="width: 100px" disabled />
        Use domain boundaries from the specified family (single entry).
    </div>
HTML;
    }
    echo <<<HTML
</div>
HTML;
}


function add_family_input_option_family_only($option_id, $max_full_family) {
    return add_family_input_option_base($option_id, $max_full_family, false, "");
}


function add_family_input_option($option_id, $max_full_family) {
    return add_family_input_option_base($option_id, $max_full_family, true, get_fraction_html($option_id));
}


function add_family_input_option_base($option_id, $max_full_family, $include_intro, $fraction_html) {
    if ($include_intro) {
        $option_text = $fraction_html ? "Options" : "Option";
        echo <<<HTML
<h3>Protein Family Addition $option_text</h3>
<div>
    <div>
        Add sequences belonging to Pfam and/or InterPro families to the sequences used to generate the SSN.
    </div>
HTML;
    } else {
        echo "<div>\n";
    }
    echo <<<HTML
    <div class="primary-input">
        <div>
            <input type="text" id="families-input-$option_id" name="families-input-$option_id">
            <input type="checkbox" id="use-uniref-$option_id" class="cb-use-uniref bigger" value="1">
            <label for="use-uniref-$option_id">Use <select id="uniref-ver-$option_id" name="uniref-ver-$option_id" class="bigger"><option value="90">UniRef90</option><option value="50">UniRef50</option></select> cluster ID sequences instead of the full family</label>
            <div style="margin-top: 10px">
HTML;
    echo est_ui::make_pfam_size_box("family-size-container-$option_id", "family-count-table-$option_id");
    echo <<<HTML
            </div>
        </div>
        The input format is a single family or comma/space separated list of families.
        Families should be specified as PFxxxxx (five digits),
        IPRxxxxxx (six digits) or CLxxxx (four digits) for Pfam clans.<br>
        For families with a size greater than $max_full_family,
        the SSN will instead contain UniRef90 or UniRef50 representative sequences.
    </div>
$fraction_html
</div>
HTML;
}


function get_fraction_html($option_id) {
    $default_fraction = functions::get_fraction(); 
    return <<<HTML
    <div>
        <div>
            Fraction:
            <input type="text" class="small fraction" id="fraction-$option_id" name="fraction-$option_id" value="$default_fraction" size="5">
            <a class="question" title="Either fraction or UniRef can be used, not both.">?</a>
            Reduce the number of sequences used to a fraction of the full family size (&ge; 1; default:
            $default_fraction)
        </div>
        <div class="primary-input">
            Selects every Nth sequence in the family; the sequences are assumed to be
            added randomly to UniProt, so the selected sequences are assumed to be a
            representative sampling of the family. This allows reduction of the size of the SSN.
        </div>
    </div>
HTML;
}


function output_job_list($jobs, $show_archive = false) {
    echo <<<HTML
            <table class="pretty_nested" style="table-layout:fixed">
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
    return output_row(RT_NESTED_COLOR, $id, NULL, $job["key"], $job, $bg_color, false);
}

function output_analysis_row($id, $key, $job, $bg_color) {
    return output_row(RT_ANALYSIS, $id, $job["analysis_id"], $key, $job, $bg_color, false);
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
    if ($is_completed) {
        $aid_param = $row_type == RT_ANALYSIS ? "&analysis_id=$aid" : "";
        $link_start = "<a href='$script?id=$id&key=${key}${aid_param}' class='$link_class'>";
        $link_end = "</a>";
    }
    $id_text = "$link_start${id}$link_end";

    if ($row_type == RT_ANALYSIS) {
        $name_style = "style=\"padding-left: 35px;\"";
        $name = '[Analysis] ' . $name;
        $id_text = "";
    } elseif ($row_type == RT_NESTED_COLOR) {
        $name_style = "style=\"padding-left: 70px;\"";
        $id_text = "";
    }
    $name = "<span title='$id'>$name</span>";

    $status_update_html = "";
    if ($show_archive)
        $status_update_html = "<div style='float:right' class='archive-btn' data-type='gnn' data-id='$id' data-key='$key' title='Archive Job'><i class='fas fa-trash-alt'></i></div>";

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

