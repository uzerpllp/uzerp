--
-- $Revision: 1.3 $
--

-- Table: hasreport

-- DROP TABLE hasreport;

CREATE TABLE hasreport
(
  role_id bigint NOT NULL,
  permissions_id bigint,
  report_id bigint NOT NULL,
  id bigserial NOT NULL,
  CONSTRAINT hasreport_pkey PRIMARY KEY (id),
  CONSTRAINT hasreport_permissions_id_fkey FOREIGN KEY (permissions_id)
      REFERENCES permissions (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT hasreport_role_id_fkey FOREIGN KEY (role_id)
      REFERENCES roles (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT hasreport_report_id_fkey FOREIGN KEY (report_id)
      REFERENCES reports (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT hasreport_ukey1 UNIQUE (role_id, permissions_id, report_id)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE haspermission OWNER TO "www-data";

-- Index: hasreport_permissions_id_index

-- DROP INDEX hasreport_permissions_id_index;

CREATE INDEX hasreport_permissions_id_index
  ON hasreport
  USING btree
  (permissions_id);

-- Index: hasreport_role_id_index

-- DROP INDEX hasreport_role_id_index;

CREATE INDEX hasreport_report_id_index
  ON hasreport
  USING btree
  (report_id);

-- Index: hasreport_role_id_index

-- DROP INDEX hasreport_role_id_index;

CREATE INDEX hasreport_role_id_index
  ON hasreport
  USING btree
  (role_id);

-- Index: hasreport_role_id_permissionsid_index

-- DROP INDEX hasreport_role_id_permissionsid_index;

CREATE INDEX hasreport_role_id_permissionsid_index
  ON hasreport
  USING btree
  (role_id, permissions_id);

DROP VIEW IF EXISTS hasreport_overview;
CREATE OR REPLACE VIEW hasreport_overview AS 
 SELECT hr.id, hr.role_id, hr.report_id, hr.permissions_id, rep.description, rol.name AS role
   FROM hasreport hr
   JOIN reports rep ON rep.id = hr.report_id
   JOIN roles rol ON rol.id = hr.role_id;

INSERT INTO module_components
  ("name", "type", location, module_id)
  SELECT 'hasreportscontroller', 'C', m.location||'/controllers/HasreportsController.php', id
    FROM modules m
   WHERE m.name = 'admin';

INSERT INTO module_components
  ("name", "type", location, module_id)
  SELECT 'hasreport', 'M', m.location||'/models/HasReport.php', id
    FROM modules m
   WHERE m.name = 'admin';

INSERT INTO module_components
  ("name", "type", location, module_id)
  SELECT 'hasreportcollection', 'M', m.location||'/models/HasReportCollection.php', id
    FROM modules m
   WHERE m.name = 'admin';