--
-- $Revision: 1.1 $
--

-- View: haspermissionoverview

DROP VIEW haspermissionoverview;

CREATE OR REPLACE VIEW haspermissionoverview AS 
 SELECT hp.roleid, hp.permissionsid, hp.id, r.name AS role
 , p.permission, p.type, p.description, p.title, p.display, p."position", p.parent_id
 , r.usercompanyid
   FROM haspermission hp
   JOIN permissions p ON p.id = hp.permissionsid
   JOIN roles r ON r.id = hp.roleid;

ALTER TABLE haspermissionoverview OWNER TO "www-data";
