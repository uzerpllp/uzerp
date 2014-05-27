--
-- $Revision: 1.4 $
--

CREATE TABLE sy_delivery_terms
(
  id serial NOT NULL,
  code character varying NOT NULL,
  description character varying NOT NULL,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT sy_delivery_terms_pkey PRIMARY KEY (id),
  CONSTRAINT sy_delivery_terms_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

ALTER TABLE syterms OWNER TO "www-data";

insert into sy_delivery_terms (code, description, usercompanyid)
select 'CPT', 'Carriage paid to', id from system_companies;
insert into sy_delivery_terms (code, description, usercompanyid)
select 'CIR', 'Carriage Insurance Paid to', id from system_companies;
insert into sy_delivery_terms (code, description, usercompanyid)
select 'CFR', 'Cost and Freight (C&F)', id from system_companies;
insert into sy_delivery_terms (code, description, usercompanyid)
select 'CIF', 'Cost, Insurance and Freight', id from system_companies;
insert into sy_delivery_terms (code, description, usercompanyid)
select 'DAF', 'Delivered at Frontier', id from system_companies;
insert into sy_delivery_terms (code, description, usercompanyid)
select 'DEQ', 'Delivered Ex Quay', id from system_companies;
insert into sy_delivery_terms (code, description, usercompanyid)
select 'DES', 'Delivered Ex Ship', id from system_companies;
insert into sy_delivery_terms (code, description, usercompanyid)
select 'DDU', 'Delivered Duty Unpaid', id from system_companies;
insert into sy_delivery_terms (code, description, usercompanyid)
select 'DDP', 'Delivered Duty Paid', id from system_companies;
insert into sy_delivery_terms (code, description, usercompanyid)
select 'EXW', 'Ex Works', id from system_companies;
insert into sy_delivery_terms (code, description, usercompanyid)
select 'FAS', 'Free Alongside Ship', id from system_companies;
insert into sy_delivery_terms (code, description, usercompanyid)
select 'FOB', 'Free on Board', id from system_companies;
insert into sy_delivery_terms (code, description, usercompanyid)
select 'FCA', 'Free Carrier', id from system_companies;
insert into sy_delivery_terms (code, description, usercompanyid)
select 'XXX', 'Other terms not listed', id from system_companies;

CREATE TABLE intrastat_trans_types
(
  id serial NOT NULL,
  code character varying NOT NULL,
  description character varying NOT NULL,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT intrastat_trans_types_pkey PRIMARY KEY (id),
  CONSTRAINT intrastat_trans_types_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

ALTER TABLE syterms OWNER TO "www-data";

insert into intrastat_trans_types (code, description, usercompanyid)
select '10', 'Sales', id from system_companies;
insert into intrastat_trans_types (code, description, usercompanyid)
select '16', 'Credits (no goods movement)', id from system_companies;
insert into intrastat_trans_types (code, description, usercompanyid)
select '17', 'Staged goods', id from system_companies;
insert into intrastat_trans_types (code, description, usercompanyid)
select '18', 'Staged goods', id from system_companies;
insert into intrastat_trans_types (code, description, usercompanyid)
select '20', 'Returns or replacements', id from system_companies;
insert into intrastat_trans_types (code, description, usercompanyid)
select '30', 'FOC', id from system_companies;
insert into intrastat_trans_types (code, description, usercompanyid)
select '40', 'Sub contract goods out', id from system_companies;
insert into intrastat_trans_types (code, description, usercompanyid)
select '50', 'Sub contract goods in', id from system_companies;
insert into intrastat_trans_types (code, description, usercompanyid)
select '70', 'Defence projects', id from system_companies;
insert into intrastat_trans_types (code, description, usercompanyid)
select '80', 'Building materials', id from system_companies;
insert into intrastat_trans_types (code, description, usercompanyid)
select '90', 'Other', id from system_companies;

ALTER TABLE plmaster ADD COLUMN delivery_term_id integer;

ALTER TABLE plmaster
  ADD CONSTRAINT plmaster_delivery_term_id_fkey FOREIGN KEY (delivery_term_id)
      REFERENCES sy_delivery_terms (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE po_header ADD COLUMN delivery_term_id integer;

ALTER TABLE po_header
  ADD CONSTRAINT po_header_delivery_term_id_fkey FOREIGN KEY (delivery_term_id)
      REFERENCES sy_delivery_terms (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE po_receivedlines ADD COLUMN net_mass numeric;
ALTER TABLE po_receivedlines ALTER COLUMN net_mass SET DEFAULT 0;

UPDATE po_receivedlines
   SET net_mass=0;
   
DROP VIEW tax_eu_arrivals;

CREATE OR REPLACE VIEW tax_eu_arrivals AS 
 SELECT por.id, por.received_date, por.received_qty, por.net_mass, por.item_description, por.delivery_note
 , por.invoice_number, plm.payee_name, c.name AS supplier, poh.order_number, uom.uom_name
 , sdt.code||' - '||sdt.description as delivery_terms 
 , por.usercompanyid
   FROM po_receivedlines por
   JOIN po_header poh ON poh.id = por.order_id
   JOIN plmaster plm ON plm.id = por.plmaster_id
   JOIN company c ON plm.company_id = c.id
   JOIN tax_statuses tst ON tst.id = plm.tax_status_id AND tst.eu_tax = true
   LEFT JOIN st_uoms uom ON por.stuom_id = uom.id
   LEFT JOIN sy_delivery_terms sdt ON poh.delivery_term_id = sdt.id
  ORDER BY por.received_date, plm.payee_name, por.item_description;

ALTER TABLE tax_eu_arrivals OWNER TO "www-data";

ALTER TABLE slmaster ADD COLUMN delivery_term_id integer;

ALTER TABLE slmaster
  ADD CONSTRAINT slmaster_delivery_term_id_fkey FOREIGN KEY (delivery_term_id)
      REFERENCES sy_delivery_terms (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE so_header ADD COLUMN delivery_term_id integer;

ALTER TABLE so_header
  ADD CONSTRAINT so_header_delivery_term_id_fkey FOREIGN KEY (delivery_term_id)
      REFERENCES sy_delivery_terms (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE so_despatchlines ADD COLUMN net_mass numeric;
ALTER TABLE so_despatchlines ALTER COLUMN net_mass SET DEFAULT 0;

UPDATE so_despatchlines
   SET net_mass=0;

DROP VIEW tax_eu_despatches;

CREATE OR REPLACE VIEW tax_eu_despatches AS 
 SELECT sod.id, sod.despatch_date, sod.despatch_qty, sod.net_mass, sol.item_description, sod.invoice_number
 , c.name AS customer, soh.order_number, uom.uom_name
 , sdt.code||' - '||sdt.description as delivery_terms , sod.usercompanyid
   FROM so_despatchlines sod
   JOIN so_header soh ON soh.id = sod.order_id
   JOIN so_lines sol ON sol.id = sod.orderline_id
   JOIN slmaster slm ON slm.id = sod.slmaster_id
   JOIN company c ON slm.company_id = c.id
   JOIN tax_statuses tst ON tst.id = slm.tax_status_id AND tst.eu_tax = true
   LEFT JOIN st_uoms uom ON sod.stuom_id = uom.id
   LEFT JOIN sy_delivery_terms sdt ON soh.delivery_term_id = sdt.id
  ORDER BY sod.despatch_date, c.name, sol.item_description;

ALTER TABLE tax_eu_despatches OWNER TO "www-data";

DROP VIEW tax_eu_saleslist;

CREATE OR REPLACE VIEW tax_eu_saleslist AS 
 SELECT si.id, si.invoice_number, si.sales_order_number, si.invoice_date, si.transaction_type, si.ext_reference, si.currency_id, si.rate, si.settlement_discount, 
        CASE
            WHEN si.transaction_type::text = 'C'::text THEN si.gross_value * (-1)::numeric
            ELSE si.gross_value
        END AS gross_value, 
        CASE
            WHEN si.transaction_type::text = 'C'::text THEN si.tax_value * (-1)::numeric
            ELSE si.tax_value
        END AS tax_value, 
        CASE
            WHEN si.transaction_type::text = 'C'::text THEN si.net_value * (-1)::numeric
            ELSE si.net_value
        END AS net_value, si.twin_currency_id AS twin_currency, si.twin_rate, 
        CASE
            WHEN si.transaction_type::text = 'C'::text THEN si.twin_gross_value * (-1)::numeric
            ELSE si.twin_gross_value
        END AS twin_gross_value, 
        CASE
            WHEN si.transaction_type::text = 'C'::text THEN si.twin_tax_value * (-1)::numeric
            ELSE si.twin_tax_value
        END AS twin_tax_value, 
        CASE
            WHEN si.transaction_type::text = 'C'::text THEN si.twin_net_value * (-1)::numeric
            ELSE si.twin_net_value
        END AS twin_net_value, 
        CASE
            WHEN si.transaction_type::text = 'C'::text THEN si.base_gross_value * (-1)::numeric
            ELSE si.base_gross_value
        END AS base_gross_value, 
        CASE
            WHEN si.transaction_type::text = 'C'::text THEN si.base_tax_value * (-1)::numeric
            ELSE si.base_tax_value
        END AS base_tax_value, 
        CASE
            WHEN si.transaction_type::text = 'C'::text THEN si.base_net_value * (-1)::numeric
            ELSE si.base_net_value
        END AS base_net_value, si.payment_term_id, si.due_date, si.status, si.description, si.tax_status_id, si.delivery_note, si.despatch_date, si.date_printed, si.print_count, si.usercompanyid, coy.name AS customer, cum.currency, twc.currency AS twin, syt.description AS payment_terms, ts.description AS tax_status, coy.vatnumber AS vat_number, cad.countrycode AS country
   FROM si_header si
   JOIN slmaster slm ON si.slmaster_id = slm.id
   JOIN company coy ON slm.company_id = coy.id
   JOIN companyaddress cad ON coy.party_id = cad.party_id
                          AND main is true
   LEFT JOIN sl_analysis sla ON slm.sl_analysis_id = sla.id
   JOIN cumaster cum ON si.currency_id = cum.id
   JOIN cumaster twc ON si.twin_currency_id = twc.id
   JOIN tax_statuses ts ON si.tax_status_id = ts.id
   JOIN syterms syt ON si.payment_term_id = syt.id
  WHERE ts.eu_tax = true;

ALTER TABLE tax_eu_saleslist OWNER TO "www-data";

insert into permissions
(permission, type, title, display, parent_id, position)
select 'setup', 'c', 'Setup', true, id, next.position
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='c'
           and parent_id = (select id
                              from permissions
                             where type='m'
                               and permission='ledger_setup')) as next
 where type='m'
   and permission='ledger_setup';

insert into permissions
(permission, type, description, title, display, parent_id, position, has_parameters)
select 'view', 'a', 'ledger setup', 'intrastat_trans_types', true, id, coalesce(next.position,1), true
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='setup'
                               and parent_id = (select id
			                          from permissions
                                                 where type='m'
                                                   and permission='ledger_setup'))) as next
 where type='c'
   and permission='setup'
   and parent_id = (select id
		      from permissions
                     where type='m'
                       and permission='ledger_setup');

insert into permission_parameters
(permissionsid, name, value)
select id, 'option', 'intrastat_trans_types'
  from permissions
 where type='a'
   and permission='view'
   and title='intrastat_trans_types';

insert into permissions
(permission, type, description, title, display, parent_id, position, has_parameters)
select 'view', 'a', 'ledger setup', 'delivery_terms', true, id, coalesce(next.position,1), true
  from permissions
     , (select max(position)+1 as position
          from permissions
         where type='a'
           and parent_id = (select id
                              from permissions
                             where type='c'
                               and permission='setup'
                               and parent_id = (select id
			                          from permissions
                                                 where type='m'
                                                   and permission='ledger_setup'))) as next
 where type='c'
   and permission='setup'
   and parent_id = (select id
		      from permissions
                     where type='m'
                       and permission='ledger_setup');

insert into permission_parameters
(permissionsid, name, value)
select id, 'option', 'sy_delivery_terms'
  from permissions
 where type='a'
   and permission='view'
   and title='sy_delivery_terms';

update permissions
   set permission='vieweuarrivals'
 where permission='vieweutransactions';