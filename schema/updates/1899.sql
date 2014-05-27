--
-- $Revision: 1.1 $
--

--
-- Modules/Components
--

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'uzletsearch', 'M', location||'/models/UzletSearch.php', id, 'Uzlet Search'
   FROM modules m
  WHERE name = 'uzlet_setup';
