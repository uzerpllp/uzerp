DROP VIEW sdl.pl_openitems;

DROP VIEW sdl.pl_agedcreditors;

DROP VIEW pltransactionsoverview;

ALTER TABLE pltransactions ADD COLUMN for_payment bool;

CREATE OR REPLACE VIEW pltransactionsoverview AS 
 SELECT plt.id, plt.transaction_date, plt.transaction_type, plt.status, plt.our_reference, plt.ext_reference, plt.currency_id, plt.rate, plt.gross_value, plt.tax_value, plt.net_value, plt.twin_currency_id, plt.twin_rate, plt.twin_tax_value, plt.twin_net_value, plt.twin_gross_value, plt.base_tax_value, plt.base_gross_value, plt.base_net_value, plt.payment_term_id, plt.due_date, plt.cross_ref, plt.os_value, plt.twin_os_value, plt.base_os_value, plt.description, plt.usercompanyid, plt.plmaster_id, plt.created, plt.createdby, plt.alteredby, plt.lastupdated, plt.original_due_date, plt.for_payment, plm.name AS supplier, plm.payment_type_id, plm.company_id, cum.currency, twc.currency AS twin, syt.description AS payment_terms, syp.name AS payment_type
   FROM pltransactions plt
   JOIN plmaster plm ON plt.plmaster_id = plm.id
   JOIN cumaster cum ON plt.currency_id = cum.id
   JOIN cumaster twc ON plt.twin_currency_id = twc.id
   JOIN sypaytypes syp ON plm.payment_type_id = syp.id
   JOIN syterms syt ON plt.payment_term_id = syt.id;

CREATE TABLE pl_payments
(
  id bigserial NOT NULL,
  payment_date date NOT NULL DEFAULT now(),
  status varchar NOT NULL default 'N',
  source varchar NOT NULL,
  reference varchar NOT NULL,
  number_transactions int8 NOT NULL,
  payment_total numeric NOT NULL,
  cb_account_id int4 NOT NULL,
  currency_id int4 NOT NULL,
  payment_type_id int4 NOT NULL,
  hash_key varchar NOT NULL,
  remittance_printed boolean,
  remittance_date date,
  usercompanyid int8 NOT NULL,
  created timestamp DEFAULT now(),
  createdby varchar,
  alteredby varchar,
  lastupdated timestamp DEFAULT now(),
  CONSTRAINT pl_payments_pkey PRIMARY KEY (id),
  CONSTRAINT plmaster_cbaccount_fkey FOREIGN KEY (cb_account_id)
      REFERENCES cb_accounts (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT pl_payments_currency_id_fkey FOREIGN KEY (currency_id)
      REFERENCES cumaster (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT pl_payments_payment_type_id_fkey FOREIGN KEY (payment_type_id)
      REFERENCES sypaytypes (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT pl_payments_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

create view pl_payments_overview as
select p.*, c.currency, a.name as bank_account, t.name as payment_type
  from pl_payments p
  join cumaster c on c.id = p.currency_id
  join cb_accounts a on a.id = p.cb_account_id
  join sypaytypes t on t.id = p.payment_type_id;

CREATE OR REPLACE VIEW sdl.pl_openitems AS 
 SELECT pltransactionsoverview.supplier, pltransactionsoverview.transaction_date, pltransactionsoverview.id, pltransactionsoverview.transaction_type, pltransactionsoverview.our_reference, pltransactionsoverview.ext_reference, pltransactionsoverview.gross_value, pltransactionsoverview.currency, pltransactionsoverview.rate, pltransactionsoverview.base_gross_value, pltransactionsoverview.description, pltransactionsoverview.payment_terms, plmaster_overview.payment_type, pltransactionsoverview.due_date, date_part('year'::text, pltransactionsoverview.due_date) AS due_year, date_part('week'::text, pltransactionsoverview.due_date) AS due_week, 
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

CREATE OR REPLACE VIEW sdl.pl_agedcreditors AS 
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

ALTER TABLE sypaytypes ADD COLUMN method int4;

ALTER TABLE sypaytypes
  ADD CONSTRAINT sypaytypes_injector_classes_fkey FOREIGN KEY (method)
      REFERENCES injector_classes (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

CREATE OR REPLACE VIEW sypaytypesoverview AS 
 SELECT pt.id, pt.name, pt.usercompanyid, pt.method_id, ic.name AS method
   FROM sypaytypes pt
   LEFT JOIN injector_classes ic ON pt.method_id = ic.id;