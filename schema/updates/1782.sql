--
-- $Revision: 1.1 $
--

--
-- Permissions
--

INSERT INTO permissions
 (permission, type, title, display, parent_id, position, module_id, component_id)
SELECT '_new', 'a', 'Add System Company', true, per.id, pos.position, mod.module_id, mod.id
  FROM permissions per
    , (SELECT coalesce(max(c.position)+1, 1) AS position
         FROM permissions c
            , permissions p
        WHERE p.type='c'
          AND p.permission='systemcompanys'
          AND p.id=c.parent_id) pos
   , (SELECT module_id, id
        FROM module_components mc
       WHERE mc.name = 'systemcompanyscontroller'
         AND mc.type = 'C') mod
 WHERE type='c'
   AND permission='systemcompanys'
   AND EXISTS (SELECT 1
                 FROM permissions pp
                WHERE pp.type = 'm'
                  AND pp.permission = 'system_admin'
                  AND pp.id = per.parent_id);

INSERT INTO permissions
 (permission, type, title, display, parent_id, position, module_id, component_id)
SELECT 'view_current', 'a', 'Current System Company', true, per.id, pos.position, mod.module_id, mod.id
  FROM permissions per
    , (SELECT coalesce(max(c.position)+1, 1) AS position
         FROM permissions c
            , permissions p
        WHERE p.type='c'
          AND p.permission='systemcompanys'
          AND p.id=c.parent_id) pos
   , (SELECT module_id, id
        FROM module_components mc
       WHERE mc.name = 'systemcompanyscontroller'
         AND mc.type = 'C') mod
 WHERE type='c'
   AND permission='systemcompanys'
   AND EXISTS (SELECT 1
                 FROM permissions pp
                WHERE pp.type = 'm'
                  AND pp.permission = 'system_admin'
                  AND pp.id = per.parent_id);
