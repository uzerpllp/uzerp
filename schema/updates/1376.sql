--
-- $Revision: 1.10 $
--

-- View: addressoverview

-- DROP VIEW addressoverview;

CREATE OR REPLACE VIEW addressoverview AS 
 SELECT a.*
 , co.name AS country
 , (((((((((((a.street1::text || ', '::text) || COALESCE(a.street2, ''::character varying)::text) || ', '::text) || COALESCE(a.street3, ''::character varying)::text) || ', '::text) || a.town::text) || ', '::text) || COALESCE(a.county, ''::character varying)::text) || ', '::text) || COALESCE(a.postcode, ''::character varying)::text) || ', '::text) || co.name::text AS address
   FROM address a
   JOIN countries co ON a.countrycode = co.code;

ALTER TABLE addressoverview OWNER TO "www-data";

-- View: companyoverview

DROP VIEW companyoverview;

CREATE OR REPLACE VIEW companyoverview AS 
 SELECT c.id, c.name, c.accountnumber, c.vatnumber, c.companynumber, c.website, c.employees, c.usercompanyid, c.parent_id
 , c.owner, c.assigned, c.created, c.lastupdated, c.alteredby, c.description, c.is_lead, c.party_id, c.classification_id
 , c.source_id, c.industry_id, c.status_id, c.rating_id, c.type_id, c.createdby, pa.address_id, a.street1, a.street2
 , a.street3, a.town, a.county, a.countrycode, a.postcode, a.country, a.address as main_address, cm1.contact AS phone, cm2.contact AS email
 , cc.name AS company_classification, ci.name AS company_industry, cr.name AS company_rating, cs.name AS company_source
 , cst.name AS company_status, ct.name AS company_type
   FROM company c
   LEFT JOIN party p ON c.party_id = p.id
   LEFT JOIN partyaddress pa ON p.id = pa.party_id AND pa.main
   LEFT JOIN addressoverview a ON a.id = pa.address_id
   LEFT JOIN party_contact_methods pcm1 ON p.id = pcm1.party_id AND pcm1.main AND pcm1.type::text = 'T'::text
   LEFT JOIN party_contact_methods pcm2 ON p.id = pcm2.party_id AND pcm2.main AND pcm2.type::text = 'E'::text
   LEFT JOIN contact_methods cm1 ON cm1.id = pcm1.contactmethod_id
   LEFT JOIN contact_methods cm2 ON cm2.id = pcm2.contactmethod_id
   LEFT JOIN company_classifications cc ON cc.id = c.classification_id
   LEFT JOIN company_industries ci ON ci.id = c.industry_id
   LEFT JOIN company_ratings cr ON cr.id = c.rating_id
   LEFT JOIN company_sources cs ON cs.id = c.source_id
   LEFT JOIN company_statuses cst ON cst.id = c.status_id
   LEFT JOIN company_types ct ON ct.id = c.type_id
  WHERE NOT (EXISTS ( SELECT sc.id
   FROM system_companies sc
  WHERE c.id = sc.company_id));

ALTER TABLE companyoverview OWNER TO "www-data";

-- View: companyaddress

DROP VIEW tax_eu_saleslist;

DROP VIEW companyaddressoverview;

DROP VIEW companyaddress;

CREATE OR REPLACE VIEW companyaddress AS 
 SELECT a.id, a.street1, a.street2, a.street3, a.town, a.county, a.postcode, a.countrycode, co.name as country
 , pa.name, pa.main, pa.billing, pa.shipping, pa.payment, pa.technical, pa.party_id
   FROM address a
   JOIN countries co ON co.code = a.countrycode
   JOIN partyaddress pa ON pa.address_id = a.id
   JOIN party p ON p.id = pa.party_id AND p.type::text = 'Company'::text;

ALTER TABLE companyaddress OWNER TO "www-data";-- View: st_itemsoverview

-- View: companyaddressoverview

CREATE OR REPLACE VIEW companyaddressoverview AS 
 SELECT ca.id, ca.street1, ca.street2, ca.street3, ca.town, ca.county, ca.postcode, ca.countrycode
 , c.id AS company_id, ca.name, ca.main, ca.billing, ca.shipping, ca.payment, ca.technical
 , c.name AS company
 , (((((((((((ca.street1::text || ', '::text) || COALESCE(ca.street2, ''::character varying)::text) || ', '::text) || COALESCE(ca.street3, ''::character varying)::text) || ', '::text) || ca.town::text) || ', '::text) || COALESCE(ca.county, ''::character varying)::text) || ', '::text) || COALESCE(ca.postcode, ''::character varying)::text) || ', '::text) || co.name::text AS address, co.name AS country
   FROM companyaddress ca
   JOIN company c ON c.party_id = ca.party_id
   JOIN countries co ON ca.countrycode = co.code;

ALTER TABLE companyaddressoverview OWNER TO "www-data";-- View: tax_eu_saleslist

CREATE OR REPLACE VIEW tax_eu_saleslist AS 
 SELECT si.id, si.invoice_number, so.order_number AS sales_order_number, si.invoice_date, si.transaction_type, si.ext_reference, si.currency_id, si.rate, si.settlement_discount, 
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
   JOIN so_header so ON si.sales_order_id = so.id
   JOIN slmaster slm ON si.slmaster_id = slm.id
   JOIN company coy ON slm.company_id = coy.id
   JOIN companyaddress cad ON coy.party_id = cad.party_id AND cad.main IS TRUE
   LEFT JOIN sl_analysis sla ON slm.sl_analysis_id = sla.id
   JOIN cumaster cum ON si.currency_id = cum.id
   JOIN cumaster twc ON si.twin_currency_id = twc.id
   JOIN tax_statuses ts ON si.tax_status_id = ts.id
   JOIN syterms syt ON si.payment_term_id = syt.id
  WHERE ts.eu_tax = true;

ALTER TABLE tax_eu_saleslist OWNER TO "www-data";

-- View: partyaddressoverview

DROP VIEW personaddress_overview;

DROP VIEW personaddress;

DROP VIEW partyaddressoverview;

CREATE OR REPLACE VIEW partyaddressoverview AS 
 SELECT p.id, ((((((COALESCE(a.street1, ''::character varying)::text || ','::text) || (COALESCE(a.street2, ''::character varying)::text || ','::text)) || (COALESCE(a.street3, ''::character varying)::text || ','::text)) || (COALESCE(a.town, ''::character varying)::text || ','::text)) || (COALESCE(a.county, ''::character varying)::text || ','::text)) || (COALESCE(a.postcode, ''::character varying)::text || ','::text)) || COALESCE(c.name, ''::character varying::bpchar::character varying)::text AS fulladdress, a.street1, a.street2, a.street3, a.town, a.county, a.postcode, a.countrycode, c.name as country, p.address_id, p.name, p.main, p.billing, p.shipping, p.payment, p.technical, p.party_id, p.parent_id, p.usercompanyid
   FROM partyaddress p
   JOIN address a ON p.address_id = a.id
   JOIN countries c ON c.code = a.countrycode;

ALTER TABLE partyaddressoverview OWNER TO "www-data";

-- View: personaddress

CREATE OR REPLACE VIEW personaddress AS 
 SELECT pa.address_id AS id, pa.fulladdress, pa.street1, pa.street2, pa.street3, pa.town, pa.county, pa.postcode
 , pa.countrycode, pa.country
 , pa.address_id, pa.name, pa.main, pa.billing, pa.shipping, pa.payment, pa.technical, pa.party_id, pa.parent_id
 , pe.id AS person_id
   FROM person pe
   JOIN party p ON p.id = pe.party_id AND p.type::text = 'Person'::text
   JOIN partyaddressoverview pa ON p.id = pa.party_id;

ALTER TABLE personaddress OWNER TO "www-data";

-- View: personaddress_overview

CREATE OR REPLACE VIEW personaddress_overview AS 
 SELECT personaddress.id, personaddress.fulladdress AS address, personaddress.countrycode, personaddress.person_id
 , personaddress.name, personaddress.main, personaddress.billing, personaddress.shipping, personaddress.payment
 , personaddress.technical
   FROM personaddress personaddress;

ALTER TABLE personaddress_overview OWNER TO "www-data";

-- View: personoverview

DROP VIEW personoverview;

CREATE OR REPLACE VIEW personoverview AS 
 SELECT per.id, per.title, per.firstname, per.middlename, per.surname, per.suffix, per.department, per.jobtitle, per.marital
 , per.lang, per.company_id, per.owner, per.userdetail, per.reports_to, per.can_call, per.can_email, per.assigned_to
 , per.created, per.lastupdated, per.alteredby, per.usercompanyid, per.crm_source, per.party_id, per.end_date
 , (COALESCE(per.title::text || ' '::text, ''::text) || COALESCE(per.firstname::text || ' '::text, ''::text)) || per.surname::text AS name
 , pa.address_id, a.street1, a.street2, a.street3, a.town, a.county, a.countrycode, a.country, a.postcode, a.address as main_address
 , c.name AS company, c.accountnumber, cm.contact AS phone, m.contact AS mobile, e.contact AS email
   FROM person per
   LEFT JOIN company c ON per.company_id = c.id
   LEFT JOIN party p ON per.party_id = p.id
   LEFT JOIN partyaddress pa ON p.id = pa.party_id AND pa.main
   LEFT JOIN addressoverview a ON a.id = pa.address_id
   LEFT JOIN party_contact_methods pcm ON p.id = pcm.party_id AND pcm.main AND pcm.type::text = 'T'::text
   LEFT JOIN contact_methods cm ON cm.id = pcm.contactmethod_id
   LEFT JOIN party_contact_methods mcm ON p.id = mcm.party_id AND mcm.main AND mcm.type::text = 'M'::text
   LEFT JOIN contact_methods m ON m.id = mcm.contactmethod_id
   LEFT JOIN party_contact_methods ecm ON p.id = ecm.party_id AND ecm.main AND ecm.type::text = 'E'::text
   LEFT JOIN contact_methods e ON e.id = ecm.contactmethod_id;

ALTER TABLE personoverview OWNER TO "www-data";

-- View: plmaster_overview

-- DROP VIEW plmaster_overview;

CREATE OR REPLACE VIEW plmaster_overview AS 
 SELECT pl.id, pl.payee_name, pl.company_id, pl.currency_id, pl.remittance_advice
 , pl.payment_term_id, pl.last_paid, pl.tax_status_id, pl.created, pl.usercompanyid
 , pl.outstanding_balance, pl.payment_type_id, pl.cb_account_id, pl.receive_action
 , pl.order_method, pl.email_order_id, pl.email_remittance_id, pl.local_sort_code
 , pl.local_account_number, pl.local_bank_name_address, pl.overseas_iban_number
 , pl.overseas_bic_code, pl.overseas_account_number, pl.overseas_bank_name_address, pl.sort_code
 , pl.account_number, pl.bank_name_address, pl.iban_number, pl.bic_code, pl.createdby
 , pl.alteredby, pl.lastupdated, pl.credit_limit, pl.delivery_term_id, pl.date_inactive
 , c.name, sy.name AS payment_type, st.description AS payment_term, cu.currency
 , p1.contact AS email_order, p2.contact AS email_remittance
 , a.street1, a.street2, a.street3, a.town, a.county, a.postcode, a.countrycode, a.country, a.address as payment_address
   FROM plmaster pl
   JOIN company c ON pl.company_id = c.id
   JOIN addressoverview a ON a.id = pl.payment_address_id
   JOIN sypaytypes sy ON sy.id = pl.payment_type_id
   JOIN syterms st ON st.id = pl.payment_term_id
   JOIN cumaster cu ON cu.id = pl.currency_id
   LEFT JOIN partycontactmethodoverview p1 ON p1.id = pl.email_order_id
   LEFT JOIN partycontactmethodoverview p2 ON p2.id = pl.email_remittance_id;

ALTER TABLE plmaster_overview OWNER TO "www-data";

-- View: po_auth_summary

-- DROP VIEW po_auth_summary;

CREATE OR REPLACE VIEW po_auth_summary AS 
 SELECT h.id, h.order_number, h.status, h.type, a.username, h.order_date, h.due_date, plm.payee_name, c.name as supplier
   FROM ( SELECT o1.order_id, pa.username, count(*) AS authlines
           FROM ( SELECT po_lines.order_id, po_lines.glaccount_id, po_lines.glcentre_id, sum(po_lines.base_net_value) AS value
                   FROM po_lines
                  WHERE po_lines.status::text <> 'X'::text
                  GROUP BY po_lines.order_id, po_lines.glaccount_id, po_lines.glcentre_id) o1
      JOIN po_authlist pa ON pa.glaccount_id = o1.glaccount_id AND pa.glcentre_id = o1.glcentre_id
     WHERE o1.value <= pa.order_limit
     GROUP BY o1.order_id, pa.username) a
   JOIN ( SELECT o2.order_id, count(*) AS totallines
           FROM ( SELECT po_lines.order_id, po_lines.glaccount_id, po_lines.glcentre_id
                   FROM po_lines
                  WHERE po_lines.status::text <> 'X'::text
                  GROUP BY po_lines.order_id, po_lines.glaccount_id, po_lines.glcentre_id) o2
          GROUP BY o2.order_id) b ON a.order_id = b.order_id AND a.authlines = b.totallines
   JOIN po_header h ON h.id = a.order_id
   JOIN plmaster plm ON h.plmaster_id = plm.id
   JOIN company c ON plm.company_id = c.id;

ALTER TABLE po_auth_summary OWNER TO "www-data";

-- View: po_no_auth_user

-- DROP VIEW po_no_auth_user;

CREATE OR REPLACE VIEW po_no_auth_user AS 
 SELECT h.id, h.order_number, h.order_date, h.due_date, plm.payee_name, c.name as supplier, h.status
   FROM po_header h
   JOIN plmaster plm ON h.plmaster_id = plm.id
   JOIN company c ON plm.company_id = c.id
   LEFT JOIN po_auth_summary a ON a.order_number = h.order_number
  WHERE h.type::text = 'R'::text AND a.username IS NULL;

ALTER TABLE po_no_auth_user OWNER TO "www-data";

-- View: po_headeroverview

-- DROP VIEW po_headeroverview;

CREATE OR REPLACE VIEW po_headeroverview AS 
 SELECT po.id, po.order_number, po.plmaster_id, po.del_address_id, po.order_date, po.due_date
 , po.ext_reference, po.currency_id, po.rate, po.net_value, po.twin_currency_id, po.twin_rate
 , po.twin_net_value, po.base_net_value, po.type, po.status, po.description, po.usercompanyid
 , po.date_authorised, po.raised_by, po.authorised_by, po.created, po.owner, po.lastupdated
 , po.alteredby
 , plm.payee_name, c.name AS supplier, cum.currency, twc.currency AS twin_currency
 , pr.username AS raised_by_person, pa.username AS authorised_by_person, p.name AS project
 , da.address AS del_address, da.street1, da.street2, da.street3, da.town, da.county, da.postcode, da.country, da.countrycode
   FROM po_header po
   JOIN plmaster plm ON po.plmaster_id = plm.id
   JOIN company c ON plm.company_id = c.id
   JOIN cumaster cum ON po.currency_id = cum.id
   JOIN cumaster twc ON po.twin_currency_id = twc.id
   JOIN users pr ON po.raised_by::text = pr.username::text
   LEFT JOIN users pa ON po.authorised_by::text = pa.username::text
   LEFT JOIN projects p ON po.project_id = p.id
   LEFT JOIN addressoverview da ON po.del_address_id = da.id;

ALTER TABLE po_headeroverview OWNER TO "www-data";

-- View: slmaster_overview

-- DROP VIEW slmaster_overview;

CREATE OR REPLACE VIEW slmaster_overview AS 
 SELECT sl.id, sl.company_id, sl.currency_id, sl.statement, sl.payment_term_id, sl.last_paid, sl.tax_status_id, sl.created
 , sl.usercompanyid, sl.outstanding_balance, sl.payment_type_id, sl.cb_account_id, sl.despatch_action, sl.invoice_method
 , sl.email_invoice_id, sl.email_statement_id, sl.sl_analysis_id, sl.createdby, sl.alteredby, sl.lastupdated
 , sl.account_status, sl.so_price_type_id, sl.credit_limit, sl.last_statement_date, sl.edi_invoice_definition_id
 , sl.delivery_term_id, sl.date_inactive, p1.contact AS email_invoice, p2.contact AS email_statement, c.name, cu.currency
 , st.description AS payment_term, sy.name AS payment_type, sa.name AS sl_analysis
 , a.street1, a.street2, a.street3, a.town, a.county, a.postcode, a.countrycode, a.country, a.address as billing_address
   FROM slmaster sl
   JOIN company c ON c.id = sl.company_id
   JOIN addressoverview a ON a.id = sl.billing_address_id
   JOIN sypaytypes sy ON sy.id = sl.payment_type_id
   JOIN syterms st ON st.id = sl.payment_term_id
   JOIN cumaster cu ON cu.id = sl.currency_id
   LEFT JOIN sl_analysis sa ON sa.id = sl.sl_analysis_id
   LEFT JOIN partycontactmethodoverview p1 ON p1.id = sl.email_invoice_id
   LEFT JOIN partycontactmethodoverview p2 ON p2.id = sl.email_statement_id;

ALTER TABLE slmaster_overview OWNER TO "www-data";

-- View: si_headeroverview

-- DROP VIEW si_headeroverview;

CREATE OR REPLACE VIEW si_headeroverview AS 
 SELECT si.id, si.invoice_number, si.sales_order_id, si.slmaster_id, si.invoice_date, si.transaction_type, si.ext_reference
 , si.currency_id, si.rate, si.gross_value, si.tax_value, si.net_value, si.twin_currency_id, si.twin_rate
 , si.twin_gross_value, si.twin_tax_value, si.twin_net_value, si.base_gross_value, si.base_tax_value, si.base_net_value
 , si.payment_term_id, si.due_date, si.status, si.description, si.usercompanyid, si.tax_status_id, si.settlement_discount
 , si.delivery_note, si.despatch_date, si.date_printed, si.print_count, si.del_address_id, si.inv_address_id
 , si.original_due_date, si.created, si.createdby, si.alteredby, si.lastupdated, si.person_id
 , so.order_number AS sales_order_number, c.name AS customer, cum.currency, twc.currency AS twin
 , syt.description AS payment_terms, ts.description AS tax_status, slm.sl_analysis_id, slm.invoice_method
 , slm.edi_invoice_definition_id, pcm.contact AS email_invoice, sla.name
 , (p.firstname::text || ' '::text) || p.surname::text AS person, COALESCE(sl.line_count, 0::bigint) AS line_count
 , a.street1, a.street2, a.street3, a.town, a.county, a.postcode, a.countrycode, a.country, a.address as invoice_address
   FROM si_header si
   LEFT JOIN ( SELECT si_lines.invoice_id, count(*) AS line_count
           FROM si_lines
          GROUP BY si_lines.invoice_id) sl ON sl.invoice_id = si.id
   JOIN slmaster slm ON si.slmaster_id = slm.id
   LEFT JOIN so_header so ON so.id = si.sales_order_id
   LEFT JOIN partycontactmethodoverview pcm ON pcm.id = slm.email_invoice_id
   JOIN company c ON slm.company_id = c.id
   LEFT JOIN person p ON si.person_id = p.id
   LEFT JOIN sl_analysis sla ON slm.sl_analysis_id = sla.id
   JOIN cumaster cum ON si.currency_id = cum.id
   JOIN cumaster twc ON si.twin_currency_id = twc.id
   JOIN tax_statuses ts ON si.tax_status_id = ts.id
   JOIN syterms syt ON si.payment_term_id = syt.id
   LEFT JOIN addressoverview a ON si.inv_address_id = a.id;

ALTER TABLE si_headeroverview OWNER TO "www-data";

-- View: so_headeroverview

-- DROP VIEW so_headeroverview;

CREATE OR REPLACE VIEW so_headeroverview AS 
 SELECT so.id, so.order_number, so.slmaster_id, so.del_address_id, so.order_date, so.due_date, so.despatch_date
 , so.ext_reference, so.currency_id, so.rate, so.net_value, so.twin_currency_id, so.twin_rate, so.twin_net_value
 , so.base_net_value, so.type, so.status, so.description, so.usercompanyid, so.despatch_action, so.inv_address_id
 , so.created, so.createdby, so.alteredby, so.lastupdated, so.person_id, slm.account_status, c.name AS customer
 , cum.currency, twc.currency AS twin_currency, (p.firstname::text || ' '::text) || p.surname::text AS person
 , wa.action_name ||'-'|| wa.description as whaction
 , da.address as delivery_address, ia.address as invoice_address
 , da.street1, da.street2, da.street3, da.town, da.county, da.postcode, da.country, da.countrycode
   FROM so_header so
   JOIN slmaster slm ON so.slmaster_id = slm.id
   JOIN company c ON slm.company_id = c.id
   LEFT JOIN person p ON so.person_id = p.id
   JOIN cumaster cum ON so.currency_id = cum.id
   JOIN cumaster twc ON so.twin_currency_id = twc.id
   LEFT JOIN wh_actions wa ON so.despatch_action = wa.id
   LEFT JOIN addressoverview da ON so.del_address_id = da.id
   LEFT JOIN addressoverview ia ON so.inv_address_id = ia.id;

ALTER TABLE so_headeroverview OWNER TO "www-data";

-- View: st_itemsoverview

DROP VIEW st_itemsoverview;

CREATE OR REPLACE VIEW st_itemsoverview AS 
 SELECT i.*
 , (p.product_group::text || ' - '::text) || p.description::text AS product_group, t.type_code, u.uom_name
 , tr.taxrate AS tax_rate
   FROM st_items i
   JOIN st_productgroups p ON i.prod_group_id = p.id
   JOIN st_uoms u ON i.uom_id = u.id
   JOIN st_typecodes t ON i.type_code_id = t.id
   JOIN taxrates tr ON i.tax_rate_id = tr.id;

ALTER TABLE st_itemsoverview OWNER TO "www-data";

-- Table: report_types

-- DROP TABLE report_types;

CREATE TABLE report_types
(
  id bigserial NOT NULL,
  "name" character varying NOT NULL,
  public boolean DEFAULT FALSE,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  usercompanyid bigint NOT NULL,
  CONSTRAINT report_types_pkey PRIMARY KEY (id),
  CONSTRAINT report_types_name_unique UNIQUE (name, usercompanyid)
);

ALTER TABLE report_types OWNER TO "www-data";

INSERT INTO report_types
("name", public, usercompanyid)
SELECT 'Labels', TRUE, sc.id
  FROM system_companies sc;

INSERT INTO report_types
("name", public, usercompanyid)
SELECT 'List', TRUE, sc.id
  FROM system_companies sc;

-- Table: report_definitions

ALTER TABLE report_definitions ADD COLUMN report_type_id bigint;
ALTER TABLE report_definitions ADD COLUMN user_defined boolean DEFAULT TRUE;

-- Foreign Key: report_definitions_report_type_fkey

-- ALTER TABLE report_definitions DROP CONSTRAINT report_definitions_report_type_fkey;

ALTER TABLE report_definitions
  ADD CONSTRAINT report_definitions_report_type_fkey FOREIGN KEY (report_type_id)
      REFERENCES report_types (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

-- View: report_definitions_overview

-- DROP VIEW report_definitions_overview;

CREATE OR REPLACE VIEW report_definitions_overview AS 
 SELECT rd.*, rt.name AS report_type
   FROM report_definitions rd
   LEFT JOIN report_types rt ON rt.id = rd.report_type_id;

ALTER TABLE report_definitions_overview OWNER TO "www-data";

--
-- Modules/Components
--

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'setupcontroller', 'C', location||'/controllers/SetupController.php', id, 'Output Setup'
   FROM modules m
  WHERE name = 'output_setup';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'reporttype', 'M', location||'/models/ReportType.php', id, 'Report Definition Types'
   FROM modules m
  WHERE name = 'output_setup';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'reporttypecollection', 'M', location||'/models/ReportTypeCollection.php', id, 'Report Definition Types'
   FROM modules m
  WHERE name = 'output_setup';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'outputsearch', 'M', location||'/models/outputSearch.php', id, 'Report Definition Types'
   FROM modules m
  WHERE name = 'output_setup';

--
-- Permissions
--

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
                               and permission='output_setup')) as next
 where type='m'
   and permission='output_setup'
   and not exists (select 1
                     from permissions
                    where type='c'
                      and permission = 'setup'
                      and parent_id = (select id
                                         from permissions
                                        where type='m'
                                          and permission='output_setup'));

--
-- Report Definitions
--

INSERT INTO report_definitions
(name, definition, usercompanyid, report_type_id)
SELECT 'Labels_a4_2x3',
'<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
	<xsl:template match="data">
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:layout-master-set>
				<fo:simple-page-master master-name="all-pages"
						page-height="29.7cm"
						page-width="21cm"
						margin="0cm" >
					<fo:region-body margin-top="0.5cm" margin-bottom="0.5cm"/>
					<fo:region-before extent="0cm"/>
					<fo:region-after extent="5mm"/>
	  			</fo:simple-page-master>
		  	</fo:layout-master-set>
			<!-- format is the style of page numbering, 1 for 1,2,3, i for roman numerals (sp?)-->
			<fo:page-sequence master-reference="all-pages" format="1">
				<fo:flow flow-name="xsl-region-body" >
						<fo:table table-layout="fixed" width="21cm">
							<fo:table-column column-width="9cm"/>
							<fo:table-column column-width="9cm"/>
							<fo:table-body>
								<xsl:for-each select="record[position() mod 2 = 1]" >
								<fo:table-row height="9cm">
									<fo:table-cell background-repeat="no-repeat" margin-right="4mm" padding="0 2.5mm 0 7.5mm">
										<xsl:for-each select="./*" >
											<fo:block font-size="10pt">
												<xsl:value-of select="." />
											</fo:block>
										</xsl:for-each>
									</fo:table-cell>
									<fo:table-cell background-repeat="no-repeat" margin-right="4mm" padding="0 2.5mm 0 7.5mm">
										<xsl:choose>
											<!-- look for a sibling with same name as current node -->
											<xsl:when test="count(following-sibling::*[name()=name(current())])">
												<xsl:for-each select="./following-sibling::record[1]/*" >
													<fo:block font-size="10pt">
														<xsl:value-of select="." />
													</fo:block>
												</xsl:for-each>
											</xsl:when>
											<!-- there is nothing to follow, so create empty cells -->
											<xsl:otherwise>
                                                                                        	<fo:block font-size="10pt">
                                                                                        	</fo:block>
											</xsl:otherwise>
										</xsl:choose>
									</fo:table-cell>
								</fo:table-row>
								</xsl:for-each >
							</fo:table-body>
						</fo:table>
				</fo:flow>
			</fo:page-sequence>
		</fo:root>
	</xsl:template>
</xsl:stylesheet>',
  sc.id, rt.id
  FROM system_companies sc
     , report_types rt
 WHERE rt.name = 'Labels';

DELETE FROM report_definitions
 WHERE name = 'PrintCollection';

UPDATE report_definitions
   SET name = 'PrintCollection'
     , user_defined = FALSE
     , report_type_id = (SELECT id
                           FROM report_types
                          WHERE name = 'List')
 WHERE name = 'CustomReport';

INSERT INTO report_definitions
(name, user_defined, definition, usercompanyid, report_type_id)
SELECT 'PrintCollectionPortrait', FALSE,
'<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
	<xsl:template match="/">
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:layout-master-set>
				<fo:simple-page-master master-name="all-pages"
						page-height="29.7cm"
						page-width="21cm"
						margin="1cm" >
					<fo:region-body margin-top="1cm" margin-bottom="1.1cm"/>
					<fo:region-before extent="1cm"/>
					<fo:region-after extent="5mm"/>
	  			</fo:simple-page-master>
		  	</fo:layout-master-set>
			<!-- format is the style of page numbering, 1 for 1,2,3, i for roman numerals (sp?)-->
			<fo:page-sequence master-reference="all-pages" format="1">
				<!-- header with running glossary entries -->
				<fo:static-content flow-name="xsl-region-before">
					<fo:block><!--[REPORT_TITLE]--></fo:block>
				</fo:static-content>
				<fo:static-content flow-name="xsl-region-after">
					<fo:table table-layout="fixed" width="100%">
						<fo:table-column column-width="proportional-column-width(1)"/>
						<fo:table-column column-width="proportional-column-width(1)"/>
						<fo:table-body>
							<fo:table-row>
								<fo:table-cell>
									<fo:block>Page <!--[page_position]--></fo:block>
								</fo:table-cell>
								<fo:table-cell>
									<fo:block text-align="right"><!--[FOOTER_STRING]--></fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:static-content>
				<fo:flow flow-name="xsl-region-body" >
					<fo:block margin-bottom="2mm"><!--[SEARCH_STRING]--></fo:block>
					<fo:table table-layout="fixed" width="100%" font-size="8pt">
						<!--[REPORT_COLUMN_DEFINITIONS]-->
						<fo:table-header>
							<fo:table-row>
								<!-- loop through headings -->
								<xsl:attribute name="font-weight">bold</xsl:attribute>
								<xsl:attribute name="background-color"><!--[table_header_colour]--></xsl:attribute>
								<!--[REPORT_COLUMN_HEADINGS]-->
							</fo:table-row>
						</fo:table-header>
						<fo:table-body>
							<xsl:for-each select="data/record">
								<fo:table-row>
									<!-- this condition is to provide us with alternate row colours -->
									<xsl:if test="(position() mod 2 = 1)">
										<xsl:attribute name="background-color"><!--[table_row_alternate_colour]--></xsl:attribute>
									</xsl:if>
									<!-- check if we''re dealing with a total row -->
									<xsl:if test="@sub_total=''true''">
										<xsl:attribute name="font-weight">bold</xsl:attribute>
										<xsl:attribute name="background-color"><!--[table_header_colour]--></xsl:attribute>
									</xsl:if>
									<!--[REPORT_ROW_CELLS]-->
								</fo:table-row>
							</xsl:for-each>
							<!-- just in case we don''t have any rows -->
							<fo:table-row>
								<fo:table-cell>
									<fo:block></fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
					<!-- this is required to calculate the last page number -->
					<fo:block id="last-page"/>
				</fo:flow>
			</fo:page-sequence>
		</fo:root>
	</xsl:template>
</xsl:stylesheet>',
  sc.id, rt.id
  FROM system_companies sc
     , report_types rt
 WHERE rt.name = 'List';
