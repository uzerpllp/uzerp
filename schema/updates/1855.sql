--
-- $Revision: 1.3 $
--

-- Table: audit_lines

ALTER TABLE audit_lines ADD COLUMN username character varying;
ALTER TABLE audit_lines ADD COLUMN remote_address character varying;
ALTER TABLE audit_lines ADD COLUMN user_agent character varying;

-- Table: so_despatchevents

ALTER TABLE so_despatchevents ADD COLUMN created timestamp without time zone;
ALTER TABLE so_despatchevents ALTER COLUMN created SET DEFAULT now();
ALTER TABLE so_despatchevents ADD COLUMN createdby character varying;
ALTER TABLE so_despatchevents ADD COLUMN alteredby character varying;
ALTER TABLE so_despatchevents ADD COLUMN lastupdated timestamp without time zone;
ALTER TABLE so_despatchevents ALTER COLUMN lastupdated SET DEFAULT now();
