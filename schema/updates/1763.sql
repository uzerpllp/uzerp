--
-- $Revision: 1.1 $
--

--

INSERT INTO uzlets
 ("name", "title", preset, enabled, dashboard, usercompanyid)
 SELECT 'SOrdersNotInvoicedUZlet', 'Sales_orders_despatched_not_invoiced', true, true, true, id
  FROM system_companies;
 
INSERT INTO uzlet_modules
 (uzlet_id, module_id, usercompanyid)
 SELECT u.id, m.id, u.usercompanyid
   FROM uzlets u
      , modules m
  WHERE u.name = 'SOrdersNotInvoicedUZlet'
    AND m.name = 'sales_order';

INSERT INTO module_components
 (name, "type", location, module_id)
 SELECT 'sordersnotinvoiceduzlet', 'E', location||'/eglets/SOrdersNotInvoicedUZlet.php', id
   FROM modules m
  WHERE name = 'sales_order';
    