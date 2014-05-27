--
-- $Revision: 1.1 $
--

-- View: companies_in_categories_overview

DROP VIEW companies_in_categories_overview;

CREATE OR REPLACE VIEW companies_in_categories_overview AS 
 SELECT cic.*, cc.name AS category, c.name AS company, c.is_lead
   FROM companies_in_categories cic
   JOIN contact_categories cc ON cc.id = cic.category_id
   JOIN company c ON c.id = cic.company_id
  ORDER BY cc.name;

ALTER TABLE companies_in_categories_overview OWNER TO "www-data";
