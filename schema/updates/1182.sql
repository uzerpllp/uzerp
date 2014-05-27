ALTER TABLE plmaster RENAME COLUMN invoice_method TO order_method;

DROP VIEW reports.pl_agedcreditors;

DROP VIEW reports.pl_openitems;

DROP VIEW validate.validate_pl;

DROP VIEW plmaster_overview;

CREATE OR REPLACE VIEW plmaster_overview AS 
 SELECT pl.*
, sy.name AS payment_type
, cu.currency
   FROM plmaster pl
   JOIN sypaytypes sy ON sy.id = pl.payment_type_id
   JOIN cumaster cu ON cu.id = pl.currency_id;

CREATE OR REPLACE VIEW validate.validate_pl AS 
 SELECT plmaster_overview.name, plmaster_overview.id, plmaster_overview.currency, plmaster_overview.outstanding_balance - (( SELECT sum(pltransactionsoverview.gross_value) AS sum
           FROM pltransactionsoverview
          WHERE plmaster_overview.id = pltransactionsoverview.plmaster_id AND pltransactionsoverview.status::text <> 'P'::text)) AS difference
   FROM plmaster_overview
  GROUP BY plmaster_overview.name, plmaster_overview.id, plmaster_overview.currency, plmaster_overview.outstanding_balance - (( SELECT sum(pltransactionsoverview.gross_value) AS sum
           FROM pltransactionsoverview
          WHERE plmaster_overview.id = pltransactionsoverview.plmaster_id AND pltransactionsoverview.status::text <> 'P'::text))
  ORDER BY plmaster_overview.name;

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
