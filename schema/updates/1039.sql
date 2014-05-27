DROP VIEW si_linesoverview;

CREATE OR REPLACE VIEW si_linesoverview AS 
 SELECT sh.invoice_date, sh.invoice_number, sh.slmaster_id, sl.id, sl.invoice_id, sl.line_number
 , sl.sales_order_id, sl.order_line_id, sl.stitem_id, sl.item_description, sl.sales_qty
 , sl.sales_price, sl.currency_id, sl.rate, sl.gross_value, sl.tax_value, sl.net_value
 , sl.twin_currency_id, sl.twin_rate, sl.twin_gross_value, sl.twin_tax_value, sl.twin_net_value
 , sl.base_gross_value, sl.base_tax_value, sl.base_net_value, sl.glaccount_id, sl.glcentre_id
 , sl.description, sl.usercompanyid, sl.line_discount, sl.tax_rate_id, sl.delivery_note
 , soh.order_number, sl.stuom_id, slm.name AS customer
 , (i.item_code::text || ' - '::text) || i.description::text AS stitem, uom.uom_name
   FROM si_lines sl
   JOIN si_header sh ON sh.id = sl.invoice_id
   JOIN slmaster slm ON sh.slmaster_id = slm.id
   LEFT JOIN so_header soh ON sl.sales_order_id = soh.id
   LEFT JOIN st_uoms uom ON sl.stuom_id = uom.id
   LEFT JOIN st_items i ON i.id = sl.stitem_id;