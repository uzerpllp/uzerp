insert into permissions
(permission, type, title, display, parent_id)
select 'clone_item', 'a', 'Clone Item', true, id
from permissions
where type='c'
and permission='stitems';