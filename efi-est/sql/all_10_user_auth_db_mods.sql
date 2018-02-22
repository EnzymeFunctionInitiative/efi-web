alter table efi_user_auth.user_token add column user_group varchar(100);
alter table efi_user_auth.user_token add column user_admin integer not null default 0;

