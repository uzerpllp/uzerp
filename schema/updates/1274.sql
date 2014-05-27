DROP VIEW companyoverview;

CREATE OR REPLACE VIEW companyoverview AS 
 SELECT c.id, c.name, c.accountnumber, c.vatnumber, c.companynumber, c.website, c.employees
 , c.usercompanyid, c.parent_id, c.owner, c.assigned, c.created, c.lastupdated, c.alteredby
 , c.description, c.is_lead, c.party_id, c.classification_id, c.source_id, c.industry_id
 , c.status_id, c.rating_id, c.type_id, c.createdby, pa.address_id, a.street1, a.street2
 , a.street3, a.town, a.county, a.countrycode, a.postcode
 , cm1.contact AS phone
 , cm2.contact AS email
   FROM company c
   LEFT JOIN party p ON c.party_id = p.id
   LEFT JOIN partyaddress pa ON p.id = pa.party_id AND pa.main
   LEFT JOIN address a ON a.id = pa.address_id
   LEFT JOIN party_contact_methods pcm1 ON p.id = pcm1.party_id AND pcm1.main AND pcm1.type::text = 'T'::text
   LEFT JOIN party_contact_methods pcm2 ON p.id = pcm2.party_id AND pcm2.main AND pcm2.type::text = 'E'::text
   LEFT JOIN contact_methods cm1 ON cm1.id = pcm1.contactmethod_id
   LEFT JOIN contact_methods cm2 ON cm2.id = pcm2.contactmethod_id
  WHERE NOT (EXISTS ( SELECT sc.id
   FROM system_companies sc
  WHERE c.id = sc.company_id));
