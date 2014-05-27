ALTER TABLE companies_in_categories ADD COLUMN id bigserial;

update companies_in_categories
set id=nextval('companies_in_categories_id_seq');

ALTER TABLE companies_in_categories
 DROP CONSTRAINT companytypexref_pkey;

ALTER TABLE companies_in_categories
 ADD CONSTRAINT companies_in_categories_id_key PRIMARY KEY(id);

ALTER TABLE companies_in_categories
 ADD CONSTRAINT companies_in_categories_ukey1 UNIQUE (company_id, category_id);

CREATE OR REPLACE VIEW companies_in_categories_overview AS 
 SELECT cic.company_id, cic.category_id, cic.id, cc.name AS category, c.name AS company
   FROM companies_in_categories cic
   JOIN contact_categories cc ON cc.id = cic.category_id
   JOIN company c ON c.id = cic.company_id
  ORDER BY cc.name;