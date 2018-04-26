<?php
require_once("../includes/main.inc.php");
require_once("../libs/input.class.inc.php");
require_once("../libs/family_size.class.inc.php");


$query_string = str_replace("\n", ",", $_GET["families"]);
$query_string = str_replace("\r", ",", $query_string);
$query_string = str_replace(" ", ",", $query_string);
$families = explode(",", $query_string);

$is_uniref90 = isset($_GET["uniref"]) && $_GET["uniref"] == 1;
$fraction = isset($_GET["fraction"]) ? $_GET["fraction"] : 1;

$results = family_size::compute_family_size($db, $families, $fraction, $is_uniref90);

echo json_encode($results);


?>
