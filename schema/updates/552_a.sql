ALTER TABLE userpreferences ADD COLUMN usercompanyid int8;

UPDATE userpreferences
       SET usercompanyid=1;

ALTER TABLE userpreferences ALTER COLUMN usercompanyid SET NOT NULL;

ALTER TABLE userpreferences
  ADD CONSTRAINT userpreferences_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE permissions ADD COLUMN parent_id int8;

ALTER TABLE permissions DROP CONSTRAINT permissions_permission_key;

ALTER TABLE permissions
  ADD CONSTRAINT permissions_permission_key UNIQUE(parent_id, permission);

ALTER TABLE system_companies ALTER COLUMN company_id DROP NOT NULL;

ALTER TABLE users ADD COLUMN audit_enabled bool;

ALTER TABLE users ALTER COLUMN audit_enabled SET DEFAULT false;

ALTER TABLE users ADD COLUMN debug_enabled bool;

ALTER TABLE users ALTER COLUMN debug_enabled SET DEFAULT false;

ALTER TABLE users ADD COLUMN access_enabled bool;

ALTER TABLE users ALTER COLUMN access_enabled SET DEFAULT true;

UPDATE users
    SET access_enabled = true
       ,audit_enabled = false
       ,debug_enabled = false;

ALTER TABLE system_companies ADD COLUMN published bool;

ALTER TABLE system_companies ALTER COLUMN published SET DEFAULT false;

ALTER TABLE system_companies ADD COLUMN published_username varchar;

ALTER TABLE system_companies ADD COLUMN published_owner_id int4;

ALTER TABLE system_companies ADD COLUMN audit_enabled bool;

ALTER TABLE system_companies ALTER COLUMN audit_enabled SET DEFAULT false;

ALTER TABLE system_companies ADD COLUMN debug_enabled bool;

ALTER TABLE system_companies ALTER COLUMN debug_enabled SET DEFAULT false;

ALTER TABLE system_companies ADD COLUMN access_role_id int4;

ALTER TABLE system_companies ADD COLUMN access_enabled varchar;

ALTER TABLE system_companies ALTER COLUMN access_enabled SET DEFAULT 'FULL'::character varying;

ALTER TABLE system_companies ADD COLUMN info_message varchar;

UPDATE system_companies
    SET access_enabled = 'FULL'
       ,published = false
       ,audit_enabled = false
       ,debug_enabled = false;

ALTER TABLE system_companies ALTER COLUMN access_enabled SET NOT NULL;

CREATE TABLE audit_header
(
  id serial NOT NULL,
  username varchar,
  customer_id int4,
  sessionid varchar,
  created timestamp NOT NULL DEFAULT now(),
  lastupdated timestamp DEFAULT now(),
  CONSTRAINT audit_header_pkey PRIMARY KEY (id)
) ;

CREATE TABLE audit_lines
(
  id serial NOT NULL,
  audit_id int4 NOT NULL,
  line varchar,
  created timestamp NOT NULL DEFAULT now(),
  CONSTRAINT audit_lines_pkey PRIMARY KEY (id),
  CONSTRAINT audit_lines_audit_id_fkey FOREIGN KEY (audit_id)
      REFERENCES audit_header (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE objectroles
(
  id bigserial NOT NULL,
  object_id int8 NOT NULL,
  object_type varchar NOT NULL,
  role_id int8 NOT NULL,
  "read" bool NOT NULL DEFAULT false,
  "write" bool NOT NULL DEFAULT false,
  CONSTRAINT objectroles_pkey PRIMARY KEY (object_id, object_type, role_id),
  CONSTRAINT objectroles_roleid_fkey FOREIGN KEY (role_id)
      REFERENCES roles (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE OR REPLACE VIEW accesspermissions AS 
 SELECT DISTINCT o.id, o.object_id, o.object_type, o.role_id, o."read", o."write", hr.username
   FROM objectroles o
   LEFT JOIN hasrole hr ON o.role_id = hr.roleid
  ORDER BY o.id, o.object_id, o.object_type, o.role_id, o."read", o."write", hr.username;

INSERT INTO objectroles
(
  object_id,
  object_type,
  role_id,
  "read",
  "write"
)
SELECT companyid, 'company', roleid, "read", "write"
 FROM companyroles;

DROP VIEW useroverview;

DROP VIEW system_companiesoverview;

CREATE OR REPLACE VIEW system_companiesoverview AS 
 SELECT sc.id, sc.company_id, sc.enabled, sc.theme, sc.published, sc.published_username, sc.published_owner_id, sc.audit_enabled, sc.debug_enabled, sc.access_role_id, sc.access_enabled, sc.info_message, c.name AS company, (p.firstname::text || ' '::text) || p.surname::text AS published_owner
   FROM system_companies sc
   LEFT JOIN company c ON sc.company_id = c.id
   LEFT JOIN person p ON sc.published_owner_id = p.id;

DROP VIEW companyrolesoverview;

CREATE OR REPLACE VIEW companyrolesoverview AS 
 SELECT cr.id, cr.object_id as companyid, cr.role_id, cr."read", cr."write", hr.username
, c.name AS company, r.name AS "role"
   FROM objectroles cr
   JOIN company c ON cr.object_id = c.id
                 AND cr.object_type='company'
   JOIN roles r ON cr.role_id = r.id
   LEFT JOIN hasrole hr ON cr.role_id = hr.roleid;