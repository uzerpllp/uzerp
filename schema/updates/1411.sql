--
-- $Revision: 1.2 $
--


ALTER TABLE reports ADD COLUMN options character varying;

ALTER TABLE reports DROP COLUMN fields;
ALTER TABLE reports DROP COLUMN measure_fields;
ALTER TABLE reports DROP COLUMN aggregate_fields;
ALTER TABLE reports DROP COLUMN aggregate_methods;
ALTER TABLE reports DROP COLUMN search_fields;
ALTER TABLE reports DROP COLUMN search_types;
ALTER TABLE reports DROP COLUMN search_defaults;
ALTER TABLE reports DROP COLUMN display_order;
ALTER TABLE reports DROP COLUMN field_formatting;