ALTER TABLE plmaster ADD COLUMN local_sort_code integer;
ALTER TABLE plmaster ADD COLUMN local_account_number integer;
ALTER TABLE plmaster ADD COLUMN local_bank_name_address character varying;
ALTER TABLE plmaster ADD COLUMN overseas_iban_number character varying;
ALTER TABLE plmaster ADD COLUMN overseas_bic_code character varying;
ALTER TABLE plmaster ADD COLUMN overseas_account_number integer;
ALTER TABLE plmaster ADD COLUMN overseas_bank_name_address character varying;