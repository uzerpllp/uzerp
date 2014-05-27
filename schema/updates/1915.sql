--
-- $Revision: 1.2 $
--

-- View: po_receivedoverview

DROP VIEW po_receivedoverview;

CREATE OR REPLACE VIEW po_receivedoverview AS 
 SELECT pr.id, pr.gr_number, pr.order_id, pr.plmaster_id, pr.received_date, pr.received_qty, pr.orderline_id
 , pr.productline_id, pr.stuom_id, pr.stitem_id, pr.status, pr.usercompanyid, pr.item_description, pr.delivery_note
 , pr.net_value, pr.tax_rate_id, pr.invoice_number, pr.invoice_id, pr.received_by, pr.currency, pr.created, pr.createdby
 , pr.alteredby, pr.lastupdated
 , s.payee_name, c.name AS supplier, ph.order_number, pl.glaccount_id, pl.glcentre_id, pl.currency_id, pl.rate
 , (i.item_code::text || ' - '::text) || i.description::text AS stitem, i.qty_decimals, u.uom_name
   FROM po_receivedlines pr
   LEFT JOIN st_items i ON i.id = pr.stitem_id
   JOIN po_header ph ON ph.id = pr.order_id
   JOIN po_lines pl ON pl.id = pr.orderline_id
   JOIN plmaster s ON s.id = pr.plmaster_id
   JOIN company c ON s.company_id = c.id
   JOIN st_uoms u ON u.id = pr.stuom_id;

ALTER TABLE po_receivedoverview OWNER TO "www-data";
