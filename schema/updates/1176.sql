CREATE INDEX so_product_lines_description
  ON so_product_lines
  USING btree
  (description);

ALTER TABLE so_lines ADD COLUMN description text;

UPDATE so_lines
   SET description = item_description;

DROP VIEW so_linesoverview;

CREATE OR REPLACE VIEW so_linesoverview AS 
 SELECT sl.*, sh.due_date, sh.order_date, sh.order_number
, sh.slmaster_id, sh.type, slm.name AS customer
, (i.item_code::text || ' - '::text) || i.description::text AS stitem, uom.uom_name
   FROM so_lines sl
   JOIN so_header sh ON sh.id = sl.order_id
   JOIN slmaster slm ON sh.slmaster_id = slm.id
   LEFT JOIN st_uoms uom ON sl.stuom_id = uom.id
   LEFT JOIN st_items i ON i.id = sl.stitem_id;

ALTER TABLE po_lines ADD COLUMN description text;

UPDATE po_lines
   SET description = item_description;

DROP VIEW po_linesoverview;

CREATE OR REPLACE VIEW po_linesoverview AS 
 SELECT pl.*, ph.due_date, ph.order_date, ph.order_number, ph.plmaster_id
, ph.receive_action, ph.type, ph.net_value AS order_value, cu.currency
, (glc.cost_centre::text || ' - '::text) || glc.description::text AS glcentre
, (gla.account::text || ' - '::text) || gla.description::text AS glaccount, ph.status AS order_status
, plm.name AS supplier, (i.item_code::text || ' - '::text) || i.description::text AS stitem, u.uom_name
   FROM po_lines pl
   JOIN gl_centres glc ON glc.id = pl.glcentre_id
   JOIN gl_accounts gla ON gla.id = pl.glaccount_id
   JOIN cumaster cu ON cu.id = pl.currency_id
   JOIN po_header ph ON ph.id = pl.order_id
   JOIN plmaster plm ON ph.plmaster_id = plm.id
   LEFT JOIN st_items i ON i.id = pl.stitem_id
   LEFT JOIN st_uoms u ON u.id = pl.stuom_id;

DROP VIEW si_linesoverview;

CREATE OR REPLACE VIEW si_linesoverview AS 
 SELECT sl.id, sl.invoice_id, sl.line_number, sl.sales_order_id, sl.order_line_id, sl.stitem_id
 , sl.item_description, sl.sales_qty, sl.sales_price, sl.currency_id, sl.rate, sl.gross_value
 , sl.tax_value, sl.net_value, sl.twin_currency_id, sl.twin_rate, sl.twin_gross_value, sl.twin_tax_value
 , sl.twin_net_value, sl.base_gross_value, sl.base_tax_value, sl.base_net_value, sl.glaccount_id
 , sl.glcentre_id, sl.description, sl.usercompanyid, sl.line_discount, sl.tax_rate_id, sl.delivery_note
 , sl.stuom_id, sl.created, sl.createdby, sl.alteredby, sl.lastupdated, sl.productline_id, sl.move_stock
 , sh.invoice_date, sh.invoice_number, sh.slmaster_id, soh.order_number, slm.name AS customer
 , i.item_code, (i.item_code::text || ' - '::text) || i.description::text AS stitem, uom.uom_name
   FROM si_lines sl
   JOIN si_header sh ON sh.id = sl.invoice_id
   JOIN slmaster slm ON sh.slmaster_id = slm.id
   LEFT JOIN so_header soh ON sl.sales_order_id = soh.id
   LEFT JOIN st_uoms uom ON sl.stuom_id = uom.id
   LEFT JOIN st_items i ON i.id = sl.stitem_id;