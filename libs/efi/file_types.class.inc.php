<?php

namespace efi;


class file_types {

    const FT_sizes = "sizes";
    const FT_color_cr = "color_cr";
    const FT_fasta = "fasta";
    const FT_fasta_ur90 = "fasta_ur90";
    const FT_fasta_ur50 = "fasta_ur50";
    const FT_fasta_dom = "fasta_dom";
    const FT_fasta_dom_ur90 = "fasta_dom_ur90";
    const FT_fasta_dom_ur50 = "fasta_dom_ur50";
    const FT_mapping = "mapping";
    const FT_mapping_dom = "mapping_dom";
    const FT_stats = "stats";
    const FT_sp_clusters = "sp_clusters";
    const FT_sp_singletons = "sp_singletons";
    const FT_ids = "ids";
    const FT_ur90_ids = "ur90_ids";
    const FT_ur50_ids = "ur50_ids";
    const FT_dom_ids = "dom_ids";
    const FT_ur90_dom_ids = "ur90_dom_ids";
    const FT_ur50_dom_ids = "ur50_dom_ids";
    const FT_ssn = "ssn";
    const FT_cons_res_full = "cons_res_full";
    const FT_cons_res_pct = "cons_res_pct";
    const FT_cons_res_pos = "cons_res_pos";
    const FT_len_hist = "len_hist";
    const FT_len_hist_ur90 = "len_hist_ur90";
    const FT_len_hist_ur50 = "len_hist_ur50";
    const FT_len_hist_dom = "len_hist_dom";
    const FT_len_hist_dom_ur90 = "len_hist_dom_ur90";
    const FT_len_hist_dom_ur50 = "len_hist_dom_ur50";
    const FT_hmm = "hmm";
    const FT_hmm_dom = "hmm_dom";
    const FT_msa = "msa";
    const FT_msa_dom = "msa_dom";
    const FT_pim = "pim";
    const FT_pim_dom = "pim_dom";
    const FT_weblogo = "weblogo";
    const FT_weblogo_dom = "weblogo_dom";
    const FT_nc_legend = "nc_legend";
    const FT_nc_table = "nc_table";
    const FT_conv_ratio = "conv_ratio";
    const FT_cr_table = "conv_ratio";
    const FT_blast_evalue = "blast_evalue";
    const FT_gnn_ssn = "coloredssn"; // the ssns that come from GNN
    const FT_ssn_gnn = "ssn_gnn";
    const FT_pfam_gnn = "pfam_gnn";
    const FT_pfam_nn = "no_pfam_neighbors";
    const FT_gnn_nn = "nomatches_noneighbors";
    const FT_cooc_table = "cooc_table";
    const FT_hub_count = "hub_count";
    const FT_gnd = "gnd";
    const FT_pfam_dom = "pfam_map";
    const FT_split_pfam_dom = "split_pfam_map";
    const FT_all_pfam_dom = "all_pfam_map";
    const FT_split_all_pfam_dom = "all_split_pfam_map";
    const FT_gnn_mapping = "gnn_map_table";
    const FT_gnn_mapping_dom = "gnn_map_table_dom";

    const FT_sb_ssn = "sb_ssn";
    const FT_sb_cdhit = "sb_sdhit";
    const FT_sb_markers = "markers";
    const FT_sb_meta_cluster_sizes = "sb_cl_size";
    const FT_sb_meta_sp_clusters = "sb_sp_cl";
    const FT_sb_meta_cp_sing = "sb_sp_sg";

    const FT_sbq_ssn = "sb_qssn";
    const FT_sbq_protein_abundance_median = "sb_qprot";
    const FT_sbq_cluster_abundance_median = "sb_qclust";
    const FT_sbq_protein_abundance_norm_median = "sb_qprot_n";
    const FT_sbq_cluster_abundance_norm_median = "sb_qclust_n";
    const FT_sbq_protein_abundance_genome_norm_median = "sb_qprot_gn";
    const FT_sbq_cluster_abundance_genome_norm_median = "sb_qclust_gn";
    const FT_sbq_protein_abundance_mean = "sb_qprot_mean";
    const FT_sbq_cluster_abundance_mean = "sb_qclust_mean";
    const FT_sbq_protein_abundance_norm_mean = "sb_qprot_n_mean";
    const FT_sbq_cluster_abundance_norm_mean = "sb_qclust_n_mean";
    const FT_sbq_protein_abundance_genome_norm_mean = "sb_qprot_gn_mean";
    const FT_sbq_cluster_abundance_genome_norm_mean = "sb_qclust_gn_mean";
    const FT_sbq_meta_info = "sb_q_mg_info";

    const OPT_xgmml = 1;


    private static $types = array(
        self::FT_sizes => array("suffix" => "cluster_sizes", "ext" => "txt"),
        self::FT_color_cr => array("suffix" => "convergence_ratio", "ext" => "txt"),
        self::FT_fasta => array("suffix" => "FASTA", "ext" => "zip"),
        self::FT_fasta_ur90 => array("suffix" => "FASTA_UniRef90", "ext" => "zip"),
        self::FT_fasta_ur50 => array("suffix" => "FASTA_UniRef50", "ext" => "zip"),
        self::FT_fasta_dom => array("suffix" => "FASTA_Domain", "ext" => "zip"),
        self::FT_fasta_dom_ur90 => array("suffix" => "FASTA_Domain_UniRef90", "ext" => "zip"),
        self::FT_fasta_dom_ur50 => array("suffix" => "FASTA_Domain_UniRef50", "ext" => "zip"),
        self::FT_mapping => array("suffix" => "mapping_table", "ext" => "txt"),
        self::FT_mapping_dom => array("suffix" => "domain_mapping_table", "ext" => "txt"),
        self::FT_stats => array("suffix" => "stats", "ext" => "txt"),
        self::FT_sp_clusters => array("suffix" => "swissprot_clusters_desc", "ext" => "txt"),
        self::FT_sp_singletons => array("suffix" => "swissprot_singletons_desc", "ext" => "txt"),
        self::FT_ids => array("suffix" => "UniProt_IDs", "ext" => "zip"),
        self::FT_ur90_ids => array("suffix" => "UniRef90_IDs", "ext" => "zip"),
        self::FT_ur50_ids => array("suffix" => "UniRef50_IDs", "ext" => "zip"),
        self::FT_dom_ids => array("suffix" => "UniProt_Domain_IDs", "ext" => "zip"),
        self::FT_ur90_dom_ids => array("suffix" => "UniRef90_Domain_IDs", "ext" => "zip"),
        self::FT_ur50_dom_ids => array("suffix" => "UniRef50_Domain_IDs", "ext" => "zip"),
        self::FT_ssn => array("suffix" => "ssn", "ext" => "zip"),
        self::FT_cons_res_full => array("suffix" => "ConsensusResidue", "ext" => "zip"), // added on later _Full
        self::FT_cons_res_pct => array("suffix" => "ConsensusResidue", "ext" => "txt"),  // added on later _Percentage
        self::FT_cons_res_pos => array("suffix" => "ConsensusResidue", "ext" => "txt"),  // added on later _Position
        self::FT_len_hist => array("suffix" => "LenHist_UniProt_Full", "ext" => "zip"),
        self::FT_len_hist_ur90 => array("suffix" => "LenHist_UniRef90_Full", "ext" => "zip"),
        self::FT_len_hist_ur50 => array("suffix" => "LenHist_UniRef50_Full", "ext" => "zip"),
        self::FT_len_hist_dom => array("suffix" => "LenHist_UniProt_Domain", "ext" => "zip"),
        self::FT_len_hist_dom_ur90 => array("suffix" => "LenHist_UniRef90_Domain", "ext" => "zip"),
        self::FT_len_hist_dom_ur50 => array("suffix" => "LenHist_UniRef50_Domain", "ext" => "zip"),
        self::FT_hmm => array("suffix" => "HMMs_Full", "ext" => "zip"),
        self::FT_hmm_dom => array("suffix" => "HMMs_Domain", "ext" => "zip"),
        self::FT_msa => array("suffix" => "MSA_Full", "ext" => "zip"),
        self::FT_msa_dom => array("suffix" => "MSAs_Domain", "ext" => "zip"),
        self::FT_pim => array("suffix" => "PIMs_Full", "ext" => "zip"),
        self::FT_pim_dom => array("suffix" => "PIMs_Domain", "ext" => "zip"),
        self::FT_weblogo => array("suffix" => "WebLogos_Full", "ext" => "zip"),
        self::FT_weblogo_dom => array("suffix" => "WebLogos_Domain", "ext" => "zip"),
        self::FT_nc_legend => array("suffix" => "legend", "ext" => "png"),
        self::FT_nc_table => array("suffix" => "nc", "ext" => "tab"),
        self::FT_conv_ratio => array("suffix" => "conv_ratio", "ext" => "txt"),
        self::FT_cr_table => array("suffix" => "conv_ratio", "ext" => "txt"),
        self::FT_blast_evalue => array("suffix" => "blast_evalue", "ext" => "tab"),
        self::FT_gnn_ssn => array("suffix" => "coloredssn", "ext" => "zip", "other" => "xgmml"), // the ssns that come from GNN
        self::FT_ssn_gnn => array("suffix" => "ssn_cluster_gnn", "ext" => "zip", "other" => "xgmml"),
        self::FT_pfam_gnn => array("suffix" => "pfam_family_gnn", "ext" => "zip", "other" => "xgmml"),
        self::FT_pfam_nn => array("suffix" => "no_pfam_neighbors", "ext" => "zip"),
        self::FT_gnn_nn => array("suffix" => "nomatches_noneighbors", "ext" => "txt"),
        self::FT_cooc_table => array("suffix" => "cooc_table", "ext" => "txt"),
        self::FT_hub_count => array("suffix" => "hub_count", "ext" => "txt"),
        self::FT_gnd => array("suffix" => "arrow_data", "ext" => "sqlite"),
        self::FT_pfam_dom => array("suffix" => "pfam_mapping", "ext" => "zip"),
        self::FT_split_pfam_dom => array("suffix" => "split_pfam_mapping", "ext" => "zip"),
        self::FT_all_pfam_dom => array("suffix" => "all_pfam_mapping", "ext" => "zip"),
        self::FT_split_all_pfam_dom => array("suffix" => "all_split_pfam_mapping", "ext" => "zip"),
        self::FT_gnn_mapping => array("suffix" => "mapping_table", "ext" => "tab"),
        self::FT_gnn_mapping_dom => array("suffix" => "domain_mapping_table", "ext" => "tab"),

        self::FT_sb_ssn => array("suffix" => "identify_ssn", "ext" => "zip"),
        self::FT_sb_cdhit => array("suffix" => "cdhit", "ext" => "txt"),
        self::FT_sb_markers => array("suffix" => "markers", "ext" => "faa"),
        self::FT_sb_meta_cluster_sizes => array("suffix" => "cluster_sizes", "ext" => "tab"),
        self::FT_sb_meta_sp_clusters => array("suffix" => "swissprot_clusters", "ext" => "tab"),
        self::FT_sb_meta_cp_sing => array("suffix" => "swissprot_singletons", "ext" => "tab"),
        self::FT_sbq_ssn => array("suffix" => "quantify_ssn", "ext" => "zip"),
        self::FT_sbq_protein_abundance_median => array("suffix" => "protein_abundance", "ext" => "txt"),
        self::FT_sbq_cluster_abundance_median => array("suffix" => "cluster_abundance", "ext" => "txt"),
        self::FT_sbq_protein_abundance_norm_median => array("suffix" => "protein_abundance_norm", "ext" => "txt"),
        self::FT_sbq_cluster_abundance_norm_median => array("suffix" => "cluster_abundance_norm", "ext" => "txt"),
        self::FT_sbq_protein_abundance_genome_norm_median => array("suffix" => "protein_abundance_genome_norm", "ext" => "txt"),
        self::FT_sbq_cluster_abundance_genome_norm_median => array("suffix" => "cluster_abundance_genome_norm", "ext" => "txt"),
        self::FT_sbq_protein_abundance_mean => array("suffix" => "protein_abundance_mean", "ext" => "txt"),
        self::FT_sbq_cluster_abundance_mean => array("suffix" => "cluster_abundance_mean", "ext" => "txt"),
        self::FT_sbq_protein_abundance_norm_mean => array("suffix" => "protein_abundance_norm_mean", "ext" => "txt"),
        self::FT_sbq_cluster_abundance_norm_mean => array("suffix" => "cluster_abundance_norm_mean", "ext" => "txt"),
        self::FT_sbq_protein_abundance_genome_norm_mean => array("suffix" => "protein_abundance_genome_norm_mean", "ext" => "txt"),
        self::FT_sbq_cluster_abundance_genome_norm_mean => array("suffix" => "cluster_abundance_genome_norm_mean", "ext" => "txt"),
        self::FT_sbq_meta_info=> array("suffix" => "metagenome_desc", "ext" => "txt"),

        self::OPT_xgmml => 1,
    );



    public static function ext($type, $option = 0) {
        if (isset(self::$types[$type])) {
            if ($option)
                return self::$types[$type]["other"];
            else
                return self::$types[$type]["ext"];
        } else {
            return false;
        }
    }

    public static function suffix($type) {
        if (isset(self::$types[$type]))
            return self::$types[$type]["suffix"];
        else
            return false;
    }
}


