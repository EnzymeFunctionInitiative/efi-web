<?php
require_once(__DIR__ . "/../../init.php");

use efi\est\family_size;
use \efi\sanitize;


$query = isset($_GET["families"]) ? $_GET["families"] : "";
if (preg_match("/[^a-zA-Z0-9\n\r ,]/", $query)) {
    echo json_encode(array("result" => false));
    exit(1);
}

$families = family_size::parse_family_query($query);

if ($families === false || !is_array($families)) {
    echo json_encode(array("result" => false));
    exit(1);
}

$use_uniref = sanitize::get_sanitize_num("uniref", 0) === 1;
$uniref_ver = sanitize::get_sanitize_num("uniref-ver", 0);
if (!$use_uniref || !$uniref_ver)
    $uniref_ver = "";
$fraction = sanitize::get_sanitize_num("fraction", 0);
$db_ver = (isset($_GET["db-ver"]) && preg_match("/^[IP0-9]{4,5}$/", $_GET["db-ver"])) ? $_GET["db-ver"] : "";

$results = family_size::compute_family_size($db, $families, $fraction, $use_uniref, $uniref_ver, $db_ver);

echo json_encode($results);


