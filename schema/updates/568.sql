ALTER TABLE people_in_categories ADD COLUMN id bigserial;

update people_in_categories
set id=nextval('people_in_categories_id_seq');

ALTER TABLE people_in_categories
 ADD CONSTRAINT people_in_categories_id_key PRIMARY KEY(id);

ALTER TABLE people_in_categories
 ADD CONSTRAINT people_in_categories_ukey1 UNIQUE (person_id, category_id);

CREATE OR REPLACE VIEW people_in_categories_overview AS 
 SELECT pic.person_id, pic.category_id, pic.id, cc.name AS category, p.firstname, p.surname
   FROM people_in_categories pic
   JOIN contact_categories cc ON cc.id = pic.category_id
   JOIN person p ON p.id = pic.person_id
  ORDER BY cc.name;

