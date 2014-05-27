--
-- $Revision: 1.2 $
--

-- Table: pi_lines

-- Column: grn_id

-- ALTER TABLE pi_lines DROP COLUMN grn_id;

ALTER TABLE pi_lines ADD COLUMN grn_id integer;

-- Column: grn_id

-- ALTER TABLE pi_lines DROP COLUMN gr_number;

ALTER TABLE pi_lines ADD COLUMN gr_number integer;

-- Foreign Key: pi_lines_grn_id_fkey

-- ALTER TABLE pi_lines DROP CONSTRAINT pi_lines_grn_id_fkey;

ALTER TABLE pi_lines
  ADD CONSTRAINT pi_lines_grn_id_fkey FOREIGN KEY (grn_id)
      REFERENCES po_receivedlines (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE;

-- Foreign Key: po_receivedlines_invoice_id_fkey

-- ALTER TABLE po_receivedlines DROP CONSTRAINT po_receivedlines_invoice_id_fkey;

ALTER TABLE po_receivedlines
  ADD CONSTRAINT po_receivedlines_invoice_id_fkey FOREIGN KEY (invoice_id)
      REFERENCES pi_header (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

UPDATE pi_lines l
   SET grn_id = (SELECT id
                   FROM po_receivedlines r
                  WHERE l.invoice_id = r.invoice_id
                    AND l.order_line_id = r.orderline_id
                    AND l.purchase_qty = r.received_qty
                    AND r.status in ('A', 'I')
                  LIMIT 1)
 WHERE EXISTS (SELECT 1
                 FROM po_receivedlines r
                WHERE l.invoice_id = r.invoice_id
                  AND l.order_line_id = r.orderline_id
                  AND l.purchase_qty = r.received_qty
                  AND r.status in ('A', 'I'));

UPDATE pi_lines l
   SET gr_number = (SELECT gr_number
                      FROM po_receivedlines r
                     WHERE l.grn_id = r.id)
 WHERE EXISTS (SELECT 1
                 FROM po_receivedlines r
                WHERE l.invoice_id = r.invoice_id
                  AND l.grn_id = r.id);

-- View: pi_linesoverview

DROP VIEW pi_linesoverview;

CREATE OR REPLACE VIEW pi_linesoverview AS 
 SELECT pl.*, ph.invoice_date, ph.invoice_number
 , ph.transaction_type, ph.plmaster_id, poh.order_number, c.name AS supplier, i.item_code
 , (i.item_code::text || ' - '::text) || i.description::text AS stitem
   FROM pi_lines pl
   JOIN pi_header ph ON ph.id = pl.invoice_id
   JOIN plmaster plm ON ph.plmaster_id = plm.id
   JOIN company c ON plm.company_id = c.id
   LEFT JOIN po_header poh ON pl.purchase_order_id = poh.id
   LEFT JOIN st_items i ON i.id = pl.stitem_id;

ALTER TABLE pi_linesoverview OWNER TO "www-data";
