--
-- $Revision: 1.1 $
--


INSERT INTO permissions
	(permission, type, title, display, parent_id, position)
	SELECT 'cancel_grn', 'a', 'Cancel GRN', false, id, pos.position
	FROM permissions
	   , (select max(c.position)+1 as position
            from permissions c
               , permissions p
           where p.type='c'
             AND p.permission='poreceivedlines'
             and p.id=c.parent_id) pos
	WHERE type='c'
	AND permission='poreceivedlines';
