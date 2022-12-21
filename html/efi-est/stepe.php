<?php 
require_once(__DIR__."/../../init.php");

use \efi\table_builder;
use \efi\global_settings;
use \efi\global_header;
use \efi\est\stepa;
use \efi\est\functions;
use \efi\est\analysis;
use \efi\est\dataset_shared;
use \efi\est\blast;
use \efi\training\example_config;
use \efi\sanitize;


$id = sanitize::validate_id("id", sanitize::GET);
$key = sanitize::validate_key("key", sanitize::GET);
$analysis_id = sanitize::validate_id("analysis_id", sanitize::GET);

if ($id === false || $key === false || $analysis_id === false) {
    error500("Unable to find the requested job.");
}

$is_example = example_config::is_example();

$generate = new stepa($db, $id, $is_example);
$email = $generate->get_email();

if ($generate->get_key() != $key) {
    error500("Unable to find the requested job.");
}

$analysis = new analysis($db, $analysis_id, $is_example);


if (!$is_example && $analysis->is_expired()) {
    require_once(__DIR__."/inc/header.inc.php");
    echo "<p class='center'><br>Your job results are only retained for a period of " . global_settings::get_retention_days() . " days.";
    echo "<br>Your job was completed on " . $analysis->get_time_completed();
    echo "<br>Please go back to the <a href='" . functions::get_server_name() . "'>homepage</a></p>";
    require_once(__DIR__."/inc/footer.inc.php");
    exit;
}



//print $analysis->get_unixtime_completed();

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

$table = new table_builder($table_format);

add_analysis_summary_table($analysis, $table);

$table->add_html('</table><br><h4>Dataset Summary</h4><table width="100%" class="pretty no-stretch">');

dataset_shared::add_generate_summary_table($generate, $table, true, $is_example);

$table_string = $table->as_string();


$ex_param = $is_example ? "&x=".$is_example : "";

if (isset($_GET["as-table"])) {
    $table_filename = functions::safe_filename($analysis->get_name()) . "_SSN_overview.txt";

    dataset_shared::send_table($table_filename, $table_string);
    exit(0);
} elseif (isset($_GET["stats-as-table"])) {

    $stats_table = new table_builder("tab");
    $stats_table->add_row("Network", "# Nodes", "# Edges", "File Size (MB)");

    $num_networks = $analysis->get_num_networks();
    for ($idx = 0; $idx < $num_networks; $idx++) {
        $info = $analysis->get_network_stats($idx);
        //$percent_id = "Full";
        //if ($idx > 0)
        //    $percent_id = analysis::get_percent_identity($fname);
        $stats_table->add_row($info["pid"], number_format($info["nodes"], 0), number_format($info["edges"], 0),
            functions::bytes_to_megabytes($info["size"], 0) . " MB");
    }

    $table_string = $stats_table->as_string();
    $table_filename = functions::safe_filename($analysis->get_name()) . "_stats.txt";

    dataset_shared::send_table($table_filename, $table_string);
    exit(0);
}

$has_nc = $analysis->get_compute_nc();
$has_repnode = $analysis->get_build_repnode();

$IncludeSubmitJs = true;
require_once(__DIR__."/inc/header.inc.php");

$rep_network_html = "";
$full_network_html = "";
$full_edge_count = 0;

$color_ssn_code_fn = function($ssn_index) use ($analysis_id, $email) {
    $js_code = "";
    $html = " <button class='mini colorssn-btn' type='button' onclick='$js_code' data-aid='$analysis_id' data-ssn-index='$ssn_index'>Color SSN</button>";
    return $html;
};


$num_ssns = $analysis->get_num_networks();
for ($idx = 0; $idx < $num_ssns; $idx++) {
    $gnt_args = "est-id=$analysis_id&est-key=$key&est-ssn=$idx";

    $stats = $analysis->get_network_stats($idx);
    $fname = $stats["file"];

    $size = $stats["zip_size"];

    if ($idx == 0) {
        $full_edge_count = number_format($stats["edges"], 0);
        if ($stats["nodes"] == 0)
            continue;
        $full_network_html = "<tr>";
        $full_network_html .= "<td>";
        //if (!$is_example)
        //    $full_network_html .= get_ssn_dl_btn($web_path);

        $full_network_html .= get_ssn_dl_btn($idx, "Download ZIP");
        if ($has_nc)
            $full_network_html .= get_nc_dl_btn($idx);
        $full_network_html .= "</td>\n";
        $full_network_html .= "<td>" . number_format($stats["nodes"], 0) . "</td>\n";
        $full_network_html .= "<td>" . number_format($stats["edges"], 0) . "</td>\n";
        //$full_network_html .= "<td>" . functions::bytes_to_megabytes($size, 0) . " MB</td>\n";
        if (!$is_example) {
            $full_network_html .= "<td>";
            $full_network_html .= make_split_button($gnt_args, !$has_nc);
            $full_network_html .= "</td>\n";
        }
        $full_network_html .= "</tr>";
    } else {
        $percent_identity = $stats["pid"];
        $rep_network_html .= "<tr>";
        if ($stats["nodes"] == 0) {
            $rep_network_html .= "<td>";
        } else {
            $rep_network_html .= "<td>";
            //if (!$is_example)
            //    $rep_network_html .= get_ssn_dl_btn($web_path);
            $rep_network_html .= get_ssn_dl_btn($idx, "Download ZIP");
            if ($has_nc)
                $rep_network_html .= get_nc_dl_btn($idx);
            $rep_network_html .= "</td>\n";
        }
        $rep_network_html .= "<td>" . $percent_identity . "</td>\n";
        if ($stats["nodes"] == 0) {
            $rep_network_html .= "<td colspan='4'>The output file was too large (edges=" . 
                number_format($stats["edges"], 0) . ") to be generated by EST.</td>\n";
        } else {
            $rep_network_html .= "<td>" . number_format($stats["nodes"], 0) . "</td>\n";
            $rep_network_html .= "<td>" . number_format($stats["edges"], 0) . "</td>\n";
            //$rep_network_html .= "<td>" . functions::bytes_to_megabytes($size, 0) . " MB</td>\n";
            if (!$is_example) {
                $rep_network_html .= "<td>";
                $rep_network_html .= make_split_button($gnt_args, !$has_nc);
                $rep_network_html .= "</td>\n";
            }
        }
        $rep_network_html .= "</tr>";
    }
}

$stepa_link = functions::get_web_root() . "/index.php#colorssn";
$gnt_link = functions::get_gnt_web_root();

$job_name = $generate->get_job_name();
$network_name = $analysis->get_name();

$blast_evalue_file = "";
if (blast::create_type() == $gen_type) {
    $file_info = $analysis->get_blast_evalue_file();
    $blast_evalue_file = $file_info["path"];
}

?>	

<h2>Download Network Files</h2>

<?php if ($job_name) { ?><h4 class="job-display">Submission Name: <b><?php echo $job_name; ?></b></h4><?php } ?>
<?php if ($network_name) { ?><h4 class="job-display">Network Name: <b><?php echo $network_name; ?></b></h4><?php } ?>

<div class="tabs-efihdr tabs">
    <ul>
        <li class="ui-tabs-active"><a href="#info">SSN Overview</a></li>
        <li><a href="#results">Network Files</a></li>
<?php if ($blast_evalue_file) { ?>
        <li><a href="#blast">BLAST Information</a></li>
<?php } ?>
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
            <div style="float: right"><a href='<?php echo $_SERVER['PHP_SELF'] . "?id=$id&key=$key&analysis_id=$analysis_id&as-table=1$ex_param" ?>'><button class='normal'>Download Information</button></a></div>
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
            <?php echo global_header::get_global_citation(); ?>
            <p>
            The panels below provide files for full and representative node SSNs for download
            with the indicated numbers of nodes and edges. As an approximate guide, SSNs with
            ~2M edges can be opened with 16 GB RAM, ~5M edges can be opened with 32 GB RAM,
            ~10M edges can be opened with 64 GB RAM, ~20M edges can be opened with 128 GB RAM,
            ~40M edges can be opened with 256 GB RAM, and ~120M edges can be opened with 768 GB RAM.
            </p>
            <p>
            Files may be transferred to the Genome Neighborhood Tool (GNT), the Color SSN utility,
            <?php echo $has_nc ? "or" : ""; ?>
            the Cluster Analysis 
            utility<?php echo !$has_nc ? ", or the Neighborhood Connectivity utility" : ""; ?>.
            </p>
            <h4>Full Network <a href="tutorial_download.php" class="question" target="_blank">?</a></h4>
            <p>Each node in the network represents a single protein sequence.
            <?php if (!$full_network_html) { ?>
                <p><b>The output file was too large (edges=<?php echo $full_edge_count; ?>) to be generated by EST.  Please use a repnode below or choose a different alignment score.</b></p>
            <?php } else { ?>
                <table width="100%" class="pretty tbl-center">
                <thead>
                <tr>
                    <th></th>
                    <th># Nodes</th>
                    <th># Edges</th>
<!--                    <th>File Size (MB)</th>-->
<?php if (!$is_example) { ?>
                    <th></th>
<?php } ?>
                </thead>
            
                <tbody>
                <?php echo $full_network_html; ?>
                </tbody>
                </table>
            <?php } ?>

<?php if ($has_repnode) { ?>        
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
            <table width="100%" class="pretty tbl-center">
            <thead>
                <th></th>
                <th>% ID</th>
                <th># Nodes</th>
                <th># Edges</th>
<!--                <th>File Size (MB)</th>-->
<?php if (!$is_example) { ?>
                <th></th>
<?php } ?>
            </thead>
        
            <tbody>
            <?php echo $rep_network_html; ?>
            </tbody>
            </table>
        
            <div style="margin-top: 10px; float: right;">
                <a href="<?php echo $_SERVER['PHP_SELF'] . "?id=$id&key=$key&analysis_id=$analysis_id&stats-as-table=1$ex_param" ?>"><button class='mini'>Download Network Statistics as Table</button></a>
            </div>
            <div style="clear:both"></div>
<?php } ?>

            <center><p><a href="tutorial_cytoscape.php">New to Cytoscape?</a></p></center>
        </div>

<?php if ($blast_evalue_file) { ?>
        <div id="blast">
            <div>A list of BLAST hits, e-values, and descriptions is provided in this file:</div>
            <div style="margin-top: 10px;">
                <a href="download.php?dl=BLASTHITS&<?php echo "analysis_id=$analysis_id&key=$key&id=$id"; ?>"><button class="mini">Download file</button></a>
            </div>
        </div>
<?php } ?>
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

require_once(__DIR__."/inc/footer.inc.php");







function get_ssn_dl_btn($ssn_idx, $btn_text = "Download") {
    global $analysis_id, $key;
    //return " <span style=\"padding-left:15px\"></span><a href=\"$web_path.zip\" title=\"$btn_text\"><i class=\"fas fa-file-archive\"></i></a>";
    $web_path = "download.php?aid=$analysis_id&key=$key&dl=ssn&idx=$ssn_idx";
    return " <a href='$web_path'><button class='mini'>$btn_text</button></a>";
}

function get_nc_dl_btn($ssn_idx) {
    global $analysis_id, $key;
    $nc_web_path = "graphs.php?aid=$analysis_id&key=$key&net=$ssn_idx&atype=NC";
    return " <span style=\"padding-left:15px\"></span><a href='$nc_web_path'><button class='mini'>Download NC color scale</button></a>";
}

function make_split_button($args, $show_nc_xfer = true) {
    $nc_link_text = $show_nc_xfer ? "<a href=\"index.php?mode=nc&$args\">Neighborhood Connectivity</a>" : "";
    return <<<HTML
<button class="mini">Transfer To:</button><div class="btn-split"><button class="mini"><i class="fa fa-caret-down"></i></button>
<div class="btn-split-content"><a href="index.php?mode=color&$args">Color SSN</a><a href="index.php?mode=cluster&$args">Cluster Analysis</a>$nc_link_text<a href="../efi-gnt/index.php?$args">Genome Neighborhood Tool</a></div></div>
HTML;
}


function add_analysis_summary_table($analysis, $table) {
    $use_advanced_options = global_settings::advanced_options_enabled();

    $stats = $analysis->get_network_stats(0);

    $network_name = $analysis->get_name();
    $time_window = $analysis->get_time_period();
    $a_id = $analysis->get_id();
    $num_filt_seq = (isset($stats) && isset($stats["nodes"])) ? $stats["nodes"] : 0;
    $tax_search = $analysis->get_tax_search_formatted();
    $remove_fragments = $analysis->get_remove_fragments();

    $table->add_row("Analysis Job Number", $a_id);
    $table->add_row("Network Name", $network_name);
    $table->add_row("Alignment Score", $analysis->get_filter_value());
    if ($use_advanced_options)
        $table->add_row("Filter", $analysis->get_filter_name());
    if ($tax_search)
        $table->add_row("Taxonomy Categories", $tax_search);
    if ($remove_fragments)
        $table->add_row("Fragments", "Removed in analysis step");
    $table->add_row("Minimum Length", number_format($analysis->get_min_length()));
    $table->add_row("Maximum Length", number_format($analysis->get_max_length()));
    if ($use_advanced_options) {
        $table->add_row("CD-HIT Method", $analysis->get_cdhit_method_nice());
        $min_method = $analysis->get_min_option_nice();
        if ($min_method)
            $table->add_row("Minimize", $min_method);
    }
    if ($num_filt_seq)
        $table->add_row("Total Number of Sequences After Filtering", number_format($num_filt_seq));
}


?>

