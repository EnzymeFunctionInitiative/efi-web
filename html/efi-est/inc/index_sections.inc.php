<?php
require_once(__DIR__ . "/../../../init.php");

use \efi\global_settings;
use \efi\ui;
use \efi\est\est_settings;
use \efi\est\colorssn;
use \efi\est\est_ui;


function output_option_a($use_advanced_options, $db_modules, $user_email, $example_fn = false) {
    $default_evalue = get_default_evalue();
    $max_blast_seq = get_max_blast_seq();
    $default_blast_seq = get_default_blast_seq();
    $show_example = $example_fn !== false;
    $example_fn = $example_fn === false ? function(){} : $example_fn;
?>
        <div id="optionAtab" class="ui-tabs-panel ui-widget-content">
<?php $example_fn("DESC_START"); ?>
            <p class="p-heading">
            Generate a SSN for a single protein and its closest homologues in the UniProt database.
            </p>

            <p>
            The input sequence is used as the query for a search of the UniProt database using BLAST.
            Sequences that are similar to the query in UniProt are retrieved.
            <?php echo add_blast_calc_desc()[0]; ?>
            </p>
<?php $example_fn("DESC_END"); ?>

            <form name="optionAform" id="optionAform" method="post" action="" enctype="multipart/form-data">
<?php $example_fn("WRAP_START"); ?>
                <div class="primary-input">
                    <div class="secondary-name">
                        Query Sequence:
                    </div>
                    <textarea id="blast-input" name="blast-input"></textarea>
                    Input a single <b>protein sequence</b> only.
            The default maximum number of  retrieved sequences is
            <?php echo number_format($default_blast_seq, 0); ?>.
                </div>
<?php $example_fn("WRAP_END"); ?>
<?php $example_fn("POST_A_INPUT"); ?>

                <div class="option-panels">
<?php $example_fn("OPTION_WRAP_START_FIRST"); ?>
                    <div>
                        <?php echo add_taxonomy_filter("opta")[0] ?>
                    </div>
<?php $example_fn("OPTION_WRAP_END"); ?>
<?php $example_fn("POST_TAX"); ?>
<?php $example_fn("OPTION_WRAP_START"); ?>
                    <div>
                        <h3>BLAST Retrieval Options</h3>
                        <div>
                            <div>
                                <span class="input-name">
                                    UniProt BLAST query e-value:
                                </span><span class="input-field">
                                    <input type="text" class="small" id="blast-evalue" name="blast-evalue" size="5"
                                        value="<?php echo $default_evalue; ?>">
                                    Negative log of e-value for retrieving similar sequences (&ge; 1; default: <?php echo $default_evalue; ?>)
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
                                    <input type="text" id="blast-max-seqs" class="small" name="blast-max-seqs" value="<?php echo $default_blast_seq; ?>" size="5">
                                    (&le; <?php echo number_format($max_blast_seq); ?>,
                                    default: <?php echo number_format($default_blast_seq); ?>)
                                </span>
                            </div>
                            <div>
                                <span class="input-name">
                                    Sequence database:
                                </span><span class="input-field">
                                    <select name="blast-db-type" id="blast-db-type">
                                        <option value="uniprot" selected>UniProt</option>
                                        <option value="uniref90">UniRef90</option>
                                        <option value="uniref50">UniRef50</option>
                                    </select>  (UniProt, UniRef90, or UniRef50; default UniProt)
                                </span>
                                <div class="input-desc">
                                    Select the sequence database to BLAST against.
                                </div>
                            </div>
                        </div>
                    </div>
<?php $example_fn("OPTION_WRAP_END"); ?>
<?php $example_fn("POST_A_BLAST"); ?>
    
<?php $example_fn("OPTION_WRAP_START"); ?>
                    <div>
                        <?php echo add_family_input_option("opta", $show_example)[0]; ?>
                    </div>
<?php $example_fn("OPTION_WRAP_END"); ?>
<?php $example_fn("POST_A_FAM"); ?>
<?php $example_fn("POST_FRAC"); ?>

<?php $example_fn("OPTION_WRAP_START"); ?>
                    <div>
                        <?php echo add_ssn_calc_option("opta")[0] ?>
                    </div>
<?php $example_fn("OPTION_WRAP_END"); ?>
<?php $example_fn("POST_CALC"); ?>

<?php $example_fn("OPTION_WRAP_START"); ?>
                    <div>
                        <?php echo add_fragment_option("opta")[0] ?>
                    </div>
<?php $example_fn("OPTION_WRAP_END"); ?>
<?php $example_fn("POST_FRAG"); ?>

<?php $example_fn("OPTION_WRAP_END_LAST"); ?>
<?php $example_fn("DESC_START"); ?>
                    <?php if ($use_advanced_options) { ?>
                    <div>
                        <?php echo add_dev_site_option("opta", $db_modules)[0]; ?>
                    </div>
                    <?php } ?>
<?php $example_fn("DESC_END"); ?>
                </div>

<?php $example_fn("WRAP_START"); ?>
                <?php echo add_submit_html("opta", "optAoutputIds", $user_email)[0]; ?>
<?php $example_fn("WRAP_END"); ?>
<?php $example_fn("POST_SUBMIT"); ?>
            </form>
        </div>
<?php
}

function output_option_b($use_advanced_options, $db_modules, $user_email, $example_fn = false) {
    $show_example = $example_fn !== false;
    $example_fn = $example_fn === false ? function(){} : $example_fn;
?>
        <div id="optionBtab" class="ui-tabs-panel ui-widget-content">
<?php $example_fn("DESC_START"); ?>
            <p class="p-heading">
            Generate a SSN for a protein family.
            </p>

            <p>
            The sequences from the Pfam families, InterPro families, and/or Pfam clans (superfamilies) input are retrieved.
            <?php echo add_blast_calc_desc()[0]; ?>
            </p>
<?php $example_fn("DESC_END"); ?>

            <form name="optionBform" id="optionBform" method="post" action="">
<?php $example_fn("WRAP_START"); ?>
                <?php echo add_family_input_option_family_only("optb", $show_example)[0]; ?>
<?php $example_fn("WRAP_END"); ?>
<?php $example_fn("POST_B_FAM"); ?>

                <div class="option-panels">
<?php $example_fn("OPTION_WRAP_START_FIRST"); ?>
                    <div>
                        <?php echo add_taxonomy_filter("optb")[0] ?>
                    </div>
<?php $example_fn("OPTION_WRAP_END"); ?>
<?php $example_fn("POST_TAX"); ?>
<?php $example_fn("OPTION_WRAP_START"); ?>
                    <div>
                        <?php echo add_domain_option("optb", false, true)[0]; ?>
                    </div>
<?php $example_fn("OPTION_WRAP_END"); ?>
<?php $example_fn("POST_DOM"); ?>
<?php $example_fn("OPTION_WRAP_START"); ?>
                    <div>
                        <h3>Protein Family Option</h3>
                        <?php echo get_fraction_html("optb")[0]; ?>
                    </div>
<?php $example_fn("OPTION_WRAP_END"); ?>
<?php $example_fn("POST_FRAC"); ?>
<?php $example_fn("OPTION_WRAP_START"); ?>
                    <div>
                        <?php echo add_ssn_calc_option("optb")[0] ?>
                    </div>
<?php $example_fn("OPTION_WRAP_END"); ?>
<?php $example_fn("POST_CALC"); ?>
<?php $example_fn("OPTION_WRAP_START"); ?>
                    <div>
                        <?php echo add_fragment_option("optb")[0] ?>
                    </div>
<?php $example_fn("OPTION_WRAP_END"); ?>
<?php $example_fn("POST_FRAG"); ?>
<?php $example_fn("DESC_START"); ?>
<?php $example_fn("OPTION_WRAP_END_LAST"); ?>
                    <?php if ($use_advanced_options) { ?>
                    <div>
                        <?php echo add_dev_site_option("optb", $db_modules, get_advanced_seq_html("optb"))[0]; ?>
                    </div>
                    <?php } ?>
                    <?php if (!$use_advanced_options) { ?>
                        <input type="hidden" id="seqid-optb" value="">
                        <input type="hidden" id="length-overlap-optb" value="">
                    <?php } ?>
<?php $example_fn("DESC_END"); ?>
                </div>

<?php $example_fn("WRAP_START"); ?>
                <?php echo add_submit_html("optb", "optBoutputIds", $user_email)[0]; ?>
<?php $example_fn("WRAP_END"); ?>
<?php $example_fn("POST_SUBMIT"); ?>
            </form>
        </div>
<?php
}

function output_option_c($use_advanced_options, $db_modules, $user_email, $example_fn = false) {
    $show_example = $example_fn !== false;
    $example_fn = $example_fn === false ? function(){} : $example_fn;
?>
        <div id="optionCtab" class="ui-tabs-panel ui-widget-content">
<?php $example_fn("DESC_START"); ?>
            <p class="p-heading">
            Generate a SSN from provided sequences. 
            </p>

            <p>
            <?php echo add_blast_calc_desc()[0]; ?>
            </p>

            <p>
            Input a list of protein sequences in FASTA format or upload a FASTA-formatted sequence file.
            </p>
<?php $example_fn("DESC_END"); ?>
            
            <form name="optionCform" id="optionCform" method="post" action="">
<?php $example_fn("WRAP_START"); ?>
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
<?php $example_fn("WRAP_END"); ?>
<?php $example_fn("POST_C_INPUT"); ?>

                <div class="option-panels">
<?php $example_fn("OPTION_WRAP_START_FIRST"); ?>
                    <div>
                        <?php echo add_taxonomy_filter("optc")[0] ?>
                    </div>
<?php $example_fn("OPTION_WRAP_END"); ?>
<?php $example_fn("POST_TAX"); ?>
<?php $example_fn("OPTION_WRAP_START"); ?>
                    <div>
                        <?php echo add_family_input_option("optc", $show_example)[0]; ?>
                    </div>
<?php $example_fn("OPTION_WRAP_END"); ?>
<?php $example_fn("POST_A_FAM"); ?>
<?php $example_fn("POST_FRAC"); ?>
<?php /*
<?php $example_fn("OPTION_WRAP_START_FIRST"); ?>
                    <div>
                        <?php echo add_domain_option("optc", false, true)[0]; ?>
                    </div>
<?php $example_fn("OPTION_WRAP_END"); ?>
<?php $example_fn("POST_DOM"); ?>
<?php $example_fn("OPTION_WRAP_START"); ?>
 */?>
<?php $example_fn("OPTION_WRAP_START_FIRST"); ?>
                    <div>
                        <?php echo add_ssn_calc_option("optc")[0] ?>
                    </div>
<?php $example_fn("OPTION_WRAP_END"); ?>
<?php $example_fn("POST_CALC"); ?>

<?php $example_fn("OPTION_WRAP_START"); ?>
                    <div>
                        <?php echo add_fragment_option("optc")[0] ?>
                    </div>
<?php $example_fn("OPTION_WRAP_END"); ?>
<?php $example_fn("POST_FRAG"); ?>

<?php $example_fn("OPTION_WRAP_END_LAST"); ?>
<?php $example_fn("DESC_START"); ?>
                    <?php if ($use_advanced_options) { ?>
                    <div>
                        <?php echo add_dev_site_option("optc", $db_modules)[0]; ?>
                    </div>
                    <?php } ?>
<?php $example_fn("DESC_END"); ?>
                </div>

<?php $example_fn("WRAP_START"); ?>
                <?php echo add_submit_html("optc", "optCoutputIds", $user_email)[0]; ?>
<?php $example_fn("WRAP_END"); ?>
<?php $example_fn("POST_SUBMIT"); ?>
            </form>
        </div>
<?php
}

function output_option_d($use_advanced_options, $db_modules, $user_email, $show_example = false) {
?>
        <div id="optionDtab" class="ui-tabs-panel ui-widget-content">
            <p class="p-heading">
            Generate a SSN from a list of UniProt, UniRef, NCBI, or Genbank IDs.
            </p>

            <p>
            <?php echo add_blast_calc_desc()[0]; ?>
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
                        <?php echo add_taxonomy_filter("optd")[0] ?>
                    </div>
                    <div>
                        <?php echo add_domain_option("optd", true, $use_advanced_options)[0]; ?>
                    </div>
                    <div>
                        <?php echo add_family_input_option("optd", $show_example)[0]; ?>
                    </div>
                    <div>
                        <?php echo add_ssn_calc_option("optd")[0] ?>
                    </div>
                    <div>
                        <?php echo add_fragment_option("optd")[0] ?>
                    </div>
                    <?php if ($use_advanced_options) { ?>
                    <div>
                        <?php echo add_dev_site_option("optd", $db_modules)[0]; ?>
                    </div>
                    <?php } ?>
                </div>

                <?php echo add_submit_html("optd", "optDoutputIds", $user_email)[0]; ?>
            </form>
        </div>
<?php
}

function output_colorssn($use_advanced_options, $user_email, $show_example = false, $mode_data = array()) {
    $ssn_filename = !empty($mode_data) ? $mode_data["filename"] : "";
    $ssn_id = !empty($mode_data) ? $mode_data["ssn_id"] : "";
    $ssn_idx = !empty($mode_data) ? $mode_data["ssn_idx"] : "";
?>
        <div id="colorssntab" class="ui-tabs-panel ui-widget-content">
            <p>
                <b>Clusters in the submitted SSN are identified, numbered and colored.</b>
                Summary tables, sets of IDs and sequences per cluster are provided.
            </p>

            <p>
            The clusters are numbered and colored using two conventions: 1)
            <b>Sequence Count Cluster Number</b> assigned in order of decreasing number of UniProt IDs in the 
            cluster; 2) <b>Node Count Cluster Number</b> assigned in order of decreasing number of 
            nodes in the cluster.   
            </p>

            <form name="" id="colorSsnform" method="post" action="">
                <div class="primary-input">
<?php echo ui::make_upload_box("SSN File:", "colorssn-file", "progress-bar-colorssn", "progress-num-colorssn", "", "", $ssn_filename); ?>
                    <div>
                        A Cytoscape-edited SNN can serve as input.
                        The accepted format is XGMML (or compressed XGMML as zip).
                    </div>
                </div>

                <?php if ($use_advanced_options) { ?>
                <div class="option-panels">
                    <div class="initial-open">
                        <h3>Dev Site Options</h3>
                        <div>
                            <div>
                                <?php echo add_extra_ram_option("colorssn") ?>
<!--
                                <span class="input-name">
                                    Extra RAM:
                                </span><span class="input-field">
                                    <input type="checkbox" id="colorssn-extra-ram" name="colorssn-extra-ram" value="1">
                                    <label for="colorssn-extra-ram">Check to use additional RAM (800GB) [default: off]</label>
                                </span>
-->
                            </div>
                            <div>
                                <span class="input-name">
                                    EfiRef:
                                </span><span class="input-field">
                                    <select name="colorssn-efiref" id="colorssn-efiref"><option>None</option><option>50</option><option>70</option></select>
                                    <label for="colorssn-efiref">Assume input is an EfiRef input [default: off]</label>
                                </span>
                            </div>
                            <div>
                                <span class="input-name">
                                    Skip FASTA:
                                </span><span class="input-field">
                                    <input type="checkbox" name="colorssn-skip-fasta" id="colorssn-skip-fasta" value="1">
                                    <label for="colorssn-skip-fasta">Skip generation of FASTA files (saves time) [default: off]</label>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
                <?php if ($ssn_id) { ?>
                    <input type="hidden" name="ssn-source-id-colorssn" id="ssn-source-id-colorssn" value="<?php echo $ssn_id; ?>">
                    <input type="hidden" name="ssn-source-idx-colorssn" id="ssn-source-idx-colorssn" value="<?php echo $ssn_idx; ?>">
                <?php } ?>
                <?php echo add_submit_html("colorssn", "", $user_email)[0]; ?>
            </form>
        </div>
<?php
}

function output_nc($use_advanced_options, $user_email, $show_example = false, $mode_data = array()) {
    $ssn_filename = !empty($mode_data) ? $mode_data["filename"] : "";
    $ssn_id = !empty($mode_data) ? $mode_data["ssn_id"] : "";
    $ssn_idx = !empty($mode_data) ? $mode_data["ssn_idx"] : "";
?>
        <div id="nctab" class="ui-tabs-panel ui-widget-content">

            <p>
            <b>
            Nodes in the submitted SSN are colored according to neighborhood connectivity 
            (number of edges to other nodes). 
            </b>
            </p>
            
            <p>
            The nodes for unresolved families can be difficult to identify in SSNs 
            generated with low alignment scores.  Coloring the nodes according to the 
            number of edges to other nodes (<b>Neighborhood Connectivity</b>, NC) helps identify 
            families with highly connected nodes 
            (<a href="https://doi.org/10.1016/j.heliyon.2020.e05867">https://doi.org/10.1016/j.heliyon.2020.e05867</a>).
            Using <b>Neighborhood Connectivity Coloring</b> as a guide, the alignment score threshold 
            can be chosen in Cytoscape to separate the SSN into families.
            </p>

            <form name="" id="ncSsnform" method="post" action="">
                <div class="primary-input">
<?php echo ui::make_upload_box("SSN File:", "nc-file", "progress-bar-nc", "progress-num-nc", "", "", $ssn_filename); ?>
                    <div>
                        A Cytoscape-edited SNN can serve as input.
                        The accepted format is XGMML (or compressed XGMML as zip).
                    </div>
                </div>
                
                <?php if ($use_advanced_options) { ?>
                    <div class="option-panels">
                        <div>
                            <h3>Dev Site Options</h3>
                            <div>
                                <?php echo add_extra_ram_option("nc") ?>
<!--
                                <div>
                                    <span class="input-name">
                                        Extra RAM:
                                    </span><span class="input-field">
                                        <input type="checkbox" id="nc-extra-ram" name="nc-extra-ram" value="1">
                                        <label for="nc-extra-ram">Check to use additional RAM (800GB) [default: off]</label>
                                    </span>
                                </div>
-->
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <?php if ($ssn_id) { ?>
                    <input type="hidden" name="ssn-source-id-nc" id="ssn-source-id-nc" value="<?php echo $ssn_id; ?>">
                    <input type="hidden" name="ssn-source-idx-nc" id="ssn-source-idx-nc" value="<?php echo $ssn_idx; ?>">
                <?php } ?>
                <?php echo add_submit_html("nc", "", $user_email)[0]; ?>
            </form>
        </div>
<?php
}

function output_cr($use_advanced_options, $user_email, $show_example = false, $mode_data = array()) {
    $ssn_filename = !empty($mode_data) ? $mode_data["filename"] : "";
    $ssn_id = !empty($mode_data) ? $mode_data["ssn_id"] : "";
?>
        <div id="crtab" class="ui-tabs-panel ui-widget-content">

            <p>
            <b>
            Convergence ratio is calculated per cluster.
            </b>
            </p>
            
            <form name="" id="ncSsnform" method="post" action="">
                <div class="primary-input">
<?php echo ui::make_upload_box("SSN File:", "cr-file", "progress-bar-cr", "progress-num-cr", "", "", $ssn_filename); ?>
                    <div>
                        A Cytoscape-edited SNN can serve as input.
                        The accepted format is XGMML (or compressed XGMML as zip).
                    </div>
                </div>
                
                <div class="option-panels">
                    <div class="initial-open">
                        <h3>Alignment Score</h3>
                        <div>
                            <div>
                                <span class="input-name">
                                    Alignment Score:
                                </span><span class="input-field">
                                    <input type="text" id="cr-ascore" name="cr-ascore" value="5" size="5">
                                    The alignment score to calculate convergence ratio per cluster (should be the same as the original SSN alignment score).
                                </span>
                            </div>
                        </div>
                    </div>
                <?php if ($use_advanced_options) { ?>
                    <div>
                        <h3>Dev Site Options</h3>
                        <div>
                                <?php echo add_extra_ram_option("cr") ?>
<!--
                            <div>
                                <span class="input-name">
                                    Extra RAM:
                                </span><span class="input-field">
                                    <input type="checkbox" id="cr-extra-ram" name="cr-extra-ram" value="1">
                                    <label for="cr-extra-ram">Check to use additional RAM (300GB) [default: off]</label>
                                </span>
                            </div>
-->
                        </div>
                    </div>
                <?php } ?>
                </div>

                <?php if ($ssn_id) { ?>
                    <input type="hidden" name="color-ssn-source-color-id" id="color-ssn-source-color-id" value="<?php echo $ssn_id; ?>">
                <?php } ?>
                <?php echo add_submit_html("cr", "", $user_email)[0]; ?>
            </form>
        </div>
<?php
}

function output_cluster($use_advanced_options, $user_email, $show_example = false, $mode_data = array()) {
    $ssn_filename = !empty($mode_data) ? $mode_data["filename"] : "";
    $ssn_id = !empty($mode_data) ? $mode_data["ssn_id"] : "";
    $ssn_idx = !empty($mode_data) ? $mode_data["ssn_idx"] : "";
?>
        <div id="clustertab" class="ui-tabs-panel ui-widget-content">

            <p>
            <b>
            Like the Color SSN utility, clusters in the submitted SSN are identified, 
            numbered and colored.
            </b>
            </p>
            
            <p>
            The SSN clusters are numbered and colored using two conventions:
            <b>Sequence Count Cluster Numbers</b>
            are assigned in order of decreasing number of UniProt IDs in 
            the cluster; <b>Node Count Cluster Numbers</b> are assigned in order of decreasing 
            number of nodes in the cluster. 
            </p>
            
            <p>
            The convergence ratio for each cluster also is calculated.  The convergence 
            ratio is the number of edges in each cluster to the number of sequence pairs.  
            The value decreases from 1.0 for a cluster very similar sequences (same 
            function?) to &lt;&lt;1.0 for clusters with distantly related sequences (different 
            functions?).  
            </p>
            
            <p>
            <b>
            Multiple sequence alignments (MSAs), WebLogos, hidden Markov models (HMMs), 
            length histograms, and consensus residues are computed for each cluster. 
            </b>
            </p>
            
            <p>
            Options are available in the tabs below to select the desired analyses:
            </p>
            
            <p>
            The <b>WebLogos</b> tab provides the WebLogo and MSA for the node IDs in each cluster 
            containing greater than the "<b>Minimum Node Count</b>" specified in the
            <b>Sequence Filter</b> tab.
            The percent identity matrix for the MSA is also provided on this tab.
            </p>
            
            <p>
            The <b>Consensus Residues</b> tab provides a tab-delimited text file with the number 
            of the conserved residues and their MSA positions for each specified residue in 
            each cluster containing greater than the "<b>Minimum Node Count</b>".  Note the 
            default residue is "C" and the percent identity levels that are displayed are 
            from 90 to 10% in intervals of 10%; a residue is counted as "conserved" if it 
            occurs with &ge;80% identity.
            </p>
            
            <p>
            The <b>HMMs</b> tab provides the HMM for each cluster containing greater than the 
            specified "<b>Minimum Node Count</b>". 
            </p>
            
            <p>
            The <b>Length Histograms</b> tab provides length histograms for each cluster 
            containing greater than the specified "<b>Minimum Node Count</b>". 
            </p>

<!--
            <p>
                <b>Clusters in the submitted SSN are identified, numbered and colored.</b>
                Summary tables, sets of IDs and sequences per cluster are provided.
            </p>
            <p>
                <b>HMMs, WebLogos, and consensus residues are computed.</b>
                Options are available in the tabs below to select the desired analyes.
            </p>
-->
            <form name="clusterTab" id="clusterTab" method="post" action="">
                <div class="primary-input">
<?php echo ui::make_upload_box("SSN File:", "cluster-file", "progress-bar-cluster", "progress-num-cluster", "", "", $ssn_filename); ?>
                    <div>
                        A Cytoscape-edited SNN can serve as input.
                        The accepted format is XGMML (or compressed XGMML as zip).
                    </div>
                </div>

                <div class="option-panels">
                        <div>
                            <h3>Sequence Filter</h3>
                            <div>
                                <div>
                                    The MSA is generated with MUSCLE using the node IDs.  Clusters containing less than
                                    the Minimum Node Count will be excluded from the analyses.
                                    Since MUSCLE can fail with a "large" number sequences (variable; anywhere from &gt;750 to 1500),
                                    the Maximum Node Count parameter can be used to limit the number of sequences
                                    that MUSCLE uses.
                                </div>
                                <div>
                                    <span class="input-name">
                                        Minimum Node Count:
                                    </span><span class="input-field">
                                        <input type="text" id="min-seq-msa-cluster" name="min-seq-msa-cluster" value="" size="10">
                                        <label for="min-seq-msa-cluster">Minimum number of nodes in order to include a cluster in the computations [default: 5]</label>
                                    </span>
                                </div>
                                <div>
                                    <span class="input-name">
                                        Maximum Node Count:
                                    </span><span class="input-field">
                                        <input type="text" id="max-seq-msa-cluster" name="max-seq-msa-cluster" value="" size="10">
                                        <label for="max-seq-msa-cluster">Maximum number of nodes to include in the MSA [default: no maximum]</label>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3>WebLogos</h3>
                            <div>
                                <div>
                                    A MSA for the (length-filtered) node IDs is generated using MUSCLE;
                                    the WebLogo is generated with the
                                    <a target="_blank" href="http://weblogo.threeplusone.com">http://weblogo.threeplusone.com</a> code.
                                </div>
                                <div>
                                    <span class="input-name">
                                        Make Weblogo:
                                    </span><span class="input-field">
                                        <input type="checkbox" id="make-weblogo-cluster" name="make-weblogo-cluster" value="1" checked>
                                        <label for="make-weblogo-cluster">Make Weblogos for each cluster [default: on]</label>
                                    </span>
                                </div>
                            </div>
                        </div>
    
                        <div>
                            <h3>Consensus Residues</h3>
                            <div>
                                <div>
                                    <div>
                                        The positions and selected percent identities of the selected residues in the MSA are determined.  
                                    </div>
                                    <span class="input-name">
                                        Compute Consensus Residues:
                                    </span><span class="input-field">
                                        <input type="checkbox" id="make-cr-cluster" name="make-cr-cluster" value="1" checked>
                                        <label for="make-cr-cluster">Compute consensus residues [default: on]</label><br>
                                        <input type="text" id="hmm-aa-list-cluster" name="hmm-aa-list-cluster" value="C" size="10">
                                        <label for="hmm-aa-list-cluster">Residues to compute for (comma-separated list of amino acid codes)</label><br>
                                        <input type="text" id="aa-threshold-cluster" name="aa-threshold-cluster" value="0.9,0.8,0.7,0.6,0.5,0.4,0.3,0.2,0.1" size="20">
                                        <label for="aa-threshold-cluster">Percent identity threshold(s) for determining conservation (multiple comma-separated values allowed) [default: 0.9,0.8,0.7,0.6,0.5,0.4,0.3,0.2,0.1]</label>
                                    </span>
                                </div>
                            </div>
                        </div>
    
                        <div>
                            <h3>HMMs</h3>
                            <div>
                                <div>
                                    The MSA for the (length-filtered) node IDs is used to generate the HMM with hmmbuild from
                                    <a href="http://hmmer.org">HMMER3 (http://hmmer.org)</a>.
                                </div>
                                <div>
                                    <span class="input-name">
                                        Make HMMs:
                                    </span><span class="input-field">
                                        <input type="checkbox" id="make-hmm-cluster" name="make-hmm-cluster" value="1" checked>
                                        <label for="make-hmm-cluster">Make HMMs for each cluster [default: on]</label>
                                    </span>
                                </div>
                            </div>
                        </div>
    
                        <div>
                            <h3>Length Histograms</h3>
                            <div>
                                <div>
                                    Length histograms for the node IDs (where applicable, UniProt, UniRef90, and UniRef50 IDs).
                                </div>
                                <div>
                                    <span class="input-name">
                                        Make Length Histograms:
                                    </span><span class="input-field">
                                        <input type="checkbox" id="make-hist-cluster" name="make-hist-cluster" value="1" checked>
                                        <label for="make-hist-cluster">Make length histograms for each cluster [default: on]</label>
                                    </span>
                                </div>
                            </div>
                        </div>
                <?php if ($use_advanced_options) { ?>
                        <div>
                            <h3>Dev Site Options</h3>
                            <div>
                                <?php echo add_extra_ram_option("cluster") ?>
<!--
                                <div>
                                    <span class="input-name">
                                        Extra RAM:
                                    </span><span class="input-field">
                                        <input type="checkbox" id="cluster-extra-ram" name="cluster-extra-ram" value="1">
                                        <label for="cluster-extra-ram">Check to use additional RAM (800GB) [default: off]</label>
                                    </span>
                                </div>
-->
                                <div>
                                    <span class="input-name">
                                        EfiRef:
                                    </span><span class="input-field">
                                        <select name="cluster-efiref" id="cluster-efiref"><option>None</option><option>50</option><option>70</option></select>
                                        <label for="cluster-efiref">Assume input is an EfiRef input [default: off]</label>
                                    </span>
                                </div>
                            </div>
                        </div>
                <?php } ?>
                </div>
                <?php if ($ssn_id) { ?>
                    <input type="hidden" name="ssn-source-id-cluster" id="ssn-source-id-cluster" value="<?php echo $ssn_id; ?>">
                    <input type="hidden" name="ssn-source-idx-cluster" id="ssn-source-idx-cluster" value="<?php echo $ssn_idx; ?>">
                <?php } ?>
                <?php echo add_submit_html("cluster", "", $user_email)[0]; ?>
            </form>
        </div>
<?php
}

function output_tutorial($show_jobs_tab = false) {
?>
        <div id="tutorial" class="tab <?php echo (!$show_jobs_tab ? "ui-tabs-active" : "") ?>">

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
                    <?php echo est_settings::get_default_blast_seq(1); ?> sequences are collected.
                    This allows a small "full" SSN to be generated and viewed with Cytoscape.
                    This for local high resolution SSNs.
                    </p>
                </li>
                
                <li><b>Families (Option B): Pfam and/or InterPro families; Pfam clans (superfamilies)</b>.
                    Defined protein families are used to generate the SSN.
                    <p class="indentall">
                    Option B allows exploration of sequence-function space from defined 
                    protein families. A limit of <?php echo est_settings::get_max_seq(1); ?> 
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
<?php
}

function output_option_e($use_advanced_options, $db_modules, $user_email, $show_example = false) {
?>
        <div id="optionEtab" class="ui-tabs-panel ui-widget-content">
            <form name="optionEform" id="optionEform" method="post" action="">
                <?php echo add_family_input_option_family_only("opte", $show_example)[0]; ?>

                <div class="option-panels">
                    <div>
                        <?php echo add_domain_option("opte")[0]; ?>
                    </div>
                    <div>
                        <?php echo add_ssn_calc_option("opte")[0] ?>
                    </div>
                    <div>
                        <h3>Protein Family Option</h3>
                        <?php echo get_fraction_html("opte")[0]; ?>
                    </div>
                    <div>
                        <?php echo add_dev_site_option("opte", $db_modules, get_advanced_seq_html("opte"))[0]; ?>
                    </div>
                </div>
    
                <?php echo add_submit_html("opte", "optEoutputIds", $user_email)[0]; ?>
            </form>
        </div>
<?php
}


////////////////////////////////////////////////////////////////////////////////////////////////////
// UTILITY TAB

function output_utility($use_advanced_options, $user_email, $example_fn = false, $mode_data = array(), $selected_tab = "") {
?>
        <div id="utilitytab" class="ui-tabs-panel ui-widget-content">
<?php
    output_tab_page_start(false, "utility-tabs");
    output_utility_tab_page_header($selected_tab);
    output_colorssn($use_advanced_options, $user_email, $example_fn, $mode_data);
    output_cluster($use_advanced_options, $user_email, $example_fn, $mode_data);
    output_nc($use_advanced_options, $user_email, $example_fn, $mode_data);
    output_cr($use_advanced_options, $user_email, $example_fn, $mode_data);
    output_tab_page_end();
?>
        </div>
<?php
}

function output_utility_tab_page_header($selected_tab = "", $class_fn = false, $url_fn = false) {
    $ul_class = $class_fn !== false ? $class_fn("ul") : "ui-tabs-nav ui-widget-header";
    $active_class = $class_fn !== false ? $class_fn("active") : "ui-tabs-active";
    if ($url_fn === false) {
        $url_fn = function($id) {
            return "#$id";
        };
    }
?>
    <ul class="<?php echo $ul_class; ?>">
        <li <?php echo ($selected_tab == "colorssn" ? "class=\"$active_class\"" : ""); ?>><a href="<?php echo $url_fn("colorssntab"); ?>"> Color SSNs</a></li>
        <li <?php echo ($selected_tab == "cluster" ? "class=\"$active_class\"" : ""); ?>><a href="<?php echo $url_fn("clustertab"); ?>">Cluster Analysis</a></li>
        <li <?php echo ($selected_tab == "nc" ? "class=\"$active_class\"" : ""); ?>><a href="<?php echo $url_fn("nctab"); ?>">Neighborhood Connectivity</a></li>
        <li <?php echo ($selected_tab == "cr" ? "class=\"$active_class\"" : ""); ?>><a href="<?php echo $url_fn("crtab"); ?>">Convergence Ratio</a></li>
    </ul>
<?php
}

// END UTILITY TAB
////////////////////////////////////////////////////////////////////////////////////////////////////


function output_tab_page_header($show_jobs_tab, $show_tutorial, $selected_tab = "", $class_fn = false, $url_fn = false) {
    $ul_class = $class_fn !== false ? $class_fn("ul") : "ui-tabs-nav ui-widget-header";
    $active_class = $class_fn !== false ? $class_fn("active") : "ui-tabs-active";
    if ($url_fn === false) {
        $url_fn = function($id) {
            return "#$id";
        };
    }
?>
    <ul class="<?php echo $ul_class; ?>">
<?php if ($show_jobs_tab) { ?>
        <li <?php echo (!$selected_tab              ? "class=\"$active_class\"" : ""); ?>"><a href="#jobs">Previous Jobs</a></li>
<?php } ?>
        <li <?php echo ($selected_tab == "option_a" ? "class=\"$active_class\"" : ""); ?>><a href="<?php echo $url_fn("optionAtab"); ?>" title="Option A">Sequence BLAST</a></li>
        <li <?php echo ($selected_tab == "option_b" ? "class=\"$active_class\"" : ""); ?>><a href="<?php echo $url_fn("optionBtab"); ?>" title="Option B">Families</a></li> <!-- Pfam and/or InterPro families</a></li>-->
        <li <?php echo ($selected_tab == "option_c" ? "class=\"$active_class\"" : ""); ?>><a href="<?php echo $url_fn("optionCtab"); ?>" title="Option C">FASTA</a></li>
        <li <?php echo ($selected_tab == "option_d" ? "class=\"$active_class\"" : ""); ?>><a href="<?php echo $url_fn("optionDtab"); ?>" title="Option D">Accession IDs</a></li>
<?php if (est_settings::option_e_enabled()) { ?>
        <li><a href="<?php echo $url_fn("optionEtab"); ?>" title="Option E">OptE</a></li>
<?php } ?>
        <li <?php echo (($selected_tab == "colorssn" || $selected_tab == "cluster" || $selected_tab == "nc" || $selected_tab == "cr") ? "class=\"$active_class\"" : ""); ?>><a href="<?php echo $url_fn("utilitytab"); ?>"> SSN Utilities</a></li>
<?php if ($show_tutorial) { ?>
        <li <?php echo (($show_tutorial || $show_jobs_tab) ? "" : "class=\"$active_class\"") ?>><a href="#tutorial">Tutorial</a></li>
<?php } ?>
    </ul>
<?php
}

function output_tab_page_start($class_fn = false, $id = "main-tabs") {
    $tab_class = $class_fn !== false ? $class_fn("tab-container") : "tabs-efihdr ui-tabs ui-widget-content";
    echo <<<HTML
<div class="$tab_class" id="$id"> <!-- style="display:none">-->
HTML;
}

function output_tab_page_end() {
    echo <<<HTML
</div> <!-- tabs -->
HTML;
}

function output_tab_page($db, $show_jobs_tab, $jobs, $tjobs, $use_advanced_options, $db_modules, $user_email, $show_tutorial, $example_fn = false, $show_all_ids = false) {

    $mode_data = check_for_color_mode($db);
    $sel_tab = !empty($mode_data) ?
        ($mode_data["mode"] == "cluster" ?
            "cluster" :
            ($mode_data["mode"] == "nc" ?
                "nc" :
                ($mode_data["mode"] == "cr" ? 
                    "cr" :
                    "colorssn"))) 
                    : "";

    output_tab_page_start();
    output_tab_page_header($show_jobs_tab, $show_tutorial, $sel_tab);
?>
    <div>
<?php if ($show_jobs_tab) { ?>
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
    echo est_ui::output_job_list($jobs, $show_archive, "sort-jobs-toggle", $example_fn, $show_all_ids);

    if (has_jobs($tjobs)) {
        echo "            <h4>Training Resources</h4>\n";
        echo est_ui::output_job_list($tjobs);
    }
?>
         </div>
<?php
    }

    output_option_a($use_advanced_options, $db_modules, $user_email, $example_fn);
    output_option_b($use_advanced_options, $db_modules, $user_email, $example_fn);
    output_option_c($use_advanced_options, $db_modules, $user_email, $example_fn);
    output_option_d($use_advanced_options, $db_modules, $user_email, $example_fn);
    if (est_settings::option_e_enabled())
        output_option_e($use_advanced_options, $db_modules, $user_email, $example_fn);
    output_utility($use_advanced_options, $user_email, $example_fn, $mode_data, $sel_tab);
    //output_colorssn($use_advanced_options, $user_email, $example_fn, $mode_data);
    //output_cluster($use_advanced_options, $user_email, $example_fn, $mode_data);
    if ($show_tutorial)
        output_tutorial($show_jobs_tab);
?>

    </div> <!-- tab-content -->
<?php
    output_tab_page_end();
}

function check_for_color_mode($db) {
    $est_id = "";
    $color_filename = "";

    $modes_avail = array("color" => 1, "cluster" => 1, "nc" => 1, "cr" => 1);
    $mode_data = array();

    if (isset($_GET["mode"])) {
        $mode = $_GET["mode"];
        if (isset($modes_avail[$mode]) && isset($_GET["est-id"]) && isset($_GET["est-key"]) && isset($_GET["est-ssn"])) {
            $the_aid = $_GET["est-id"];
            $the_key = $_GET["est-key"];
            $the_idx = $_GET["est-ssn"];
    
            $job_info = global_functions::verify_est_job($db, $the_aid, $the_key, $the_idx);
            if ($job_info !== false) {
                $est_file_info = global_functions::get_est_filename($job_info, $the_aid, $the_idx);
                if ($est_file_info !== false) {
                    $est_id = $job_info["generate_id"];
                    $est_key = $the_key;
                    $color_filename = $est_file_info["filename"];
                    $mode_data["filename"] = $color_filename;
                    $mode_data["ssn_id"] = $the_aid;
                    $mode_data["ssn_idx"] = $the_idx;
                    $mode_data["mode"] = $mode;
                }
            }
        } else if ($mode === "cr" && isset($_GET["est-id"]) && isset($_GET["est-key"])) {
            $job_info = colorssn_shared::get_source_color_ssn_info($db, $_GET["est-id"], $_GET["est-key"]);
            if ($job_info !== false) {
                $mode_data["filename"] = $job_info["filename"];
                $mode_data["ssn_id"] = $_GET["est-id"];
                $mode_data["mode"] = $mode;
            }
        }
    }

    return $mode_data;
}


?>
