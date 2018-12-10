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
    elseif (time() < $gnn->get_time_completed() + settings::get_retention_days()) {
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

$pageSize = 20;
if (isset($_GET["pagesize"]) && is_numeric($_GET["pagesize"])) {
    $pageSize = $_GET["pagesize"];
}

$output["message"] = "";
$output["error"] = false;
$output["eod"] = false;

if ($message) {
    $output["message"] = $message;
    $output["error"] = true;
    echo json_encode($output);
    exit;
}


if (array_key_exists("query", $_GET)) {
    $queryString = strtoupper($_GET["query"]);
    $queryString = str_replace("\n", ",", $queryString);
    $queryString = str_replace("\r", ",", $queryString);
    $queryString = str_replace(" ", ",", $queryString);
    $items = explode(",", $queryString);

    $blastId = getBlastId();
    //$orderData = getOrder($blastId, $items, $dbFile, $blastCacheDir, $gnn);
    $orderData = getDefaultOrder();
    $arrowData = getArrowData($items, $dbFile, $orderData, $window, $isDirectJob, $pageSize);
    $output["eod"] = $arrowData["eod"];
    $output["counts"] = $arrowData["counts"];
    $output["IDS"] = $arrowData["IDS"];
    $output["data"] = $arrowData["data"];
    $output["min_bp"] = $arrowData["min_bp"];
    $output["max_bp"] = $arrowData["max_bp"];
    $output["min_pct"] = $arrowData["min_pct"];
    $output["max_pct"] = $arrowData["max_pct"];
    $output["legend_scale"] = $arrowData["legend_scale"]; // the base unit for the legend. the client can draw however many units they want for the legend.
}
else if (array_key_exists("fams", $_GET)) {
    $famData = getFamilies($dbFile);
    $output["families"] = $famData["pfam"];
    $output["ipro_families"] = $famData["ipro"];
}
else {
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



function getArrowData($items, $dbFile, $orderDataStruct, $window, $isDirectJob, $pageSize) {

    $orderData = $orderDataStruct['order'];
    $output = array();
    
    $resultsDb = new SQLite3($dbFile);
    $orderByClause = getOrderByClause($resultsDb);

    $ids = parseIds($items, $orderDataStruct, $resultsDb, $orderByClause);
    $output["IDS"] = $ids;

    $pageBounds = getPageLimits($pageSize);
    $startCount = $pageBounds['start'];
    $maxCount = $pageBounds['end'];
    $output["eod"] = "$startCount $maxCount";
    $output["counts"] = array("max" => count($ids), "invalid" => array());
    
    $minBp = 999999999999;
    $maxBp = -999999999999;
    $maxQueryWidth = -1;
    
    $output["data"] = array();
    $idCount = 0;
    for ($i = 0; $i < count($ids); $i++) {
        $id = $ids[$i][0];
        $evalue = $ids[$i][1];

        $attrSql = "SELECT * FROM attributes WHERE accession = '$id' $orderByClause";
        $dbQuery = $resultsDb->query($attrSql);
        $row = $dbQuery->fetchArray(SQLITE3_ASSOC);
        if (!$row) {
            array_push($output["counts"]["invalid"], $id);
            continue;
        }
    
        if ($idCount++ < $startCount)
            continue;
    
        if (++$startCount > $maxCount)
            break;

        $attr = getQueryAttributes($row, $orderData, $isDirectJob);
        if ($attr['rel_start_coord'] < $minBp)
            $minBp = $attr['rel_start_coord'];
        if ($attr['rel_stop_coord'] > $maxBp)
            $maxBp = $attr['rel_stop_coord'];
        $queryWidth = $attr['rel_stop_coord'] - $attr['rel_start_coord'];
        if ($queryWidth > $maxQueryWidth)
            $maxQueryWidth = $queryWidth;


        $nbSql = "SELECT * FROM neighbors WHERE gene_key = '" . $row['sort_key'] . "'";
        if ($window !== NULL) {
            //TODO: handle circular case
            $numClause = "num >= " . ($attr['num'] - $window) . " AND num <= " . ($attr['num'] + $window);
            $nbSql .= " AND " . $numClause;
        }
        $dbQuery = $resultsDb->query($nbSql);
    
        $neighbors = array();
        while ($row = $dbQuery->fetchArray()) {
            $nb = getNeighborAttributes($row);
            if ($nb['rel_start_coord'] < $minBp)
                $minBp = $nb['rel_start_coord'];
            if ($nb['rel_stop_coord'] > $maxBp)
                $maxBp = $nb['rel_stop_coord'];
            array_push($neighbors, $nb);
        }

        array_push($output["data"],
            array(
                'attributes' => $attr,
                'neighbors' => $neighbors,
            ));
    }

    $resultsDb->close();

    $output["eod"] = $startCount < $maxCount;
    $output["counts"]["displayed"] = $startCount;
    if (!$output["eod"])
        $output["counts"]["displayed"]--;

    $output = computeRelativeCoordinates($output, $minBp, $maxBp, $maxQueryWidth);
    
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


function computeRelativeCoordinates($output, $minBp, $maxBp, $maxQueryWidth) {
    $maxSide = (abs($maxBp) > abs($minBp)) ? abs($maxBp) : abs($minBp);
    $maxWidth = $maxSide * 2 + $maxQueryWidth;
    $minBp = -$maxSide;
    $maxBp = $maxSide + $maxQueryWidth;

    $legendScale = $maxWidth; //100 / ($maxBp - $minBp);
//    $legendScale = ($maxBp - $minBp) / 100;
//    die("$maxBp $minBp $maxSide $maxQueryWidth $maxWidth");
//    die($legendScale);

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

    $output["legend_scale"] = ($maxBp - $minBp);
    //$output["legend_scale"] = (($maxBp - $minBp) * ($maxPct - $minPct)) / 2 - $maxQueryWidth;// / 2 - $maxQueryWidth;
    $output["min_pct"] = $minPct;
    $output["max_pct"] = $maxPct;
    $output["min_bp"] = $minBp;
    $output["max_bp"] = $maxBp;

    return $output;
}


function parseIds($items, $orderDataStruct, $resultsDb, $sortOrderClause) {

    $orderData = $orderDataStruct['order'];
    $centralId = $orderDataStruct['central_id'];

    $ids = array();

    foreach ($items as $item) {
        if (is_numeric($item)) {
            $clusterIds = getIdsFromDatabase($item, $resultsDb, $sortOrderClause);
            foreach ($clusterIds as $clusterId) {
                $evalue = array_key_exists($clusterId, $orderData) ? $orderData[$clusterId][0] : -1;
                $pctId = array_key_exists($clusterId, $orderData) ? $orderData[$clusterId][1] : -1;
                array_push($ids, array($clusterId, $evalue, $pctId));
            }
        }
        else if ($item) {
            $evalue = array_key_exists($item, $orderData) ? $orderData[$item][0] : -1;
            $pctId = array_key_exists($item, $orderData) ? $orderData[$clusterId][1] : -1;
            array_push($ids, array($item, $evalue, $pctId));
        }
    }

    // This will be useful when we start sorting/grouping
    //usort($ids, "sortNodes");
    //if ($centralId)
    //    array_unshift($ids, array($centralId, 0));

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
    
    return $nb;
}


function getIdsFromDatabase($clusterId, $resultsDb, $sortOrderClause) {
    if (!is_numeric($clusterId))
        return array();

    $sql = "SELECT accession FROM attributes WHERE cluster_num = '$clusterId' $sortOrderClause";
    $dbQuery = $resultsDb->query($sql);

    $ids = array();
    while ($row = $dbQuery->fetchArray()) {
        array_push($ids, $row['accession']);
    }
    
    return $ids;
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
