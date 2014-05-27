--
-- $Revision: 1.4 $
--

--

-- Table: ledger_categories

-- DROP TABLE ledger_categories;

CREATE TABLE ledger_categories
(
  id bigserial NOT NULL,
  category_id bigint NOT NULL,
  ledger_type character varying NOT NULL,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT ledger_categories_pkey PRIMARY KEY (id),
  CONSTRAINT ledger_categories_category_id_fkey FOREIGN KEY (category_id)
      REFERENCES contact_categories (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION
)
WITH (
  OIDS=TRUE
);
ALTER TABLE ledger_categories OWNER TO "www-data";

-- View: ledger_cateogries_overview

-- DROP VIEW ledger_categories_overview;

CREATE VIEW ledger_categories_overview AS
SELECT lc.*, cc.name AS category
  FROM ledger_categories lc
  JOIN contact_categories cc ON cc.id = lc.category_id;
ALTER TABLE ledger_categories_overview OWNER TO "www-data";

-- DROP VIEW ledger_category_accounts;

CREATE VIEW ledger_category_accounts AS
SELECT lc.*, cc.company_id, c.name
  FROM ledger_categories_overview lc 
  JOIN companies_in_categories cc ON cc.category_id = lc.category_id
                                 AND lc.ledger_type = 'PL'
  JOIN company c   ON c.id = cc.company_id
  JOIN plmaster pl ON pl.company_id = c.id
UNION
SELECT lc.*, cc.company_id, c.name
  FROM ledger_categories_overview lc 
  JOIN companies_in_categories cc ON cc.category_id = lc.category_id
                                 AND lc.ledger_type = 'SL'
  JOIN company c   ON c.id = cc.company_id
  JOIN slmaster sl ON sl.company_id = c.id;
  
ALTER TABLE ledger_category_accounts OWNER TO "www-data";

--
-- Populate ledger_categories
--

INSERT INTO ledger_categories
 (category_id, ledger_type, usercompanyid)
SELECT id,
       CASE
          WHEN name='Supplier' THEN 'PL'
          WHEN name='Customer' THEN 'SL'
       END AS ledger_type,
       usercompanyid
  FROM contact_categories
 WHERE name IN ('Supplier', 'Customer');

--
-- Create Module Component entries
--

INSERT INTO module_components
 (name, "type", location, module_id)
 SELECT 'ledgercategoryscontroller', 'C', location||'/controllers/LedgercategorysController.php', id
   FROM modules m
  WHERE name = 'ledger_setup';

INSERT INTO module_components
 (name, "type", location, module_id)
 SELECT 'ledgercategory', 'M', location||'/models/LedgerCategory.php', id
   FROM modules m
  WHERE name = 'ledger';

INSERT INTO module_components
 (name, "type", location, module_id)
 SELECT 'ledgercategorycollection', 'M', location||'/models/LedgerCategoryCollection.php', id
   FROM modules m
  WHERE name = 'ledger';

--
-- Create permissions
--

INSERT INTO permissions
 (permission, type, title, display, parent_id, position)
 SELECT 'ledgercategorys', 'c', 'Ledger Categories', true, id, pos.position
  FROM permissions
    , (select max(c.position)+1 as position
         from permissions c
            , permissions p
        where p.type='m'
          AND p.permission='ledger_setup'
          and p.id=c.parent_id) pos
 WHERE type='m'
   AND permission='ledger_setup';
