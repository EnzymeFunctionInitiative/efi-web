<?php
namespace efi\est;

require_once(__DIR__."/../../../init.php");

use \efi\global_settings;
use \efi\est\settings;
use \efi\est\functions;
use \efi\est\job_factory;
use \efi\est\blast;
use \efi\est\family;
use \efi\est\accession;
use \efi\est\fasta;
use \efi\est\taxonomy_job;


class dataset_shared {
    
    public static function create_generate_object($gen_type, $db, $is_example = false) {
        $generate = false;
        $id = $_GET['id'];
        if ($gen_type == "BLAST")
            $generate = new blast($db, $id, $is_example);
        elseif ($gen_type == "FAMILIES")
            $generate = new family($db, $id, $is_example);
        elseif ($gen_type == "TAXONOMY")
            $generate = new taxonomy_job($db, $id, $is_example);
        elseif ($gen_type == "ACCESSION")
            $generate = new accession($db, $id, $is_example);
        elseif ($gen_type == "FASTA" || $gen_type == "FASTA_ID")
            $generate = new fasta($db, $id, "C", $is_example);
        //elseif ($gen_type == "COLORSSN")
        //    $generate = new colorssn($db, $id, $is_example);
        else
            $generate = job_factory::create($db, $id, $is_example);
        return $generate;
    }
    
    public static function get_uniref_version($gen_type, $generate) {
        if ($gen_type != "COLORSSN" && $gen_type != "CLUSTER" && $gen_type != "NBCONN" && $gen_type != "CONVRATIO")
            return $generate->get_uniref_version();
        else
            return "";
    }

    public static function get_domain($gen_type, $generate) {
        if ($gen_type == "FAMILIES" || $gen_type == "ACCESSION")
            return $generate->get_domain() ? "on" : "off";
        else
            return "";
    }
    
    public static function add_generate_summary_table($generate, $table, $is_analysis, $is_example = false) {

        $gen_id = $generate->get_id();
        $gen_type = $generate->get_type();
        $db_version = $generate->get_db_version();
        $time_window = $generate->get_time_period();
        $job_name = $generate->get_job_name();
        $key = $generate->get_key();

        $use_advanced_options = global_settings::advanced_options_enabled();
        $formatted_gen_type = functions::format_job_type($gen_type);
        $uniref = self::get_uniref_version($gen_type, $generate);

        if ($is_analysis)
            $table->add_row_with_html("EST Job Number", "$gen_id (<a href='stepc.php?id=$gen_id&key=$key'>Original Dataset</a>)");
        else
            $table->add_row("Job Number", $gen_id);
        if (!$is_example)
            $table->add_row("Time Started -- Finished", $time_window);
        if (!empty($db_version))
            $table->add_row("Database Version", $db_version);
        $table->add_row("Input Option", $formatted_gen_type);
        if ($job_name)
            $table->add_row("Job Name", $job_name);
    

        $uploaded_file = "";
        $included_family = $generate->get_families_comma();
        $num_family_nodes = $generate->get_counts("num_family");
        if (!$num_family_nodes)  //LEGACY
            $num_family_nodes = $generate->get_counts("num_family_seq");
        $num_full_family_nodes = $generate->get_counts("num_full_family");
        if (!$num_full_family_nodes)  //LEGACY
            $num_full_family_nodes = $generate->get_counts("num_full_family_seq");
        if (empty($num_full_family_nodes))
            $num_full_family_nodes = $num_family_nodes;
        $total_num_nodes = $generate->get_counts("num_seq");
        $num_overlap = $generate->get_counts("num_family_overlap");
        $num_user = $generate->get_counts("num_user");
        if (!$num_user)  //LEGACY
            $num_user = $generate->get_counts("total_num_file_seq");
        $num_uniref_overlap = $generate->get_counts("num_uniref_overlap");
        $num_matched = $generate->get_counts("num_user_matched");
        if (!$num_matched)  //LEGACY
            $num_matched = $generate->get_counts("num_matched_file_seq");
        $num_unmatched = $generate->get_counts("num_user_unmatched");
        if (!$num_unmatched)  //LEGACY
            $num_unmatched = $generate->get_counts("num_unmatched_file_seq");
        $num_file_seq = $num_matched + $num_unmatched;
        
        $family_label = "Added Pfam / InterPro Family";
        $fraction_label = "Fraction Option for Added Family";
        $total_label = "Total Number of Sequences in Dataset";
        $domain_label = "Domain Option";
        $blast_total_label = "";
        
        
        $fraction = $generate->get_fraction();
        $default_fraction = functions::get_fraction();
        if ($fraction == $default_fraction)
            $fraction = "off";

        $is_taxonomy = false;
        if ($generate->get_is_tax_only())
            $is_taxonomy = true;

//        $generate->print_raw_counts();
        $evalue_option = $generate->get_evalue();
        $default_evalue = settings::get_evalue();
        $show_evalue = $evalue_option != $default_evalue;

        $add_fam_rows_fn = function() {};
        $add_fam_overlap_rows_fn = function() {};
        if ($included_family) {
            $add_fam_rows_fn = function($family_label, $fraction_label, $domain_label)
                use ($included_family, $uniref, $table, $num_family_nodes, $num_full_family_nodes, $generate, $fraction, $is_taxonomy)
                {
                    $included_family = implode(", ", explode(",", $included_family));
                    $table->add_row($family_label, $included_family);
                    $table->add_row("Number of IDs in Pfam / InterPro Family", number_format($num_full_family_nodes));
                    if (!$uniref && !$is_taxonomy)
                        $table->add_row($fraction_label, $fraction);
                    if ($domain_label && !$is_taxonomy) {
                        $row_val = "off";
                        if ($generate->get_domain()) {
                            $row_val = "on";
                            $term_opt = $generate->get_domain_region_pretty();
                            if ($term_opt)
                                $row_val .= " ($term_opt)";
                        }
                        $table->add_row($domain_label, $row_val);
                    }
                    if ($uniref) {
                        $table->add_row("UniRef Version", $uniref);
                        $table->add_row("Number of Cluster IDs in UniRef$uniref Family", number_format($num_family_nodes));
                    }
                };
            $add_fam_overlap_rows_fn = function($label)
                use ($num_user, $num_overlap, $num_uniref_overlap, $table)
                {
                    $uniref_text = "";
                    $overlap = $num_overlap;
                    if ($num_uniref_overlap)
                        $overlap = $num_uniref_overlap;
#                        $uniref_text = " (" . number_format($num_uniref_overlap) . " sequences were mapped to UniRef clusters)";
                    $table->add_row("Number of $label Matched in Family", number_format($overlap) . $uniref_text);
                    $table->add_row("Number of $label not in Family", number_format($num_user));
                };
        }

        $tax_row = "";
        $tax_search = $generate->get_taxonomy_filter();
        if (is_array($tax_search)) {
            for ($i = 0; $i < count($tax_search); $i++) {
                $name = ucfirst($tax_search[$i][0]);
                $search = $tax_search[$i][1];
                if ($tax_row)
                    $tax_row .= ", ";
                $tax_row .= "$name: $search";
            }
        }

        $total_note = "";
        
        if ($gen_type == "BLAST") {
            $blast_total_label = " (includes sequence submitted for BLAST)";
            $evalue_blast = $generate->get_blast_evalue();
            $code = $generate->get_blast_input();
            if ($table->is_html())
                $code = "<a href='blast.php?blast=$code'>View Sequence</a>";
            
            $retrieved_seq = $generate->get_counts("num_blast_retr");


            if ($evalue_blast != $default_evalue)
                $table->add_row("E-Value for UniProt BLAST Retrieval", $evalue_blast);
            if ($show_evalue)
                $table->add_row("E-Value for SSN Edge Calculation", $evalue_option);
            $table->add_row("Sequence Submitted for BLAST", $code);
            $table->add_row("Maximum Number of Retrieved Sequences", number_format($generate->get_submitted_max_sequences()));
            if ($use_advanced_options || $retrieved_seq) // If this number is not set, only show this row if we're on the dev site.
                $table->add_row("Actual Number of Retrieved Sequences", number_format($retrieved_seq));
            $add_fam_rows_fn($family_label, $fraction_label, "");
            $add_fam_overlap_rows_fn("Retrieved Sequences");
            if ($tax_row)
                $table->add_row("Taxonomy Categories:", $tax_row);
            $table->add_row("Exclude Fragments", $generate->get_exclude_fragments() ? "Yes" : "No");
        }
        elseif ($gen_type == "FAMILIES" || $gen_type == "TAXONOMY") {
            $seqid = $generate->get_sequence_identity();
            $overlap = $generate->get_length_overlap();

            if ($gen_type == "FAMILIES") {
                $fraction_label = "Fraction Option";
                $family_label = "Pfam / InterPro Family";
                if ($show_evalue)
                    $table->add_row("E-Value for SSN Edge Calculation", $evalue_option);
            }

            $add_fam_rows_fn($family_label, $fraction_label, $domain_label, "");
            if ($use_advanced_options) {
                if ($seqid)
                    $table->add_row("Sequence Identity", $seqid);
                if ($overlap)
                    $table->add_row("Sequence Overlap", $overlap);
            }
            if ($tax_row)
                $table->add_row("Taxonomy Categories:", $tax_row);
            $table->add_row("Exclude Fragments", $generate->get_exclude_fragments() ? "Yes" : "No");
        }
        elseif ($gen_type == "ACCESSION" || $gen_type == "FASTA" || $gen_type == "FASTA_ID") {
            $term = "";
            $file_label = "";
            $domain_opt = 0;
            $table_dom_label = "";
            if ($gen_type == "ACCESSION") {
                $file_label = "Accession ID";
                $domain_opt = $generate->get_domain();
                $term = "IDs";
                $table_dom_label = global_settings::advanced_options_enabled() ? $domain_label : "";
            } else {
                $file_label = "FASTA";
                $term = "Sequences";
            }
            
            $uploaded_file = $generate->get_uploaded_filename();
            $match_text = "";
            if ($gen_type == "FASTA_ID" || $gen_type == "ACCESSION")
                $match_text = " (" . number_format($num_matched) . " UniProt ID matches and " . number_format($num_unmatched) . " unmatched)";

            if ($domain_opt && !$included_family) {
                $term_opt = $generate->get_domain_region_pretty();
                $row_val = "on " . strtoupper($generate->get_domain_family());
                if ($term_opt)
                    $row_val .= " ($term_opt)";
                $table->add_row($domain_label, $row_val);
            }
            if ($uniref && !$included_family)
                $table->add_row("Input Sequence Source", "UniRef$uniref");
            if ($show_evalue)
                $table->add_row("E-Value for SSN Edge Calculation", $evalue_option);
            if ($uploaded_file)
                $table->add_row("Uploaded $file_label File", $uploaded_file);
            if ($gen_type == "ACCESSION")
                $table->add_row_html_only("No matches file", "<a href=\"" . $generate->get_no_matches_download_path() . "\"><button class=\"mini\">Download</button></a>");
            $add_fam_rows_fn($family_label, $fraction_label, $table_dom_label);
            $table->add_row("Number of $term in Uploaded File", number_format($num_file_seq) . $match_text);
            $add_fam_overlap_rows_fn("Input $term");
            if ($tax_row)
                $table->add_row("Taxonomy Categories:", $tax_row);
            $table->add_row("Exclude Fragments", $generate->get_exclude_fragments() ? "Yes" : "No");
        }
        elseif ($gen_type == "COLORSSN" || $gen_type == "CLUSTER" || $gen_type == "NBCONN" || $gen_type == "CONVRATIO") {
            $table->add_row("Uploaded XGMML File", $generate->get_uploaded_filename());
            $table->add_row("Neighborhood Size", $generate->get_neighborhood_size());
            $table->add_row("Cooccurrence", $generate->get_cooccurrence());
            $table->add_row("Exclude Fragments", $generate->get_exclude_fragments() ? "Yes" : "No");
        }
        
        if ($gen_type != "COLORSSN" && $gen_type != "CLUSTER" && $gen_type != "NBCONN" && $gen_type != "CONVRATIO") {
            if (functions::get_program_selection_enabled())
                $table->add_row("Program Used", $generate->get_program());
        }
        
        $table->add_row_with_html($total_label, number_format($total_num_nodes) . $blast_total_label . $total_note);

        $num_edges = $generate->get_counts("num_edges");
        if ($num_edges)
            $table->add_row("Total Number of Edges", number_format($num_edges));
        $num_unique = $generate->get_counts("num_unique");
        if ($num_unique)
            $table->add_row("Number of Unique Sequences", number_format($num_unique));

        if (!$is_taxonomy) {
            $conv_ratio = $generate->get_convergence_ratio();
            $convergence_ratio_string = "";
            if ($conv_ratio > -0.5) {
                $convergence_ratio_string = <<<STR
        The convergence ratio is a measure of the similarity of the sequences used in the BLAST.  It is the
        ratio of the total number of edges retained from the BLAST (e-values less than the specified threshold;
        default 5) to the total number of sequence pairs.  The value decreases from 1.0 for sequences that are
        very similar (identical) to 0.0 for sequences that are very different (unrelated).
STR;
                $table->add_row_with_html("Convergence Ratio<a class=\"question\" title=\"$convergence_ratio_string\">?</a>", number_format($conv_ratio, 3));
                $convergence_ratio_string = "";
            }
        }
    }


    public static function send_table($table_filename, $table_string) {
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $table_filename . '"');
        header('Content-Length: ' . strlen($table_string));
        ob_clean();
        echo $table_string;
    }
}

