ALTER TABLE slmaster ADD COLUMN "account_status" character varying;

UPDATE slmaster
   SET account_status='O';
   
ALTER TABLE slmaster ALTER COLUMN "account_status" SET NOT NULL;

insert into permissions
(permission, "type", title, display, parent_id)
select 'updatestatus','a', 'Update Status', false, id
  from permissions
 where "type"='c'
   and permission='slcustomers'