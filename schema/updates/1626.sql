--
-- $Revision: 1.2 $
--

-- TABLES AND VIEWS

--DROP VIEW crm_calendar_events_overview;
--DROP TABLE crm_calendar_events;
--DROP TABLE crm_calendars;

CREATE TABLE crm_calendars
(
 id bigserial NOT NULL,
 title character varying NOT NULL,
 colour character varying NOT NULL,
 created timestamp without time zone DEFAULT now(),
 createdby character varying,
 alteredby character varying,
 lastupdated timestamp without time zone DEFAULT now(),
 CONSTRAINT crm_calendars_pkey PRIMARY KEY (id )
);

CREATE TABLE crm_calendar_events
(
 id bigserial NOT NULL,
 title character varying NOT NULL,
 crm_calendar_id bigint NOT NULL,
 start_date timestamp without time zone NOT NULL,
 end_date timestamp without time zone NOT NULL,
 created timestamp without time zone DEFAULT now(),
 createdby character varying,
 alteredby character varying,
 lastupdated timestamp without time zone DEFAULT now(),
 CONSTRAINT crm_calendar_events_pkey PRIMARY KEY (id ),
 CONSTRAINT crm_calendar_events_crm_calendar_id_fkey FOREIGN KEY (crm_calendar_id)
     REFERENCES crm_calendars (id) MATCH SIMPLE
     ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE OR REPLACE VIEW crm_calendar_events_overview AS
 SELECT e.id, e.title, e.start_date, e.end_date, e.crm_calendar_id, c.colour
  FROM crm_calendar_events e
  INNER JOIN crm_calendars c ON c.id = e.crm_calendar_id;


-- MODULES

INSERT INTO module_components
 ("name", "type", location, module_id)
 SELECT 'crmcalendarscontroller', 'C', m.location||'/controllers/CrmcalendarsController.php', id
  FROM modules m
 WHERE m.name = 'crm';

INSERT INTO module_components
 ("name", "type", location, module_id)
 SELECT 'crmcalendareventscontroller', 'C', m.location||'/controllers/CrmcalendareventsController.php', id
  FROM modules m
 WHERE m.name = 'crm';

INSERT INTO module_components
 ("name", "type", location, module_id)
 SELECT 'crmcalendar', 'M', m.location||'/models/CRMCalendar.php', id
  FROM modules m
 WHERE m.name = 'crm';

INSERT INTO module_components
 ("name", "type", location, module_id)
 SELECT 'crmcalendarcollection', 'M', m.location||'/models/CRMCalendarCollection.php', id
  FROM modules m
 WHERE m.name = 'crm';

INSERT INTO module_components
 ("name", "type", location, module_id)
 SELECT 'crmcalendarevent', 'M', m.location||'/models/CRMCalendarEvent.php', id
  FROM modules m
 WHERE m.name = 'crm';


INSERT INTO module_components
 ("name", "type", location, module_id)
 SELECT 'crmcalendareventcollection', 'M', m.location||'/models/CRMCalendarEventCollection.php', id
  FROM modules m
 WHERE m.name = 'crm';


-- PERMISSIONS

INSERT INTO permissions
  (permission, type, title, display, parent_id, position)
  SELECT 'crmcalendars', 'c', 'CRM Calendar', true, id, pos.position
  FROM permissions
     , (select max(c.position)+1 as position
          from permissions c
             , permissions p
         where p.type='m'
           AND p.permission='crm'
           and p.id=c.parent_id) pos
  WHERE type='m'
  AND permission='crm';

INSERT INTO permissions
 (permission, type, title, display, parent_id, position)
 SELECT 'index', 'a', 'Calendar', true, id, 1
 FROM permissions
   , (select max(c.position)+1 as position
        from permissions c
           , permissions p
       where p.type='c'
         AND p.permission='crmcalendars'
         and p.id=c.parent_id) pos
 WHERE type='c'
 AND permission='crmcalendars';

INSERT INTO permissions
  (permission, type, title, display, parent_id, position)
  SELECT 'view_calendars', 'a', 'Manage Calendars', true, id, 2
  FROM permissions
     , (select max(c.position)+1 as position
          from permissions c
             , permissions p
         where p.type='c'
           AND p.permission='crmcalendars'
           and p.id=c.parent_id) pos
  WHERE type='c'
  AND permission='crmcalendars';

INSERT INTO permissions
 (permission, type, title, display, parent_id, position)
 SELECT 'crmcalendarevents', 'c', 'CRM Calendar Events', false, id, pos.position
 FROM permissions
   , (select max(c.position)+1 as position
         from permissions c
            , permissions p
        where p.type='m'
          AND p.permission='crm'
          and p.id=c.parent_id) pos
 WHERE type='m'
 AND permission='crm';

INSERT INTO permissions
  (permission, type, title, display, parent_id, position)
  SELECT 'index', 'a', 'View Events', false, id, 1
    FROM permissions
    WHERE type='c'
    AND permission='crmcalendarevents';

INSERT INTO permissions
  (permission, type, title, display, parent_id, position)
  SELECT '_new', 'a', 'New Event', false, id, 2
    FROM permissions
    WHERE type='c'
    AND permission='crmcalendarevents';

INSERT INTO permissions
  (permission, type, title, display, parent_id, position)
  SELECT 'edit', 'a', 'Edit Event', false, id, 3
    FROM permissions
    WHERE type='c'
    AND permission='crmcalendarevents';

INSERT INTO permissions
  (permission, type, title, display, parent_id, position)
  SELECT 'delete', 'a', 'Delete Event', false, id, 4
    FROM permissions
    WHERE type='c'
    AND permission='crmcalendarevents';