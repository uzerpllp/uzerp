--
-- $Revision: 1.5 $
--

ALTER TABLE tickets ADD COLUMN change_log character varying;
ALTER TABLE tickets RENAME originator_person_id TO raised_by;
ALTER TABLE tickets ADD COLUMN originator_person_id bigint;

ALTER TABLE tickets
  ADD CONSTRAINT tickets_origintor_raised_by_fkey FOREIGN KEY (raised_by)
      REFERENCES users (username) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE tickets DROP CONSTRAINT tickets_origintor_person_id_fkey;

ALTER TABLE tickets
  ADD CONSTRAINT tickets_origintor_person_id_fkey FOREIGN KEY (originator_person_id)
      REFERENCES person (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

UPDATE tickets
   SET originator_person_id = (SELECT person_id
                                 FROM users
                                WHERE users.username = tickets.raised_by)
      ,originator_email_address = (SELECT email
                                     FROM users
                                    WHERE users.username = tickets.raised_by);

ALTER TABLE ticket_queues DROP COLUMN default_queue;
ALTER TABLE ticket_queues ADD COLUMN queue_owner character varying;

ALTER TABLE ticket_queues
  ADD CONSTRAINT ticket_queues_owner_fkey FOREIGN KEY (queue_owner)
      REFERENCES users (username) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE SET NULL;

ALTER TABLE ticket_configurations ADD COLUMN company_id bigint;
ALTER TABLE ticket_configurations rename client_ticket_status_default to client_ticket_status_id;
ALTER TABLE ticket_configurations rename client_ticket_priority_default to client_ticket_priority_id;
ALTER TABLE ticket_configurations rename client_ticket_severity_default to client_ticket_severity_id;
ALTER TABLE ticket_configurations rename internal_ticket_status_default to internal_ticket_status_id;
ALTER TABLE ticket_configurations rename internal_ticket_priority_default to internal_ticket_priority_id;
ALTER TABLE ticket_configurations rename internal_ticket_severity_default to internal_ticket_severity_id;
ALTER TABLE ticket_configurations rename ticket_queue_default to ticket_queue_id;
ALTER TABLE ticket_configurations rename ticket_category_default to ticket_category_id;

ALTER TABLE ticket_configurations
  ADD CONSTRAINT ticket_configurations_company_id_fkey FOREIGN KEY (company_id)
      REFERENCES company (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE ticket_configurations
  ADD CONSTRAINT ticket_configurations_ticket_category_default_fkey FOREIGN KEY (ticket_category_id)
      REFERENCES ticket_categories (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

CREATE OR REPLACE VIEW ticket_configurations_overview AS 
 SELECT tc.*, 
        case
           when tc.company_id = sc.company_id
           then 'Default'
        else
           c.name
        end as company,
        cpr.name AS client_ticket_priority,
        csev.name AS client_ticket_severity,
        cstat.name AS client_ticket_status,
        ipr.name AS internal_ticket_priority,
        isev.name AS internal_ticket_severity,
        istat.name AS internal_ticket_status,
        tcat.name AS ticket_category,
        tq.name AS ticket_queue
   FROM ticket_configurations tc
   JOIN system_companies sc ON sc.id = tc.usercompanyid
   JOIN ticket_priorities cpr ON cpr.id = tc.client_ticket_priority_id
   JOIN ticket_severities csev ON csev.id = tc.client_ticket_severity_id
   JOIN ticket_statuses cstat ON cstat.id = tc.client_ticket_status_id
   JOIN ticket_priorities ipr ON ipr.id = tc.internal_ticket_priority_id
   JOIN ticket_severities isev ON isev.id = tc.internal_ticket_severity_id
   JOIN ticket_statuses istat ON istat.id= tc.internal_ticket_status_id
   JOIN ticket_queues tq ON tq.id = tc.ticket_queue_id
   LEFT JOIN ticket_categories tcat ON tcat.id = tc.ticket_category_id
   LEFT JOIN company c ON c.id = tc.company_id;

ALTER TABLE ticket_configurations_overview OWNER TO "www-data";