--
-- $Revision: 1.1 $
--

--

DROP VIEW personaddress_overview;

DROP VIEW personaddress;

DROP VIEW partyaddressoverview;

CREATE OR REPLACE VIEW partyaddressoverview AS 
 SELECT p.id, ((((((COALESCE(a.street1, ''::character varying)::text || ','::text) || (COALESCE(a.street2, ''::character varying)::text || ','::text)) || (COALESCE(a.street3, ''::character varying)::text || ','::text)) || (COALESCE(a.town, ''::character varying)::text || ','::text)) || (COALESCE(a.county, ''::character varying)::text || ','::text)) || (COALESCE(a.postcode, ''::character varying)::text || ','::text)) || COALESCE(c.name, ''::character varying::bpchar)::text AS fulladdress
 , a.street1, a.street2, a.street3, a.town, a.county, a.postcode, a.countrycode
 , p.address_id, p.name, p.main, p.billing, p.shipping, p.payment, p.technical
 , p.party_id, p.parent_id, p.usercompanyid
   FROM partyaddress p
   JOIN address a ON p.address_id = a.id
   JOIN countries c ON c.code = a.countrycode;

ALTER TABLE partyaddressoverview OWNER TO "www-data";

CREATE OR REPLACE VIEW personaddress AS 
 SELECT pa.address_id AS id, pa.fulladdress, pa.street1, pa.street2, pa.street3, pa.town, pa.county, pa.postcode
 , pa.countrycode, pa.address_id, pa.name, pa.main, pa.billing
 , pa.shipping, pa.payment, pa.technical, pa.party_id, pa.parent_id, pe.id AS person_id
   FROM person pe
   JOIN party p ON p.id = pe.party_id AND p.type::text = 'Person'::text
   JOIN partyaddressoverview pa ON p.id = pa.party_id;

ALTER TABLE personaddress OWNER TO "www-data";

CREATE OR REPLACE VIEW personaddress_overview AS 
 SELECT personaddress.id, personaddress.fulladdress AS address, personaddress.countrycode, personaddress.person_id, personaddress.name, personaddress.main, personaddress.billing, personaddress.shipping, personaddress.payment, personaddress.technical
   FROM personaddress personaddress;

ALTER TABLE personaddress_overview OWNER TO "www-data"; 