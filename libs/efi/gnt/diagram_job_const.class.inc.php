<?php
namespace efi\gnt;


abstract class DiagramJob {
    const Uploaded = "DIRECT";          # A previously-saved .sqlite file that was uploaded; upload-id url parameter
    const UploadedZip = "DIRECT_ZIP";   # A previously-saved .sqlite file that was uploaded; upload-id url parameter
    const BLAST = "BLAST";              # direct-id url parameter
    const IdLookup = "ID_LOOKUP";       # direct-id url parameter
    const FastaLookup = "FASTA";        # direct-id url parameter
    const UNKNOWN = "UNKNOWN";
    const GNN = "GNN";                  # id url parameter

    const JobCompleted = "job.completed";
    const JobError = "job.error";
}


