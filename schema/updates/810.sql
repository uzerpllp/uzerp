DROP VIEW po_linesoverview;

CREATE OR REPLACE VIEW po_linesoverview AS 
 SELECT ph.due_date, ph.order_date, ph.order_number, ph.plmaster_id, ph.receive_action, pl.id, pl.order_id, pl.line_number, pl.productline_id, pl.stuom_id, ph.type, ph.net_value AS order_value, pl.item_description, pl.order_qty, pl.price, pl.currency_id, cu.currency, pl.rate, pl.net_value, pl.twin_currency_id, pl.twin_rate, pl.twin_net_value, pl.base_net_value, pl.glcentre_id, (glc.cost_centre::text || ' - '::text) || glc.description::text AS glcentre, pl.glaccount_id, (gla.account::text || ' - '::text) || gla.description::text AS glaccount, pl.line_discount, pl.os_qty, pl.revised_qty, pl.del_qty, pl.due_delivery_date, pl.actual_delivery_date, pl.gr_note, pl.status, pl.usercompanyid, pl.stitem_id, pl.tax_rate_id, plm.name AS supplier, (i.item_code::text || ' - '::text) || i.description::text AS stitem, u.uom_name
   FROM po_lines pl
   JOIN gl_centres glc ON glc.id = pl.glcentre_id
   JOIN gl_accounts gla ON gla.id = pl.glaccount_id
   JOIN cumaster cu ON cu.id = pl.currency_id
   JOIN po_header ph ON ph.id = pl.order_id
   JOIN plmaster plm ON ph.plmaster_id = plm.id
   LEFT JOIN st_items i ON i.id = pl.stitem_id
   LEFT JOIN st_uoms u ON u.id = pl.stuom_id;


DROP VIEW po_receivedoverview;

CREATE OR REPLACE VIEW po_receivedoverview AS 
 SELECT pr.*, s.name AS supplier, ph.order_number, (i.item_code::text || ' - '::text) || i.description::text AS stitem, u.uom_name
   FROM po_receivedlines pr
   LEFT JOIN st_items i ON i.id = pr.stitem_id
   JOIN po_header ph ON ph.id = pr.order_id
   JOIN plmaster s ON s.id = pr.plmaster_id
   JOIN st_uoms u ON u.id = pr.stuom_id;