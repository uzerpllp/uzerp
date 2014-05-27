--
-- $Revision: 1.1 $
--


INSERT INTO permissions
	(permission, type, title, display, parent_id, position)
	SELECT 'grn_write_off', 'a', 'Write Off GRN', true, id, pos.position
	FROM permissions
	   , (select max(c.position)+1 as position
            from permissions c
               , permissions p
           where p.type='c'
             AND p.permission='porders'
             and p.id=c.parent_id) pos
	WHERE type='c'
	AND permission='porders';
