--
-- $Revision: 1.1 $
--

-- Table: modules

-- Column: defaults_enabled

ALTER TABLE modules DROP COLUMN defaults_enabled;

-- Column: help_link

ALTER TABLE modules ADD COLUMN help_link character varying;

UPDATE modules
   SET help_link = 'http://wiki.uzerp.com/doku.php/'||name;

-- Table: module_components

-- Column: help_link

ALTER TABLE module_components ADD COLUMN help_link character varying;

UPDATE module_components
   SET help_link = 'http://wiki.uzerp.com/doku.php/'||replace(name, 'controller', '')
 WHERE "type" = 'C'
   AND name NOT IN ('defaultcontroller', 'indexcontroller', 'setupcontroller', 'sidebarcontroller')
   AND strpos(name, 'controller') > 0;

--
-- Modules/Components
--

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'modulessearch', 'M', location||'/models/modulesSearch.php', id, 'Moduels Search'
   FROM modules m
  WHERE name = 'system_admin';

