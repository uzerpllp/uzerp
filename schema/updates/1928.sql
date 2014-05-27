--
-- $Revision: 1.1 $
--

-- View: companyoverview

DROP VIEW companyoverview;

CREATE OR REPLACE VIEW companyoverview AS 
 SELECT c.*
 , pa.address_id, a.street1, a.street2
 , a.street3, a.town, a.county, a.countrycode, a.postcode, a.country, a.address as main_address
 , cm1.contact AS phone, cm2.contact AS email, cm3.contact AS fax, cm4.contact AS mobile
 , cc.name AS company_classification, ci.name AS company_industry, cr.name AS company_rating, cs.name AS company_source
 , cst.name AS company_status, ct.name AS company_type, pc.name AS company_parent
   FROM company c
   LEFT JOIN company pc ON c.parent_id = pc.id
   LEFT JOIN party p ON c.party_id = p.id
   LEFT JOIN partyaddress pa ON p.id = pa.party_id AND pa.main
   LEFT JOIN addressoverview a ON a.id = pa.address_id
   LEFT JOIN party_contact_methods pcm1 ON p.id = pcm1.party_id AND pcm1.main AND pcm1.type::text = 'T'::text
   LEFT JOIN party_contact_methods pcm2 ON p.id = pcm2.party_id AND pcm2.main AND pcm2.type::text = 'E'::text
   LEFT JOIN party_contact_methods pcm3 ON p.id = pcm3.party_id AND pcm3.main AND pcm3.type::text = 'F'::text
   LEFT JOIN party_contact_methods pcm4 ON p.id = pcm4.party_id AND pcm4.main AND pcm4.type::text = 'M'::text
   LEFT JOIN contact_methods cm1 ON cm1.id = pcm1.contactmethod_id
   LEFT JOIN contact_methods cm2 ON cm2.id = pcm2.contactmethod_id
   LEFT JOIN contact_methods cm3 ON cm3.id = pcm3.contactmethod_id
   LEFT JOIN contact_methods cm4 ON cm4.id = pcm4.contactmethod_id
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

-- View: personoverview

DROP VIEW personoverview;

CREATE OR REPLACE VIEW personoverview AS 
 SELECT per.*
 , (COALESCE(per.title::text || ' '::text, ''::text) || COALESCE(per.firstname::text || ' '::text, ''::text)) || per.surname::text AS name
 , pa.address_id, a.street1, a.street2, a.street3, a.town, a.county, a.countrycode, a.country, a.postcode, a.address as main_address
 , c.name AS company, c.accountnumber, cm.contact AS phone, m.contact AS mobile, e.contact AS email, f.contact AS fax
 , l.name as language, per.reports_to as person_reports_to, per.owner as owned_by, per.assigned_to as person_assigned_to
   FROM person per
   JOIN lang l ON per.lang = l.code
   LEFT JOIN company c ON per.company_id = c.id
   LEFT JOIN party p ON per.party_id = p.id
   LEFT JOIN partyaddress pa ON p.id = pa.party_id AND pa.main
   LEFT JOIN addressoverview a ON a.id = pa.address_id
   LEFT JOIN party_contact_methods pcm ON p.id = pcm.party_id AND pcm.main AND pcm.type::text = 'T'::text
   LEFT JOIN contact_methods cm ON cm.id = pcm.contactmethod_id
   LEFT JOIN party_contact_methods mcm ON p.id = mcm.party_id AND mcm.main AND mcm.type::text = 'M'::text
   LEFT JOIN contact_methods m ON m.id = mcm.contactmethod_id
   LEFT JOIN party_contact_methods ecm ON p.id = ecm.party_id AND ecm.main AND ecm.type::text = 'E'::text
   LEFT JOIN contact_methods e ON e.id = ecm.contactmethod_id
   LEFT JOIN party_contact_methods fcm ON p.id = fcm.party_id AND fcm.main AND fcm.type::text = 'F'::text
   LEFT JOIN contact_methods f ON f.id = fcm.contactmethod_id;

ALTER TABLE personoverview OWNER TO "www-data";

-- View: slmaster_overview

CREATE OR REPLACE VIEW slmaster_overview AS 
 SELECT sl.id, sl.company_id, sl.currency_id, sl.statement, sl.payment_term_id, sl.last_paid, sl.tax_status_id, sl.created
 , sl.usercompanyid, sl.outstanding_balance, sl.payment_type_id, sl.cb_account_id, sl.despatch_action, sl.invoice_method
 , sl.email_invoice_id, sl.email_statement_id, sl.sl_analysis_id, sl.createdby, sl.alteredby, sl.lastupdated
 , sl.account_status, sl.so_price_type_id, sl.credit_limit, sl.last_statement_date, sl.edi_invoice_definition_id
 , sl.delivery_term_id, sl.date_inactive
 , p1.contact AS email_invoice, p2.contact AS email_statement
 , c.name, cu.currency, st.description AS payment_term, sy.name AS payment_type, sa.name AS sl_analysis
 , a.street1, a.street2, a.street3, a.town, a.county, a.postcode, a.countrycode, a.country, a.address AS billing_address
 , cb.name as bank_account, ts.description as tax_status, pt.name as so_price_type
 , dt.code || ' - ' || dt.description as delivery_term, dd.name as edi_invoice_definition
   FROM slmaster sl
   JOIN company c ON c.id = sl.company_id
   JOIN addressoverview a ON a.id = sl.billing_address_id
   JOIN sypaytypes sy ON sy.id = sl.payment_type_id
   JOIN syterms st ON st.id = sl.payment_term_id
   JOIN cumaster cu ON cu.id = sl.currency_id
   JOIN tax_statuses ts ON ts.id = sl.tax_status_id
   LEFT JOIN cb_accounts cb ON cb.id = sl.cb_account_id
   LEFT JOIN data_definitions dd on dd.id = sl.edi_invoice_definition_id
   LEFT JOIN sy_delivery_terms dt ON dt.id = sl.delivery_term_id
   LEFT JOIN sl_analysis sa ON sa.id = sl.sl_analysis_id
   LEFT JOIN so_price_types pt ON pt.id = sl.so_price_type_id
   LEFT JOIN partycontactmethodoverview p1 ON p1.id = sl.email_invoice_id
   LEFT JOIN partycontactmethodoverview p2 ON p2.id = sl.email_statement_id;

ALTER TABLE slmaster_overview OWNER TO "www-data";

-- View: qc_complaints_overview

DROP VIEW qc_complaints_overview;

CREATE OR REPLACE VIEW qc_complaints_overview AS 
 SELECT qc.*
 , c.name AS retailer, st.description AS product, cc.code AS complaint_code, qc.assignedto as assigned_to
 , cu.currency, scc.code || ' - ' || scc.description as supplmentary_code
   FROM qc_complaints qc
   LEFT JOIN slmaster sl ON sl.id = qc.slmaster_id
   LEFT JOIN company c ON sl.company_id = c.id
   LEFT JOIN cumaster cu ON sl.currency_id = cu.id
   LEFT JOIN st_items st ON st.id = qc.stitem_id
   LEFT JOIN qc_supplementary_complaint_codes scc ON scc.id = qc.supplementary_code_id
   LEFT JOIN qc_complaint_codes cc ON cc.id = qc.complaint_code_id;

ALTER TABLE qc_complaints_overview OWNER TO "www-data";

