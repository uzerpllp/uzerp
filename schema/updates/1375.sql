-- Remove previous injector classes
DELETE FROM injector_classes WHERE class_name='CSVObject';
DELETE FROM injector_classes WHERE class_name='PDFObject';
DELETE FROM injector_classes WHERE class_name='TextObject';
DELETE FROM injector_classes WHERE class_name='XMLObject';

-- Add PSQL_CSV
INSERT INTO injector_classes
  ("name", category, class_name, description, usercompanyid)
  SELECT 'CSV'
       , 'RP'
       , 'PSQL_CSV'
       , 'PostgreSQL implementation of CSV output'
       , id
   FROM system_companies;