--
-- $Revision: 1.1 $
--

DROP VIEW pi_headeroverview;

CREATE OR REPLACE VIEW pi_headeroverview AS 
 SELECT pi.id, pi.invoice_number, pi.our_reference, pi.plmaster_id, pi.invoice_date, pi.transaction_type, pi.ext_reference
 , pi.currency_id, pi.rate, pi.gross_value, pi.tax_value, pi.tax_status_id, pi.net_value, pi.twin_currency_id, pi.twin_rate
 , pi.twin_gross_value, pi.twin_tax_value, pi.twin_net_value, pi.base_gross_value, pi.base_tax_value, pi.base_net_value
 , pi.payment_term_id, pi.due_date, pi.status, pi.description, pi.auth_date, pi.auth_by, pi.usercompanyid
 , pi.original_due_date, pi.created, pi.createdby, pi.alteredby, pi.lastupdated, plm.payee_name, c.name AS supplier
 , cum.currency, twc.currency AS twin, syt.description AS payment_terms
 , coalesce(pl.line_count, 0) as line_count
   FROM pi_header pi
   LEFT JOIN (select invoice_id, count(*) as line_count
           from pi_lines
          group by invoice_id) pl ON pl.invoice_id = pi.id
   JOIN plmaster plm ON pi.plmaster_id = plm.id
   JOIN company c ON plm.company_id = c.id
   JOIN cumaster cum ON pi.currency_id = cum.id
   JOIN cumaster twc ON pi.twin_currency_id = twc.id
   JOIN syterms syt ON pi.payment_term_id = syt.id;

ALTER TABLE pi_headeroverview OWNER TO "www-data";

DROP VIEW si_headeroverview;

CREATE OR REPLACE VIEW si_headeroverview AS 
 SELECT si.id, si.invoice_number, si.sales_order_id, si.slmaster_id, si.invoice_date, si.transaction_type
 , si.ext_reference, si.currency_id, si.rate, si.gross_value, si.tax_value, si.net_value, si.twin_currency_id
 , si.twin_rate, si.twin_gross_value, si.twin_tax_value, si.twin_net_value, si.base_gross_value, si.base_tax_value
 , si.base_net_value, si.payment_term_id, si.due_date, si.status, si.description, si.usercompanyid, si.tax_status_id
 , si.sales_order_number, si.settlement_discount, si.delivery_note, si.despatch_date, si.date_printed, si.print_count
 , si.del_address_id, si.inv_address_id, si.original_due_date, si.created, si.createdby, si.alteredby, si.lastupdated
 , si.person_id, c.name AS customer, cum.currency, twc.currency AS twin, syt.description AS payment_terms
 , ts.description AS tax_status, slm.sl_analysis_id, slm.invoice_method, slm.edi_invoice_definition_id
 , pcm.contact AS email_invoice, sla.name, (p.firstname::text || ' '::text) || p.surname::text AS person
 , coalesce(sl.line_count, 0) as line_count
   FROM si_header si
   LEFT JOIN (select invoice_id, count(*) as line_count
           from si_lines
          group by invoice_id) sl ON sl.invoice_id = si.id
   JOIN slmaster slm ON si.slmaster_id = slm.id
   LEFT JOIN partycontactmethodoverview pcm ON pcm.id = slm.email_invoice_id
   JOIN company c ON slm.company_id = c.id
   LEFT JOIN person p ON si.person_id = p.id
   LEFT JOIN sl_analysis sla ON slm.sl_analysis_id = sla.id
   JOIN cumaster cum ON si.currency_id = cum.id
   JOIN cumaster twc ON si.twin_currency_id = twc.id
   JOIN tax_statuses ts ON si.tax_status_id = ts.id
   JOIN syterms syt ON si.payment_term_id = syt.id;

ALTER TABLE si_headeroverview OWNER TO "www-data";