DROP VIEW personoverview;

CREATE OR REPLACE VIEW personoverview AS
SELECT per.id, per.title, per.firstname, per.middlename, per.surname
, per.suffix, per.department, per.jobtitle, per.dob, per.ni
, per.marital, per.lang, per.company_id, per.owner, per.userdetail
, per.reports_to, per.can_call, per.can_email, per.assigned_to
, per.created, per.lastupdated, per.alteredby, per.usercompanyid
, per.crm_source, per.party_id
, ((per.surname || ', '::text) || per.firstname::text) AS name
  , pa.address_id, a.street1, a.street2, a.street3, a.town, a.county
  , a.countrycode, a.postcode, c.name AS company, c.accountnumber
  , cm.contact AS phone, m.contact AS mobile, e.contact AS email
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

DROP VIEW calendar_events_overview;

CREATE OR REPLACE VIEW calendar_events_overview AS 
 SELECT ce.*
 , ce.end_time - ce.start_time AS difference
 , c.name as calendar
   FROM calendar_events ce
   LEFT JOIN calendars c ON ce.calendar_id = c.id
  ORDER BY ce.start_time;

DROP VIEW wh_transfer_rules_overview;

CREATE OR REPLACE VIEW wh_transfer_rules_overview AS 
 SELECT t.*
 , (((l1.whstore::text || '/'::text) || l1.location::text) || '-'::text) || l1.description::text AS from_location
 , (((l2.whstore::text || '/'::text) || l2.location::text) || '-'::text) || l2.description::text AS to_location
 , l1.bin_controlled AS from_bin_controlled
 , l2.bin_controlled AS to_bin_controlled
 , a.action_name, a.type
   FROM wh_transfer_rules t
   JOIN wh_locationsoverview l1 ON l1.id = t.from_whlocation_id
   JOIN wh_locationsoverview l2 ON l2.id = t.to_whlocation_id
   JOIN wh_actions a ON a.id = t.whaction_id;