DROP VIEW so_items;

CREATE OR REPLACE VIEW so_items AS 
 SELECT sl.stitem_id, sl.usercompanyid, u.uom_name, (i.item_code::text || ' - '::text) || i.description::text AS stitem, sum(sl.revised_qty) AS required
   FROM so_lines sl
   JOIN st_items i ON i.id = sl.stitem_id
   JOIN st_uoms u ON sl.stuom_id = u.id
  WHERE sl.stitem_id IS NOT NULL
   AND sl.status in ('N', 'R')
  GROUP BY sl.stitem_id, (i.item_code::text || ' - '::text) || i.description::text, u.uom_name, sl.usercompanyid;

DROP VIEW so_itemorders;

CREATE OR REPLACE VIEW so_itemorders AS 
 SELECT sh.despatch_date, sh.customer, sh.order_number, sh.slmaster_id, sh."type", sh.despatch_action, sl.id, sl.order_id, sl.line_number, sl.productline_id, sl.stuom_id, sl.item_description, sl.order_qty, sl.price, sl.currency_id, sl.rate, sl.net_value, sl.twin_currency_id, sl.twin_rate, sl.twin_net_value, sl.base_net_value, sl.glaccount_id, sl.glcentre_id, sl.line_discount, sl.os_qty, sl.revised_qty, sl.del_qty, sl.due_delivery_date, sl.due_despatch_date, sl.actual_despatch_date, sl.delivery_note, sl.status, sl.usercompanyid, sl.stitem_id, (i.item_code::text || ' - '::text) || i.description::text AS stitem, sl.revised_qty AS required, u.uom_name AS stuom
   FROM so_lines sl
   JOIN st_items i ON i.id = sl.stitem_id
   JOIN st_uoms u ON u.id = sl.stuom_id
   JOIN so_headeroverview sh ON sh.id = sl.order_id
  WHERE sl.stitem_id IS NOT NULL
   AND sl.status in ('N', 'R');

DROP VIEW so_itemdates;

CREATE OR REPLACE VIEW so_itemdates AS 
 SELECT sh.due_date, sl.stitem_id, sl.usercompanyid, (i.item_code::text || ' - '::text) || i.description::text AS stitem, sum(sl.revised_qty) AS required
   FROM so_lines sl
   JOIN st_items i ON i.id = sl.stitem_id
   JOIN so_header sh ON sh.id = sl.order_id
  WHERE sl.stitem_id IS NOT NULL
   AND sl.status in ('N', 'R')
  GROUP BY sh.due_date, sl.stitem_id, (i.item_code::text || ' - '::text) || i.description::text, sl.usercompanyid;
