<?php

namespace efi\cgfp;

use \efi\file_types;


class result_table {
    public function __construct($id_query_string, $size_data, $extra_labels = array()) {
        $this->default_id_query_string = $id_query_string;
        $this->size_data = $size_data;
        $labels = array(
            file_types::FT_sbq_protein_abundance_median => "Protein abundance data (median)",
            file_types::FT_sbq_cluster_abundance_median => "Cluster abundance data (median)",
            file_types::FT_sbq_protein_abundance_norm_median => "Normalized protein abundance data (median)",
            file_types::FT_sbq_cluster_abundance_norm_median => "Normalized cluster abundance data (median)",
            file_types::FT_sbq_protein_abundance_genome_norm_median => "Average genome size (AGS) normalized protein abundance data (median)",
            file_types::FT_sbq_cluster_abundance_genome_norm_median => "Average genome size (AGS) normalized cluster abundance data (median)",
            file_types::FT_sbq_protein_abundance_mean => "Protein abundance data (mean)",
            file_types::FT_sbq_cluster_abundance_mean => "Cluster abundance data (mean)",
            file_types::FT_sbq_protein_abundance_norm_mean => "Normalized protein abundance data (mean)",
            file_types::FT_sbq_cluster_abundance_norm_mean => "Normalized cluster abundance data (mean)",
            file_types::FT_sbq_protein_abundance_genome_norm_mean => "Average genome size (AGS) normalized protein abundance data (mean)",
            file_types::FT_sbq_cluster_abundance_genome_norm_mean => "Average genome size (AGS) normalized cluster abundance data (mean)",
            file_types::FT_sbq_ssn => "SSN with quantify results (ZIP)",
            file_types::FT_sb_ssn => "SSN with identify results (ZIP)",
            file_types::FT_sb_cdhit => "CD-HIT ShortBRED families by cluster",
            file_types::FT_sb_markers => "ShortBRED marker data",
            file_types::FT_sb_meta_cluster_sizes => "Cluster sizes",
            file_types::FT_sb_meta_sp_clusters => "SwissProt annotations by cluster",
            file_types::FT_sb_meta_cp_sing => "SwissProt annotations by singletons",
            file_types::FT_sbq_meta_info => "Description of selected metagenomes",
        );
        $this->labels = $labels;
        if (is_array($extra_labels)) {
            $this->labels = array_merge($this->labels, $extra_labels);
        }
    }

    public function get_label($ftype) {
        return isset($this->labels[$ftype]) ? $this->labels[$ftype] : "";
    }

    public function make_results_row($ftype, $button_text = "Download", $alt_query_string = "") {
        $query_string = $alt_query_string ? $alt_query_string : $this->default_id_query_string;

        $link_fn = function($arg, $text, $id_query_string) {
            return "<a href='download_files.php?type=$arg&$id_query_string'><button class='mini'>$text</button></a>";
        };

        $size_data = isset($this->size_data[$ftype]) ? $this->size_data[$ftype] : "";
        $link_html = $link_fn($ftype, $button_text, $query_string);
        $size_html = $size_data ? "<td style='text-align:center;'>$size_data MB</td>" : "";
        $label = isset($this->labels[$ftype]) ? $this->labels[$ftype] : "";
    
        return <<<HTML
            <tr>
                <td style='text-align:center;'>$link_html</td>
                <td>$label</td>
                $size_html
            </tr>
HTML;
    }
}


