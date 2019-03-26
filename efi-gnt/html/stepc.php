<?php 
require_once("../includes/main.inc.php");
require_once(__BASE_DIR__ . "/libs/table_builder.class.inc.php");
require_once(__BASE_DIR__ . "/libs/ui.class.inc.php");

if ((isset($_GET['id'])) && (is_numeric($_GET['id']))) {
    $gnn = new gnn($db,$_GET['id']);
    if ($gnn->get_key() != $_GET['key']) {
        prettyError404();
    }
    elseif (time() < $gnn->get_time_completed() + settings::get_retention_days()) {
        prettyError404("That job has expired and doesn't exist anymore.");
    }
}
else {
    prettyError404();
}

$gnnId = $gnn->get_id();
$gnnKey = $gnn->get_key();
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
    $tableFilename = functions::safe_filename($gnn->get_filename()) . "_settings.txt";
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


$allow_regenerate = !$gnn->has_parent();



$ssnFile = $gnn->get_relative_color_ssn();
$ssnZipFile = $gnn->get_relative_color_ssn_zip_file();
$ssnFilesize = format_file_size($gnn->get_color_ssn_filesize());
$gnnFile = $gnn->get_relative_gnn();
$gnnZipFile = $gnn->get_relative_gnn_zip_file();
$gnnFilesize = format_file_size($gnn->get_gnn_filesize());
$pfamFile = $gnn->get_relative_pfam_hub();
$pfamZipFile = $gnn->get_relative_pfam_hub_zip_file();
$pfamFilesize = format_file_size($gnn->get_pfam_hub_filesize());
$pfamZipFilesize = format_file_size($gnn->get_pfam_hub_zip_filesize());

$uniprotIdDataZip = $gnn->get_relative_cluster_data_zip_file(gnn::SEQ_UNIPROT);
$uniprotIdDataZipFilesize = format_file_size($gnn->get_cluster_data_zip_filesize(gnn::SEQ_UNIPROT));
$uniprotDomIdDataZip = $gnn->get_relative_cluster_data_zip_file(gnn::SEQ_UNIPROT_DOMAIN);
$uniprotDomIdDataZipFilesize = format_file_size($gnn->get_cluster_data_zip_filesize(gnn::SEQ_UNIPROT_DOMAIN));
$uniref50IdDataZip = $gnn->get_relative_cluster_data_zip_file(gnn::SEQ_UNIREF50);
$uniref50IdDataZipFilesize = format_file_size($gnn->get_cluster_data_zip_filesize(gnn::SEQ_UNIREF50));
$uniref90IdDataZip = $gnn->get_relative_cluster_data_zip_file(gnn::SEQ_UNIREF90);
$uniref90IdDataZipFilesize = format_file_size($gnn->get_cluster_data_zip_filesize(gnn::SEQ_UNIREF90));

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
$fastaZip = $gnn->get_relative_fasta_zip_file();
$fastaDomainZip = $gnn->get_relative_fasta_zip_file(true);
$fastaZipFilesize = $gnn->get_fasta_zip_filesize();
$fastaDomainZipFilesize = $gnn->get_fasta_zip_filesize(true);
$coocTableFile = $gnn->get_relative_cooc_table_file();
$coocTableFilesize = $gnn->get_cooc_table_filesize();
$hubCountFile = $gnn->get_relative_hub_count_file();
$hubCountFilesize = $gnn->get_hub_count_filesize();
$hasDiagrams = $gnn->does_job_have_arrows();
$diagramFile = $gnn->get_relative_diagram_data_file();
$diagramZipFile = $gnn->get_relative_diagram_zip_file();
$diagramFileSize = format_file_size($gnn->get_diagram_data_filesize());
$diagramZipFileSize = format_file_size($gnn->get_diagram_zip_filesize());
$clusterSizesFile = $gnn->get_relative_cluster_sizes_file();
$clusterSizesFileSize = $gnn->get_cluster_sizes_filesize();
$swissprotClustersDescFile = $gnn->get_relative_swissprot_desc_file(true);
$swissprotClustersDescFileSize = $gnn->get_swissprot_desc_filesize(true);
$swissprotSinglesDescFile = $gnn->get_relative_swissprot_desc_file(false);
$swissprotSinglesDescFileSize = $gnn->get_swissprot_desc_filesize(false);

$otherFiles = array();

if ($idTableFile or $pfamDataZip or $splitPfamDataZip or $allPfamDataZip or $allSplitPfamDataZip)
    array_push($otherFiles, array("Mapping Tables"));
if ($idTableFile)
    array_push($otherFiles, array($idTableFile, format_file_size($idTableFilesize), "UniProt ID-Color-Cluster Number"));
if ($idDomTableFilesize !== false)
    array_push($otherFiles, array($idDomTableFile, format_file_size($idDomTableFilesize), "UniProt ID-Color-Cluster (Domain) Number"));
if ($pfamDataZip)
    array_push($otherFiles, array($pfamDataZip, format_file_size($pfamDataZipFilesize), "Neighbor Pfam domain fusions at specified minimal cooccurrence frequency"));
if ($splitPfamDataZip)
    array_push($otherFiles, array($splitPfamDataZip, format_file_size($splitPfamDataZipFilesize), "Neighbor Pfam domains at specified minimal cooccurrence frequency"));
if ($allPfamDataZip)
    array_push($otherFiles, array($allPfamDataZip, format_file_size($allPfamDataZipFilesize), "Neighbor Pfam domain fusions at 0% minimal cooccurrence frequency"));
if ($allSplitPfamDataZip)
    array_push($otherFiles, array($allSplitPfamDataZip, format_file_size($allSplitPfamDataZipFilesize), "Neighbor Pfam domains at 0% minimal cooccurrence frequency"));

if ($uniprotIdDataZip or $uniref50IdDataZip or $uniref90IdDataZip or $fastaZip or $pfamNoneZip)
    array_push($otherFiles, array("Data Files by Cluster"));
if ($uniprotIdDataZip)
    array_push($otherFiles, array($uniprotIdDataZip, format_file_size($uniprotIdDataZipFilesize), "UniProt ID Lists"));
if ($uniprotDomIdDataZip)
    array_push($otherFiles, array($uniprotDomIdDataZip, format_file_size($uniprotDomIdDataZipFilesize), "UniProt ID (Domain) Lists"));
if ($uniref50IdDataZip)
    array_push($otherFiles, array($uniref50IdDataZip, format_file_size($uniref50IdDataZipFilesize), "UniRef50 ID Lists"));
if ($uniref90IdDataZip)
    array_push($otherFiles, array($uniref90IdDataZip, format_file_size($uniref90IdDataZipFilesize), "UniRef90 ID Lists"));
if ($fastaZip)
    array_push($otherFiles, array($fastaZip, format_file_size($fastaZipFilesize), "FASTA Files"));
if ($fastaDomainZipFilesize)
    array_push($otherFiles, array($fastaDomainZip, format_file_size($fastaDomainZipFilesize), "FASTA Files (with Domain)"));
if ($pfamNoneZip)
    array_push($otherFiles, array($pfamNoneZip, format_file_size($pfamNoneZipFilesize), "Neighbors without PFAM assigned"));

if ($warningFile or $coocTableFile or $hubCountFile or $clusterSizesFileSize !== false or $swissprotClustersDescFileSize !== false or $swissprotSinglesDescFileSize !== false)
    array_push($otherFiles, array("Miscellaneous Files"));
if ($warningFile)
    array_push($otherFiles, array($warningFile, format_file_size($warningFilesize), "No Matches/No Neighbors File"));
if ($coocTableFile)
    array_push($otherFiles, array($coocTableFile, format_file_size($coocTableFilesize), "Pfam Family/Cluster Cooccurrence Table File"));
if ($hubCountFile)
    array_push($otherFiles, array($hubCountFile, format_file_size($hubCountFilesize), "GNN Hub Cluster Sequence Count File"));
if ($clusterSizesFileSize !== false)
    array_push($otherFiles, array($clusterSizesFile, format_file_size($clusterSizesFileSize), "Cluster Size File"));
if ($swissprotClustersDescFileSize !== false)
    array_push($otherFiles, array($swissprotClustersDescFile, format_file_size($swissprotClustersDescFileSize), "SwissProt Annotations by Cluster"));
if ($swissprotSinglesDescFileSize !== false)
    array_push($otherFiles, array($swissprotSinglesDescFile, format_file_size($swissprotSinglesDescFileSize), "SwissProt Annotations by Singleton"));

$updateMessage = functions::get_update_message();
$gnn_name = $gnn->get_gnn_name();

require_once('inc/header.inc.php'); 

?>

<div id="update-message" class="update_message initial-hidden">
    Several new features have been added to the GNT! <a href="notes.php">See the release notes.</a><br>
<?php if (isset($updateMessage)) echo $updateMessage; ?>
</div>

<h2>Results</h2>

<h4>Name: <b><?php echo $gnn_name; ?></b></h4>

<div class="tabs-efihdr tabs">
    <ul class="tab-headers">
        <li><a href="#info">Job Information</a></li>
        <li class="ui-tabs-active"><a href="#results">GNN/GND Download</a></li>
        <li><a href="#other">Other Files</a></li>
<?php if ($allow_regenerate) { ?>
        <li><a href="#regenerate">Regenerate GNN</a></li>
<?php } ?>
    </ul>

    <div class="tab-content">

        <!-- NETWORK INFORMATION -->
        <div id="info" class="">
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
            <h3>Colored Sequence Similarity Network (SSN)</h3>
            <p>The nodes in the input SSN are assigned unique cluster numbers and colors.</p>
        
            <table width="100%" class="pretty">
                <thead>
                    <th></th>
                    <th># Nodes</th>
                    <th># Edges</th>
                    <th>File Size (MB)</th>
                </thead>
                <tbody>
                    <tr style='text-align:center;'>
                        <td class="button-col">
                            <a href="<?php echo "$baseUrl/$ssnFile" ?>"><button class="mini">Download</button></a>
                            <?php if ($ssnZipFile) { ?>
                            <a href="<?php echo "$baseUrl/$ssnZipFile"; ?>"><button class="mini">Download ZIP</button></a>
                            <?php } ?>
                        </td>
                        <td><?php echo number_format($gnn->get_ssn_nodes()); ?></td>
                        <td><?php echo number_format($gnn->get_ssn_edges()); ?></td>
                        <td><?php echo $ssnFilesize; ?>MB</td>
                    </tr>
                </tbody>
            </table>
        
            <h3>SSN Cluster Hub-Nodes: Genome Neighborhood Network (GNN)</h3>
            <p>Each hub-node in the network represents an SSN cluster that identified neighbors, with spoke-nodes for Pfam family with neighbors.</p>
        
            <table width="100%" class="pretty">
                <thead>
                    <th></th>
                    <th>File Size (MB)</th>
                </thead>
                <tbody>
                    <tr style='text-align:center;'>
                        <td class="button-col">
                            <a href="<?php echo "$baseUrl/$gnnFile"; ?>"><button class="mini">Download</button></a>
                            <?php if ($gnnZipFile) { ?>
                            <a href="<?php echo "$baseUrl/$gnnZipFile"; ?>"><button class="mini">Download ZIP</button></a>
                            <?php } ?>
                        </td>
                        <td><?php echo $gnnFilesize; ?>MB</td>
                    </tr>
                </tbody>
            </table>
        
            <h3>Pfam Family Hub-Nodes Genome Neighborhood Network (GNN)</h3>
            <p>Each hub-node in the network represents a Pfam family of neighbors, with spoke-nodes for each SSN cluster that identified the Pfam family.</p>
        
            <table width="100%" class="pretty">
                <thead>
                    <th></th>
                    <th>File Size (Unzipped/Zipped MB)</th>
                </thead>
                <tbody>
                    <tr style='text-align:center;'>
                        <td class="button-col">
                            <a href="<?php echo "$baseUrl/$pfamFile"; ?>"><button class="mini">Download</button></a>
                            <?php if ($pfamZipFile) { ?>
                            <a href="<?php echo "$baseUrl/$pfamZipFile"; ?>"><button class="mini">Download ZIP</button></a>
                            <?php } ?>
                        </td>
                        <td>
                            <?php echo $pfamFilesize; ?> MB
                            <?php if ($pfamZipFilesize) { echo "/ $pfamZipFilesize MB"; } ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        
            <?php if ($hasDiagrams) { ?>
            <h3>Genome Neighborhood Diagrams</h3> 
            Genome neighboorhoods can be visualized in an arrow digram format in a new window.
        
            <table width="100%" class="pretty">
                <thead>
                    <th>Action</th>
                    <th></th>
                    <th>File Size (Unzipped/Zipped MB)</th>
                </thead>
                <tbody>
                    <tr style='text-align:center;'>
                        <td class="button-col">
                            <a href="view_diagrams.php?gnn-id=<?php echo $gnnId; ?>&key=<?php echo $gnnKey; ?>" target="_blank"><button class="mini">View diagrams</button></a>
                        </td>
                        <td colspan="2">
                            Opens arrow diagram explorer in a new tab or window.
                        </td>
                    </tr>
                    <tr style="text-align:center;">
                        <td class="button-col">
                            <a href="<?php echo "$baseUrl/$diagramFile"; ?>"><button class="mini">Download</button></a>
                            <?php if ($diagramZipFileSize) { ?>
                            <a href="<?php echo "$baseUrl/$diagramZipFile"; ?>"><button class="mini">Download ZIP</button></a>
                            <?php } ?>
                        </td>
                        <td>
                            Diagram data for later review
                        </td>
                        <td>
                            <?php echo $diagramFileSize; ?> MB
                            <?php if ($diagramZipFileSize) { echo "/ $diagramZipFileSize MB"; } ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php } else { ?>
            <div style="color:red">
                <br><br>
                Since this job was run before the neighborhood diagrams were supported, there are no
                genome neighborhood diagrams available.
            </div>
            <?php } ?>
        </div>


        <!-- OTHER DOWNLOAD FILES -->
        <div id="other" class="">
            <table width="100%" class="pretty">
                <thead>
                    <th></th>
                    <th>File</th>
                    <th>File Size (MB)</th>
                </thead>
                <tbody>
                    <?php
                    foreach ($otherFiles as $info) {
                        if (count($info) == 1) {
                            echo <<<HTML
                    <tr style="text-align:center;">
                        <td colspan="3" style="font-weight:bold">
                            $info[0]
                        </td>
                    </tr>
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
    $neighbor_size_html = "";
    $default_neighbor_size = $gnn->get_size();
    for ($i=3;$i<=20;$i++) {
        if ($i == $default_neighbor_size)
            $neighbor_size_html .= "<option value='" . $i . "' selected='selected'>" . $i . "</option>";
        else
            $neighbor_size_html .= "<option value='" . $i . "'>" . $i . "</option>";
    }

?>
        <div id="regenerate" class="">
            <?php
                $child_jobs = $gnn->get_child_jobs();
                if (count($child_jobs) > 0) {
                    echo <<<HTML
                        <h3>Related Regenerated GNT Jobs</h3>
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
            <h3>Regenerate GNN</h3>
            
            <form id="upload_form" action="">
            <input type="hidden" id="parent_id" value="<?php echo $gnnId; ?>">
            <input type="hidden" id="parent_key" value="<?php echo $gnnKey; ?>">
            
            Recreate an SSN with a different cooccurrence frequency and/or neighborhood size from this job.

            <p>
                <b>Co-occurrence percentage lower limit:</b> <input type="text" id="cooccurrence" maxlength="3" value="<?php echo $gnn->get_cooccurrence(); ?>">
                <div class="left-margin-70">
                    This option allows to filter the neighboring pFAMs with a co-occurrence percentage lower than the set value.
                    The default value is  <?php echo settings::get_default_cooccurrence(); ?>, Valid values are 0-100.
                </div>
            </p>
            
            <p>
                <b>Neighborhood Size:</b> 
                <select name="neighbor_size" id="neighbor_size" class="bigger">
                    <?php echo $neighbor_size_html; ?>
                </select>
                <div class="left-margin-70">
                    With a value of <?php echo $default_neighbor_size; ?>, the PFAM families for <?php echo $default_neighbor_size; ?> 
                    genes located upstream and for <?php echo $default_neighbor_size; ?> genes
                    located downstream of sequences in the SNN will be collected and displayed.
                    The default value is  <?php echo $default_neighbor_size; ?>.
                </div>
            </p>
            
            <p>
                <?php echo ui::make_upload_box("(Optional) Select a File to Upload:", "ssn_file", "progress_bar", "progress_number", "The acceptable format is uncompressed or zipped xgmml.", "", ""); ?>
                <br>
                Uploading a new file will use information saved from this GNN to generate a new GNN, 
                returning results to the user faster than creating a brand-new GNN.  This is useful when examining SSNs with varying
                alignment scores.
            </p>
                
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

<?php if (settings::is_beta_release()) { ?>
    <div><center><h4><b><span style="color: red">BETA</span></b></h4></center></div>
<?php } ?>

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
        return "<1";
    return $mb;
}
?>

<?php require_once('inc/footer.inc.php'); ?>

