
ALTER TABLE gnn CHANGE COLUMN gnn_gnt_source_id gnn_parent_id INT(11);
ALTER TABLE gnn ADD COLUMN gnn_child_type VARCHAR(10) AFTER gnn_parent_id;

