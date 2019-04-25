<?php

require_once(__DIR__ . "/../conf/settings_shared.inc.php");
require_once(__DIR__ . "/functions.class.inc.php");

class est_user_jobs_shared {

    private static function get_filename($data, $type) {
        if (array_key_exists("generate_fasta_file", $data)) {
            $file = $data["generate_fasta_file"];
            if ($file) {
                return str_replace("_", " ", $file);
            } elseif ($type == "FASTA" || $type == "FASTA_ID" || $type == "ACCESSION") {
                return "Text Input";
            } else {
                return "";
            }
        } else {
            return "";
        }
    }

    private static function get_families($data, $type, $familyLookupFn) {
        $famStr = "";
        if (array_key_exists("generate_families", $data)) {
            $fams = $data["generate_families"];
            if ($fams) {
                $famParts = explode(",", $fams);
                if (count($famParts) > 2)
                    $famParts = array($famParts[0], $famParts[1], "...");
                $fams = implode(", ", array_map($familyLookupFn, $famParts));
                $famStr = $fams;
            }
        }
        return $famStr;
    }

    private static function get_evalue($data) {
        $evalueStr = "";
        if (array_key_exists("generate_evalue", $data)) {
            $evalue = $data["generate_evalue"];
            if ($evalue && $evalue != functions::get_evalue())
                $evalueStr = "E-value=" . $evalue;
        }
        return $evalueStr;
    }

    private static function get_fraction($data) {
        $fractionStr = "";
        if (array_key_exists("generate_fraction", $data)) {
            $fraction = $data["generate_fraction"];
            if ($fraction && $fraction != functions::get_fraction())
                $fractionStr = "Fraction=" . $fraction;
        }
        return $fractionStr;
    }

    private static function get_uniref_version($data) {
        $unirefStr = "";
        if (array_key_exists("generate_uniref", $data)) {
            if ($data["generate_uniref"])
                $unirefStr = "UniRef" . $data["generate_uniref"];
        }
        return $unirefStr;
    }

    private static function get_domain($data) {
        $domainStr = "";
        if (array_key_exists("generate_domain", $data)) {
            if ($data["generate_domain"])
                $domainStr = "Domain=on";
        }
        return $domainStr;
    }

    private static function get_sequence($data) {
        $seqStr = "";
        if (array_key_exists("generate_blast", $data) && $data["generate_blast"]) {
            $seq = $data["generate_blast"];
            $seqLabel = substr($seq, 0, 20);
            $seqTitle = "";
            $pos = 0;
            while ($pos < strlen($seq)) {
                $seqTitle .= substr($seq, $pos, 27) . "\n";
                $pos += 40;
            }
            $seqStr = "Sequence: <span title='$seqTitle'>$seqLabel...</span>";
        }
        return $seqStr;
    }

    private static function get_blast_evalue($data) {
        $info = "";
        if (array_key_exists("generate_blast_evalue", $data) && $data["generate_blast_evalue"]) {
            $info = "e-value=" . $data["generate_blast_evalue"];
        }
        return $info;
    }

    private static function get_max_blast_hits($data) {
        $info = "";
        if (array_key_exists("generate_blast_max_sequence", $data) && $data["generate_blast_max_sequence"]) {
            $info = "max hits=" . $data["generate_blast_max_sequence"];
        }
        return $info;
    }

    public static function build_job_name($data, $type, $familyLookupFn, $job_id = 0) {

        $newFamilyLookupFn = function($family_id) use($familyLookupFn) {
            $fam_name = $familyLookupFn($family_id);
            return $fam_name ? "$family_id-$fam_name" : $family_id;
        };

        $fileName = self::get_filename($data, $type);
        $families = self::get_families($data, $type, $newFamilyLookupFn);
        $evalue = self::get_evalue($data);
        $fraction = self::get_fraction($data);
        $uniref = self::get_uniref_version($data);
        $domain = self::get_domain($data);
        $sequence = self::get_sequence($data);
        $blastEvalue = self::get_blast_evalue($data);
        $maxHits = self::get_max_blast_hits($data);
        $jobNameField = isset($data["generate_job_name"]) ? $data["generate_job_name"] : "";

        $job_name = "";
        if ($jobNameField) {
            $job_name = $jobNameField;
        } else {
            if ($type == "FAMILIES") {
                $job_name = $families;
                $families = ""; // do this so we don't add it in the metadata line below
            } elseif ($type == "BLAST") {
                $job_name = $sequence;
                $sequence = "";
            } else {
                $job_name = $fileName;
                $fileName = "";
            }
        }

        $job_type = self::get_job_label($type);

        $info = array($job_type);
        if ($fileName) array_push($info, $fileName);
        if ($families) array_push($info, $families);
        if ($evalue) array_push($info, $evalue);
        if ($fraction) array_push($info, $fraction);
        if ($uniref) array_push($info, $uniref);
        if ($domain) array_push($info, $domain);
        if ($sequence) array_push($info, $sequence);
        if ($blastEvalue) array_push($info, $blastEvalue);
        if ($maxHits) array_push($info, $maxHits);

        $job_info = implode("; ", $info);
        
        $job_name = "<span class='job-name'>$job_name</span><br><span class='job-metadata'>$job_info</span>";

        return $job_name;
    }

    public static function get_job_label($type) {
        switch ($type) {
        case "FAMILIES":
            return "Families";
        case "FASTA":
            return "FASTA";
        case "FASTA_ID":
            return "FASTA+Headers";
        case "ACCESSION":
            return "Accession IDs";
        case "COLORSSN":
            return "Color SSN";
        case "BLAST":
            return "Sequence BLAST";
        default:
            return $type;
        }
    }

    public static function build_analyze_job_name($data_row) {
        $a_min = $data_row["analysis_min_length"] == __MINIMUM__ ? "" : "Min=".$data_row["analysis_min_length"];
        $a_max = $data_row["analysis_max_length"] == __MAXIMUM__ ? "" : "Max=".$data_row["analysis_max_length"];
        $job_name = "<span class='job-name'>" . $data_row["analysis_name"] . "</span><br><span class='job-metadata'>SSN Threshold=" . $data_row["analysis_evalue"];
        if ($a_min)
            $job_name .= " $a_min";
        if ($a_max)
            $job_name .= " $a_max";
        $job_name .= "</span>";
        return $job_name;
    }
}

?>

