--
-- $Revision: 1.3 $
--

ALTER TABLE data_mapping_rules ADD COLUMN external_format character varying;
ALTER TABLE data_mapping_rules ADD COLUMN data_type character varying;

ALTER TABLE data_definitions ALTER COLUMN external_system_id SET NOT NULL;
ALTER TABLE data_definitions ADD COLUMN transfer_type character varying;
ALTER TABLE data_definitions ADD COLUMN implementation_class character varying;

ALTER TABLE data_definition_details ADD COLUMN default_value character varying;

DROP VIEW data_mappings_overview;

DROP VIEW data_definition_details_overview;

DROP VIEW data_definitions_overview;

CREATE OR REPLACE VIEW data_definitions_overview AS 
 SELECT d.*
 , s.name AS external_system
   FROM data_definitions d
   JOIN external_systems s ON s.id = d.external_system_id;

ALTER TABLE data_definitions_overview OWNER TO "www-data";

CREATE OR REPLACE VIEW data_definition_details_overview AS 
 SELECT d.*
 , p.name AS data_definition, pd.element AS parent, m.internal_type AS map_to_type, m.internal_attribute AS map_to_attribute, r.name AS mapping_rule
   FROM data_definition_details d
   JOIN data_definitions p ON p.id = d.data_definition_id
   LEFT JOIN data_definition_details pd ON pd.id = d.parent_id
   LEFT JOIN data_mappings m ON m.id = d.data_mapping_id
   LEFT JOIN data_mapping_rules r ON r.id = d.data_mapping_rule_id;

ALTER TABLE data_definition_details_overview OWNER TO "www-data";

CREATE OR REPLACE VIEW data_mappings_overview AS 
 SELECT m.id, m.internal_type, m.internal_attribute, m.parent_id, m.name, m.usercompanyid, m.created, m.createdby, m.alteredby, m.lastupdated, p.internal_type AS parent_type, p.internal_attribute AS parent_attribute, d.data_definition_id
   FROM data_mappings m
   LEFT JOIN data_mappings p ON p.id = m.parent_id
   LEFT JOIN data_definition_details_overview d ON m.id = d.data_mapping_id;

ALTER TABLE data_mappings_overview OWNER TO "www-data";

DROP VIEW edi_transactions_log_overview;

CREATE OR REPLACE VIEW edi_transactions_log_overview AS 
 SELECT l.id, l.name, l.status, l.message, l.external_system_id, l.data_definition_id
 , l.usercompanyid, l.created, l.createdby, l.alteredby, l.lastupdated, l.id_value
 , l.identifier_field, l.identifier_value
 , s.name AS external_system, d.name AS data_definition
 , d.working_folder, d.implementation_class
   FROM edi_transactions_log l
   JOIN external_systems s ON s.id = l.external_system_id
   LEFT JOIN data_definitions d ON d.id = l.data_definition_id;

ALTER TABLE edi_transactions_log_overview OWNER TO "www-data";

ALTER TABLE so_lines ADD COLUMN external_data character varying;