--
-- $Revision: 1.2 $
--

-- Engineering Module

-- Tables

CREATE TABLE eng_work_schedules
(
  id bigserial NOT NULL,
  job_no integer NOT NULL,
  description character varying NOT NULL,
  start_date date NOT NULL,
  end_date date NOT NULL,
  status character varying NOT NULL DEFAULT 'N'::character varying,
  centre_id bigint NOT NULL,
  planned_time bigint,
  actual_time bigint,
  mf_downtime_code_id bigint,
  usercompanyid bigint NOT NULL,
  alteredby character varying,
  lastupdated timestamp without time zone NOT NULL DEFAULT now() ,
  created timestamp without time zone NOT NULL DEFAULT now() ,
  createdby character varying,
  CONSTRAINT eng_work_schedules_pkey PRIMARY KEY (id),
  CONSTRAINT eng_work_schedules_centre_id_fkey FOREIGN KEY (centre_id)
      REFERENCES mf_centres (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT eng_work_schedules_alteredby_fkey FOREIGN KEY (alteredby)
      REFERENCES users (username) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION,
  CONSTRAINT eng_work_schedules_createdby_fkey FOREIGN KEY (createdby)
      REFERENCES users (username) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION,
  CONSTRAINT eng_work_schedules_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT eng_work_schedules_mf_downtime_code_id_fkey FOREIGN KEY (mf_downtime_code_id)
      REFERENCES mf_downtime_codes (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

ALTER TABLE eng_work_schedules OWNER TO "www-data";

CREATE TABLE eng_work_schedule_notes
(
  id serial NOT NULL,
  title character varying NOT NULL,
  note character varying NOT NULL,
  work_schedule_id bigint NOT NULL,
  usercompanyid bigint NOT NULL,
  alteredby character varying,
  lastupdated timestamp without time zone,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  CONSTRAINT eng_work_schedule_notes_pkey PRIMARY KEY (id),
  CONSTRAINT eng_work_schedule_notes_work_schedule_id_fkey FOREIGN KEY (work_schedule_id)
      REFERENCES eng_work_schedules (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT eng_work_schedule_notes_alteredby_fkey FOREIGN KEY (alteredby)
      REFERENCES users (username) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION,
  CONSTRAINT eng_work_schedule_notes_createdby_fkey FOREIGN KEY (createdby)
      REFERENCES users (username) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION,
  CONSTRAINT eng_work_schedule_notes_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);
ALTER TABLE eng_work_schedule_notes OWNER TO "www-data";

CREATE TABLE eng_work_schedule_parts
(
  id serial NOT NULL,
  work_schedule_id bigint NOT NULL,
  productline_header_id bigint NOT NULL,
  order_qty numeric,
  order_id bigint,
  usercompanyid bigint NOT NULL,
  alteredby character varying,
  lastupdated timestamp without time zone,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  CONSTRAINT eng_work_schedule_parts_pkey PRIMARY KEY (id),
  CONSTRAINT eng_work_schedule_parts_work_schedule_id_fkey FOREIGN KEY (work_schedule_id)
      REFERENCES eng_work_schedules (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT eng_work_schedule_parts_productline_header_id_fkey FOREIGN KEY (productline_header_id)
      REFERENCES po_product_lines_header (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT eng_work_schedule_parts_order_id_fkey FOREIGN KEY (order_id)
      REFERENCES po_header (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT eng_work_schedule_parts_alteredby_fkey FOREIGN KEY (alteredby)
      REFERENCES users (username) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION,
  CONSTRAINT eng_work_schedule_parts_createdby_fkey FOREIGN KEY (createdby)
      REFERENCES users (username) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION,
  CONSTRAINT eng_work_schedule_parts_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);
ALTER TABLE eng_work_schedule_parts OWNER TO "www-data";

CREATE TABLE eng_resources
(
  id bigserial NOT NULL,
  person_id bigint,
  work_schedule_id bigint NOT NULL,
  resource_id bigint NOT NULL,
  quantity bigint NOT NULL DEFAULT 1,
  usercompanyid bigint,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  lastupdated timestamp without time zone,
  alteredby character varying,
  CONSTRAINT eng_resources_pkey PRIMARY KEY (id),
  CONSTRAINT eng_resources_person_id FOREIGN KEY (person_id)
      REFERENCES person (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT eng_resources_work_schedule_id FOREIGN KEY (work_schedule_id)
      REFERENCES eng_work_schedules (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT eng_resources_resources_id_fkey FOREIGN KEY (resource_id)
      REFERENCES mf_resources (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT eng_resources_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);
ALTER TABLE eng_resources OWNER TO "www-data";

-- Views

CREATE VIEW eng_work_schedules_overview
AS
SELECT ws.*
     , mc.work_centre || mc.centre as centre, mc.mfdept_id
     , md.dept_code || dept as dept
  FROM eng_work_schedules ws
  JOIN mf_centres mc ON mc.id = ws.centre_id
  JOIN mf_depts md ON md.id = mc.mfdept_id;

ALTER TABLE eng_work_schedules_overview OWNER TO "www-data";

CREATE OR REPLACE VIEW eng_resources_overview AS 
 SELECT er.id, er.person_id, er.work_schedule_id, er.resource_id, er.quantity, er.usercompanyid, er.created, er.createdby
 , er.lastupdated, er.alteredby
 , ws.job_no, mr.resource_code, mr.description, mr.resource_rate
 , (per.firstname::text || ' '::text) || per.surname::text AS person
   FROM eng_resources er
   JOIN eng_work_schedules ws ON ws.id = er.work_schedule_id
   JOIN mf_resources mr ON mr.id = er.resource_id
   LEFT JOIN person per ON er.person_id = per.id;

ALTER TABLE eng_resources_overview OWNER TO "www-data";

CREATE VIEW eng_work_schedule_parts_overview
AS
SELECT ep.*
     , ws.job_no
     , plh.description, uom.uom_name
     , poh.order_number, poh.status
  FROM eng_work_schedule_parts ep
  JOIN eng_work_schedules ws ON ws.id = ep.work_schedule_id
  JOIN po_product_lines_header plh ON plh.id = ep.productline_header_id
  JOIN st_uoms uom ON plh.stuom_id = uom.id
  LEFT JOIN po_header poh ON poh.id = ep.order_id;

ALTER TABLE eng_work_schedule_parts_overview OWNER TO "www-data";

-- Modules

INSERT INTO modules
 (name, description, registered, enabled, location)
values
 ('engineering', 'Engineering', true, true, 'modules/public_pages/engineering');

INSERT INTO module_components
 (name, "type", location, module_id)
 SELECT 'engineeringsearch', 'M', location||'/models/EngineeringSearch.php', id
   FROM modules m
  WHERE name = 'engineering';

INSERT INTO module_components
 (name, "type", location, module_id)
 SELECT 'workschedulescontroller', 'C', location||'/controllers/WorkschedulesController.php', id
   FROM modules m
  WHERE name = 'engineering';

INSERT INTO module_components
 (name, "type", location, module_id)
 SELECT 'workschedule', 'M', location||'/models/WorkSchedule.php', id
   FROM modules m
  WHERE name = 'engineering';

INSERT INTO module_components
 (name, "type", location, module_id)
 SELECT 'workschedulecollection', 'M', location||'/models/WorkScheduleCollection.php', id
   FROM modules m
  WHERE name = 'engineering';

INSERT INTO module_components
 (name, "type", location, module_id)
 SELECT 'workschedulenotescontroller', 'C', location||'/controllers/WorkschedulenotesController.php', id
   FROM modules m
  WHERE name = 'engineering';

INSERT INTO module_components
 (name, "type", location, module_id)
 SELECT 'workschedulenote', 'M', location||'/models/WorkScheduleNote.php', id
   FROM modules m
  WHERE name = 'engineering';

INSERT INTO module_components
 (name, "type", location, module_id)
 SELECT 'workschedulenotecollection', 'M', location||'/models/WorkScheduleNoteCollection.php', id
   FROM modules m
  WHERE name = 'engineering';

INSERT INTO module_components
 (name, "type", location, module_id)
 SELECT 'engineeringresourcescontroller', 'C', location||'/controllers/EngineeringresourcesController.php', id
   FROM modules m
  WHERE name = 'engineering';

INSERT INTO module_components
 (name, "type", location, module_id)
 SELECT 'engineeringresource', 'M', location||'/models/EngineeringResource.php', id
   FROM modules m
  WHERE name = 'engineering';

INSERT INTO module_components
 (name, "type", location, module_id)
 SELECT 'engineeringresourcecollection', 'M', location||'/models/EngineeringResourceCollection.php', id
   FROM modules m
  WHERE name = 'engineering';

INSERT INTO module_components
 (name, "type", location, module_id)
 SELECT 'workschedulepartscontroller', 'C', location||'/controllers/WorkschedulepartsController.php', id
   FROM modules m
  WHERE name = 'engineering';

INSERT INTO module_components
 (name, "type", location, module_id)
 SELECT 'workschedulepart', 'M', location||'/models/WorkSchedulePart.php', id
   FROM modules m
  WHERE name = 'engineering';

INSERT INTO module_components
 (name, "type", location, module_id)
 SELECT 'workschedulepartcollection', 'M', location||'/models/WorkSchedulePartCollection.php', id
   FROM modules m
  WHERE name = 'engineering';

-- Permissions

INSERT INTO permissions
(permission, type, title, display, position, module_id)
 SELECT 'engineering', 'm', 'Engineering', true, per.position, mod.id
   FROM (SELECT max(position)+1 AS position
           FROM permissions
          WHERE parent_id is null) per
      , (SELECT id
           FROM modules m
          WHERE m.name = 'engineering') mod;

INSERT INTO permissions
 (permission, type, title, display, parent_id, position, module_id, component_id)
SELECT 'stitems', 'c', 'Stock Items', true, per.id, coalesce(pos.position,1) , mod.module_id, mod.id
  FROM permissions per
    , (SELECT max(c.position)+1 as position
         FROM permissions c
            , permissions p
        WHERE p.type='m'
          AND p.permission='engineering'
          AND p.id=c.parent_id) pos
   , (SELECT module_id, id
        FROM module_components mc
       WHERE mc.name = 'stitemscontroller') mod
 WHERE type='m'
   AND permission='engineering';

INSERT INTO permissions
 (permission, type, title, display, parent_id, position, module_id, component_id)
SELECT 'sttransactions', 'c', 'Stock Transactions', true, per.id, pos.position, mod.module_id, mod.id
  FROM permissions per
    , (SELECT max(c.position)+1 as position
         FROM permissions c
            , permissions p
        WHERE p.type='m'
          AND p.permission='engineering'
          AND p.id=c.parent_id) pos
   , (SELECT module_id, id
        FROM module_components mc
       WHERE mc.name = 'sttransactionscontroller') mod
 WHERE type='m'
   AND permission='engineering';

INSERT INTO permissions
 (permission, type, title, display, parent_id, position, module_id, component_id)
 SELECT 'workschedules', 'c', 'Work Schedules', true, per.id, pos.position, mod.module_id, mod.id
  FROM permissions per
    , (SELECT max(c.position)+1 as position
         FROM permissions c
            , permissions p
        WHERE p.type='m'
          AND p.permission='engineering'
          AND p.id=c.parent_id) pos
   , (SELECT module_id, id
        FROM module_components mc
       WHERE mc.name = 'workschedulescontroller') mod
 WHERE type='m'
   AND permission='engineering';

INSERT INTO permissions
 (permission, type, title, display, parent_id, position, module_id, component_id)
 SELECT 'workschedulenotes', 'c', 'Work Schedule Notes', false, per.id, pos.position, mod.module_id, mod.id
 FROM permissions per
   , (SELECT max(c.position)+1 as position
         FROM permissions c
            , permissions p
        WHERE p.type='m'
          AND p.permission='engineering'
          AND p.id=c.parent_id) pos
   , (SELECT module_id, id
        FROM module_components mc
       WHERE mc.name = 'workschedulenotescontroller') mod
 WHERE type='m'
   AND permission='engineering';

INSERT INTO permissions
 (permission, type, title, display, parent_id, position, module_id, component_id)
 SELECT 'workscheduleparts', 'c', 'Work Schedule Parts', false, per.id, pos.position, mod.module_id, mod.id
 FROM permissions per
   , (SELECT max(c.position)+1 as position
         FROM permissions c
            , permissions p
        WHERE p.type='m'
          AND p.permission='engineering'
          AND p.id=c.parent_id) pos
   , (SELECT module_id, id
        FROM module_components mc
       WHERE mc.name = 'workschedulepartscontroller') mod
 WHERE type='m'
   AND permission='engineering';
