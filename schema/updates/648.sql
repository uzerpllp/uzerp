ALTER TABLE slmaster ADD COLUMN email_invoice_id integer;
ALTER TABLE slmaster ADD COLUMN email_statement_id integer;

ALTER TABLE plmaster ADD COLUMN email_order_id integer;
ALTER TABLE plmaster ADD COLUMN email_remittance_id integer;