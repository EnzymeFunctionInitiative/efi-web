<?php 
require_once(__DIR__."/../../conf/settings_paths.inc.php");
require_once(__EST_DIR__ . "/includes/main.inc.php");
require_once(__EST_DIR__ . "/libs/job_factory.class.inc.php");


if ((!isset($_GET["id"])) || (!is_numeric($_GET["id"]))) {
    error_404();
    exit;
}


$obj = job_factory::create($db,$_GET["id"]);

$key = $obj->get_key();
if ($key != $_GET["key"]) {
    error_404();
    exit;
}


if (!isset($_GET["logo"])) {
    error_404();
    exit;
}


$gtype = "hmm";
if (isset($_GET["t"])) {
    if ($_GET["t"] == "w")
        $gtype = "weblogo";
    elseif ($_GET["t"] == "l")
        $gtype = "length";
    elseif ($_GET["t"] == "afa")
        $gtype = "afa";
    elseif ($_GET["t"] == "hmm-png")
        $gtype = "hmm_png";
    elseif ($_GET["t"] == "hmm")
        $gtype = "hmm";
}


$output_dir = $obj->get_full_output_dir();

$file_prefix = "";
$file_ext = ".png";
$format = "png";
if ($gtype == "hmm_png" || $gtype == "hmm") {
    $graphics = $obj->get_hmm_graphics();
    $file_prefix = "HMM_Cluster";
    if ($gtype == "hmm") {
        $file_ext = ".hmm";
        $format = "hmm";
    }
} elseif ($gtype == "weblogo") {
    $graphics = $obj->get_weblogo_graphics();
    $file_prefix = "WebLogo_Cluster";
} elseif ($gtype == "length") {
    $graphics = $obj->get_lenhist_graphics();
    $file_prefix = "Length_Histogram";
} elseif ($gtype == "afa") {
    $graphics = $obj->get_alignment_list();
    $file_prefix = "Alignment";
    $file_ext = "";
    $format = "afa";
}


list($cluster, $seq_type, $quality) = explode("-", $_GET["logo"]);
if (!isset($graphics[$cluster][$seq_type][$quality])) {
    die("$cluster $seq_type $quality");
    exit;
}

$filename = $obj->get_base_filename() . "_${file_prefix}_${cluster}_${seq_type}_${quality}.$format";
$full_path = $obj->get_full_output_dir() . "/" . $graphics[$cluster][$seq_type][$quality]["path"] . $file_ext;


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
