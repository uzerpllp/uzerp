DROP VIEW pi_headeroverview;

CREATE OR REPLACE VIEW pi_headeroverview AS 
 SELECT pi.id, pi.invoice_number, pi.purchase_order_id, pi.purchase_order_number, pi.our_reference, pi.plmaster_id, pi.invoice_date, pi.transaction_type, pi.status, pi.auth_date, pi.auth_by, pi.ext_reference, pi.currency_id, pi.rate, pi.gross_value, pi.tax_value, pi.net_value, pi.twin_gross_value, pi.twin_tax_value, pi.twin_net_value, pi.base_gross_value, pi.base_tax_value, pi.base_net_value, pi.payment_term_id, pi.due_date, pi.description, pi.usercompanyid, pi.twin_currency_id AS twin_currency, pi.twin_rate, plm.name AS supplier, cum.currency, twc.currency AS twin, syt.description AS payment_terms
   FROM pi_header pi
   JOIN plmaster plm ON pi.plmaster_id = plm.id
   JOIN cumaster cum ON pi.currency_id = cum.id
   JOIN cumaster twc ON pi.twin_currency_id = twc.id
   JOIN syterms syt ON pi.payment_term_id = syt.id;