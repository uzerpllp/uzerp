DROP VIEW pl_aged_creditors_overview;

DROP VIEW pl_aged_creditors_summary;

DROP VIEW reports.pl_agedcreditors;

DROP VIEW validate.validate_pl;

DROP VIEW reports.pl_openitems;

DROP VIEW validate.validate_pl_to_gl;

DROP VIEW pltransactionsoverview;

DROP VIEW plmaster_overview;

alter table plmaster
  alter column sort_code type character varying
 ,alter column account_number type character varying
 ,alter column overseas_account_number type character varying
 ,alter column local_sort_code type character varying
 ,alter column local_account_number type character varying;

CREATE OR REPLACE VIEW plmaster_overview AS 
 SELECT pl.id, pl.payee_name, pl.company_id, pl.currency_id, pl.remittance_advice, pl.payment_term_id, pl.last_paid, pl.tax_status_id, pl.created, pl.usercompanyid, pl.outstanding_balance, pl.payment_type_id, pl.cb_account_id, pl.receive_action, pl.order_method, pl.email_order_id, pl.email_remittance_id, pl.local_sort_code, pl.local_account_number, pl.local_bank_name_address, pl.overseas_iban_number, pl.overseas_bic_code, pl.overseas_account_number, pl.overseas_bank_name_address, pl.sort_code, pl.account_number, pl.bank_name_address, pl.iban_number, pl.bic_code, pl.createdby, pl.alteredby, pl.lastupdated, c.name, sy.name AS payment_type, cu.currency
   FROM plmaster pl
   JOIN company c ON pl.company_id = c.id
   JOIN sypaytypes sy ON sy.id = pl.payment_type_id
   JOIN cumaster cu ON cu.id = pl.currency_id;

CREATE OR REPLACE VIEW pltransactionsoverview AS 
 SELECT plt.id, plt.transaction_date, plt.transaction_type, plt.status, plt.our_reference
 , plt.ext_reference, plt.currency_id, plt.rate, plt.gross_value, plt.tax_value, plt.net_value
 , plt.twin_currency_id, plt.twin_rate, plt.twin_tax_value, plt.twin_net_value, plt.twin_gross_value
 , plt.base_tax_value, plt.base_gross_value, plt.base_net_value, plt.payment_term_id, plt.due_date
 , plt.cross_ref, plt.os_value, plt.twin_os_value, plt.base_os_value, plt.description, plt.usercompanyid
 , plt.plmaster_id, plt.created, plt.createdby, plt.alteredby, plt.lastupdated, plt.original_due_date
 , plt.for_payment, plm.payee_name, c.name AS supplier, plm.payment_type_id, plm.company_id
 , plm.sort_code
 , plm.account_number
 , cum.currency, twc.currency AS twin, syt.description AS payment_terms, syp.name AS payment_type
   FROM pltransactions plt
   JOIN plmaster plm ON plt.plmaster_id = plm.id
   JOIN company c ON plm.company_id = c.id
   JOIN cumaster cum ON plt.currency_id = cum.id
   JOIN cumaster twc ON plt.twin_currency_id = twc.id
   JOIN sypaytypes syp ON plm.payment_type_id = syp.id
   JOIN syterms syt ON plt.payment_term_id = syt.id;

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

GRANT SELECT ON TABLE reports.pl_agedcreditors TO "ooo-data";

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

GRANT SELECT ON TABLE reports.pl_openitems TO "ooo-data";

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

CREATE OR REPLACE VIEW validate.validate_pl AS 
 SELECT plmaster_overview.name, plmaster_overview.id, plmaster_overview.currency, plmaster_overview.outstanding_balance - (( SELECT sum(pltransactionsoverview.gross_value) AS sum
           FROM pltransactionsoverview
          WHERE plmaster_overview.id = pltransactionsoverview.plmaster_id AND pltransactionsoverview.status::text <> 'P'::text)) AS difference
   FROM plmaster_overview
  GROUP BY plmaster_overview.name, plmaster_overview.id, plmaster_overview.currency, plmaster_overview.outstanding_balance - (( SELECT sum(pltransactionsoverview.gross_value) AS sum
           FROM pltransactionsoverview
          WHERE plmaster_overview.id = pltransactionsoverview.plmaster_id AND pltransactionsoverview.status::text <> 'P'::text))
  ORDER BY plmaster_overview.name;

CREATE OR REPLACE VIEW pl_aged_creditors_overview AS 
 SELECT (((t.plmaster_id::text || '-'::text) || t.our_reference::text) || '-'::text) || t.transaction_type::text AS id, t.plmaster_id, t.supplier, date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) AS age, t.usercompanyid, sum(t.base_gross_value) AS value, t.our_reference, t.transaction_type
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

DROP VIEW pltransactionsoverview;

CREATE OR REPLACE VIEW pltransactionsoverview AS 
 SELECT plt.id, plt.transaction_date, plt.transaction_type, plt.status, plt.our_reference
 , plt.ext_reference, plt.currency_id, plt.rate, plt.gross_value, plt.tax_value, plt.net_value
 , plt.twin_currency_id, plt.twin_rate, plt.twin_tax_value, plt.twin_net_value
 , plt.twin_gross_value, plt.base_tax_value, plt.base_gross_value, plt.base_net_value
 , plt.payment_term_id, plt.due_date, plt.cross_ref, plt.os_value, plt.twin_os_value
 , plt.base_os_value, plt.description, plt.usercompanyid, plt.plmaster_id, plt.created
 , plt.createdby, plt.alteredby, plt.lastupdated, plt.original_due_date, plt.for_payment
 , plm.payee_name, c.name AS supplier, plm.payment_type_id, plm.company_id, plm.sort_code
 , plm.account_number, cum.currency, twc.currency AS twin
 , syt.description AS payment_terms, syp.name AS payment_type
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
