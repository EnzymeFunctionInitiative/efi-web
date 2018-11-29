
ALTER TABLE identify CHANGE COLUMN identify_status identify_status enum('NEW','RUNNING','FINISH','FAILED','CANCELLED','ARCHIVED');
ALTER TABLE quantify CHANGE COLUMN quantify_status quantify_status enum('NEW','RUNNING','FINISH','FAILED','CANCELLED','ARCHIVED');

