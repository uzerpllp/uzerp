insert into permissions
(permission, type, title, display, parent_id)
select 'update_glcodes', 'a', 'Update Order Line GL Codes', false, id
from permissions
where type='c'
and permission='porders';