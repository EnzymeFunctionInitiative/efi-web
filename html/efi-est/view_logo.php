<?php 
require_once(__DIR__."/../../init.php");

use \efi\est\job_factory;
use \efi\training\example_config;


if ((!isset($_GET["id"])) || (!is_numeric($_GET["id"]))) {
    error_404();
    exit;
}


$is_example = example_config::is_example();
$obj = job_factory::create($db, $_GET["id"], $is_example);

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

$parts = explode("-", $_GET["logo"]);
$cluster = $parts[0];
$seq_type = $parts[1];
$quality = $parts[2];
if (count($parts) > 3)
    $quality .= "-" . $parts[3];


if (!isset($hmm_graphics[$cluster][$seq_type][$quality])) {
    die("$cluster $seq_type $quality");
    exit;
}

$hmm_path = "$output_dir/" . $hmm_graphics[$cluster][$seq_type][$quality]["path"] . ".json";
$json = file_get_contents($hmm_path);

$title = isset($_GET["title"]) ? $_GET["title"] : "";

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<link rel="stylesheet" type="text/css" href="../css/hmm_logo.min.css">
<script src="../vendor/components/jquery/jquery.min.js" type="text/javascript"></script>
<script src="../js/hmm_logo.js" type="text/javascript"></script>
    <title>Logo</title>
</head>
<body>

<div><big><b><?php echo $title; ?></b></big></div>


<div id="logo" class="logo" data-logo='<?php echo $json; ?>'></div>

<script>
$(document).ready(function () {
    var data = <?php echo $json; ?>;
    $("#logo").hmm_logo({height_toggle: true}).toggle_scale("obs");
});
</script>

</body>
</html>

