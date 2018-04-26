<?php

require_once("functions.class.inc.php");

class family_size {

    public static function compute_family_size($db, $families, $fraction, $is_uniref) {
        if ($fraction < 1)
            $fraction = 1;

        $totalFull = 0;
        $totalUniref = 0;
        $results = array("use_uniref90" => true, "use_uniref50" => false, "is_uniref90_required" => false,
            "is_too_large" => false, "families" => array());
        
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
                $results["families"][strtoupper($family)] = array(
                    "name" => $dbResult[0]["short_name"],
                    "all" => $dbResult[0]["num_members"],
                    "uniref90" => $dbResult[0]["num_uniref90_members"],
                    "uniref50" => $dbResult[0]["num_uniref50_members"]);
                $totalFull += $dbResult[0]["num_members"];
                $totalUniref += $dbResult[0]["num_uniref90_members"];
            }
        }
        
        $maxFull = functions::get_maximum_full_family_count();
        $maxSeq = functions::get_max_seq();
        
        $totalFraction = floor($totalFull / $fraction);
        
        $totalUnirefFraction = floor($totalUniref / $fraction);
        $isTooLarge = $totalUnirefFraction > $maxSeq;
        
        $totalCompute = $is_uniref ? $totalUnirefFraction : $totalFraction;
        $useUnirefWarning = $maxFull > 0 && $totalCompute > $maxFull;
        if ($useUnirefWarning)
            $totalCompute = $totalUnirefFraction;
        
        $results["is_uniref90_required"] = $useUnirefWarning;
        $results["is_too_large"] = $isTooLarge;
        $results["max_full"] = $maxFull;
        $results["total"] = $totalFull;
        $results["total_compute"] = $totalCompute;

        return $results;
    }

}

?>
