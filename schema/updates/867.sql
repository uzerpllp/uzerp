--
-- $Revision: 1.2 $
--

DROP VIEW companyaddressoverview;

CREATE OR REPLACE VIEW companyaddressoverview AS 
 SELECT ca.id, ca.street1, ca.street2, ca.street3, ca.town, ca.county, ca.postcode
 , ca.countrycode, c.id AS company_id, ca.name, ca.main, ca.billing, ca.shipping
 , ca.payment, ca.technical, c.name AS company
 , (((((((((((ca.street1::text || ', '::text) || COALESCE(ca.street2, ''::character varying)::text) || ', '::text) || COALESCE(ca.street3, ''::character varying)::text) || ', '::text) || ca.town::text) || ', '::text) || COALESCE(ca.county, ''::character varying)::text) || ', '::text) || COALESCE(ca.postcode, ''::character varying)::text) || ', '::text) || co.name::text AS address
 , co.name AS country
   FROM companyaddress ca
   JOIN company c ON c.party_id = ca.party_id
   JOIN countries co ON ca.countrycode = co.code;

ALTER TABLE companyaddressoverview OWNER TO "www-data";

CREATE OR REPLACE VIEW party_notesoverview AS 
 SELECT n.id, n.title, n.note, n.party_id, n.owner, n.alteredby, n.lastupdated, n.created
 , n.usercompanyid, n.note_type
 , COALESCE(c.name::text, (per.surname::text || ','::text) || per.firstname::text) AS party
   FROM party_notes n
   JOIN party p ON p.id = n.party_id
   LEFT JOIN company c ON p.id = c.party_id
   LEFT JOIN person per ON p.id = per.party_id;

ALTER TABLE party_notesoverview OWNER TO "www-data";

