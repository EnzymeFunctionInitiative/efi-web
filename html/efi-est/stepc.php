<?php 
require_once(__DIR__."/../../conf/settings_paths.inc.php");
require_once(__EST_DIR__."/includes/main.inc.php");
require_once(__BASE_DIR__ . "/libs/table_builder.class.inc.php");
require_once(__BASE_DIR__ . "/libs/global_settings.class.inc.php");
require_once(__BASE_DIR__ . "/includes/login_check.inc.php");
require_once(__BASE_DIR__ . "/libs/ui.class.inc.php");
require_once(__EST_DIR__ . "/libs/dataset_shared.class.inc.php");


if ((!isset($_GET['id'])) || (!is_numeric($_GET['id']))) {
    error500("Unable to find the requested job.");
}

$is_example = isset($_GET["x"]);
$generate = new stepa($db, $_GET['id'], $is_example);
$gen_id = $generate->get_id();
$key = $_GET['key'];

if ($generate->get_key() != $_GET['key']) {
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




///////////////////////////////////////////////////////////////////////////////////////////////////
// This code handles submission of the form.  It exits at the end of the code block if the
// analysis job was successfully created.
if (isset($_POST['analyze_data'])) {
    foreach ($_POST as $var) {
        $var = trim(rtrim($var));
    }
    $min = $_POST['minimum'];
    if ($_POST['minimum'] == "") {
        $min = __MINIMUM__;
    }
    $max = $_POST['maximum'];
    if ($_POST['maximum'] == "") {
        $max = __MAXIMUM__;
    }

    $job_id = $_POST['id'];

    $analysis = new analysis($db);

    $customFile = "";
    if (isset($_FILES['cluster_file']) && (!isset($_FILES['cluster_file']['error']) || $_FILES['cluster_file']['error'] == 0)) {
        $customFile = $_FILES['cluster_file']['tmp_name'];
    }

    $filter = "";
    $filter_value = 0;
    if (isset($_POST['filter'])) {
        $filter = $_POST['filter'];
        if ($filter == "eval") {
            $filter_value = $_POST['evalue'];
        } elseif ($filter == "pid") {
            $filter_value = $_POST['pid'];
        } elseif ($filter == "bit") {
            $filter_value = $_POST['bitscore'];
        } elseif ($filter != "custom") {
            $filter = "eval";
        }
    } else {
        $filter = "eval";
        $filter_value = $_POST['evalue'];
    }

    if (user_auth::has_token_cookie()) {
        $email = user_auth::get_email_from_token($db, user_auth::get_user_token());
        if (functions::is_job_sticky($db, $job_id, $email)) {
            $parent_id = $job_id;
            $parent_check = functions::is_parented($db, $job_id, $email);

            if ($parent_check === false) {
                $job_id = stepa::duplicate_job($db, $job_id, $email);
                if ($job_id === false) {
                    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
                    die("Unable to create duplicate of $parent_id for $email");
                }
            } else {
                $job_id = $parent_check;
            }
        }
    }

    $cdhitOpt = "";
    if (functions::custom_clustering_enabled() && isset($_POST["cdhit-opt"])) {
        $opt = $_POST["cdhit-opt"];
        if ($opt == "sb" || $opt == "est" || $opt == "est+")
            $cdhitOpt = $opt;
    }

    $min_node_attr = isset($_POST['min_node_attr']) ? 1 : 0;
    $min_edge_attr = isset($_POST['min_edge_attr']) ? 1 : 0;
    $compute_nc = isset($_POST['compute_nc']) ? true : false;

    $result = $analysis->create(
        $job_id,
        $filter_value,
        $_POST['network_name'],
        $min,
        $max,
        $customFile,
        $cdhitOpt,
        $filter,
        $min_node_attr,
        $min_edge_attr,
        $compute_nc
    );

    if ($result['RESULT']) {
        header('Location: stepd.php');
        exit(0);
    }
}

// If an analysis was not submitted, then display the Step C page.



$web_address = dirname($_SERVER['PHP_SELF']);

$table_format = "html";
if (isset($_GET["as-table"])) {
    $table_format = "tab";
}

$use_advanced_options = global_settings::advanced_options_enabled();

$gen_type = $generate->get_type();
$generate = dataset_shared::create_generate_object($gen_type, $db, $is_example);

$uniref = dataset_shared::get_uniref_version($gen_type, $generate);
$job_name = $generate->get_job_name();
$use_domain = dataset_shared::get_domain($gen_type, $generate) == "on";


$table = new table_builder($table_format);
dataset_shared::add_generate_summary_table($generate, $table, false, $is_example);
$table_string = $table->as_string();

$ex_param = $is_example ? "&x=1" : "";

if (isset($_GET["as-table"])) {
    $file_job_name = $gen_id . "_" . $gen_type;
    $table_filename = functions::safe_filename($file_job_name) . "_summary.txt";

    dataset_shared::send_table($table_filename, $table_string);
    exit(0);
}

$IncludePlotlyJs = true;
require_once(__DIR__."/inc/header.inc.php");


$date_completed = $generate->get_time_completed_formatted();

$url = $_SERVER['PHP_SELF'] . "?" . http_build_query(array('id'=>$generate->get_id(),
    'key'=>$generate->get_key()));

function make_plot_download($gen, $hdr, $type, $preview_img, $download_img, $plot_exists) {
    global $is_example;
    $html = ""; //"<span class='plot-header'>$hdr</span> \n";
    if (!$plot_exists) {
        $html .= "Unable to be generated";
        return;
    }
    if ($preview_img) {
        $html .= <<<HTML
<div>
    <center>
        <img src='$preview_img' />
HTML;
        $ex_param = $is_example ? "&x=1" : "";
        $html .= "<a href='graphs.php?id=" . $gen->get_id() . "&type=" . $type . "&key=" . $_GET["key"] . "$ex_param'><button class='file_download'>Download high resolution <img src='images/download.svg' /></button></a>";
        $html .= <<<HTML
    </center>
</div>
HTML;
    } else {
        $html .= "<a href='$download_img'><button class='file_download'>Preview</button></a>\n";
    }

    return $html;
}

function make_histo_plot_download($gen, $hdr, $plot_type) {
    $download_type = $gen->get_length_histogram_download_type($plot_type);
    $web_path = $gen->get_length_histogram($plot_type, true, true);
    $large_web_path = $gen->get_length_histogram($plot_type, true, false);
    $plot_exists = $web_path ? true : false;
    if (!$plot_exists) {
        $plot_type = "";
        $use_v2 = false;
        $download_type = $gen->get_length_histogram_download_type($plot_type, $use_v2);
        $web_path = $gen->get_length_histogram($plot_type, true, true, $use_v2);
        $large_web_path = $gen->get_length_histogram($plot_type, true, false, $use_v2);
        $plot_exists = $large_web_path ? true : false;
    }
    return make_plot_download($gen, $hdr, $download_type, $web_path, $large_web_path, $plot_exists); 
}

function make_interactive_plot($gen, $hdr, $plot_div, $plot_id) {
    $plot = plots::get_plot($gen, $plot_id);
    if (!$plot || !$plot->has_data())
        return "";

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


$ssn_jobs = $generate->get_analysis_jobs();

// Still use old EST graphing code, but this is the new length histogram graphs.
$use_v2_graphs = $generate->get_graphs_version() >= 2;

?>	



<h2>Dataset Completed</h2>

<?php if ($job_name) { ?>
<h4 class="job-display">Submission Name: <b><?php echo $job_name; ?></b></h4>
<?php } ?>
<p>
A minimum sequence similarity threshold that specifies the sequence pairs 
connected by edges is needed to generate the SSN. This threshold also 
determines the segregation of proteins into clusters. The threshold is applied 
to the edges in the SSN using the alignment score, an edge node attribute that 
is a measure of the similarity between sequence pairs.
</p>

<div style="margin:20px;color:red"><?php if (isset($result['MESSAGE'])) { echo $result['MESSAGE']; } ?></div>

<div class="tabs-efihdr tabs">
    <ul class="">
        <li class="ui-tabs-active"><a href="#info">Dataset Summary</a></li>
        <li><a href="#graphs">Dataset Analysis</a></li>
        <?php if (!$is_example) { ?>
        <li><a href="#final">SSN Finalization</a></li>
        <?php } ?>
        <?php if (count($ssn_jobs) > 0) { ?>
        <li><a href="#jobs">SSNs Created From this Dataset</a></li>
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
            <div style="float: right"><a href='<?php echo $_SERVER['PHP_SELF'] . "?id=" . $_GET['id'] . "&key=$key&as-table=1$ex_param" ?>'><button class="normal">Download Information</button></a></div>
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
            <?php echo make_plot_download($generate, "Alignment Length vs Alignment Score", "ALIGNMENT", $generate->get_alignment_plot_sm(), $generate->get_alignment_plot(1), $generate->alignment_plot_exists()); ?>
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
            <?php echo make_plot_download($generate, "Percent Identity vs Alignment Score", "IDENTITY", $generate->get_percent_identity_plot_sm(), $generate->get_percent_identity_plot(1), $generate->percent_identity_plot_exists()); ?>
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
            <?php echo make_plot_download($generate, "Number of Edges at Alignment Score", "EDGES", $generate->get_number_edges_plot_sm(), $generate->get_number_edges_plot(1), $generate->number_edges_plot_exists()); ?>
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
            <?php echo make_plot_download($generate, "Alignment Length vs Alignment Score", "ALIGNMENT", $generate->get_alignment_plot_new(1), $generate->get_alignment_plot_new(1), $generate->alignment_plot_new_exists()); ?>
                    <iframe src="<?php echo $generate->get_alignment_length_plot_new_html(1); ?>"></iframe>
                    </div>
                </div>
<?php } ?>
<?php if ($generate->percent_identity_plot_new_exists()) { ?>
                <div>
                    <h3>Percent Identity vs Alignment Score (dynamic)</h3>
                    <div>
            <?php echo make_plot_download($generate, "Percent Identity vs Alignment Score", "IDENTITY", $generate->get_percent_identity_plot_new(1), $generate->get_percent_identity_plot_new(1), $generate->percent_identity_plot_new_exists()); ?>
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


        <?php if (!$is_example) { ?>
        <!-- FINALIZATION -->
        <div id="final">
            <form name="define_length" method="post" action="<?php echo $url; ?>" class="align_left" enctype="multipart/form-data">
            
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
                        that connect the proteins (nodes)  in the SSN.  This tab also is used to 
                        specify Minimum and Maximum "Sequence Length Restriction Options" that exclude 
                        fragments and/or domain architectures.
                        </p>

                        <p>
                            <span class="input-name">Alignment Score Threshold:</span>
                            <span class="input-field">
                                <input type="text" name="evalue" size="5" <?php if (isset($_POST['evalue'])) { echo "value='" . $_POST['evalue'] ."'"; } ?>>
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
            
                        <p><input type="text" name="pid" <?php if (isset($_POST['pid'])) { echo "value='" . $_POST['pid'] ."'"; } ?>>
                        % ID
                        </p>
            
                        This score is the similarity threshold which determine the 
                        connection of proteins with each other. All pairs of proteins with a percent
                        ID below this number will not be connected. Sets of connected proteins will 
                        form clusters.
                    </div>
                    <div id="threshold-bit" class="tab">
                        
                        <p>Select a lower limit for the bit score to use as a lower threshold for clustering in the SSN.</p>
            
                        <p><input type="text" name="bitscore" <?php if (isset($_POST['bitscore'])) { echo "value='" . $_POST['bitscore'] ."'"; } ?>></p>
            
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
                                    <input type="text" name="minimum" size="7" maxlength='20' <?php if (isset($_POST['minimum'])) { echo "value='" . $_POST['minimum'] . "'"; } ?>> (default: <?php echo __MINIMUM__; ?>)
                                </span>
                            </div>
                            <div>
                                <span class="input-name">Maximum:</span>
                                <span class="input-field">
                                    <input type="text" name="maximum" size="7" maxlength='20' <?php if (isset($_POST['maximum'])) { echo "value='" . $_POST['maximum'] . "'"; } ?>> (default: <?php echo __MAXIMUM__; ?>)
                                </span>
                            </div>
                        </p>
                    </div>
                </div>
                <div class="initial-open">
                    <h3>Neighborhood Connectivity Option</h3>
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
                                (<a href="https://www.biorxiv.org/content/10.1101/2020.04.16.045138v1.full">https://www.biorxiv.org/content/10.1101/2020.04.16.045138v1.full</a>).
                                Using <b>Neighborhood Connectivity Coloring</b> as a guide, the alignment score threshold 
                                can be chosen in Cytoscape to separate the SSN into families.
                                </p>
                            </div>
                        </div>
                        </p>
                    </div>
                </div>
                <?php if ($use_advanced_options) { ?>
                <div>
                    <h3>Dev Site Options</h3>
                    <div>
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
            
            <input type='hidden' name='id' value='<?php echo $generate->get_id(); ?>'>

            <center>
                <button type="submit" name="analyze_data" class="dark">Create SSN</button>
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

    $(".tabs").tabs();
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
<?php

require_once(__DIR__."/inc/footer.inc.php");


?>

