DROP VIEW mf_workordersoverview;

ALTER TABLE mf_workorders ADD COLUMN wo_number integer;

ALTER TABLE mf_workorders RENAME job TO project_id ;

ALTER TABLE mf_workorders ALTER COLUMN project_id TYPE int8 USING cast(project_id AS int8); 

ALTER TABLE mf_workorders
  ADD CONSTRAINT mf_workorders_project_id_fkey FOREIGN KEY (project_id)
      REFERENCES projects (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

CREATE TABLE mf_data_sheets
(
  id serial NOT NULL,
  "name" character varying NOT NULL,
  category character varying NOT NULL,
  class_name character varying NOT NULL,
  description character varying,
  usercompanyid bigint NOT NULL,
  CONSTRAINT mf_data_sheets_pkey PRIMARY KEY (id),
  CONSTRAINT mf_data_sheets_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT mf_data_sheets_name_key UNIQUE (name, class_name)
);

ALTER TABLE mf_workorders ADD COLUMN data_sheet_id integer;

CREATE OR REPLACE VIEW mf_workordersoverview AS 
 SELECT w.id, w.wo_number, w.order_qty, w.made_qty, w.required_by, w.project_id, w.text1, w.text2, w.text3, w.order_no, w.order_line, w.status, w.stitem_id, w.usercompanyid, w.data_sheet_id, s.description AS stitem
   FROM mf_workorders w
   JOIN st_items s ON w.stitem_id = s.id;
