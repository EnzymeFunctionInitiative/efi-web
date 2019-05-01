<?php


class dataset_shared {
    
    public static function create_generate_object($gen_type, $db) {
        $generate = false;
        if ($gen_type == "BLAST")
            $generate = new blast($db, $_GET['id']);
        elseif ($gen_type == "FAMILIES")
            $generate = new generate($db, $_GET['id']);
        elseif ($gen_type == "ACCESSION")
            $generate = new accession($db, $_GET['id']);
        elseif ($gen_type == "FASTA" || $gen_type == "FASTA_ID")
            $generate = new fasta($db, $_GET['id']);
        elseif ($gen_type == "COLORSSN")
            $generate = new colorssn($db, $_GET['id']);
        return $generate;
    }
    
    public static function get_uniref_version($gen_type, $generate) {
        if ($gen_type != "COLORSSN")
            return $generate->get_uniref_version();
        else
            return "";
    }
    
    public static function add_generate_summary_table($generate, $table, $is_analysis) {

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
        $table->add_row("Time Started -- Finished", $time_window);
        if (!empty($db_version))
            $table->add_row("Database Version", $db_version);
        $table->add_row("Input Option", $formatted_gen_type);
        if ($job_name)
            $table->add_row("Job Name", $job_name);
    
    
        $uploaded_file = "";
        $included_family = "";
        $num_family_nodes = $generate->get_num_family_sequences();
        $num_full_family_nodes = $generate->get_num_full_family_sequences();
        if (empty($num_full_family_nodes))
            $num_full_family_nodes = $num_family_nodes;
        $total_num_nodes = $generate->get_num_sequences();
        
        $family_label = "Added Pfam / InterPro Family";
        $fraction_label = "Fraction Option for Added Family";
        $total_label = "Total Number of Sequences in Dataset";
        $domain_label = "Domain Option";
        $blast_total_label = "";
        
        
        $fraction = $generate->get_fraction();
        $default_fraction = functions::get_fraction();
        if ($fraction == $default_fraction)
            $fraction = "off";
        
        $evalue_option = $generate->get_evalue();
        $default_evalue = functions::get_evalue();
        $show_evalue = $evalue_option != $default_evalue;
        
        if ($gen_type == "BLAST") {
            $code = $generate->get_blast_input();
            $included_family = $generate->get_families_comma();
            $evalue_blast = $generate->get_blast_evalue();
            $retrieved_seq = $generate->get_num_blast_sequences();
            if ($evalue_blast != $default_evalue)
                $table->add_row("E-Value for UniProt BLAST Retrieval", $evalue_blast);
            if ($show_evalue)
                $table->add_row("E-Value for SSN Edge Calculation", $evalue_option);
            
            if ($table->is_html())
                $code = "<a href='blast.php?blast=$code'>View Sequence</a>";
            $table->add_row("Sequence Submitted for BLAST", $code);
            
            $table->add_row("Maximum Number of Retrieved Sequences", number_format($generate->get_submitted_max_sequences()));
            if ($use_advanced_options || $retrieved_seq) // If this number is not set, only show this row if we're on the dev site.
                $table->add_row("Actual Number of Retrieved Sequences", number_format($retrieved_seq));
            $blast_total_label = " (includes sequence submitted for BLAST)";
            if ($included_family != "") {
                $table->add_row($family_label, $included_family);
                $table->add_row($fraction_label, $fraction);
                if ($uniref)
                    $table->add_row("UniRef Version", $uniref);
            }
        }
        elseif ($gen_type == "FAMILIES") {
            $included_family = $generate->get_families_comma();
            $seqid = $generate->get_sequence_identity();
            $overlap = $generate->get_length_overlap();
        
            $fraction_label = "Fraction Option";
            $family_label = "Pfam / InterPro Family";
        
            if ($show_evalue)
                $table->add_row("E-Value for SSN Edge Calculation", $evalue_option);
            $table->add_row($family_label, $included_family);
            $table->add_row($fraction_label, $fraction);
            $table->add_row($domain_label, $generate->get_domain());
            if ($uniref)
                $table->add_row("UniRef Version", $uniref);
            if ($use_advanced_options) {
                if ($seqid)
                    $table->add_row("Sequence Identity", $seqid);
                if ($overlap)
                    $table->add_row("Sequence Overlap", $overlap);
            }
        }
        elseif ($gen_type == "ACCESSION" || $gen_type == "FASTA" || $gen_type == "FASTA_ID") {
            $file_label = "";
            $domain_opt = "off";
            if ($gen_type == "ACCESSION") {
                $file_label = "Accession ID";
                $domain_opt = $generate->get_domain();
            } else {
                $file_label = "FASTA";
            }
        
            if ($show_evalue)
                $table->add_row("E-Value for SSN Edge Calculation", $evalue_option);
            $uploaded_file = $generate->get_uploaded_filename();
            if ($uploaded_file)
                $table->add_row("Uploaded $file_label File", $uploaded_file);
            if ($gen_type == "ACCESSION")
                $table->add_row_html_only("No matches file", "<a href=\"" . $generate->get_no_matches_download_path() . "\"><button class=\"mini\">Download</button></a>");
        
            $included_family = $generate->get_families_comma();
            if ($included_family != "") {
                $table->add_row($family_label, $included_family);
                $table->add_row($fraction_label, $fraction);
            }
            if (global_settings::advanced_options_enabled())
                $table->add_row($domain_label, $domain_opt);
            if ($uniref)
                $table->add_row("UniRef Version", $uniref);
        
            $term = "";
            if ($gen_type == "ACCESSION")
                $term = "IDs";
            else
                $term = "Sequences";
            
            $num_file_seq = $generate->get_total_num_file_sequences();
            $num_matched = $generate->get_num_matched_file_sequences();
            $num_unmatched = $generate->get_num_unmatched_file_sequences();
            $num_unique = $generate->get_num_unique_file_sequences();
            $both = $num_matched + $num_unmatched;
        
            $table->add_row("Number of $term in Uploaded File", number_format($num_file_seq));
            if ($gen_type == "FASTA_ID") {
                //add this when we deal with the multi-species NCBI issue $table->add_row("Number of FASTA Headers in Uploaded File", number_format($both));
                $table->add_row("Number of $term in Uploaded File with UniProt Match", number_format($num_matched));
                $table->add_row("Number of $term in Uploaded File without UniProt Match", number_format($num_unmatched));
                if ($num_unique !== false && $uniref)
                    $table->add_row("Number of Unique $term in Uploaded File", number_format($num_unique));
            }
        }
        elseif ($gen_type == "COLORSSN") {
            $table->add_row("Uploaded XGMML File", $generate->get_uploaded_filename());
            $table->add_row("Neighborhood Size", $generate->get_neighborhood_size());
            $table->add_row("Cooccurrence", $generate->get_cooccurrence());
        }
        
        if ($gen_type != "COLORSSN") {
            if (functions::get_program_selection_enabled())
                $table->add_row("Program Used", $generate->get_program());
        }
        
        if ($included_family && !empty($num_family_nodes)) {
            if ($uniref)
                $table->add_row("Number of Cluster IDs in UniRef$uniref", number_format($num_family_nodes));
            else
                $table->add_row("Number of IDs in Pfam / InterPro Family", number_format($num_full_family_nodes));
        }
        
        $table->add_row($total_label, number_format($total_num_nodes) . $blast_total_label);
            
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

?>
