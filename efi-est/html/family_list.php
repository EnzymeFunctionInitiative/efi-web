<?php
require_once("../includes/main.inc.php");
require_once(__BASE_DIR__ . "/includes/login_check.inc.php");

$defaultFilterKey = "pfam";
$defaultFilter = "PF%";
$defaultTitle = "Pfam Families";
$clanKey = "clan";
$clanMapKey = "pfam-clan";

$Filters = array("pfam" => $defaultFilter, "interpro" => "IPR%", $clanKey => "CL%", "ssf" => "SSF%", "g3d" => "G3D%");
$LinkTemplate = array("pfam" => "http://pfam.xfam.org/family/%FAMILY%",
                      "interpro" => "http://www.ebi.ac.uk/interpro/entry/%FAMILY%",
                      $clanKey => "http://pfam.xfam.org/clan/%FAMILY%",
                      "ssf" => "",
                      "g3d" => "");
$Titles = array("pfam" => $defaultTitle, "interpro" => "InterPro Families", $clanKey => "Pfam Clans",
                "ssf" => "SSF Families", "g3d" => "Gene3D Families", $clanMapKey => "Clan-Pfam Mapping");

$isClan = false;
$isPfamClanMap = false;
$familyFilter = $defaultFilter;
$pageTitle = $defaultTitle;
$filter = $defaultFilterKey;

if (isset($_GET["filter"])) {
    $filter = $_GET["filter"];
    if (array_key_exists($filter, $Filters)) {
        $familyFilter = $Filters[$filter];
        $pageTitle = $Titles[$filter];
    }
    $isClan = $filter == $clanKey;
    $isPfamClanMap = $filter == $clanMapKey;
}

if ($isPfamClanMap) {
    $sql = "
        SELECT C.clan_id, C.pfam_id, P.short_name, P.num_members AS pfam_count,
            P.num_uniref90_members as pfam_uniref90_count, P.num_uniref50_members as pfam_uniref50_count,
            PC.short_name AS clan_short_name, PC.num_members AS clan_count,
            PC.num_uniref90_members AS clan_uniref90_count, PC.num_uniref50_members AS clan_uniref50_count
        FROM PFAM_clans AS C
            JOIN family_info AS P ON C.pfam_id = P.family 
            JOIN family_info AS PC ON C.clan_id = PC.family
        WHERE C.clan_id <> '' ORDER BY C.clan_id, C.pfam_id
        ";
    $dbResult = $db->query($sql);
} else {
    $sql = "SELECT * FROM family_info WHERE family LIKE \"$familyFilter\"";
    $dbResult = $db->query($sql);
}

function get_tab_style($filter, $category, $defaultTab = false) {
    if ((!$filter && $defaultTab) || $filter == $category)
        echo "class=\"active\"";
    else
        echo "";
}

if (isset($_GET["as-table"])) {
    $table_filename = preg_replace("/\s/", "_", $pageTitle) . "_info.txt";

    $hdr = "Family";
    if ($isClan)
        $hdr = "Clan";

    $headers = array("$hdr ID", "$hdr Short Name", "$hdr Size", "UniRef90 Size", "UniRef50 Size");

    $table_string = implode("\t", $headers) . "\n";
    
    foreach ($dbResult as $row) {
        $line = implode("\t", array($row["family"], $row["short_name"], 
                                    number_format($row["num_members"]),
                                    number_format($row["num_uniref90_members"]),
                                    number_format($row["num_uniref50_members"])));
        $table_string .= $line . "\n";
    }

    send_table($table_filename, $table_string);
    exit(0);
}



$LightweightTabs = true;
require_once("inc/header.inc.php");

?>

<h2><?php echo $pageTitle; ?></h2>

<p>
These data are sourced from the <a href="http://www.uniprot.org">UniProt</a> and
<a href="http://www.ebi.ac.uk/interpro/">EMBL-EBI InterPro</a> databases.
</p>

<div class="tabs">
    <ul class="tab-headers">
        <li <?php get_tab_style($filter, "pfam", true); ?>><a href="family_list.php?filter=pfam">Pfam Families</a></li>
        <li <?php get_tab_style($filter, "interpro"); ?>><a href="family_list.php?filter=interpro">InterPro Families</a></li>
        <li <?php get_tab_style($filter, "clan"); ?>><a href="family_list.php?filter=clan">Pfam Clans</a></li>
        <li <?php get_tab_style($filter, "pfam-clan"); ?>><a href="family_list.php?filter=pfam-clan">Clan-Pfam Mapping</a></li>
    </ul>

    <div class="tab-content">
        <div class="tab active">

<?php
if ($isPfamClanMap) { ?>

            <table class="family" width="100%" border="0">
                <thead>
                    <th>Clan ID</th>
                    <th>Clan Short Name</th>
                    <th>Clan Size</th>
                    <th>Clan UniRef90 Size</th>
                    <th>Pfam ID</th>
                    <th>Pfam Short Name</th>
                    <th>Pfam Size</th>
                    <th>Pfam UniRef90 Size</th>
                </thead>
                <tbody>

<?php

    $hasLink = true;
    $templateClan = $hasLink ? $LinkTemplate[$clanKey] : "";
    $templatePfam = $hasLink ? $LinkTemplate["pfam"] : "";
    $lastClan = "";
    foreach ($dbResult as $row) {
        $theClan = $row["clan_id"];
        $theClanName = $row["clan_short_name"];
        $theFamily = $row["pfam_id"];
        $thePfamName = $row["short_name"];
        $pfamCount = number_format($row["pfam_count"]);
        $pfamUniRef90Count = number_format($row["pfam_uniref90_count"]);
        $clanCount = number_format($row["clan_count"]);
        $clanUniRef90Count = number_format($row["clan_uniref90_count"]);

        $rowStyle = "";
        if ($theClan != $lastClan) {
            $lastClan = $theClan;
            $rowStyle = "style=\"border-top: 2px black solid;\"";
        } else {
            $theClan = "";
            $theClanName = "";
            $clanCount = "";
            $clanUniRef90Count = "";
        }

        $linkStartClan = $hasLink ? "<a href=\"" . str_replace("%FAMILY%", $theClan, $templateClan) . "\">" : "";
        $linkStartPfam = $hasLink ? "<a href=\"" . str_replace("%FAMILY%", $theFamily, $templatePfam) . "\">" : "";
        $linkEnd = $hasLink ? "</a>" : "";
        echo "                    <tr>\n";
        echo "                        <td $rowStyle>$linkStartClan$theClan$linkEnd</td>\n";
        echo "                        <td $rowStyle>$theClanName</td>\n";
        echo "                        <td $rowStyle class=\"right-align\">$clanCount</td>\n";
        echo "                        <td $rowStyle class=\"right-align\">$clanUniRef90Count</td>\n";
        echo "                        <td $rowStyle>$linkStartPfam$theFamily$linkEnd</td>\n";
        echo "                        <td $rowStyle>$thePfamName</td>\n";
        echo "                        <td $rowStyle class=\"right-align\">$pfamCount</td>\n";
        echo "                        <td $rowStyle class=\"right-align\">$pfamUniRef90Count</td>\n";
        echo "                    </tr>\n";
    }

} else {
    $hdr = "Family";
    if ($isClan)
        $hdr = "Clan";

?>
            <a href="?<?php echo $_SERVER["QUERY_STRING"] . "&as-table=1"; ?>">Download as tab-separated text file.</a>
            <table class="family" width="100%" border="0">
                <thead>
                    <th><?php echo $hdr; ?> ID</th>
                    <th><?php echo $hdr; ?> Short Name</th>
                    <th><?php echo $hdr; ?> Size</th>
                    <th>UniRef 90 Size</th>
                    <th>UniRef 50 Size</th>
                </thead>
                <tbody>
<?php
    
    $hasLink = array_key_exists($filter, $LinkTemplate);
    $template = $hasLink ? $LinkTemplate[$filter] : "";
    foreach ($dbResult as $row) {
        $fam = $row["family"];
        $linkStart = $hasLink ? "<a href=\"" . str_replace("%FAMILY%", $fam, $template) . "\">" : "";
        $linkEnd = $linkStart ? "</a>" : "";
        echo "                    <tr>\n";
        echo "                        <td>$linkStart$fam$linkEnd</td>\n";
        echo "                        <td>" . $row["short_name"] . "</td>\n";
        echo "                        <td class=\"right-align\">" . number_format($row["num_members"]) . "</td>\n";
        echo "                        <td class=\"right-align\">" . number_format($row["num_uniref90_members"]) . "</td>\n";
        echo "                        <td class=\"right-align\">" . number_format($row["num_uniref50_members"]) . "</td>\n";
        echo "                    </tr>\n";
    }

}
?>

                </tbody>
            </table>
        </div>
    </div>
</div>


<p></p>

<p class="center"><a href="index.php"><button class="dark">Run EST</button></a></p>


<?php

require_once("inc/footer.inc.php");


function send_table($table_filename, $table_string) {
    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $table_filename . '"');
    header('Content-Length: ' . strlen($table_string));
    ob_clean();
    echo $table_string;
}


?>


