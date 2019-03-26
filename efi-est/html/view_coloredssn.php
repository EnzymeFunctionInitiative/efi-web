<?php 
include_once '../includes/main.inc.php';
require_once(__BASE_DIR__ . "/includes/login_check.inc.php");

if ((!isset($_GET['id'])) || (!is_numeric($_GET['id']))) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    exit;
}


$obj = new colorssn($db,$_GET['id']);

if ($obj->get_key() != $_GET['key']) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    //echo "No EFI-EST Selected. Please go back";
    exit;
}

$table_format = "html";
if (isset($_GET["as-table"])) {
    $table_format = "tab";
}
$table = new table_builder($table_format);

$jobNumber = $obj->get_id();
$uploadedFilename = $obj->get_uploaded_filename();

$url = $_SERVER['PHP_SELF'] . "?" . http_build_query(array('id'=>$obj->get_id(), 'key'=>$obj->get_key()));
$baseUrl = functions::get_web_root() . "/results/" . $obj->get_output_dir();


$want_clusters_file = true;
$want_singles_file = false;
$baseSsnName = $obj->get_colored_xgmml_filename_no_ext();
$ssnFile = $obj->get_colored_ssn_web_path();
$ssnFileZip = $obj->get_colored_ssn_zip_web_path();
$uniprotNodeFilesZip = $obj->get_node_files_zip_web_path(colorssn::SEQ_UNIPROT);
$uniref50NodeFilesZip = $obj->get_node_files_zip_web_path(colorssn::SEQ_UNIREF50);
$uniref90NodeFilesZip = $obj->get_node_files_zip_web_path(colorssn::SEQ_UNIREF90);
$fastaFilesZip = $obj->get_fasta_files_zip_web_path();
$tableFile = $obj->get_table_file_web_path();
$tableFileDomain = $obj->get_table_file_web_path(true); // true = for domain file
$statsFile = $obj->get_stats_web_path();
$clusterSizesFile = $obj->get_cluster_sizes_web_path();
$swissprotClustersDescFile = $obj->get_swissprot_desc_web_path($want_clusters_file);
$swissprotSinglesDescFile = $obj->get_swissprot_desc_web_path($want_singles_file);


$fileInfo = array();
array_push($fileInfo, array("Colored SSN", array($ssnFile, $ssnFileZip)));
array_push($fileInfo, array("UniProt ID-Color-Cluster number mapping table", $tableFile));
if ($tableFileDomain)
    array_push($fileInfo, array("UniProt ID-Color-Cluster number mapping table with domain", $tableFileDomain));
if ($uniprotNodeFilesZip)
    array_push($fileInfo, array("UniProt ID lists per cluster", $uniprotNodeFilesZip));
if ($uniref50NodeFilesZip)
    array_push($fileInfo, array("UniRef50 ID lists per cluster", $uniref50NodeFilesZip));
if ($uniref90NodeFilesZip)
    array_push($fileInfo, array("UniRef90 ID lists per cluster", $uniref90NodeFilesZip));
array_push($fileInfo, array("FASTA files per cluster", $fastaFilesZip));
if ($clusterSizesFile)
    array_push($fileInfo, array("Cluster sizes", $clusterSizesFile));
if ($swissprotClustersDescFile)
    array_push($fileInfo, array("SwissProt annotations by cluster", $swissprotClustersDescFile));
if ($swissprotSinglesDescFile)
    array_push($fileInfo, array("SwissProt annotations by singletons", $swissprotSinglesDescFile));


$metadata = $obj->get_metadata();

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
#$table->add_row("Job Number", $jobNumber);
#$table->add_row("Uploaded XGMML File", $uploadedFilename);

$table_string = $table->as_string();

if (isset($_GET["as-table"])) {
    $table_filename = functions::safe_filename($baseSsnName) . "_settings.txt";

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
<p>&nbsp;</p>

<h3>Network Information</h3>
<table width="100%" class="pretty">
<?php echo $table_string ?>
</table>
<div style="float:right"><a href='<?php echo $_SERVER['PHP_SELF'] . "?id=" . $_GET['id'] . "&key=" . $_GET['key'] . "&as-table=1" ?>'><button class='mini'>Download Information</button></a></div>
<div style="clear:both;margin-bottom:20px"></div>

<hr>

<h3>Data File Download</h3>
<table width="100%" class="pretty">
<?php
   foreach ($fileInfo as $info) {
       echo <<<HTML
<tr>
    <td>$info[0]</td>
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
       echo "</tr>\n";
   }
?>
</table>


</div>

<?php
    
    include_once 'inc/footer.inc.php';

}

?>

