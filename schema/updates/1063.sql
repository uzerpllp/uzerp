DROP VIEW validate.validate_sl;

DROP VIEW sdl.sl_openitems;

DROP VIEW sdl.sl_factored_transactions;

DROP VIEW sdl.sl_ageddebtors;

DROP VIEW slmaster_overview;

CREATE OR REPLACE VIEW slmaster_overview AS 
 SELECT sl.*
 , cu.currency
 , c.creditlimit
 , sy.name AS payment_type
 , sa.name AS sl_analysis
   FROM slmaster sl
   JOIN sypaytypes sy ON sy.id = sl.payment_type_id
   JOIN cumaster cu ON cu.id = sl.currency_id
   LEFT JOIN sl_analysis sa ON sa.id = sl.sl_analysis_id
   LEFT JOIN company c ON c.id = sl.company_id;

CREATE OR REPLACE VIEW validate.validate_sl AS 
 SELECT slmaster_overview.name, slmaster_overview.currency, slmaster_overview.outstanding_balance - (( SELECT sum(sltransactionsoverview.gross_value) AS sum
           FROM sltransactionsoverview
          WHERE slmaster_overview.id = sltransactionsoverview.slmaster_id AND sltransactionsoverview.status::text = 'O'::text)) AS difference
   FROM slmaster_overview
  ORDER BY slmaster_overview.name;

CREATE OR REPLACE VIEW sdl.sl_openitems AS 
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

ALTER TABLE sdl.sl_openitems OWNER TO "www-data";
GRANT SELECT, UPDATE, INSERT, DELETE, REFERENCES, TRIGGER ON TABLE sdl.sl_openitems TO "www-data";
GRANT SELECT ON TABLE sdl.sl_openitems TO "ooo-data";

CREATE OR REPLACE VIEW sdl.sl_factored_transactions AS 
 SELECT sltransactionsoverview.customer, sltransactionsoverview.transaction_date, sltransactionsoverview.id, sltransactionsoverview.transaction_type, sltransactionsoverview.our_reference, sltransactionsoverview.ext_reference, sltransactionsoverview.gross_value, sltransactionsoverview.currency, sltransactionsoverview.rate, sltransactionsoverview.base_gross_value, sltransactionsoverview.description, slmaster_overview.sl_analysis, sltransactionsoverview.due_date, date_part('year'::text, sltransactionsoverview.transaction_date) AS year, date_part('month'::text, sltransactionsoverview.transaction_date) AS month
   FROM sltransactionsoverview
   LEFT JOIN slmaster_overview ON sltransactionsoverview.slmaster_id = slmaster_overview.id
  WHERE slmaster_overview.sl_analysis::text = 'Factored'::text
  ORDER BY sltransactionsoverview.transaction_date, sltransactionsoverview.customer;

CREATE OR REPLACE VIEW sdl.sl_ageddebtors AS 
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

ALTER TABLE sdl.sl_ageddebtors OWNER TO postgres;
GRANT SELECT ON TABLE sdl.sl_ageddebtors TO "ooo-data";