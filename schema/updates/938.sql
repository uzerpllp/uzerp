DROP VIEW companyoverview;

CREATE OR REPLACE VIEW companyoverview AS 
 SELECT c.id, c.name, c.accountnumber, c.creditlimit, c.vatnumber, c.companynumber, c.website, c.employees, c.usercompanyid
, c.parent_id, c."owner", c.assigned, c.created, c.lastupdated, c.alteredby, c.description, c.is_lead, c.party_id
, pa.address_id, a.street1, a.street2, a.street3, a.town, a.county, a.countrycode, a.postcode
, cm.contact as phone
   FROM company c
   LEFT JOIN party p ON c.party_id = p.id
   LEFT JOIN partyaddress pa ON p.id = pa.party_id AND pa.main
   LEFT JOIN address a ON a.id = pa.address_id
   LEFT JOIN party_contact_methods pcm ON p.id = pcm.party_id AND pcm.main AND pcm."type" = 'T' 
   LEFT JOIN contact_methods cm ON cm.id = pcm.contactmethod_id;