<?php 
require_once(__DIR__ . "/../includes/main.inc.php");

if ((!isset($_GET["id"])) || (!is_numeric($_GET["id"]))) {
    pretty_error_404();
    exit;
}


$obj = new colorssn($db,$_GET["id"]);

$key = $obj->get_key();
if ($key != $_GET["key"]) {
    error_404();
    exit;
}


if (!isset($_GET["logo"])) {
    error_404();
    exit;
}


$hmm_graphics = $obj->get_hmm_graphics();
$output_dir = $obj->get_full_output_dir();

list($cluster, $seq_type, $quality) = explode("-", $_GET["logo"]);

if (!isset($hmm_graphics[$cluster][$seq_type][$quality])) {
    die("$cluster $seq_type $quality");
    exit;
}

$format = "png";
$filename = $obj->get_base_filename() . "_HMM_Cluster_${cluster}_${seq_type}_${quality}.$format";
$full_path = $obj->get_full_output_dir() . "/" . $hmm_graphics[$cluster][$seq_type][$quality] . ".png";

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Content-Transfer-Encoding: binary');
header('Connection: Keep-Alive');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($full_path));
ob_clean();
readfile($full_path);

?>
