-- DROP VIEW pl_aged_creditors_overview;

CREATE OR REPLACE VIEW pl_aged_creditors_overview AS 
 SELECT (t.plmaster_id::text || t.our_reference::text) || t.transaction_type::text AS id, t.plmaster_id, t.supplier, date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) AS age, t.usercompanyid, sum(t.base_gross_value) AS value, t.our_reference, t.transaction_type
   FROM pltransactionsoverview t
  WHERE t.status::text <> 'P'::text
  GROUP BY t.supplier, t.plmaster_id, date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)), t.usercompanyid, t.our_reference, t.transaction_type
  ORDER BY t.supplier, t.plmaster_id, date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone));


-- DROP VIEW sl_aged_debtors_overview;

CREATE OR REPLACE VIEW sl_aged_debtors_overview AS 
 SELECT (s.slmaster_id::text || s.our_reference::text) || s.transaction_type::text AS id, s.slmaster_id, s.customer, date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone)) AS age, s.usercompanyid, sum(s.base_gross_value) AS value, s.our_reference, s.transaction_type
   FROM sltransactionsoverview s
  WHERE s.status::text <> 'P'::text
  GROUP BY s.customer, s.slmaster_id, date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone)), s.usercompanyid, s.our_reference, s.transaction_type
  ORDER BY s.customer, s.slmaster_id, date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone));
