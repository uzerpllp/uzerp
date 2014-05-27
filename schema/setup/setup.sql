insert into system_companies
(theme, access_enabled)
values
('default', 'FULL');

insert into users
(username, password, lastcompanylogin, access_enabled)
select 'admin', md5('password'), max(id), true
   from system_companies;

insert into user_company_access
(username, usercompanyid, enabled)
select 'admin', md5('password'), true
   from system_companies;