DROP VIEW mf_workordersoverview;

CREATE OR REPLACE VIEW mf_workordersoverview AS 
 SELECT w.id, w.wo_number, w.order_qty, w.data_sheet_id, w.made_qty, w.required_by, w.project_id, w.text1, w.text2, w.text3, w.order_no, w.order_line, w.status, w.stitem_id, w.usercompanyid, s.description AS stitem, s.item_code
   FROM mf_workorders w
   JOIN st_items s ON w.stitem_id = s.id;
