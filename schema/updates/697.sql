DROP VIEW slmaster_overview;
CREATE OR REPLACE VIEW slmaster_overview AS 
 SELECT sl.id, sl.name, sl.company_id, sl.currency_id, cu.currency, sl.statement, sl.outstanding_balance, sl.invoice_method, sl.payment_type_id, sy.name AS payment_type, sl.last_paid, sl.tax_status_id, sl.usercompanyid, sl.created, sl.cb_account_id, sl.despatch_action, sl.sl_analysis_id, sl.email_invoice_id, sl.email_statement_id
   FROM slmaster sl
   JOIN sypaytypes sy ON sy.id = sl.payment_type_id
   JOIN cumaster cu ON cu.id = sl.currency_id;

DROP VIEW plmaster_overview;
CREATE OR REPLACE VIEW plmaster_overview AS 
 SELECT pl.id, pl.name, pl.company_id, pl.currency_id, cu.currency, pl.remittance_advice, pl.outstanding_balance, pl.invoice_method, pl.payment_type_id, sy.name AS payment_type, pl.last_paid, pl.tax_status_id, pl.cb_account_id, pl.receive_action, pl.email_order_id, pl.email_remittance_id, pl.sort_code, pl.account_number, pl.bank_name_address, pl.iban_number, pl.bic_code, pl.created, pl.usercompanyid
   FROM plmaster pl
   JOIN sypaytypes sy ON sy.id = pl.payment_type_id
   JOIN cumaster cu ON cu.id = pl.currency_id;