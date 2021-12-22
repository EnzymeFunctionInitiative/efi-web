<?php
namespace efi\est;

require_once(__DIR__."/../../../init.php");

use \efi\global_functions;
use \efi\est\settings;
use \efi\est\functions;


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
            if ($evalue && $evalue != settings::get_evalue())
                $evalueStr = "E-value: " . $evalue;
        }
        return $evalueStr;
    }

    private static function get_fraction($data) {
        $fractionStr = "";
        if (array_key_exists("generate_fraction", $data)) {
            $fraction = $data["generate_fraction"];
            if ($fraction && $fraction != functions::get_fraction())
                $fractionStr = "Fraction: " . $fraction;
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
            if ($data["generate_domain"]) {
                $domainStr = "Domain: on";
                if (isset($data["generate_domain_region"]) && $data["generate_domain_region"] != "domain") {
                    switch ($data["generate_domain_region"]) {
                    case "nterminal":
                        $domainStr .= " (N-terminal)";
                        break;
                    case "cterminal":
                        $domainStr .= " (C-terminal)";
                        break;
                    }
                }
            }
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

    private static function get_exclude_fragments($data) {
        $info = "";
        if (isset($data["exclude_fragments"]) && $data["exclude_fragments"] == true)
            $info = "Fragments: no";
        else
            $info = "Fragments: yes";
        return $info;
    }

    private static function get_tax_search($data) {
        $info = "";
        if (isset($data["tax_search"]) && $data["tax_search"]) {
            $info = preg_replace("/;/", "; ", $data["tax_search"]);
            $info = preg_replace("/:/", ": ", $info);
            $info = "Taxonomic filter: [<small>$info</small>]";
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
        $excludeFractions = self::get_exclude_fragments($data);
        $taxSearch = self::get_tax_search($data);
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
        if ($type == "CLUSTER") {
            $opts = isset($data["make_hmm"]) ? $data["make_hmm"] : "";
            $min_seq_msa = (isset($data["min_seq_msa"]) && $data["min_seq_msa"]) ? $data["min_seq_msa"] : "";
            $max_seq_msa = (isset($data["max_seq_msa"]) && $data["max_seq_msa"]) ? $data["max_seq_msa"] : "";

            $parms = array();
            if (preg_match("/HMM/", $opts))
                array_push($parms, "HMMs");
            if (preg_match("/LOGO/", $opts))
                array_push($parms, "WebLogos");
            if (preg_match("/HIST/", $opts))
                array_push($parms, "Length Histograms");
            if (preg_match("/CR/", $opts))
                array_push($parms, "AAs=" . $data["hmm_aa"] . "; Thresholds=" . $data["aa_threshold"]);
            if ($min_seq_msa)
                array_push($parms, "MinNumSeq=" . $min_seq_msa);
            if ($max_seq_msa)
                array_push($parms, "MaxNumSeq=" . $max_seq_msa);

            if (count($parms))
                $job_type .= " (" . implode("; ", $parms) . ")";
        } else if ($type === "CONVRATIO") {
            $job_type .= " (E-value=" . $data["ascore"] . ")";
        }

        $info = array($job_type);
        if ($fileName) array_push($info, $fileName);
        if ($families) array_push($info, $families);
        if ($evalue) array_push($info, $evalue);
        if ($fraction) array_push($info, $fraction);
        if ($uniref) array_push($info, $uniref);
        if ($domain) array_push($info, $domain);
        if ($type != "CLUSTER" && $type != "COLORSSN" && $type != "NBCONN" && $type != "CONVRATIO" && $excludeFractions) array_push($info, $excludeFractions);
        if ($type != "CLUSTER" && $type != "COLORSSN" && $type != "NBCONN" && $type != "CONVRATIO" && $taxSearch) array_push($info, $taxSearch);
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
        case "CLUSTER":
            return "Cluster Analysis";
        case "NBCONN":
            return "Neighborhood Connectivity";
        case "CONVRATIO":
            return "Convergence Ratio";
        case "BLAST":
            return "Sequence BLAST";
        default:
            return $type;
        }
    }

    public static function build_analyze_job_name($data_row) {
        $a_min = $data_row["analysis_min_length"] == settings::get_ascore_minimum() ? "" : "Min=".$data_row["analysis_min_length"];
        $a_max = $data_row["analysis_max_length"] == settings::get_ascore_maximum() ? "" : "Max=".$data_row["analysis_max_length"];
        $job_name = "<span class='job-name'>" . $data_row["analysis_name"] . "</span><br>";
        $job_name .= "<span class='job-metadata'>SSN Threshold=" . $data_row["analysis_evalue"];
        if ($a_min)
            $job_name .= " $a_min";
        if ($a_max)
            $job_name .= " $a_max";
        $job_name .= "</span>";
        if (isset($data_row["analysis_params"])) {
            $data = global_functions::decode_object($data_row["analysis_params"]);
            if (isset($data["compute_nc"]) && $data["compute_nc"]) {
                $job_name .= " <span class='job-metadata'>Neighborhood Connectivity</span>";
            }
        }
        return $job_name;
    }

    public static function get_completed_date_label($comp, $status) {
        $is_completed = false;
        if ($status == "FAILED") {
            $comp = "FAILED";
        } elseif (!$comp || substr($comp, 0, 4) == "0000" || $status == "RUNNING") {
            $comp = $status;
            if ($comp == "NEW")
                $comp = "PENDING";
        } else {
            $comp = date_format(date_create($comp), "n/j h:i A");
            $is_completed = true;
        }
        return array($is_completed, $comp);
    }

    public static function build_job_name_json($json, $type, $familyLookupFn, $job_id = 0) {
        $data = global_functions::decode_object($json);

        return self::build_job_name($data, $type, $familyLookupFn, $job_id);
    }

    public static function process_load_generate_rows($db, $rows, $includeAnalysisJobs, $includeFailedAnalysisJobs, $familyLookupFn, $generate_table = "", $analysis_table = "", $table_db = "") {

        if (!$analysis_table)
            $analysis_table = "analysis";
        if ($table_db)
            $table_db .= ".";
        if (!$generate_table)
            $generate_table = "generate";

        $jobs = array();

        $map_fn = function ($row) use($generate_table) { return $row["${generate_table}_id"]; };
        $id_order = array_map($map_fn, $rows);
        $date_order = array();

        // First process all of the color SSN jobs.  This allows us to link them to SSN jobs.
        $child_color_jobs = array();
        $child_x2_color_jobs = array();
        $color_generate_id = array();
        $color_jobs = array();
        foreach ($rows as $row) {
            $type = $row["${generate_table}_type"];
            if ($type != "COLORSSN" && $type != "CLUSTER" && $type != "NBCONN" && $type != "CONVRATIO")
                continue;

            $comp_result = self::get_completed_date_label($row["${generate_table}_time_completed"], $row["${generate_table}_status"]);
            $job_name = self::build_job_name_json($row["${generate_table}_params"], $row["${generate_table}_type"], $familyLookupFn, $row["${generate_table}_id"]);
            $comp = $comp_result[1];
            $is_completed = $comp_result[0];
            $id = $row["${generate_table}_id"];
            $key = $row["${generate_table}_key"];
            if ($row["${generate_table}_time_completed"])
                $date_order[$id] = $row["${generate_table}_time_completed"];

            $params = global_functions::decode_object($row["${generate_table}_params"]);
            if (isset($params["generate_color_ssn_source_id"]) && $params["generate_color_ssn_source_id"]) {
                $aid = $params["generate_color_ssn_source_id"];
                $color_job = array("id" => $id, "key" => $key, "job_name" => $job_name, "is_completed" => $is_completed, "date_completed" => $comp, "parent_aid" => $aid);
                if (isset($child_color_jobs[$aid])) {
                    array_push($child_color_jobs[$aid], $color_job);
                } else {
                    $child_color_jobs[$aid] = array($color_job);
                }
                $color_generate_id[$id] = $aid;
            // Color jobs that originate from color jobs
            } else if (isset($params["color_ssn_source_color_id"]) && $params["color_ssn_source_color_id"]) {
                $cid = $params["color_ssn_source_color_id"];
                $color_job = array("id" => $id, "key" => $key, "job_name" => $job_name, "is_completed" => $is_completed, "date_completed" => $comp, "parent_id" => $cid);
                if (isset($child_x2_color_jobs[$cid])) {
                    array_push($child_x2_color_jobs[$cid], $color_job);
                } else {
                    $child_x2_color_jobs[$cid] = array($color_job);
                }
            } else {
                $color_jobs[$id] = array("key" => $key, "job_name" => $job_name, "is_completed" => $is_completed, "date_completed" => $comp);
            }
        }

        $colors_to_remove = array(); // these are the generate_id that will need to be removed from $id_order, since they are now attached to an analysis job

        foreach ($child_x2_color_jobs as $parent_cid => $jobs) {
            foreach ($jobs as $job) {
                $colors_to_remove[$job["id"]] = 1;
            }
            if (isset($color_generate_id[$parent_cid])) {
                $aid = $color_generate_id[$parent_cid];
                for ($i = 0; $i < count($child_color_jobs[$aid]); $i++) {
                    if ($child_color_jobs[$aid][$i]["id"] == $parent_cid)
                        $child_color_jobs[$aid][$i]["color_jobs"] = $jobs;
                }
            } else {
                $color_jobs[$parent_cid]["color_jobs"] = $jobs;
            }
        }

        // Process all non Color SSN jobs.  Link analysis jobs to generate jobs and color SSN jobs to analysis jobs.
        foreach ($rows as $row) {
            $type = $row["${generate_table}_type"];
            if ($type == "COLORSSN" || $type == "CLUSTER" || $type == "NBCONN" || $type == "CONVRATIO")
                continue;

            $comp_result = self::get_completed_date_label($row["${generate_table}_time_completed"], $row["${generate_table}_status"]);
            $job_name = self::build_job_name_json($row["${generate_table}_params"], $row["${generate_table}_type"], $familyLookupFn);
            $comp = $comp_result[1];
            $is_completed = $comp_result[0];
            $id = $row["${generate_table}_id"];
            $key = $row["${generate_table}_key"];
            
            $job_data = array("key" => $key, "job_name" => $job_name, "is_completed" => $is_completed, "date_completed" => $comp);
            if ($row["${generate_table}_time_completed"])
                $date_order[$id] = $row["${generate_table}_time_completed"];

            $analysis_jobs = array();
            $has_analysis_date_order = false;
            if ($is_completed && $includeAnalysisJobs) {
                $sql = "SELECT analysis_id, analysis_time_completed, analysis_status, analysis_name, analysis_evalue, analysis_min_length, analysis_max_length, analysis_filter, analysis_params FROM ${table_db}${analysis_table}" .
                    " WHERE analysis_generate_id = $id AND analysis_status != 'ARCHIVED'";
                if (!$includeFailedAnalysisJobs)
                    $sql .= " AND analysis_status = 'FINISH'";
                $sql .= " ORDER BY analysis_time_completed DESC";
                $arows = $db->query($sql); // Analysis Rows
    
                foreach ($arows as $arow) {
                    $aid = $arow["analysis_id"];
                    $acomp_result = self::get_completed_date_label($arow["analysis_time_completed"], $arow["analysis_status"]);
                    $acomp = $acomp_result[1];
                    $a_is_completed = $acomp_result[0];
                    $a_job_name = self::build_analyze_job_name($arow);

                    $a_job = array("analysis_id" => $aid, "job_name" => $a_job_name, "is_completed" => $a_is_completed, "date_completed" => $acomp, "parent_id" => $id);
                    if (isset($child_color_jobs[$aid])) {
                        $a_job["color_jobs"] = $child_color_jobs[$aid];
                        foreach ($child_color_jobs[$aid] as $cjob) {
                            $colors_to_remove[$cjob["id"]] = 1;
                        }
                        unset($child_color_jobs[$aid]);
                    }
                    array_push($analysis_jobs, $a_job);
                    if ($arow["analysis_status"] == __FINISH__ && $arow["analysis_time_completed"] && !$has_analysis_date_order) {
                        $date_order[$id] = $arow["analysis_time_completed"];
                        $has_analysis_date_order = true;
                    }
                }
            }

            $job_data["analysis_jobs"] = $analysis_jobs;
            $jobs[$id] = $job_data;
        }

        foreach ($child_color_jobs as $aid => $infos) {
            foreach ($infos as $info) {
                $color_jobs[$info["id"]] = $info;
            }
        }

        // Remove color jobs that have been attached to an analysis job.
        $id_order_new = array();
        for ($i = 0; $i < count($id_order); $i++) {
            if (!isset($colors_to_remove[$id_order[$i]]))
                array_push($id_order_new, $id_order[$i]);
        }

        $retval = array("generate_jobs" => $jobs, "color_jobs" => $color_jobs, "order" => $id_order_new, "date_order" => $date_order);
        return $retval;
    }
}

