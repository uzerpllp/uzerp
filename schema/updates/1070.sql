insert into permissions
(permission, type, title, display, parent_id)
select 'view_balance', 'a', 'View Balances by Stock/Location', true, id
from permissions
where type='c'
and permission='sttransactions';