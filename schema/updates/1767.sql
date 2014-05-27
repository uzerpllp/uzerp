--
-- $Revision: 1.2 $
--

--

update permissions
   set description = null
     , title = 'Purchase Order Detail'
 where permission = 'view'
   and type = 'a'
   and parent_id = (select id
                      from permissions
                     where type = 'c'
                       and permission = 'porders');

update permissions
   set description = null
     , title = 'Sales Order Detail'
 where permission = 'view'
   and type = 'a'
   and parent_id = (select id
                      from permissions
                     where type = 'c'
                       and permission = 'sorders');

update permissions
   set title='Create Invoice from GRN'
 where permission='createinvoice'
   and type='a'
   and parent_id = (select id
                      from permissions
                     where type='c'
                       and permission='porders');
