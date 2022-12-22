<?php 
require_once(__DIR__."/../../init.php");

use \efi\ui;
use \efi\table_builder;
use \efi\global_settings;
use \efi\global_header;
use \efi\gnt\gnn;
use \efi\gnt\gnn_example;
use \efi\gnt\gnt_ui;
use \efi\gnt\functions;
use \efi\gnt\settings;
use \efi\training\example_config;
use \efi\sanitize;
use \efi\file_types;


$is_example = example_config::is_example();

$gnn_id = sanitize::validate_id("id", sanitize::GET);
$key = sanitize::validate_key("key", sanitize::GET);

if ($gnn_id === false || $key === false) {
    error500("Unable to find the requested job.");
}

if ($is_example) {
    $gnn = new gnn_example($db, $gnn_id, $is_example);
} else {
    $gnn = new gnn($db, $gnn_id);
}

$gnn_key = $gnn->get_key();

if ($gnn_key != $key) {
    error500("Unable to find the requested job.");
} elseif (!$is_example && $gnn->is_expired()) {
    error404("That job has expired and doesn't exist anymore.");
}

$baseUrl = settings::get_web_address();



$tableFormat = "html";
if (isset($_GET["as-table"])) {
    $tableFormat = "tab";
}
$table = new table_builder($tableFormat);

$metadata = $gnn->get_metadata();
foreach ($metadata as $row) {
    if (strpos($row[1], "<a") !== false)
        $table->add_row_with_html($row[0], $row[1]);
    else
        $table->add_row($row[0], $row[1]);
}


$tableString = $table->as_string();

if (isset($_GET["as-table"])) {
    $tableFilename = functions::safe_filename($gnn->get_filename()) . "_summary.txt";
    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $tableFilename . '"');
    header('Content-Length: ' . strlen($tableString));
    ob_clean();
    echo $tableString;
    exit(0);
}


$allow_regenerate = !$gnn->has_parent() && !$is_example;
$cooccurrence = $gnn->get_cooccurrence();





$ur50_fasta = $gnn->get_results_file_info(file_types::FT_fasta_ur50);
$ur90_fasta = $gnn->get_results_file_info(file_types::FT_fasta_ur90);
$uniprot_text = ($ur50_fasta !== false || $ur90_fasta !== false) ? "UniProt" : "";

$has_diagrams = $gnn->does_job_have_arrows();

$other_files = array();

$mapping_files = array();
add_file_type($mapping_files, array(file_types::FT_gnn_mapping, "UniProt ID-Color-Cluster number"), $gnn);
add_file_type($mapping_files, array(file_types::FT_gnn_mapping_dom, "UniProt ID-Color-Cluster (domain) number"), $gnn);
add_file_type($mapping_files, array(file_types::FT_pfam_dom, "Neighbor Pfam domain fusions at specified minimal co-occurrence frequency"), $gnn);
add_file_type($mapping_files, array(file_types::FT_split_pfam_dom, "Neighbor Pfam domains at specified minimal co-occurrence frequency"), $gnn);
add_file_type($mapping_files, array(file_types::FT_all_pfam_dom, "Neighbor Pfam domain fusions at 0% minimal co-occurrence frequency"), $gnn);
add_file_type($mapping_files, array(file_types::FT_split_all_pfam_dom, "Neighbor Pfam domains at 0% minimal co-occurrence frequency"), $gnn);
if (count($mapping_files)) {
    $other_files = array_merge($other_files, array(array("Mapping Tables")), $mapping_files);
}

$data_files = array();
add_file_type($data_files, array(file_types::FT_ids, "UniProt ID lists per cluster"), $gnn);
add_file_type($data_files, array(file_types::FT_dom_ids, "UniProt ID lists per cluster (with domain_"), $gnn);
add_file_type($data_files, array(file_types::FT_ur90_ids, "UniRef90 ID lists per cluster"), $gnn);
add_file_type($data_files, array(file_types::FT_ur90_dom_ids, "UniRef90 ID lists per cluster (with domain)"), $gnn);
add_file_type($data_files, array(file_types::FT_ur50_ids, "UniRef50 ID lists per cluster"), $gnn);
add_file_type($data_files, array(file_types::FT_ur50_dom_ids, "UniRef50 ID lists per cluster (with domain)"), $gnn);
if (count($data_files)) {
    $other_files = array_merge($other_files, array(array("Data Files per SSN Cluster")), $data_files);
}

$fasta_files = array();
add_file_type($fasta_files, array(file_types::FT_fasta, "FASTA files per $uniprot_text cluster"), $gnn);
add_file_type($fasta_files, array(file_types::FT_fasta_dom, "FASTA files per $uniprot_text cluster (with domain)"), $gnn);
add_file_type($fasta_files, array(file_types::FT_fasta_ur90, "FASTA files per UniRef90 cluster"), $gnn);
add_file_type($fasta_files, array(file_types::FT_fasta_dom_ur90, "FASTA files per UniRef90 cluster (with domain)"), $gnn);
add_file_type($fasta_files, array(file_types::FT_fasta_ur50, "FASTA files per UniRef50 cluster"), $gnn);
add_file_type($fasta_files, array(file_types::FT_fasta_dom_ur50, "FASTA files per UniRef50 cluster (with domain)"), $gnn);
add_file_type($fasta_files, array(file_types::FT_pfam_nn, "Neighbors without Pfam assigned"), $gnn);
$other_files = array_merge($other_files, $fasta_files);

$misc_files = array();
add_file_type($misc_files, array(file_types::FT_gnn_nn, "No matches/no neighbors file"), $gnn);
add_file_type($misc_files, array(file_types::FT_cooc_table, "Pfam family/cluster co-occurrence table file"), $gnn);
add_file_type($misc_files, array(file_types::FT_hub_count, "GNN hub cluster sequence count file"), $gnn);
add_file_type($misc_files, array(file_types::FT_sizes, "Cluster size file"), $gnn);
add_file_type($misc_files, array(file_types::FT_sp_clusters, "SwissProt annotations per SSN cluster"), $gnn);
add_file_type($misc_files, array(file_types::FT_sp_singletons, "SwissProt annotations by singleton"), $gnn);
if (count($misc_files)) {
    $other_files = array_merge($other_files, array(array("Miscellaneous Files")), $misc_files);
}

$gnn_name = $gnn->get_gnn_name();

//$file_size_col_hdr = $is_example ? "(Zipped MB)" : "(Unzipped/Zipped MB)";
$file_size_col_hdr = "(Zipped MB)";
$ex_param = $is_example ? "&x=".$is_example : "";

$dl_base = "download.php?id=$gnn_id&key=$key$ex_param&dl=gnn";

require_once(__DIR__."/inc/header.inc.php");

?>

<h2>Results</h2>

<h4 class="job-display">Submitted Network Name: <b><?php echo $gnn_name; ?></b></h4>

<div class="tabs-efihdr tabs">
    <ul class="tab-headers">
        <li class="ui-tabs-active"><a href="#info">Submission Summary</a></li>
        <li><a href="#results">Networks and GND</a></li>
        <li><a href="#other">Other Files</a></li>
<?php if ($allow_regenerate) { ?>
        <li><a href="#regenerate">Regenerate GNN</a></li>
<?php } ?>
    </ul>

    <div class="tab-content">

        <!-- NETWORK INFORMATION -->
        <div id="info" class="">
            <p>The parameters for computing the GNN and associated files are summarized in the table.</p>
            <table width="100%" style="margin-top: 10px" class="pretty">
                <tbody>
                    <?php echo $tableString; ?>
                </tbody>
            </table>
            <div style="float: right"><a href='<?php echo $_SERVER['PHP_SELF'] . "?id=$gnn_id&key=$gnn_key&as-table=1" ?>'><button class="normal">Download Information</button></a></div>
            <div style="clear: both"></div>
        </div>

        
        <!-- DOWNLOAD NETWORKS -->
        <div id="results" class="ui-tabs-active">
            <?php echo global_header::get_global_citation(); ?>

            <h4>Colored Sequence Similarity Network (SSN)</h4>
            <p>
            Each cluster in the submitted SSN has been identified and assigned a unique number and color.
            Node attributes for "Neighbor Pfam Families" and "Neighbor InterPro Families" have been added.
            </p>
        
            <table width="100%" class="pretty">
                <thead>
                    <th></th>
                    <th># Nodes</th>
                    <th># Edges</th>
                    <th>File Size <?php echo $file_size_col_hdr; ?></th>
                </thead>
                <tbody>
                    <tr style='text-align:center;'>
                        <td class="button-col">
                            <a href="<?php echo "$dl_base&ft=" . file_types::FT_gnn_ssn; ?>"><button class="mini">Download ZIP</button></a>
                        </td>
                        <td><?php echo number_format($gnn->get_ssn_nodes()); ?></td>
                        <td><?php echo number_format($gnn->get_ssn_edges()); ?></td>
                        <td><?php echo format_size($gnn, file_types::FT_gnn_ssn); ?></td>
                    </tr>
                </tbody>
            </table>

            <h4>Genome Neighborhood Networks (GNNs)</h4>
            <p>
            GNNs provide a representation of the neighboring Pfam families for each SSN 
            cluster identified in the colored SSN. To be displayed, neighboring Pfams 
            families must be detected in the specified window and at a co-occurrence 
            frequency higher than the specified minimum.
            </p>
        
            <h5>SSN Cluster Hub-Nodes: Genome Neighborhood Network (GNN)</h5>
            <p>
            Each hub-node in the network represents a SSN cluster. The spoke nodes 
            represent Pfam families that have been identified as neighbors of the sequences 
            from the center hub.
            </p>
        
            <table width="100%" class="pretty">
                <thead>
                    <th></th>
                    <th>File Size <?php echo $file_size_col_hdr; ?></th>
                </thead>
                <tbody>
                    <tr style='text-align:center;'>
                        <td class="button-col">
                            <a href="<?php echo "$dl_base&ft=" . file_types::FT_ssn_gnn; ?>"><button class="mini">Download ZIP</button></a>
                        </td>
                        <td><?php echo format_size($gnn, file_types::FT_ssn_gnn); ?></td>
                    </tr>
                </tbody>
            </table>
        
            <h5>Pfam Family Hub-Nodes Genome Neighborhood Network (GNN)</h5>
            <p>
            Each hub-node in the network represents a Pfam family identified as a neighbor. 
            The spokes nodes represent SSN clusters that identified the Pfam family from 
            the center hub.
            </p>
        
            <table width="100%" class="pretty">
                <thead>
                    <th></th>
                    <th>File Size <?php echo $file_size_col_hdr; ?></th>
                </thead>
                <tbody>
                    <tr style='text-align:center;'>
                        <td class="button-col">
                            <a href="<?php echo "$dl_base&ft=" . file_types::FT_pfam_gnn; ?>"><button class="mini">Download ZIP</button></a>
                        </td>
                        <td><?php echo format_size($gnn, file_types::FT_pfam_gnn); ?></td>
                    </tr>
                </tbody>
            </table>
        
            <?php if ($has_diagrams) { ?>
            <h4>Genome Neighborhood Diagrams (GNDs)</h4> 
            <p>
            Diagrams representing genomic regions around the genes encoded for the 
            sequences from the submitted SSN are generated. All genes present in the 
            specified window can be visualized
            (no minimal co-occurrence frequency filter or neighborhood size threshold is applied).
            Diagram data can be downloaded in .sqlite file format for later review in the View Saved Diagrams tab.
            </p>
        
            <table width="100%" class="pretty">
                <thead>
                    <th>Action</th>
                    <th></th>
                    <th>File Size <?php echo $file_size_col_hdr; ?></th>
                </thead>
                <tbody>
                    <tr style='text-align:center;'>
                        <td class="button-col">
                            <a href="view_diagrams_v3.php?gnn-id=<?php echo $gnn_id; ?>&key=<?php echo $gnn_key; ?><?php echo $ex_param; ?>" target="_blank"><button class="mini">View diagrams</button></a>
                        </td>
                        <td colspan="2">
                            Opens GND explorer in a new tab.
                        </td>
                    </tr>
                    <tr style="text-align:center;">
                        <td class="button-col">
                            <a href="<?php echo "$dl_base&ft=" . file_types::FT_gnd; ?>"><button class="mini">Download ZIP</button></a>
                        </td>
                        <td>
                            Diagram data for later review
                        </td>
                        <td><?php echo format_size($gnn, file_types::FT_gnd); ?></td>
                    </tr>
                </tbody>
            </table>
            <?php } ?>
        </div>


        <!-- OTHER DOWNLOAD FILES -->
        <div id="other" class="">
            <h4>Mapping Tables, FASTA Files, ID Lists, and Supplementary Files</h4>
            <table width="100%" class="pretty no-border">
<?php
                    $first = true;
                    foreach ($other_files as $info) {
                        if (count($info) == 1) {
                            if (!$first)
                                echo "                </tbody>\n";
                            $first = false;
                            echo <<<HTML
                <tbody>
                    <tr style="text-align:center;">
                        <td colspan="3" class="file-header-row">
                            $info[0]
                        </td>
                    </tr>
                </tbody>
                <tbody class="file-group">

HTML;
                        } else {
                            $type = $info[0];
                            $btn_text = file_types::ext($type) == "zip" ? "Download All (ZIP)" : "Download";
                            echo <<<HTML
                    <tr style="text-align:center;">
                        <td>
                        <a href="$dl_base&ft=$type"><button class="mini">$btn_text</button></a>
                        </td>
                        <td>$info[1]</td>
                        <td>$info[2] MB</td>
                    </tr>

HTML;
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- REGENERATE GNN -->
<?php
// Regen stuff

if ($allow_regenerate) {

?>
        <div id="regenerate" class="">
            <?php
                $child_jobs = $gnn->get_child_jobs();
                if (count($child_jobs) > 0) {
                    echo <<<HTML
                        <h4>Related Regenerated GNT Jobs</h4>
                        <div>
                            <ul>
HTML;
                    foreach ($child_jobs as $id => $job) {
                        $name = "N=" . $job["size"] . " Cooc=" . $job["cooccurrence"] . " Submission=<i>" . $job["filename"] . "</i>";
                        $url = "stepc.php?id=" . $id . "&key=" . $job["key"];
                        echo "<li><a href='$url'>$name</a></li>\n";
                    }
                    echo <<<HTML
                            </ul>
                        </div>
HTML;
                }
?>
            <p>
            <b>Use existing neighboring information to perform a new GNN analysis on a previously-submitted SSN.</b>
            This process is faster than performing a new GNN analysis.
            </p>
            
            <form id="upload_form" action="">
            <input type="hidden" id="parent_id" value="<?php echo $gnn_id; ?>">
            <input type="hidden" id="parent_key" value="<?php echo $gnn_key; ?>">

            <h4>Set Neighborhood Parameters</h4>
            <p>
            Specify new "Co-occurrence Percentage Lower Limit" and "Neighborhood Size".
            See above for the name of the previously-submitted SSN.
            </p>

            <p>
                <?php gnt_ui::add_neighborhood_size_setting(false); ?>
            </p>
            <p>
                <?php gnt_ui::add_cooccurrence_setting(false, $cooccurrence); ?>
            </p>

            <h4>Submit Edited SSN and Reuse GNN Information</h4>
            <p>
                Uploading a new but related network will use information saved from this GNN analysis to generate a new GNN. 
                Parameters for this analysis can be defined above.
            </p>
            <p>
                The SSN to be submitted needs to contains IDs that are present in the initial SSN submitted. Examples:
                <ul>
                    <li>Examining SSNs from the same dataset at varying alignment scores</li>
                    <li>Examining a network focusing on a single SSN cluster from the initial SNN at a different alignment score</li>
                </ul>
            </p>

            <div class="primary-input">
            <?php echo ui::make_upload_box("(Optional) SSN File:", "ssn_file", "progress_bar", "progress_number", "", "", ""); ?>
            SSNs generated by EFI-EST are compatible with GNT analysis (with the exception 
            of SSNs from the FASTA sequences without the "Read FASTA header" option), even 
            when they have been modified in Cytoscape.
            The accepted format is XGMML (or compressed XGMML as zip).
            </div>

            <p>You will receive an e-mail when your network has been processed.</p>
                
            <p>
                <center>
                <button type="button" id="filter-btn" class="dark">Filter/Regenerate GNN</button>
                </center>
            </p>
            </form>

            <div id="ssn_message">
            </div>
        </div>
<?php
}
?>
    </div>
</div>



<div id="filter-confirm" title="" style="display: none">
<p>
<span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>
Please change parameters or select a new file to upload before refiltering.
</p>    
</div>

<script>
    $(document).ready(function() {
        $("#filter-btn").click(function() {
            var origCooc = <?php echo $gnn->get_cooccurrence(); ?>;
            var origNb = <?php echo $gnn->get_size(); ?>;
            var newCooc = $("#cooccurrence").val();
            var newNb = $("#neighbor_size").val();
            var hasFile = $("#ssn_file").val().length > 0;

            if (origCooc == newCooc && origNb == newNb && !hasFile) {
                $("#filter-confirm").dialog({
                    resizable: false,
                    height: "auto",
                    width: 400,
                    modal: true,
                    buttons: {
                        Ok: function() {
                            $( this ).dialog("close");
                        },
                    }
                });
            } else {
                var email = "<?php echo $IsLoggedIn ? $IsLoggedIn : ""; ?>";
                uploadSsnFilter({fileInputId: 'ssn_file', formId: 'upload_form', progressNumId: 'progress_number', progressBarId: 'progress_bar', messageId: 'ssn_message', emailId: email, submitId: 'filter-btn'});
            }
        });

        $(".tabs").tabs();
    });
</script>
<script src="<?php echo $SiteUrlPrefix; ?>/js/custom-file-input.js" type="text/javascript"></script>

<?php



function format_size($gnn, $type) {
    $size = $gnn->get_file_size($type);
    return format_file_size($size);
}


function format_file_size($size) {
    $mb = round($size, 0);
    if ($mb == 0)
        return "&lt;1";
    return $mb;
}


function add_file_type(&$data, $info, $gnn) {
    $file_info = $gnn->get_results_file_info($info[0]);
    if ($file_info !== false)
        array_push($data, array($info[0], $info[1], format_file_size($file_info["file_size"])));
}




require_once(__DIR__."/inc/footer.inc.php");

