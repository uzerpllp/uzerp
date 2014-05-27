DROP VIEW mf_workordersoverview;

CREATE OR REPLACE VIEW mf_workordersoverview AS 
 SELECT w.*
 , s.description AS stitem, s.item_code, s.type_code_id
   FROM mf_workorders w
   JOIN st_items s ON w.stitem_id = s.id;