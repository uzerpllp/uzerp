ALTER TABLE po_receivedlines ADD COLUMN currency varchar;

DROP VIEW po_receivedoverview;

CREATE OR REPLACE VIEW po_receivedoverview AS 
 SELECT pr.*
, s.name AS supplier, ph.order_number, (i.item_code::text || ' - '::text) || i.description::text AS stitem, u.uom_name
   FROM po_receivedlines pr
   LEFT JOIN st_items i ON i.id = pr.stitem_id
   JOIN po_header ph ON ph.id = pr.order_id
   JOIN plmaster s ON s.id = pr.plmaster_id
   JOIN st_uoms u ON u.id = pr.stuom_id;
