CREATE TABLE output_header
(
  id bigserial NOT NULL,
  "type" character varying NOT NULL,
  "class" character varying NOT NULL,
  process character varying NOT NULL,
  emailtext character varying NOT NULL,
  printtype character varying NOT NULL,
  printer character varying NOT NULL,
  filename character varying NOT NULL,
  fieldnames boolean,
  fieldseparater character varying,
  textdelimiter character varying,
  processed boolean NOT NULL DEFAULT false,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT output_header_pkey PRIMARY KEY (id)
);

CREATE TABLE output_details
(
  id bigserial NOT NULL,
  output_header_id bigint NOT NULL,
  select_id bigint NOT NULL,
  description character varying NOT NULL,
  printaction character varying NOT NULL,
  email character varying,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT output_details_pkey PRIMARY KEY (id),
  CONSTRAINT output_details_header_id_fkey FOREIGN KEY (output_header_id)
      REFERENCES output_header (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

ALTER TABLE slmaster ADD COLUMN last_statement_date date;

DROP VIEW validate.validate_sl;

DROP VIEW reports.sl_openitems;

DROP VIEW reports.sl_factored_transactions;

DROP VIEW reports.sl_ageddebtors;

DROP VIEW slmaster_overview;

CREATE OR REPLACE VIEW slmaster_overview AS 
 SELECT sl.*
 , p1.contact as email_invoice, p2.contact as email_statement
 , c.name
 , cu.currency, sy.name AS payment_type, sa.name AS sl_analysis
   FROM slmaster sl
   JOIN company c ON c.id = sl.company_id
   JOIN sypaytypes sy ON sy.id = sl.payment_type_id
   JOIN cumaster cu ON cu.id = sl.currency_id
   LEFT JOIN sl_analysis sa ON sa.id = sl.sl_analysis_id
   LEFT JOIN partycontactmethodoverview p1 on p1.id = sl.email_invoice_id
   LEFT JOIN partycontactmethodoverview p2 on p2.id = sl.email_statement_id;

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

CREATE OR REPLACE VIEW validate.validate_sl AS 
 SELECT slmaster_overview.name, slmaster_overview.currency, slmaster_overview.outstanding_balance - (( SELECT sum(sltransactionsoverview.gross_value) AS sum
           FROM sltransactionsoverview
          WHERE slmaster_overview.id = sltransactionsoverview.slmaster_id AND sltransactionsoverview.status::text = 'O'::text)) AS difference
   FROM slmaster_overview
  ORDER BY slmaster_overview.name;

insert into permissions
(permission, type, title, display, parent_id, position)
select 'select_for_output', 'a', 'Output Statements', false, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='slcustomers')) as next
 where type='c'
   and permission='slcustomers';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'output_summary', 'a', 'Statements Summary', false, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='slcustomers')) as next
 where type='c'
   and permission='slcustomers';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'output_detail', 'a', 'Statement Details', false, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='slcustomers')) as next
 where type='c'
   and permission='slcustomers';