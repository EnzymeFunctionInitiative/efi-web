<?php
require_once "../includes/main.inc.php";
require_once "inc/header.inc.php";

$defaultFilter = "PF%";
$defaultTitle = "Pfam Families";
$clanKey = "clan";
$clanMapKey = "pfam-clan";

$Filters = array("pfam" => $defaultFilter, "interpro" => "IPR%", $clanKey => "CL%", "ssf" => "SSF%", "g3d" => "G3D%");
$Titles = array("pfam" => $defaultTitle, "interpro" => "InterPro Families", $clanKey => "Pfam Clans",
                "ssf" => "SSF Families", "g3d" => "Gene3D Families", $clanMapKey => "Clan-Pfam Mapping");

$isClan = false;
$isPfamClanMap = false;
$familyFilter = $defaultFilter;
$pageTitle = $defaultTitle;
$filter = "";

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
        SELECT C.clan_id, C.pfam_id, P.short_name, PC.short_name AS clan_short_name
        FROM PFAM_clans AS C
            JOIN family_info AS P ON C.pfam_id = P.family 
            JOIN family_info as PC on C.clan_id = PC.family
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
                    <th>Pfam ID</th>
                    <th>Pfam Short Name</th>
                </thead>
                <tbody>

<?php

    $lastClan = "";
    foreach ($dbResult as $row) {
        $theClan = $row["clan_id"];
        $theClanName = $row["clan_short_name"];
        $rowStyle = "";
        if ($theClan != $lastClan) {
            $lastClan = $theClan;
            $rowStyle = "style=\"border-top: 2px black solid;\"";
        } else {
            $theClan = "";
            $theClanName = "";
        }
        echo "                    <tr>\n";
        echo "                        <td $rowStyle>" . $theClan . "</td>\n";
        echo "                        <td $rowStyle>" . $theClanName . "</td>\n";
        echo "                        <td $rowStyle>" . $row["pfam_id"] . "</td>\n";
        echo "                        <td $rowStyle>" . $row["short_name"] . "</td>\n";
        echo "                    </tr>\n";
    }

} else {
    if ($isClan) { ?>

            <table class="family" width="100%" border="0">
                <thead>
                    <th>Clan ID</th>
                    <th>Clan Short Name</th>
                    <th>Clan Size</th>
                    <th>UniRef90 Size</th>
                </thead>
                <tbody>

<?php     } else { ?>

            <table class="family" width="100%" border="0">
                <thead>
                    <th>Family ID</th>
                    <th>Family Short Name</th>
                    <th>Family Size</th>
                    <th>UniRef90 Size</th>
                </thead>
                <tbody>
<?php
    } 

    foreach ($dbResult as $row) {
        echo "                    <tr>\n";
        echo "                        <td>" . $row["family"] . "</td>\n";
        echo "                        <td>" . $row["short_name"] . "</td>\n";
        echo "                        <td class=\"right-align\">" . number_format($row["num_members"]) . "</td>\n";
        echo "                        <td class=\"right-align\">" . number_format($row["num_uniref90_members"]) . "</td>\n";
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


<?php require_once("inc/footer.inc.php"); ?>


