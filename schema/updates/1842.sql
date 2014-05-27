--
-- $Revision: 1.2 $
--

-- Column: override

-- ALTER TABLE pl_payments DROP COLUMN override;

ALTER TABLE pl_payments ADD COLUMN override boolean;
ALTER TABLE pl_payments ALTER COLUMN override SET DEFAULT false;

UPDATE pl_payments
   SET override = false;

-- Column: no_output

-- ALTER TABLE pl_payments DROP COLUMN no_output;

ALTER TABLE pl_payments ADD COLUMN no_output boolean;
ALTER TABLE pl_payments ALTER COLUMN no_output SET DEFAULT false;

UPDATE pl_payments
   SET no_output = false;

--
-- Modules/Components
--

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'plpaymentscontroller', 'C', location||'/controllers/PlpaymentsController.php', id, 'PL Payments'
   FROM modules m
  WHERE name = 'purchase_ledger';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'plpaymentcollection', 'M', location||'/models/PLPaymentCollection.php', id, 'PL Payment List'
   FROM modules m
  WHERE name = 'purchase_ledger';

--
-- Permissions
--

INSERT INTO permissions
 (permission, type, title, display, parent_id, position, module_id, component_id)
SELECT 'plpayments', 'c', 'PL Payments', true, per.id, pos.position, mod.module_id, mod.id
  FROM permissions per
    , (SELECT max(c.position)+1 as position
         FROM permissions c
            , permissions p
        WHERE p.type='m'
          AND p.permission='purchase_ledger'
          AND p.id=c.parent_id) pos
   , (SELECT module_id, id
        FROM module_components mc
       WHERE mc.name = 'plpaymentscontroller') mod
 WHERE type='m'
   AND permission='purchase_ledger';

INSERT INTO permissions
 (permission, type, title, display, parent_id, position, module_id, component_id)
SELECT 'view', 'a', 'View Payment Details', true, per.id, coalesce(pos.position, 1), mod.module_id, mod.id
  FROM permissions per
    , (SELECT max(c.position)+1 as position
         FROM permissions c
            , permissions p
        WHERE p.type='c'
          AND p.permission='plpayments'
          AND p.id=c.parent_id) pos
   , (SELECT module_id, id
        FROM module_components mc
       WHERE mc.name = 'plpaymentscontroller') mod
 WHERE type='c'
   AND permission='plpayments';

INSERT INTO permissions
 (permission, type, title, display, parent_id, position, module_id, component_id)
SELECT 'select_for_payment', 'a', 'Select for Payment', true, per.id, coalesce(pos.position, 1), mod.module_id, mod.id
  FROM permissions per
    , (SELECT max(c.position)+1 as position
         FROM permissions c
            , permissions p
        WHERE p.type='c'
          AND p.permission='plpayments'
          AND p.id=c.parent_id) pos
   , (SELECT module_id, id
        FROM module_components mc
       WHERE mc.name = 'plpaymentscontroller') mod
 WHERE type='c'
   AND permission='plpayments';

INSERT INTO permissions
 (permission, type, title, display, parent_id, position, module_id, component_id)
SELECT 'selected_payments', 'a', 'Selected Payments', true, per.id, coalesce(pos.position, 1), mod.module_id, mod.id
  FROM permissions per
    , (SELECT max(c.position)+1 as position
         FROM permissions c
            , permissions p
        WHERE p.type='c'
          AND p.permission='plpayments'
          AND p.id=c.parent_id) pos
   , (SELECT module_id, id
        FROM module_components mc
       WHERE mc.name = 'plpaymentscontroller') mod
 WHERE type='c'
   AND permission='plpayments';

INSERT INTO permissions
 (permission, type, title, display, parent_id, position, module_id, component_id)
SELECT 'process_override', 'a', 'Set/Unset Process Override', true, per.id, coalesce(pos.position, 1), mod.module_id, mod.id
  FROM permissions per
    , (SELECT max(c.position)+1 as position
         FROM permissions c
            , permissions p
        WHERE p.type='c'
          AND p.permission='plpayments'
          AND p.id=c.parent_id) pos
   , (SELECT module_id, id
        FROM module_components mc
       WHERE mc.name = 'plpaymentscontroller') mod
 WHERE type='c'
   AND permission='plpayments';

INSERT INTO permissions
 (permission, type, title, display, parent_id, position, module_id, component_id)
SELECT 'make_batch_payment', 'a', 'Process Payment', true, per.id, coalesce(pos.position, 1), mod.module_id, mod.id
  FROM permissions per
    , (SELECT max(c.position)+1 as position
         FROM permissions c
            , permissions p
        WHERE p.type='c'
          AND p.permission='plpayments'
          AND p.id=c.parent_id) pos
   , (SELECT module_id, id
        FROM module_components mc
       WHERE mc.name = 'plpaymentscontroller') mod
 WHERE type='c'
   AND permission='plpayments';

