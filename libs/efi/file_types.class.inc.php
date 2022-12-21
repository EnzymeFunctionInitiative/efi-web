<?php

namespace efi;


class file_types {

    const FT_sizes = "cluster_sizes";
    const FT_color_cr = "convergence_ratio";
    const FT_fasta = "FASTA";
    const FT_fasta_ur90 = "FASTA_UniRef90";
    const FT_fasta_ur50 = "FASTA_UniRef50";
    const FT_fasta_dom = "FASTA_Domain";
    const FT_fasta_dom_ur90 = "FASTA_Domain_UniRef90";
    const FT_fasta_dom_ur50 = "FASTA_Domain_UniRef50";
    const FT_mapping = "mapping_table";
    const FT_stats = "stats";
    const FT_sp_clusters = "swissprot_clusters_desc";
    const FT_sp_singletons = "swissprot_singletons_desc";
    const FT_ids = "UniProt_IDs";
    const FT_ur90_ids = "UniRef90_IDs";
    const FT_ur50_ids = "UniRef50_IDs";
    const FT_dom_ids = "UniProt_Domain_IDs";
    const FT_ur90_dom_ids = "UniRef90_Domain_IDs";
    const FT_ur50_dom_ids = "UniRef50_Domain_IDs";
    const FT_ssn = "ssn";
    const FT_cons_res_full = "ConsensusResidue_Full";
    const FT_cons_res_pct = "ConsensusResidue_Percentage";
    const FT_cons_res_pos = "ConsensusResidue_Position";
    const FT_hmm = "HMMs";
    const FT_len_hist = "LenHist_UniProt";
    const FT_len_hist_ur90 = "LenHist_UniRef90";
    const FT_len_hist_ur50 = "LenHist_UniRef50";
    const FT_msa = "MSAs";
    const FT_pim = "PIMs";
    const FT_weblogo = "WebLogos";
    const FT_nc_legend = "nc_legend";
    const FT_nc_table = "nc_table";
    const FT_conv_ratio = "conv_ratio";
    const FT_blast_evalue = "blast_evalue";

    public static function get_ext($type) {
        $ext_mapping = array(
            FT_sizes => "txt",
            FT_color_cr => "txt",
            FT_fasta => "zip",
            FT_fasta_ur90 => "zip",
            FT_fasta_ur50 => "zip",
            FT_fasta_dom => "zip",
            FT_fasta_dom_ur90 => "zip",
            FT_fasta_dom_ur50 => "zip",
            FT_mapping => "txt",
            FT_stats => "txt",
            FT_sp_clusters => "txt",
            FT_sp_singletons => "txt",
            FT_ids => "zip",
            FT_ur90_ids => "zip",
            FT_ur50_ids => "zip",
            FT_dom_ids => "zip",
            FT_ur90_dom_ids => "zip",
            FT_ur50_dom_ids => "zip",
            FT_ssn => "zip",
            FT_cons_res_full => "zip",
            FT_cons_res_pct => "txt",
            FT_cons_res_pos => "txt",
            FT_hmm => "zip",
            FT_len_hist => "zip",
            FT_len_hist_ur90 => "zip",
            FT_len_hist_ur50=> "zip",
            FT_msa => "zip",
            FT_pim => "zip",
            FT_weblogo => "zip",
            FT_nc_legend => "png",
            FT_nc_table => "tab",
            FT_conv_ratio => "txt",
            FT_blast_evalue => "tab",
        );
        if (!isset($ext_mapping[$type]))
            return "";
        else
            return $ext_mapping[$type];
    }
}


