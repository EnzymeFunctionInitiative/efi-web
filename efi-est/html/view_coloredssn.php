<?php 
include_once '../includes/main.inc.php';

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

$ssnFile = $obj->get_colored_xgmml_filename_no_ext();
$ssnFileZip = "$ssnFile.zip";

$nodeFilesZip = "${ssnFile}_UniProt_IDs.zip";
$fastaFilesZip = "${ssnFile}_FASTA.zip";
$tableFile = $ssnFile . "_" . functions::get_colorssn_map_file_name();

$ssnFile = "$ssnFile.xgmml";

$dateCompleted = $obj->get_time_completed_formatted();
$dbVersion = $obj->get_db_version();

$table->add_row("Date Completed", $dateCompleted);
if (!empty($dbVersion)) {
    $table->add_row("Database Version", $dbVersion);
}
$table->add_row("Input Option", "Color SSN");
$table->add_row("Job Number", $jobNumber);
$table->add_row("Uploaded XGMML File", $uploadedFilename);

$table_string = $table->as_string();

if (isset($_GET["as-table"])) {
    $table_filename = functions::safe_filename($ssnFile) . "_settings.txt";

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
<p>
    Generation Summary Table
    <a href='<?php echo $_SERVER['PHP_SELF'] . "?id=" . $_GET['id'] . "&key=" . $_GET['key'] . "&as-table=1" ?>'><button class='mini'>Download</button></a>
</p>
<table width="100%" border="1">
<?php echo $table_string ?>
</table>

<p>&nbsp;</p>

<hr>

<h3>Data File Download</h3>
<table width="100%" border="1">
<tr>
    <td>Colored SSN</td>
    <td>
        <a href="<?php echo "$baseUrl/$ssnFile"; ?>"><button class='mini'>Download</button></a>
        <a href="<?php echo "$baseUrl/$ssnFileZip"; ?>"><button class='mini'>Download ZIP</button></a>
    </td>
</tr>
<tr>
    <td>UniProt ID-Color-Cluster Number Mapping Table</td>
    <td>
        <a href="<?php echo "$baseUrl/$tableFile"; ?>"><button class='mini'>Download</button></a>
    </td>
</tr>
<tr>
    <td>UniProt ID Lists per Cluster</td>
    <td>
        <a href="<?php echo "$baseUrl/$nodeFilesZip"; ?>"><button class='mini'>Download All (ZIP)</button></a>
    </td>
</tr>
<tr>
    <td>FASTA Files per Cluster</td>
    <td>
        <a href="<?php echo "$baseUrl/$fastaFilesZip"; ?>"><button class='mini'>Download All (ZIP)</button></a>
    </td>
</tr>
</table>


</div>

<?php
    
    include_once 'inc/footer.inc.php';

}

?>

