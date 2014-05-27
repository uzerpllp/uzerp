--
-- $Revision: 1.4 $
--

--
-- Purchase Ledger
--

CREATE SEQUENCE pl_allocation_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;
  
ALTER TABLE pl_allocation_id_seq OWNER TO "www-data";

CREATE TABLE pl_allocation_details
(
  id bigserial NOT NULL,
  allocation_id integer NOT NULL,
  transaction_id integer NOT NULL,
  payment_value numeric NOT NULL,
  payment_id integer,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT pl_allocation_details_pkey PRIMARY KEY (id),
  CONSTRAINT pl_allocation_details_payment_id_fkey FOREIGN KEY (payment_id)
      REFERENCES pl_payments (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT pl_allocation_details_transaction_id_fkey FOREIGN KEY (transaction_id)
      REFERENCES pltransactions (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT pl_allocation_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

ALTER TABLE pl_allocation_details OWNER TO "www-data";

CREATE OR REPLACE VIEW pl_allocation_details_overview AS 
 SELECT pad.id , pad.allocation_id, pad.transaction_id, pad.payment_value, pad.payment_id
 , plt.transaction_date, plt.transaction_type, plt.status, plt.our_reference
 , plt.ext_reference, plt.currency_id, plt.rate, plt.gross_value, plt.tax_value, plt.net_value
 , plt.twin_currency_id, plt.twin_rate, plt.twin_tax_value, plt.twin_net_value
 , plt.twin_gross_value, plt.base_tax_value, plt.base_gross_value, plt.base_net_value
 , plt.payment_term_id, plt.due_date, plt.cross_ref, plt.os_value, plt.twin_os_value
 , plt.base_os_value, plt.description, plt.usercompanyid, plt.plmaster_id, plt.created
 , plt.createdby, plt.alteredby, plt.lastupdated, plt.original_due_date, plt.for_payment
 , plt.payee_name, plt.supplier, plt.payment_type_id, plt.company_id, plt.sort_code
 , plt.account_number, plt.currency, plt.twin, plt.payment_terms, plt.payment_type
 , plt.email_order, plt.email_remittance, plt.remittance_advice
   FROM pl_allocation_details pad
   JOIN pltransactionsoverview plt ON plt.id = pad.transaction_id;

ALTER TABLE pl_allocation_details_overview OWNER TO "www-data";

create table pl_temp_allocations
as select t.plmaster_id, p.id as payment_id, t.id as transaction_id, t.gross_value, p.usercompanyid
          from pl_payments p
          join pltransactions t on p.id = cast(t.cross_ref as numeric)
        union
        select t1.plmaster_id, p.id as payment_id, t2.id as transaction_id, t2.gross_value, p.usercompanyid
          from pl_payments p
          join pltransactions t1 on p.id = cast(t1.cross_ref as numeric)
          join pltransactions t2 on t1.id = cast(t2.cross_ref as numeric)
         order by 2, 1, 3;

alter table pl_temp_allocations
add column allocation_id int8;

create table pl_temp_allocation_ids
as
select plmaster_id, payment_id, nextval('pl_allocation_id_seq') as allocation_id, count
 from (select plmaster_id, payment_id, count(*) as count
         from pl_temp_allocations
        group by payment_id, plmaster_id
        order by payment_id, plmaster_id) alloc;

select *
  from pl_temp_allocation_ids
 order by allocation_id;
  
update pl_temp_allocations
   set allocation_id = (select allocation_id
                             from pl_temp_allocation_ids
                            where pl_temp_allocation_ids.payment_id=pl_temp_allocations.payment_id
                              and pl_temp_allocation_ids.plmaster_id=pl_temp_allocations.plmaster_id);

insert into pl_allocation_details
  (allocation_id, transaction_id, payment_value, payment_id, usercompanyid)
  select allocation_id, transaction_id, gross_value, payment_id, usercompanyid
    from pl_temp_allocations
   order by 1, 4,2;

drop table pl_temp_allocations;

drop table pl_temp_allocation_ids;

create table pl_temp_allocation_ids
as
select nextval('pl_allocation_id_seq') as allocation_id, plmaster_id, updated, count
  from (select plmaster_id, to_char(lastupdated, 'YYYYMMDDHH24') as updated, count(*) as count
          from pltransactions
         where status='P'
           AND cross_ref is null
         group by plmaster_id, to_char(lastupdated, 'YYYYMMDDHH24')
         order by 2,1) alloc;

insert into pl_allocation_details
  (allocation_id, transaction_id, payment_value, usercompanyid)
  select allocation_id, trans.id, gross_value, usercompanyid
    from pltransactions trans
    join pl_temp_allocation_ids alloc on alloc.plmaster_id=trans.plmaster_id
                                     and updated=to_char(trans.lastupdated, 'YYYYMMDDHH24')
   where status='P'
     AND cross_ref is null;

drop table pl_temp_allocation_ids;

ALTER TABLE pltransactions DROP COLUMN cross_ref;

DROP VIEW pl_aged_creditors_summary;

CREATE OR REPLACE VIEW pl_aged_creditors_summary AS 
 SELECT date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) AS id, t.usercompanyid, sum(t.base_os_value) AS value
   FROM pltransactionsoverview t
  WHERE t.status::text <> 'P'::text
  GROUP BY date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)), t.usercompanyid
  ORDER BY date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone));

ALTER TABLE pl_aged_creditors_summary OWNER TO "www-data";

DROP VIEW pl_aged_creditors_overview;

CREATE OR REPLACE VIEW pl_aged_creditors_overview AS 
 SELECT (((t.plmaster_id::text || '-'::text) || t.our_reference::text) || '-'::text) || t.transaction_type::text AS id, t.plmaster_id, t.supplier, date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) AS age, t.usercompanyid, sum(t.base_os_value) AS value, t.our_reference, t.transaction_type
   FROM pltransactionsoverview t
  WHERE t.status::text <> 'P'::text
  GROUP BY t.supplier, t.plmaster_id, date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)), t.usercompanyid, t.our_reference, t.transaction_type
  ORDER BY t.supplier, t.plmaster_id, date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone));

ALTER TABLE pl_aged_creditors_overview OWNER TO "www-data";

--
-- Sales Ledger
--

CREATE SEQUENCE sl_allocation_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;
  
ALTER TABLE sl_allocation_id_seq OWNER TO "www-data";

CREATE TABLE sl_allocation_details
(
  id bigserial NOT NULL,
  allocation_id integer NOT NULL,
  transaction_id integer NOT NULL,
  payment_value numeric NOT NULL,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT sl_allocation_details_pkey PRIMARY KEY (id),
  CONSTRAINT sl_allocation_details_transaction_id_fkey FOREIGN KEY (transaction_id)
      REFERENCES sltransactions (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT sl_allocation_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

ALTER TABLE sl_allocation_details OWNER TO "www-data";

CREATE OR REPLACE VIEW sl_allocation_details_overview AS 
 SELECT sad.id, sad.allocation_id, sad.transaction_id, sad.payment_value
      , slt.transaction_date, slt.transaction_type, slt.status, slt.our_reference
      , slt.ext_reference, slt.currency_id, slt.rate, slt.gross_value, slt.tax_value, slt.net_value
      , slt.twin_currency, slt.twin_rate, slt.twin_gross_value, slt.twin_tax_value, slt.twin_net_value
      , slt.base_gross_value, slt.base_tax_value, slt.base_net_value, slt.payment_term_id, slt.due_date
      , slt.cross_ref, slt.os_value, slt.twin_os_value, slt.base_os_value, slt.description
      , slt.usercompanyid, slt.slmaster_id, slt.customer, slt.currency, slt.twin, slt.payment_terms
   FROM sl_allocation_details sad
   JOIN sltransactionsoverview slt ON slt.id = sad.transaction_id;

ALTER TABLE sl_allocation_details_overview OWNER TO "www-data";

create table sl_temp_allocation_ids
as
select nextval('sl_allocation_id_seq') as allocation_id, slmaster_id, updated, count
  from (select slmaster_id, to_char(lastupdated, 'YYYYMMDDHH24') as updated, count(*) as count
          from sltransactions
         where status='P'
           AND cross_ref is null
         group by slmaster_id, to_char(lastupdated, 'YYYYMMDDHH24')
         order by 2,1) alloc;

insert into sl_allocation_details
  (allocation_id, transaction_id, payment_value, usercompanyid)
  select allocation_id, trans.id, gross_value, usercompanyid
    from sltransactions trans
    join sl_temp_allocation_ids alloc on alloc.slmaster_id=trans.slmaster_id
                                     and updated=to_char(trans.lastupdated, 'YYYYMMDDHH24')
   where status='P'
     AND cross_ref is null;

drop table sl_temp_allocation_ids;

ALTER TABLE sltransactions DROP COLUMN cross_ref;

DROP VIEW sl_aged_debtors_summary;

CREATE OR REPLACE VIEW sl_aged_debtors_summary AS 
 SELECT date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone
 , t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) AS id, t.usercompanyid, sum(t.base_os_value) AS value
   FROM sltransactionsoverview t
  WHERE t.status::text <> 'P'::text
  GROUP BY date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)), t.usercompanyid
  ORDER BY date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, t.transaction_date::timestamp with time zone));

ALTER TABLE sl_aged_debtors_summary OWNER TO "www-data";

DROP VIEW sl_aged_debtors_overview;

CREATE OR REPLACE VIEW sl_aged_debtors_overview AS 
 SELECT (((s.slmaster_id::text || '-'::text) || s.our_reference::text) || '-'::text) || s.transaction_type::text AS id, s.slmaster_id, s.customer, date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone)) AS age, s.usercompanyid, sum(s.base_os_value) AS value, s.our_reference, s.transaction_type
   FROM sltransactionsoverview s
  WHERE s.status::text <> 'P'::text
  GROUP BY s.customer, s.slmaster_id, date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone)), s.usercompanyid, s.our_reference, s.transaction_type
  ORDER BY s.customer, s.slmaster_id, date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone));

ALTER TABLE sl_aged_debtors_overview OWNER TO "www-data";

-- Sanity check
-- The following should return no rows
--

select allocation_id, sum(payment_value)
  from pl_allocation_details
 group by allocation_id
 having sum(payment_value)<>0
 order by allocation_id;
 
select payment_id, sum(payment_value)
  from pl_allocation_details
 where payment_id is not null
 group by payment_id
 having sum(payment_value)<>0
 order by payment_id;
 
select allocation_id, sum(payment_value)
  from sl_allocation_details
 group by allocation_id
 having sum(payment_value)<>0
 order by allocation_id;
 
--
-- Register module components
--
	    
insert into module_components
  ("name", "type", location, module_id)
  select 'plalloaction', 'M', m.location||'/models/PLAllocation.php', id
    from modules m
   where m.name='purchase_ledger';

insert into module_components
  ("name", "type", location, module_id)
  select 'plalloactioncollection', 'M', m.location||'/models/PLAllocationCollection.php', id
    from modules m
   where m.name='purchase_ledger';

--insert into module_components
--  ("name", "type", location, module_id)
--  select 'hsbc_bacs', 'R', m.location||'/reports/HSBC_BACS.php', id
--    from modules m
--   where m.name='purchase_ledger';

insert into module_components
  ("name", "type", location, module_id)
  select 'slalloaction', 'M', m.location||'/models/SLAllocation.php', id
    from modules m
   where m.name='sales_ledger';

insert into module_components
  ("name", "type", location, module_id)
  select 'slalloactioncollection', 'M', m.location||'/models/SLAllocationCollection.php', id
    from modules m
   where m.name='sales_ledger';
 