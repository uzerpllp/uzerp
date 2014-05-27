--
-- $Revision: 1.3 $
--

ALTER TABLE data_definitions ADD COLUMN external_identifier_field character varying;
ALTER TABLE data_definitions ADD COLUMN field_separator character varying default ',';
ALTER TABLE data_definitions ADD COLUMN text_delimiter character varying default '"';
ALTER TABLE data_definitions ALTER COLUMN direction SET NOT NULL;
ALTER TABLE data_definitions ADD COLUMN remote_archive_folder character varying;
ALTER TABLE data_definitions RENAME COLUMN archive_folder TO local_archive_folder;
ALTER TABLE data_definitions ADD COLUMN abort_action character varying;
ALTER TABLE data_definitions ADD COLUMN duplicates_action character varying;

ALTER TABLE edi_transactions_log ADD COLUMN external_id character varying;
ALTER TABLE edi_transactions_log RENAME COLUMN id_value TO internal_id;
ALTER TABLE edi_transactions_log RENAME COLUMN identifier_field TO internal_identifier_field;
ALTER TABLE edi_transactions_log RENAME COLUMN identifier_value TO internal_identifier_value;
ALTER TABLE edi_transactions_log ADD COLUMN action character varying;
ALTER TABLE edi_transactions_log ALTER COLUMN message DROP NOT NULL;

UPDATE edi_transactions_log
   SET action = (SELECT CASE direction WHEN 'IN' THEN 'I'
                                       WHEN 'OUT' THEN 'S'
                        END
                   FROM data_definitions
                  WHERE id = data_definition_id);

ALTER TABLE edi_transactions_log ALTER COLUMN action SET NOT NULL;

DROP VIEW edi_transactions_log_overview;

CREATE OR REPLACE VIEW edi_transactions_log_overview AS 
 SELECT l.*
 , s.name AS external_system
 , d.name AS data_definition, d.working_folder, d.implementation_class
   FROM edi_transactions_log l
   JOIN external_systems s ON s.id = l.external_system_id
   LEFT JOIN data_definitions d ON d.id = l.data_definition_id;

ALTER TABLE edi_transactions_log_overview OWNER TO "www-data";

CREATE TABLE edi_transactions_log_history
(
  id bigint,
  "name" character varying,
  status character varying,
  message character varying,
  external_system_id bigint,
  data_definition_id bigint,
  usercompanyid bigint,
  created timestamp without time zone,
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone,
  id_value bigint,
  identifier_field character varying,
  identifier_value character varying,
  "action" character varying,
  "current" boolean
);

ALTER TABLE edi_transactions_log_history OWNER TO "www-data";

INSERT INTO module_components
  ("name", "type", location, module_id)
  SELECT 'ediinterface', 'M', m.location||'/models/EdiInterface.php', id
    FROM modules m
   WHERE m.name = 'edi';
 