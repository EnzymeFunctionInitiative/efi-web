
alter table quantify change column quantify_status quantify_status enum('NEW','RUNNING','FINISH','FAILED','CANCELLED');
alter table identify change column identify_status identify_status enum('NEW','RUNNING','FINISH','FAILED','CANCELLED');

