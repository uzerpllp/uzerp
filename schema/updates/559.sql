DROP VIEW companyoverview;

CREATE OR REPLACE VIEW companyoverview AS 
 SELECT c.id, c.name, c.accountnumber, c.creditlimit, c.vatnumber, c.companynumber, c.website, c.employees, c.usercompanyid, c.parent_id, c."owner", c.assigned, c.created, c.lastupdated, c.alteredby, c.description, c.is_lead, c.party_id, pa.address_id, a.street1, a.street2, a.street3, a.town, a.county, a.countrycode, a.postcode, cm.contact AS phone
   FROM company c
   LEFT JOIN party p ON c.party_id = p.id
   LEFT JOIN partyaddress pa ON p.id = pa.party_id AND pa.main
   LEFT JOIN address a ON a.id = pa.address_id
   LEFT JOIN party_contact_methods pcm ON p.id = pcm.party_id AND pcm.main AND pcm."type"::text = 'T'::text
   LEFT JOIN contact_methods cm ON cm.id = pcm.contactmethod_id
 WHERE NOT EXISTS (SELECT id
                     FROM system_companies sc
                    WHERE c.id = sc.company_id);

DROP VIEW companypermissionsoverview;

CREATE OR REPLACE VIEW companypermissionsoverview AS 
 SELECT cp.id, cp.usercompanyid, cp.permissionid, p."position", p.permission, p.title, p.description, c.name
   FROM companypermissions cp
   JOIN permissions p ON cp.permissionid = p.id
   JOIN system_companies sc ON cp.usercompanyid = sc.id
   JOIN company c ON sc.company_id = c.id;

DROP VIEW personoverview;

CREATE OR REPLACE VIEW personoverview AS 
 SELECT per.id, per.title, per.firstname, per.middlename, per.surname, per.suffix, per.department, per.jobtitle
, per.dob, per.ni, per.marital, per.lang, per.company_id, per."owner", per.userdetail, per.reports_to, per.can_call
, per.can_email, per.assigned_to, per.created, per.lastupdated, per.alteredby, per.usercompanyid, per.crm_source
, per.published_username, per.allow_publish, per.party_id
, (((((((COALESCE(per.title, ''::character varying)::text || ' '::text) || per.firstname::text) || ' '::text)
 || COALESCE(per.middlename, ''::character varying)::text) || ' '::text) || per.surname::text) || ' '::text)
 || COALESCE(per.suffix, ''::character varying)::text AS name, pa.address_id, a.street1, a.street2, a.street3, a.town, a.county, a.countrycode, a.postcode, c.name AS company
   FROM person per
   LEFT JOIN company c ON per.company_id = c.id
   LEFT JOIN party p ON per.party_id = p.id
   LEFT JOIN partyaddress pa ON p.id = pa.party_id AND pa.main
   LEFT JOIN address a ON a.id = pa.address_id;