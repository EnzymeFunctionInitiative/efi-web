<?php
namespace efi\est;

require_once(__DIR__ . "/../../../init.php");
//require_once(__DIR__."/../../conf/settings_paths.inc.php");
//require_once(__BASE_DIR__ . "/libs/global_settings.class.inc.php");
//require_once(__DIR__ . "/functions.class.inc.php");
//require_once(__DIR__ . "/est_settings.class.inc.php");

use \efi\est\functions;


class family_size {

    public static function parse_family_query($query) {
        $query = strtoupper($query);
        $query = trim($query);
        $families = preg_split("/[\n\r ,]+/", $query);
        return $families;
    }

    public static function compute_family_size($db, $families, $fraction, $is_uniref, $uniref_ver = "", $db_version = "") {

        $families = array_unique($families);

        if ($fraction < 1)
            $fraction = 1;

        $table = "family_info";
        if ($db_version) {
            // Get the actual module not the alias.
            $mod_info = \efi\global_settings::get_database_modules();
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
            "is_uniref50_required" => false, "is_too_large" => false, "families" => array());
        
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
        
        $maxFull = est_settings::get_maximum_full_family_count();
        $maxSeq = est_settings::get_max_seq();
        
        $totalFraction = floor($totalFull / $fraction);

        $totalUniref50Fraction = floor($totalUniref50 / $fraction);
        $totalUniref90Fraction = floor($totalUniref90 / $fraction);
        $isTooLarge = $totalUniref90Fraction > $maxSeq && $totalUniref50Fraction > $maxSeq;
        
        $totalCompute = $is_uniref ? ($uniref_ver == "50" ? $totalUniref50Fraction : $totalUniref90Fraction) : $totalFraction;
        $useUniref90Warning = $maxFull > 0 && $totalFraction > $maxFull; //$totalUniref90Fraction > $maxFull;
        $useUniref50Warning = $maxFull > 0 && $totalFraction > $maxFull && $totalUniref90Fraction > $maxSeq;
        if ($useUniref50Warning)
            $totalCompute = $totalUniref50Fraction;
        elseif ($useUniref90Warning && $uniref_ver != "50")
            $totalCompute = $totalUniref90Fraction;
        
        $results["is_uniref90_required"] = $useUniref90Warning;
        $results["is_uniref50_required"] = $useUniref50Warning;
        $results["is_too_large"] = $isTooLarge;
        $results["max_full"] = $maxFull;
        $results["total"] = $totalFull;
        $results["total_compute"] = $totalCompute;
        $results["a"] = $table;

        return $results;
    }

}

?>
