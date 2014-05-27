DROP VIEW tickets_overview;

ALTER TABLE tickets DROP CONSTRAINT tickets_origintor_person_id_fkey;

ALTER TABLE tickets
ALTER COLUMN originator_person_id TYPE varchar;

UPDATE tickets
   SET originator_person_id = (SELECT username
                                 FROM users
                                WHERE users.person_id = tickets.originator_person_id);

ALTER TABLE tickets
  ADD CONSTRAINT tickets_origintor_person_id_fkey FOREIGN KEY (originator_person_id)
      REFERENCES users (username) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

CREATE OR REPLACE VIEW tickets_overview AS 
 SELECT t.id, (t.ticket_queue_id::text || '-'::text) || t.id::text AS number, t.summary, t.assigned_to, (tpc."index"::text || '-'::text) || tpc.name::text AS client_ticket_priority, t.client_ticket_priority_id, (tpi."index"::text || '-'::text) || tpi.name::text AS internal_ticket_priority, t.internal_ticket_priority_id, (tsc."index"::text || '-'::text) || tsc.name::text AS client_ticket_severity, t.client_ticket_severity_id, (tsi."index"::text || '-'::text) || tsi.name::text AS internal_ticket_severity, t.internal_ticket_severity_id, tstc.name AS client_ticket_status, tstc.status_code AS client_status_code, t.client_ticket_status_id, tsti.name AS internal_ticket_status, tsti.status_code AS internal_status_code, t.internal_ticket_status_id, tc.name AS ticket_category, t.ticket_category_id, tq.name AS ticket_queue, t.ticket_queue_id, (p.firstname::text || ' '::text) || p.surname::text AS originator_person, t.originator_person_id, c.name AS originator_company, t.originator_company_id, to_char(t.created, 'IYYY-MM-DD HH24:MI:SS'::text) AS created, to_char(t.lastupdated, 'IYYY-MM-DD HH24:MI:SS'::text) AS lastupdated, tq.usercompanyid, ( SELECT ticket_responses.created
           FROM ticket_responses
          WHERE ticket_responses.ticket_id = t.id
          ORDER BY ticket_responses.created DESC
         LIMIT 1) AS last_response_time, ( SELECT ticket_responses."owner"
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
   LEFT JOIN person p ON t.originator_person_id = p.id
   LEFT JOIN company c ON t.originator_company_id = c.id;