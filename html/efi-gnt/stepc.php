<?php 
require_once(__DIR__."/../../init.php");

require_once(__GNT_DIR__."/includes/main.inc.php");

use \efi\ui;
use \efi\table_builder;
use \efi\gnt\gnn;
use \efi\gnt\gnn_example;
use \efi\gnt\gnt_ui;
use \efi\gnt\functions;


$is_example = isset($_GET["x"]);

if ((isset($_GET['id'])) && (is_numeric($_GET['id']))) {
    if ($is_example) {
        $gnn = new gnn_example($db, $_GET['id']);
    } else {
        $gnn = new gnn($db, $_GET['id']);
    }

    $gnnId = $gnn->get_id();
    $gnnKey = $gnn->get_key();
    if ($gnnKey != $_GET['key']) {
        error500("Unable to find the requested job.");
    } elseif (!$is_example && $gnn->is_expired()) {
        error404("That job has expired and doesn't exist anymore.");
    }
}
else {
    error500("Unable to find the requested job.");
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



$ssnFile = $gnn->get_relative_color_ssn();
$ssnZipFile = $gnn->get_relative_color_ssn_zip_file();
$ssnFilesize = format_file_size($gnn->get_color_ssn_filesize());
$ssnZipFilesize = format_file_size($gnn->get_color_ssn_zip_filesize());
$gnnFile = $gnn->get_relative_gnn();
$gnnZipFile = $gnn->get_relative_gnn_zip_file();
$gnnFilesize = format_file_size($gnn->get_gnn_filesize());
$gnnZipFilesize = format_file_size($gnn->get_gnn_zip_filesize());
$pfamFile = $gnn->get_relative_pfam_hub();
$pfamZipFile = $gnn->get_relative_pfam_hub_zip_file();
$pfamFilesize = format_file_size($gnn->get_pfam_hub_filesize());
$pfamZipFilesize = format_file_size($gnn->get_pfam_hub_zip_filesize());

$uniprotIdDataZip = $gnn->get_relative_cluster_data_zip_file(gnn::SEQ_NO_DOMAIN, gnn::SEQ_UNIPROT);
$uniprotIdDataZipFilesize = format_file_size($gnn->get_file_size($uniprotIdDataZip));
$uniprotDomIdDataZip = $gnn->get_relative_cluster_data_zip_file(gnn::SEQ_DOMAIN, gnn::SEQ_UNIPROT_DOMAIN);
$uniprotDomIdDataZipFilesize = format_file_size($gnn->get_file_size($uniprotDomIdDataZip));
$uniref50IdDataZip = $gnn->get_relative_cluster_data_zip_file(gnn::SEQ_NO_DOMAIN, gnn::SEQ_UNIREF50);
$uniref50IdDataZipFilesize = format_file_size($gnn->get_file_size($uniref50IdDataZip));
$uniref50DomainIdDataZip = $gnn->get_relative_cluster_data_zip_file(gnn::SEQ_DOMAIN, gnn::SEQ_UNIREF50);
$uniref50DomainIdDataZipFilesize = format_file_size($gnn->get_file_size($uniref50DomainIdDataZip));
$uniref90IdDataZip = $gnn->get_relative_cluster_data_zip_file(gnn::SEQ_NO_DOMAIN, gnn::SEQ_UNIREF90);
$uniref90IdDataZipFilesize = format_file_size($gnn->get_file_size($uniref90IdDataZip));
$uniref90DomainIdDataZip = $gnn->get_relative_cluster_data_zip_file(gnn::SEQ_DOMAIN, gnn::SEQ_UNIREF90);
$uniref90DomainIdDataZipFilesize = format_file_size($gnn->get_file_size($uniref90DomainIdDataZip));

$pfamDataZip = $gnn->get_relative_pfam_data_zip_file();
$pfamDataZipFilesize = $gnn->get_pfam_data_zip_filesize();
$allPfamDataZip = $gnn->get_relative_all_pfam_data_zip_file();
$allPfamDataZipFilesize = $gnn->get_all_pfam_data_zip_filesize();
$splitPfamDataZip = $gnn->get_relative_split_pfam_data_zip_file();
$splitPfamDataZipFilesize = $gnn->get_split_pfam_data_zip_filesize();
$allSplitPfamDataZip = $gnn->get_relative_all_split_pfam_data_zip_file();
$allSplitPfamDataZipFilesize = $gnn->get_all_split_pfam_data_zip_filesize();
$warningFile = $gnn->get_relative_warning_file();
$warningFilesize = $gnn->get_warning_filesize();
$idTableFile = $gnn->get_relative_id_table_file();
$idTableFilesize = $gnn->get_id_table_filesize();
$idDomTableFile = $gnn->get_relative_id_table_file(true);
$idDomTableFilesize = $gnn->get_id_table_filesize(true);
$pfamNoneZip = $gnn->get_relative_pfam_none_zip_file();
$pfamNoneZipFilesize = $gnn->get_pfam_none_zip_filesize();

$fastaUniProtZip = $gnn->get_relative_fasta_zip_file(gnn::SEQ_NO_DOMAIN, gnn::SEQ_UNIPROT);
$fastaUniProtZipFilesize = $gnn->get_file_size($fastaUniProtZip);
$fastaUniProtDomainZip = $gnn->get_relative_fasta_zip_file(gnn::SEQ_DOMAIN, gnn::SEQ_UNIPROT);
$fastaUniProtDomainZipFilesize = $gnn->get_file_size($fastaUniProtDomainZip);
$fastaUniRef90Zip = $gnn->get_relative_fasta_zip_file(gnn::SEQ_NO_DOMAIN, gnn::SEQ_UNIREF90);
$fastaUniRef90ZipFilesize = $gnn->get_file_size($fastaUniRef90Zip);
$fastaUniRef90DomainZip = $gnn->get_relative_fasta_zip_file(gnn::SEQ_DOMAIN, gnn::SEQ_UNIREF90);
$fastaUniRef90DomainZipFilesize = $gnn->get_file_size($fastaUniRef90DomainZip);
$fastaUniRef50Zip = $gnn->get_relative_fasta_zip_file(gnn::SEQ_NO_DOMAIN, gnn::SEQ_UNIREF50);
$fastaUniRef50ZipFilesize = $gnn->get_file_size($fastaUniRef50Zip);
$fastaUniRef50DomainZip = $gnn->get_relative_fasta_zip_file(gnn::SEQ_DOMAIN, gnn::SEQ_UNIREF50);
$fastaUniRef50DomainZipFilesize = $gnn->get_file_size($fastaUniRef50DomainZip);

$coocTableFile = $gnn->get_relative_cooc_table_file();
$coocTableFilesize = $gnn->get_cooc_table_filesize();
$hubCountFile = $gnn->get_relative_hub_count_file();
$hubCountFilesize = $gnn->get_hub_count_filesize();
$hasDiagrams = $gnn->does_job_have_arrows();
$diagramFile = $gnn->get_relative_diagram_data_file();
$diagramZipFile = $gnn->get_relative_diagram_zip_file();
$diagramFilesize = format_file_size($gnn->get_diagram_data_filesize());
$diagramZipFilesize = format_file_size($gnn->get_diagram_zip_filesize());
$clusterSizesFile = $gnn->get_relative_cluster_sizes_file();
$clusterSizesFilesize = $gnn->get_cluster_sizes_filesize();
$swissprotClustersDescFile = $gnn->get_relative_swissprot_desc_file(true);
$swissprotClustersDescFilesize = $gnn->get_swissprot_desc_filesize(true);
$swissprotSinglesDescFile = $gnn->get_relative_swissprot_desc_file(false);
$swissprotSinglesDescFilesize = $gnn->get_swissprot_desc_filesize(false);

$otherFiles = array();

if ($idTableFile or $pfamDataZip or $splitPfamDataZip or $allPfamDataZip or $allSplitPfamDataZip)
    array_push($otherFiles, array("Mapping Tables"));
if ($idTableFile)
    array_push($otherFiles, array($idTableFile, format_file_size($idTableFilesize), "UniProt ID-Color-Cluster number"));
if ($idDomTableFilesize !== false)
    array_push($otherFiles, array($idDomTableFile, format_file_size($idDomTableFilesize), "UniProt ID-Color-Cluster (domain) number"));
if ($pfamDataZip)
    array_push($otherFiles, array($pfamDataZip, format_file_size($pfamDataZipFilesize), "Neighbor Pfam domain fusions at specified minimal co-occurrence frequency"));
if ($splitPfamDataZip)
    array_push($otherFiles, array($splitPfamDataZip, format_file_size($splitPfamDataZipFilesize), "Neighbor Pfam domains at specified minimal co-occurrence frequency"));
if ($allPfamDataZip)
    array_push($otherFiles, array($allPfamDataZip, format_file_size($allPfamDataZipFilesize), "Neighbor Pfam domain fusions at 0% minimal co-occurrence frequency"));
if ($allSplitPfamDataZip)
    array_push($otherFiles, array($allSplitPfamDataZip, format_file_size($allSplitPfamDataZipFilesize), "Neighbor Pfam domains at 0% minimal co-occurrence frequency"));

if ($uniprotIdDataZip or $uniref50IdDataZip or $uniref90IdDataZip or $fastaUniProtZip or $pfamNoneZip)
    array_push($otherFiles, array("Data Files per SSN Cluster"));
if ($uniprotIdDataZip)
    array_push($otherFiles, array($uniprotIdDataZip, format_file_size($uniprotIdDataZipFilesize), "UniProt ID lists per cluster"));
if ($uniprotDomIdDataZip)
    array_push($otherFiles, array($uniprotDomIdDataZip, format_file_size($uniprotDomIdDataZipFilesize), "UniProt ID lists per cluster (with domain_"));
if ($uniref90IdDataZip)
    array_push($otherFiles, array($uniref90IdDataZip, format_file_size($uniref90IdDataZipFilesize), "UniRef90 ID lists per cluster"));
if ($uniref90DomainIdDataZip)
    array_push($otherFiles, array($uniref90DomainIdDataZip, format_file_size($uniref90DomainIdDataZipFilesize), "UniRef90 ID lists per cluster (with domain)"));
if ($uniref50IdDataZip)
    array_push($otherFiles, array($uniref50IdDataZip, format_file_size($uniref50IdDataZipFilesize), "UniRef50 ID lists per cluster"));
if ($uniref50DomainIdDataZip)
    array_push($otherFiles, array($uniref50DomainIdDataZip, format_file_size($uniref50DomainIdDataZipFilesize), "UniRef50 ID lists per cluster (with domain)"));

$uniprotText = ($fastaUniRef90ZipFilesize || $fastaUniRef50ZipFilesize) ? "UniProt" : "";
if ($fastaUniProtZipFilesize)
    array_push($otherFiles, array($fastaUniProtZip, format_file_size($fastaUniProtZipFilesize), "FASTA files per $uniprotText cluster"));
if ($fastaUniProtDomainZipFilesize)
    array_push($otherFiles, array($fastaUniProtDomainZip, format_file_size($fastaUniProtDomainZipFilesize), "FASTA files per $uniprotText cluster (with domain)"));
if ($fastaUniRef90ZipFilesize)
    array_push($otherFiles, array($fastaUniRef90Zip, format_file_size($fastaUniRef90ZipFilesize), "FASTA files per UniRef90 cluster"));
if ($fastaUniRef90DomainZipFilesize)
    array_push($otherFiles, array($fastaUniRef90DomainZip, format_file_size($fastaUniRef90DomainZipFilesize), "FASTA files per UniRef90 cluster (with domain)"));
if ($fastaUniRef50ZipFilesize)
    array_push($otherFiles, array($fastaUniRef50Zip, format_file_size($fastaUniRef50ZipFilesize), "FASTA files per UniRef50 cluster"));
if ($fastaUniRef50DomainZipFilesize)
    array_push($otherFiles, array($fastaUniRef50DomainZip, format_file_size($fastaUniRef50DomainZipFilesize), "FASTA files per UniRef50 cluster (with domain)"));
if ($pfamNoneZip)
    array_push($otherFiles, array($pfamNoneZip, format_file_size($pfamNoneZipFilesize), "Neighbors without Pfam assigned"));

if ($warningFile or $coocTableFile or $hubCountFile or $clusterSizesFilesize !== false or $swissprotClustersDescFilesize !== false or $swissprotSinglesDescFilesize !== false)
    array_push($otherFiles, array("Miscellaneous Files"));
if ($warningFile)
    array_push($otherFiles, array($warningFile, format_file_size($warningFilesize), "No matches/no neighbors file"));
if ($coocTableFile)
    array_push($otherFiles, array($coocTableFile, format_file_size($coocTableFilesize), "Pfam family/cluster co-occurrence table file"));
if ($hubCountFile)
    array_push($otherFiles, array($hubCountFile, format_file_size($hubCountFilesize), "GNN hub cluster sequence count file"));
if ($clusterSizesFilesize !== false)
    array_push($otherFiles, array($clusterSizesFile, format_file_size($clusterSizesFilesize), "Cluster size file"));
if ($swissprotClustersDescFilesize !== false)
    array_push($otherFiles, array($swissprotClustersDescFile, format_file_size($swissprotClustersDescFilesize), "SwissProt annotations per SSN cluster"));
if ($swissprotSinglesDescFilesize !== false)
    array_push($otherFiles, array($swissprotSinglesDescFile, format_file_size($swissprotSinglesDescFilesize), "SwissProt annotations by singleton"));

$gnn_name = $gnn->get_gnn_name();
$useDiagramsV3 = $gnn->get_diagram_version() >= 3;

$file_size_col_hdr = $is_example ? "(Zipped MB)" : "(Unzipped/Zipped MB)";
$ex_param = $is_example ? "&x=1" : "";

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
            <div style="float: right"><a href='<?php echo $_SERVER['PHP_SELF'] . "?id=$gnnId&key=$gnnKey&as-table=1" ?>'><button class="normal">Download Information</button></a></div>
            <div style="clear: both"></div>
        </div>

        
        <!-- DOWNLOAD NETWORKS -->
        <div id="results" class="ui-tabs-active">
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
                            <?php if (!$is_example) { ?>
                            <a href="<?php echo "$baseUrl/$ssnFile" ?>"><button class="mini">Download</button></a>
                            <?php } ?>
                            <?php if ($ssnZipFile) { ?>
                            <a href="<?php echo "$baseUrl/$ssnZipFile"; ?>"><button class="mini">Download ZIP</button></a>
                            <?php } ?>
                        </td>
                        <td><?php echo number_format($gnn->get_ssn_nodes()); ?></td>
                        <td><?php echo number_format($gnn->get_ssn_edges()); ?></td>
                        <td>
                            <?php
                            if ($is_example) {
                                echo "$ssnZipFilesize MB";
                            } else {
                                echo "$ssnFilesize MB";
                                if ($ssnZipFilesize)
                                    echo "/ $ssnZipFilesize MB";
                            }
                            ?>
                        </td>
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
                            <?php if (!$is_example) { ?>
                            <a href="<?php echo "$baseUrl/$gnnFile"; ?>"><button class="mini">Download</button></a>
                            <?php } ?>
                            <?php if ($gnnZipFile) { ?>
                            <a href="<?php echo "$baseUrl/$gnnZipFile"; ?>"><button class="mini">Download ZIP</button></a>
                            <?php } ?>
                        </td>
                        <td>
                            <?php
                            if ($is_example) {
                                echo "$gnnZipFilesize MB";
                            } else {
                                echo "$gnnFilesize MB";
                                if ($gnnZipFilesize)
                                    echo "/ $gnnZipFilesize MB";
                            }
                            ?>
                        </td>
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
                            <?php if (!$is_example) { ?>
                            <a href="<?php echo "$baseUrl/$pfamFile"; ?>"><button class="mini">Download</button></a>
                            <?php } ?>
                            <?php if ($pfamZipFile) { ?>
                            <a href="<?php echo "$baseUrl/$pfamZipFile"; ?>"><button class="mini">Download ZIP</button></a>
                            <?php } ?>
                        </td>
                        <td>
                            <?php
                            if ($is_example) {
                                echo "$pfamZipFilesize MB";
                            } else {
                                echo "$pfamFilesize MB";
                                if ($pfamZipFilesize)
                                    echo "/ $pfamZipFilesize MB";
                            }
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        
            <?php if ($hasDiagrams) { ?>
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
                            <?php if ($useDiagramsV3) { ?>
                            <a href="view_diagrams_v3.php?gnn-id=<?php echo $gnnId; ?>&key=<?php echo $gnnKey; ?><?php echo $ex_param; ?>" target="_blank"><button class="mini">View diagrams</button></a>
                            <?php } else { ?>
                            <a href="view_diagrams.php?gnn-id=<?php echo $gnnId; ?>&key=<?php echo $gnnKey; ?>" target="_blank"><button class="mini">View diagrams</button></a>
                            <?php } ?>
                        </td>
                        <td colspan="2">
                            Opens GND explorer in a new tab.
                        </td>
                    </tr>
                    <tr style="text-align:center;">
                        <td class="button-col">
                            <?php if (!$is_example) { ?>
                            <a href="<?php echo "$baseUrl/$diagramFile"; ?>"><button class="mini">Download</button></a>
                            <?php } ?>
                            <?php if ($diagramZipFilesize) { ?>
                            <a href="<?php echo "$baseUrl/$diagramZipFile"; ?>"><button class="mini">Download ZIP</button></a>
                            <?php } ?>
                        </td>
                        <td>
                            Diagram data for later review
                        </td>
                        <td>
                            <?php
                            if ($is_example) {
                                echo "$diagramZipFilesize MB";
                            } else {
                                echo "$diagramFilesize MB";
                                if ($diagramZipFilesize)
                                    echo "/ $diagramZipFilesize MB";
                            }
                            ?>
                        </td>
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
                    foreach ($otherFiles as $info) {
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
                            $btnText = strpos($info[0], ".zip") > 0 ? "Download All (ZIP)" : "Download";
                            echo <<<HTML
                    <tr style="text-align:center;">
                        <td>
                            <a href="$baseUrl/$info[0]"><button class="mini">$btnText</button></a>
                        </td>
                        <td>$info[2]</td>
                        <td>$info[1] MB</td>
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
            <input type="hidden" id="parent_id" value="<?php echo $gnnId; ?>">
            <input type="hidden" id="parent_key" value="<?php echo $gnnKey; ?>">

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
                uploadSsnFilter('ssn_file','upload_form','progress_number','progress_bar','ssn_message',email,'filter-btn');
            }
        });

        $(".tabs").tabs();
    });
</script>
<script src="<?php echo $SiteUrlPrefix; ?>/js/custom-file-input.js" type="text/javascript"></script>

<?php
function format_file_size($size) {
    $mb = round($size, 0);
    if ($mb == 0)
        return "&lt;1";
    return $mb;
}
?>

<?php require_once(__DIR__."/inc/footer.inc.php"); ?>

