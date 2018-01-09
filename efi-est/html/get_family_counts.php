<?php
require_once '../includes/main.inc.php';
require_once '../libs/input.class.inc.php';

$result = "";

$queryString = str_replace("\n", ",", $_GET["families"]);
$queryString = str_replace("\r", ",", $queryString);
$queryString = str_replace(" ", ",", $queryString);
$families = explode(",", $queryString);

$results = array();

foreach ($families as $family) {
    $family = functions::sanitize_family($family);
    if (!$family)
        continue;

    $familyType = functions::get_family_type($family);
    if (!$familyType)
        continue;

    $sql = "SELECT * FROM family_info WHERE family='$family'";
    $dbResult = $db->query($sql);
    if ($dbResult) {
        $results[strtoupper($family)] = array(
            "name" => $dbResult[0]["short_name"],
            "all" => $dbResult[0]["num_members"],
            "uniref90" => $dbResult[0]["num_uniref90_members"],
            "uniref50" => $dbResult[0]["num_uniref50_members"]);
    }
}

echo json_encode($results);


?>
