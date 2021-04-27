<?php
require_once(__DIR__."/../../conf/settings_paths.inc.php");
require_once(__EST_DIR__."/includes/main.inc.php");
require_once(__EST_DIR__."/libs/input.class.inc.php");
require_once(__EST_DIR__."/libs/family_size.class.inc.php");


$query = $_GET["families"];
$families = family_size::parse_family_query($query);

$use_uniref = isset($_GET["uniref"]) && $_GET["uniref"] == 1;
$uniref_ver = ($use_uniref && isset($_GET["uniref-ver"]) && $_GET["uniref-ver"]) ? $_GET["uniref-ver"] : "";
$fraction = isset($_GET["fraction"]) ? $_GET["fraction"] : 1;
$db_ver = isset($_GET["db-ver"]) ? $_GET["db-ver"] : "";

$results = family_size::compute_family_size($db, $families, $fraction, $use_uniref, $uniref_ver, $db_ver);

echo json_encode($results);


?>
