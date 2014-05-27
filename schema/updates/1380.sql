--
-- $Revision: 1.3 $
--

insert into uzlets
  ("name", title, preset, enabled, dashboard, usercompanyid)
  select 'BankAccountsSummary'
       , 'Bank Accounts Summary
       ', true
       , true
       , true
       , id
   from system_companies;

insert into uzlet_modules
  (uzlet_id, module_id, usercompanyid)
  select u.id, m.id, u.usercompanyid
    from uzlets u
       , modules m
   where u.name='BankAccountsSummary'
     and m.name='cashbook';