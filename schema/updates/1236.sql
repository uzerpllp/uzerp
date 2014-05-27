CREATE OR REPLACE VIEW pi_linesoverview AS 
 SELECT pl.*
 , ph.invoice_date, ph.invoice_number, ph.plmaster_id, poh.order_number
 , c.name AS supplier, i.item_code
 , (i.item_code::text || ' - '::text) || i.description::text AS stitem
   FROM pi_lines pl
   JOIN pi_header ph ON ph.id = pl.invoice_id
   JOIN plmaster plm ON ph.plmaster_id = plm.id
   JOIN company c ON plm.company_id = c.id
   LEFT JOIN po_header poh ON pl.purchase_order_id = poh.id
   LEFT JOIN st_items i ON i.id = pl.stitem_id;