<?php 
require_once(__DIR__."/../../init.php");

use \efi\ui;
use \efi\table_builder;
use \efi\global_settings;
use \efi\user_auth;

use \efi\est\stepa;
use \efi\est\analysis;
use \efi\est\dataset_shared;
use \efi\est\plots;
use \efi\est\functions;
use \efi\est\settings;
use \efi\training\example_config;
use \efi\sanitize;


$gen_id = sanitize::validate_id("id", sanitize::GET);
$key = sanitize::validate_key("key", sanitize::GET);

if ($gen_id === false || $key === false) {
    error500("Unable to find the requested job.");
}

$is_example = example_config::is_example();

$generate = new stepa($db, $gen_id, $is_example);

if ($generate->get_key() != $key) {
    error500("Unable to find the requested job.");
}

if (!$is_example && $generate->is_expired()) {
    require_once(__DIR__."/inc/header.inc.php");
    echo "<p class='center'><br>Your job results are only retained for a period of " . global_settings::get_retention_days(). " days";
    echo "<br>Your job was completed on " . $generate->get_time_completed();
    echo "<br>Please go back to the <a href='" . functions::get_server_name() . "'>homepage</a></p>";
    require_once(__DIR__."/inc/footer.inc.php");
    exit;
}




$web_address = dirname($_SERVER['PHP_SELF']);


$use_advanced_options = global_settings::advanced_options_enabled();

$gen_type = $generate->get_type();
$generate = dataset_shared::create_generate_object($gen_type, $db, $is_example);

$uniref = dataset_shared::get_uniref_version($gen_type, $generate);

$sunburst_app_primary_id_type = "UniProt";
if ($gen_type == "FAMILIES" || $gen_type == "ACCESSION") {
    $sunburst_app_uniref = 50;
    $has_uniref = "true";
} else {
    $sunburst_app_uniref = 0;
    $has_uniref = "false";
    if ($gen_type == "BLAST") {
        $sunburst_app_primary_id_type = $generate->get_blast_db_type();
    }
}

$job_name = $generate->get_job_name();
$use_domain = dataset_shared::get_domain($gen_type, $generate) == "on";
$ex_param = $is_example ? "&x=".$is_example : "";


$table_format = "html";
if (isset($_GET["as-table"]))
    $table_format = "tab";
$table = new table_builder($table_format);
dataset_shared::add_generate_summary_table($generate, $table, false, $is_example);
$table_string = $table->as_string();

if (isset($_GET["as-table"])) {
    $file_job_name = $gen_id . "_" . $gen_type;
    $table_filename = functions::safe_filename($file_job_name) . "_summary.txt";

    dataset_shared::send_table($table_filename, $table_string);
    exit(0);
}


$has_tax_data = $generate->has_tax_data();
$is_taxonomy = ($gen_type == "TAXONOMY") && $has_tax_data;
$show_taxonomy = true && $has_tax_data;

$IncludeSunburstJs = $show_taxonomy;
$IncludePlotlyJs = true;
$IncludeTaxonomyJs = true;
require_once(__DIR__."/inc/header.inc.php");

$tax_tab_text = $show_taxonomy ? get_tax_tab_text($gen_type) : "";
$tax_filt_text = $show_taxonomy ? get_tax_filt_text($gen_type) : "";
$sunburst_post_sunburst_text = $show_taxonomy ? get_tax_sb_text($gen_type, $sunburst_app_primary_id_type) : "";;

$date_completed = $generate->get_time_completed_formatted();

$post_url = "stepc_do.php";




$ssn_jobs = $generate->get_analysis_jobs();

// Still use old EST graphing code, but this is the new length histogram graphs.
$use_v2_graphs = $generate->get_graphs_version() >= 2;


$post_evalue = sanitize::post_sanitize_num("evalue");
$post_pid = sanitize::post_sanitize_num("pid");
$post_bitscore = sanitize::post_sanitize_num("bitscore");
$post_min = sanitize::post_sanitize_num("minimum");
$post_max = sanitize::post_sanitize_num("maximum");


?>	



<h2>Dataset Completed</h2>

<?php if ($job_name) { ?>
<h4 class="job-display">Submission Name: <b><?php echo $job_name; ?></b></h4>
<?php } ?>
<?php if (!$is_taxonomy) { ?>
<p>
A minimum sequence similarity threshold that specifies the sequence pairs 
connected by edges is needed to generate the SSN. This threshold also 
determines the segregation of proteins into clusters. The threshold is applied 
to the edges in the SSN using the alignment score, an edge node attribute that 
is a measure of the similarity between sequence pairs.
</p>
<?php } ?>

<div style="margin:20px;color:red"><?php if (isset($result['MESSAGE'])) { echo $result['MESSAGE']; } ?></div>

<div class="tabs-efihdr tabs">
    <ul class="">
        <li class="ui-tabs-active"><a href="#info">Dataset Summary</a></li>
        <?php if ($show_taxonomy) { ?>
            <li data-tab-id="sunburst"><a href="#taxonomy-tab">Taxonomy Sunburst</a></li>
        <?php } ?>
        <?php if (!$is_taxonomy) { ?>
            <li><a href="#graphs">Dataset Analysis</a></li>
            <?php if (!$is_example) { ?>
                <li><a href="#final">SSN Finalization</a></li>
            <?php } ?>
            <?php if (count($ssn_jobs) > 0) { ?>
                <li><a href="#jobs">SSNs Created From this Dataset</a></li>
            <?php } ?>
        <?php } ?>
    </ul>
    <div>
        <!-- JOB INFORMATION -->
        <div id="info">
            <p>
            The parameters for generating the initial dataset are summarized in the table. 
            </p>

            <table width="100%" class="pretty no-stretch">
                <?php echo $table_string; ?>
            </table>
            <div style="float: right"><a href='<?php echo $_SERVER['PHP_SELF'] . "?id=$gen_id&key=$key&as-table=1$ex_param" ?>'><button class="normal">Download Information</button></a></div>
            <div style="clear: both"></div>
        </div>


        <?php if ($generate->is_cd_hit_job()) { ?>

        <div id="cdhit">
            <h4>CD-HIT Counts</h4>
            <table class="pretty">
            <thead><th>Sequence % ID</th><th>Sequence Length</th><th>Number of Nodes</th></thead>
            <tbody>
            <?php
                    $cdhit_stats = $generate->get_cdhit_stats();
                    for ($i = 0; $i < count($cdhit_stats); $i++) {
                        echo "<tr><td>";
                        echo join("</td><td>", array($cdhit_stats[$i]['SequenceId'], $cdhit_stats[$i]['SequenceLength'], $cdhit_stats[$i]['Nodes']));
                        echo "</td></tr>\n";
                    }
            ?>
            </tbody>
            </table>
        </div>

        <?php } ?>

    <?php if ($show_taxonomy) { ?>
        <div id="taxonomy-tab">
            <div><?php echo $tax_tab_text; ?></div>
            <div id="taxonomy" style="position: relative">
<?php
                    //include(__DIR__."/../taxonomy/inc/shared.inc.php");
                    //add_sunburst_download_warning();
                    //add_sunburst_container();
                    //add_sunburst_download_dialogs();
?>
            </div>
        </div>
    <?php } ?>

    <?php if (!$is_taxonomy) { ?>

        <!-- GRAPHS -->
        <div id="graphs">
<p>
This tab provides histograms and box plots with statistics about the sequences 
in the input dataset as well as the BLAST all-by-all pairwise comparisons that 
were computed. 
</p>
<p>
The descriptions for the histograms and plots guide the choice of the values 
for the "Alignment Score Threshold" and the Minimum and Maximum "Sequence 
Length Restrictions" that are applied to the sequences and edges to generate 
the SSN.

<?php if (!$is_example) { ?>
These values are entered using the "SSN Finalization" tab on this page.
<?php } ?>
</p>

            <div class="option-panels stepc-graphs">
                <div class="initial-open">
<?php
    $length_text = $use_domain ? "Domain" : "Full";
?>
                    <h3>Sequences as a Function of <?php echo $length_text; ?> Length Histogram (First Step for Alignment Score Threshold Selection)</h3>
                    <div>
<?php
    $plot_type = $use_domain ? "uniprot_domain" : "uniprot";
    echo make_histo_plot_download($generate, "Number of Sequences at Each Length", $plot_type);
?>
                        <div>
                            <p>
<?php if ($use_domain) { ?>
<?php     if ($uniref) { ?>
<p>This histogram describes the length distribution for all trimmed domains 
(from all UniProt IDs) in the input dataset.  </p>
<p>Inspection of the histogram permits identification of fragments and 
full-length domains.  This histogram is used to select Minimum and Maximum 
"Sequence Length Restrictions" in the "SSN Finalization" tab to remove 
fragments and select desired domain lengths in the input UniRef dataset.  The 
sequences in the "Sequences as a Function of Domain-Length Histogram (UniRef90 
Cluster IDs)" (last plot) are used to calculate the edges.</p>
<?php     } else { ?>
<p>This histogram describes the length distribution for all of the trimmed 
domains (from all UniProt IDs) in the input dataset; the sequences in this 
histogram are used to calculate the edges. </p>
<p>Inspection of the histogram permits identification of fragments and 
full-length domains.  The domain dataset for the BLAST can be length-filtered 
using the Minimum and Maximum "Sequence Length Restrictions" in the "SSN 
Finalization" tab to select desired domain lengths. </p>
<?php     } ?>
<?php } else { ?>
<?php     if ($uniref) { ?>
<p>This histogram describes the length distribution for all sequences (UniProt 
IDs) in the input dataset.  </p>
<p>Inspection of the histogram permits identification of fragments, single 
domain proteins, and multidomain fusion proteins. This histogram is used to 
select Minimum and Maximum "Sequence Length Restrictions" in the "SSN 
Finalization" tab to remove fragments, select only single domain proteins, or 
select multidomain proteins.  The sequences in the "Sequences as a Function of 
Full-Length Histogram (UniRef90 Cluster IDs)" (last histogram) are used to 
calculate the edges.</p>
<?php     } else { ?>
<p>This histogram describes the length distribution for all sequences (UniProt 
IDs) in the input dataset; the sequences in this histogram are used to 
calculate the edges.  </p>
<p>Inspection of the histogram permits identification of fragments, single 
domain proteins, and multidomain fusion proteins. The dataset can be 
length-filtered using the Minimum and Maximum "Sequence Length Restrictions" in 
the "SSN Finalization" tab to remove fragments, select single domain proteins, 
or select multidomain fusion proteins. </p>
<?php     } ?>
<?php } ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="initial-open">
                    <h3>Alignment Length vs Alignment Score Box Plot (Second Step for Alignment Score Threshold Selection)</h3>
                    <div>
            <?php echo make_plot_download($generate, "Alignment Length vs Alignment Score", "alignment"); ?>
                        <div>
<?php if ($use_domain) { ?>
<p>This box plot describes the relationship between the query-subject alignment 
lengths (for the trimmed domains) used by BLAST (y-axis) to calculate the 
alignment scores (x-axis). </p>
<p>The value of the "Alignment Score Threshold" for generating the SSN (entered 
in the "SSN Finalization" tab) should be selected (from the "Percent Identity 
vs Alignment Score Box Plot"; next box plot) at an "Alignment Length" &ge; the 
minimum length of full-length domains in the input dataset (determined by 
inspection of the "Sequences as a Function of Length Histogram"; first 
histogram).  In that region, the "Alignment Length" should independent of the 
"Alignment Score" in this box plot. </p>
<?php } else { ?>
<p>This box plot describes the relationship between the query-subject alignment 
lengths used by BLAST (y-axis) to calculate the alignment scores (x-axis). </p>
<p>This plot shows a monophasic increase in alignment length to a constant 
value for single domain proteins; this plot shows multiphasic increases in 
alignment length for datasets with multidomain proteins (one phase for each 
fusion length).  The value of the "Alignment Score Threshold" for generating 
the SSN (entered in the "SSN Finalization" tab) should be selected (from the 
"Percent Identity vs Alignment Score Box Plot"; next box plot) at an alignment 
length &ge; the minimum length of single domain proteins in the dataset 
(determined by inspection of the "Sequences as a Function of Full-Length 
Histogram"; previous histogram). In that region, the 
"Alignment Length" should be independent of the "Alignment Score".</p>
<?php } ?>
                        </div>
                    </div>
                </div>
                <div class="initial-open">
                    <h3>Percent Identity vs Alignment Score Box Plot (Third Step for Alignment Score Threshold Selection)</h3>
                    <div>
            <?php echo make_plot_download($generate, "Percent Identity vs Alignment Score", "identity"); ?>
                        <div>
<?php if ($use_domain) { ?>
<p>This box plot describes the pairwise percent sequence identity as a function 
of alignment score. </p>
<p>Complementing the "Alignment Length vs Alignment Score Box Plot" (previous 
box plot), this plot describes a monophasic increase in sequence identity with 
full-length domains.  Referring to the " Alignment Length vs Alignment Score 
Box Plot" (previous box plot), the monophasic increase in sequence identity 
occurs as the alignment score increases at a constant alignment length.</p>
<p>For the initial SSN, we recommend that an alignment score corresponding to 
35 to 40% pairwise identity be entered in the "SSN Finalization" tab. </p>
<?php } else { ?>
<p>This box plot describes the pairwise percent sequence identity as a function 
of alignment score. </p>
<p>Complementing the "Alignment Length vs Alignment Score Box Plot" (previous 
box plot), this box plot describes a monophasic increase in sequence identity 
for single domain proteins or a multiphasic increase in sequence identity for 
datasets with multidomain proteins (one phase for each fusion length). In the 
"Alignment Length vs Alignment Score" box plot (previous box plot), a 
monophasic increase in sequence identity occurs as the alignment score 
increases at a constant alignment length; multiphasic increases occur as the alignment 
score increases at additional longer constant alignment lengths. </p>
<p>For the initial SSN, we recommend that an alignment score corresponding to 
35 to 40% pairwise identity be entered in the "SSN Finalization" tab (for the 
first phase in multiphasic plots). </p>
<?php } ?>
                        </div>
                    </div>
                </div>
                <div>
                    <h3>Edge Count vs Alignment Score Plot (Preview of Full SSN Size)</h3>
                    <div>
            <?php make_interactive_plot($generate, "Edge Count vs Alignment Score Plot", "edge-evalue-plot", "edge_evalue"); ?>
                        <div>
<p>This plot shows the number of edges in the full SSN for the input dataset (a 
node of each sequence) as a function of alignment score. By moving the cursor 
over the plot, the number of edges for each alignment score is displayed. </p>
<p>This plot helps determine if the full SSN generated using the initial 
alignment score can be opened with Cytoscape on the user’s computer. As a rough guide, 
SSNs with ~2M edges can be opened with 16GB RAM, ~4M edges with 32GB RAM, ~8M 
edges with 64GB RAM, ~15M edges with 128GB RAM, and ~30M edges with 256GB RAM. 
</p>
<p>If the number of edges for the full SSN is too large to be opened, a 
representative node (rep node) SSN can be opened. In a rep node SSN, sequences 
are grouped into metanodes based on pairwise sequence identity (from 40 to 100% 
identity, in 5% intervals). The download tables on the "Download Network Files" 
page provide the numbers of metanodes and edges in rep node SSNs. The rep node 
SSNs are lower resolution than full SSNs; clusters of interest in rep node SSNs 
can be expanded to provide the full SSNs. </p>
                        </div>
                    </div>
                </div>
                <div>
                    <h3>Edges as a Function of Alignment Score Histogram (Preview of SSN Diversity)</h3>
                    <div>
            <?php echo make_plot_download($generate, "Number of Edges at Alignment Score", "edges"); ?>
                        <div>
<p>This histogram describes the number of edges calculated at each alignment 
score. This plot is not used to select the alignment score for the initial SSN; 
however, it provides an overview of the functional diversity within the input 
dataset. </p>
<p>In the histogram, edges with low alignment scores typically are those 
between isofunctional clusters; edges with large alignment scores typically are 
those connecting nodes within isofunctional clusters. </p>
<p>The histogram for a dataset with a single isofunctional SSN cluster is 
single distribution centered at a "large" alignment score; the histogram for a 
dataset with many isofunctional SSN clusters will be dominated by the edges 
that connect the clusters, with the number of edges decreasing as the alignment 
score increases. </p>
                        </div>
                    </div>
                </div>
<?php if ($use_v2_graphs) { ?>
<?php if ($use_domain) { ?>
                <div>
                    <h3>Sequences as a Function of Full Length Histogram (UniProt IDs)</h3>
                    <div>
<?php 
        $plot_type = "uniprot";
        echo make_histo_plot_download($generate, "Number of Sequences at Each Full Length (UniProt IDs)", $plot_type);
?>
                        <div>
<p>This histogram describes the length distribution of tall sequences (UniProt 
IDs) in the input dataset.  Inspection of this histogram permits identification 
of fragments and the lengths of both single domain and multidomain fusion 
proteins in the input dataset before domain trimming. </p>
                        </div>
                    </div>
                </div>
<?php } ?>
<?php if ($uniref) { ?>
                <div>
                    <h3>Sequences as a Function of Full Length Histogram (UniRef<?php echo $uniref; ?> Cluster IDs)</h3>
                    <div>
<?php 
        $plot_type = "uniref";
        echo make_histo_plot_download($generate, "Number of Sequences at Each Full Length (UniRef Cluster IDs)", $plot_type);
?>
                        <div>
<p>This histogram describes the distribution of the full-length UniRef cluster 
IDs in the input dataset. The sequences of the cluster IDs displayed do not 
accurately reflect the distribution of fragments, single domain proteins, and 
multidomain full-length proteins in the input dataset. </p>
                        </div>
                    </div>
                </div>
<?php } ?>
<?php if ($uniref && $use_domain) { ?>
                <div>
                    <h3>Sequences as a Function of Domain Length Histogram (UniRef<?php echo $uniref; ?> Cluster IDs)</h3>
                    <div>
<?php 
        $plot_type = "uniref_domain";
        echo make_histo_plot_download($generate, "Number of Sequences at Each Domain Length (UniRef Cluster IDs)", $plot_type);
?>
                        <div>
<p>This histogram describes the domain length distribution of the UniRef 
cluster IDs in the input dataset; the sequences in this histogram are used to 
calculate the edges. </p>
<p>The domains of the cluster IDs displayed do not accurately reflect the 
distribution of domains in the input dataset (diverse sequences and lengths are 
over-represented).  Therefore, this histogram is not used to determine the 
lengths of full-length domains in the input dataset; these are determined from 
the "Sequences as a Function of Domain Length Histogram for UniProt IDs" (first 
histogram).</p>
                        </div>
                    </div>
                </div>
<?php } ?>
<?php } ?>
<?php if ($generate->alignment_plot_new_exists()) { ?>
                <div>
                    <h3>Alignment Length vs Alignment Score (dynamic)</h3>
                    <div>
            <?php echo make_plot_download($generate, "Alignment Length vs Alignment Score", "alignment"); ?>
                    <iframe src="<?php echo $generate->get_alignment_length_plot_new_html(1); ?>"></iframe>
                    </div>
                </div>
<?php } ?>
<?php if ($generate->percent_identity_plot_new_exists()) { ?>
                <div>
                    <h3>Percent Identity vs Alignment Score (dynamic)</h3>
                    <div>
            <?php echo make_plot_download($generate, "Percent Identity vs Alignment Score", "identity"); ?>
                    <iframe src="<?php echo $generate->get_percent_identity_plot_new_html(1); ?>" border="0" width="100%" height="500"></iframe>
                    </div>
                </div>
<?php } ?>
            </div>

<?php if (!$is_example) { ?>
            <p>
            Enter chosen <b>Sequence Length Restriction</b> and <b>Alignment Score Threshold</b> in the <a href="#" id="final-link">SSN Finalization tab</a>.
            </p>
<?php } ?>
        </div>
    <?php } /* end of graphs div */ ?>


        <?php if (!$is_example && !$is_taxonomy) { ?>
        <!-- FINALIZATION -->
        <div id="final">
            <form name="define_length" method="post" class="align_left" enctype="multipart/form-data" id="a-form">
            <input type="hidden" name="key" value="<?php echo $key; ?>" />
            <input type="hidden" name="id" value="<?php echo $gen_id; ?>" />

            <?php if ($use_advanced_options) { ?>
            <div class="tabs-efihdr tabs">
                <ul class="tab-headers">
                    <li class="active"><a href="#threshold-eval">Alignment Score Threshold</a></li>
                    <li><a href="#threshold-pid">Percent ID Threshold</a></li>
                    <li><a href="#threshold-bit">Bit Score Threshold</a></li>
                    <li><a href="#threshold-custom">Custom Clustering</a></li>
                </ul>

                <div class="tab-content" style="min-height: 220px">
            <?php } ?>
                    <div id="threshold-eval" class="tab active">
                        <p>
                        This tab is used to specify the minimum "Alignment Score Threshold" (that is a 
                        measure of the minimum sequence similarity threshold) for drawing the edges 
                        that connect the proteins (nodes)  in the SSN.
                        </p>

                        <p>
                            <span class="input-name">Alignment Score Threshold:</span>
                            <span class="input-field">
                                <input type="text" name="evalue" size="5" <?php if (isset($post_evalue)) { echo "value='$post_evalue'"; } ?>>
                                <a href="tutorial_analysis.php" class="question">?</a>
                            </span>
                            <div class="input-desc">
                                This value corresponds to the lower limit for which an edge will be present in the SSN.
                                The alignment score is similar in magnitude to the negative base-10 logarithm of a BLAST e-value.
                            </div>
                        </p>

                    </div>
            <?php if ($use_advanced_options) { ?>
                    <div id="threshold-pid" class="tab">
                        <p>Select a lower limit for the percent ID to use as a lower threshold for clustering in the SSN.</p>

                        <p><input type="text" name="pid" <?php if (isset($post_pid)) { echo "value='$post_pid'"; } ?>>
                        % ID
                        </p>

                        This score is the similarity threshold which determine the 
                        connection of proteins with each other. All pairs of proteins with a percent
                        ID below this number will not be connected. Sets of connected proteins will 
                        form clusters.
                    </div>
                    <div id="threshold-bit" class="tab">

                        <p>Select a lower limit for the bit score to use as a lower threshold for clustering in the SSN.</p>

                        <p><input type="text" name="bitscore" <?php if (isset($post_bitscore)) { echo "value='$post_bitscore'"; } ?>></p>

                        <p>
                        This score is the similarity threshold which determine the 
                        connection of proteins with each other. All pairs of proteins with a bit score
                        below this number will not be connected. Sets of connected proteins will 
                        form clusters.
                        </p>
                    </div>
                    <div id="threshold-custom" class="tab">

                        A file specifying which proteins are in what cluster can be uploaded.  The file must be givein in the format below.
                        Tabs or spaces can be used instead of the ',' comma separating character.
<pre>
Protein_ID_1,Cluster#
Protein_ID_2,Cluster#
Protein_ID_3,Cluster#
...
</pre>
                        <div class="primary-input">
                            <?php echo ui::make_upload_box("Custom cluster file (text)", "cluster_file", "progress-bar-cluster", "progress-num-cluster"); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
            <input type="hidden" name="filter" id="filter-type" value="eval" />

            <div class="option-panels">
                <div class="initial-open">
                    <h3>Sequence Length Restriction Options</h3>
                    <div>
                        <p>
                            Allows restriction of sequences in the generated SSN based on their length.
                            <a href="tutorial_analysis.php" class="question">?</a>
                        </p>

                        <p>
                            <div>
                                <span class="input-name">Minimum:</span>
                                <span class="input-field">
                                    <input type="text" name="minimum" size="7" maxlength='20' <?php if (isset($post_min)) { echo "value='$post_min'"; } ?>> (default: <?php echo __MINIMUM__; ?>)
                                </span>
                            </div>
                            <div>
                                <span class="input-name">Maximum:</span>
                                <span class="input-field">
                                    <input type="text" name="maximum" size="7" maxlength='20' <?php if (isset($post_max)) { echo "value='$post_max'"; } ?>> (default: <?php echo __MAXIMUM__; ?>)
                                </span>
                            </div>
                        </p>
                    </div>
                </div>
                <div class="">
                    <h3>Filter by Taxonomy</h3>
                    <div class="initial-open">

<div>
<?php echo $tax_filt_text; ?>
</div>

                        <div>
                            Preselected conditions:
                            <select id="taxonomy-stepc-select" class="taxonomy-preselects" data-option-id="stepc">
                                <option disabled selected value>-- select a preset to auto populate --</option>
                            </select>
                        </div>
                        <div style="display: none">
                            <input id="taxonomy-stepc-preset-name" type="hidden" name="taxonomy-preset-name" id="taxonomy-preset-name" value="" />
                        </div>
                        <div id="taxonomy-stepc-container"></div>
                        <div>
                            <button id="taxonomy-stepc-add-btn" data-option-id="stepc" type="button" class="light add-tax-btn">Add taxonomic condition</button>
                        </div>
                    </div>
                </div>
                <div class="">
                    <h3>Neighborhood Connectivity</h3>
                    <div>
                        <div>
                            <span class="input-name">Neighborhood Connectivity:</span>
                            <span class="input-field">
                                <label><input type="checkbox" name="compute_nc" id="compute_nc" /> Check to compute Neighborhood Connectivity (number of edges to other nodes) [default: off].</label>
                            </span>
                            <div class="input-desc">
                                <p>
                                The nodes for unresolved families can be difficult to identify in SSNs 
                                generated with low alignment scores.  Coloring the nodes according to the 
                                number of edges to other nodes (<b>Neighborhood Connectivity</b>, NC) helps identify 
                                families with highly connected nodes 
                                (<a href="https://doi.org/10.1016/j.heliyon.2020.e05867">https://doi.org/10.1016/j.heliyon.2020.e05867</a>).
                                Using <b>Neighborhood Connectivity Coloring</b> as a guide, the alignment score threshold 
                                can be chosen in Cytoscape to separate the SSN into families.
                                </p>
                            </div>
                        </div>
                        </p>
                    </div>
                </div>
                <div class="">
                    <h3>Fragment Option</h3>
                    <div>
                        <div>
UniProt designates a Sequence Status for each member: Complete if the encoding DNA sequence has both start and stop codons; Fragment if the start and/or stop codon is missing.   Approximately 10% of the entries in UniProt are fragments. 
                        </div>
                        <span class="input-name">
                            Fragments:
                        </span><span class="input-field">
                            <label><input type="checkbox" id="remove_fragments" name="remove_fragments" />
                                Check to exclude UniProt-defined fragments in the results.
                                (default: off)
                            </label>
                        </span>
                        <div class="input-desc">
<p>
For the UniRef90 and UniRef50 databases, clusters are excluded if the cluster 
ID ("representative sequence") is a fragment.   
</p>

<p>
UniProt IDs in UniRef90 and UniRef50 clusters with complete cluster IDs are 
removed from the clusters if they are fragments. 
</p>


                        </div>
                    </div>
                </div>
                <?php if ($use_advanced_options) { ?>
                <div>
                    <h3>Dev Site Options</h3>
                    <div>
                        <div><label><input type="checkbox" name="build_repnode" id="build_repnode" /> Make repnode networks</label></div>
                        <div><label><input type="checkbox" name="min_edge_attr" id="min_edge_attr" /> Use minimum number of edge attributes</label></div>
                        <div><label><input type="checkbox" name="min_node_attr" id="min_node_attr" /> Use minimum number of node attributes</label></div>
                    </div>
                </div>
                <?php } ?>
            </div>
            <div>
                <p>
                <span class="input-name">Network name</span>:
                <input type="text" name="network_name" class="email" value="<?php echo $job_name; ?>" />
                This name will be displayed in Cytoscape.
                </p>
            </div>

            <p>
            You will be notified by e-mail when the SSN is ready for download.
            </p>

            <center>
                <div id="submit-error" style="color: red; font-size: 1.2em" class="error_message"></div>
                <div><button type="button" class="dark" id="submit-button">Create SSN</button></div>
            </center>

            </form>
        </div>
        <?php } ?>


        <?php if (count($ssn_jobs) > 0) { ?>
        <div id="jobs">
            <p>
            A list of the SSNs generated from this initial dataset.
            </p>

            <ul>

            <?php
                    foreach ($ssn_jobs as $job_id => $job_info) {
                        $ssn_name = $job_info["name"];
                        $ssn_ascore = $job_info["ascore"];
                        $ssn_min = $job_info["min_len"];
                        $ssn_max = $job_info["max_len"];
                        $min_text = $max_text = "";
                        if ($ssn_min)
                            $min_text = "Min=$ssn_min";
                        if ($ssn_max)
                            $max_text = "Max=$ssn_max";
                        $status = $job_info["status"];

                        if ($status != __FINISH__)
                            continue;

                        echo "<li>";
                        if ($status == __FINISH__)
                            echo "<a href=\"stepe.php?id=$gen_id&key=$key&analysis_id=$job_id$ex_param\">";
                        echo "$ssn_name AS=$ssn_ascore $min_text $max_text (Analysis ID=$job_id)";
                        if ($status == __FINISH__)
                            echo "</a>";
                        else
                            echo " ($status)";
                        echo "</li>\n";
                    }
            ?>
            </ul>
        </div>
        <?php } ?>
    </div>
</div>


<div style="margin-top:85px"></div>

<center>Portions of these data are derived from the Universal Protein Resource (UniProt) databases.</center>

<script src="<?php echo $SiteUrlPrefix; ?>/js/panel-toggle.js" type="text/javascript"></script>
<script>
    $(document).ready(function() {
        var fileSizeIframe = $("#file-size-iframe");
        var edgeIframe = $("#edge-evalue-iframe");

        $('#file-size-button').click(function() {
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
            fileSizeIframe.attr('src', function() {
                return $(this).data('src');
            });
        });

        $('#edge-evalue-button').click(function() {
            edgeIframe.attr('src', function() {
                return $(this).data('src');
            });
        });

        fileSizeIframe.each(function() {
            var src = $(this).attr('src');
            $(this).data('src', src).attr('src', '');
        });
        edgeIframe.each(function() {
            var src = $(this).attr('src');
            $(this).data('src', src).attr('src', '');
        });

        $(".tabs").tabs({
<?php if ($show_taxonomy) { ?>
            activate: sunburstTabActivate // comes from shared/js/sunburst.jS
<?php } ?>
        });

        $(".option-panels > div").accordion({
                heightStyle: "content",
                    collapsible: true,
                    active: false,
        });
        $(".initial-open").accordion("option", {active: 0});

        $("#final-link").click(function() {
            $(".tabs").tabs({active: 2});
        });
    });

    var acc = document.getElementsByClassName("panel-toggle");
    for (var i = 0; i < acc.length; i++) {
        acc[i].onclick = function() {
            this.classList.toggle("active");
            var panel = this.nextElementSibling;
            if (panel.style.maxHeight){
                panel.style.maxHeight = null;
            } else {
                panel.style.maxHeight = panel.scrollHeight + "px";
            } 
        }
    }
</script>




<?php if (functions::custom_clustering_enabled()) { ?>
<script>
    $(document).ready(function() {
        $(".tabs-efihdr .tab-headers a").on("click", function(e) {
            var curAttrValue = $(this).attr("href");
            var filterType = curAttrValue.substr(11);
            $("#filter-type").val(filterType);
        });
    }).tooltip();
</script>
<script src="<?php echo $SiteUrlPrefix; ?>/js/custom-file-input.js" type="text/javascript"></script>
<?php } ?>




<?php if ($show_taxonomy) { ?>
<script>
    $(document).ready(function() {
        var hasUniref = <?php echo $has_uniref; ?>;

        var estPath = "<?php echo global_settings::get_web_path('est'); ?>/index.php";
        var gntPath = "<?php echo global_settings::get_web_path('gnt'); ?>/index.php";
        var jobId = "<?php echo $gen_id; ?>";
        var jobKey = "<?php echo $key; ?>";
        var onComplete = makeOnSunburstCompleteFn(estPath, gntPath, jobId, jobKey);
        var isExample = <?php echo $is_example ? "\"$is_example\"" : "\"\""; ?>;

        var apiExtra = [];
        if (isExample)
            apiExtra.push(["x", isExample]);

        var sunburstTextFn = function() {
            return $('<div><?php echo $sunburst_post_sunburst_text; ?></div>');
        };

        var scriptAppDir = "<?php echo $SiteUrlPrefix; ?>/vendor/efiillinois/sunburst/php";
        var sbParams = {
                apiId: "<?php echo $gen_id; ?>",
                apiKey: "<?php echo $key; ?>",
                apiExtra: apiExtra,
                appUniRefVersion: <?php echo $sunburst_app_uniref; ?>,
                appPrimaryIdTypeText: '<?php echo $sunburst_app_primary_id_type; ?>',
                appPostSunburstTextFn: sunburstTextFn,
                scriptApp: scriptAppDir + "/get_tax_data.php",
                fastaApp: scriptAppDir + "/get_sunburst_fasta.php",
                hasUniRef: hasUniref
        };
        var sunburstApp = new AppSunburst(sbParams);
        sunburstApp.attachToContainer("taxonomy");
        sunburstApp.addSunburstFeatureAsync(onComplete);
    });
</script>
<?php } ?>




<script>
    $(document).ready(function() {
        var warningMsgId = "submit-error";
        var taxSearchApp = "<?php echo $SiteUrlPrefix; ?>/vendor/efiillinois/taxonomy/php/get_tax_typeahead.php";
        var taxContainerFn = function(opt) { return "#taxonomy-stepc-container"; };
        var taxonomyApp = new AppTF(taxContainerFn, taxSearchApp);

        setupTaxonomyUi(taxonomyApp);

        $("#submit-button").click(function(e) {
            var fd = new FormData($("#a-form")[0]);

            var taxPresetName = $("#taxonomy-preset-name").val();
            if (taxPresetName) {
                fd.append("tax_name", taxPresetName);
            }
            var taxGroups = taxonomyApp.getTaxSearchConditions("");
            taxGroups.forEach((group) => fd.append("tax_search[]", group));

            var fileHandler = function(xhr) {};
            var completionHandler = function(jsonObj) {
                var nextStepScript = "stepd.php";
                window.location.href = nextStepScript + "?id=" + jsonObj.id;
            };

            doFormPost("<?php echo $post_url; ?>", fd, warningMsgId, fileHandler, completionHandler);
        });
    });
</script>

<?php

require_once(__DIR__."/inc/footer.inc.php");








function make_plot_download($gen, $hdr, $type) {
    global $is_example, $gen_id, $key;
    $html = ""; //"<span class='plot-header'>$hdr</span> \n";
    $ex_param = $is_example ? "&x=".$is_example : "";
    $info = $gen->get_graph_info($type);
    if ($info === false) {
        $html .= "Invalid data";
        return $html;
    }
    $url = "graphs.php?id=$gen_id&key=$key&$ex_param&type=stepc&graph=$type";
    $html .= <<<HTML
<div>
    <center>
        <img src="$url&small=1" width="800" />
HTML;
        
        $html .= "<a href='$url'><button class='file_download'>Download high resolution <img src='images/download.svg' /></button></a>";
        $html .= <<<HTML
    </center>
</div>
HTML;

    return $html;
}

function make_histo_plot_download($gen, $hdr, $plot_type) {
    return make_plot_download($gen, $hdr, "histogram_" . $plot_type);
}

function make_interactive_plot($gen, $hdr, $plot_div, $plot_id) {
    $plot = plots::get_plot($gen, $plot_id);
    if (!$plot || !$plot->has_data()) {
        echo "Invalid data";
        return;
    }

    $data = $plot->render_data(); // Javascript notation
    $plotly = $plot->render_plotly_config(); // Javascript code
    $trace_var = $plot->get_trace_var();
    $layout_var = $plot->get_layout_var();

    $html = <<<HTML
                <div>
                    <center>
                    <div id="$plot_div"></div>
                </center>
                    <script>
                        $data
                        $plotly
                        Plotly.newPlot("$plot_div", $trace_var, $layout_var);
                    </script>
                </div>
HTML;
    echo $html;
}

function get_tax_sb_text($gen_type, $blast_id_type = "") {
    $text = "";
    if ($gen_type == "BLAST") {
        $text = <<<TEXT
<p>
List of $blast_id_type IDs and 
FASTA-formatted sequences can be downloaded.
</p>

<p>
The list of IDs can be transferred to the Accession IDs option (Option D) of 
EFI-EST to generate an SSN. The Accession IDs option provides Filter by 
Taxonomy that should be used to remove internal UniProt IDs that are not 
members of the selected taxonomy category.
</p>

<p>
The list also can be transferred to the GND-Viewer to obtain GNDs.
</p>
TEXT;
    } else if ($gen_type == "FAMILIES") {
        $text = <<<TEXT
<p>
Lists of UniProt, UniRef90, and UniRef50 IDs and FASTA-formatted sequences can 
be downloaded.
</p>

<p>
The lists of UniProt, UniRef90, and UniRef50 IDs can be transferred to the 
Accession IDs option (Option D) of EFI-EST to generate an SSN. The Accession 
IDs option provides both Filter by Family and Filter by Taxonomy that should be 
used to remove internal UniProt IDs that are not members of the input families 
and selected taxonomy category.
</p>

<p>
The lists also can be transferred to the GND-Viewer to obtain GNDs.
</p>
TEXT;
    } else if ($gen_type == "FASTA" || $gen_type == "FASTA_ID") {
        $text = <<<TEXT
<p>
The UniProt IDs and FASTA-formatted sequences can be downloaded.
</p>

<p>
The list of UniProt IDs can be transferred to the Accession IDs option (Option 
D) of EFI-EST to generate an SSN. The Accession IDs option provides both Filter 
by Family and Filter by Taxonomy that should be used to remove UniProt IDs that 
are not members of desired families and the selected taxonomy category.
</p>
TEXT;
    } else if ($gen_type == "ACCESSION") {
        $text = <<<TEXT
<p>
Lists of UniProt, UniRef90, and UniRef50 IDs and FASTA-formatted sequences can 
be downloaded.
</p>

<p>
The lists of UniProt, UniRef90, and UniRef50 IDs can be transferred to the 
Accession IDs option (Option D) of EFI-EST to generate an SSN. The Accession 
IDs option provides both Filter by Family and Filter by Taxonomy that should be 
used to remove internal UniProt IDs that are not members of the input families 
and selected taxonomy category.
</p>

<p>
The lists also can be transferred to the GND-Viewer to obtain GNDs.
</p>
TEXT;
    } else {
        $text = "";
    }

    #$text = preg_replace('/<p>/', "##S#", $text);
    #$text = preg_replace('/<\/p>/', "##E#", $text);
    $text = preg_replace('/\n/', " ", $text);
    #$text = htmlspecialchars($text);

    return $text;
}

function get_tax_filt_text($gen_type) {
    if ($gen_type == "BLAST") {
        return <<<TEXT
<p>
From preselected conditions, the user can select "Bacteria, Archaea, Fungi", 
"Eukaryota, no Fungi", "Fungi", "Viruses", "Bacteria", "Eukaryota", or 
"Archaea" to restrict the SSN nodes the retrieved sequences to these taxonomy 
groups. 
</p>

<p>
"Bacteria, Archaea, Fungi", "Bacteria", "Archaea", and "Fungi" select organisms 
that may provide genome context (gene clusters/operons) useful for inferring 
functions. 
</p>

<p>
The SSN nodes also can be restricted to taxonomy categories within the 
Superkingdom, Kingdom, Phylum, Class, Order, Family, Genus, and Species ranks. 
Multiple conditions are combined to be a union of each other. 
</p>

<p>
The SSN nodes from the UniRef90 and UniRef50 databases are the UniRef90 and 
UniRef50 clusters for which the cluster ID ("representative sequence") matches 
the specified taxonomy categories. The UniProt members in these nodes that do 
not match the specified taxonomy categories are removed from the nodes. 
</p>
TEXT;

    } else if ($gen_type == "FAMILIES") {
        return <<<TEXT
<p>
From preselected conditions, the user can select "Bacteria, Archaea, Fungi", 
"Eukaryota, no Fungi", "Fungi", "Viruses", "Bacteria", "Eukaryota", or 
"Archaea" to restrict the SSN nodes to these taxonomy groups.
</p>

<p>
"Bacteria, Archaea, Fungi", "Bacteria", "Archaea", and "Fungi" select organisms 
that may provide genome context (gene clusters/operons) useful for inferring 
functions. 
</p>

<p>
The SSN nodes also can be restricted to taxonomy categories within the 
Superkingdom, Kingdom, Phylum, Class, Order, Family, Genus, and Species ranks. 
Multiple conditions are combined to be a union of each other. 
</p>

<p>
The SSN nodes from the UniRef90 and UniRef50 databases are the UniRef90 and 
UniRef50 clusters for which the cluster ID ("representative sequence") matches 
the specified taxonomy categories. The UniProt members in these nodes that do 
not match the specified taxonomy categories are removed from the nodes.  
</p>
TEXT;

    } else if ($gen_type == "FASTA" || $gen_type == "FASTA_ID") {
        return <<<TEXT
<p>
From preselected conditions, the user can select "Bacteria, Archaea, Fungi", 
"Eukaryota, no Fungi", "Fungi", "Viruses", "Bacteria", "Eukaryota", or 
"Archaea" to restrict the SSN nodes to these taxonomy groups. 
</p>

<p>
"Bacteria, Archaea, Fungi", "Bacteria", "Archaea", and "Fungi" select organisms 
that may provide genome context (gene clusters/operons) useful for inferring 
functions. 
</p>

<p>
The SSN nodes also can be restricted to taxonomy categories within the 
Superkingdom, Kingdom, Phylum, Class, Order, Family, Genus, and Species ranks. 
Multiple conditions are combined to be a union of each other. 
</p>
TEXT;

    } else if ($gen_type == "ACCESSION") {
        return <<<TEXT
<p>
From preselected conditions, the user can select "Bacteria, Archaea, Fungi", 
"Eukaryota, no Fungi", "Fungi", "Viruses", "Bacteria", "Eukaryota", or 
"Archaea" to restrict the SSN nodes to these taxonomy groups. 
</p>

<p>
"Bacteria, Archaea, Fungi", "Bacteria", "Archaea", and "Fungi" select organisms 
that may provide genome context (gene clusters/operons) useful for inferring 
functions. 
</p>

<p>
The SSN nodes also can be restricted to taxonomy categories within the 
Superkingdom, Kingdom, Phylum, Class, Order, Family, Genus, and Species ranks. 
Multiple conditions are combined to be a union of each other. 
</p>

<p>
The SSN nodes from the UniRef90 and UniRef50 databases are the UniRef90 and 
UniRef50 clusters for which the cluster ID ("representative sequence") matches 
the specified taxonomy categories. The UniProt members in these nodes that do 
not match the specified taxonomy categories are removed from the nodes. 
</p>
TEXT;

    }

    return "";
}

function get_tax_tab_text($gen_type) {
    if ($gen_type == "BLAST") {
        return <<<TEXT
<p>
The taxonomy distribution for the UniProt, UniRef90 cluster, or UniRef50 
cluster IDs identified in the BLAST is displayed.   
</p>

<p>
The sunburst is interactive, providing the ability to zoom to a selected 
taxonomy category by clicking on that category; clicking on the center circle 
will zoom the display to the next highest rank.
</p>
TEXT;
    } else if ($gen_type == "FAMILIES") {
        return <<<TEXT
<p>
The taxonomy distribution for the UniProt IDs in the input dataset is 
displayed.   For UniRef90 and UniRef50 cluster datasets, these are retrieved 
from the lookup table provided by UniProt/UniRef.
</p>

<p>
The UniRef90 and UniRef50 clusters containing the UniProt IDs then are 
identified using the lookup table provided by UniProt/UniRef. These UniRef90 
and UniRef50 clusters may contain UniProt IDs from other families; in addition, 
the UniRef90 and UniRef50 clusters in the selected taxonomy category may 
contain UniProt IDs from other categories. This results from conflation of 
UniProt IDs in UniRef90 and UniRef50 clusters that share &ge;90% and &ge;50% sequence 
identity, respectively. 
</p>

<p>
The numbers of UniProt IDs, UniRef90 cluster IDs, and UniRef50 cluster IDs for 
the selected category are displayed. 
</p>

<p>
The sunburst is interactive, providing the ability to zoom to a selected 
taxonomy category by clicking on that category; clicking on the center circle 
will zoom the display to the next highest rank. 
</p>
TEXT;
    } else if ($gen_type == "FASTA" || $gen_type == "FASTA_ID") {
        return <<<TEXT
<p>
The taxonomy distribution for the UniProt IDs in the input dataset is 
displayed.   
</p>

<p>
The numbers of UniProt IDs for the selected category are displayed. 
</p>

<p>
The sunburst is interactive, providing the ability to zoom to a selected 
taxonomy category by clicking on that category; clicking on the center circle 
will zoom the display to the next highest rank. 
</p>
TEXT;
    } else if ($gen_type == "ACCESSION") {
        return <<<TEXT
<p>
The taxonomy distribution for the UniProt IDs in the input dataset is displayed.
For UniRef90 and UniRef50 cluster datasets, these are retrieved from the lookup
table provided by UniProt/UniRef.
</p>

<p>
The UniRef90 and UniRef50 clusters containing the UniProt IDs then are 
identified using the lookup table provided by UniProt/UniRef. These UniRef90 
and UniRef50 clusters may contain UniProt IDs from other families; in addition, 
the UniRef90 and UniRef50 clusters in the selected taxonomy category may 
contain UniProt IDs from other categories. This results from conflation of 
UniProt IDs in UniRef90 and UniRef50 clusters that share &ge;90% and &ge;50% sequence 
identity, respectively. 
</p>

<p>
The numbers of UniProt IDs, UniRef90 cluster IDs, and UniRef50 cluster IDs for 
the selected category are displayed. 
</p>

<p>
The sunburst is interactive, providing the ability to zoom to a selected 
taxonomy category by clicking on that category; clicking on the center circle 
will zoom the display to the next highest rank. 
</p>
TEXT;
    } else {
        return "";
    }
}


