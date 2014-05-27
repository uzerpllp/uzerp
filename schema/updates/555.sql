DROP VIEW haspermissionoverview;

CREATE OR REPLACE VIEW haspermissionoverview AS 
 SELECT hp.roleid, hp.permissionsid, hp.id, r.name AS "role", p."position", p.permission, r.usercompanyid
   FROM haspermission hp
   JOIN permissions p ON p.id = hp.permissionsid
   JOIN roles r ON r.id = hp.roleid;

DROP VIEW companypermissionsoverview;

CREATE OR REPLACE VIEW companypermissionsoverview AS 
 SELECT cp.id, cp.usercompanyid, cp.permissionid, p."position", p.permission, c.name
   FROM companypermissions cp
   JOIN permissions p ON cp.permissionid = p.id
   JOIN company c ON cp.usercompanyid = c.id;
