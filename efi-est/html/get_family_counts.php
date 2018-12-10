<?php
require_once("../includes/main.inc.php");
require_once("../libs/input.class.inc.php");
require_once("../libs/family_size.class.inc.php");


$query_string = str_replace("\n", ",", $_GET["families"]);
$query_string = str_replace("\r", ",", $query_string);
$query_string = str_replace(" ", ",", $query_string);
$families = explode(",", $query_string);

$use_uniref = isset($_GET["uniref"]) && $_GET["uniref"] == 1;
$uniref_ver = ($use_uniref && isset($_GET["uniref-ver"]) && $_GET["uniref-ver"]) ? $_GET["uniref-ver"] : "";
$fraction = isset($_GET["fraction"]) ? $_GET["fraction"] : 1;
$db_ver = isset($_GET["db-ver"]) ? $_GET["db-ver"] : "";

$results = family_size::compute_family_size($db, $families, $fraction, $use_uniref, $uniref_ver, $db_ver);

echo json_encode($results);


?>
