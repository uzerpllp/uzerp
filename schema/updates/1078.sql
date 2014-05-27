ALTER TABLE so_header ADD COLUMN person_id bigint;

ALTER TABLE si_header ADD COLUMN person_id bigint;

insert into permissions
(permission, type, title, display, parent_id)
select 'confirm_pick_list', 'a', 'Confirm Pick List', true, id
from permissions
where type='c'
and permission='sorders';

insert into permissions
(permission, type, title, display, parent_id)
select 'confirm_sale', 'a', 'Confirm Sale', true, id
from permissions
where type='c'
and permission='sorders';