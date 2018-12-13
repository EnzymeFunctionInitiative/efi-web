<?php

require_once("../../libs/global_settings.class.inc.php");
require_once("functions.class.inc.php");

class family_size {

    public static function compute_family_size($db, $families, $fraction, $is_uniref, $uniref_ver = "", $db_version = "") {
        if ($fraction < 1)
            $fraction = 1;

        $table = "family_info";
        if ($db_version) {
            // Get the actual module not the alias.
            $mod_info = global_settings::get_database_modules();
            $default_mod = functions::get_efidb_module();
            $found_it = false;
            foreach ($mod_info as $mod) {
                if ($mod[1] == $db_version && $mod[0] != $default_mod)
                    $found_it = true;
            }
            if ($found_it) {
                $table .= "_" . strtolower($db_version);
            }
        }

        $use_uniref90 = true;
        $use_uniref50 = true;
//        $use_uniref50 = false;
//        if ($is_uniref && $uniref_ver == "50") {
//            $use_uniref90 = false;
//            $use_uniref50 = true;
//        }

        $totalFull = 0;
        $totalUniref90 = 0;
        $totalUniref50 = 0;
        $results = array("use_uniref90" => $use_uniref90, "use_uniref50" => $use_uniref50, "is_uniref90_required" => false,
            "is_too_large" => false, "families" => array());
        
        foreach ($families as $family) {
            $family = functions::sanitize_family($family);
            if (!$family)
                continue;
        
            $familyType = functions::get_family_type($family);
            if (!$familyType)
                continue;
        
            $sql = "SELECT * FROM $table WHERE family='$family'";
            $dbResult = $db->query($sql);
            if ($dbResult) {
                $results["families"][strtoupper($family)] = array(
                    "name" => $dbResult[0]["short_name"],
                    "all" => $dbResult[0]["num_members"],
                    "uniref90" => $dbResult[0]["num_uniref90_members"],
                    "uniref50" => $dbResult[0]["num_uniref50_members"]);
                $totalFull += $dbResult[0]["num_members"];
                if ($use_uniref90)
                    $totalUniref90 += $dbResult[0]["num_uniref90_members"];
                if ($use_uniref50)
                    $totalUniref50 += $dbResult[0]["num_uniref50_members"];
            }
        }
        
        $maxFull = functions::get_maximum_full_family_count();
        $maxSeq = functions::get_max_seq();
        
        $totalFraction = floor($totalFull / $fraction);

        $totalUnirefFraction = ($is_uniref && $uniref_ver == "50") ? $totalUniref50 : $totalUniref90; // reuse the var
        $totalUnirefFraction = floor($totalUnirefFraction / $fraction);
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
        $results["a"] = $table;

        return $results;
    }

}

?>
