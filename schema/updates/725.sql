DROP VIEW slmaster_overview;

CREATE OR REPLACE VIEW slmaster_overview AS 
 SELECT sl.id, sl.name, sl.company_id, sl.currency_id, cu.currency, sl.statement, sl.outstanding_balance, sl.invoice_method, sl.payment_type_id, sy.name AS payment_type, sl.last_paid, sl.tax_status_id, sl.usercompanyid, sl.created, sl.cb_account_id, sl.despatch_action, sl.sl_analysis_id, sl.email_invoice_id, sl.email_statement_id, sa.name AS sl_analysis
   FROM slmaster sl
   JOIN sypaytypes sy ON sy.id = sl.payment_type_id
   JOIN cumaster cu ON cu.id = sl.currency_id
   LEFT JOIN sl_analysis sa ON sa.id = sl.sl_analysis_id;