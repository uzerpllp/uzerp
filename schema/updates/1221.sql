insert into permissions
(permission, type, title, display, parent_id, position)
select 'printdespatchnote', 'a', 'Print Despatch Note', false, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='sodespatchlines')) as next
 where type='c'
   and permission='sodespatchlines';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'printtransfernote', 'a', 'Print Transfer Note', false, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='whtransferlines')) as next
 where type='c'
   and permission='whtransferlines';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'invoicelist', 'a', 'Print Invoice List', false, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='pinvoices')) as next
 where type='c'
   and permission='pinvoices';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'invoicelist', 'a', 'Print Invoice List', false, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='sinvoices')) as next
 where type='c'
   and permission='sinvoices';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'printinvoice', 'a', 'Print Invoice', false, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='sinvoices')) as next
 where type='c'
   and permission='sinvoices';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'printtrialbalance', 'a', 'Print Trial Balance', false, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='glbalances')) as next
 where type='c'
   and permission='glbalances';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'ageddebtors', 'a', 'Print Aged Debtors Report', false, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='slcustomers')) as next
 where type='c'
   and permission='slcustomers';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'print_customer_statement', 'a', 'Print Statement', false, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='slcustomers')) as next
 where type='c'
   and permission='slcustomers';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'printtransactions', 'a', 'Print Transactions', false, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='sltransactions')) as next
 where type='c'
   and permission='sltransactions';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'suggestedpayments', 'a', 'Print Suggested Payments', false, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='plsuppliers')) as next
 where type='c'
   and permission='plsuppliers';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'agedcreditors', 'a', 'Print Aged Creditors', false, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='plsuppliers')) as next
 where type='c'
   and permission='plsuppliers';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'process_payments', 'a', 'Process Payments', false, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='plsuppliers')) as next
 where type='c'
   and permission='plsuppliers';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'printtransactions', 'a', 'Print Transactions', false, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='pltransactions')) as next
 where type='c'
   and permission='pltransactions';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'printtransactions', 'a', 'Print Transactions', false, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='vat')) as next
 where type='c'
   and permission='vat';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'printtransactions', 'a', 'Print Transactions', false, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='stitems')) as next
 where type='c'
   and permission='stitems';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'printbalancelist', 'a', 'Print Balance List', false, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='whbins')) as next
 where type='c'
   and permission='whbins';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'printsupplydemand', 'a', 'Print Supply Demand', false, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='poproductlines')) as next
 where type='c'
   and permission='poproductlines';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'printsupplydemanddetail', 'a', 'Print Supply Demand Detail', false, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='poproductlines')) as next
 where type='c'
   and permission='poproductlines';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'printorderlist', 'a', 'Print Order List', false, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='porders')) as next
 where type='c'
   and permission='porders';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'printorder', 'a', 'Print Order', false, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='porders')) as next
 where type='c'
   and permission='porders';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'printgrnireport', 'a', 'Print GRNI Report', false, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='porders')) as next
 where type='c'
   and permission='porders';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'printsupplydemand', 'a', 'Print Supply Demand', false, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='soproductlines')) as next
 where type='c'
   and permission='soproductlines';

update permissions
   set position=position+4
 where position>11
   and parent_id = (select id
                      from permissions
                     where type='c'
                       and permission='sorders');

insert into permissions
(permission, type, title, display, parent_id, position)
select 'print_remittances', 'a', 'Print Remittances', false, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='plsuppliers')) as next
 where type='c'
   and permission='plsuppliers';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'printcostsheet', 'a', 'Print Cost Sheet', false, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='stcosts')) as next
 where type='c'
   and permission='stcosts';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'printcomplaint', 'a', 'Print Complaint', false, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='sdcomplaints')) as next
 where type='c'
   and permission='sdcomplaints';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'printcomplaint', 'a', 'Print Complaint', false, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='rrcomplaints')) as next
 where type='c'
   and permission='rrcomplaints';

update permissions
   set position=position+4
 where position>11
   and parent_id = (select id
                      from permissions
                     where type='c'
                       and permission='sorders');

insert into permissions
(permission, type, title, display, parent_id, position)
select 'print_single_remittance', 'a', 'Print Single Remittance', false, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='plsuppliers')) as next
 where type='c'
   and permission='plsuppliers';

update permissions
   set position=position+4
 where position>11
   and parent_id = (select id
                      from permissions
                     where type='c'
                       and permission='sorders');

insert into permissions
(permission, type, title, display, parent_id, position)
select 'printorderlist', 'a', 'Print Order List', false, id, 12
  from permissions
 where type='c'
   and permission='sorders';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'printacknowledgement', 'a', 'Print Acknowledgement', false, id, 13
  from permissions
 where type='c'
   and permission='sorders';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'printproformainvoice', 'a', 'Print Pro Forma Invoice', false, id, 14
  from permissions
 where type='c'
   and permission='sorders';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'printpicklist', 'a', 'Print Pick List', false, id, 15
  from permissions
 where type='c'
   and permission='sorders';