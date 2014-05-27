--
-- $Revision: 1.2 $
--

DROP VIEW so_linesoverview;

CREATE OR REPLACE VIEW so_linesoverview AS 
 SELECT sl.id, sl.order_id, sl.line_number, sl.productline_id, sl.stuom_id, sl.item_description
 , sl.order_qty, sl.price, sl.currency_id, sl.rate, sl.net_value, sl.twin_currency_id
 , sl.twin_rate, sl.twin_net_value, sl.base_net_value, sl.glaccount_id, sl.glcentre_id
 , sl.line_discount, sl.os_qty, sl.revised_qty, sl.del_qty, sl.due_delivery_date
 , sl.due_despatch_date, sl.actual_despatch_date, sl.delivery_note, sl.status, sl.usercompanyid
 , sl.stitem_id, sl.tax_rate_id, sl.created, sl.createdby, sl.alteredby, sl.lastupdated
 , sl.line_value, sl.line_tradedisc_percentage, sl.line_qtydisc_percentage, sl.description
 , sh.due_date, sh.order_date, sh.order_number, sh.slmaster_id, sh.type, c.name AS customer
 , (i.item_code::text || ' - '::text) || i.description::text AS stitem, i.item_code, uom.uom_name
   FROM so_lines sl
   JOIN so_header sh ON sh.id = sl.order_id
   JOIN slmaster slm ON sh.slmaster_id = slm.id
   JOIN company c ON slm.company_id = c.id
   LEFT JOIN st_uoms uom ON sl.stuom_id = uom.id
   LEFT JOIN st_items i ON i.id = sl.stitem_id;
