DROP VIEW qc_complaints_overview;

ALTER TABLE qc_complaints RENAME product_id TO stitem_id;

ALTER TABLE qc_complaints DROP CONSTRAINT complaints_product_id_fkey;

ALTER TABLE qc_complaints
  ADD CONSTRAINT complaints_stitem_id_fkey FOREIGN KEY (stitem_id)
      REFERENCES st_items (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE qc_complaints RENAME customer_id TO slmaster_id;

ALTER TABLE qc_complaints DROP CONSTRAINT complaints_customer_id_fkey;

ALTER TABLE qc_complaints
  ADD CONSTRAINT complaints_slmaster_id_fkey FOREIGN KEY (slmaster_id)
      REFERENCES company (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE qc_complaints ADD COLUMN customer character varying;

ALTER TABLE qc_complaints ADD COLUMN currency_id integer;

ALTER TABLE qc_complaints
  ADD CONSTRAINT complaints_currency_id_fkey FOREIGN KEY (currency_id)
      REFERENCES cumaster (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

CREATE OR REPLACE VIEW qc_complaints_overview AS 
 SELECT qc.id, qc.date, qc.slmaster_id, sl.name AS retailer, qc.customer, qc.stitem_id, st.description AS product, qc.complaint_code_id, qc.supplementary_code_id, qc.problem, qc.investigation, qc.outcome, qc.credit_amount, qc.credit_note_no, qc.invoice_debit_no, qc.date_complete, qc.cost, qc.usercompanyid, qc.lastupdated, qc.alteredby, qc.type, qc.complaint_number, qc.assignedto
   FROM qc_complaints qc
   LEFT JOIN slmaster sl ON sl.id = qc.slmaster_id
   LEFT JOIN st_items st ON st.id = qc.stitem_id;
