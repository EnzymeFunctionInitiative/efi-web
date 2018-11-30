
ALTER TABLE gnn CHANGE COLUMN gnn_source_id gnn_est_source_id INT(11);
ALTER TABLE gnn ADD COLUMN gnn_gnt_source_id INT(11) AFTER gnn_est_source_id;

