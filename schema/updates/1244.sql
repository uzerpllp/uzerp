insert into permissions
(permission, type, title, display, position)
select 'edi', 'm', 'EDI', true, next.position
  from (select max(position)+1 as position
          from permissions
         where parent_id is null) as next;

insert into permissions
(permission, type, title, display, parent_id, position)
select 'externalsystems', 'c', 'External Systems', true, id, 1
  from permissions
 where type='m'
   and permission='edi';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'datamappings', 'c', 'Data Mappings', true, id, 2
  from permissions
 where type='m'
   and permission='edi';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'datamappingdetails', 'c', 'Data Mapping Details', false, id, 3
  from permissions
 where type='m'
   and permission='edi';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'datamappingrules', 'c', 'Data Mapping Rules', false, id, 4
  from permissions
 where type='m'
   and permission='edi';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'datadefinitions', 'c', 'Data Definitions', false, id, 5
  from permissions
 where type='m'
   and permission='edi';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'viewbyname', 'a', 'Import EDI Orders', true, id, 1
  from permissions
 where type='c'
   and permission='datadefinitions';

insert into permissions
(permission, type, title, display, parent_id, position)
select 'datadefinitiondetails', 'c', 'Data Definition Details', false, id, 6
  from permissions
 where type='m'
   and permission='edi';

DROP VIEW tax_eu_saleslist;

DROP VIEW companyaddressoverview;

DROP VIEW companyaddress;

DROP VIEW personaddress_overview;

DROP VIEW personaddress;

DROP VIEW personoverview;

DROP VIEW partyaddressoverview;

DROP VIEW reports.mi_salesoverview;

DROP VIEW companyoverview;

alter table address
alter column id type int;

CREATE OR REPLACE VIEW companyoverview AS 
 SELECT c.id, c.name, c.accountnumber, c.vatnumber, c.companynumber, c.website, c.employees, c.usercompanyid, c.parent_id, c.owner, c.assigned, c.created, c.lastupdated, c.alteredby, c.description, c.is_lead, c.party_id, c.classification_id, c.source_id, c.industry_id, c.status_id, c.rating_id, c.type_id, c.createdby, pa.address_id, a.street1, a.street2, a.street3, a.town, a.county, a.countrycode, a.postcode, cm.contact AS phone
   FROM company c
   LEFT JOIN party p ON c.party_id = p.id
   LEFT JOIN partyaddress pa ON p.id = pa.party_id AND pa.main
   LEFT JOIN address a ON a.id = pa.address_id
   LEFT JOIN party_contact_methods pcm ON p.id = pcm.party_id AND pcm.main AND pcm.type::text = 'T'::text
   LEFT JOIN contact_methods cm ON cm.id = pcm.contactmethod_id
  WHERE NOT (EXISTS ( SELECT sc.id
   FROM system_companies sc
  WHERE c.id = sc.company_id));

CREATE OR REPLACE VIEW partyaddressoverview AS 
 SELECT ((((((COALESCE(a.street1, ''::character varying)::text || ','::text) || (COALESCE(a.street2, ''::character varying)::text || ','::text)) || (COALESCE(a.street3, ''::character varying)::text || ','::text)) || (COALESCE(a.town, ''::character varying)::text || ','::text)) || (COALESCE(a.county, ''::character varying)::text || ','::text)) || (COALESCE(a.postcode, ''::character varying)::text || ','::text)) || COALESCE(a.countrycode, ''::character varying::bpchar)::text AS fulladdress, a.street1, a.street2, a.street3, a.town, a.county, a.postcode, a.countrycode, p.id, p.address_id, p.name, p.main, p.billing, p.shipping, p.payment, p.technical, p.party_id, p.parent_id, p.usercompanyid
   FROM partyaddress p
   JOIN address a ON p.address_id = a.id;

CREATE OR REPLACE VIEW personoverview AS 
 SELECT per.id, per.title, per.firstname, per.middlename, per.surname, per.suffix, per.department, per.jobtitle, per.dob, per.ni, per.marital, per.lang, per.company_id, per.owner, per.userdetail, per.reports_to, per.can_call, per.can_email, per.assigned_to, per.created, per.lastupdated, per.alteredby, per.usercompanyid, per.crm_source, per.party_id, (per.surname::text || ', '::text) || per.firstname::text AS name, pa.address_id, a.street1, a.street2, a.street3, a.town, a.county, a.countrycode, a.postcode, c.name AS company, c.accountnumber, cm.contact AS phone, m.contact AS mobile, e.contact AS email
   FROM person per
   LEFT JOIN company c ON per.company_id = c.id
   LEFT JOIN party p ON per.party_id = p.id
   LEFT JOIN partyaddress pa ON p.id = pa.party_id AND pa.main
   LEFT JOIN address a ON a.id = pa.address_id
   LEFT JOIN party_contact_methods pcm ON p.id = pcm.party_id AND pcm.main AND pcm.type::text = 'T'::text
   LEFT JOIN contact_methods cm ON cm.id = pcm.contactmethod_id
   LEFT JOIN party_contact_methods mcm ON p.id = mcm.party_id AND mcm.main AND mcm.type::text = 'M'::text
   LEFT JOIN contact_methods m ON m.id = mcm.contactmethod_id
   LEFT JOIN party_contact_methods ecm ON p.id = ecm.party_id AND ecm.main AND ecm.type::text = 'E'::text
   LEFT JOIN contact_methods e ON e.id = ecm.contactmethod_id;

CREATE OR REPLACE VIEW personaddress AS 
 SELECT pa.fulladdress, pa.street1, pa.street2, pa.street3, pa.town, pa.county, pa.postcode, pa.countrycode, pa.address_id AS id, pa.address_id, pa.name, pa.main, pa.billing, pa.shipping, pa.payment, pa.technical, pa.party_id, pa.parent_id, pe.id AS person_id
   FROM person pe
   JOIN party p ON p.id = pe.party_id AND p.type::text = 'Person'::text
   JOIN partyaddressoverview pa ON p.id = pa.party_id;

CREATE OR REPLACE VIEW personaddress_overview AS 
 SELECT personaddress.id, personaddress.fulladdress AS address, personaddress.countrycode, personaddress.person_id, personaddress.name, personaddress.main, personaddress.billing, personaddress.shipping, personaddress.payment, personaddress.technical
   FROM personaddress personaddress;

CREATE OR REPLACE VIEW companyaddress AS 
 SELECT a.id, a.street1, a.street2, a.street3, a.town, a.county, a.postcode, a.countrycode, pa.name, pa.main, pa.billing, pa.shipping, pa.payment, pa.technical, pa.party_id
   FROM address a
   JOIN partyaddress pa ON pa.address_id = a.id
   JOIN party p ON p.id = pa.party_id AND p.type::text = 'Company'::text;

CREATE OR REPLACE VIEW companyaddressoverview AS 
 SELECT ca.street1, ca.street2, ca.street3, ca.town, ca.county, ca.postcode, ca.countrycode, c.id AS company_id, ca.name, ca.main, ca.billing, ca.shipping, ca.payment, ca.technical, ca.id, c.name AS company, (((((((((((ca.street1::text || ', '::text) || COALESCE(ca.street2, ''::character varying)::text) || ', '::text) || COALESCE(ca.street3, ''::character varying)::text) || ', '::text) || ca.town::text) || ', '::text) || COALESCE(ca.county, ''::character varying)::text) || ', '::text) || COALESCE(ca.postcode, ''::character varying)::text) || ', '::text) || co.name::text AS address, co.name AS country
   FROM companyaddress ca
   JOIN company c ON c.party_id = ca.party_id
   JOIN countries co ON ca.countrycode = co.code;

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
   LEFT JOIN sl_analysis sla ON slm.sl_analysis_id = sla.id
   JOIN cumaster cum ON si.currency_id = cum.id
   JOIN cumaster twc ON si.twin_currency_id = twc.id
   JOIN tax_statuses ts ON si.tax_status_id = ts.id
   JOIN syterms syt ON si.payment_term_id = syt.id
  WHERE ts.eu_tax = true;
CREATE OR REPLACE VIEW reports.mi_salesoverview AS 
 SELECT date_part('year'::text, si_header.invoice_date) AS year, date_part('month'::text, si_header.invoice_date) AS month, si_header.transaction_type, 
        CASE
            WHEN si_lines.stitem_id IS NULL THEN si_lines.item_description::text
            ELSE (st_items.item_code::text || ' - '::text) || st_items.description::text
        END AS item, si_lines.item_description AS line_item, st_productgroups.product_group, st_productgroups.description AS group_desc, (gl_accounts.account::text || ' - '::text) || gl_accounts.description::text AS glaccount, (gl_centres.cost_centre::text || ' - '::text) || gl_centres.description::text AS glcentre, company.name AS company, company_industries.name AS industry, company_types.name AS type, company_ratings.name AS salesagent, companyoverview.countrycode, st_items.ref1, st_items.text1, 
        CASE
            WHEN si_header.transaction_type::text = 'I'::text THEN si_lines.sales_qty
            ELSE 0::numeric - si_lines.sales_qty
        END AS qty_sold, st_uoms.uom_name AS sales_unit, 
        CASE
            WHEN si_header.transaction_type::text = 'I'::text THEN si_lines.base_net_value
            ELSE 0::numeric - si_lines.base_net_value
        END AS sales_value, 
        CASE
            WHEN si_header.transaction_type::text = 'I'::text THEN si_lines.base_net_value - (st_items.std_mat + st_items.std_osc) * si_lines.sales_qty
            ELSE 0::numeric - (si_lines.base_net_value - (st_items.std_mat + st_items.std_osc) * si_lines.sales_qty)
        END AS value_added, 
        CASE
            WHEN si_header.transaction_type::text = 'I'::text THEN si_lines.base_net_value - (st_items.std_mat + st_items.std_osc + st_items.std_lab) * si_lines.sales_qty
            ELSE 0::numeric - (si_lines.base_net_value - (st_items.std_mat + st_items.std_osc + st_items.std_lab) * si_lines.sales_qty)
        END AS contribution
   FROM si_lines
   LEFT JOIN si_header ON si_lines.invoice_id = si_header.id
   LEFT JOIN slmaster ON si_header.slmaster_id = slmaster.id
   LEFT JOIN gl_accounts ON si_lines.glaccount_id = gl_accounts.id
   LEFT JOIN gl_centres ON si_lines.glcentre_id = gl_centres.id
   LEFT JOIN st_items ON si_lines.stitem_id = st_items.id
   LEFT JOIN st_uoms ON si_lines.stuom_id = st_uoms.id
   LEFT JOIN company ON slmaster.company_id = company.id
   LEFT JOIN companyoverview ON slmaster.company_id = companyoverview.id
   LEFT JOIN company_industries ON company.industry_id = company_industries.id
   LEFT JOIN company_types ON company.type_id = company_types.id
   LEFT JOIN company_ratings ON company.rating_id = company_ratings.id
   LEFT JOIN st_productgroups ON st_items.prod_group_id = st_productgroups.id;

GRANT SELECT ON TABLE reports.mi_salesoverview TO "ooo-data";

CREATE TABLE external_systems
(
  id bigserial NOT NULL,
  "name" character varying NOT NULL,
  description character varying NOT NULL,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT external_systems_pkey PRIMARY KEY (id),
  CONSTRAINT external_systems_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
);

CREATE TABLE data_mappings
(
  id bigserial NOT NULL,
  internal_type character varying,
  internal_attribute character varying,
  parent_id bigint,
  "name" character varying,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT data_mappings_pkey PRIMARY KEY (id),
  CONSTRAINT data_mappings_parent_fkey FOREIGN KEY (parent_id)
      REFERENCES data_mappings (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT data_mappings_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
);

CREATE TABLE data_definitions
(
  id bigserial NOT NULL,
  external_system_id bigint,
  "name" character varying NOT NULL,
  "type" character varying NOT NULL,
  description character varying NOT NULL,
  direction character varying,
  root_location character varying,
  folder character varying,
  username character varying,
  "password" character varying,
  archive_folder character varying,
  working_folder character varying,
  process_model character varying,
  process_function character varying,
  file_prefix character varying,
  file_extension character varying,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT data_defintions_pkey PRIMARY KEY (id),
  CONSTRAINT data_definitions_log_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT data_defintions_external_system_fkey FOREIGN KEY (external_system_id)
      REFERENCES external_systems (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
);

CREATE TABLE data_mapping_rules
(
  id bigserial NOT NULL,
  "name" character varying,
  external_system_id bigint,
  data_mapping_id bigint,
  parent_id bigint,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT data_mapping_rules_pkey PRIMARY KEY (id),
  CONSTRAINT data_mapping_rules_data_mapping_fkey FOREIGN KEY (data_mapping_id)
      REFERENCES data_mappings (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT data_mapping_rules_external_system_fkey FOREIGN KEY (external_system_id)
      REFERENCES external_systems (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT data_mapping_rules_parent_fkey FOREIGN KEY (parent_id)
      REFERENCES data_mapping_rules (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT data_mapping_rules_log_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
);

CREATE TABLE data_definition_details
(
  id bigserial NOT NULL,
  data_definition_id bigint NOT NULL,
  element character varying NOT NULL,
  "position" bigint,
  parent_id bigint,
  data_mapping_id bigint,
  data_mapping_rule_id bigint,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT data_definition_details_pkey PRIMARY KEY (id),
  CONSTRAINT data_definition_details_parent_fkey FOREIGN KEY (parent_id)
      REFERENCES data_definition_details (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT data_defintion_details_fkey FOREIGN KEY (data_definition_id)
      REFERENCES data_definitions (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT data_defintion_details_mapping_rule_fkey FOREIGN KEY (data_mapping_rule_id)
      REFERENCES data_mapping_rules (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT data_defintion_details_data_mappings_fkey FOREIGN KEY (data_mapping_id)
      REFERENCES data_mappings (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT data_definition_details_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
);

CREATE TABLE data_mapping_details
(
  id bigserial NOT NULL,
  data_mapping_rule_id bigint,
  external_code character varying,
  internal_code bigint,
  parent_id bigint,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT data_mapping_details_pkey PRIMARY KEY (id),
  CONSTRAINT data_mapping_details_parent_fkey FOREIGN KEY (parent_id)
      REFERENCES data_mapping_details (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT data_mapping_details_rules_fkey FOREIGN KEY (data_mapping_rule_id)
      REFERENCES data_mapping_rules (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT data_mapping_details_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
);

CREATE TABLE edi_transactions_log
(
  id bigserial NOT NULL,
  "name" character varying NOT NULL,
  status character varying NOT NULL,
  message character varying NOT NULL,
  external_system_id bigint,
  data_definition_id bigint NOT NULL,
  id_value bigint,
  identifier_field character varying,
  identifier_value character varying,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT edi_transactions_log_pkey PRIMARY KEY (id),
  CONSTRAINT edi_transactions_log_data_defintion_fkey FOREIGN KEY (data_definition_id)
      REFERENCES data_definitions (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT edi_transactions_log_external_system_fkey FOREIGN KEY (external_system_id)
      REFERENCES external_systems (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT edi_transactions_log_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
);

CREATE OR REPLACE VIEW data_definitions_overview AS 
 SELECT d.*
 , s.name AS external_system
   FROM data_definitions d
   JOIN external_systems s ON s.id = d.external_system_id;

CREATE OR REPLACE VIEW data_definition_details_overview AS 
 SELECT d.*
 , p.name AS data_definition
 , pd.element AS parent
 , m.internal_type AS map_to_type, m.internal_attribute AS map_to_attribute, r.name AS mapping_rule
   FROM data_definition_details d
   JOIN data_definitions p ON p.id = d.data_definition_id
   LEFT JOIN data_definition_details pd ON pd.id = d.parent_id
   LEFT JOIN data_mappings m ON m.id = d.data_mapping_id
   LEFT JOIN data_mapping_rules r ON r.id = d.data_mapping_rule_id;

CREATE OR REPLACE VIEW data_mappings_overview AS 
 SELECT m.*
 , p.internal_type AS parent_type, p.internal_attribute AS parent_attribute, d.data_definition_id
   FROM data_mappings m
   LEFT JOIN data_mappings p ON p.id = m.parent_id
   LEFT JOIN data_definition_details_overview d ON m.id = d.data_mapping_id;

CREATE OR REPLACE VIEW data_mapping_details_overview AS 
 SELECT d.*
 , r.name AS data_mapping_rule, m.name AS data_mapping
   FROM data_mapping_details d
   JOIN data_mapping_rules r ON r.id = d.data_mapping_rule_id
   LEFT JOIN data_mappings m ON m.id = r.data_mapping_id;

CREATE OR REPLACE VIEW data_mapping_rules_overview AS 
 SELECT r.*
 , p.name as parent_rule
 , s.name AS external_system, m.name AS data_mappings
   FROM data_mapping_rules r
   LEFT JOIN data_mapping_rules p ON p.id = r.parent_id
   JOIN external_systems s ON s.id = r.external_system_id
   LEFT JOIN data_mappings m ON m.id = r.data_mapping_id;

CREATE OR REPLACE VIEW edi_transactions_log_overview AS 
 SELECT l.*
 , s.name AS external_system, d.name AS data_definition
   FROM edi_transactions_log l
   JOIN external_systems s ON s.id = l.external_system_id
   LEFT JOIN data_definitions d ON d.id = l.data_definition_id;

DROP VIEW reports.audit_materialsales;

DROP VIEW si_headeroverview;

ALTER TABLE slmaster ADD COLUMN edi_invoice_definition_id bigint;

ALTER TABLE slmaster
  ADD CONSTRAINT slmaster_edi_invoice_definition_id_fkey FOREIGN KEY (edi_invoice_definition_id)
      REFERENCES data_definitions (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE SET NULL;

CREATE OR REPLACE VIEW si_headeroverview AS 
 SELECT si.id, si.invoice_number, si.sales_order_id, si.slmaster_id, si.invoice_date, si.transaction_type, si.ext_reference, si.currency_id, si.rate, si.gross_value, si.tax_value, si.net_value, si.twin_currency_id, si.twin_rate, si.twin_gross_value, si.twin_tax_value, si.twin_net_value, si.base_gross_value, si.base_tax_value, si.base_net_value, si.payment_term_id, si.due_date, si.status, si.description, si.usercompanyid, si.tax_status_id, si.sales_order_number, si.settlement_discount, si.delivery_note, si.despatch_date, si.date_printed, si.print_count, si.del_address_id, si.inv_address_id, si.original_due_date, si.created, si.createdby, si.alteredby, si.lastupdated, si.person_id, c.name AS customer, cum.currency, twc.currency AS twin, syt.description AS payment_terms, ts.description AS tax_status, slm.sl_analysis_id, slm.invoice_method, slm.edi_invoice_definition_id, pcm.contact AS email_invoice, sla.name, (p.firstname::text || ' '::text) || p.surname::text AS person
   FROM si_header si
   JOIN slmaster slm ON si.slmaster_id = slm.id
   LEFT JOIN partycontactmethodoverview pcm ON pcm.id = slm.email_invoice_id
   JOIN company c ON slm.company_id = c.id
   LEFT JOIN person p ON si.person_id = p.id
   LEFT JOIN sl_analysis sla ON slm.sl_analysis_id = sla.id
   JOIN cumaster cum ON si.currency_id = cum.id
   JOIN cumaster twc ON si.twin_currency_id = twc.id
   JOIN tax_statuses ts ON si.tax_status_id = ts.id
   JOIN syterms syt ON si.payment_term_id = syt.id;

CREATE OR REPLACE VIEW reports.audit_materialsales AS 
 SELECT si_headeroverview.invoice_number, si_headeroverview.sales_order_number, si_headeroverview.delivery_note, si_headeroverview.invoice_date, si_headeroverview.transaction_type, si_headeroverview.ext_reference, si_headeroverview.gross_value, si_headeroverview.base_gross_value, si_headeroverview.description, si_headeroverview.customer, si_headeroverview.currency, si_headeroverview.payment_terms, si_headeroverview.name AS sl_analysis
   FROM si_headeroverview
  WHERE si_headeroverview.base_gross_value > 15000::numeric;

GRANT SELECT ON TABLE reports.audit_materialsales TO "ooo-data";

ALTER TABLE output_details ADD COLUMN status character varying;

ALTER TABLE so_product_lines ADD COLUMN ean character varying;

DROP VIEW so_productlines_overview;

CREATE OR REPLACE VIEW so_productlines_overview AS 
 SELECT so.*
 , c.name AS customer, uom.uom_name
 , (st.item_code::text || ' - '::text) || st.description::text AS stitem, gla.account AS glaccount
 , glc.cost_centre AS glcentre, cu.currency, tax.description AS taxrate, pt.name AS so_price_type
   FROM so_product_lines so
   LEFT JOIN slmaster slm ON so.slmaster_id = slm.id
   LEFT JOIN company c ON slm.company_id = c.id
   LEFT JOIN st_items st ON so.stitem_id = st.id
   LEFT JOIN st_uoms uom ON so.stuom_id = uom.id
   JOIN cumaster cu ON so.currency_id = cu.id
   JOIN taxrates tax ON so.tax_rate_id = tax.id
   JOIN gl_accounts gla ON so.glaccount_id = gla.id
   JOIN gl_centres glc ON so.glcentre_id = glc.id
   LEFT JOIN so_price_types pt ON so.so_price_type_id = pt.id;

ALTER TABLE po_product_lines ADD COLUMN ean character varying;

DROP VIEW po_productlines_overview;

CREATE OR REPLACE VIEW po_productlines_overview AS 
 SELECT po.*
 , plm.payee_name, c.name AS supplier, uom.uom_name
 , (st.item_code::text || ' - '::text) || st.description::text AS stitem, gla.account AS glaccount
 , glc.cost_centre AS glcentre, cur.currency
   FROM po_product_lines po
   LEFT JOIN plmaster plm ON po.plmaster_id = plm.id
   LEFT JOIN company c ON plm.company_id = c.id
   LEFT JOIN st_items st ON po.stitem_id = st.id
   LEFT JOIN st_uoms uom ON po.stuom_id = uom.id
   JOIN cumaster cur ON po.currency_id = cur.id
   JOIN gl_accounts gla ON po.glaccount_id = gla.id
   JOIN gl_centres glc ON po.glcentre_id = glc.id;
