--
-- $Revision: 1.6 $
--

-- Table: syterms

-- Column: discount

ALTER TABLE syterms ALTER COLUMN discount SET DEFAULT 0;

-- Column: allow_discount_on_allocation

ALTER TABLE syterms ADD COLUMN allow_discount_on_allocation boolean;
ALTER TABLE syterms ALTER COLUMN allow_discount_on_allocation SET DEFAULT false;

UPDATE syterms
   SET allow_discount_on_allocation = TRUE
 WHERE discount > 0;

UPDATE syterms
   SET allow_discount_on_allocation = FALSE
 WHERE discount = 0;

ALTER TABLE syterms ALTER COLUMN allow_discount_on_allocation SET NOT NULL;

-- Column: pl_discount_glaccount_id

ALTER TABLE syterms ADD COLUMN pl_discount_glaccount_id integer;

-- Column: pl_discount_glcentre_id

ALTER TABLE syterms ADD COLUMN pl_discount_glcentre_id integer;

-- Foreign Key: syterms_pl_discount_glaccount_id_fkey

ALTER TABLE syterms
  ADD CONSTRAINT syterms_pl_discount_glaccount_id_fkey FOREIGN KEY (pl_discount_glaccount_id)
      REFERENCES gl_accounts (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

-- Foreign Key: syterms_pl_discount_glcentre_id_fkey

ALTER TABLE syterms
  ADD CONSTRAINT syterms_pl_discount_glcentre_id_fkey FOREIGN KEY (pl_discount_glcentre_id)
      REFERENCES gl_centres (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

-- Column: sl_discount_glaccount_id

ALTER TABLE syterms ADD COLUMN sl_discount_glaccount_id integer;

-- Column: sl_discount_glcentre_id

ALTER TABLE syterms ADD COLUMN sl_discount_glcentre_id integer;

-- Foreign Key: syterms_sl_discount_glaccount_id_fkey

ALTER TABLE syterms
  ADD CONSTRAINT syterms_sl_discount_glaccount_id_fkey FOREIGN KEY (sl_discount_glaccount_id)
      REFERENCES gl_accounts (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

-- Foreign Key: syterms_sl_discount_glcentre_id_fkey

ALTER TABLE syterms
  ADD CONSTRAINT syterms_sl_discount_glcentre_id_fkey FOREIGN KEY (sl_discount_glcentre_id)
      REFERENCES gl_centres (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

-- Column: pl_discount_description

ALTER TABLE syterms ADD COLUMN pl_discount_description character varying;

-- Column: sl_discount_description

ALTER TABLE syterms ADD COLUMN sl_discount_description character varying;

-- Table: pi_header

-- Column: settlement_discount

-- ALTER TABLE pi_header DROP COLUMN settlement_discount;

ALTER TABLE pi_header ADD COLUMN settlement_discount numeric;

UPDATE pi_header
   SET settlement_discount = 0.00;

ALTER TABLE pi_header ALTER COLUMN settlement_discount SET NOT NULL;
ALTER TABLE pi_header ALTER COLUMN settlement_discount SET DEFAULT 0.00;

-- View: pi_headeroverview

DROP VIEW po_product_invoices;

DROP VIEW pi_headeroverview;

CREATE OR REPLACE VIEW pi_headeroverview AS 
 SELECT pi.*
 , plm.payee_name
 , c.name AS supplier, cum.currency, twc.currency AS twin, syt.description AS payment_terms
 , COALESCE(pl.line_count, 0::bigint) AS line_count
   FROM pi_header pi
   LEFT JOIN ( SELECT pi_lines.invoice_id, count(*) AS line_count
           FROM pi_lines
          GROUP BY pi_lines.invoice_id) pl ON pl.invoice_id = pi.id
   JOIN plmaster plm ON pi.plmaster_id = plm.id
   JOIN company c ON plm.company_id = c.id
   JOIN cumaster cum ON pi.currency_id = cum.id
   JOIN cumaster twc ON pi.twin_currency_id = twc.id
   JOIN syterms syt ON pi.payment_term_id = syt.id;

ALTER TABLE pi_headeroverview OWNER TO "www-data";

CREATE OR REPLACE VIEW po_product_invoices AS 
 SELECT ph.id, ph.invoice_number, ph.our_reference, ph.plmaster_id, ph.invoice_date, ph.transaction_type, ph.ext_reference, ph.currency_id, ph.rate, ph.gross_value, ph.tax_value, ph.tax_status_id, ph.net_value, ph.twin_currency_id, ph.twin_rate, ph.twin_gross_value, ph.twin_tax_value, ph.twin_net_value, ph.base_gross_value, ph.base_tax_value, ph.base_net_value, ph.payment_term_id, ph.due_date, ph.status, ph.description, ph.auth_date, ph.auth_by, ph.usercompanyid, ph.original_due_date, ph.created, ph.createdby, ph.alteredby, ph.lastupdated, ph.payee_name, ph.supplier, ph.currency, ph.twin, ph.payment_terms, ph.line_count, pl.id AS invoiceline_id, pl.productline_id, ppl.productline_header_id
   FROM pi_headeroverview ph
   JOIN pi_lines pl ON ph.id = pl.invoice_id
   JOIN po_product_lines ppl ON ppl.id = pl.productline_id;

ALTER TABLE po_product_invoices OWNER TO "www-data";

-- Table: pltransactions

-- Column: include_discount

-- ALTER TABLE pltransactions DROP COLUMN include_discount;

ALTER TABLE pltransactions ADD COLUMN include_discount boolean;
ALTER TABLE pltransactions ALTER COLUMN include_discount SET DEFAULT FALSE;

UPDATE pltransactions
   SET include_discount = FALSE;

-- Drop views that use pltransactions or pltransactionsoverview

DROP VIEW pl_allocation_details_overview;

DROP VIEW pl_aged_creditors_summary;

DROP VIEW pl_aged_creditors_overview;

DROP VIEW validate.validate_pl_to_gl;

DROP VIEW validate.validate_pl;

-- View: pltransactionsoverview

DROP VIEW pltransactionsoverview;

CREATE OR REPLACE VIEW pltransactionsoverview AS 
 SELECT plt.*
 , c.name AS supplier
 , plm.payee_name, plm.payment_type_id, plm.company_id, plm.sort_code
 , plm.account_number , plm.remittance_advice
 , cum.currency, twc.currency AS twin
 , syp.name AS payment_type
 , syt.description AS payment_terms, syt.allow_discount_on_allocation
 , syt.pl_discount_glaccount_id, syt.pl_discount_glcentre_id, syt.pl_discount_description
 , p1.contact AS email_order, p2.contact AS email_remittance
   FROM pltransactions plt
   JOIN plmaster plm ON plt.plmaster_id = plm.id
   JOIN company c ON plm.company_id = c.id
   JOIN cumaster cum ON plt.currency_id = cum.id
   JOIN cumaster twc ON plt.twin_currency_id = twc.id
   JOIN sypaytypes syp ON plm.payment_type_id = syp.id
   JOIN syterms syt ON plt.payment_term_id = syt.id
   LEFT JOIN partycontactmethodoverview p1 ON p1.id = plm.email_order_id
   LEFT JOIN partycontactmethodoverview p2 ON p2.id = plm.email_remittance_id;

ALTER TABLE pltransactionsoverview OWNER TO "www-data";

-- View: pl_allocation_overview

-- DROP VIEW pl_allocation_overview;

CREATE OR REPLACE VIEW pl_allocation_overview AS 
 SELECT plt.*, pih.settlement_discount
   FROM pltransactionsoverview plt
   LEFT JOIN pi_header pih ON pih.invoice_number = cast(plt.our_reference as integer)
                          AND plt.transaction_type = 'I';

ALTER TABLE pl_allocation_overview OWNER TO "www-data";

-- Recreate views that use pltransactionsoverview

-- View: validate.validate_pl

CREATE OR REPLACE VIEW validate.validate_pl AS 
 SELECT plmaster_overview.name, plmaster_overview.id, plmaster_overview.currency, plmaster_overview.outstanding_balance - (( SELECT sum(pltransactionsoverview.gross_value) AS sum
           FROM pltransactionsoverview
          WHERE plmaster_overview.id = pltransactionsoverview.plmaster_id AND pltransactionsoverview.status::text <> 'P'::text)) AS difference
   FROM plmaster_overview
  GROUP BY plmaster_overview.name, plmaster_overview.id, plmaster_overview.currency, plmaster_overview.outstanding_balance - (( SELECT sum(pltransactionsoverview.gross_value) AS sum
           FROM pltransactionsoverview
          WHERE plmaster_overview.id = pltransactionsoverview.plmaster_id AND pltransactionsoverview.status::text <> 'P'::text))
  ORDER BY plmaster_overview.name;

ALTER TABLE validate.validate_pl OWNER TO "www-data";

-- View: validate.validate_pl_to_gl

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

ALTER TABLE validate.validate_pl_to_gl OWNER TO "www-data";

-- View: pl_aged_creditors_overview

CREATE OR REPLACE VIEW pl_aged_creditors_overview AS 
 SELECT (((t.plmaster_id::text || '-'::text) || t.our_reference::text) || '-'::text) || t.transaction_type::text AS id, t.plmaster_id, t.supplier, date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) AS age, t.usercompanyid, sum(t.base_os_value) AS value, t.our_reference, t.transaction_type
   FROM pltransactionsoverview t
  WHERE t.status::text <> 'P'::text
  GROUP BY t.supplier, t.plmaster_id, date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)), t.usercompanyid, t.our_reference, t.transaction_type
  ORDER BY t.supplier, t.plmaster_id, date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone));

ALTER TABLE pl_aged_creditors_overview OWNER TO "www-data";

-- View: pl_aged_creditors_summary

CREATE OR REPLACE VIEW pl_aged_creditors_summary AS 
 SELECT date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) AS id, t.usercompanyid, sum(t.base_os_value) AS value
   FROM pltransactionsoverview t
  WHERE t.status::text <> 'P'::text
  GROUP BY date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)), t.usercompanyid
  ORDER BY date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone));

ALTER TABLE pl_aged_creditors_summary OWNER TO "www-data";

-- View: pl_allocation_details_overview

CREATE OR REPLACE VIEW pl_allocation_details_overview AS 
 SELECT pad.id, pad.allocation_id, pad.transaction_id, pad.payment_value, pad.payment_id, plt.transaction_date, plt.transaction_type, plt.status, plt.our_reference, plt.ext_reference, plt.currency_id, plt.rate, plt.gross_value, plt.tax_value, plt.net_value, plt.twin_currency_id, plt.twin_rate, plt.twin_tax_value, plt.twin_net_value, plt.twin_gross_value, plt.base_tax_value, plt.base_gross_value, plt.base_net_value, plt.payment_term_id, plt.due_date, plt.cross_ref, plt.os_value, plt.twin_os_value, plt.base_os_value, plt.description, plt.usercompanyid, plt.plmaster_id, plt.created, plt.createdby, plt.alteredby, plt.lastupdated, plt.original_due_date, plt.for_payment, plt.payee_name, plt.supplier, plt.payment_type_id, plt.company_id, plt.sort_code, plt.account_number, plt.currency, plt.twin, plt.payment_terms, plt.payment_type, plt.email_order, plt.email_remittance, plt.remittance_advice, pad.created AS allocation_date
   FROM pl_allocation_details pad
   JOIN pltransactionsoverview plt ON plt.id = pad.transaction_id;

ALTER TABLE pl_allocation_details_overview OWNER TO "www-data";

-- View: sltransactionsoverview

-- Drop views that use sltransactionsoverview

DROP VIEW validate.validate_sl_to_gl;

DROP VIEW validate.validate_sl;

DROP VIEW sl_allocation_details_overview;

DROP VIEW sl_aged_debtors_summary;

DROP VIEW sl_aged_debtors_overview;

DROP VIEW IF EXISTS sl_allocation_overview;

DROP VIEW sltransactionsoverview;

CREATE OR REPLACE VIEW sltransactionsoverview AS 
 SELECT slt.id, slt.transaction_date, slt.transaction_type, slt.status, slt.our_reference, slt.ext_reference
 , slt.currency_id, slt.rate, slt.gross_value, slt.tax_value, slt.net_value, slt.twin_currency_id AS twin_currency
 , slt.twin_rate, slt.twin_gross_value, slt.twin_tax_value, slt.twin_net_value, slt.base_gross_value, slt.base_tax_value
 , slt.base_net_value, slt.payment_term_id, slt.due_date, slt.cross_ref, slt.os_value, slt.twin_os_value, slt.base_os_value
 , slt.description, slt.usercompanyid, slt.slmaster_id, slt.person_id
 , c.name AS customer, cum.currency
 , (p.firstname::text || ' '::text) || p.surname::text AS person, twc.currency AS twin
 , syt.description AS payment_terms, syt.discount, syt.allow_discount_on_allocation
 , syt.sl_discount_glaccount_id, syt.sl_discount_glcentre_id, syt.sl_discount_description
   FROM sltransactions slt
   JOIN slmaster slm ON slt.slmaster_id = slm.id
   JOIN company c ON c.id = slm.company_id
   LEFT JOIN person p ON p.id = slt.person_id
   JOIN cumaster cum ON slt.currency_id = cum.id
   JOIN cumaster twc ON slt.twin_currency_id = twc.id
   JOIN syterms syt ON slt.payment_term_id = syt.id;

ALTER TABLE sltransactionsoverview OWNER TO "www-data";

-- View: sl_allocation_overview

-- DROP VIEW sl_allocation_overview;

CREATE OR REPLACE VIEW sl_allocation_overview AS 
 SELECT slt.*, sih.settlement_discount
   FROM sltransactionsoverview slt
   LEFT JOIN si_header sih ON sih.invoice_number = cast(slt.our_reference as integer)
                          AND slt.transaction_type = 'I';

ALTER TABLE sl_allocation_overview OWNER TO "www-data";

-- Recreate views that use sltransactionsoverview

-- View: sl_aged_debtors_overview

CREATE OR REPLACE VIEW sl_aged_debtors_overview AS 
 SELECT (((s.slmaster_id::text || '-'::text) || s.our_reference::text) || '-'::text) || s.transaction_type::text AS id, s.slmaster_id, s.customer, date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone)) AS age, s.usercompanyid, sum(s.base_os_value) AS value, s.our_reference, s.transaction_type
   FROM sltransactionsoverview s
  WHERE s.status::text <> 'P'::text
  GROUP BY s.customer, s.slmaster_id, date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone)), s.usercompanyid, s.our_reference, s.transaction_type
  ORDER BY s.customer, s.slmaster_id, date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone));

ALTER TABLE sl_aged_debtors_overview OWNER TO "www-data";

-- View: sl_aged_debtors_summary

CREATE OR REPLACE VIEW sl_aged_debtors_summary AS 
 SELECT date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) AS id, t.usercompanyid, sum(t.base_os_value) AS value
   FROM sltransactionsoverview t
  WHERE t.status::text <> 'P'::text
  GROUP BY date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)), t.usercompanyid
  ORDER BY date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone));

ALTER TABLE sl_aged_debtors_summary OWNER TO "www-data";

-- View: sl_allocation_details_overview

CREATE OR REPLACE VIEW sl_allocation_details_overview AS 
 SELECT sad.id, slt.transaction_date, slt.transaction_type, slt.status, slt.our_reference, slt.ext_reference, slt.currency_id, slt.rate, slt.gross_value, slt.tax_value, slt.net_value, slt.twin_currency, slt.twin_rate, slt.twin_gross_value, slt.twin_tax_value, slt.twin_net_value, slt.base_gross_value, slt.base_tax_value, slt.base_net_value, slt.payment_term_id, slt.due_date, slt.cross_ref, slt.os_value, slt.twin_os_value, slt.base_os_value, slt.description, slt.usercompanyid, slt.slmaster_id, slt.customer, slt.currency, slt.twin, slt.payment_terms, sad.allocation_id, sad.transaction_id, sad.payment_value, sad.created AS allocation_date
   FROM sl_allocation_details sad
   JOIN sltransactionsoverview slt ON slt.id = sad.transaction_id;

ALTER TABLE sl_allocation_details_overview OWNER TO "www-data";

-- View: validate.validate_sl

CREATE OR REPLACE VIEW validate.validate_sl AS 
 SELECT slmaster_overview.name, slmaster_overview.currency, slmaster_overview.outstanding_balance - (( SELECT sum(sltransactionsoverview.gross_value) AS sum
           FROM sltransactionsoverview
          WHERE slmaster_overview.id = sltransactionsoverview.slmaster_id AND sltransactionsoverview.status::text = 'O'::text)) AS difference
   FROM slmaster_overview
  ORDER BY slmaster_overview.name;

ALTER TABLE validate.validate_sl OWNER TO "www-data";

-- View: validate.validate_sl_to_gl

CREATE OR REPLACE VIEW validate.validate_sl_to_gl AS 
 SELECT glbalancesoverview.glaccount_id, glbalancesoverview.account, glbalancesoverview.glcentre_id, glbalancesoverview.centre, sum(glbalancesoverview.value) AS balance_per_glbalances, ( SELECT sum(gltransactionsoverview.value) AS sum
           FROM gltransactionsoverview
          WHERE gltransactionsoverview.glaccount_id = glbalancesoverview.glaccount_id AND gltransactionsoverview.glcentre_id = glbalancesoverview.glcentre_id) AS balance_per_gltransactions, ( SELECT sum(sltransactionsoverview.base_gross_value) AS sum
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

ALTER TABLE validate.validate_sl_to_gl OWNER TO "www-data";

--
-- Update pltransactions
--
-- Set outstanding values to zero where they are fully paid
-- Corrects data due to bug in batch payments where os_value were not being zeroised on payment
--

update pltransactions t
   set os_value = 0
     , base_os_value = 0
     , twin_os_value = 0
 where status = 'P'
   and os_value <> 0
   AND EXISTS (select 1
                 from pl_allocation_details a 
                where a.transaction_id = t.id
                  and a.payment_value = t.gross_value);
