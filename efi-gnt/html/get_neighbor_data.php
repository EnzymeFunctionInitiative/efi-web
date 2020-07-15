<?php
include_once '../includes/main.inc.php';
include_once '../libs/gnn.class.inc.php';
require_once '../libs/bigscape_job.class.inc.php';


$output = array();

$dbFile = "";
$hasBigscape = false;
$isDirectJob = false;

$message = "";
if ((isset($_GET["gnn-id"])) && (is_numeric($_GET["gnn-id"]))) {
    $gnnId = $_GET["gnn-id"];
    $gnn = new gnn($db, $gnnId);
    if ($gnn->get_key() != $_GET["key"]) {
        $message = "No GNN selected.";
        exit;
    }
    elseif ($gnn->is_expired()) {
        $message = "GNN results are expired.";
    }

    if (settings::get_bigscape_enabled()) {
        $bss = new bigscape_job($db, $gnnId, DiagramJob::GNN);
        $hasBigscape = $bss->get_status() == bigscape_job::STATUS_FINISH && isset($_GET['bigscape']) && $_GET['bigscape']=="1";
    }
    
    $dbFile = $gnn->get_diagram_data_file($hasBigscape);
    if (!file_exists($dbFile))
        $dbFile = $gnn->get_diagram_data_file_legacy();
}
else if (isset($_GET['upload-id']) && functions::is_diagram_upload_id_valid($_GET['upload-id'])) {
    $gnnId = $_GET['upload-id'];
    $arrows = new diagram_data_file($gnnId);
    if (settings::get_bigscape_enabled()) {
        $bss = new bigscape_job($db, $gnnId, DiagramJob::Uploaded);
        $hasBigscape = $bss->get_status() == bigscape_job::STATUS_FINISH && isset($_GET['bigscape']) && $_GET['bigscape']=="1";
    }
    $dbFile = $arrows->get_diagram_data_file($hasBigscape);
}
else if (isset($_GET['direct-id']) && functions::is_diagram_upload_id_valid($_GET['direct-id'])) {
    $gnnId = $_GET['direct-id'];
    $arrows = new diagram_data_file($gnnId);
    if (settings::get_bigscape_enabled()) {
        $bss = new bigscape_job($db, $gnnId, DiagramJob::Uploaded);
        $hasBigscape = $bss->get_status() == bigscape_job::STATUS_FINISH && isset($_GET['bigscape']) && $_GET['bigscape']=="1";
    }
    $dbFile = $arrows->get_diagram_data_file($hasBigscape);
    $isDirectJob = true;
}
else {
    $message = "No GNN selected.";
}


$window = NULL;
if (isset($_GET["window"]) && is_numeric($_GET["window"])) {
    $window = intval($_GET["window"]);
}
$scaleFactor = NULL;
if (isset($_GET["scale-factor"]) && is_numeric($_GET["scale-factor"])) {
    $scaleFactor = floatval($_GET["scale-factor"]);
    if ($scaleFactor < 0.00000001 || $scaleFactor > 1000000.0)
        $scaleFactor = NULL;
}

$pageSize = 20;
if (isset($_GET["pagesize"]) && is_numeric($_GET["pagesize"])) {
    $pageSize = $_GET["pagesize"];
}
if (isset($_GET["sidx"]) && isset($_GET["eidx"]) && is_numeric($_GET["sidx"]) && is_numeric($_GET["eidx"]))
    $pageSize = array($_GET["sidx"], $_GET["eidx"]);

$output["message"] = "";
$output["error"] = false;
$output["eod"] = false;

if (isset($_GET["stats"])) {
    $resultsDb = new SQLite3($dbFile);
    $orderData = getDefaultOrder();
    $S = microtime(true);
    $stats = getStats($_GET["query"], $resultsDb, $window, $orderData);
    $T = microtime(true) - $S;
    $stats["totaltime"] = $T;
    $output["stats"] = $stats;
    echo json_encode($output);
    exit;
}

if ($message) {
    $output["message"] = $message;
    $output["error"] = true;
    echo json_encode($output);
    exit;
}

if (array_key_exists("query", $_GET)) {
    $items = parseQueryString($_GET["query"]);

    $blastId = getBlastId();
    //$orderData = getOrder($blastId, $items, $dbFile, $blastCacheDir, $gnn);
    $orderData = getDefaultOrder();

    $S = microtime(true);
    $arrowData = getArrowData($items, $dbFile, $orderData, $window, $scaleFactor, $isDirectJob, $pageSize);
    $T = microtime(true) - $S;
    $output["scale_factor"] = $arrowData["scale_factor"];
    $output["time"] = $arrowData["time"] . " Total=$T";
    $output["eod"] = $arrowData["eod"];
    $output["counts"] = $arrowData["counts"];
    $output["data"] = $arrowData["data"];
    $output["min_bp"] = $arrowData["min_bp"];
    $output["max_bp"] = $arrowData["max_bp"];
    $output["min_pct"] = $arrowData["min_pct"];
    $output["max_pct"] = $arrowData["max_pct"];
    $output["legend_scale"] = $arrowData["legend_scale"]; // the base unit for the legend. the client can draw however many units they want for the legend.
} elseif (isset($_GET["fams"])) {
    $famData = getFamilies($dbFile);
    $output["families"] = $famData["pfam"];
    $output["ipro_families"] = $famData["ipro"];
} else {
    $output["error"] = true;
    $output["message"] = "No query is selected.";
    echo json_encode($output);
    exit;
}



echo json_encode($output);






function getBlastId() {
    return "test";
}


function getFamilies($dbFile) {
    $output = array("pfam" => array(), "ipro" => array());

    $resultsDb = new SQLite3($dbFile);

    // Check if the table exists
    $checkSql = "SELECT name FROM sqlite_master WHERE type='table' AND name='families'";
    $dbQuery = $resultsDb->query($checkSql);
    if ($dbQuery->fetchArray()) {
        $famSql = "SELECT * FROM families";
        $dbQuery = $resultsDb->query($famSql);
        while ($row = $dbQuery->fetchArray()) {
            if (strlen($row["family"]) > 0) {
                if (substr($row["family"], 0, 2) == "PF")
                    array_push($output["pfam"], array("id" => $row["family"], "name" => "a name"));
                if (substr($row["family"], 0, 3) == "IPRO")
                    array_push($output["ipro"], array("id" => $row["ipro_family"], "name" => "a name"));
            }
        }
    }

    return $output;
}


function hasClusterIndex($dbFile) {
    $resultsDb = new SQLite3($dbFile);
    $result = $resultsDb->query("PRAGMA TABLE_INFO(attributes)");
    
    $hasClusterIndex = false;
    while ($result && $row = $result->fetchArray()) {
        $hasClusterIndex = $row["name"] == "cluster_index";
    }

    return $hasClusterIndex;
}


function getArrowData($items, $dbFile, $orderDataStruct, $window, $scaleFactor, $isDirectJob, $pageSize) {

    $orderData = $orderDataStruct['order'];
    $output = array();
    
    $resultsDb = new SQLite3($dbFile);
    $orderByClause = getOrderByClause($resultsDb);

    $S = microtime(true);
    $ids = parseIds($items, $orderDataStruct, $resultsDb, $orderByClause);
    $parseTime = microtime(true) - $S;

    $sidx = $eidx = 0;
    $useIndex = false;
    $isEod = false;
    if (is_array($pageSize)) {
        $sidx = $pageSize[0];
        $eidx = $pageSize[1];
        if ($sidx < 0)
            $sidx = 0;
        if ($eidx < $sidx)
            $eidx = $sidx;
        if ($eidx >= count($ids))
            $eidx = count($ids) - 1;

        $len = $eidx - $sidx + 1;
        $isEod = $eidx == count($ids) - 1;
        if ($sidx >= count($ids))
            $ids = array();
        else
            $ids = array_slice($ids, $sidx, $len);
        $useIndex = true;
    }

    $pageBounds = getPageLimits($pageSize);
    $startCount = $pageBounds['start'];
    $maxCount = $pageBounds['end'];
    $output["eod"] = "$startCount $maxCount";
    $output["counts"] = array("max" => count($ids), "invalid" => array());
    
    $minBp = 999999999999;
    $maxBp = -999999999999;
    $maxQueryWidth = -1;

    $queryTime = 0;
    $queryCount = 0;
    $nbTime = 0;
    $nbCount = 0;
    $procTime = 0;
    
    $output["data"] = array();
    $idCount = 0;
    for ($i = 0; $i < count($ids); $i++) {
        $id = $ids[$i][0];
        $evalue = $ids[$i][1];

        $idCol = is_numeric($id) ? "cluster_index" : "accession";
        $attrSql = "SELECT * FROM attributes WHERE $idCol = '$id' $orderByClause";
        $S = microtime(true); //TIME
        $dbQuery = $resultsDb->query($attrSql);
        $queryTime += microtime(true) - $S; //TIME
        $queryCount++; //TIME
        $row = $dbQuery->fetchArray(SQLITE3_ASSOC);
        if (!$row) {
            array_push($output["counts"]["invalid"], $id);
            continue;
        }

        if (!$useIndex) {
            if ($idCount++ < $startCount)
                continue;
            if (++$startCount > $maxCount)
                break;
        }

        $S = microtime(true);
        $attr = getQueryAttributes($row, $orderData, $isDirectJob);
        if ($attr['rel_start_coord'] < $minBp)
            $minBp = $attr['rel_start_coord'];
        if ($attr['rel_stop_coord'] > $maxBp)
            $maxBp = $attr['rel_stop_coord'];
        $queryWidth = $attr['rel_stop_coord'] - $attr['rel_start_coord'];
        if ($queryWidth > $maxQueryWidth)
            $maxQueryWidth = $queryWidth;
        $procTime += microtime(true) - $S;

        $nbSql = "SELECT * FROM neighbors WHERE gene_key = '" . $row['sort_key'] . "'";
        if ($window !== NULL) {
            //TODO: handle circular case
            $numClause = "num >= " . ($attr['num'] - $window) . " AND num <= " . ($attr['num'] + $window);
            $nbSql .= " AND " . $numClause;
        }
        $dbQuery = $resultsDb->query($nbSql);

        $S = microtime(true); //TIME
        $neighbors = array();
        while ($row = $dbQuery->fetchArray()) {
            $S = microtime(true);
            $nb = getNeighborAttributes($row);
            $procTime += microtime(true) - $S;
            if ($nb['rel_start_coord'] < $minBp)
                $minBp = $nb['rel_start_coord'];
            if ($nb['rel_stop_coord'] > $maxBp)
                $maxBp = $nb['rel_stop_coord'];
            array_push($neighbors, $nb);
        }
        $nbTime += microtime(true) - $S; //TIME
        $nbCount++;

        array_push($output["data"],
            array(
                'attributes' => $attr,
                'neighbors' => $neighbors,
            ));
    }

    $resultsDb->close();

    $output["eod"] = $useIndex ? $isEod : $startCount < $maxCount;
    $output["counts"]["displayed"] = $startCount;
    $output["time"] = "#Q=$queryCount TQ=$queryTime #N=$nbCount TN=$nbTime PROC=$procTime PARSE=$parseTime";
    if (!$output["eod"])
        $output["counts"]["displayed"]--;

    $output = computeRelativeCoordinates($output, $minBp, $maxBp, $maxQueryWidth, $scaleFactor);
    
    return $output;
}


function getOrderByClause($db) {
    $hasSortOrder = 0;

    $result = $db->query("PRAGMA table_info(attributes)");
    while ($row = $result->fetchArray()) {
        if ($row['name'] == "sort_order") {
            $hasSortOrder = 1;
            break;
        }
    }
    
    if ($hasSortOrder) {
        return "ORDER BY sort_order";
    } else {
        return "";
    }
}


function sortNodes($a, $b) {
    if ($a[1] == $b[1])
        return 0;
    return $a[1] < $b[1] ? -1 : 1;
}


function computeScaleFactor($minBp, $maxBp, $maxQueryWidth, $widthCap = 0) {

    $maxSide = (abs($maxBp) > abs($minBp)) ? abs($maxBp) : abs($minBp);
    $maxWidth = $maxSide * 2 + $maxQueryWidth;
    $actualMaxWidth = $maxWidth;
    if ($widthCap > 0 && $maxWidth > $widthCap)
        $maxWidth = $widthCap;
    if ($maxWidth < 0.000001)
        $maxWidth = 1;
    $scaleFactor = 300000 / $maxWidth;

    $legendScale = $maxBp - $minBp;

    return array($scaleFactor, $legendScale, $maxSide, $maxWidth, $actualMaxWidth);
}


function computeRelativeCoordinates($output, $minBp, $maxBp, $maxQueryWidth, $scaleFactor) {

    if ($scaleFactor !== NULL) {
        $maxWidth = 300000 / $scaleFactor; // scale factor is between 1 and 100 (specifying the scale factor as a percentage of the screen width = 1000AA) the data points in the file are given in bp so we x3 to get the factor in bp
        $maxQueryWidth = 0;
        $maxSide = $maxWidth / 2;
        $legendScale = $maxWidth;
    } else {
        list($scaleFactor, $legendScale, $maxSide, $maxWidth) = computeScaleFactor($minBp, $maxBp, $maxQueryWidth);
    }
    $minBp = -$maxSide;
    $maxBp = $maxSide + $maxQueryWidth;

    $minPct = 2;
    $maxPct = -2;
    for ($i = 0; $i < count($output["data"]); $i++) {
        $start = $output["data"][$i]["attributes"]["rel_start_coord"];
        $stop = $output["data"][$i]["attributes"]["rel_stop_coord"];
        $acStart = 0.5;
        $acWidth = ($stop - $start) / $maxWidth;
        $offset = 0.5 - ($start - $minBp) / $maxWidth;
        $output["data"][$i]["attributes"]["rel_start"] = $acStart;
        $output["data"][$i]["attributes"]["rel_width"] = $acWidth;
        $acEnd = $acStart + $acWidth;
        if ($acEnd > $maxPct)
            $maxPct = $acEnd;
        if ($acStart < $minPct)
            $minPct = $acStart;

        foreach ($output["data"][$i]["neighbors"] as $idx => $data2) {
            $nbStartBp = $output["data"][$i]["neighbors"][$idx]["rel_start_coord"];
            $nbWidthBp = $output["data"][$i]["neighbors"][$idx]["rel_stop_coord"] - $output["data"][$i]["neighbors"][$idx]["rel_start_coord"];
            $nbStart = ($nbStartBp - $minBp) / $maxWidth;
            $nbWidth = $nbWidthBp / $maxWidth;
            $nbStart += $offset;
            $nbEnd = $nbStart + $nbWidth;
            $output["data"][$i]["neighbors"][$idx]["rel_start"] = $nbStart;
            $output["data"][$i]["neighbors"][$idx]["rel_width"] = $nbWidth;
            if ($nbEnd > $maxPct)
                $maxPct = $nbEnd;
            if ($nbStart < $minPct)
                $minPct = $nbStart;
        }
    }

    $output["legend_scale"] = $legendScale;
    $output["min_pct"] = $minPct;
    $output["max_pct"] = $maxPct;
    $output["min_bp"] = $minBp;
    $output["max_bp"] = $maxBp;
    $output["scale_factor"] = $scaleFactor;

    return $output;
}


function parseQueryString($queryString) {
    $queryString = strtoupper($queryString);
    $queryString = str_replace("\n", ",", $queryString);
    $queryString = str_replace("\r", ",", $queryString);
    $queryString = str_replace(" ", ",", $queryString);
    $items = explode(",", $queryString);
    return $items;
}


// Get the start and ending ID indexes for the given cluster inputs
function getClusterIndices($items, $resultsDb) {
    $ranges = array();
    foreach ($items as $item) {
        if (is_numeric($item)) {
            $sql = "SELECT start_index, end_index FROM cluster_index WHERE cluster_num = $item";
            $queryResult = $resultsDb->query($sql);
            if ($queryResult) {
                $result = $queryResult->fetchArray(SQLITE3_ASSOC);
                array_push($ranges, array($result["start_index"], $result["end_index"]));
            }
        } else {
            //TODO: lookup the cluster_index field in the database for the given prot ID
            $sql = "SELECT cluster_index FROM attributes WHERE accession = '$item'";
            $queryResult = $resultsDb->query($sql);
            if ($queryResult) {
                $result = $queryResult->fetchArray(SQLITE3_ASSOC);
                array_push($ranges, array($result["cluster_index"], $result["cluster_index"]));
            }
        }
    }
    return $ranges;
}


function parseIds($items, $orderDataStruct, $resultsDb, $sortOrderClause, $countOnly = false) {

    $orderData = $orderDataStruct['order'];
    $centralId = $orderDataStruct['central_id'];

    $ids = array();
    $count = 0;

    foreach ($items as $item) {
        if (is_numeric($item)) {
            $clusterIds = getIdsFromDatabase($item, $resultsDb, $sortOrderClause, $countOnly);
            if ($countOnly) {
                $count += $clusterIds;
            } else {
                foreach ($clusterIds as $clusterId) {
                    $evalue = array_key_exists($clusterId, $orderData) ? $orderData[$clusterId][0] : -1;
                    $pctId = array_key_exists($clusterId, $orderData) ? $orderData[$clusterId][1] : -1;
                    array_push($ids, array($clusterId, $evalue, $pctId));
                }
                $count += count($clusterIds);
            }
        } else if ($item) {
            if (idExists($item, $resultsDb)) {
                if (!$countOnly) {
                    $evalue = array_key_exists($item, $orderData) ? $orderData[$item][0] : -1;
                    $pctId = array_key_exists($item, $orderData) ? $orderData[$clusterId][1] : -1;
                    array_push($ids, array($item, $evalue, $pctId));
                }
                $count++;
            }
        }
    }

    // This will be useful when we start sorting/grouping
    //usort($ids, "sortNodes");
    //if ($centralId)
    //    array_unshift($ids, array($centralId, 0));

    if ($countOnly)
        return $count;
    else
        return $ids;
}


function getPageLimits($pageSize) {
    $startCount = 0;
    $maxCount = 100000000;
    if (array_key_exists("page", $_GET)) {
        $parm = $_GET["page"];
        $dashPos = strpos($parm, "-");
        if ($dashPos !== FALSE) {
            $startPage = substr($parm, 0, $dashPos);
            $endPage = substr($parm, $dashPos + 1);
            $startCount = $startPage * $pageSize;
            $maxCount = $endPage * $pageSize + $pageSize;
        } else {
            $page = intval($parm);
            if ($page >= 0 && $page <= 10000) { // error check to limit to 10000 pages 
                $startCount = $page * $pageSize;
                $maxCount = $startCount + $pageSize;
            }
        }
    }

    return array('start' => $startCount, 'end' => $maxCount);
}


function getStats($query, $resultsDb, $window, $orderDataStruct) {
    $queryItems = parseQueryString($query);
    $S = microtime(true);
    $indexRange = getClusterIndices($queryItems, $resultsDb); // true = countOnly
    $parseTime = microtime(true) - $S;
    $mapFn = function($e) { return $e[0]; };
    $allIds = expandRange($indexRange);
    $count = count($allIds);

    $idx = array_slice($allIds, 0, 100);
    $numToCheck = max(1, round(intval($count / 200) / 10) * 10);
    if ($count > 100) {
        for ($i = 100; $i < $count; $i++) {
            if (!(rand(1, $count) % $numToCheck))
                array_push($idx, $allIds[$i]);
        }
    }

    $scaleCap = 40000; // Limit the scale cap by default to 40000 bp. The user can zoom out.
    $S = microtime(true); //TIME
    list($scaleFactor, $legendScale, $min, $max, $qWidth, $actualMaxWidth, $timeData) = computeClusterScaleFactor($idx, $resultsDb, $window, $scaleCap);
    $procTime = microtime(true) - $S;

    $stats = array("max_index" => $count - 1, "scale_factor" => $scaleFactor, "legend_scale" => $legendScale,
        "min_bp" => $min, "max_bp" => $max, "query_width" => $qWidth, "actual_max_width" => $actualMaxWidth, "time_data" => $timeData . " PROC=$procTime PARSE=$parseTime",
        "num_checked" => count($idx), "index_range" => $indexRange);

    return $stats;
}


function expandRange($range) {
    $idx = array();
    for ($i = 0; $i < count($range); $i++) {
        $idx = array_merge($idx, range($range[$i][0], $range[$i][1]));
    }
    return $idx;
}


function coordCompare($row, &$minBp, &$maxBp) {
    if ($row["start"] < $minBp)
        $minBp = $row["start"];
    if ($row["stop"] > $maxBp)
        $maxBp = $row["stop"];
}


function computeClusterScaleFactor($idx, $resultsDb, $window, $scaleCap) {
    
    $minBp = 999999999999;
    $maxBp = -999999999999;
    $maxQueryWidth = -1;

    $start = microtime(true); //TIME
    $dbQueryTime = 0; //TIME
    $dbFetchTime = 0; //TIME
    $dbQueries = 0; //TIME
    $dbFetch = 0; //TIME

    for ($i = 0; $i < count($idx); $i++) {
        $clIndex = $idx[$i];
        $first = true;
        $attrSql = "SELECT A.rel_start AS start, A.rel_stop AS stop, A.sort_key AS key, A.num AS num FROM attributes AS A WHERE A.cluster_index = $clIndex";
        $dbStart = microtime(true); //TIME
        $dbQuery = $resultsDb->query($attrSql);
        $dbQueryTime += microtime(true) - $dbStart; //TIME
        $dbQueries++; //TIME

        $dbStart = microtime(true); //TIME
        $row = $dbQuery->fetchArray(SQLITE3_ASSOC);
        $dbFetchTime += microtime(true) - $dbStart; //TIME
        $dbFetch++; //TIME
        $key = "";
        if ($row) {
            coordCompare($row, $minBp, $maxBp);
            $key = $row["key"];
            $queryWidth = $row["stop"] - $row["start"];
            if ($queryWidth > $maxQueryWidth)
                $maxQueryWidth = $queryWidth;
        }

        if (!$key)
            continue;

        $nbSql = "SELECT N.rel_start AS start, N.rel_stop AS stop, N.accession AS id FROM neighbors AS N WHERE N.gene_key = '$key'";
        if ($window !== NULL) {
            $numClause = "num >= " . ($row["num"] - $window) . " AND num <= " . ($row["num"] + $window);
            $nbSql .= " AND " . $numClause;
        }
        $dbStart = microtime(true); //TIME
        $dbQuery = $resultsDb->query($nbSql);
        $dbQueryTime += microtime(true) - $dbStart; //TIME
        $dbQueries++; //TIME
        $dbStart = microtime(true); //TIME
        while ($row = $dbQuery->fetchArray(SQLITE3_ASSOC)) {
            $dbFetchTime += microtime(true) - $dbStart; //TIME
            $dbFetch++; //TIME
            coordCompare($row, $minBp, $maxBp);
            $dbStart = microtime(true); //TIME
        }
    }

    $total = microtime(true) - $start;

    $timeData = "#Ids: " . count($idx) . ", #Queries: $dbQueries, QueryTime: $dbQueryTime, #Fetch: $dbFetch, FetchTime: $dbFetchTime, Total: $total";

    list ($scaleFactor, $legendScale, $maxSide, $maxWidth, $actualMaxWidth) = computeScaleFactor($minBp, $maxBp, $maxQueryWidth, $scaleCap);
    return array($scaleFactor, $legendScale, $minBp, $maxBp, $maxQueryWidth, $actualMaxWidth, $timeData);
}


function getQueryAttributes($row, $orderData, $isDirectJob) {
    $attr = array();
    $attr['accession'] = $row['accession'];
    $attr['id'] = $row['id'];
    $attr['num'] = $row['num'];
    $attr['family'] = explode("-", $row['family']);
    if (isset($row['ipro_family']))
        $attr['ipro_family'] = explode("-", $row['ipro_family']);
    else
        $attr['ipro_family'] = array();
    $attr['start'] = $row['start'];
    $attr['stop'] = $row['stop'];
    $attr['rel_start_coord'] = $row['rel_start'];
    $attr['rel_stop_coord'] = $row['rel_stop'];
    $attr['strain'] = $row['strain'];
    $attr['direction'] = $row['direction'];
    $attr['type'] = $row['type'];
    $attr['seq_len'] = $row['seq_len'];
    $attr['organism'] = rtrim($row['organism']);
    $attr['taxon_id'] = $row['taxon_id'];
    $attr['anno_status'] = $row['anno_status'];
    $attr['desc'] = $row['desc'];
    if (array_key_exists('evalue', $row) && $row['evalue'] !== NULL)
        $attr['evalue'] = $row['evalue'];
    elseif (! $isDirectJob && array_key_exists('cluster_num', $row))
        $attr['cluster_num'] = $row['cluster_num'];

    if (count($attr['family']) > 0 && $attr['family'][0] == "")
        $attr['family'][0] = "none-query";
    if (count($attr['ipro_family']) > 0 && $attr['ipro_family'][0] == "")
        $attr['ipro_family'][0] = "none-query";
    $familyCount = count($attr['family']);

    $familyDesc = explode(";", $row['family_desc']);
    if (count($familyDesc) == 1) {
        $familyDesc = explode("-", $row['family_desc']);
        if ($familyDesc[0] == "")
            $familyDesc[0] = "Query without family";
    }
    $attr['family_desc'] = $familyDesc;
    if (count($attr['family_desc']) < $familyCount) {
        if (count($attr['family_desc']) > 0)
            $attr['family_desc'] = array_fill(0, $familyCount, $attr['family_desc'][0]);
        else
            $attr['family_desc'] = array_fill(0, $familyCount, "none");
    }

    $iproFamilyCount = isset($attr['ipro_family']) ? count($attr['ipro_family']) : 0;
    $iproFamilyDesc = isset($row['ipro_family_desc']) ? explode(";", $row['ipro_family_desc']) : array();
    if (count($iproFamilyDesc) == 1) {
        $iproFamilyDesc = explode("-", $row['ipro_family_desc']);
        if ($iproFamilyDesc[0] == "")
            $iproFamilyDesc[0] = "Query without family";
    }
    $attr['ipro_family_desc'] = $iproFamilyDesc;
    if (count($attr['ipro_family_desc']) < $iproFamilyCount) {
        if (count($attr['ipro_family_desc']) > 0)
            $attr['ipro_family_desc'] = array_fill(0, $iproFamilyCount, $attr['ipro_family_desc'][0]);
        else
            $attr['ipro_family_desc'] = array_fill(0, $iproFamilyCount, "none");
    }
    
    $attr['pfam'] = $attr['family']; // will migrate to this eventually
    $attr['interpro'] = $attr['ipro_family'];
    $attr['pfam_desc'] = $attr['family_desc'];
    $attr['interpro_desc'] = $attr['ipro_family_desc'];
    
    if (array_key_exists("color", $row))
        $attr['color'] = explode(",", $row['color']);
    if (count($attr['color']) < $familyCount) {
        if (count($attr['color']) > 0)
            $attr['color'] = array_fill(0, $familyCount, $attr['color'][0]);
        else
            $attr['color'] = array_fill(0, $familyCount, "grey");
    }

    if (array_key_exists("sort_order", $row))
        $attr['sort_order'] = $row['sort_order'];
    else
        $attr['sort_order'] = -1;
    
    if (array_key_exists("is_bound", $row))
        $attr['is_bound'] = $row['is_bound'];
    else
        $attr['is_bound'] = 0;

    $pid = array_key_exists($attr['accession'], $orderData) ? $orderData[$attr['accession']][1] : -1;
    $attr['pid'] = $pid;

    if (strlen($attr['organism']) > 0 && substr_compare($attr['organism'], ".", -1) === 0)
        $attr['organism'] = substr($attr['organism'], 0, strlen($attr['organism'])-1);

    return $attr;
}


function getNeighborAttributes($row) {
    $nb = array();
    $nb['accession'] = $row['accession'];
    $nb['id'] = $row['id'];
    $nb['num'] = $row['num'];
    $nb['family'] = explode("-", $row['family']);
    if (isset($row['ipro_family']))
        $nb['ipro_family'] = explode("-", $row['ipro_family']);
    else
        $nb['ipro_family'] = array();
    $nb['start'] = $row['start'];
    $nb['stop'] = $row['stop'];
    $nb['rel_start_coord'] = $row['rel_start'];
    $nb['rel_stop_coord'] = $row['rel_stop'];
    $nb['direction'] = $row['direction'];
    $nb['type'] = $row['type'];
    $nb['seq_len'] = $row['seq_len'];
    $nb['anno_status'] = $row['anno_status'];
    $nb['desc'] = $row['desc'];

    $familyCount = count($nb['family']);

    $familyDesc = explode(";", $row['family_desc']);
    if (count($familyDesc) == 1)
        $familyDesc = explode("-", $row['family_desc']);
    $nb['family_desc'] = $familyDesc;
    if (count($nb['family_desc']) < $familyCount) {
        if (count($nb['family_desc']) > 0)
            $nb['family_desc'] = array_fill(0, $familyCount, $nb['family_desc'][0]);
        else
            $nb['family_desc'] = array_fill(0, $familyCount, "none");
    }
    
    $iproFamilyCount = count($nb['ipro_family']);
    $iproFamilyDesc = isset($row['ipro_family_desc']) ? explode(";", $row['ipro_family_desc']) : array();
    if (count($iproFamilyDesc) == 1)
        $iproFamilyDesc = explode("-", $row['ipro_family_desc']);
    $nb['ipro_family_desc'] = $iproFamilyDesc;
    if (count($nb['ipro_family_desc']) < $iproFamilyCount) {
        if (count($nb['ipro_family_desc']) > 0)
            $nb['ipro_family_desc'] = array_fill(0, $iproFamilyCount, $nb['ipro_family_desc'][0]);
        else
            $nb['ipro_family_desc'] = array_fill(0, $iproFamilyCount, "none");
    }
    
    if (array_key_exists("color", $row))
        $nb['color'] = explode(",", $row['color']);
    if (count($nb['color']) < $familyCount) {
        if (count($nb['color']) > 0)
            $nb['color'] = array_fill(0, count($nb['family']), $nb['color'][0]);
        else
            $nb['color'] = array_fill(0, count($nb['family']), "grey");
    }
    
    $nb['pfam'] = $nb['family']; // will migrate to this eventually
    $nb['interpro'] = $nb['ipro_family'];
    $nb['pfam_desc'] = $nb['family_desc'];
    $nb['interpro_desc'] = $nb['ipro_family_desc'];
    
    return $nb;
}


function getIdsFromDatabase($clusterId, $resultsDb, $sortOrderClause, $countOnly = false) {
    if (!is_numeric($clusterId))
        return array();

    if ($countOnly)
        $sql = "SELECT COUNT(accession) FROM attributes WHERE cluster_num = $clusterId AND accession IS NOT NULL";
    else
        $sql = "SELECT accession FROM attributes WHERE cluster_num = $clusterId AND accession IS NOT NULL $sortOrderClause";
    $dbQuery = $resultsDb->query($sql);

    $ids = array();
    if (!$dbQuery)
        return $ids;

    if ($countOnly) {
        $result = $dbQuery->fetchArray();
        if ($result) {
            $ids = $result[0];
        } else {
            $ids = -1;
        }
    } else {
        $ids = array();
        while ($row = $dbQuery->fetchArray()) {
            array_push($ids, $row['accession']);
        }
    }
    
    return $ids;
}


function idExists($id, $resultsDb) {
    $sql = "SELECT accession FROM attributes WHERE accession = '$id' AND accession IS NOT NULL";
    $dbQuery = $resultsDb->query($sql);
    return $dbQuery ? true : false;
}


function getIdsFromClusterFile($clusterId, $dataDir) {
    if (!is_numeric($clusterId))
        return array();

    $filePath = "$dataDir/cluster-data/cluster_UniProt_IDs_" . $clusterId . ".txt";
    if (!file_exists($filePath))
        return array();

    $flags = FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES;
    $ids = file($filePath, $flags);
    return $ids;
}


function getDefaultOrder() {
    $result = array('order' => array(), 'central_id' => "");
    return $result;
}


//function getOrder($blastId, $items, $dbFile, $jobDataDir, $blastCacheDir, $gnn) {
//
//    $cwd = getcwd();
//
//    $resultsDb = new SQLite3($dbFile);
//
//    $centralId = "";
//    $blastIds = array();
//    foreach ($items as $item) {
//        if (is_numeric($item)) {
//            $ids = getIdsFromCluster($item, $jobDataDir);
//
//            if (!$centralId) {
//                $sql = "SELECT * FROM cluster_degree where cluster_num = '$item'";
//                $dbQuery = $resultsDb->query($sql);
//                $row = $dbQuery->fetchArray(SQLITE3_ASSOC);
//
//                if (!$row && count($ids) > 0)
//                    $centralId = $ids[0];
//                else
//                    $centralId = $row["accession"];
//            }
//
//            foreach ($ids as $id)
//                $blastIds[$id] = 1;
////            array_push($blastIds, $ids);
//        } else if ($item) {
//            if (!$centralId)
//                $centralId = $item;
//            $blastIds[$item] = 1;
////            array_push($blastIds, $item);
//        }
//    }
//
//    $resultsDb->close();
//
//    if (!$centralId)
//        return FALSE;
//
//    $index = array_search($centralId, $blastIds);
//    if ($index !== FALSE)
//        unset($blastIds[$index]);
//
//    $blastMod = settings::get_blast_module();
//    $blastDir = "$blastCacheDir/blast-$blastId";
//    if (!file_exists($blastDir))
//        mkdir($blastDir);
//
//    $blastInputFile = "$blastDir/blast.input";
//    $blastOutputFile = "$blastDir/blast.output";
//    $blasthits = 100000; //TODO: find this
//    $evalue = "1e-5"; //TODO: find this
//
//    $exec = "source /etc/profile.d/modules.sh; ";
//    $exec .= "module load $blastMod; ";
//    $exec .= "fastacmd -d $jobDataDir/blast/database -s $centralId > $blastInputFile; ";
//    $exec .= "blastall -p blastp -i $blastInputFile -d $jobDataDir/blast/database -m 8 -e $evalue -b $blasthits -o $blastOutputFile";
//
//    $exitStatus = 1;
//    $outputArray = array();
//    $cmdOutput = exec($exec, $outputArray, $exitStatus);
//    $cmdOutput = trim(rtrim($cmdOutput));
//    //TODO: handle errors
//
//    $order = getIdOrder($blastOutputFile, $blastIds);
//
//    $result = array('order' => $order, 'central_id' => $centralId);
//    return $result;
//}
//function getIdOrder($blastOutputFile, $blastIds) {
//    $order = array();
//
//    $data = file_get_contents($blastOutputFile);
//    $lines = preg_split("/(\r\n|\r|\n)/", $data);
//    foreach ($lines as $line) {
//        $line = rtrim($line);
//        $parts = explode("\t", $line);
//        if (count($parts) < 11)
//            continue;
//
//        if (array_key_exists($parts[1], $blastIds)) {
//            $order[$parts[1]] = array(floatval($parts[10]), floatval($parts[2]));
//        }
//    }
//
//    return $order;
//}

?>
