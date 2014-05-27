--
-- $Revision: 1.3 $
--

-- View: si_headeroverview

DROP VIEW reports.audit_materialsales;

DROP VIEW reports.tax_eu_saleslist;

DROP VIEW reports.si_settlementoverview;

DROP VIEW tax_eu_saleslist;

DROP VIEW si_headeroverview;

ALTER TABLE si_header DROP COLUMN sales_order_number;

CREATE OR REPLACE VIEW si_headeroverview AS 
 SELECT si.id, si.invoice_number, si.sales_order_id, si.slmaster_id, si.invoice_date
 , si.transaction_type, si.ext_reference, si.currency_id, si.rate, si.gross_value, si.tax_value
 , si.net_value, si.twin_currency_id, si.twin_rate, si.twin_gross_value, si.twin_tax_value
 , si.twin_net_value, si.base_gross_value, si.base_tax_value, si.base_net_value
 , si.payment_term_id, si.due_date, si.status, si.description, si.usercompanyid
 , si.tax_status_id, si.settlement_discount
 , si.delivery_note, si.despatch_date, si.date_printed, si.print_count, si.del_address_id
 , si.inv_address_id, si.original_due_date, si.created, si.createdby, si.alteredby
 , si.lastupdated, si.person_id
 , so.order_number as sales_order_number, c.name AS customer, cum.currency, twc.currency AS twin
 , syt.description AS payment_terms, ts.description AS tax_status, slm.sl_analysis_id
 , slm.invoice_method, slm.edi_invoice_definition_id, pcm.contact AS email_invoice
 , sla.name, (p.firstname::text || ' '::text) || p.surname::text AS person
 , COALESCE(sl.line_count, 0::bigint) AS line_count
   FROM si_header si
   LEFT JOIN ( SELECT si_lines.invoice_id, count(*) AS line_count
           FROM si_lines
          GROUP BY si_lines.invoice_id) sl ON sl.invoice_id = si.id
   JOIN slmaster slm ON si.slmaster_id = slm.id
   LEFT JOIN so_header so ON so.id = si.sales_order_id
   LEFT JOIN partycontactmethodoverview pcm ON pcm.id = slm.email_invoice_id
   JOIN company c ON slm.company_id = c.id
   LEFT JOIN person p ON si.person_id = p.id
   LEFT JOIN sl_analysis sla ON slm.sl_analysis_id = sla.id
   JOIN cumaster cum ON si.currency_id = cum.id
   JOIN cumaster twc ON si.twin_currency_id = twc.id
   JOIN tax_statuses ts ON si.tax_status_id = ts.id
   JOIN syterms syt ON si.payment_term_id = syt.id;

ALTER TABLE si_headeroverview OWNER TO "www-data";

CREATE OR REPLACE VIEW tax_eu_saleslist AS 
 SELECT si.id, si.invoice_number, so.order_number as sales_order_number, si.invoice_date, si.transaction_type, si.ext_reference, si.currency_id, si.rate, si.settlement_discount, 
        CASE
            WHEN si.transaction_type::text = 'C'::text THEN si.gross_value * (-1)::numeric
            ELSE si.gross_value
        END AS gross_value, 
        CASE
            WHEN si.transaction_type::text = 'C'::text THEN si.tax_value * (-1)::numeric
            ELSE si.tax_value
        END AS tax_value, 
        CASE
            WHEN si.transaction_type::text = 'C'::text THEN si.net_value * (-1)::numeric
            ELSE si.net_value
        END AS net_value, si.twin_currency_id AS twin_currency, si.twin_rate, 
        CASE
            WHEN si.transaction_type::text = 'C'::text THEN si.twin_gross_value * (-1)::numeric
            ELSE si.twin_gross_value
        END AS twin_gross_value, 
        CASE
            WHEN si.transaction_type::text = 'C'::text THEN si.twin_tax_value * (-1)::numeric
            ELSE si.twin_tax_value
        END AS twin_tax_value, 
        CASE
            WHEN si.transaction_type::text = 'C'::text THEN si.twin_net_value * (-1)::numeric
            ELSE si.twin_net_value
        END AS twin_net_value, 
        CASE
            WHEN si.transaction_type::text = 'C'::text THEN si.base_gross_value * (-1)::numeric
            ELSE si.base_gross_value
        END AS base_gross_value, 
        CASE
            WHEN si.transaction_type::text = 'C'::text THEN si.base_tax_value * (-1)::numeric
            ELSE si.base_tax_value
        END AS base_tax_value, 
        CASE
            WHEN si.transaction_type::text = 'C'::text THEN si.base_net_value * (-1)::numeric
            ELSE si.base_net_value
        END AS base_net_value, si.payment_term_id, si.due_date, si.status, si.description, si.tax_status_id, si.delivery_note, si.despatch_date, si.date_printed, si.print_count, si.usercompanyid, coy.name AS customer, cum.currency, twc.currency AS twin, syt.description AS payment_terms, ts.description AS tax_status, coy.vatnumber AS vat_number, cad.countrycode AS country
   FROM si_header si
   JOIN so_header so ON si.sales_order_id = so.id
   JOIN slmaster slm ON si.slmaster_id = slm.id
   JOIN company coy ON slm.company_id = coy.id
   JOIN companyaddress cad ON coy.party_id = cad.party_id AND cad.main IS TRUE
   LEFT JOIN sl_analysis sla ON slm.sl_analysis_id = sla.id
   JOIN cumaster cum ON si.currency_id = cum.id
   JOIN cumaster twc ON si.twin_currency_id = twc.id
   JOIN tax_statuses ts ON si.tax_status_id = ts.id
   JOIN syterms syt ON si.payment_term_id = syt.id
  WHERE ts.eu_tax = true;

ALTER TABLE tax_eu_saleslist OWNER TO "www-data";

CREATE OR REPLACE VIEW reports.audit_materialsales AS 
 SELECT si_headeroverview.invoice_number, si_headeroverview.sales_order_number, si_headeroverview.delivery_note, si_headeroverview.invoice_date, si_headeroverview.transaction_type, si_headeroverview.ext_reference, si_headeroverview.gross_value, si_headeroverview.base_gross_value, si_headeroverview.description, si_headeroverview.customer, si_headeroverview.currency, si_headeroverview.payment_terms, si_headeroverview.name AS sl_analysis
   FROM si_headeroverview
  WHERE si_headeroverview.base_gross_value > 15000::numeric;

ALTER TABLE reports.audit_materialsales OWNER TO "www-data";
GRANT ALL ON TABLE reports.audit_materialsales TO "www-data";
GRANT SELECT ON TABLE reports.audit_materialsales TO "ooo-data";

CREATE OR REPLACE VIEW reports.tax_eu_saleslist AS 
 SELECT si.id, si.invoice_number, so.order_number as sales_order_number, si.invoice_date, si.transaction_type, si.ext_reference
 , si.currency_id, si.rate, si.settlement_discount, si.gross_value, si.tax_value, si.net_value
 , si.twin_currency_id AS twin_currency, si.twin_rate, si.twin_gross_value, si.twin_tax_value, si.twin_net_value
 , si.base_gross_value, si.base_tax_value, si.base_net_value, si.payment_term_id, si.due_date, si.status, si.description
 , si.tax_status_id, si.delivery_note, si.despatch_date, si.date_printed, si.print_count, si.usercompanyid
 , coy.name AS customer, cum.currency, twc.currency AS twin, syt.description AS payment_terms
 , ts.description AS tax_status, coy.vatnumber
   FROM si_header si
   JOIN so_header so ON si.sales_order_id = so.id
   JOIN slmaster slm ON si.slmaster_id = slm.id
   JOIN company coy ON slm.company_id = coy.id
   LEFT JOIN sl_analysis sla ON slm.sl_analysis_id = sla.id
   JOIN cumaster cum ON si.currency_id = cum.id
   JOIN cumaster twc ON si.twin_currency_id = twc.id
   JOIN tax_statuses ts ON si.tax_status_id = ts.id
   JOIN syterms syt ON si.payment_term_id = syt.id
  WHERE ts.eu_tax = true;

ALTER TABLE reports.tax_eu_saleslist OWNER TO "www-data";
GRANT ALL ON TABLE reports.tax_eu_saleslist TO "www-data";
GRANT SELECT ON TABLE reports.tax_eu_saleslist TO "ooo-data";

CREATE OR REPLACE VIEW reports.si_settlementoverview AS 
 SELECT si.*
 , com.name AS customer, cum.currency, twc.currency AS twin, syt.description AS payment_terms
 , ts.description AS tax_status, slm.sl_analysis_id, sla.name
   FROM si_header si
   JOIN slmaster slm ON si.slmaster_id = slm.id
   JOIN company com ON slm.company_id = com.id
   LEFT JOIN sl_analysis sla ON slm.sl_analysis_id = sla.id
   JOIN cumaster cum ON si.currency_id = cum.id
   JOIN cumaster twc ON si.twin_currency_id = twc.id
   JOIN tax_statuses ts ON si.tax_status_id = ts.id
   JOIN syterms syt ON si.payment_term_id = syt.id
  WHERE si.settlement_discount <> 0::numeric AND si.status::text <> 'P'::text;

ALTER TABLE reports.si_settlementoverview OWNER TO "www-data";
GRANT ALL ON TABLE reports.si_settlementoverview TO "www-data";
GRANT SELECT ON TABLE reports.si_settlementoverview TO "ooo-data";