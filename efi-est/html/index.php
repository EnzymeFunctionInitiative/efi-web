<?php
require_once "../includes/main.inc.php";
require_once "../libs/user_jobs.class.inc.php";
require_once("../../includes/login_check.inc.php");
require_once "../libs/ui.class.inc.php";

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

$useUniref90 = true;
$useUniref50 = true;
$useAdvancedFamilyInputs = functions::option_e_enabled();
$maxFullFamily = number_format(functions::get_maximum_full_family_count(), 0);

$db_modules = global_settings::get_database_modules();

$updateMessage = functions::get_update_message() .
    "<br>SSNs can now be generated using UniRef90 and UniRef50 databases.";


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
over 80% of the sequence length are grouped together and represented by a single seed 
sequence (the longest sequence in the group). UniRef50 is similar except that the
sequence identity is &ge;50%. If one of the UniRef databases is used,
the output SSN is equivalent to a 90% (for UniRef90) or 50% (for UniRef50)
Representative Node Network with each node corresponding to a seed sequence; in this
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

<div class="tabs">
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
    $allowStatusUpdate = true;
    outputJobList($jobs, $allowStatusUpdate);

    if (count($tjobs)) {
        echo "            <h3>Training Jobs</h3>\n";
        outputJobList($tjobs);
    }
?>
         </div>
<?php
} ?>

<?php if ($showTrainingJobsTab) {
    echo "        <div id=\"tjobs\" class=\"tab\">\n";
    outputJobList($tjobs);
    echo "        </div>\n";
} ?>

<?php if (functions::option_a_enabled()) { ?>
        <div id="optionAtab" class="tab">

            <p>
            The provided sequence is used as the query for a BLAST search of the UniProt database and then, the
            similarities between the sequences are calculated and used to generate the SSN.  Submit only one
            protein sequence without FASTA header. The default maximum number of retrieved sequences is <?php echo number_format(functions::get_default_blast_seq(),0); ?>.
            </p>
    
            <form name="optionAform" id="optionAform" method="post" action="" enctype="multipart/form-data">
                <div class="primary-input">
                    <textarea id="blast-input" name="blast-input"></textarea>
                </div>
                <div>
                    UniProt BLAST Query E-value:
                    <input type="text" class="small" id="blast-evalue" name="blast-evalue"
                        value="<?php echo functions::get_evalue(); ?>">
                    Negative log of e-value for retrieving similar sequences (&ge; 1; default: <?php echo functions::get_evalue(); ?>)
                </div>
                <div style="margin: 10px 0px">
                    Maximum Blast Sequences: <input type="text" id="blast-max-seqs" class="small" name="blast-max-seqs"
                        value="<?php  echo functions::get_default_blast_seq(); ?>">
                    Maximum number of sequences retrieved (&le; <?php echo functions::get_max_blast_seq(); ?>;
                    default: <?php echo functions::get_default_blast_seq(); ?>)
                </div>

<?php if (functions::option_a_families_enabled()) { ?>                 
                <div>
                    If desired, include Pfam and/or InterPro families, in the analysis of your sequence. For Pfam families,
                    the format is a comma separated list of PFxxxxx (five digits); for InterPro families, the format is
                    IPRxxxxxx (six digits); for Pfam clans, the format is CLxxxx (four digits).
                    For Pfam families, InterPro families, and Pfam clans with a size greater than <?php echo $maxFullFamily; ?>,
                    UniRef90 seed sequences will be utilized instead of the full family.
                </div>
                <div class="primary-input">
                    <div>
                        <input type="text" id="families-input-opta" name="families-input-opta">
<div class="new_feature"></div>
                        <input type="checkbox" id="opta-use-uniref" class="cb-use-uniref bigger" value="1">
                        <label for="opta-use-uniref">Use <select id="option-a-uniref-ver" name="option-a-uniref-ver" class="bigger"><option value="90">UniRef90</option><option value="50">UniRef50</option></select> seed sequences instead of the full family</label>
                        <div style="margin-top: 10px">
<?php echo ui::make_pfam_size_box("family-size-container-opta", "family-count-table-opta", $useUniref90, $useUniref50); ?> 
                        </div>
                    </div>
                </div>

                <div class="advanced-toggle">Advanced Family Options <i class="fas fa-plus-square" aria-hidden="true"></i></div>
                <div id="blast-advanced" class="advanced-options" style="display: none;">
                    <div>
                        E-Value: <input type="text" class="small" id="families-evalue-opta" name="families-evalue-opta"
                            value="<?php echo functions::get_evalue(); ?>">
                        Negative log of e-value for all-by-all BLAST (&ge;1; default <?php echo functions::get_evalue(); ?>)
                    </div>
                    <div>
                        Fraction: <input type="text" class="small fraction" id="blast-fraction" name="blast-fraction"
                            value="<?php echo functions::get_fraction(); ?>"> <a class="question" title="Either fraction or UniRef90 can be used, not both.">?</a>
                        Fraction of sequences in Pfam/Interpro family for network (&ge; 1; default:
                        <?php echo functions::get_fraction(); ?>)
                    </div>
<?php make_db_mod_option($db_modules, "a"); ?>
                </div>
<?php } ?>

                <div>Optional job name: <input type="text" class="small" name="option-a-job-name" id="option-a-job-name" value=""></div>
                <div>
                    E-mail address:
                    <input name="email" id="option-a-email" type="text" value="<?php echo $userEmail; ?>" class="email"
                        onfocus='if(!this._haschanged){this.value=""};this._haschanged=true;' value="asdf"><br>
                    When the sequence has been uploaded and processed, you will receive an e-mail containing a link
                    to analyze the data.
                </div>
    
                <div id="option-a-message" style="color: red" class="error_message">
                </div>
                <center>
                    <div><button type="button" class="dark" onclick="submitOptionAForm()">Submit Analysis</button></div>
                </center>
            </form>
        </div>
<?php } ?>

<?php if (functions::option_b_enabled()) { ?>
        <div id="optionBtab" class="tab">
            <p>
            The sequences from the Pfam families, InterPro families, and/or Pfam clans (superfamilies) are retrieved,
            and then, the similarities between the sequences are calculated and used to generate the SSN.
            For Pfam families, the format is a comma separated list of PFxxxxx (five digits); for InterPro families, the
            format is IPRxxxxxx (six digits); for Pfam clans, the format is CLxxxx (four digits).
            Lists of Pfam families, InterPro families, and Pfam clans are included in the <a href="notes.php">release notes</a>.
            </p>
            <p>
            <a href="https://www.ebi.ac.uk/interpro/search/sequence-search">InterProScan sequence search</a> can be used to
            find matches within the InterPro database for a given sequence.
            </p>
            <p>
            The maximum number of retrieved sequences is <?php echo $maxSeqFormatted; ?>.
            For Pfam families, InterPro families, and Pfam clans with a size greater than <?php echo $maxFullFamily; ?>,
            UniRef90 seed sequences will be utilized instead of the full family.
            </p>

            <form name="optionBform" id="optionBform" method="post" action="">
                <div class="primary-input">
                    <input type="text" id="families-input" name="families-input">
<div class="new_feature"></div>
                    <input type="checkbox" id="pfam-use-uniref" class="cb-use-uniref bigger" value="1">
                    <label for="pfam-use-uniref">Use <select id="option-b-uniref-ver" name="option-b-uniref-ver" class="bigger"><option value="90">UniRef90</option><option value="50">UniRef50</option></select> seed sequences instead of the full family</label>
                    <div style="margin-top: 10px">
<?php echo ui::make_pfam_size_box('family-size-container', 'family-count-table', $useUniref90, $useUniref50); ?> 
                    </div>
                </div>
                

                <div class="advanced-toggle">Advanced Options <i class="fas fa-plus-square" aria-hidden="true"></i></div>
                <div style="display: none;" class="advanced-options">
                    <div>
                        E-Value: <input type="text" class="small" id="pfam-evalue" name="pfam-evalue"
                            value="<?php echo functions::get_evalue(); ?>">
                        Negative log of e-value for all-by-all BLAST (&ge;1; default <?php echo functions::get_evalue(); ?>)
                    </div>
                    <div>
                        Fraction: <input type="text" class="small fraction" id="pfam-fraction" name="pfam-fraction"
                            value="<?php echo functions::get_fraction(); ?>"> <a class="question" title="Either fraction or UniRef90 can be used, not both.">?</a>
                        Fraction of sequences in Pfam/Interpro family for network (&ge; 1; default:
                        <?php echo functions::get_fraction(); ?>)
                    </div>
                    <div>
                        Enable Domain: <input type="checkbox" id="pfam-domain" name="pfam-domain" value="1">
                        Check to generate SSN with Pfam-defined domains (default: off)
                    </div>
<?php    if ($useAdvancedFamilyInputs) { ?>
                    <div>
                        Sequence Identity: <input type="text" class="small" id="pfam-seqid" name="pfam-seqid" value="1">
                        Sequence identity (&le; 1; default: 1)
                    </div>
                    <div>
                        Sequence Length Overlap:
                        <input type="text" class="small" id="pfam-length-overlap" name="pfam-length-overlap" value="1">
                        Sequence length overlap (&le; 1; default: 1)
                    </div>
<?php    } else { ?>
                    <div>
                        <input type="hidden" id="pfam-seqid" value="">
                        <input type="hidden" id="pfam-length-overlap" value="">
                    </div>
<?php    } ?>
<?php    if (functions::get_program_selection_enabled()) { ?>
                    <div>
                        Select Program to use: 
                        <select name="option-b-program" id="option-b-program">
                            <option value="BLAST">Blast</option>
                            <option value="BLAST+">Blast+</option>
                            <option selected="selected" value="DIAMOND">Diamond</option>
                        	<option value="DIAMONDSENSITIVE">Diamond Sensitive</option>
                        </select>
                    </div>
<?php    } ?>
<?php    make_db_mod_option($db_modules, "b"); ?>
                </div>

                <div>Optional job name: <input type="text" class="small" name="option-b-job-name" id="option-b-job-name" value=""></div>
                <div>
                    E-mail address:
                    <input name="email" id="option-b-email" type="text" value="<?php echo $userEmail; ?>" class="email"
                        onfocus='if(!this._haschanged){this.value=""};this._haschanged=true;'><br>
                    When the sequence has been uploaded and processed, you will receive an e-mail containing a link
                    to analyze the data.
                </div>
    
                <div id="option-b-message" style="color: red" class="error_message">
                    <?php if (isset($message)) { echo "<h4 class='center'>" . $message . "</h4>"; } ?>
                </div>
                <center>
                    <div><button type="button" class="dark" onclick="submitOptionBForm()">Submit Analysis</button></div>
                </center>
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
                        <input type="checkbox" id="fasta-use-headers" name="fasta-use-headers" value="1"> <b>Read FASTA headers</b><br>
                        When selected, recognized UniProt or Genbank identifiers from FASTA headers are used to retrieve
                        corresponding node attributes from the UniProt database.
                    </div>
<?php echo ui::make_upload_box("FASTA File:", "fasta-file", "progress-bar-fasta", "progress-num-fasta"); ?>
                </div>

                    <div>
                        If desired, include Pfam and/or InterPro families, in the analysis of your FASTA file. For Pfam families,
                        the format is a comma separated list of PFxxxxx (five digits); for InterPro families, the format is
                        IPRxxxxxx (six digits); for Pfam clans, the format is CLxxxx (four digits).
                        For Pfam families, InterPro families, and Pfam clans with a size greater than <?php echo $maxFullFamily; ?>,
                        UniRef90 seed sequences will be utilized instead of the full family.
                    </div>
                <div class="primary-input">
                    <div>
                        <input type="text" id="families-input-optc" name="families-input-optc">
<div class="new_feature"></div>
                        <input type="checkbox" id="optc-use-uniref" class="cb-use-uniref bigger" value="1">
                        <label for="optc-use-uniref">Use <select id="option-c-uniref-ver" name="option-c-uniref-ver" class="bigger"><option value="90">UniRef90</option><option value="50">UniRef50</option></select> seed sequences instead of the full family</label>
                        <div style="margin-top: 10px">
<?php echo ui::make_pfam_size_box("family-size-container-optc", "family-count-table-optc", $useUniref90, $useUniref50); ?> 
                        </div>
                    </div>
                </div>
                
                <div class="advanced-toggle">Advanced Options <i class="fas fa-plus-square" aria-hidden="true"></i></div>
                <div style="display: none;" class="advanced-options">
                    <div>
                        E-Value: <input type="text" class="small" id="fasta-evalue" name="fasta-evalue"
                            value="<?php echo functions::get_evalue(); ?>">
                        Negative log of e-value for all-by-all BLAST (&ge;1; default: <?php echo functions::get_evalue(); ?>)
                    </div>
                    <div>
                        Fraction:
                        <input type="text" class="small fraction" id="fasta-fraction" name="fasta-fraction"
                            value="<?php echo functions::get_fraction(); ?>"> <a class="question" title="Either fraction or UniRef90 can be used, not both.">?</a>
                        Fraction of sequences in Pfam/Interpro family for network (&ge; 1; default: 
                        <?php echo functions::get_fraction(); ?>)
                    </div>
<?php    if (functions::get_program_selection_enabled()) { ?>
                    <div>
                        Select Program to use:
                        <select name="option-c-program" id="option-c-program">
                            <option value="BLAST">Blast</option>
                            <option value="BLAST+">Blast+</option>
                            <option selected="selected" value="DIAMOND">Diamond</option>
                        	<option value="DIAMONDSENSITIVE">Diamond Sensitive</option>
                        </select>
                    </div>
<?php    } ?>
<?php    make_db_mod_option($db_modules, "c"); ?>
                </div>

                <div>Optional job name: <input type="text" class="small" name="option-c-job-name" id="option-c-job-name" value=""></div>
                <div>
                    E-mail address:
                    <input name="email" id="option-c-email" type="text" value="<?php echo $userEmail; ?>" class="email"
                        onfocus='if(!this._haschanged){this.value=""};this._haschanged=true;'><br>
                    When the sequence has been uploaded and processed, you will receive an e-mail containing a link
                    to analyze the data.
                </div>
    
                <div id="option-c-message" style="color: red" class="error_message">
                    <?php if (isset($message)) { echo "<h4 class='center'>" . $message . "</h4>"; } ?>
                </div>
                <center>
                    <div><button type="button" class="dark" onclick="submitOptionCForm()">Submit Analysis</button></div>
                </center>
            </form>
        </div>
<?php    } ?>

<?php    if (functions::option_d_enabled()) { ?>
        <div id="optionDtab" class="tab">
            Input a list of Uniprot, NCBI, or Genbank sequence accession IDs, and/or upload a text file containing
            the accession IDs.
            
            <form name="optionDform" id="optionDform" method="post" action="">
                <div class="primary-input">
                    <textarea id="accession-input" name="accession-input"></textarea>
                    <div>
<?php echo ui::make_upload_box("Accession ID File:", "accession-file", "progress-bar-accession", "progress-num-accession"); ?>
                    </div>
                    <div id="accession-seq-type-container">
                        Treat UniProt accession IDs as:
                        <select id="accession-seq-type">
                            <option value="uniprot" selected>UniProt IDs</option>
                            <option value="uniref90">UniRef90 seed sequence IDs</option>
                            <option value="uniref50">UniRef50 seed sequence IDs</option>
                        </select>
                        <a class="question" title="If a list of UniProt accession IDs is provided, this option can be used to specify if the accession IDs are to be treated as UniRef50, UniRef90, or UniProt sequences.">?</a>
                    </div>
                </div>

                <div>
                    If desired, include Pfam and/or InterPro families, in the analysis of your list of IDs. For Pfam families,
                    the format is a comma separated list of PFxxxxx (five digits); for InterPro families, the format is
                    IPRxxxxxx (six digits); for Pfam clans, the format is CLxxxx (four digits).
                    For Pfam families, InterPro families, and Pfam clans with a size greater than <?php echo $maxFullFamily; ?>,
                    UniRef90 seed sequences will be utilized instead of the full family.
                </div>
                <div class="primary-input">
                    <div>
                        <input type="text" id="families-input-optd" name="families-input-optd">
<div class="new_feature"></div>
                        <input type="checkbox" id="optd-use-uniref" class="cb-use-uniref bigger" value="1">
                        <label for="optd-use-uniref">Use <select id="option-d-uniref-ver" name="option-d-uniref-ver" class="bigger"><option value="90">UniRef90</option><option value="50">UniRef50</option></select> seed sequences instead of the full family</label>
                        <div style="margin-top: 10px">
<?php echo ui::make_pfam_size_box("family-size-container-optd", "family-count-table-optd", $useUniref90, $useUniref50); ?> 
                        </div>
                    </div>
                </div>
                
                <div class="advanced-toggle">Advanced Options <i class="fas fa-plus-square" aria-hidden="true"></i></div>
                <div style="display: none;" class="advanced-options">
                    <div>
                        E-Value: <input type="text" class="small" id="accession-evalue" name="accession-evalue" 
                            value="<?php echo functions::get_evalue(); ?>">
                        Negative log of e-value for all-by-all BLAST (&ge;1; default: <?php echo functions::get_evalue(); ?>)
                    </div>
                    <div>
                        Fraction: <input type="text" class="small fraction" id="accession-fraction" name="accession-fraction" 
                            value="<?php echo functions::get_fraction(); ?>"> <a class="question" title="Either fraction or UniRef90 can be used, not both.">?</a>
                        Fraction of sequences in Pfam/Interpro family for network (&ge; 1; default:
                        <?php echo functions::get_fraction(); ?>)
                    </div>
<?php    if ($useAdvancedFamilyInputs) { ?>
                    <div>
                        Expand UniRef homologs: 
                        <input type="checkbox" id="accession-use-uniref" name="accession-use-uniref"
                            onchange="toggleUniref('accession-uniref-version', this)">
                        Check to expand the homologs for any input sequences that are UniRef seed sequences (default: off)
                        <div>
                            <select name="accession-uniref-version" id="accession-uniref-version" disabled="disabled">
                                <option value="50">UniRef50</option>
                                <option value="90">UniRef90</option>
                            </select>
                        </div>
                    </div>
<?php    } else { ?>
                    <div>
                        <input type="checkbox" id="accession-use-uniref" value="" style="display: none">
                        <input type="hidden" id="accession-uniref-version" value="">
                    </div>
<?php    } ?>
<?php    if (functions::get_program_selection_enabled()) { ?>
                    <div>
                        Select Program to use:
                        <select name='option_d_program' id='option_d_program'>
                            <option value='BLAST'>Blast</option>
                            <option value='BLAST+'>Blast+</option>
                            <option selected='selected' value='DIAMOND'>Diamond</option>
                        	<option value='DIAMONDSENSITIVE'>Diamond Sensitive</option>
                        </select>
                    </div>
<?php    } ?>
<?php    make_db_mod_option($db_modules, "d"); ?>
                </div>

                <div>Optional job name: <input type="text" class="small" name="option-d-job-name" id="option-d-job-name" value=""></div>
                <div>
                    E-mail address:
                    <input name="email" id="option-d-email" type="text" value="<?php echo $userEmail; ?>" class="email"
                        onfocus='if(!this._haschanged){this.value=""};this._haschanged=true;'><br>
                    When the sequence has been uploaded and processed, you will receive an e-mail containing a link
                    to analyze the data.
                </div>
    
                <div id="option-d-message" style="color: red" class="error_message">
                    <?php if (isset($message)) { echo "<h4 class='center'>" . $message . "</h4>"; } ?>
                </div>
                <center>
                    <div><button type="button" class="dark" onclick="submitOptionDForm()">Submit Analysis</button></div>
                </center>
            </form>
        </div>
<?php    } ?>

<?php if (functions::option_e_enabled()) { ?>
        <div id="optionEtab" class="tab">
            <div>
                The sequences from the Pfam and/or InterPro families are retrieved, and then, the similarities between the
                sequences are calculated and used to generate the SSN. Advanced options, including specifying the use of
                UniRef, sequence identity, and sequence length are provided.
                For Pfam families, the format is a comma separated list of PFxxxxx (five digits); for InterPro families, the
                format is IPRxxxxxx (six digits). The maximum number of retrieved sequences is <?php echo $maxSeqFormatted; ?>.
                For Pfam families, InterPro families, and Pfam clans with a size greater than <?php echo $maxFullFamily; ?>,
                UniRef90 seed sequences will be utilized instead of the full family.
            </div>

            <form name="optionEform" id="optionEform" method="post" action="">
                <div class="primary-input">
                    <input type="text" id="option-e-input" name="option-e-input">
                    <div style="margin-top: 10px">
<?php echo ui::make_pfam_size_box('option-e-size-container', 'option-e-count-table', $useUniref90, $useUniref50); ?> 
                    </div>
                </div>
                

                <div class="advanced-toggle">Advanced Options <i class="fas fa-plus-square" aria-hidden="true"></i></div>
                <div style="display: none;" class="advanced-options">
                    <div>
                        E-Value: <input type="text" class="small" id="pfam-plus-evalue" name="pfam-evalue"
                            value="<?php echo functions::get_evalue(); ?>">
                        Negative log of e-value for all-by-all BLAST (&ge;1; default <?php echo functions::get_evalue(); ?>)
                    </div>
                    <div>
                        Fraction: <input type="text" class="small fraction" id="pfam-plus-fraction" name="pfam-fraction"
                            value="<?php echo functions::get_fraction(); ?>"> <a class="question" title="Either fraction or UniRef90 can be used, not both.">?</a>
                        Fraction of sequences in Pfam/Interpro family for network (&ge; 1; default:
                        <?php echo functions::get_fraction(); ?>)
                    </div>
                    <div>
                        Enable Domain: <input type="checkbox" id="pfam-plus-domain" name="pfam-domain" value="1">
                        Check to generate SSN with Pfam-defined domains (default: off)
                    </div>
                    <div>
                        Sequence Identity: <input type="text" class="small" id="pfam-plus-seqid" name="pfam-seqid" value="1">
                        Sequence identity (&le; 1; default: 1)
                    </div>
                    <div>
                        Minimum Sequence Length: <input type="text" class="small" id="pfam-plus-min-seq-len" name="pfam-min-seq-len" value="">
                    </div>
                    <div>
                        Maximum Sequence Length: <input type="text" class="small" id="pfam-plus-max-seq-len" name="pfam-max-seq-len" value="">
                    </div>
                    <div>
                        Sequence Length Overlap:
                        <input type="text" class="small" id="pfam-plus-length-overlap" name="pfam-length-overlap" value="1">
                        Sequence length overlap (&le; 1; default: 1)
                    </div>
                    <div>
                        Do not demultiplex:
                        <input type="checkbox" id="pfam-plus-demux" name="pfam-plus-demux" value="1">
                        Check to prevent a demultiplex to expand cd-hit clusters (default: demultiplex)
                    </div>
<?php    if (functions::get_program_selection_enabled()) { ?>
                    <div>
                        Select Program to use: 
                        <select name="option-e-program" id="option-e-program">
                            <option value="BLAST">Blast</option>
                            <option value="BLAST+">Blast+</option>
                            <option selected="selected" value="DIAMOND">Diamond</option>
                        	<option value="DIAMONDSENSITIVE">Diamond Sensitive</option>
                        </select>
                    </div>
<?php    } ?>
<?php    make_db_mod_option($db_modules, "e"); ?>
                </div>
    
                <div>Optional job name: <input type="text" class="small" name="option-e-job-name" id="option-e-job-name" value=""></div>
                <div>
                    E-mail address:
                    <input name="email" id="option-e-email" type="text" value="<?php echo $userEmail; ?>" class="email"
                        onfocus='if(!this._haschanged){this.value=""};this._haschanged=true;'><br>
                    When the sequence has been uploaded and processed, you will receive an e-mail containing a link
                    to analyze the data.
                </div>
    
                <div id="option-e-message" style="color: red" class="error_message">
                    <?php if (isset($message)) { echo "<h4 class='center'>" . $message . "</h4>"; } ?>
                </div>
                <center>
                    <div><button type="button" class="dark" onclick="submitOptionEForm()">Submit Analysis</button></div>
                </center>
            </form>
        </div>
<?php } ?>

<?php    if (functions::colorssn_enabled()) { ?>
        <div id="colorssntab" class="tab">
            <b>Color a previously generated SSN and return associated cluster data.</b><br>
            Independent sequence clusters in the uploaded SSN are identified, numbered and colored. Summary tables,
            sets of IDs and sequences for specific clusters are provided. A Cytoscape-edited SNN can serve as input for this utility.
            In order for all of the new features to work correctly, SSNs generated by EFI-EST <?php echo functions::get_est_version(); ?>
            (released 8/17/2017) should be used. 

            <form name="colorSsnForm" id="colorSsnform" method="post" action="">
                <div class="primary-input">
<?php echo ui::make_upload_box("SNN to color and analyze (uncompressed or zipped XGMML file):", "colorssn-file", "progress-bar-colorssn", "progress-num-colorssn"); ?>
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

    $(document).ready(function() {
        $(".tabs .tab-headers a").on("click", function(e) {
            var curAttrValue = $(this).attr("href");
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

        $("#create-accordion" ).accordion({
            icons: { "header": "ui-icon-plus", "activeHeader": "ui-icon-minus" },
            heightStyle: "content"
        });

        var setupFamilyInput = function (familyInputId, containerOutputId, countOutputId, unirefCbId, unirefVerId, fractionId, dbVerId) {
            $("#" + familyInputId).on("input", function() {
                checkFamilyInput(familyInputId, containerOutputId, countOutputId, familyInputId, unirefCbId, unirefVerId, fractionId, dbVerId);
            });
            $("#" + unirefCbId).change(function() {
                checkFamilyInput(familyInputId, containerOutputId, countOutputId, familyInputId, unirefCbId, unirefVerId, fractionId, dbVerId);
            });
            $("#" + unirefVerId).change(function() {
                checkFamilyInput(familyInputId, containerOutputId, countOutputId, familyInputId, unirefCbId, unirefVerId, fractionId, dbVerId);
            });
            $("#" + fractionId).on("input", function() {
                checkFamilyInput(familyInputId, containerOutputId, countOutputId, familyInputId, unirefCbId, unirefVerId, fractionId, dbVerId);
            });
            $("#" + dbVerId).on("input", function() {
                checkFamilyInput(familyInputId, containerOutputId, countOutputId, familyInputId, unirefCbId, unirefVerId, fractionId, dbVerId);
            });
        };

        setupFamilyInput("families-input-opta", "family-size-container-opta", "family-count-table-opta", "opta-use-uniref", "option-a-uniref-ver", "blast-fraction", "option-a-db-mod");
        setupFamilyInput("families-input", "family-size-container", "family-count-table", "pfam-use-uniref", "option-b-uniref-ver", "pfam-fraction", "option-b-db-mod");
        setupFamilyInput("option-e-input", "option-e-size-container", "option-e-count-table", "opte-use-uniref90", "option-e-uniref-ver", "pfam-plus-fraction", "option-e-db-mod");
        setupFamilyInput("families-input-optd", "family-size-container-optd", "family-count-table-optd", "optd-use-uniref", "option-d-uniref-ver", "accession-fraction", "option-d-db-mod");
        setupFamilyInput("families-input-optc", "family-size-container-optc", "family-count-table-optc", "optc-use-uniref", "option-c-uniref-ver", "fasta-fraction", "option-c-db-mod");

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

        $("#accession-file").on("change", function(e) {
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
        });

    }).tooltip();
</script>
<script src="<?php echo $SiteUrlPrefix; ?>/js/custom-file-input.js" type="text/javascript"></script>
<script src="js/family-counts.js" type="text/javascript"></script>

<div id="family-warning" class="hidden" title="Family Size Warning">
<div style="color:red;">
The family(ies) selected has <span id="family-warning-total-size"> </span> proteins&mdash;this is greater than
the maximum allowed (<?php echo $maxFullFamily; ?>). To reduce computing time and the size of
output SSN, UniRef90 seed sequences will automatically be used.
</div>

<p>In UniRef90, sequences that share &ge;90% sequence identity over 80% of the sequence 
length are grouped together and represented by a single seed sequence. The output 
SSN is equivalent a to 90% Representative Node Network with each node corresponding 
to a seed sequence, and for which the node attribute "UniRef90 Cluster IDs" lists 
all the sequences represented by a node. UniRef90 SSNs are compatible with the Color 
SSN utility as well as the EFI-GNT tool.
</p>

<p>Press Ok to continue with UniRef90.</p>

<?php /* ?>
    The family(ies) selected has <span id="family-warning-total-size"> </span> 
    proteins<span id="family-warning-fraction-size"></span>, which is greater
    than the maximum allowed (<?php echo $maxFullFamily; ?>) for full family
    inclusion.  UniRef90 seed sequences will automatically be 
    used instead of the full number of proteins in the family(ies).  Press OK to continue with UniRef90
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

function make_db_mod_option($db_modules, $form) {
    if (count($db_modules) < 2)
        return "";

    $id = "option-$form-db-mod";
    $ws = "                    ";
    echo <<<HTML
$ws Database version:
$ws<select name="$id" id="$id">
HTML;

    foreach ($db_modules as $mod) {
        $mod_name = $mod[1];
        echo "$ws    <option value=\"$mod_name\">$mod_name</option>\n";
    }

    echo <<<HTML
$ws</select>
HTML;
}

function outputJobList($jobs, $allowStatusUpdate = false) {
    echo <<<HTML
            <table class="pretty_nested" style="table-layout:fixed">
                <thead>
                    <th class="id-col">ID</th>
                    <th>Job Name</th>
                    <th class="date-col">Date Completed</th>
                </thead>
                <tbody>
HTML;

    $lastBgColor = "#eee";
    for ($i = 0; $i < count($jobs); $i++) {
        $key = $jobs[$i]["key"];
        $id = $jobs[$i]["id"];
        $name = $jobs[$i]["job_name"];
        $dateCompleted = $jobs[$i]["date_completed"];
        $isCompleted = $jobs[$i]["is_completed"];
    
        $idText = "";
        $linkStart = "";
        $linkEnd = "";
        $nameStyle = "";
    
        if ($jobs[$i]["is_analysis"]) {
            if ($isCompleted) {
                $analysisId = $jobs[$i]["analysis_id"];
                $linkStart = "<a href=\"stepe.php?id=$id&key=$key&analysis_id=$analysisId\">";
                $linkEnd = "</a>";
            }
            $nameStyle = "style=\"padding-left: 50px;\"";
            //$name = '<i class="fas fa-long-arrow-right" aria-hidden="true"></i> ' . $name;
            $name = '[Analysis] ' . $name;
        } else {
            if ($isCompleted) {
                $theScript = $jobs[$i]["is_colorssn"] ? "view_coloredssn.php" : "stepc.php";
                $linkStart = "<a href=\"$theScript?id=$id&key=$key\">";
                $linkEnd = "</a>";
            }
            $idText = "$linkStart${id}$linkEnd";
            if ($lastBgColor == "#fff")
                $lastBgColor = "#eee";
            else
                $lastBgColor = "#fff";
        }

        $statusUpdateHtml = "";
        if ($allowStatusUpdate && !$jobs[$i]["is_analysis"])
            $statusUpdateHtml = "<div style=\"float:right\" class=\"archive-btn\" data-type=\"gnn\" data-id=\"$id\" data-key=\"$key\" title=\"Archive Job\"><i class=\"fas fa-trash-alt\"></i></div>";
        
        echo <<<HTML
                    <tr style="background-color: $lastBgColor">
                        <td>$idText</td>
                        <td $nameStyle>$linkStart${name}$linkEnd</td>
                        <td>$dateCompleted $statusUpdateHtml</td>
                    </tr>
HTML;
    }

    echo <<<HTML
                </tbody>
            </table>
HTML;
}

?>

