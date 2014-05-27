--
-- $Revision: 1.3 $
--

-- Column: override

-- ALTER TABLE pl_payments DROP COLUMN override;

ALTER TABLE sl_analysis ADD COLUMN usercompanyid bigint;

UPDATE sl_analysis
   SET usercompanyid = (select id
                          from system_companies);

ALTER TABLE sl_analysis ALTER COLUMN usercompanyid SET NOT NULL;

ALTER TABLE sl_analysis ADD COLUMN created timestamp without time zone;
ALTER TABLE sl_analysis ALTER COLUMN created SET DEFAULT now();
ALTER TABLE sl_analysis ADD COLUMN createdby character varying;
ALTER TABLE sl_analysis ADD COLUMN alteredby character varying;
ALTER TABLE sl_analysis ADD COLUMN lastupdated timestamp without time zone;
ALTER TABLE sl_analysis ALTER COLUMN lastupdated SET DEFAULT now();

--
-- Permissions
--

INSERT INTO permissions
 (permission, type, description, title, display, parent_id, position, module_id, component_id, has_parameters)
SELECT 'view', 'a', 'Ledger Setup', 'sl_analysis', true, per.id, pos.position, mod.module_id, mod.id, true
  FROM permissions per
  JOIN permissions par on par.id = per.parent_id
                      AND par.permission = 'ledger_setup'
                      AND par.type = 'm'
    , (SELECT max(c.position)+1 as position
         FROM permissions c
            , permissions p
        WHERE p.type='c'
          AND p.permission='setup'
          AND p.id=c.parent_id) pos
   , (SELECT mc.module_id, mc.id
        FROM module_components mc
        JOIN modules m on m.id = mc.module_id
       WHERE m.name = 'ledger_setup'
         AND mc.name = 'setupcontroller') mod
 WHERE per.type='c'
   AND per.permission='setup';

INSERT INTO permission_parameters
 (permissionsid, name, value)
SELECT id, 'option', 'sl_analysis'
  FROM permissions per
 WHERE type='a'
   AND permission='view'
   AND title='sl_analysis';

DELETE FROM permissions
 WHERE permission = 'slanalysiss'
   AND type = 'c';

DELETE FROM module_components
 WHERE name = 'slanalysisscontroller';
