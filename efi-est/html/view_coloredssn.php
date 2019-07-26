<?php 
include_once '../includes/main.inc.php';
require_once(__BASE_DIR__ . "/includes/login_check.inc.php");

if ((!isset($_GET['id'])) || (!is_numeric($_GET['id']))) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    exit;
}


$obj = new colorssn($db,$_GET['id']);

$key = $obj->get_key();
if ($key != $_GET['key']) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    //echo "No EFI-EST Selected. Please go back";
    exit;
}

$table_format = "html";
if (isset($_GET["as-table"])) {
    $table_format = "tab";
}
$table = new table_builder($table_format);

$est_id = $obj->get_id();
$uploadedFilename = $obj->get_uploaded_filename();

$url = $_SERVER['PHP_SELF'] . "?" . http_build_query(array('id'=>$obj->get_id(), 'key' => $key));
$baseUrl = functions::get_web_root() . "/results/" . $obj->get_output_dir();


$want_clusters_file = true;
$want_singles_file = false;
$baseSsnName = $obj->get_colored_xgmml_filename_no_ext();
$ssnFile = $obj->get_colored_ssn_web_path();
$ssnFileSize = format_file_size($obj->get_file_size($ssnFile));
$ssnFileZip = $obj->get_colored_ssn_zip_web_path();
$ssnFileZipSize = format_file_size($obj->get_file_size($ssnFileZip));
$uniprotNodeFilesZip = $obj->get_node_files_zip_web_path(colorssn::SEQ_UNIPROT);
$uniprotNodeFilesZipSize = format_file_size($obj->get_file_size($uniprotNodeFilesZip));
$uniref50NodeFilesZip = $obj->get_node_files_zip_web_path(colorssn::SEQ_UNIREF50);
$uniref50NodeFilesZipSize = format_file_size($obj->get_file_size($uniref50NodeFilesZip));
$uniref90NodeFilesZip = $obj->get_node_files_zip_web_path(colorssn::SEQ_UNIREF90);
$uniref90NodeFilesZipSize = format_file_size($obj->get_file_size($uniref90NodeFilesZip));
$fastaFilesUniProtZip = $obj->get_fasta_files_zip_web_path(colorssn::SEQ_NO_DOMAIN, colorssn::SEQ_UNIPROT);
$fastaFilesUniProtZipSize = format_file_size($obj->get_file_size($fastaFilesUniProtZip));
$fastaFilesUniProtDomainZip = $obj->get_fasta_files_zip_web_path(colorssn::SEQ_DOMAIN, colorssn::SEQ_UNIPROT);
$fastaFilesUniProtDomainZipSize = format_file_size($obj->get_file_size($fastaFilesUniProtDomainZip));
$fastaFilesUniRef90Zip = $obj->get_fasta_files_zip_web_path(colorssn::SEQ_NO_DOMAIN, colorssn::SEQ_UNIREF90);
$fastaFilesUniRef90ZipSize = format_file_size($obj->get_file_size($fastaFilesUniRef90Zip));
$fastaFilesUniRef90DomainZip = $obj->get_fasta_files_zip_web_path(colorssn::SEQ_DOMAIN, colorssn::SEQ_UNIREF90);
$fastaFilesUniRef90DomainZipSize = format_file_size($obj->get_file_size($fastaFilesUniRef90DomainZip));
$fastaFilesUniRef50Zip = $obj->get_fasta_files_zip_web_path(colorssn::SEQ_NO_DOMAIN, colorssn::SEQ_UNIREF50);
$fastaFilesUniRef50ZipSize = format_file_size($obj->get_file_size($fastaFilesUniRef50Zip));
$fastaFilesUniRef50DomainZip = $obj->get_fasta_files_zip_web_path(colorssn::SEQ_DOMAIN, colorssn::SEQ_UNIREF50);
$fastaFilesUniRef50DomainZipSize = format_file_size($obj->get_file_size($fastaFilesUniRef50DomainZip));
$tableFile = $obj->get_table_file_web_path();
$tableFileSize = format_file_size($obj->get_file_size($tableFile));
$tableFileDomain = $obj->get_table_file_web_path(true); // true = for domain file
$tableFileDomainSize = format_file_size($obj->get_file_size($tableFileDomain));
$statsFile = $obj->get_stats_web_path();
$statsFileSize = format_file_size($obj->get_file_size($statsFile));
$clusterSizesFile = $obj->get_cluster_sizes_web_path();
$clusterSizesFileSize = format_file_size($obj->get_file_size($clusterSizesFile));
$swissprotClustersDescFile = $obj->get_swissprot_desc_web_path($want_clusters_file);
$swissprotClustersDescFileSize = format_file_size($obj->get_file_size($swissprotClustersDescFile));
$swissprotSinglesDescFile = $obj->get_swissprot_desc_web_path($want_singles_file);
$swissprotSinglesDescFileSize = format_file_size($obj->get_file_size($swissprotSinglesDescFile));


$fileInfo = array();
array_push($fileInfo, array("UniProt ID-Color-Cluster number mapping table", $tableFile, $tableFileSize));
if ($tableFileDomain)
    array_push($fileInfo, array("UniProt ID-Color-Cluster number mapping table with domain", $tableFileDomain, $tableFileDomainSize));
array_push($fileInfo, array("ID Lists and FASTA Files per Cluster"));
if ($uniprotNodeFilesZip)
    array_push($fileInfo, array("UniProt ID lists per cluster", $uniprotNodeFilesZip, $uniprotNodeFilesZipSize));
if ($uniref50NodeFilesZip)
    array_push($fileInfo, array("UniRef50 ID lists per cluster", $uniref50NodeFilesZip, $uniref50NodeFilesZipSize));
if ($uniref90NodeFilesZip)
    array_push($fileInfo, array("UniRef90 ID lists per cluster", $uniref90NodeFilesZip, $uniref90NodeFilesZipSize));
array_push($fileInfo, array("FASTA files per cluster", $fastaFilesUniProtZip, $fastaFilesUniProtZipSize));
if ($fastaFilesUniProtDomainZip)
    array_push($fileInfo, array("FASTA files per cluster (with domain)", $fastaFilesUniProtDomainZip, $fastaFilesUniProtDomainZipSize));
array_push($fileInfo, array("Miscellaneous Files"));
if ($clusterSizesFile)
    array_push($fileInfo, array("Cluster sizes", $clusterSizesFile, $clusterSizesFileSize));
if ($swissprotClustersDescFile)
    array_push($fileInfo, array("SwissProt annotations by cluster", $swissprotClustersDescFile, $swissprotClustersDescFileSize));
if ($swissprotSinglesDescFile)
    array_push($fileInfo, array("SwissProt annotations by singletons", $swissprotSinglesDescFile, $swissprotSinglesDescFileSize));


$metadata = $obj->get_metadata();
$job_name = $obj->get_uploaded_filename();

foreach ($metadata as $row) {
    if (strpos($row[0], '<a href') !== false)
        $table->add_row_html_only($row[0], $row[1]);
    else
        $table->add_row($row[0], $row[1]);
}

#$dateCompleted = $obj->get_time_completed_formatted();
#$dbVersion = $obj->get_db_version();
#$table->add_row("Date Completed", $dateCompleted);
#if (!empty($dbVersion)) {
#    $table->add_row("Database Version", $dbVersion);
#}
#$table->add_row("Input Option", "Color SSN");
#$table->add_row("Job Number", $est_id);
#$table->add_row("Uploaded XGMML File", $uploadedFilename);

$table_string = $table->as_string();

if (isset($_GET["as-table"])) {
    $table_filename = functions::safe_filename($baseSsnName) . "_summary.txt";

    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $table_filename . '"');
    header('Content-Length: ' . strlen($table_string));
    ob_clean();
    echo $table_string;
}
else {
    
   include_once 'inc/header.inc.php'; 

    if (time() > $obj->get_unixtime_completed() + functions::get_retention_secs()) {
        echo "<p class='center'><br>Your job results are only retained for a period of " . functions::get_retention_days(). " days";
        echo "<br>Your job was completed on " . $obj->get_time_completed();
        echo "<br>Please go back to the <a href='" . functions::get_server_name() . "'>homepage</a></p>";
        exit;
    }


?>	

<h2>Download Colored SSN Files</h2>

<h3>Uploaded Filename: <?php echo $job_name; ?></h3>

<div class="tabs-efihdr tabs">
    <ul class="">
        <li class="ui-tabs-active"><a href="#info">Submission Summary</a></li>
        <li><a href="#data">Data File Download</a></li>
    </ul>
    <div>
        <!-- JOB INFORMATION -->
        <div id="info">
            <h4>Submission Summary Table</h4>
            <table width="100%" class="pretty">
            <?php echo $table_string ?>
            </table>
            <div style="float:right"><a href='<?php echo $_SERVER['PHP_SELF'] . "?id=$est_id&key=$key&as-table=1" ?>'><button class='normal'>Download Information</button></a></div>
            <div style="clear:both;margin-bottom:20px"></div>
        </div>
        <div id="data">
            <h4>Colored SSN</h4>
            <p>Each cluster in the submitted SSN has been identified and assigned a unique number and color.</p>

            <table width="100%" class="pretty">
                <thead>
                    <th></th>
                    <th>File Size (Unzipped/Zipped MB)</th>
                </thead>
                <tbody>
                    <tr style='text-align:center;'>
                        <td class="button-col">
                            <a href="<?php echo "$ssnFile"; ?>"><button class="mini">Download</button></a>
                            <a href="<?php echo "$ssnFileZip"; ?>"><button class="mini">Download ZIP</button></a>
                        </td>
                        <td>
                            <?php echo "$ssnFileSize MB / $ssnFileZipSize MB"; ?>
                        </td>
                    </tr>
                </tbody>
            </table>


            <h4>Supplementary Files</h4>
            <table width="100%" class="pretty">
                <tbody>
                <?php
                    $first = false;
                    foreach ($fileInfo as $info) {
                        if (count($info) == 1) {
                            $first_class = $first ? "file-header-row-first" : "";
                            echo <<<HTML
                    <tr style="text-align:center;">
                        <td colspan="3" class="file-header-row $first_class">
                            $info[0]
                        </td>
                    </tr>
HTML;
                        } else {
                            echo <<<HTML
                    <tr class="file-row" style="text-align:center;">
HTML;
                            if (is_array($info[1])) {
                                $f1 = $info[1][0];
                                $f2 = $info[1][1];
                                echo <<<HTML
                        <td>
                            <a href="$f1"><button class='mini'>Download</button></a>
                            <a href="$f2"><button class='mini'>Download (ZIP)</button></a>
                        </td>
HTML;
                            } else {
                                $text = substr($info[1], -4) == ".zip" ? "Download All (ZIP)" : "Download";
                                echo <<<HTML
                        <td><a href="$info[1]"><button class='mini'>$text</button></a></td>
HTML;
                            }
                            echo <<<HTML
                        <td>$info[0]</td>
                        <td>$info[2] MB</td>
                    </tr>
HTML;
                        }
                    }
                ?>
                </tbody>
            </table>

            <center>
                <a href="../efi-cgfp/index.php?<?php echo "est-id=$est_id&est-key=$key"; ?>"><button type="button" name="analyze_data" class="dark white">Run CGFP on Colored SSN</button></a>
            </center>
            
        </div>
    </div>
</div>
   
<script>
$(document).ready(function() {
    $(".tabs").tabs();
});
</script>

<?php
    include_once 'inc/footer.inc.php';

}


function format_file_size($size) {
    $mb = round($size, 0);
    if ($mb == 0)
        return "<1";
    return $mb;
}


?>

