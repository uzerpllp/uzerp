-- DROP OVERVIEWS
DROP VIEW calendars_overview;
DROP VIEW calendar_events_overview;
DROP VIEW calendar_shares_overview;

-- DROP TABLES
DROP TABLE calendar_event_attendees;
DROP TABLE calendar_events;
DROP TABLE calendar_shares;
DROP TABLE calendars;

-- CREATE TABLES
CREATE TABLE calendars
(
  id bigserial NOT NULL,
  "name" character varying NOT NULL,
  "owner" character varying NOT NULL,
  usercompanyid bigint,
  "type" character varying,
  gcal_calendar_id character varying,
  gcal_magic_cookie character varying,
  colour character varying,
  gcal_url character varying,
  CONSTRAINT calendars_pkey PRIMARY KEY (id)
);

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

CREATE TABLE calendar_events
(
  id bigserial NOT NULL,
  start_time timestamp without time zone NOT NULL,
  end_time timestamp without time zone NOT NULL,
  all_day boolean NOT NULL DEFAULT false,
  title character varying NOT NULL,
  description character varying,
  "location" character varying,
  url character varying,
  status character varying,
  "owner" character varying NOT NULL,
  private boolean NOT NULL DEFAULT true,
  usercompanyid bigint,
  company_id bigint,
  person_id bigint,
  calendar_id bigint,
  CONSTRAINT calendar_events_pkey PRIMARY KEY (id),
  CONSTRAINT calendar_events_calendar_id_fkey FOREIGN KEY (calendar_id)
      REFERENCES calendars (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT calendar_events_company_id_fkey FOREIGN KEY (company_id)
      REFERENCES company (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT calendar_events_person_id_fkey FOREIGN KEY (person_id)
      REFERENCES person (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT calendar_events_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT calendar_events_username_fkey FOREIGN KEY ("owner")
      REFERENCES users (username) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE calendar_event_attendees
(
  id bigserial NOT NULL,
  calendar_event_id bigint,
  person_id bigint NOT NULL,
  reminder boolean NOT NULL DEFAULT false,
  reminder_interval interval DEFAULT '00:15:00'::interval,
  CONSTRAINT calendar_event_attendees_pkey PRIMARY KEY (id),
  CONSTRAINT calendar_event_attendees_calendar_event_id_fkey FOREIGN KEY (calendar_event_id)
      REFERENCES calendar_events (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT calendar_event_attendees_person_id_fkey FOREIGN KEY (person_id)
      REFERENCES person (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

-- CREAT OVERVIEWS

CREATE OR REPLACE VIEW calendar_events_overview AS 
 SELECT ce.id, ce.calendar_id, c.name AS calendar, c.colour AS calendar_colour, ce.start_time, ce.end_time, ce.location, ce.private, ce.all_day, ce.title, ce.owner, ce.end_time - ce.start_time AS difference, ce.usercompanyid
   FROM calendar_events ce
   LEFT JOIN calendars c ON ce.calendar_id = c.id
  ORDER BY ce.start_time;

CREATE OR REPLACE VIEW calendar_shares_overview AS 
 SELECT cs.id, cs.calendar_id, cs.username, c.owner, c.name, c.colour
   FROM calendar_shares cs
   JOIN calendars c ON cs.calendar_id = c.id;

CREATE OR REPLACE VIEW calendars_overview AS 
 SELECT c.id, c.name, c.owner, c.colour, cs.username, c.type, c.gcal_url, c.gcal_calendar_id, c.gcal_magic_cookie, c.usercompanyid
   FROM calendars c
   LEFT JOIN calendar_shares cs ON c.id = cs.calendar_id;