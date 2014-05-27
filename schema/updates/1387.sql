--
-- $Revision: 1.1 $
--

INSERT INTO permissions
	(permission, type, title, display, parent_id)
	SELECT 'cache_management', 'm', 'Cache Management', true, id
	FROM permissions
	WHERE type='g'
	AND permission='systemsetup';

INSERT INTO permissions
	(permission, type, title, display, parent_id)
	SELECT 'cache', 'c', 'Cache', true, id
	FROM permissions
	WHERE type='m'
	AND permission='cache_management';