--
--  Tables
--

ALTER TABLE mf_depts
  ADD COLUMN production_recording boolean;

ALTER TABLE mf_centres
  ADD COLUMN production_recording boolean;

DROP VIEW mf_centresoverview;

CREATE OR REPLACE VIEW mf_centresoverview AS 
 SELECT c.*
 , d.dept AS mfdept
   FROM mf_centres c
   JOIN mf_depts d ON c.mfdept_id = d.id;

CREATE TABLE mf_waste_types
(
  id bigserial NOT NULL,
  description character varying NOT NULL,
  uom_id bigint NOT NULL,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT mf_waste_types_pkey PRIMARY KEY (id),
  CONSTRAINT mf_waste_types_uom_id_fkey FOREIGN KEY (uom_id)
      REFERENCES st_uoms (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT mf_waste_types_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE mf_centre_waste_types
(
  id bigserial NOT NULL,
  mf_centre_id bigint NOT NULL,
  mf_waste_type_id bigint NOT NULL,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT mf_centre_waste_types_pkey PRIMARY KEY (id),
  CONSTRAINT mf_centre_waste_types_mf_centre_id_fkey FOREIGN KEY (mf_centre_id)
      REFERENCES mf_centres (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT mf_centre_waste_types_mf_waste_type_fkey FOREIGN KEY (mf_waste_type_id)
      REFERENCES mf_waste_types (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT mf_centre_waste_types_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE mf_downtime_codes
(
  id bigserial NOT NULL,
  downtime_code character varying NOT NULL,
  description character varying NOT NULL,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT mf_downtime_codes_pkey PRIMARY KEY (id),
  CONSTRAINT mf_downtime_codes_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE mf_shifts
(
  id bigserial NOT NULL,
  shift character varying NOT NULL,
  shift_date date DEFAULT now(),
  comment character varying,
  mf_dept_id bigint NOT NULL,
  mf_centre_id bigint NOT NULL,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT mf_shifts_pkey PRIMARY KEY (id),
  CONSTRAINT mf_shifts_mf_dept_id_fkey FOREIGN KEY (mf_dept_id)
      REFERENCES mf_depts (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT mf_shifts_mf_centre_id_fkey FOREIGN KEY (mf_centre_id)
      REFERENCES mf_centres (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT mf_shifts_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT mf_shifts_uk1 UNIQUE (shift, shift_date, mf_dept_id, mf_centre_id, usercompanyid)
);

CREATE TABLE mf_centre_downtime_codes
(
  id bigserial NOT NULL,
  mf_centre_id bigint NOT NULL,
  mf_downtime_code_id bigint NOT NULL,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT mf_centre_downtime_codes_pkey PRIMARY KEY (id),
  CONSTRAINT mf_centre_downtime_codes_mfcentre_id_fkey FOREIGN KEY (mf_centre_id)
      REFERENCES mf_centres (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT mf_centre_downtime_codes_mf_downtime_code_id_fkey FOREIGN KEY (mf_downtime_code_id)
      REFERENCES mf_downtime_codes (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT mf_centre_downtime_codes_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE mf_shift_outputs
(
  id bigserial NOT NULL,
  mf_shift_id bigint NOT NULL,
  stitem_id bigint NOT NULL,
  uom_id bigint NOT NULL,
  output numeric NOT NULL,
  planned_time numeric NOT NULL,
  run_time_speed numeric NOT NULL,
  operators numeric NOT NULL,
  work_order_id bigint,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT mf_shift_outputs_pkey PRIMARY KEY (id),
  CONSTRAINT mf_shift_outputs_shift_id_fkey FOREIGN KEY (mf_shift_id)
      REFERENCES mf_shifts (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT mf_shift_outputs_stitem_id_fkey FOREIGN KEY (stitem_id)
      REFERENCES st_items (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT mf_shift_outputs_uom_id_fkey FOREIGN KEY (uom_id)
      REFERENCES st_uoms (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT mf_shift_outputs_work_order_id_fkey FOREIGN KEY (work_order_id)
      REFERENCES mf_workorders (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT mf_shift_outputs_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE mf_shift_waste
(
  id bigserial NOT NULL,
  mf_shift_outputs_id bigint NOT NULL,
  mf_centre_waste_type_id bigint NOT NULL,
  qty numeric NOT NULL,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT mf_shift_waste_pkey PRIMARY KEY (id),
  CONSTRAINT mf_shift_waste_mf_shift_outputs_id_fkey FOREIGN KEY (mf_shift_outputs_id)
      REFERENCES mf_shift_outputs (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT mf_shift_waste_mf_centre_waste_type_id_fkey FOREIGN KEY (mf_centre_waste_type_id)
      REFERENCES mf_centre_waste_types (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT mf_shift_waste_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE mf_shift_downtime
(
  id bigserial NOT NULL,
  mf_shift_id bigint NOT NULL,
  mf_centre_downtime_code_id bigint NOT NULL,
  down_time numeric NOT NULL,
  time_period character varying NOT NULL,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT mf_shift_downtime_pkey PRIMARY KEY (id),
  CONSTRAINT mf_shift_downtime_mf_shift_id_fkey FOREIGN KEY (mf_shift_id)
      REFERENCES mf_shifts (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT mf_shift_downtime_mf_centre_downtime_code_id_fkey FOREIGN KEY (mf_centre_downtime_code_id)
      REFERENCES mf_centre_downtime_codes (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT mf_shift_downtime_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);


--
--  Views
--

CREATE OR REPLACE VIEW mf_shifts_overview AS 
 SELECT s.*
      , (d.dept_code::text || ' - '::text) || d.dept::text AS mf_dept
      , (c.work_centre::text || ' - '::text) || c.centre::text AS mf_centre
   FROM mf_shifts s
   JOIN mf_depts d ON d.id = s.mf_dept_id
   JOIN mf_centres c ON c.id = s.mf_centre_id;

   
CREATE OR REPLACE VIEW mf_waste_types_overview AS
SELECT w.*
     , u.uom_name
  FROM mf_waste_types w
  JOIN st_uoms u ON u.id = w.uom_id;

CREATE OR REPLACE VIEW mf_centre_downtime_codes_overview AS
SELECT cdc.*
     , c.work_centre||' - '||c.centre as mf_centre
     , dc.downtime_code||' - '||dc.description as downtime_code
  FROM mf_centre_downtime_codes cdc
  JOIN mf_centres c ON c.id = cdc.mf_centre_id
  JOIN mf_downtime_codes dc ON dc.id = cdc.mf_downtime_code_id;

CREATE OR REPLACE VIEW mf_centre_waste_types_overview AS
SELECT cwt.*
     , c.work_centre||' - '||c.centre as mf_centre
     , wt.description as waste_type
  FROM mf_centre_waste_types cwt
  JOIN mf_centres c ON c.id = cwt.mf_centre_id
  JOIN mf_waste_types wt ON wt.id = cwt.mf_waste_type_id;

CREATE OR REPLACE VIEW mf_shift_outputs_overview AS 
 SELECT o.*
 , s.shift
 , (i.item_code::text || ' - '::text) || i.description::text AS stitem
 , u.uom_name
 , w.wo_number
   FROM mf_shift_outputs o
   JOIN mf_shifts s ON s.id = o.mf_shift_id
   JOIN st_items i ON i.id = o.stitem_id
   JOIN st_uoms u ON u.id = o.uom_id
   LEFT JOIN mf_workorders w ON w.id = o.work_order_id;

CREATE OR REPLACE VIEW mf_shift_downtime_overview AS 
 SELECT sd.*
 , s.shift
 , (dc.downtime_code::text || ' - '::text) || dc.description::text AS downtime_code
   FROM mf_shift_downtime sd
   JOIN mf_shifts s ON s.id = sd.mf_shift_id
   JOIN mf_centre_downtime_codes cdc ON cdc.id = sd.mf_centre_downtime_code_id
   JOIN mf_downtime_codes dc ON dc.id = cdc.mf_downtime_code_id;

CREATE OR REPLACE VIEW mf_shift_waste_overview AS
SELECT sw.*
     , s.shift
     , wt.description as waste_type
     , wt.uom_name
  FROM mf_shift_waste sw
  JOIN mf_shift_outputs so ON so.id = sw.mf_shift_outputs_id
  JOIN mf_shifts s ON s.id = so.mf_shift_id
  JOIN mf_centre_waste_types cwt ON cwt.id = sw.mf_centre_waste_type_id
  JOIN mf_waste_types_overview wt ON wt.id = cwt.mf_waste_type_id;

DROP VIEW mf_operationsoverview;

CREATE OR REPLACE VIEW mf_operationsoverview AS 
 SELECT o.id, o.op_no, o.start_date, o.end_date, o.remarks, o.volume_target, o.volume_period
 , o.quality_target, o.uptime_target, o.resource_qty, o.stitem_id, o.volume_uom_id, o.mfcentre_id
 , o.mfresource_id, o.std_cost, o.std_lab, o.std_ohd, o.latest_cost, o.latest_lab, o.latest_ohd
 , o.usercompanyid
 , s.item_code||' - '||s.description AS stitem
 , u.uom_name AS volume_uom
 , c.centre
 , r.description AS resource
   FROM mf_operations o
   JOIN st_items s ON o.stitem_id = s.id
   JOIN st_uoms u ON o.volume_uom_id = u.id
   JOIN mf_centres c ON o.mfcentre_id = c.id
   JOIN mf_resources r ON o.mfresource_id = r.id;

--
-- Permissions
--

insert into permissions
(permission, type, title, display, position, parent_id)
select 'production_recording', 'm', 'Production Recording', true, next.position, id
  from permissions
     , (select max(c.position)+1 as position
          from permissions c
          join permissions p on p.id = c.parent_id
                            and p.type='m'
                            and p.permission='manufacturing') as next
 where type='m'
   and permission='manufacturing';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'mfshifts', 'c', 'MF Shifts', true, id, 1
  from permissions
 where type='m'
   and permission='production_recording';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'mfdowntimecodes', 'c', 'MF Downtime Codes', true, id, 2
  from permissions
 where type='m'
   and permission='production_recording';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'mfwastetypes', 'c', 'MF Waste Types', true, id, 3
  from permissions
 where type='m'
   and permission='production_recording';