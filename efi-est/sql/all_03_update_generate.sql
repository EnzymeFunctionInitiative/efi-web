

ALTER TABLE `generate` DROP COLUMN  `generate_domain`;
ALTER TABLE `generate` DROP COLUMN  `generate_seq_id`;
ALTER TABLE `generate` DROP COLUMN  `generate_length_overlap`;
ALTER TABLE `generate` DROP COLUMN  `generate_no_demux`;
ALTER TABLE `generate` DROP COLUMN  `generate_uniref`;
ALTER TABLE `generate` DROP COLUMN  `generate_evalue`;
ALTER TABLE `generate` DROP COLUMN  `generate_fraction`;
ALTER TABLE `generate` DROP COLUMN  `generate_families`;
ALTER TABLE `generate` DROP COLUMN  `generate_fasta_file`;
ALTER TABLE `generate` DROP COLUMN  `generate_blast`;
ALTER TABLE `generate` DROP COLUMN  `generate_blast_max_sequence`;

ALTER TABLE `generate` DROP COLUMN  `generate_num_seq`;
ALTER TABLE `generate` DROP COLUMN  `generate_num_family_seq`;
ALTER TABLE `generate` DROP COLUMN  `generate_total_num_file_seq`;
ALTER TABLE `generate` DROP COLUMN  `generate_num_matched_file_seq`;
ALTER TABLE `generate` DROP COLUMN  `generate_num_unmatched_file_seq`;

