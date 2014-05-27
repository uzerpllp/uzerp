--
-- $Revision: 1.1 $
--

INSERT INTO permissions
	(permission, type, title, display, parent_id, position)
	SELECT 'accrual', 'a', 'Received Lines Accruals', true, id, pos.position
	FROM permissions
	   , (select max(c.position)+1 as position
            from permissions c
               , permissions p
           where p.type='c'
             AND p.permission='porders'
             and p.id=c.parent_id) pos
	WHERE type='c'
	AND permission='porders';

INSERT INTO permissions
	(permission, type, title, display, parent_id, position)
	SELECT 'match_invoice', 'a', 'Match GRN to Invoice', true, id, pos.position
	FROM permissions
	   , (select max(c.position)+1 as position
            from permissions c
               , permissions p
           where p.type='c'
             AND p.permission='porders'
             and p.id=c.parent_id) pos
	WHERE type='c'
	AND permission='porders';
	