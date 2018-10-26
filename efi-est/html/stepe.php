<?php 
require_once '../includes/main.inc.php';
require_once '../libs/table_builder.class.inc.php';
require_once("../../includes/login_check.inc.php");


if ((!isset($_GET['id'])) || (!is_numeric($_GET['id']))) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    exit;
}

$generate = new stepa($db,$_GET['id']);
$generate_id = $generate->get_id();
$email = $generate->get_email();

if ($generate->get_key() != $_GET['key']) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    //echo "No EFI-EST Selected. Please go back";
    exit;
}
$key = $_GET['key'];

$analysis_id = $_GET['analysis_id'];
$analysis = new analysis($db, $analysis_id);

$mig_info = functions::get_gnt_migrate_info($db, $analysis_id);
$is_migrated = false;
if ($mig_info !== false) {
    $is_migrated = true;
    $gnn_id = $mig_info["gnn_id"];
    $gnn_key = $mig_info["gnn_key"];
}

$table_format = "html";
if (isset($_GET["as-table"])) {
    $table_format = "tab";
}
$table = new table_builder($table_format);


$web_address = dirname($_SERVER['PHP_SELF']);
$date_completed = $generate->get_time_completed_formatted();
$db_version = $generate->get_db_version();
$legacy = empty($db_version); // Indicates if we are looking at old jobs.


$gen_type = $generate->get_type();
$formatted_gen_type = functions::format_job_type($gen_type);

$table->add_row("Date Completed", $date_completed);
if (!empty($db_version)) {
    $table->add_row("Database Version", $db_version);
}
$table->add_row("Input Option", $formatted_gen_type);
$table->add_row("Job Number", $generate_id);
if ($generate->get_job_name())
    $table->add_row("Job Name", $generate->get_job_name());

$uploaded_file = "";
$included_family = "";
$num_family_nodes = $generate->get_num_family_sequences();
$total_num_nodes = $generate->get_num_sequences();
$extra_nodes_string = "";
$extra_nodes_ast = "";

if ($gen_type == "BLAST") {
    $generate = new blast($db,$_GET['id']);
    $code = $generate->get_blast_input();
    if ($table_format == "html") {
        $code = "<a href='blast.php?blast=$code' target='_blank'>View Sequence</a>";
    }
    $table->add_row("Blast Sequence", $code);
    $table->add_row("E-Value", $generate->get_evalue());
    $included_family = $generate->get_families_comma();
    if ($included_family != "")
        $table->add_row("PFam/Interpro Families", $included_family);
    $table->add_row("Maximum Blast Sequences", number_format($generate->get_submitted_max_sequences()));
}
elseif ($gen_type == "FAMILIES") {
    $generate = new generate($db,$_GET['id']);
    $included_family = $generate->get_families_comma();
    $seqid = $generate->get_sequence_identity();
    $overlap = $generate->get_length_overlap();
    $unirefVersion = $generate->get_uniref_version();

    if ($included_family != "")
        $table->add_row("PFam/Interpro Families", $included_family);
    $table->add_row("E-Value", $generate->get_evalue());
    $table->add_row("Fraction", $generate->get_fraction());
    $table->add_row("Domain", $generate->get_domain());
    if ($unirefVersion)
        $table->add_row("UniRef Version", $unirefVersion);
    if ($seqid)
        $table->add_row("Sequence Identity", $seqid);
    if ($overlap)
        $table->add_row("Sequence Overlap", $overlap);
}
elseif ($gen_type == "ACCESSION") {
    $generate = new accession($db,$_GET['id']);
    $uploaded_file = $generate->get_uploaded_filename();
    if ($uploaded_file)
        $table->add_row("Uploaded Accession ID File", $uploaded_file);
    $included_family = $generate->get_families_comma();
    if ($included_family != "")
        $table->add_row("PFam/Interpro Families", $included_family);
    $table->add_row("E-Value", $generate->get_evalue());
    $table->add_row("Fraction", $generate->get_fraction());
    $unirefVersion = $generate->get_uniref_version();
    if ($unirefVersion)
        $table->add_row("UniRef Version", $unirefVersion);
}
elseif ($gen_type == "FASTA" || $gen_type == "FASTA_ID") {
    $generate = new fasta($db,$_GET['id']);
    $uploaded_file = $generate->get_uploaded_filename();
    if ($uploaded_file)
        $table->add_row("Uploaded Fasta File", $uploaded_file);
    $included_family = $generate->get_families_comma();
    if ($included_family != "")
        $table->add_row("PFam/Interpro Families", $included_family);
    $table->add_row("E-Value", $generate->get_evalue());
    $table->add_row("Fraction", $generate->get_fraction());
    $unirefVersion = $generate->get_uniref_version();
    if ($unirefVersion)
        $table->add_row("UniRef Version", $unirefVersion);

    $num_file_seq = $generate->get_total_num_file_sequences();
    $num_matched = $generate->get_num_matched_file_sequences();
    $num_unmatched = $generate->get_num_unmatched_file_sequences();
    
    if (!empty($num_file_seq))
        $table->add_row("Number of Sequences in Uploaded File", number_format($num_file_seq));
    if (!empty($num_matched) && !empty($num_unmatched))
        $table->add_row("Number of FASTA Headers in Uploaded File", number_format($num_matched + $num_unmatched));
    if (!empty($num_matched))
        $table->add_row("Number of SSN Nodes with UniProt IDs from Uploaded File", number_format($num_matched));
    if (!empty($num_unmatched))
        $table->add_row("Number of SSN Nodes without UniProt IDs from Uploaded File", number_format($num_unmatched));

    if (!empty($num_family_nodes) && !empty($num_file_seq)) {
        $extra_num_nodes = $total_num_nodes - $num_family_nodes - $num_file_seq;
        if ($extra_num_nodes > 0) {
            $extra_nodes_string = "* $extra_num_nodes additional nodes have been added since multiple UniProt IDs were found for a single sequence with more than one header in one or more cases.";
            $extra_nodes_ast = "*";
        }
    }
}

if ($included_family && !empty($num_family_nodes))
    $table->add_row("Number of Sequences in PFAM/InterPro Family", number_format($num_family_nodes));
$table->add_row("Total Number of Sequences $extra_nodes_ast", number_format($total_num_nodes));

$table->add_row("Network Name", $analysis->get_name());
$table->add_row("Alignment Score", $analysis->get_evalue());
$table->add_row("Minimum Length", number_format($analysis->get_min_length()));
$table->add_row("Maximum Length", number_format($analysis->get_max_length()));
if (functions::custom_clustering_enabled())
    $table->add_row("CD-HIT Method", $analysis->get_cdhit_method_nice());
$conv_ratio = $generate->get_convergence_ratio();
$convergence_ratio_string = "";
if ($conv_ratio > -0.5) {
    $table->add_row("Convergence Ratio<sup>+</sup>", number_format($conv_ratio, 3));
    $convergence_ratio_string = <<<STR
<div><sup>+</sup>
The convergence ratio is a measure of the similarity of the sequences used in the BLAST.  It is the
ratio of the total number of edges retained from the BLAST (e-values less than the specified threshold;
default 5) to the total number of sequence pairs.  The value decreases from 1.0 for sequences that are
very similar (identical) to 0.0 for sequences that are very different (unrelated).</div>
STR;
}

$table_string = $table->as_string();

if (isset($_GET["as-table"])) {
    $table_filename = functions::safe_filename($analysis->get_name()) . "_settings.txt";

    send_table($table_filename, $table_string);

//    header('Pragma: public');
//    header('Expires: 0');
//    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
//    header('Content-Type: application/octet-stream');
//    header('Content-Disposition: attachment; filename="' . $table_filename . '"');
//    header('Content-Length: ' . strlen($table_string));
//    ob_clean();
//    echo $table_string;
}
elseif (isset($_GET["stats-as-table"])) {

    $stats = $analysis->get_network_stats();
    $stats_table = new table_builder("tab");
    $stats_table->add_row("Network", "# Nodes", "# Edges", "File Size (MB)");

    for ($i = 0; $i < count($stats); $i++) {
        $percent_id = "Full";
        if ($i > 0) {
            $percent_id = substr($stats[$i]['File'], strrpos($stats[$i]['File'],'-') + 1);
            $sep_char = $legacy ? "." : "_";
            $percent_id = substr($percent_id, 0, strrpos($percent_id, $sep_char));
            $percent_id = str_replace(".","",$percent_id);
        }
        $stats_table->add_row($percent_id, number_format($stats[$i]['Nodes'],0), number_format($stats[$i]['Edges'],0),
            functions::bytes_to_megabytes($stats[$i]['Size'],0) . " MB");
    }

    $table_string = $stats_table->as_string();
    $table_filename = functions::safe_filename($analysis->get_name()) . "_stats.txt";

    send_table($table_filename, $table_string);
}
else {

    if ($analysis->is_expired()) {
        echo "<p class='center'><br>Your job results are only retained for a period of " . functions::get_retention_days() . " days.";
        echo "<br>Your job was completed on " . $analysis->get_time_completed();
        echo "<br>Please go back to the <a href='" . functions::get_server_name() . "'>homepage</a></p>";
        exit;
    }

    $stats = $analysis->get_network_stats();
    $rep_network_html = "";
    $full_network_html = "";
    $full_edge_count = 0;
    $network_sel_list = array();

    $color_ssn_code_fn = function($ssn_index) use ($analysis_id, $email) {
        $js_code = "submitStepEColorSsnForm(\"$email\", $analysis_id, $ssn_index)";
        //$js_code = "alert(\"hi\")";
        $html = " <button class='mini' type='button' onclick='$js_code'>Color SSN</button>";
        return $html;
    };

    for ($i = 0; $i < count($stats); $i++) {
        $gnt_args = "est-id=$analysis_id&est-key=$key&est-ssn=$i";
        if ($i == 0) {
            $full_edge_count = number_format($stats[$i]['Edges'], 0);
            if ($stats[$i]['Nodes'] == 0)
                continue;
            $rel_path = $analysis->get_output_dir() . "/" . $analysis->get_network_dir() . "/" . $stats[$i]['File'];
            $path = functions::get_web_root() . "/results/" . $rel_path;
            $full_network_html = "<tr>";
            $full_network_html .= "<td style='text-align:center;'><a href='$path'><button class='mini'>Download</button></a>";
            if (!$legacy)
                $full_network_html .= "  <a href='$path.zip'><button class='mini'>Download ZIP</button></a>";
            $full_network_html .= "</td>\n";
            $full_network_html .= "<td style='text-align:center;'>" . number_format($stats[$i]['Nodes'],0) . "</td>\n";
            $full_network_html .= "<td style='text-align:center;'>" . number_format($stats[$i]['Edges'],0) . "</td>\n";
            $full_network_html .= "<td style='text-align:center;'>" . functions::bytes_to_megabytes($stats[$i]['Size'],0) . " MB</td>\n";
            $full_network_html .= "<td style='text-align:center;'>";
            $full_network_html .= "<a href='../efi-gnt/index.php?$gnt_args'><button class='mini' type='button'>Make GNN</button></a>";
            $full_network_html .= $color_ssn_code_fn($i);
            $full_network_html .= "</td>\n";
            $full_network_html .= "</tr>";
            $network_sel_list["full"] = $rel_path;
        }
        else {
            $percent_identity = substr($stats[$i]['File'], strrpos($stats[$i]['File'],'-') + 1);
            $sep_char = $legacy ? "." : "_";
            $percent_identity = substr($percent_identity, 0, strrpos($percent_identity, $sep_char));
            $percent_identity = str_replace(".","",$percent_identity);
            $rel_path = $analysis->get_output_dir() . "/" . $analysis->get_network_dir() . "/" . $stats[$i]['File'];
            $path = functions::get_web_root() . "/results/" . $rel_path;
            $rep_network_html .= "<tr>";
            if ($stats[$i]['Nodes'] == 0) {
                $rep_network_html .= "<td style='text-align:center;'></a>";
            } else {
                $rep_network_html .= "<td style='text-align:center;'><a href='$path'><button class='mini'>Download</button></a>";
                if (!$legacy)
                    $rep_network_html .= "  <a href='$path.zip'><button class='mini'>Download ZIP</button></a>";
                $rep_network_html .= "</td>\n";
            }
            $rep_network_html .= "<td style='text-align:center;'>" . $percent_identity . "</td>\n";
            if ($stats[$i]['Nodes'] == 0) {
                $rep_network_html .= "<td style='text-align:center;' colspan='4'>The output file was too large (edges=" . 
                    number_format($stats[$i]['Edges'], 0) . ") to be generated by EST.</td>\n";
            } else {
                $rep_network_html .= "<td style='text-align:center;'>" . number_format($stats[$i]['Nodes'],0) . "</td>\n";
                $rep_network_html .= "<td style='text-align:center;'>" . number_format($stats[$i]['Edges'],0) . "</td>\n";
                $rep_network_html .= "<td style='text-align:center;'>" . functions::bytes_to_megabytes($stats[$i]['Size'],0) . " MB</td>\n";
                $rep_network_html .= "<td style='text-align:center;'>";
                $rep_network_html .= "<a href='../efi-gnt/index.php?$gnt_args'><button class='mini' type='button'>Make GNN</button></a>";
                $rep_network_html .= $color_ssn_code_fn($i);
                $rep_network_html .= "</td>\n";
            }
            $rep_network_html .= "</tr>";
            $network_sel_list[$percent_identity] = $rel_path;
        }
    }

    require_once 'inc/header.inc.php'; 


    $stepa_link = functions::get_web_root() . "/index.php#colorssn";
    $gnt_link = functions::get_gnt_web_root();

?>	

<h2 class="darkbg">Download Network Files</h2>
    <p>&nbsp;</p>
    <h3>Network Information</h3>

    <h4>Generation and Analysis Summary Table</h4>

    <table width="100%" class="pretty">
        <?php echo $table_string; ?>
    </table>
    <div style="float: right"><a href='<?php echo $_SERVER['PHP_SELF'] . "?id=" . $_GET['id'] . "&key=" . $_GET['key'] . "&analysis_id=" . $_GET['analysis_id'] . "&as-table=1" ?>'><button class='normal'>Download</button></a></div>
    <div style="clear: both"></div>
    <?php echo $extra_nodes_string; ?>
    <?php echo $convergence_ratio_string; ?>

    <h3>Full Network <a href="tutorial_download.php" class="question" target="_blank">?</a></h3>
    <p>Each node in the network represents a single protein sequence. Large files (&gt;500MB) may not open in Cytoscape.</p>
<?php if (!$full_network_html) { ?>
    <p><b>The output file was too large (edges=<?php echo $full_edge_count; ?>) to be generated by EST.  Please use a repnode below or choose a different alignment score.</b></p>
<?php } else { ?>
    <table width="100%" class="pretty">
    <thead>
    <tr>
        <th></th>
        <th># Nodes</th>
        <th># Edges</th>
        <th>File Size (MB)</th>
        <th></th>
    </thead>

    <tbody>
    <?php echo $full_network_html; ?>
    </tbody>
    </table>
<?php } ?>

    <p>&nbsp;</p>
    <div class="align_left">
    <h3>Representative Node Networks <a href="tutorial_download.php" class="question" target="_blank">?</a></h3>
    <p>
        In representative node (RepNode) networks, each node in the network represents a collection of proteins grouped
        according to percent identity. For example, for a 75% identity RepNode network, all connected sequences
        that share 75% or more identity are grouped into a single node (meta node).
        <br><br>
        The cluster organization is not changed, and the clustering of sequences remains identical to the full network.
        <br><br>
        Sequences are collapsed together to reduce the overall number of nodes making for less complicated networks
        easier to load in Cytoscape.
    </div>
    <table width="100%" class="pretty">
    <thead>
        <th></th>
        <th>% ID</th>
        <th># Nodes</th>
        <th># Edges</th>
        <th>File Size (MB)</th>
        <th></th>
    </thead>

    <tbody>
    <?php echo $rep_network_html; ?>
    </tbody>
    </table>

    <div style="margin-top: 10px; float: right;">
        <a href="<?php echo $_SERVER['PHP_SELF'] . "?id=" . $_GET['id'] .
                            "&key=" . $_GET['key'] .
                            "&analysis_id=" . $_GET['analysis_id'] .
                            "&stats-as-table=1" ?>"><button class='mini'>Download Network Statistics as Table</button></a>
    </div>
    <div style="clear:both"></div>

<?php if ($is_migrated) { ?>
    <div style="margin-top: 20px;">
        <a href="../efi-gnt/stepc.php?<?php echo "id=$gnn_id&key=$gnn_key"; ?>"><button class="mini" type="button">View GNN</button></a>
    </div>
<?php } ?>

    <hr>

<center><p><a href='http://enzymefunction.org/resources/tutorials/efi-and-cytoscape3'>New to Cytoscape?</a></p></center>

<hr>

<p>
    The coloring utility recently developed will help downstream analysis of your SSN. 
    <a href="<?php echo $stepa_link; ?>">Try it!</a>
</p>
<p>
    Have you tried exploring Genome Neighborhood Networks (GNTs) from your favorite SSNs?
    <a href="<?php echo $gnt_link; ?>">Submit a GNT analysis</a>.
</p>

<p>
If you use an SSN from EFI-EST, please <a href="http://www.sciencedirect.com/science/article/pii/S1570963915001120">cite</a>:<br>
John A. Gerlt, Jason T. Bouvier, Daniel B. Davidson, Heidi J. Imker, Boris Sadkhin, David R. Slater, Katie L. Whalen,
<b>Enzyme Function Initiative-Enzyme Similarity Tool (EFI-EST): A web tool for generating protein sequence similarity networks</b>,
Biochimica et Biophysica Acta (BBA) - Proteins and Proteomics, Volume 1854, Issue 8, 2015, Pages 1019-1037, ISSN 1570-9639.
</p>

<center>Portions of these data are derived from the Universal Protein Resource (UniProt) databases.</center>

<hr>

<?php if (functions::is_beta_release()) { ?>
<center><h4><b><span style="color: blue">BETA</span></b></h4></center>
<?php } ?>


<?php

    require_once 'inc/footer.inc.php';

} // as-table block




function send_table($table_filename, $table_string) {
    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $table_filename . '"');
    header('Content-Length: ' . strlen($table_string));
    ob_clean();
    echo $table_string;
}

?>

