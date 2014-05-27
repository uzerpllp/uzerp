--
-- $Revision: 1.2 $
--

--

ALTER TABLE sltransactions ADD COLUMN person_id bigint;

ALTER TABLE sltransactions
  ADD CONSTRAINT sltransactions_person_id_fkey FOREIGN KEY (person_id)
      REFERENCES person (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

--
-- update sl transactions with person from linked invoices
--

UPDATE sltransactions slt
   SET person_id = (SELECT person_id
                      FROM si_header sih
                     WHERE person_id IS NOT NULL
                       AND slt.our_reference = sih.invoice_number||'')
 WHERE slt.transaction_type = 'I'
   AND EXISTS (SELECT person_id
                 FROM si_header sih
                WHERE person_id IS NOT NULL
                  AND slt.our_reference = sih.invoice_number||'');

--
-- recreate sltransactionsoverview
--
-- need to deal with dependencies
--

DROP VIEW sl_allocation_details_overview;

DROP VIEW sl_aged_debtors_summary;

DROP VIEW sl_aged_debtors_overview;

DROP VIEW validate.validate_sl_to_gl;

DROP VIEW validate.validate_sl;

DROP VIEW sltransactionsoverview;

CREATE OR REPLACE VIEW sltransactionsoverview AS 
 SELECT slt.id, slt.transaction_date, slt.transaction_type, slt.status, slt.our_reference
 , slt.ext_reference, slt.currency_id, slt.rate, slt.gross_value, slt.tax_value, slt.net_value
 , slt.twin_currency_id AS twin_currency, slt.twin_rate, slt.twin_gross_value, slt.twin_tax_value
 , slt.twin_net_value, slt.base_gross_value, slt.base_tax_value, slt.base_net_value
 , slt.payment_term_id, slt.due_date, slt.cross_ref, slt.os_value, slt.twin_os_value, slt.base_os_value
 , slt.description, slt.usercompanyid, slt.slmaster_id, slt.person_id
 , c.name AS customer, cum.currency
 , p.firstname || p.surname as person
 , twc.currency AS twin, syt.description AS payment_terms
   FROM sltransactions slt
   JOIN slmaster slm ON slt.slmaster_id = slm.id
   JOIN company c ON c.id = slm.company_id
   LEFT JOIN person p ON p.id = slt.person_id
   JOIN cumaster cum ON slt.currency_id = cum.id
   JOIN cumaster twc ON slt.twin_currency_id = twc.id
   JOIN syterms syt ON slt.payment_term_id = syt.id;

ALTER TABLE sltransactionsoverview OWNER TO "www-data";

CREATE OR REPLACE VIEW validate.validate_sl AS 
 SELECT slmaster_overview.name, slmaster_overview.currency, slmaster_overview.outstanding_balance - (( SELECT sum(sltransactionsoverview.gross_value) AS sum
           FROM sltransactionsoverview
          WHERE slmaster_overview.id = sltransactionsoverview.slmaster_id AND sltransactionsoverview.status::text = 'O'::text)) AS difference
   FROM slmaster_overview
  ORDER BY slmaster_overview.name;

ALTER TABLE validate.validate_sl OWNER TO "www-data";

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

CREATE OR REPLACE VIEW sl_aged_debtors_overview AS 
 SELECT (((s.slmaster_id::text || '-'::text) || s.our_reference::text) || '-'::text) || s.transaction_type::text AS id, s.slmaster_id, s.customer, date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone)) AS age, s.usercompanyid, sum(s.base_os_value) AS value, s.our_reference, s.transaction_type
   FROM sltransactionsoverview s
  WHERE s.status::text <> 'P'::text
  GROUP BY s.customer, s.slmaster_id, date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone)), s.usercompanyid, s.our_reference, s.transaction_type
  ORDER BY s.customer, s.slmaster_id, date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone));

ALTER TABLE sl_aged_debtors_overview OWNER TO "www-data";

CREATE OR REPLACE VIEW sl_aged_debtors_summary AS 
 SELECT date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) AS id, t.usercompanyid, sum(t.base_os_value) AS value
   FROM sltransactionsoverview t
  WHERE t.status::text <> 'P'::text
  GROUP BY date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)), t.usercompanyid
  ORDER BY date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone));

ALTER TABLE sl_aged_debtors_summary OWNER TO "www-data";

CREATE OR REPLACE VIEW sl_allocation_details_overview AS 
 SELECT sad.id, slt.transaction_date, slt.transaction_type, slt.status, slt.our_reference, slt.ext_reference, slt.currency_id, slt.rate, slt.gross_value, slt.tax_value, slt.net_value, slt.twin_currency, slt.twin_rate, slt.twin_gross_value, slt.twin_tax_value, slt.twin_net_value, slt.base_gross_value, slt.base_tax_value, slt.base_net_value, slt.payment_term_id, slt.due_date, slt.cross_ref, slt.os_value, slt.twin_os_value, slt.base_os_value, slt.description, slt.usercompanyid, slt.slmaster_id, slt.customer, slt.currency, slt.twin, slt.payment_terms, sad.allocation_id, sad.transaction_id, sad.payment_value
   FROM sl_allocation_details sad
   JOIN sltransactionsoverview slt ON slt.id = sad.transaction_id;

ALTER TABLE sl_allocation_details_overview OWNER TO "www-data";


DROP VIEW cb_transactionsoverview;

CREATE OR REPLACE VIEW cb_transactionsoverview AS 
 SELECT cb.id, cb.cb_account_id, cb.source, cb.company_id, cb.ext_reference, cb.transaction_date, cb.currency_id
 , cb.twin_currency_id, cb.basecurrency_id, cb.description, cb.payment_type_id, cb.gross_value, cb.tax_value, cb.net_value
 , cb.twin_gross_value, cb.twin_tax_value, cb.twin_net_value, cb.base_gross_value, cb.base_tax_value, cb.base_net_value
 , cb.rate, cb.twin_rate, cb.status, cb.tax_rate_id, cb.tax_percentage, cb.usercompanyid, cb.statement_date
 , cb.statement_page, cb.person_id, cb.reference, cb.transaction_currency_id, cb.transaction_net_value
 , cb.transaction_tax_value, cb.created, cb.createdby, cb.alteredby, cb.lastupdated, a.name AS cb_account, c.currency
 , cmp.name AS company, (p.firstname::text || ' '::text) || p.surname::text AS person, sy.name AS payment_type
   FROM cb_transactions cb
   JOIN cb_accounts a ON a.id = cb.cb_account_id
   LEFT JOIN company cmp ON cmp.id = cb.company_id
   LEFT JOIN person p ON p.id = cb.person_id
   LEFT JOIN sypaytypes sy ON sy.id = cb.payment_type_id
   LEFT JOIN cumaster c ON c.id = cb.currency_id;

ALTER TABLE cb_transactionsoverview OWNER TO "www-data";

