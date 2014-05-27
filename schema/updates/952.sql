ALTER TABLE tasks
   ALTER COLUMN project_id DROP NOT NULL;

CREATE TABLE calendars
(
  id bigserial NOT NULL,
  "name" character varying NOT NULL,
  "owner" character varying NOT NULL,
  usercompanyid bigint,
  CONSTRAINT calendars_pkey PRIMARY KEY (id)
);

ALTER TABLE calendar_events
   ADD COLUMN calendar_id bigint;
ALTER TABLE calendar_events
   ALTER COLUMN calendar_id SET NOT NULL;
ALTER TABLE calendar_events ADD CONSTRAINT calendar_events_calendar_id_fkey FOREIGN KEY (calendar_id) REFERENCES calendars (id)
   ON UPDATE CASCADE ON DELETE CASCADE;


DROP TABLE calendar_shares;
CREATE TABLE calendar_shares
(
  id bigserial NOT NULL,
  calendar_id bigint,
  username character varying NOT NULL,
  CONSTRAINT calendar_shares_pkey PRIMARY KEY (id),
  CONSTRAINT calendar_shares_calendar_id_fkey FOREIGN KEY (calendar_id)
      REFERENCES calendars (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);



-- OVERVIEWS

DROP VIEW tasksoverview;
CREATE OR REPLACE VIEW tasksoverview AS 
 SELECT t.id, t.name, t.budget, t.priority_id, t.progress, t.start_date, t.end_date, t.end_date - t.start_date AS difference, t.duration, t.milestone, t.project_id, t.parent_id, t.description, t.owner, t.alteredby, t.created, t.lastupdated, t.deliverable, t.equipment_id, pr.name AS project, pri.name AS priority, pt.name AS parent, pe.name AS equipment
   FROM tasks t
   LEFT JOIN projects pr ON t.project_id = pr.id
   LEFT JOIN tasks pt ON t.parent_id = pt.id
   LEFT JOIN task_priorities pri ON t.priority_id = pri.id
   LEFT JOIN project_equipment pe ON t.equipment_id = pe.id;


CREATE OR REPLACE VIEW calendar_events_overview AS 
 SELECT ce.id, ce.calendar_id, c.name, ce.start_time, ce.end_time, ce.location, ce.private, ce.all_day, ce.summary, ce.owner, ce.end_time - ce.start_time AS difference, ce.usercompanyid
   FROM calendar_events ce
   LEFT JOIN calendars c ON ce.calendar_id = c.id
  ORDER BY ce.start_time;


CREATE OR REPLACE VIEW calendar_shares_overview AS 
 SELECT cs.id, cs.calendar_id, cs.username, c.owner, c.name
   FROM calendar_shares cs
   JOIN calendars c ON cs.calendar_id = c.id;
