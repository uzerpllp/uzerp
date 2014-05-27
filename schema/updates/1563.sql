--
-- $Revision: 1.1 $
--


INSERT INTO uzlets
(
  "name",
  title,
  preset,
  enabled,
  dashboard,
  usercompanyid
)
SELECT 'PriceCheckuzLET', 'Check Sales Price', false, true, false, id
  FROM system_companies;

INSERT INTO uzlet_modules
(
  uzlet_id,
  module_id,
  usercompanyid
)
SELECT u.id, m.id, u.usercompanyid
  FROM uzlets u
    , modules m
 WHERE u.name='PriceCheckuzLET'
   AND m.name='sales_order';

INSERT INTO module_components
(
  "name",
  "type",
  "location",
  module_id
)
SELECT 'pricecheckuzlet', 'E', "location"||'/eglets/PriceCheckuzLET.php', id
  FROM modules
 WHERE "name" = 'sales_order';
