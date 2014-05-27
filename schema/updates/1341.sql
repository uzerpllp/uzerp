--
-- $Revision: 1.1 $
--

--
-- Purchase Ledger
--
DROP VIEW reports.pl_openitems;

DROP VIEW validate.validate_pl;

DROP VIEW reports.pl_agedcreditors;

DROP VIEW plmaster_overview;

CREATE OR REPLACE VIEW plmaster_overview AS 
 SELECT pl.id, pl.payee_name, pl.company_id, pl.currency_id
 , pl.remittance_advice, pl.payment_term_id, pl.last_paid
 , pl.tax_status_id, pl.created, pl.usercompanyid
 , pl.outstanding_balance, pl.payment_type_id, pl.cb_account_id
 , pl.receive_action, pl.order_method, pl.email_order_id
 , pl.email_remittance_id, pl.local_sort_code, pl.local_account_number
 , pl.local_bank_name_address, pl.overseas_iban_number
 , pl.overseas_bic_code, pl.overseas_account_number
 , pl.overseas_bank_name_address, pl.sort_code, pl.account_number
 , pl.bank_name_address, pl.iban_number, pl.bic_code, pl.createdby
 , pl.alteredby, pl.lastupdated, c.name, sy.name AS payment_type
 , st.description AS payment_term, cu.currency, p1.contact AS email_order
 , p2.contact AS email_remittance
   FROM plmaster pl
   JOIN company c ON pl.company_id = c.id
   JOIN sypaytypes sy ON sy.id = pl.payment_type_id
   JOIN syterms st ON st.id = pl.payment_term_id
   JOIN cumaster cu ON cu.id = pl.currency_id
   LEFT JOIN partycontactmethodoverview p1 ON p1.id = pl.email_order_id
   LEFT JOIN partycontactmethodoverview p2 ON p2.id = pl.email_remittance_id;

CREATE OR REPLACE VIEW validate.validate_pl AS 
 SELECT plmaster_overview.name, plmaster_overview.id, plmaster_overview.currency, plmaster_overview.outstanding_balance - (( SELECT sum(pltransactionsoverview.gross_value) AS sum
           FROM pltransactionsoverview
          WHERE plmaster_overview.id = pltransactionsoverview.plmaster_id AND pltransactionsoverview.status::text <> 'P'::text)) AS difference
   FROM plmaster_overview
  GROUP BY plmaster_overview.name, plmaster_overview.id, plmaster_overview.currency, plmaster_overview.outstanding_balance - (( SELECT sum(pltransactionsoverview.gross_value) AS sum
           FROM pltransactionsoverview
          WHERE plmaster_overview.id = pltransactionsoverview.plmaster_id AND pltransactionsoverview.status::text <> 'P'::text))
  ORDER BY plmaster_overview.name;

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

--
-- Sales Ledger
--
DROP VIEW validate.validate_sl;

DROP VIEW reports.sl_ageddebtors;

DROP VIEW reports.sl_factored_transactions;

DROP VIEW reports.sl_openitems;

DROP VIEW slmaster_overview;

CREATE OR REPLACE VIEW slmaster_overview AS 
 SELECT sl.id, sl.company_id, sl.currency_id, sl.statement
 , sl.payment_term_id, sl.last_paid, sl.tax_status_id, sl.created
 , sl.usercompanyid, sl.outstanding_balance, sl.payment_type_id
 , sl.cb_account_id, sl.despatch_action, sl.invoice_method
 , sl.email_invoice_id, sl.email_statement_id, sl.sl_analysis_id
 , sl.createdby, sl.alteredby, sl.lastupdated, sl.account_status
 , sl.so_price_type_id, sl.credit_limit, sl.last_statement_date
 , p1.contact AS email_invoice, p2.contact AS email_statement, c.name
 , cu.currency, st.description AS payment_term, sy.name AS payment_type, sa.name AS sl_analysis
   FROM slmaster sl
   JOIN company c ON c.id = sl.company_id
   JOIN sypaytypes sy ON sy.id = sl.payment_type_id
   JOIN syterms st ON st.id = sl.payment_term_id
   JOIN cumaster cu ON cu.id = sl.currency_id
   LEFT JOIN sl_analysis sa ON sa.id = sl.sl_analysis_id
   LEFT JOIN partycontactmethodoverview p1 ON p1.id = sl.email_invoice_id
   LEFT JOIN partycontactmethodoverview p2 ON p2.id = sl.email_statement_id;

CREATE OR REPLACE VIEW validate.validate_sl AS 
 SELECT slmaster_overview.name, slmaster_overview.currency, slmaster_overview.outstanding_balance - (( SELECT sum(sltransactionsoverview.gross_value) AS sum
           FROM sltransactionsoverview
          WHERE slmaster_overview.id = sltransactionsoverview.slmaster_id AND sltransactionsoverview.status::text = 'O'::text)) AS difference
   FROM slmaster_overview
  ORDER BY slmaster_overview.name;

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

GRANT SELECT ON TABLE reports.sl_ageddebtors TO "ooo-data";

CREATE OR REPLACE VIEW reports.sl_factored_transactions AS 
 SELECT sltransactionsoverview.customer, sltransactionsoverview.transaction_date, sltransactionsoverview.id, sltransactionsoverview.transaction_type, sltransactionsoverview.our_reference, sltransactionsoverview.ext_reference, sltransactionsoverview.gross_value, sltransactionsoverview.currency, sltransactionsoverview.rate, sltransactionsoverview.base_gross_value, sltransactionsoverview.description, slmaster_overview.sl_analysis, sltransactionsoverview.due_date, date_part('year'::text, sltransactionsoverview.transaction_date) AS year, date_part('month'::text, sltransactionsoverview.transaction_date) AS month
   FROM sltransactionsoverview
   LEFT JOIN slmaster_overview ON sltransactionsoverview.slmaster_id = slmaster_overview.id
  WHERE slmaster_overview.sl_analysis::text = 'Factored'::text
  ORDER BY sltransactionsoverview.transaction_date, sltransactionsoverview.customer;

GRANT SELECT ON TABLE reports.sl_factored_transactions TO "ooo-data";

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

GRANT SELECT ON TABLE reports.sl_openitems TO "ooo-data";


