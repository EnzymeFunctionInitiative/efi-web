<?php
require_once '../includes/main.inc.php';
require_once '../libs/input.class.inc.php';

$result = "";

$queryString = str_replace("\n", ",", $_GET["families"]);
$queryString = str_replace("\r", ",", $queryString);
$queryString = str_replace(" ", ",", $queryString);
$families = explode(",", $queryString);

$isUniref90Check = isset($_GET["check-warning"]) && $_GET["check-warning"] == 1;
$fraction = isset($_GET["fraction"]) ? $_GET["fraction"] : 1;

$totalFull = 0;
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
        $totalFull += $dbResult[0]["num_members"];
    }
}

if ($isUniref90Check) {
    $maxFull = functions::get_maximum_full_family_count();
    $totalFraction = $totalFull / $fraction;
    //$isWarning = $maxFull > 0 && $totalFull > $maxFull;
    $isWarning = $maxFull > 0 && $totalFraction > $maxFull;
    $results = array("is_warning" => $isWarning, "max_full" => $maxFull, "total" => $totalFull);
    if ($totalFraction < $totalFull) {
        $results["total_fraction"] = $totalFraction;
    }
}

echo json_encode($results);


?>
