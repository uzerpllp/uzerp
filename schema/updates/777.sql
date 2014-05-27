 DROP VIEW mf_wostructuresoverview;

CREATE OR REPLACE VIEW mf_wostructuresoverview AS 
 SELECT ws.id, ws.line_no, ws.qty, ws.uom_id, ws.remarks, ws.waste_pc, ws.work_order_id, ws.ststructure_id, ws.usercompanyid, (sip.item_code::text || ' - '::text) || sip.description::text AS stitem, sip.item_code as stitem_code, (sic.item_code::text || ' - '::text) || sic.description::text AS ststructure, sic.item_code as structure_item_code, sic.comp_class, su.uom_name AS uom, wo.order_qty, wo.made_qty, wo.stitem_id, wo.status, wo.required_by, wo.wo_number
   FROM mf_wo_structures ws
   JOIN mf_workorders wo ON ws.work_order_id = wo.id
   JOIN st_items sip ON wo.stitem_id = sip.id
   JOIN st_items sic ON ws.ststructure_id = sic.id
   JOIN st_uoms su ON ws.uom_id = su.id;
