<?php 
require_once("../includes/main.inc.php");
require_once(__BASE_DIR__ . "/libs/table_builder.class.inc.php");
require_once(__BASE_DIR__ . "/libs/global_settings.class.inc.php");
require_once(__BASE_DIR__ . "/includes/login_check.inc.php");
require_once(__BASE_DIR__ . "/libs/ui.class.inc.php");
require_once(__DIR__ . "/../libs/dataset_shared.class.inc.php");


if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['analysis_id']) || !is_numeric($_GET['analysis_id'])) {
    error500("Unable to find the requested job.");
}

$is_example = isset($_GET["x"]);
$generate = new stepa($db, $_GET['id'], $is_example);
$generate_id = $generate->get_id();
$email = $generate->get_email();

if ($generate->get_key() != $_GET['key']) {
    error500("Unable to find the requested job.");
}
$key = $_GET['key'];

$analysis_id = $_GET['analysis_id'];
$analysis = new analysis($db, $analysis_id, $is_example);


if (!$is_example && $analysis->is_expired()) {
    require_once("inc/header.inc.php");
    echo "<p class='center'><br>Your job results are only retained for a period of " . global_settings::get_retention_days() . " days.";
    echo "<br>Your job was completed on " . $analysis->get_time_completed();
    echo "<br>Please go back to the <a href='" . functions::get_server_name() . "'>homepage</a></p>";
    require_once("inc/footer.inc.php");
    exit;
}




$mig_info = functions::get_gnt_migrate_info($db, $analysis_id);
$is_migrated = false;
if ($mig_info !== false) {
    $is_migrated = true;
    $gnn_id = $mig_info["gnn_id"];
    $gnn_key = $mig_info["gnn_key"];
}

$web_address = dirname($_SERVER['PHP_SELF']);

$use_advanced_options = global_settings::advanced_options_enabled();

$table_format = "html";
if (isset($_GET["as-table"])) {
    $table_format = "tab";
}


$table = new table_builder($table_format);

$gen_type = $generate->get_type();
$generate = dataset_shared::create_generate_object($gen_type, $db, $is_example);
$stats = $analysis->get_network_stats();

$table = new table_builder($table_format);

add_analysis_summary_table($analysis, $stats, $table);

$table->add_html('</table><br><h4>Dataset Summary</h4><table width="100%" class="pretty no-stretch">');

dataset_shared::add_generate_summary_table($generate, $table, true, $is_example);

$table_string = $table->as_string();


$ex_param = $is_example ? "&x=1" : "";

if (isset($_GET["as-table"])) {
    $table_filename = functions::safe_filename($analysis->get_name()) . "_SSN_overview.txt";

    dataset_shared::send_table($table_filename, $table_string);
    exit(0);
} elseif (isset($_GET["stats-as-table"])) {

    $stats = $analysis->get_network_stats();
    $stats_table = new table_builder("tab");
    $stats_table->add_row("Network", "# Nodes", "# Edges", "File Size (MB)");

    for ($i = 0; $i < count($stats); $i++) {
        $percent_id = "Full";
        if ($i > 0) {
            $percent_id = substr($stats[$i]['File'], strrpos($stats[$i]['File'],'-') + 1);
            $sep_char = "_";
            $percent_id = substr($percent_id, 0, strrpos($percent_id, $sep_char));
            $percent_id = str_replace(".","",$percent_id);
        }
        $stats_table->add_row($percent_id, number_format($stats[$i]['Nodes'],0), number_format($stats[$i]['Edges'],0),
            functions::bytes_to_megabytes($stats[$i]['Size'],0) . " MB");
    }

    $table_string = $stats_table->as_string();
    $table_filename = functions::safe_filename($analysis->get_name()) . "_stats.txt";

    dataset_shared::send_table($table_filename, $table_string);
    exit(0);
}

$IncludeSubmitJs = true;
require_once("inc/header.inc.php");

// $stats is set above
$rep_network_html = "";
$full_network_html = "";
$full_edge_count = 0;
$network_sel_list = array();

$color_ssn_code_fn = function($ssn_index) use ($analysis_id, $email) {
    $js_code = "";
    $html = " <button class='mini colorssn-btn' type='button' onclick='$js_code' data-aid='$analysis_id' data-ssn-index='$ssn_index'>Color SSN</button>";
    return $html;
};

$res_dir = $is_example ? functions::get_results_example_dirname() : functions::get_results_dirname();

for ($i = 0; $i < count($stats); $i++) {
    $gnt_args = "est-id=$analysis_id&est-key=$key&est-ssn=$i";
    $size = $analysis->get_zip_file_size($stats[$i]['File']);
    if ($i == 0) {
        $full_edge_count = number_format($stats[$i]['Edges'], 0);
        if ($stats[$i]['Nodes'] == 0)
            continue;
        $rel_path = $analysis->get_output_dir() . "/" . $analysis->get_network_dir() . "/" . $stats[$i]['File'];
        $path = functions::get_web_root() . "/$res_dir/$rel_path";
        $full_network_html = "<tr>";
        $full_network_html .= "<td style='text-align:center;'>";
        if (!$is_example)
            $full_network_html .= "<a href='$path'><button class='mini'>Download</button></a>";
        $full_network_html .= "  <a href='$path.zip'><button class='mini'>Download ZIP</button></a>";
        $full_network_html .= "</td>\n";
        $full_network_html .= "<td style='text-align:center;'>" . number_format($stats[$i]['Nodes'],0) . "</td>\n";
        $full_network_html .= "<td style='text-align:center;'>" . number_format($stats[$i]['Edges'],0) . "</td>\n";
        $full_network_html .= "<td style='text-align:center;'>" . functions::bytes_to_megabytes($size, 0) . " MB</td>\n";
        if (!$is_example) {
            $full_network_html .= "<td style='text-align:center;'>";
            $full_network_html .= "<a href='../efi-gnt/index.php?$gnt_args'><button class='mini' type='button'>GNT Submission</button></a>";
            //Old: automatically submit color SSN job.  Now we want to provide user with options.
            //$full_network_html .= $color_ssn_code_fn($i);
            $full_network_html .= " <a href='index.php?mode=color&$gnt_args'><button class='mini' type='button'>Color SSN</button></a>";
            $full_network_html .= "</td>\n";
        }
        $full_network_html .= "</tr>";
        $network_sel_list["full"] = $rel_path;
    } else {
        $percent_identity = substr($stats[$i]['File'], strrpos($stats[$i]['File'],'-') + 1);
        $sep_char = "_";
        $percent_identity = substr($percent_identity, 0, strrpos($percent_identity, $sep_char));
        $percent_identity = str_replace(".","",$percent_identity);
        $rel_path = $analysis->get_output_dir() . "/" . $analysis->get_network_dir() . "/" . $stats[$i]['File'];
        $path = functions::get_web_root() . "/$res_dir/$rel_path";
        $rep_network_html .= "<tr>";
        if ($stats[$i]['Nodes'] == 0) {
            $rep_network_html .= "<td style='text-align:center;'>";
        } else {
            $rep_network_html .= "<td style='text-align:center;'>";
            if (!$is_example)
                $rep_network_html .= "<a href='$path'><button class='mini'>Download</button></a>";
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
            $rep_network_html .= "<td style='text-align:center;'>" . functions::bytes_to_megabytes($size, 0) . " MB</td>\n";
            if (!$is_example) {
                $rep_network_html .= "<td style='text-align:center;'>";
                $rep_network_html .= "<a href='../efi-gnt/index.php?$gnt_args'><button class='mini' type='button'>GNT Submission</button></a>";
                //Old: automatically submit color SSN job.  Now we want to provide user with options.
                //$rep_network_html .= $color_ssn_code_fn($i);
                $rep_network_html .= " <a href='index.php?mode=color&$gnt_args'><button class='mini' type='button'>Color SSN</button></a>";
                $rep_network_html .= "</td>\n";
            }
        }
        $rep_network_html .= "</tr>";
        $network_sel_list[$percent_identity] = $rel_path;
    }
}

$stepa_link = functions::get_web_root() . "/index.php#colorssn";
$gnt_link = functions::get_gnt_web_root();

$job_name = $generate->get_job_name();
$network_name = $analysis->get_name();

?>	

<h2>Download Network Files</h2>

<?php if ($job_name) { ?><h4 class="job-display">Submission Name: <b><?php echo $job_name; ?></b></h4><?php } ?>
<?php if ($network_name) { ?><h4 class="job-display">Network Name: <b><?php echo $network_name; ?></b></h4><?php } ?>

<div class="tabs-efihdr tabs">
    <ul>
        <li class="ui-tabs-active"><a href="#info">SSN Overview</a></li>
        <li><a href="#results">Network Files</a></li>
    </ul>

    <div>
        <div id="info">
            <p>
            The parameters used for the initial submission and the finalization are summarized in the table below.
            </p>
        
            <h4>Analysis Summary</h4>
            <table width="100%" class="pretty no-stretch">
                <?php echo $table_string; ?>
            </table>
            <div style="float: right"><a href='<?php echo $_SERVER['PHP_SELF'] . "?id=" . $_GET['id'] . "&key=" . $_GET['key'] . "&analysis_id=" . $_GET['analysis_id'] . "&as-table=1$ex_param" ?>'><button class='normal'>Download Information</button></a></div>
            <div style="clear: both"></div>
            <?php if ($is_migrated) { ?>
                <div style="margin-top: 20px;">
                    <center>
                    <a href="../efi-gnt/stepc.php?<?php echo "id=$gnn_id&key=$gnn_key"; ?>">
                        <button class="hl-gnt-bg" type="button" style="color:white">View GNN generated from this SSN</button>
                    </a>
                    </center>
                </div>
            <?php } ?>

        </div>


        <div id="results">
            <p>
            The panels below provide files for full and representative node SSNs for 
            download with the indicated numbers of nodes and edges.  As an approximate guide, SSNs 
            with ~2M edges can be opened with 16 GB RAM, ~4M edges can be opened with 32 
            GB RAM, ~8M edges can be opened with 64 GB RAM, ~15M edges can be opened with 
            128 GB RAM, and ~30M edges can be opened with 256 GB RAM. 
            </p>
            <h4>Full Network <a href="tutorial_download.php" class="question" target="_blank">?</a></h4>
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
<?php if (!$is_example) { ?>
                    <th></th>
<?php } ?>
                </thead>
            
                <tbody>
                <?php echo $full_network_html; ?>
                </tbody>
                </table>
            <?php } ?>
        
            <p>&nbsp;</p>
            <div class="align_left">
                <h4>Representative Node Networks <a href="tutorial_download.php" class="question" target="_blank">?</a></h4>
                <p>
                    In representative node (RepNode) networks, each node in the network represents a collection of proteins grouped
                    according to percent identity. For example, for a 75% identity RepNode network, all connected sequences
                    that share 75% or more identity are grouped into a single node (meta node).
                    Sequences are collapsed together to reduce the overall number of nodes, making for less complicated networks
                    easier to load in Cytoscape.
                </p>
                <p>
                    The cluster organization is not changed, and the clustering of sequences remains identical to the full network.
                </p>
            </div>
            <table width="100%" class="pretty">
            <thead>
                <th></th>
                <th>% ID</th>
                <th># Nodes</th>
                <th># Edges</th>
                <th>File Size (MB)</th>
<?php if (!$is_example) { ?>
                <th></th>
<?php } ?>
            </thead>
        
            <tbody>
            <?php echo $rep_network_html; ?>
            </tbody>
            </table>
        
            <div style="margin-top: 10px; float: right;">
                <a href="<?php echo $_SERVER['PHP_SELF'] . "?id=" . $_GET['id'] .
                                    "&key=" . $_GET['key'] .
                                    "&analysis_id=" . $_GET['analysis_id'] .
                                    "&stats-as-table=1$ex_param" ?>"><button class='mini'>Download Network Statistics as Table</button></a>
            </div>
            <div style="clear:both"></div>

            <center><p><a href="tutorial_cytoscape.php">New to Cytoscape?</a></p></center>
        </div>
    </div>
</div>



<center><p>Portions of these data are derived from the Universal Protein Resource (UniProt) databases.</p></center>


<div id="ssn-confirm" title="Submit Confirmation" style="display: none">
<p>
<span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>
Would you like to color the SSN?
</p>    
</div>

<script>
    $(document).ready(function() {
        $(".colorssn-btn").click(function(evt) {
            var aid = $(this).data("aid");
            var ssnIndex = $(this).data("ssn-index");
            $("#ssn-confirm").dialog({
                resizable: false,
                height: "auto",
                width: 400,
                modal: true,
                buttons: {
                    Yes: function() {
                        submitStepEColorSsnForm(aid, ssnIndex);
                        $(this).dialog("close");
                    },
                    No: function() {
                        $(this).dialog("close");
                    }
                }
            });
        });

        $(".tabs").tabs();
    });
</script>

<?php

require_once("inc/footer.inc.php");






function add_analysis_summary_table($analysis, $stats, $table) {
    $use_advanced_options = global_settings::advanced_options_enabled();
    
    $network_name = $analysis->get_name();
    $time_window = $analysis->get_time_period();
    $a_id = $analysis->get_id();
    $num_filt_seq = isset($stats[0]["Nodes"]) ? $stats[0]["Nodes"] : 0;

    $table->add_row("Analysis Job Number", $a_id);
    $table->add_row("Network Name", $network_name);
    $table->add_row("Alignment Score", $analysis->get_filter_value());
    if ($use_advanced_options)
        $table->add_row("Filter", $analysis->get_filter_name());
    $table->add_row("Minimum Length", number_format($analysis->get_min_length()));
    $table->add_row("Maximum Length", number_format($analysis->get_max_length()));
    if ($use_advanced_options) {
        $table->add_row("CD-HIT Method", $analysis->get_cdhit_method_nice());
        $min_method = $analysis->get_min_option_nice();
        if ($min_method)
            $table->add_row("Minimize", $min_method);
    }
    if ($num_filt_seq)
        $table->add_row("Total Number of Sequences After Length Filtering", number_format($num_filt_seq));
}


?>

