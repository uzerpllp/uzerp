--
-- ******************************** Sales Ledger Suppliers ********************************
--
-- 1) Drop 'name' column from slmaster
-- 2) Add 'credit_limit' column to slmaster
-- 3) Copy value of company.creditlimit to slmaster.credit_limit
-- 4) Set default value of slmaster.credit_limit = 0
-- 5) Update slmaster to set slmaster.credit_limit =0 where it is null
-- 6) Set slmaster.credit_limit as not null

DROP VIEW reports.tax_eu_saleslist;

DROP VIEW reports.tax_eu_despatches;

DROP VIEW reports.sl_ageddebtors;

DROP VIEW reports.sl_factored_transactions;

DROP VIEW reports.sl_openitems;

DROP VIEW validate.validate_sl_to_gl;

DROP VIEW validate.validate_sl;

DROP VIEW customer_service_summary;

DROP VIEW customer_service;

DROP VIEW qc_complaints_overview;

DROP VIEW tax_eu_saleslist;

DROP VIEW tax_eu_despatches;

DROP VIEW si_linesoverview;

DROP VIEW si_headeroverview;

DROP VIEW so_despatchoverview;

DROP VIEW so_productlines_overview;

DROP VIEW so_itemorders;

DROP VIEW so_headeroverview;

DROP VIEW so_linesoverview;

DROP VIEW sl_aged_creditors_summary;

DROP VIEW sl_aged_debtors_overview;

DROP VIEW sltransactionsoverview;

DROP VIEW sl_discounts_overview;

DROP VIEW slmaster_overview;

ALTER TABLE slmaster DROP COLUMN name;

ALTER TABLE slmaster ADD COLUMN credit_limit numeric;
ALTER TABLE slmaster ALTER COLUMN credit_limit SET DEFAULT 0;

UPDATE slmaster
   SET credit_limit = (SELECT creditlimit
                         FROM company
                        WHERE id = slmaster.company_id);

UPDATE slmaster
   SET credit_limit = 0
 WHERE credit_limit is null;

ALTER TABLE slmaster ALTER COLUMN credit_limit SET NOT NULL;

CREATE OR REPLACE VIEW slmaster_overview AS 
 SELECT sl.*, c.name, cu.currency
 , sy.name AS payment_type, sa.name AS sl_analysis
   FROM slmaster sl
   JOIN company c ON c.id = sl.company_id
   JOIN sypaytypes sy ON sy.id = sl.payment_type_id
   JOIN cumaster cu ON cu.id = sl.currency_id
   LEFT JOIN sl_analysis sa ON sa.id = sl.sl_analysis_id;

CREATE OR REPLACE VIEW sl_discounts_overview AS 
 SELECT sd.id, sd.slmaster_id, sd.prod_group_id, sd.discount_percentage, sd.usercompanyid, sd.created
, sd.createdby, sd.alteredby, sd.lastupdated
, c.name AS customer, (pg.product_group::text || ' - '::text) || pg.description::text AS product_group
   FROM sl_discounts sd
   JOIN slmaster slm ON sd.slmaster_id = slm.id
   JOIN company c ON c.id = slm.company_id
   JOIN st_productgroups pg ON pg.id = sd.prod_group_id;

CREATE OR REPLACE VIEW sltransactionsoverview AS 
 SELECT slt.id, slt.transaction_date, slt.transaction_type, slt.status, slt.our_reference, slt.ext_reference
, slt.currency_id, slt.rate, slt.gross_value, slt.tax_value, slt.net_value, slt.twin_currency_id AS twin_currency
, slt.twin_rate, slt.twin_gross_value, slt.twin_tax_value, slt.twin_net_value, slt.base_gross_value, slt.base_tax_value
, slt.base_net_value, slt.payment_term_id, slt.due_date, slt.cross_ref, slt.os_value, slt.twin_os_value
, slt.base_os_value, slt.description, slt.usercompanyid, slt.slmaster_id, c.name AS customer
, cum.currency, twc.currency AS twin, syt.description AS payment_terms
   FROM sltransactions slt
   JOIN slmaster slm ON slt.slmaster_id = slm.id
   JOIN company c ON c.id = slm.company_id
   JOIN cumaster cum ON slt.currency_id = cum.id
   JOIN cumaster twc ON slt.twin_currency_id = twc.id
   JOIN syterms syt ON slt.payment_term_id = syt.id;

CREATE OR REPLACE VIEW sl_aged_creditors_summary AS 
 SELECT date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) AS id, t.usercompanyid, sum(t.base_gross_value) AS value
   FROM sltransactionsoverview t
  WHERE t.status::text <> 'P'::text
  GROUP BY date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)), t.usercompanyid
  ORDER BY date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone));

CREATE OR REPLACE VIEW sl_aged_debtors_overview AS 
 SELECT (((s.slmaster_id::text || '-'::text) || s.our_reference::text) || '-'::text) || s.transaction_type::text AS id
, s.slmaster_id, s.customer, date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone
, s.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone
, s.transaction_date::timestamp with time zone)) AS age, s.usercompanyid, sum(s.base_gross_value) AS value
, s.our_reference, s.transaction_type
   FROM sltransactionsoverview s
  WHERE s.status::text <> 'P'::text
  GROUP BY s.customer, s.slmaster_id, date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone)), s.usercompanyid, s.our_reference, s.transaction_type
  ORDER BY s.customer, s.slmaster_id, date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone));

CREATE OR REPLACE VIEW so_headeroverview AS 
 SELECT so.id, so.order_number, so.slmaster_id, so.del_address_id, so.order_date, so.due_date, so.despatch_date
, so.ext_reference, so.currency_id, so.rate, so.net_value, so.twin_currency_id, so.twin_rate, so.twin_net_value
, so.base_net_value, so.type, so.status, so.description, so.usercompanyid, so.despatch_action, so.inv_address_id
, so.created, so.createdby, so.alteredby, so.lastupdated, so.person_id, slm.account_status
, c.name AS customer, cum.currency, twc.currency AS twin_currency
   FROM so_header so
   JOIN slmaster slm ON so.slmaster_id = slm.id
   JOIN company c ON slm.company_id = c.id
   JOIN cumaster cum ON so.currency_id = cum.id
   JOIN cumaster twc ON so.twin_currency_id = twc.id;

CREATE OR REPLACE VIEW so_linesoverview AS 
 SELECT sl.id, sl.order_id, sl.line_number, sl.productline_id, sl.stuom_id, sl.item_description, sl.order_qty, sl.price
, sl.currency_id, sl.rate, sl.net_value, sl.twin_currency_id, sl.twin_rate, sl.twin_net_value, sl.base_net_value
, sl.glaccount_id, sl.glcentre_id, sl.line_discount, sl.os_qty, sl.revised_qty, sl.del_qty, sl.due_delivery_date
, sl.due_despatch_date, sl.actual_despatch_date, sl.delivery_note, sl.status, sl.usercompanyid, sl.stitem_id
, sl.tax_rate_id, sl.created, sl.createdby, sl.alteredby, sl.lastupdated, sl.line_value, sl.line_tradedisc_percentage
, sl.line_qtydisc_percentage, sl.description, sh.due_date, sh.order_date, sh.order_number
, sh.slmaster_id, sh.type, c.name AS customer
, (i.item_code::text || ' - '::text) || i.description::text AS stitem, uom.uom_name
   FROM so_lines sl
   JOIN so_header sh ON sh.id = sl.order_id
   JOIN slmaster slm ON sh.slmaster_id = slm.id
   JOIN company c ON slm.company_id = c.id
   LEFT JOIN st_uoms uom ON sl.stuom_id = uom.id
   LEFT JOIN st_items i ON i.id = sl.stitem_id;

CREATE OR REPLACE VIEW so_itemorders AS 
 SELECT sl.id, sl.order_id, sl.line_number, sl.productline_id, sl.stuom_id, sl.item_description, sl.order_qty
, sl.price, sl.currency_id, sl.rate, sl.net_value, sl.twin_currency_id, sl.twin_rate, sl.twin_net_value, sl.base_net_value
, sl.glaccount_id, sl.glcentre_id, sl.line_discount, sl.os_qty, sl.revised_qty, sl.del_qty, sl.due_delivery_date
, sl.due_despatch_date, sl.actual_despatch_date, sl.delivery_note, sl.status, sl.usercompanyid, sl.stitem_id
, sl.tax_rate_id, sl.created, sl.createdby, sl.alteredby, sl.lastupdated, sl.line_value, sl.line_tradedisc_percentage
, sl.line_qtydisc_percentage, sh.despatch_date, sh.customer, sh.order_number, sh.slmaster_id
, sh.type, sh.account_status, sh.despatch_action
, (i.item_code::text || ' - '::text) || i.description::text AS stitem
, sl.revised_qty AS required, u.uom_name AS stuom
   FROM so_lines sl
   JOIN st_items i ON i.id = sl.stitem_id
   JOIN st_uoms u ON u.id = sl.stuom_id
   JOIN so_headeroverview sh ON sh.id = sl.order_id
  WHERE sl.stitem_id IS NOT NULL AND (sl.status::text = ANY (ARRAY['N'::character varying, 'R'::character varying]::text[]));

CREATE OR REPLACE VIEW so_productlines_overview AS 
 SELECT so.id, so.currency_id, so.glaccount_id, so.glcentre_id, so.slmaster_id, so.stitem_id, so.stuom_id
, so.customer_product_code, so.description, so.price, so.usercompanyid, so.tax_rate_id, so.created, so.createdby
, so.alteredby, so.lastupdated, so.so_price_type_id, so.start_date, so.end_date, so.prod_group_id
, c.name AS customer
, uom.uom_name, (st.item_code::text || ' - '::text) || st.description::text AS stitem, gla.account AS glaccount, glc.cost_centre AS glcentre, cu.currency, tax.description AS taxrate, pt.name AS so_price_type
   FROM so_product_lines so
   LEFT JOIN slmaster slm ON so.slmaster_id = slm.id
   JOIN company c ON slm.company_id = c.id
   LEFT JOIN st_items st ON so.stitem_id = st.id
   LEFT JOIN st_uoms uom ON so.stuom_id = uom.id
   JOIN cumaster cu ON so.currency_id = cu.id
   JOIN taxrates tax ON so.tax_rate_id = tax.id
   JOIN gl_accounts gla ON so.glaccount_id = gla.id
   JOIN gl_centres glc ON so.glcentre_id = glc.id
   LEFT JOIN so_price_types pt ON so.so_price_type_id = pt.id;

CREATE OR REPLACE VIEW so_despatchoverview AS 
 SELECT sd.id, sd.despatch_number, sd.order_id, sd.slmaster_id, sd.despatch_date, sd.despatch_qty
, sd.orderline_id, sd.productline_id, sd.stuom_id, sd.stitem_id, sd.status, sd.usercompanyid, sd.cs_failurecode_id
, sd.invoice_number, sd.invoice_id, sd.despatch_action, c.name AS customer, sh.order_number
, (i.item_code::text || ' - '::text) || i.description::text AS stitem, u.uom_name
   FROM so_despatchlines sd
   JOIN st_items i ON i.id = sd.stitem_id
   JOIN so_header sh ON sh.id = sd.order_id
   JOIN slmaster sl ON sl.id = sd.slmaster_id
   JOIN company c ON sl.company_id = c.id
   JOIN st_uoms u ON u.id = sd.stuom_id;

CREATE OR REPLACE VIEW si_headeroverview AS 
 SELECT si.id, si.invoice_number, si.sales_order_id, si.slmaster_id, si.invoice_date, si.transaction_type
, si.ext_reference, si.currency_id, si.rate, si.gross_value, si.tax_value, si.net_value, si.twin_currency_id
, si.twin_rate, si.twin_gross_value, si.twin_tax_value, si.twin_net_value, si.base_gross_value, si.base_tax_value
, si.base_net_value, si.payment_term_id, si.due_date, si.status, si.description, si.usercompanyid, si.tax_status_id
, si.sales_order_number, si.settlement_discount, si.delivery_note, si.despatch_date, si.date_printed, si.print_count
, si.del_address_id, si.inv_address_id, si.original_due_date, si.created, si.createdby, si.alteredby, si.lastupdated
, c.name AS customer, cum.currency, twc.currency AS twin, syt.description AS payment_terms
, ts.description AS tax_status, slm.sl_analysis_id, sla.name
   FROM si_header si
   JOIN slmaster slm ON si.slmaster_id = slm.id
   JOIN company c ON slm.company_id = c.id
   LEFT JOIN sl_analysis sla ON slm.sl_analysis_id = sla.id
   JOIN cumaster cum ON si.currency_id = cum.id
   JOIN cumaster twc ON si.twin_currency_id = twc.id
   JOIN tax_statuses ts ON si.tax_status_id = ts.id
   JOIN syterms syt ON si.payment_term_id = syt.id;

CREATE OR REPLACE VIEW si_linesoverview AS 
 SELECT sl.id, sl.invoice_id, sl.line_number, sl.sales_order_id, sl.order_line_id, sl.stitem_id, sl.item_description
, sl.sales_qty, sl.sales_price, sl.currency_id, sl.rate, sl.gross_value, sl.tax_value, sl.net_value, sl.twin_currency_id
, sl.twin_rate, sl.twin_gross_value, sl.twin_tax_value, sl.twin_net_value, sl.base_gross_value, sl.base_tax_value
, sl.base_net_value, sl.glaccount_id, sl.glcentre_id, sl.description, sl.usercompanyid, sl.line_discount
, sl.tax_rate_id, sl.delivery_note, sl.stuom_id, sl.created, sl.createdby, sl.alteredby, sl.lastupdated, sl.productline_id
, sl.move_stock, sh.invoice_date, sh.invoice_number, sh.slmaster_id, soh.order_number
, c.name AS customer, i.item_code
, (i.item_code::text || ' - '::text) || i.description::text AS stitem
, uom.uom_name
   FROM si_lines sl
   JOIN si_header sh ON sh.id = sl.invoice_id
   JOIN slmaster slm ON sh.slmaster_id = slm.id
   JOIN company c ON slm.company_id = c.id
   LEFT JOIN so_header soh ON sl.sales_order_id = soh.id
   LEFT JOIN st_uoms uom ON sl.stuom_id = uom.id
   LEFT JOIN st_items i ON i.id = sl.stitem_id;

CREATE OR REPLACE VIEW qc_complaints_overview AS 
 SELECT qc.id, qc.date, qc.slmaster_id, c.name AS retailer, qc.customer, qc.stitem_id
, st.description AS product, qc.product_complaint, qc.complaint_code_id, cc.code AS complaint_code
, qc.supplementary_code_id, qc.problem, qc.investigation, qc.outcome, qc.credit_amount, qc.credit_note_no
, qc.invoice_debit_no, qc.date_complete, qc.cost, qc.usercompanyid, qc.lastupdated, qc.alteredby
, qc.type, qc.complaint_number, qc.assignedto
   FROM qc_complaints qc
   LEFT JOIN slmaster sl ON sl.id = qc.slmaster_id
   LEFT JOIN company c ON sl.company_id = c.id
   LEFT JOIN st_items st ON st.id = qc.stitem_id
   LEFT JOIN qc_complaint_codes cc ON cc.id = qc.complaint_code_id;

CREATE OR REPLACE VIEW customer_service AS 
 SELECT sd.id, sd.despatch_number, sd.order_id, sd.slmaster_id, sd.despatch_date, sd.despatch_qty
, sd.orderline_id, sd.productline_id, sd.stuom_id, sd.stitem_id, sd.status, sd.usercompanyid, sd.cs_failurecode_id
, sh.order_number, sl.due_despatch_date, sl.order_qty, st.prod_group_id, pg.product_group
, c.name AS customer, cs.code AS failurecode, cs.description AS failure_description
   FROM so_despatchlines sd
   JOIN slmaster sc ON sc.id = sd.slmaster_id
   JOIN company c ON sc.company_id = c.id
   JOIN so_lines sl ON sl.id = sd.orderline_id
   JOIN so_header sh ON sh.id = sl.order_id
   LEFT JOIN cs_failurecodes cs ON cs.id = sd.cs_failurecode_id
   JOIN st_items st ON st.id = sd.stitem_id
   JOIN st_productgroups pg ON pg.id = st.prod_group_id
  WHERE sd.status::text = 'D'::text;

CREATE OR REPLACE VIEW customer_service_summary AS 
 SELECT customer_service.slmaster_id, customer_service.customer, customer_service.product_group
, customer_service.despatch_date, customer_service.usercompanyid
, to_char(customer_service.despatch_date::timestamp with time zone, 'YYYY/MM'::text) AS year_month, 
        CASE
            WHEN customer_service.despatch_date > customer_service.due_despatch_date THEN 0
            ELSE 1
        END AS ontime, 
        CASE
            WHEN customer_service.order_qty > customer_service.despatch_qty THEN 0
            ELSE 1
        END AS infull, 
        CASE
            WHEN customer_service.despatch_date > customer_service.due_despatch_date THEN 0
            WHEN customer_service.order_qty > customer_service.despatch_qty THEN 0
            ELSE 1
        END AS otif, 1 AS count
   FROM customer_service;

CREATE OR REPLACE VIEW tax_eu_despatches AS 
 SELECT sod.id, sod.despatch_date, sod.despatch_qty, sol.item_description, sod.invoice_number
, c.name AS customer, soh.order_number, uom.uom_name, sod.usercompanyid
   FROM so_despatchlines sod
   JOIN so_header soh ON soh.id = sod.order_id
   JOIN so_lines sol ON sol.id = sod.orderline_id
   JOIN slmaster slm ON slm.id = sod.slmaster_id
   JOIN company c ON slm.company_id = c.id
   JOIN tax_statuses tst ON tst.id = slm.tax_status_id AND tst.eu_tax = true
   LEFT JOIN st_uoms uom ON sod.stuom_id = uom.id
  ORDER BY sod.despatch_date, c.name, sol.item_description;

CREATE OR REPLACE VIEW tax_eu_saleslist AS 
 SELECT si.id, si.invoice_number, si.sales_order_number, si.invoice_date, si.transaction_type, si.ext_reference
, si.currency_id, si.rate, si.settlement_discount, si.gross_value, si.tax_value, si.net_value
, si.twin_currency_id AS twin_currency, si.twin_rate, si.twin_gross_value, si.twin_tax_value, si.twin_net_value
, si.base_gross_value, si.base_tax_value, si.base_net_value, si.payment_term_id, si.due_date, si.status
, si.description, si.tax_status_id, si.delivery_note, si.despatch_date, si.date_printed, si.print_count, si.usercompanyid
, coy.name AS customer, cum.currency, twc.currency AS twin, syt.description AS payment_terms
, ts.description AS tax_status, coy.vatnumber AS vat_number, cad.countrycode AS country
   FROM si_header si
   JOIN slmaster slm ON si.slmaster_id = slm.id
   JOIN company coy ON slm.company_id = coy.id
   JOIN companyaddress cad ON coy.party_id = cad.party_id
   LEFT JOIN sl_analysis sla ON slm.sl_analysis_id = sla.id
   JOIN cumaster cum ON si.currency_id = cum.id
   JOIN cumaster twc ON si.twin_currency_id = twc.id
   JOIN tax_statuses ts ON si.tax_status_id = ts.id
   JOIN syterms syt ON si.payment_term_id = syt.id
  WHERE ts.eu_tax = true;

CREATE OR REPLACE VIEW validate.validate_sl AS 
 SELECT slmaster_overview.name, slmaster_overview.currency, slmaster_overview.outstanding_balance - (( SELECT sum(sltransactionsoverview.gross_value) AS sum
           FROM sltransactionsoverview
          WHERE slmaster_overview.id = sltransactionsoverview.slmaster_id AND sltransactionsoverview.status::text = 'O'::text)) AS difference
   FROM slmaster_overview
  ORDER BY slmaster_overview.name;

CREATE OR REPLACE VIEW validate.validate_sl_to_gl AS 
 SELECT glbalancesoverview.glaccount_id, glbalancesoverview.account, glbalancesoverview.glcentre_id
, glbalancesoverview.centre, sum(glbalancesoverview.value) AS balance_per_glbalances
, ( SELECT sum(gltransactionsoverview.value) AS sum
           FROM gltransactionsoverview
          WHERE gltransactionsoverview.glaccount_id = glbalancesoverview.glaccount_id
       AND gltransactionsoverview.glcentre_id = glbalancesoverview.glcentre_id) AS balance_per_gltransactions
, ( SELECT sum(sltransactionsoverview.base_gross_value) AS sum
           FROM sltransactionsoverview
          WHERE sltransactionsoverview.status::text <> 'P'::text) AS outstanding_per_sltransactions
   FROM glbalancesoverview
  WHERE glbalancesoverview.glaccount_id = (( SELECT gl_accounts.id
           FROM gl_accounts
          WHERE gl_accounts.id::text = ((( SELECT gl_params.paramvalue_id
                   FROM gl_params
                  WHERE gl_params.paramdesc::text = 'Sales Ledger Control Account'::text))::text))) AND glbalancesoverview.glcentre_id = (( SELECT gl_centres.id
           FROM gl_centres
          WHERE gl_centres.id::text = ((( SELECT gl_params.paramvalue_id
                   FROM gl_params
                  WHERE gl_params.paramdesc::text = 'Balance Sheet Cost Centre'::text))::text))) AND glbalancesoverview.glperiods_id >= 14
  GROUP BY glbalancesoverview.glaccount_id, glbalancesoverview.account, glbalancesoverview.glcentre_id, glbalancesoverview.centre;

CREATE OR REPLACE VIEW reports.sl_openitems AS 
 SELECT sltransactionsoverview.customer, sltransactionsoverview.transaction_date, sltransactionsoverview.id, sltransactionsoverview.transaction_type, sltransactionsoverview.our_reference, sltransactionsoverview.ext_reference, sltransactionsoverview.gross_value, sltransactionsoverview.currency, sltransactionsoverview.rate, sltransactionsoverview.base_gross_value, sltransactionsoverview.description, sltransactionsoverview.payment_terms, slmaster_overview.sl_analysis, sltransactionsoverview.due_date, date_part('year'::text, sltransactionsoverview.due_date) AS due_year, date_part('week'::text, sltransactionsoverview.due_date) AS due_week, 
        CASE
            WHEN sltransactionsoverview.due_date < 'now'::text::date THEN date_part('year'::text, 'now'::text::date)
            ELSE date_part('year'::text, sltransactionsoverview.due_date)
        END AS pay_year, 
        CASE
            WHEN sltransactionsoverview.due_date < 'now'::text::date THEN date_part('week'::text, 'now'::text::date)
            ELSE date_part('week'::text, sltransactionsoverview.due_date)
        END AS pay_week
   FROM sltransactionsoverview
   LEFT JOIN slmaster_overview ON sltransactionsoverview.slmaster_id = slmaster_overview.id
  WHERE sltransactionsoverview.status::text <> 'P'::text
  ORDER BY sltransactionsoverview.customer, sltransactionsoverview.transaction_date, sltransactionsoverview.id;

CREATE OR REPLACE VIEW reports.sl_factored_transactions AS 
 SELECT sltransactionsoverview.customer, sltransactionsoverview.transaction_date, sltransactionsoverview.id, sltransactionsoverview.transaction_type, sltransactionsoverview.our_reference, sltransactionsoverview.ext_reference, sltransactionsoverview.gross_value, sltransactionsoverview.currency, sltransactionsoverview.rate, sltransactionsoverview.base_gross_value, sltransactionsoverview.description, slmaster_overview.sl_analysis, sltransactionsoverview.due_date, date_part('year'::text, sltransactionsoverview.transaction_date) AS year, date_part('month'::text, sltransactionsoverview.transaction_date) AS month
   FROM sltransactionsoverview
   LEFT JOIN slmaster_overview ON sltransactionsoverview.slmaster_id = slmaster_overview.id
  WHERE slmaster_overview.sl_analysis::text = 'Factored'::text
  ORDER BY sltransactionsoverview.transaction_date, sltransactionsoverview.customer;

CREATE OR REPLACE VIEW reports.sl_ageddebtors AS 
 SELECT sltransactionsoverview.customer, sltransactionsoverview.transaction_date, sltransactionsoverview.id, sltransactionsoverview.transaction_type, sltransactionsoverview.our_reference, sltransactionsoverview.ext_reference, sltransactionsoverview.gross_value, sltransactionsoverview.currency, sltransactionsoverview.rate, sltransactionsoverview.base_gross_value, sltransactionsoverview.description, sltransactionsoverview.payment_terms, slmaster_overview.sl_analysis, age(sltransactionsoverview.transaction_date::timestamp with time zone) AS age, 
        CASE
            WHEN age(sltransactionsoverview.transaction_date::timestamp with time zone) <= '1 mon'::interval THEN sltransactionsoverview.gross_value
            ELSE 0::numeric
        END AS current_gross, 
        CASE
            WHEN age(sltransactionsoverview.transaction_date::timestamp with time zone) > '1 mon'::interval AND age(sltransactionsoverview.transaction_date::timestamp with time zone) <= '2 mons'::interval THEN sltransactionsoverview.gross_value
            ELSE 0::numeric
        END AS m1_gross, 
        CASE
            WHEN age(sltransactionsoverview.transaction_date::timestamp with time zone) > '2 mons'::interval AND age(sltransactionsoverview.transaction_date::timestamp with time zone) <= '3 mons'::interval THEN sltransactionsoverview.gross_value
            ELSE 0::numeric
        END AS m2_gross, 
        CASE
            WHEN age(sltransactionsoverview.transaction_date::timestamp with time zone) > '3 mons'::interval AND age(sltransactionsoverview.transaction_date::timestamp with time zone) <= '4 mons'::interval THEN sltransactionsoverview.gross_value
            ELSE 0::numeric
        END AS m3_gross, 
        CASE
            WHEN age(sltransactionsoverview.transaction_date::timestamp with time zone) > '4 mons'::interval AND age(sltransactionsoverview.transaction_date::timestamp with time zone) <= '5 mons'::interval THEN sltransactionsoverview.gross_value
            ELSE 0::numeric
        END AS m4_gross, 
        CASE
            WHEN age(sltransactionsoverview.transaction_date::timestamp with time zone) > '5 mons'::interval THEN sltransactionsoverview.gross_value
            ELSE 0::numeric
        END AS m5_gross, 
        CASE
            WHEN age(sltransactionsoverview.transaction_date::timestamp with time zone) <= '1 mon'::interval THEN sltransactionsoverview.base_gross_value
            ELSE 0::numeric
        END AS current_base, 
        CASE
            WHEN age(sltransactionsoverview.transaction_date::timestamp with time zone) > '1 mon'::interval AND age(sltransactionsoverview.transaction_date::timestamp with time zone) <= '2 mons'::interval THEN sltransactionsoverview.base_gross_value
            ELSE 0::numeric
        END AS m1_base, 
        CASE
            WHEN age(sltransactionsoverview.transaction_date::timestamp with time zone) > '2 mons'::interval AND age(sltransactionsoverview.transaction_date::timestamp with time zone) <= '3 mons'::interval THEN sltransactionsoverview.base_gross_value
            ELSE 0::numeric
        END AS m2_base, 
        CASE
            WHEN age(sltransactionsoverview.transaction_date::timestamp with time zone) > '3 mons'::interval AND age(sltransactionsoverview.transaction_date::timestamp with time zone) <= '4 mons'::interval THEN sltransactionsoverview.base_gross_value
            ELSE 0::numeric
        END AS m3_base, 
        CASE
            WHEN age(sltransactionsoverview.transaction_date::timestamp with time zone) > '4 mons'::interval AND age(sltransactionsoverview.transaction_date::timestamp with time zone) <= '5 mons'::interval THEN sltransactionsoverview.base_gross_value
            ELSE 0::numeric
        END AS m4_base, 
        CASE
            WHEN age(sltransactionsoverview.transaction_date::timestamp with time zone) > '5 mons'::interval THEN sltransactionsoverview.base_gross_value
            ELSE 0::numeric
        END AS m5_base
   FROM sltransactionsoverview
   LEFT JOIN slmaster_overview ON sltransactionsoverview.slmaster_id = slmaster_overview.id
  WHERE sltransactionsoverview.status::text <> 'P'::text
  ORDER BY sltransactionsoverview.customer, sltransactionsoverview.transaction_date, sltransactionsoverview.id;

CREATE OR REPLACE VIEW reports.tax_eu_saleslist AS 
 SELECT si.id, si.invoice_number, si.sales_order_number, si.invoice_date, si.transaction_type, si.ext_reference
, si.currency_id, si.rate, si.settlement_discount, si.gross_value, si.tax_value, si.net_value
, si.twin_currency_id AS twin_currency, si.twin_rate, si.twin_gross_value, si.twin_tax_value, si.twin_net_value
, si.base_gross_value, si.base_tax_value, si.base_net_value, si.payment_term_id, si.due_date, si.status
, si.description, si.tax_status_id, si.delivery_note, si.despatch_date, si.date_printed, si.print_count, si.usercompanyid
, coy.name AS customer, cum.currency, twc.currency AS twin, syt.description AS payment_terms
, ts.description AS tax_status, coy.vatnumber
   FROM si_header si
   JOIN slmaster slm ON si.slmaster_id = slm.id
   JOIN company coy ON slm.company_id = coy.id
   LEFT JOIN sl_analysis sla ON slm.sl_analysis_id = sla.id
   JOIN cumaster cum ON si.currency_id = cum.id
   JOIN cumaster twc ON si.twin_currency_id = twc.id
   JOIN tax_statuses ts ON si.tax_status_id = ts.id
   JOIN syterms syt ON si.payment_term_id = syt.id
  WHERE ts.eu_tax = true;

CREATE OR REPLACE VIEW reports.tax_eu_despatches AS 
 SELECT sd.id, sd.despatch_number, sd.order_id, sd.slmaster_id, sd.despatch_date, sd.despatch_qty
, sd.orderline_id, sd.productline_id, sd.stuom_id, sd.stitem_id, sd.status, sd.usercompanyid, sd.cs_failurecode_id
, sd.invoice_number, sd.invoice_id, sd.despatch_action, c.name AS customer, sh.order_number
, (i.item_code::text || ' - '::text) || i.description::text AS stitem, u.uom_name, ts.description
   FROM so_despatchlines sd
   JOIN st_items i ON i.id = sd.stitem_id
   JOIN so_header sh ON sh.id = sd.order_id
   JOIN slmaster sl ON sl.id = sd.slmaster_id
   JOIN company c ON sl.company_id = c.id
   JOIN tax_statuses ts ON ts.id = sl.tax_status_id
   JOIN st_uoms u ON u.id = sd.stuom_id
  WHERE ts.eu_tax = true AND sd.status::text <> 'X'::text;

--
-- ******************************** Purchase Ledger Suppliers ********************************
--
-- 1) Alter 'name' column to 'payee_name'
-- 2) Add 'credit_limit' column to plmaster
-- 3) Copy value of company.creditlimit to plmaster.credit_limit
-- 4) Set default value of plmaster.credit_limit = 0
-- 5) Update slmaster to set plmaster.credit_limit =0 where it is null
-- 6) Set plmaster.credit_limit as not null

DROP VIEW validate.validate_pl;

DROP VIEW validate.validate_pl_to_gl;

DROP VIEW reports.tax_eu_arrivals;

DROP VIEW reports.my_po_linesoverview;

DROP VIEW reports.my_po_receivedoverview;

DROP VIEW reports.po_supplierperformance;

DROP VIEW reports.po_stockreceivedoverview;

DROP VIEW reports.pl_openitems;

DROP VIEW pl_aged_creditors_summary;

DROP VIEW reports.pl_agedcreditors;

DROP VIEW tax_eu_arrivals;

DROP VIEW pl_aged_creditors_overview;

DROP VIEW pi_headeroverview;

DROP VIEW po_structure_lines;

DROP VIEW po_receivedoverview;

DROP VIEW po_productlines_overview;

DROP VIEW po_no_auth_user;

DROP VIEW po_auth_requisitions;

DROP VIEW po_linesoverview;

DROP VIEW po_headeroverview;

DROP VIEW pltransactionsoverview;

DROP VIEW plmaster_overview;

ALTER TABLE plmaster RENAME COLUMN name TO payee_name;

ALTER TABLE plmaster ADD COLUMN credit_limit numeric;
ALTER TABLE plmaster ALTER COLUMN credit_limit SET DEFAULT 0;

UPDATE plmaster
   SET credit_limit = (SELECT creditlimit
                         FROM company
                        WHERE id = plmaster.company_id);

UPDATE plmaster
   SET credit_limit = 0
 WHERE credit_limit is null;

ALTER TABLE plmaster ALTER COLUMN credit_limit SET NOT NULL;

CREATE OR REPLACE VIEW plmaster_overview AS 
 SELECT pl.id, pl.payee_name, pl.company_id, pl.currency_id, pl.remittance_advice, pl.payment_term_id
, pl.last_paid, pl.tax_status_id, pl.created, pl.usercompanyid, pl.outstanding_balance, pl.payment_type_id
, pl.cb_account_id, pl.receive_action, pl.order_method, pl.email_order_id, pl.email_remittance_id, pl.local_sort_code
, pl.local_account_number, pl.local_bank_name_address, pl.overseas_iban_number, pl.overseas_bic_code
, pl.overseas_account_number, pl.overseas_bank_name_address, pl.sort_code, pl.account_number
, pl.bank_name_address, pl.iban_number, pl.bic_code, pl.createdby, pl.alteredby, pl.lastupdated
, c.name, sy.name AS payment_type, cu.currency
   FROM plmaster pl
   JOIN company c ON pl.company_id = c.id
   JOIN sypaytypes sy ON sy.id = pl.payment_type_id
   JOIN cumaster cu ON cu.id = pl.currency_id;

CREATE OR REPLACE VIEW pltransactionsoverview AS 
 SELECT plt.id, plt.transaction_date, plt.transaction_type, plt.status, plt.our_reference, plt.ext_reference
, plt.currency_id, plt.rate, plt.gross_value, plt.tax_value, plt.net_value, plt.twin_currency_id, plt.twin_rate
, plt.twin_tax_value, plt.twin_net_value, plt.twin_gross_value, plt.base_tax_value, plt.base_gross_value
, plt.base_net_value, plt.payment_term_id, plt.due_date, plt.cross_ref, plt.os_value, plt.twin_os_value
, plt.base_os_value, plt.description, plt.usercompanyid, plt.plmaster_id, plt.created, plt.createdby, plt.alteredby
, plt.lastupdated, plt.original_due_date, plt.for_payment, plm.payee_name, c.name AS supplier, plm.payment_type_id
, plm.company_id, cum.currency, twc.currency AS twin, syt.description AS payment_terms
, syp.name AS payment_type
   FROM pltransactions plt
   JOIN plmaster plm ON plt.plmaster_id = plm.id
   JOIN company c ON plm.company_id = c.id
   JOIN cumaster cum ON plt.currency_id = cum.id
   JOIN cumaster twc ON plt.twin_currency_id = twc.id
   JOIN sypaytypes syp ON plm.payment_type_id = syp.id
   JOIN syterms syt ON plt.payment_term_id = syt.id;

CREATE OR REPLACE VIEW po_headeroverview AS 
 SELECT po.id, po.order_number, po.plmaster_id, po.del_address_id, po.order_date, po.due_date, po.ext_reference
, po.currency_id, po.rate, po.net_value, po.twin_currency_id, po.twin_rate, po.twin_net_value, po.base_net_value
, po.type, po.status, po.description, po.usercompanyid, po.date_authorised, po.raised_by, po.authorised_by
, po.created, po.owner, po.lastupdated, po.alteredby, plm.payee_name, c.name AS supplier, cum.currency
, twc.currency AS twin_currency, pr.username AS raised_by_person, pa.username AS authorised_by_person
, p.name AS project
   FROM po_header po
   JOIN plmaster plm ON po.plmaster_id = plm.id
   JOIN company c ON plm.company_id = c.id
   JOIN cumaster cum ON po.currency_id = cum.id
   JOIN cumaster twc ON po.twin_currency_id = twc.id
   JOIN users pr ON po.raised_by::text = pr.username::text
   LEFT JOIN users pa ON po.authorised_by::text = pa.username::text
   LEFT JOIN projects p ON po.project_id = p.id;

CREATE OR REPLACE VIEW po_linesoverview AS 
 SELECT pl.id, pl.order_id, pl.line_number, pl.productline_id, pl.stuom_id, pl.item_description, pl.order_qty
, pl.price, pl.currency_id, pl.rate, pl.net_value, pl.twin_currency_id, pl.twin_rate, pl.twin_net_value, pl.base_net_value
, pl.glaccount_id, pl.glcentre_id, pl.line_discount, pl.os_qty, pl.revised_qty, pl.del_qty, pl.due_delivery_date
, pl.actual_delivery_date, pl.gr_note, pl.status, pl.usercompanyid, pl.stitem_id, pl.tax_rate_id, pl.created
, pl.createdby, pl.alteredby, pl.lastupdated, pl.description, ph.due_date, ph.order_date, ph.order_number
, ph.plmaster_id, ph.receive_action, ph.type, ph.net_value AS order_value, cu.currency
, (glc.cost_centre::text || ' - '::text) || glc.description::text AS glcentre
, (gla.account::text || ' - '::text) || gla.description::text AS glaccount, ph.status AS order_status
, plm.payee_name, c.name AS supplier, (i.item_code::text || ' - '::text) || i.description::text AS stitem, u.uom_name
   FROM po_lines pl
   JOIN gl_centres glc ON glc.id = pl.glcentre_id
   JOIN gl_accounts gla ON gla.id = pl.glaccount_id
   JOIN cumaster cu ON cu.id = pl.currency_id
   JOIN po_header ph ON ph.id = pl.order_id
   JOIN plmaster plm ON ph.plmaster_id = plm.id
   JOIN company c ON plm.company_id = c.id
   LEFT JOIN st_items i ON i.id = pl.stitem_id
   LEFT JOIN st_uoms u ON u.id = pl.stuom_id;

CREATE OR REPLACE VIEW po_auth_requisitions AS 
 SELECT h.id, a.order_number, h.status, a.username, h.order_date, h.due_date, h.payee_name, h.supplier
   FROM ( SELECT o.order_number, a.username, count(*) AS authlines
           FROM po_linesum o
      JOIN po_authlist a ON a.glaccount_id = o.glaccount_id AND a.glcentre_id = o.glcentre_id
     WHERE o.value <= a.order_limit AND o.type::text = 'R'::text
     GROUP BY o.order_number, a.username) a
   JOIN ( SELECT o.order_number, count(*) AS totallines
           FROM po_linesum o
          GROUP BY o.order_number) b ON a.order_number = b.order_number
   JOIN po_headeroverview h ON h.order_number = a.order_number
  WHERE a.authlines = b.totallines;

CREATE OR REPLACE VIEW po_no_auth_user AS 
 SELECT h.id, h.order_number, h.order_date, h.due_date, h.payee_name, h.supplier, h.status
   FROM po_headeroverview h
   LEFT JOIN po_auth_requisitions a ON a.order_number = h.order_number
  WHERE h.type::text = 'R'::text AND a.username IS NULL;

CREATE OR REPLACE VIEW po_productlines_overview AS 
 SELECT po.id, po.glaccount_id, po.glcentre_id, po.plmaster_id, po.stitem_id, po.stuom_id
, po.supplier_product_code, po.description, po.price, po.usercompanyid, po.tax_rate_id, po.currency_id
, po.created, po.createdby, po.alteredby, po.lastupdated, po.start_date, po.end_date
, plm.payee_name, c.name AS supplier, uom.uom_name
, (st.item_code::text || ' - '::text) || st.description::text AS stitem, gla.account AS glaccount
, glc.cost_centre AS glcentre, cur.currency
   FROM po_product_lines po
   LEFT JOIN plmaster plm ON po.plmaster_id = plm.id
   LEFT JOIN company c ON plm.company_id = c.id
   LEFT JOIN st_items st ON po.stitem_id = st.id
   LEFT JOIN st_uoms uom ON po.stuom_id = uom.id
   JOIN cumaster cur ON po.currency_id = cur.id
   JOIN gl_accounts gla ON po.glaccount_id = gla.id
   JOIN gl_centres glc ON po.glcentre_id = glc.id;

CREATE OR REPLACE VIEW po_receivedoverview AS 
 SELECT pr.id, pr.gr_number, pr.order_id, pr.plmaster_id, pr.received_date, pr.received_qty, pr.orderline_id
, pr.productline_id, pr.stuom_id, pr.stitem_id, pr.status, pr.usercompanyid, pr.item_description, pr.delivery_note
, pr.net_value, pr.tax_rate_id, pr.invoice_number, pr.invoice_id, pr.received_by, pr.currency, pr.created, pr.createdby
, pr.alteredby, pr.lastupdated, s.payee_name, c.name AS supplier, ph.order_number
, (i.item_code::text || ' - '::text) || i.description::text AS stitem, u.uom_name
   FROM po_receivedlines pr
   LEFT JOIN st_items i ON i.id = pr.stitem_id
   JOIN po_header ph ON ph.id = pr.order_id
   JOIN plmaster s ON s.id = pr.plmaster_id
   JOIN company c ON s.company_id = c.id
   JOIN st_uoms u ON u.id = pr.stuom_id;

CREATE OR REPLACE VIEW po_structure_lines AS 
 SELECT pl.id, ph.due_date, ph.order_number, ph.plmaster_id, ph.receive_action, pl.order_id, pl.line_number
, pl.productline_id, pl.stuom_id, pl.item_description, pl.order_qty, pl.price, pl.currency_id, pl.rate, pl.net_value
, pl.twin_currency_id, pl.twin_rate, pl.twin_net_value, pl.base_net_value, pl.glaccount_id, pl.glcentre_id
, pl.line_discount, pl.os_qty, pl.revised_qty, pl.del_qty, pl.due_delivery_date, pl.actual_delivery_date, pl.gr_note
, pl.status, pl.usercompanyid, pl.stitem_id, pl.tax_rate_id, plm.payee_name, c.name AS supplier
, (i.item_code::text || ' - '::text) || i.description::text AS stitem, u.uom_name, i.item_code, st.ststructure_id
, st.qty, st.qty * pl.os_qty::double precision AS required, st.uom_id
   FROM po_lines pl
   JOIN po_header ph ON ph.id = pl.order_id
   JOIN plmaster plm ON ph.plmaster_id = plm.id
   JOIN company c ON plm.company_id = c.id
   JOIN mf_structures st ON st.stitem_id = pl.stitem_id AND pl.due_delivery_date >= st.start_date AND (pl.due_delivery_date <= st.end_date OR st.end_date IS NULL)
   JOIN st_items i ON i.id = st.stitem_id
   JOIN st_uoms u ON pl.stuom_id = u.id;

CREATE OR REPLACE VIEW pi_headeroverview AS 
 SELECT pi.id, pi.invoice_number, pi.purchase_order_id, pi.purchase_order_number, pi.our_reference, pi.plmaster_id
, pi.invoice_date, pi.transaction_type, pi.ext_reference, pi.currency_id, pi.rate, pi.gross_value, pi.tax_value
, pi.tax_status_id, pi.net_value, pi.twin_currency_id, pi.twin_rate, pi.twin_gross_value, pi.twin_tax_value
, pi.twin_net_value, pi.base_gross_value, pi.base_tax_value, pi.base_net_value, pi.payment_term_id, pi.due_date
, pi.status, pi.description, pi.auth_date, pi.auth_by, pi.usercompanyid, pi.original_due_date, pi.created, pi.createdby
, pi.alteredby, pi.lastupdated, plm.payee_name, c.name AS supplier, cum.currency, twc.currency AS twin
, syt.description AS payment_terms
   FROM pi_header pi
   JOIN plmaster plm ON pi.plmaster_id = plm.id
   JOIN company c ON plm.company_id = c.id
   JOIN cumaster cum ON pi.currency_id = cum.id
   JOIN cumaster twc ON pi.twin_currency_id = twc.id
   JOIN syterms syt ON pi.payment_term_id = syt.id;

CREATE OR REPLACE VIEW tax_eu_arrivals AS 
 SELECT por.id, por.received_date, por.received_qty, por.item_description, por.delivery_note, por.invoice_number
, plm.payee_name, c.name AS supplier, poh.order_number, uom.uom_name, por.usercompanyid
   FROM po_receivedlines por
   JOIN po_header poh ON poh.id = por.order_id
   JOIN plmaster plm ON plm.id = por.plmaster_id
   JOIN company c ON plm.company_id = c.id
   JOIN tax_statuses tst ON tst.id = plm.tax_status_id AND tst.eu_tax = true
   LEFT JOIN st_uoms uom ON por.stuom_id = uom.id
  ORDER BY por.received_date, plm.payee_name, por.item_description;

CREATE OR REPLACE VIEW pl_aged_creditors_overview AS 
 SELECT (((t.plmaster_id::text || '-'::text) || t.our_reference::text) || '-'::text) || t.transaction_type::text AS id
, t.plmaster_id, t.supplier, date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone
, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) AS age
, t.usercompanyid, sum(t.base_gross_value) AS value, t.our_reference, t.transaction_type
   FROM pltransactionsoverview t
  WHERE t.status::text <> 'P'::text
  GROUP BY t.supplier, t.plmaster_id, date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)), t.usercompanyid, t.our_reference, t.transaction_type
  ORDER BY t.supplier, t.plmaster_id, date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone));

CREATE OR REPLACE VIEW pl_aged_creditors_summary AS 
 SELECT date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) AS id, t.usercompanyid, sum(t.base_gross_value) AS value
   FROM pltransactionsoverview t
  WHERE t.status::text <> 'P'::text
  GROUP BY date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)), t.usercompanyid
  ORDER BY date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone));

CREATE OR REPLACE VIEW validate.validate_pl AS 
 SELECT plmaster_overview.name, plmaster_overview.id, plmaster_overview.currency, plmaster_overview.outstanding_balance - (( SELECT sum(pltransactionsoverview.gross_value) AS sum
           FROM pltransactionsoverview
          WHERE plmaster_overview.id = pltransactionsoverview.plmaster_id AND pltransactionsoverview.status::text <> 'P'::text)) AS difference
   FROM plmaster_overview
  GROUP BY plmaster_overview.name, plmaster_overview.id, plmaster_overview.currency, plmaster_overview.outstanding_balance - (( SELECT sum(pltransactionsoverview.gross_value) AS sum
           FROM pltransactionsoverview
          WHERE plmaster_overview.id = pltransactionsoverview.plmaster_id AND pltransactionsoverview.status::text <> 'P'::text))
  ORDER BY plmaster_overview.name;

CREATE OR REPLACE VIEW reports.po_supplierperformance AS 
 SELECT pr.gr_number, pr.received_date, pl.due_delivery_date, pr.received_date - pl.due_delivery_date AS dayslate
, (pr.received_date - pl.due_delivery_date) <= 0 AS ontime, pr.received_qty, pl.revised_qty
, pl.revised_qty - pr.received_qty AS shortage, (pl.revised_qty - pr.received_qty) <= 0::numeric AS infull
, pr.usercompanyid, pr.item_description, pr.delivery_note, pr.net_value, s.payee_name, c.name AS supplier
, ph.order_number, (i.item_code::text || ' - '::text) || i.description::text AS stitem, u.uom_name
   FROM po_receivedlines pr
   LEFT JOIN st_items i ON i.id = pr.stitem_id
   JOIN po_lines pl ON pl.id = pr.orderline_id
   JOIN po_header ph ON ph.id = pr.order_id
   JOIN plmaster s ON s.id = pr.plmaster_id
   JOIN company c ON s.company_id = c.id
   JOIN st_uoms u ON u.id = pr.stuom_id
  ORDER BY pr.gr_number;

CREATE OR REPLACE VIEW reports.po_stockreceivedoverview AS 
 SELECT pr.id, pr.gr_number, pr.order_id, pr.plmaster_id, pr.received_date, pr.received_qty, pr.orderline_id
, pil.order_line_id, pr.productline_id, pr.stuom_id, pr.stitem_id, pr.status, pr.usercompanyid, pr.item_description
, pr.delivery_note, pr.net_value, pil.net_value AS invoice_value, pil.base_net_value AS base_invoice_value
, pr.tax_rate_id, pr.invoice_number, pr.invoice_id, pil.invoice_id AS pinvoice_id, pr.received_by, pr.currency
, s.payee_name, c.name AS supplier, ph.order_number, (i.item_code::text || ' - '::text) || i.description::text AS stitem
, u.uom_name, date_part('year'::text, pr.received_date) AS year, date_part('month'::text
, pr.received_date) AS period
   FROM po_receivedlines pr
   JOIN st_items i ON i.id = pr.stitem_id
   JOIN po_header ph ON ph.id = pr.order_id
   JOIN plmaster s ON s.id = pr.plmaster_id
   JOIN company c ON s.company_id = c.id
   JOIN st_uoms u ON u.id = pr.stuom_id
   LEFT JOIN pi_lines pil ON pr.invoice_id = pil.invoice_id AND pr.orderline_id = pil.order_line_id;

CREATE OR REPLACE VIEW reports.pl_openitems AS 
 SELECT pltransactionsoverview.supplier, pltransactionsoverview.status, pltransactionsoverview.transaction_date, pltransactionsoverview.id, pltransactionsoverview.transaction_type, pltransactionsoverview.our_reference, pltransactionsoverview.ext_reference, pltransactionsoverview.gross_value, pltransactionsoverview.currency, pltransactionsoverview.rate, pltransactionsoverview.base_gross_value, pltransactionsoverview.description, pltransactionsoverview.payment_terms, plmaster_overview.payment_type, pltransactionsoverview.due_date, date_part('year'::text, pltransactionsoverview.due_date) AS due_year, date_part('week'::text, pltransactionsoverview.due_date) AS due_week, 
        CASE
            WHEN pltransactionsoverview.due_date < 'now'::text::date THEN date_part('year'::text, 'now'::text::date)
            ELSE date_part('year'::text, pltransactionsoverview.due_date)
        END AS pay_year, 
        CASE
            WHEN pltransactionsoverview.due_date < 'now'::text::date THEN date_part('week'::text, 'now'::text::date)
            ELSE date_part('week'::text, pltransactionsoverview.due_date)
        END AS pay_week
   FROM pltransactionsoverview
   LEFT JOIN plmaster_overview ON pltransactionsoverview.plmaster_id = plmaster_overview.id
  WHERE pltransactionsoverview.status::text <> 'P'::text
  ORDER BY pltransactionsoverview.supplier, pltransactionsoverview.transaction_date, pltransactionsoverview.id;

CREATE OR REPLACE VIEW reports.pl_agedcreditors AS 
 SELECT pltransactionsoverview.supplier, pltransactionsoverview.transaction_date, pltransactionsoverview.id, pltransactionsoverview.transaction_type, pltransactionsoverview.our_reference, pltransactionsoverview.ext_reference, pltransactionsoverview.gross_value, pltransactionsoverview.currency, pltransactionsoverview.rate, pltransactionsoverview.base_gross_value, pltransactionsoverview.description, pltransactionsoverview.payment_terms, plmaster_overview.payment_type, age(pltransactionsoverview.transaction_date::timestamp with time zone) AS age, 
        CASE
            WHEN age(pltransactionsoverview.transaction_date::timestamp with time zone) <= '1 mon'::interval THEN pltransactionsoverview.gross_value
            ELSE 0::numeric
        END AS current_gross, 
        CASE
            WHEN age(pltransactionsoverview.transaction_date::timestamp with time zone) > '1 mon'::interval AND age(pltransactionsoverview.transaction_date::timestamp with time zone) <= '2 mons'::interval THEN pltransactionsoverview.gross_value
            ELSE 0::numeric
        END AS m1_gross, 
        CASE
            WHEN age(pltransactionsoverview.transaction_date::timestamp with time zone) > '2 mons'::interval AND age(pltransactionsoverview.transaction_date::timestamp with time zone) <= '3 mons'::interval THEN pltransactionsoverview.gross_value
            ELSE 0::numeric
        END AS m2_gross, 
        CASE
            WHEN age(pltransactionsoverview.transaction_date::timestamp with time zone) > '3 mons'::interval AND age(pltransactionsoverview.transaction_date::timestamp with time zone) <= '4 mons'::interval THEN pltransactionsoverview.gross_value
            ELSE 0::numeric
        END AS m3_gross, 
        CASE
            WHEN age(pltransactionsoverview.transaction_date::timestamp with time zone) > '4 mons'::interval AND age(pltransactionsoverview.transaction_date::timestamp with time zone) <= '5 mons'::interval THEN pltransactionsoverview.gross_value
            ELSE 0::numeric
        END AS m4_gross, 
        CASE
            WHEN age(pltransactionsoverview.transaction_date::timestamp with time zone) > '5 mons'::interval THEN pltransactionsoverview.gross_value
            ELSE 0::numeric
        END AS m5_gross, 
        CASE
            WHEN age(pltransactionsoverview.transaction_date::timestamp with time zone) <= '1 mon'::interval THEN pltransactionsoverview.base_gross_value
            ELSE 0::numeric
        END AS current_base, 
        CASE
            WHEN age(pltransactionsoverview.transaction_date::timestamp with time zone) > '1 mon'::interval AND age(pltransactionsoverview.transaction_date::timestamp with time zone) <= '2 mons'::interval THEN pltransactionsoverview.base_gross_value
            ELSE 0::numeric
        END AS m1_base, 
        CASE
            WHEN age(pltransactionsoverview.transaction_date::timestamp with time zone) > '2 mons'::interval AND age(pltransactionsoverview.transaction_date::timestamp with time zone) <= '3 mons'::interval THEN pltransactionsoverview.base_gross_value
            ELSE 0::numeric
        END AS m2_base, 
        CASE
            WHEN age(pltransactionsoverview.transaction_date::timestamp with time zone) > '3 mons'::interval AND age(pltransactionsoverview.transaction_date::timestamp with time zone) <= '4 mons'::interval THEN pltransactionsoverview.base_gross_value
            ELSE 0::numeric
        END AS m3_base, 
        CASE
            WHEN age(pltransactionsoverview.transaction_date::timestamp with time zone) > '4 mons'::interval AND age(pltransactionsoverview.transaction_date::timestamp with time zone) <= '5 mons'::interval THEN pltransactionsoverview.base_gross_value
            ELSE 0::numeric
        END AS m4_base, 
        CASE
            WHEN age(pltransactionsoverview.transaction_date::timestamp with time zone) > '5 mons'::interval THEN pltransactionsoverview.base_gross_value
            ELSE 0::numeric
        END AS m5_base
   FROM pltransactionsoverview
   LEFT JOIN plmaster_overview ON pltransactionsoverview.plmaster_id = plmaster_overview.id
  WHERE pltransactionsoverview.status::text <> 'P'::text
  ORDER BY pltransactionsoverview.supplier, pltransactionsoverview.transaction_date, pltransactionsoverview.id;

CREATE OR REPLACE VIEW reports.my_po_receivedoverview AS 
 SELECT pr.id, pr.gr_number, pr.order_id, pr.plmaster_id, pr.received_date, pr.received_qty, pr.orderline_id
, pr.productline_id, pr.stuom_id, pr.stitem_id, pr.status, pr.usercompanyid, pr.item_description, pr.delivery_note
, pr.net_value, pr.tax_rate_id, pr.invoice_number, pr.invoice_id, pr.received_by, pr.currency
, s.payee_name, c.name AS supplier, ph.order_number, (i.item_code::text || ' - '::text) || i.description::text AS stitem
, u.uom_name, (gla.account::text || ' - '::text) || gla.description::text AS glaccount
, (glc.cost_centre::text || ' - '::text) || glc.description::text AS glcentre
, date_part('year'::text, pr.received_date) AS year, date_part('month'::text, pr.received_date) AS period
   FROM po_receivedlines pr
   LEFT JOIN st_items i ON i.id = pr.stitem_id
   JOIN po_header ph ON ph.id = pr.order_id
   JOIN plmaster s ON s.id = pr.plmaster_id
   JOIN company c ON s.company_id = c.id
   JOIN st_uoms u ON u.id = pr.stuom_id
   JOIN po_lines pl ON pl.id = pr.orderline_id
   LEFT JOIN gl_accounts gla ON pl.glaccount_id = gla.id
   LEFT JOIN gl_centres glc ON pl.glcentre_id = glc.id
  WHERE pr.status::text <> 'I'::text;

CREATE OR REPLACE VIEW reports.my_po_linesoverview AS 
 SELECT ph.due_date, ph.order_date, ph.order_number, pl.line_number, ph.type, ph.net_value AS order_value
, pl.item_description, pl.order_qty, pl.price, cu.currency, pl.rate, pl.net_value, pl.base_net_value
, gl_summaries.summary, gl_analysis.analysis, pl.glcentre_id
, (glc.cost_centre::text || ' - '::text) || glc.description::text AS glcentre, pl.glaccount_id
, (gla.account::text || ' - '::text) || gla.description::text AS glaccount, pl.line_discount, pl.os_qty, pl.revised_qty
, pl.del_qty, pl.due_delivery_date, pl.actual_delivery_date, pl.status, ph.status AS order_status, pl.usercompanyid
, plm.payee_name, c.name AS supplier, (i.item_code::text || ' - '::text) || i.description::text AS stitem, u.uom_name
, date_part('year'::text, pl.due_delivery_date) AS due_year
, date_part('week'::text, pl.due_delivery_date) AS due_week
   FROM po_lines pl
   JOIN gl_centres glc ON glc.id = pl.glcentre_id
   JOIN gl_accounts gla ON gla.id = pl.glaccount_id
   JOIN cumaster cu ON cu.id = pl.currency_id
   JOIN po_header ph ON ph.id = pl.order_id
   JOIN plmaster plm ON ph.plmaster_id = plm.id
   JOIN company c ON plm.company_id = c.id
   LEFT JOIN st_items i ON i.id = pl.stitem_id
   LEFT JOIN st_uoms u ON u.id = pl.stuom_id
   LEFT JOIN gl_analysis ON gla.glanalysis_id = gl_analysis.id
   LEFT JOIN gl_summaries ON gl_analysis.glsummary_id = gl_summaries.id
  WHERE pl.status::text = 'A'::text;

CREATE OR REPLACE VIEW reports.tax_eu_arrivals AS 
 SELECT pr.id, pr.gr_number, pr.order_id, pr.plmaster_id, pr.received_date, pr.received_qty, pr.orderline_id
, pr.productline_id, pr.stuom_id, pr.stitem_id, pr.status, pr.usercompanyid, pr.item_description, pr.delivery_note
, pr.net_value, pr.tax_rate_id, pr.invoice_number, pr.invoice_id, pr.received_by, pr.currency
, s.payee_name, c.name AS supplier, ph.order_number, (i.item_code::text || ' - '::text) || i.description::text AS stitem
, u.uom_name, ts.description
   FROM po_receivedlines pr
   LEFT JOIN st_items i ON i.id = pr.stitem_id
   JOIN po_header ph ON ph.id = pr.order_id
   JOIN plmaster s ON s.id = pr.plmaster_id
   JOIN company c ON s.company_id = c.id
   JOIN st_uoms u ON u.id = pr.stuom_id
   JOIN po_lines pl ON pl.id = pr.orderline_id
   JOIN tax_statuses ts ON ts.id = s.tax_status_id
  WHERE ts.eu_tax = true;

CREATE OR REPLACE VIEW validate.validate_pl_to_gl AS 
 SELECT glbalancesoverview.glaccount_id, glbalancesoverview.account, glbalancesoverview.glcentre_id, glbalancesoverview.centre, sum(glbalancesoverview.value) AS balance_per_glbalances, ( SELECT sum(gltransactionsoverview.value) AS sum
           FROM gltransactionsoverview
          WHERE gltransactionsoverview.glaccount_id = glbalancesoverview.glaccount_id AND gltransactionsoverview.glcentre_id = glbalancesoverview.glcentre_id) AS balance_per_gltransactions, - (( SELECT sum(pltransactionsoverview.base_gross_value) AS sum
           FROM pltransactionsoverview
          WHERE pltransactionsoverview.status::text <> 'P'::text)) AS outstanding_per_pltransactions
   FROM glbalancesoverview
  WHERE glbalancesoverview.glaccount_id = (( SELECT gl_accounts.id
           FROM gl_accounts
          WHERE gl_accounts.id::text = ((( SELECT gl_params.paramvalue_id
                   FROM gl_params
                  WHERE gl_params.paramdesc::text = 'Purchase Ledger Control Account'::text))::text))) AND glbalancesoverview.glcentre_id = (( SELECT gl_centres.id
           FROM gl_centres
          WHERE gl_centres.id::text = ((( SELECT gl_params.paramvalue_id
                   FROM gl_params
                  WHERE gl_params.paramdesc::text = 'Balance Sheet Cost Centre'::text))::text))) AND glbalancesoverview.glperiods_id >= 14
  GROUP BY glbalancesoverview.glaccount_id, glbalancesoverview.account, glbalancesoverview.glcentre_id, glbalancesoverview.centre;

--
-- ******************************** Company ********************************
--
-- 1) Drop 'creditlimit' column

DROP VIEW companyoverview;

ALTER TABLE company DROP COLUMN creditlimit;

CREATE OR REPLACE VIEW companyoverview AS 
 SELECT c.*
, pa.address_id, a.street1, a.street2, a.street3, a.town, a.county, a.countrycode, a.postcode
, cm.contact AS phone
   FROM company c
   LEFT JOIN party p ON c.party_id = p.id
   LEFT JOIN partyaddress pa ON p.id = pa.party_id AND pa.main
   LEFT JOIN address a ON a.id = pa.address_id
   LEFT JOIN party_contact_methods pcm ON p.id = pcm.party_id AND pcm.main AND pcm.type::text = 'T'::text
   LEFT JOIN contact_methods cm ON cm.id = pcm.contactmethod_id
  WHERE NOT (EXISTS ( SELECT sc.id
   FROM system_companies sc
  WHERE c.id = sc.company_id));

