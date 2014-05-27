ALTER TABLE si_lines ADD COLUMN productline_id INTEGER;
ALTER TABLE si_lines ADD COLUMN move_stock BOOLEAN DEFAULT false;

DROP VIEW si_linesoverview;

CREATE OR REPLACE VIEW si_linesoverview AS 
 SELECT sl.*
 , sh.invoice_date, sh.invoice_number, sh.slmaster_id, soh.order_number, slm.name AS customer
 , (i.item_code::text || ' - '::text) || i.description::text AS stitem, uom.uom_name
   FROM si_lines sl
   JOIN si_header sh ON sh.id = sl.invoice_id
   JOIN slmaster slm ON sh.slmaster_id = slm.id
   LEFT JOIN so_header soh ON sl.sales_order_id = soh.id
   LEFT JOIN st_uoms uom ON sl.stuom_id = uom.id
   LEFT JOIN st_items i ON i.id = sl.stitem_id;