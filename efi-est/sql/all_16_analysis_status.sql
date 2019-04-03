
ALTER TABLE analysis CHANGE COLUMN analysis_status analysis_status enum('NEW','RUNNING','FINISH','FAILED','CANCELLED','ARCHIVED');

