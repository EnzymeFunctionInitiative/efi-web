<?php
require_once(__DIR__ . "/../init.php");

use efi\est\family_size;


$ARGS = array();
if (php_sapi_name() == "cli") {
    $ARGS["families"] = $argv[1];
    if (isset($argv[2]))
        $ARGS["uniref"] = $argv[2];
    if (isset($argv[3]))
        $ARGS["uniref-ver"] = $argv[3];
    if (isset($argv[4]))
        $ARGS["fraction"] = $argv[4];
    if (isset($argv[5]))
        $ARGS["db-ver"] = $argv[5];
} else {
    $ARGS = $_GET;
}

$query = $ARGS["families"];
$families = family_size::parse_family_query($query);

$use_uniref = isset($ARGS["uniref"]) && $ARGS["uniref"] == 1;
$uniref_ver = ($use_uniref && isset($ARGS["uniref-ver"]) && $ARGS["uniref-ver"]) ? $ARGS["uniref-ver"] : "";
$fraction = isset($ARGS["fraction"]) ? $ARGS["fraction"] : 1;
$db_ver = isset($ARGS["db-ver"]) ? $ARGS["db-ver"] : "";

$results = family_size::compute_family_size($db, $families, $fraction, $use_uniref, $uniref_ver, $db_ver);

echo json_encode($results);


