--
-- $Revision: 1.4 $
--

CREATE TABLE ticket_release_versions
(
  id bigserial NOT NULL,
  release_version character varying NOT NULL,
  status character varying NOT NULL,
  summary character varying,
  planned_release_date date,
  actual_release_date date,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  alteredby character varying,
  usercompanyid bigint NOT NULL,
  CONSTRAINT ticket_release_versions_pkey PRIMARY KEY (id),
  CONSTRAINT tickets_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
);

ALTER TABLE tickets OWNER TO "www-data";

ALTER TABLE tickets ADD COLUMN ticket_release_version_id bigint;

ALTER TABLE tickets
  ADD CONSTRAINT tickets_release_version_id_fkey FOREIGN KEY (ticket_release_version_id)
      REFERENCES ticket_release_versions (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

DROP VIEW tickets_overview;

CREATE OR REPLACE VIEW tickets_overview AS 
 SELECT t.id, (t.ticket_queue_id::text || '-'::text) || t.id::text AS number, t.summary
 , t.assigned_to, (tpc.index::text || '-'::text) || tpc.name::text AS client_ticket_priority
 , t.client_ticket_priority_id
 , (tpi.index::text || '-'::text) || tpi.name::text AS internal_ticket_priority
 , t.internal_ticket_priority_id
 , (tsc.index::text || '-'::text) || tsc.name::text AS client_ticket_severity
 , t.client_ticket_severity_id
 , (tsi.index::text || '-'::text) || tsi.name::text AS internal_ticket_severity
 , t.internal_ticket_severity_id, tstc.name AS client_ticket_status
 , tstc.status_code AS client_status_code, t.client_ticket_status_id
 , tsti.name AS internal_ticket_status, tsti.status_code AS internal_status_code
 , t.internal_ticket_status_id, tc.name AS ticket_category, t.ticket_category_id
 , tq.name AS ticket_queue, t.ticket_queue_id, t.ticket_release_version_id, trv.release_version
 , (p.firstname::text || ' '::text) || p.surname::text AS originator_person
 , t.raised_by AS originator_person_id, c.name AS originator_company, t.originator_company_id
 , t.created, t.lastupdated, tq.usercompanyid
 , ( SELECT ticket_responses.created
           FROM ticket_responses
          WHERE ticket_responses.ticket_id = t.id
          ORDER BY ticket_responses.created DESC
         LIMIT 1) AS last_response_time, ( SELECT ticket_responses.owner
           FROM ticket_responses
          WHERE ticket_responses.ticket_id = t.id
          ORDER BY ticket_responses.created DESC
         LIMIT 1) AS last_response_by
   FROM tickets t
   LEFT JOIN ticket_priorities tpc ON t.client_ticket_priority_id = tpc.id
   LEFT JOIN ticket_severities tsc ON t.client_ticket_severity_id = tsc.id
   LEFT JOIN ticket_priorities tpi ON t.internal_ticket_priority_id = tpi.id
   LEFT JOIN ticket_severities tsi ON t.internal_ticket_severity_id = tsi.id
   LEFT JOIN ticket_statuses tstc ON t.client_ticket_status_id = tstc.id
   LEFT JOIN ticket_statuses tsti ON t.internal_ticket_status_id = tsti.id
   LEFT JOIN ticket_categories tc ON t.ticket_category_id = tc.id
   LEFT JOIN ticket_queues tq ON t.ticket_queue_id = tq.id
   LEFT JOIN ticket_release_versions trv ON t.ticket_release_version_id = trv.id
   LEFT JOIN person p ON t.raised_by::text = p.id::text
   LEFT JOIN company c ON t.originator_company_id = c.id;

ALTER TABLE tickets_overview OWNER TO "www-data";

CREATE OR REPLACE VIEW ticket_release_versions_overview
AS
SELECT t.id, trv.release_version, tmv.module, tmv."version"
  FROM tickets t
  JOIN ticket_module_versions tmv ON tmv.ticket_id = t.id
  JOIN ticket_release_versions trv ON t.ticket_release_version_id = trv.id;

ALTER TABLE ticket_release_versions_overview OWNER TO "www-data";

CREATE OR REPLACE VIEW ticket_release_order
AS
SELECT t.*
  FROM ticket_release_versions_overview t
     ,(SELECT release_version, module, count(*)
         FROM ticket_release_versions_overview
        GROUP BY release_version, module
       HAVING count(*) > 1) sum
 WHERE sum.release_version=t.release_version
   AND t.version is not null
   AND t.module = sum.module
 ORDER BY t.module, t.version;

ALTER TABLE ticket_release_order OWNER TO "www-data";

INSERT INTO module_components
(
  "name",
  "type",
  "location",
  module_id
)
SELECT 'ticketreleaseversionscontroller', 'C', "location"||'/controllers/TicketreleaseversionsController.php', id
  FROM modules
 WHERE "name" = 'ticketing';
 
INSERT INTO module_components
(
  "name",
  "type",
  "location",
  module_id
)
SELECT 'ticketreleaseversion', 'M', "location"||'/models/TicketReleaseVersion.php', id
  FROM modules
 WHERE "name" = 'ticketing';

INSERT INTO module_components
(
  "name",
  "type",
  "location",
  module_id
)
SELECT 'ticketreleaseversioncollection', 'M', "location"||'/models/TicketReleaseVersionCollection.php', id
  FROM modules
 WHERE "name" = 'ticketing';

INSERT INTO permissions
	(permission, type, title, display, parent_id, position)
	SELECT 'ticketreleaseversions', 'c', 'Ticket Release Versions', true, id, pos.position
	FROM permissions
	   , (select max(c.position)+1 as position
            from permissions c
               , permissions p
           where p.type='m'
             AND p.permission='ticketing'
             and p.id=c.parent_id) pos
	WHERE type='m'
	AND permission='ticketing';

