--
-- $Revision: 1.1 $
--

DROP VIEW mf_shifts_overview;

CREATE OR REPLACE VIEW mf_shifts_overview AS 
 SELECT s.id, s.id as shift_ref, s.shift, s.shift_date, s.comment, s.mf_dept_id, s.mf_centre_id, s.usercompanyid, s.created, s.createdby, s.alteredby, s.lastupdated, (d.dept_code::text || ' - '::text) || d.dept::text AS mf_dept, (c.work_centre::text || ' - '::text) || c.centre::text AS mf_centre
   FROM mf_shifts s
   JOIN mf_depts d ON d.id = s.mf_dept_id
   JOIN mf_centres c ON c.id = s.mf_centre_id;

ALTER TABLE mf_shifts_overview OWNER TO "www-data";

ALTER TABLE mf_waste_types ADD COLUMN cost numeric;

DROP VIEW mf_shift_waste_overview;

DROP VIEW mf_waste_types_overview;

CREATE OR REPLACE VIEW mf_waste_types_overview AS 
 SELECT w.*, u.uom_name
   FROM mf_waste_types w
   JOIN st_uoms u ON u.id = w.uom_id;

ALTER TABLE mf_waste_types_overview OWNER TO "www-data";

CREATE OR REPLACE VIEW mf_shift_waste_overview AS 
 SELECT sw.id, sw.mf_shift_outputs_id, sw.mf_centre_waste_type_id, sw.qty, sw.usercompanyid, sw.created, sw.createdby, sw.alteredby, sw.lastupdated, s.shift, wt.description AS waste_type, wt.uom_name
   FROM mf_shift_waste sw
   JOIN mf_shift_outputs so ON so.id = sw.mf_shift_outputs_id
   JOIN mf_shifts s ON s.id = so.mf_shift_id
   JOIN mf_centre_waste_types cwt ON cwt.id = sw.mf_centre_waste_type_id
   JOIN mf_waste_types_overview wt ON wt.id = cwt.mf_waste_type_id;

ALTER TABLE mf_shift_waste_overview OWNER TO "www-data";
