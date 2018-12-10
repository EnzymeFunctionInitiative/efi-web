
ALTER TABLE gnn ADD COLUMN gnn_params TEXT AFTER gnn_gnt_source_id;
ALTER TABLE gnn ADD COLUMN gnn_results TEXT AFTER gnn_params;

