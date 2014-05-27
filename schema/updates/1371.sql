--
-- $Revision: 1.3 $
--

insert into uzlets
  ("name", title, preset, enabled, dashboard, usercompanyid)
  select 'SalesHistorySummary'
       , 'Sales History Summary'
       , true
       , true
       , true
       , id
   from system_companies;
   
insert into uzlet_modules
  (uzlet_id, module_id, usercompanyid)
  select u.id, m.id, u.usercompanyid
    from uzlets u
       , modules m
   where u.name='SalesHistorySummary'
     and m.name='sales_invoicing';