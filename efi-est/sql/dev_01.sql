
alter table generate add column generate_seq_id float default 1 after generate_domain;
alter table generate add column generate_length_overlap float default 1 after generate_seq_id;
alter table generate add column generate_uniref enum('--', '50', '90') default '--' after generate_length_overlap;
alter table generate add column generate_force_demux boolean default FALSE after generate_uniref;



