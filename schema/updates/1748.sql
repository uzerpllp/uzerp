--
-- $Revision: 1.2 $
--

--

INSERT INTO modules
  ("name", "description", registered, enabled, location, defaults_enabled)
  values
  ('cache_management', 'cache management', true, false, 'modules/public_pages/cache/cache_management', false);

UPDATE module_components
   SET module_id = (SELECT id
                      FROM modules m
                     WHERE m.name = 'cache_management')
 WHERE name = 'cachecontroller';

INSERT INTO modules
  ("name", "description", registered, enabled, location, defaults_enabled)
  values
  ('output_setup', 'Output Setup', true, false, 'modules/public_pages/output/output_setup', false);

UPDATE module_components
   SET module_id = (SELECT id
                      FROM modules m
                     WHERE m.name = 'output_setup')
 WHERE module_id = (SELECT id
                      FROM modules m
                     WHERE m.name = 'output');

-- Tidy up permissions

-- Delete obsolete controller faq

DELETE FROM permissions
 WHERE permission = 'faq'
   AND type = 'c';

-- set the module_id and component_id

-- Set the module id on module permissions

UPDATE permissions p
   SET module_id = (SELECT id
                      FROM modules m
                     WHERE m.name = p.permission)
 WHERE p.type = 'm'
   AND p.module_id IS NULL;

-- Set the module id on children of module permissions

UPDATE permissions p1
   SET module_id = (SELECT module_id
                      FROM permissions p2
                     WHERE p2.id = p1.parent_id)
 WHERE p1.module_id IS NULL;

-- set the component id of controllers where the permissions module_id
-- matches to the component module_id

UPDATE permissions p
   SET component_id = (SELECT id
                         FROM module_components c
                        WHERE c.name      = p.permission||'controller'
                          AND c.module_id = p.module_id
                          AND c.type      = 'C'
                          AND p.type      = 'c'
                          AND p.component_id IS NULL)
 WHERE p.type = 'c'
   AND p.module_id IS NOT NULL
   AND p.component_id IS NULL;

-- this is required to resolve anything not updated in above script
-- because the module component for the controller sits under a different
-- module to the parent permission
-- e.g. component mfdeptscontroller is under module manufacturing_setup,
-- but in permissions controller mfdepts is under manufacturing and manufacturing_setup
-- This is a two stage process; first set the component_id then themodule_id

UPDATE permissions p
   SET component_id = (SELECT id
                         FROM module_components c
                        WHERE c.name      = p.permission||'controller'
                          AND c.type      = 'C'
                          AND p.type      = 'c'
                          AND p.component_id IS NULL)
 WHERE p.type = 'c'
   AND p.module_id IS NOT NULL
   AND p.component_id IS NULL;

UPDATE permissions p
   SET module_id = (SELECT module_id
                      FROM module_components c
                     WHERE c.id = p.component_id)
 WHERE EXISTS (SELECT 1
                 FROM module_components c
                WHERE c.id = p.component_id
                  AND c.module_id != p.module_id);

-- now set the module_id and component_id for all children where these arenot set
-- i.e. these will be the permissions with type of 'Action'

UPDATE permissions p1
   SET module_id = (SELECT module_id
                      FROM permissions p2
                     WHERE p2.id = p1.parent_id)
 WHERE p1.module_id IS NULL;

UPDATE permissions p1
   SET component_id = (SELECT component_id
                         FROM permissions p2
                        WHERE p2.id = p1.parent_id)
 WHERE p1.component_id IS NULL;
 
-- and finally, just check everything is resolved

SELECT count(*)
  FROM permissions p
  JOIN module_components c ON c.id = p.component_id
                          AND c.module_id != p.module_id;

SELECT count(*)
  FROM permissions p
 WHERE type != 'g'
   AND (module_id IS NULL
       OR (component_id IS NULL
           AND type != 'm'));

