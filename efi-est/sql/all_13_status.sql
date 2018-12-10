
ALTER TABLE generate CHANGE COLUMN generate_status generate_status enum('NEW','RUNNING','FINISH','FAILED','CANCELLED','ARCHIVED');

