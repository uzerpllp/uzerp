ALTER TABLE module_admins ADD COLUMN id bigserial;

update module_admins
set id=nextval('module_admins_id_seq');

ALTER TABLE module_admins
 ADD CONSTRAINT module_admins_id_pkey PRIMARY KEY(id);

ALTER TABLE module_admins
 ADD CONSTRAINT module_admins_ukey1 UNIQUE (role_id, module_name);

CREATE OR REPLACE VIEW moduleadminsoverview AS 
 SELECT ma.role_id, ma.module_name, ma.id, r.name AS "role"
   FROM module_admins ma
   JOIN roles r ON r.id = ma.role_id;