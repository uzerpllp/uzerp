DROP VIEW si_headeroverview;

CREATE OR REPLACE VIEW si_headeroverview AS 
 SELECT si.id, si.invoice_number, si.sales_order_number, si.sales_order_id, si.slmaster_id, si.invoice_date, si.transaction_type, si.ext_reference, si.currency_id, si.rate, si.settlement_discount, si.gross_value, si.tax_value, si.net_value, si.twin_currency_id AS twin_currency, si.twin_rate, si.twin_gross_value, si.twin_tax_value, si.twin_net_value, si.base_gross_value, si.base_tax_value, si.base_net_value, si.payment_term_id, si.due_date, si.status, si.description, si.tax_status_id, si.delivery_note, si.despatch_date, si.date_printed, si.print_count, si.usercompanyid, slm.name AS customer, cum.currency, twc.currency AS twin, syt.description AS payment_terms, ts.description AS tax_status, slm.sl_analysis_id, sla.name
   FROM si_header si
   JOIN slmaster slm ON si.slmaster_id = slm.id
   LEFT JOIN sl_analysis sla ON slm.sl_analysis_id = sla.id
   JOIN cumaster cum ON si.currency_id = cum.id
   JOIN cumaster twc ON si.twin_currency_id = twc.id
   JOIN tax_statuses ts ON si.tax_status_id = ts.id
   JOIN syterms syt ON si.payment_term_id = syt.id;