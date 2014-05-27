--
-- $Revision: 1.4 $
--

-- Table si_lines
--
-- Column: invoice_line_id

-- ALTER TABLE si_lines DROP COLUMN invoice_line_id;

ALTER TABLE si_lines ADD COLUMN invoice_line_id integer;

-- Foreign Key: si_lines_order_line_id_fkey

-- ALTER TABLE si_lines DROP CONSTRAINT si_lines_order_line_id_fkey;

ALTER TABLE si_lines
  ADD CONSTRAINT si_lines_order_line_id_fkey FOREIGN KEY (order_line_id)
      REFERENCES so_lines (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

-- Foreign Key: si_lines_invoice_line_id_fkey

-- ALTER TABLE si_lines DROP CONSTRAINT si_lines_invoice_line_id_fkey;

ALTER TABLE si_lines
  ADD CONSTRAINT si_lines_invoice_line_id_fkey FOREIGN KEY (invoice_line_id)
      REFERENCES si_lines (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

-- Table pi_lines
--
-- Column: invoice_line_id

-- ALTER TABLE pi_lines DROP COLUMN invoice_line_id;

ALTER TABLE pi_lines ADD COLUMN invoice_line_id integer;

-- Foreign Key: pi_lines_order_line_id_fkey

-- ALTER TABLE pi_lines DROP CONSTRAINT pi_lines_order_line_id_fkey;

ALTER TABLE pi_lines
  ADD CONSTRAINT pi_lines_order_line_id_fkey FOREIGN KEY (order_line_id)
      REFERENCES po_lines (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

-- Foreign Key: pi_lines_invoice_line_id_fkey

-- ALTER TABLE pi_lines DROP CONSTRAINT pi_lines_invoice_line_id_fkey;

ALTER TABLE pi_lines
  ADD CONSTRAINT pi_lines_invoice_line_id_fkey FOREIGN KEY (invoice_line_id)
      REFERENCES pi_lines (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

-- Foreign Key: po_receivedlines_porder_id_fkey

ALTER TABLE po_receivedlines DROP CONSTRAINT po_receivedlines_porder_id_fkey;

ALTER TABLE po_receivedlines
  ADD CONSTRAINT po_receivedlines_porder_id_fkey FOREIGN KEY (order_id)
      REFERENCES po_header (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

-- Foreign Key: po_receivedlines_porderline_id_fkey

ALTER TABLE po_receivedlines DROP CONSTRAINT po_receivedlines_porderline_id_fkey;

ALTER TABLE po_receivedlines
  ADD CONSTRAINT po_receivedlines_porderline_id_fkey FOREIGN KEY (orderline_id)
      REFERENCES po_lines (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

-- Foreign Key: po_receivedlines_stuom_id_fkey

ALTER TABLE po_receivedlines DROP CONSTRAINT po_receivedlines_stuom_id_fkey;

ALTER TABLE po_receivedlines
  ADD CONSTRAINT po_receivedlines_stuom_id_fkey FOREIGN KEY (stuom_id)
      REFERENCES st_uoms (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

-- Table cb_transactions
--
-- Column: transaction_type

-- ALTER TABLE cb_transactions DROP COLUMN transaction_type;

ALTER TABLE cb_transactions ADD COLUMN type character varying;

UPDATE cb_transactions
   SET type = 'R'
 WHERE source = 'S'
   AND EXISTS (SELECT transaction_type
                 FROM sltransactions slt
                WHERE cast(slt.our_reference as integer) = cb_transactions.reference
                  AND slt.transaction_type = 'R');

UPDATE cb_transactions
   SET type = 'P'
 WHERE source = 'P'
   AND EXISTS (SELECT transaction_type
                 FROM pltransactions plt
                WHERE cast(plt.our_reference as integer) = cb_transactions.reference
                  AND plt.transaction_type = 'P');

UPDATE gl_transactions
   SET docref = (SELECT cbt.reference
                   FROM cb_transactions cbt
                   JOIN cb_accounts a ON a.id = cbt.cb_account_id
                                     AND a.glaccount_id = gl_transactions.glaccount_id
                                     AND cast(gl_transactions.docref as integer) = cbt.reference+1
                                     AND cbt.base_net_value = gl_transactions.value)
 WHERE gl_transactions.source = 'C'
   AND gl_transactions.type = 'T'
   AND EXISTS (SELECT 1
                 FROM cb_transactions cbt
                 JOIN cb_accounts a ON a.id = cbt.cb_account_id
                                   AND a.glaccount_id = gl_transactions.glaccount_id
                                   AND cast(gl_transactions.docref as integer) = cbt.reference+1
                                   AND cbt.base_net_value = gl_transactions.value);

UPDATE gl_transactions
   SET comment = (SELECT cbt.reference
                   FROM cb_transactions cbt
                   JOIN cb_accounts a ON a.id = cbt.cb_account_id
                                     AND a.glaccount_id = gl_transactions.glaccount_id
                                     AND gl_transactions.comment = cast((cbt.reference+1) as character varying)
                                     AND cbt.base_net_value = gl_transactions.value)
 WHERE gl_transactions.source = 'C'
   AND gl_transactions.type = 'T'
   AND EXISTS (SELECT 1
                 FROM cb_transactions cbt
                 JOIN cb_accounts a ON a.id = cbt.cb_account_id
                                   AND a.glaccount_id = gl_transactions.glaccount_id
                                   AND gl_transactions.comment = cast((cbt.reference+1) as character varying)
                                   AND cbt.base_net_value = gl_transactions.value);

UPDATE cb_transactions
   SET type = (SELECT distinct type
                             FROM gl_transactions glt
                            WHERE cast(glt.docref as integer) = cb_transactions.reference
                              AND glt.source = 'C')
 WHERE source = 'C';


ALTER TABLE cb_transactions ALTER COLUMN type SET NOT NULL;

-- View: cb_transactionsoverview

DROP VIEW cb_transactionsoverview;

CREATE OR REPLACE VIEW cb_transactionsoverview AS 
 SELECT cb.*
 , a.name AS cb_account
 , c.currency
 , cmp.name AS company
 , (p.firstname::text || ' '::text) || p.surname::text AS person
 , sy.name AS payment_type
   FROM cb_transactions cb
   JOIN cb_accounts a ON a.id = cb.cb_account_id
   LEFT JOIN company cmp ON cmp.id = cb.company_id
   LEFT JOIN person p ON p.id = cb.person_id
   LEFT JOIN sypaytypes sy ON sy.id = cb.payment_type_id
   LEFT JOIN cumaster c ON c.id = cb.currency_id;

ALTER TABLE cb_transactionsoverview OWNER TO "www-data";

