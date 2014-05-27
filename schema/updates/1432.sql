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
 , (gla.account::text || ' - '::text) || gla.description::text AS glaccount
 , (glc.cost_centre::text || ' - '::text) || glc.description::text AS glcentre
 , tax.description AS taxrate
 , (i.item_code::text || ' - '::text) || i.description::text AS stitem
 , i.item_code, uom.uom_name
   FROM so_lines sl
   JOIN so_header sh ON sh.id = sl.order_id
   JOIN slmaster slm ON sh.slmaster_id = slm.id
   JOIN company c ON slm.company_id = c.id
   JOIN taxrates tax ON sl.tax_rate_id = tax.id
   JOIN gl_accounts gla ON sl.glaccount_id = gla.id
   JOIN gl_centres glc ON sl.glcentre_id = glc.id
   LEFT JOIN st_uoms uom ON sl.stuom_id = uom.id
   LEFT JOIN st_items i ON i.id = sl.stitem_id;

ALTER TABLE so_linesoverview OWNER TO "www-data";

DROP VIEW po_linesoverview;

CREATE OR REPLACE VIEW po_linesoverview AS 
 SELECT pl.id, pl.order_id, pl.line_number, pl.productline_id, pl.stuom_id, pl.item_description
 , pl.order_qty, pl.price, pl.currency_id, pl.rate, pl.net_value, pl.twin_currency_id
 , pl.twin_rate, pl.twin_net_value, pl.base_net_value, pl.glaccount_id, pl.glcentre_id
 , pl.line_discount, pl.os_qty, pl.revised_qty, pl.del_qty, pl.due_delivery_date
 , pl.actual_delivery_date, pl.gr_note, pl.status, pl.usercompanyid, pl.stitem_id
 , pl.tax_rate_id, pl.created, pl.createdby, pl.alteredby, pl.lastupdated, pl.description
 , ph.due_date, ph.order_date, ph.order_number, ph.plmaster_id, ph.receive_action, ph.type
 , ph.net_value AS order_value, cu.currency
 , (glc.cost_centre::text || ' - '::text) || glc.description::text AS glcentre
 , (gla.account::text || ' - '::text) || gla.description::text AS glaccount, tax.description AS taxrate
 , ph.status AS order_status, plm.payee_name, c.name AS supplier
 , (i.item_code::text || ' - '::text) || i.description::text AS stitem, u.uom_name
   FROM po_lines pl
   JOIN gl_centres glc ON glc.id = pl.glcentre_id
   JOIN gl_accounts gla ON gla.id = pl.glaccount_id
   JOIN taxrates tax ON tax.id = pl.tax_rate_id
   JOIN cumaster cu ON cu.id = pl.currency_id
   JOIN po_header ph ON ph.id = pl.order_id
   JOIN plmaster plm ON ph.plmaster_id = plm.id
   JOIN company c ON plm.company_id = c.id
   LEFT JOIN st_items i ON i.id = pl.stitem_id
   LEFT JOIN st_uoms u ON u.id = pl.stuom_id;

ALTER TABLE po_linesoverview OWNER TO "www-data";
	