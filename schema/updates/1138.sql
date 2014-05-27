DROP VIEW personaddress_overview;

DROP VIEW personaddress;

CREATE OR REPLACE VIEW personaddress AS 
 SELECT pa.fulladdress, pa.street1, pa.street2, pa.street3, pa.town, pa.county, pa.postcode, pa.countrycode, pa.address_id as id, pa.address_id, pa.name, pa.main, pa.billing, pa.shipping, pa.payment, pa.technical, pa.party_id, pa.parent_id, pe.id AS person_id
   FROM person pe
   JOIN party p ON p.id = pe.party_id AND p.type::text = 'Person'::text
   JOIN partyaddressoverview pa ON p.id = pa.party_id;

CREATE OR REPLACE VIEW personaddress_overview AS 
 SELECT personaddress.id, personaddress.fulladdress AS address, personaddress.countrycode, personaddress.person_id, personaddress.name, personaddress.main, personaddress.billing, personaddress.shipping, personaddress.payment, personaddress.technical
   FROM personaddress personaddress;