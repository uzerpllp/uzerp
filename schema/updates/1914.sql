--
-- $Revision: 1.1 $
--

-- View: eng_work_schedules_overview

DROP VIEW eng_work_schedules_overview;

CREATE OR REPLACE VIEW eng_work_schedules_overview AS 
 SELECT ws.*
 , mc.work_centre::text || mc.centre::text AS centre
 , mc.mfdept_id, md.dept_code::text || md.dept::text AS dept
 , dc.downtime_code
   FROM eng_work_schedules ws
   JOIN mf_centres mc ON mc.id = ws.centre_id
   JOIN mf_depts md ON md.id = mc.mfdept_id
   LEFT JOIN mf_downtime_codes dc ON dc.id = ws.mf_downtime_code_id;

ALTER TABLE eng_work_schedules_overview OWNER TO "www-data";

-- View: eng_resources_overview

DROP VIEW eng_resources_overview;

CREATE OR REPLACE VIEW eng_resources_overview AS 
 SELECT er.*
 , ws.job_no, mr.resource_code || ' - '|| mr.description as resource, mr.resource_rate, (per.firstname::text || ' '::text) || per.surname::text AS person
   FROM eng_resources er
   JOIN eng_work_schedules ws ON ws.id = er.work_schedule_id
   JOIN mf_resources mr ON mr.id = er.resource_id
   LEFT JOIN person per ON er.person_id = per.id;

ALTER TABLE eng_resources_overview OWNER TO "www-data";
