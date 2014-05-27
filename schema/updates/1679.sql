--
-- $Revision: 1.1 $
--

DROP VIEW st_itemsoverview;

CREATE OR REPLACE VIEW st_itemsoverview AS 
 SELECT i.id, i.item_code, i.description, i.alpha_code, i.comp_class, i.abc_class, i.ref1, i.batch_size, i.lead_time
 , i.text1, i.cost_decimals, i.balance, i.min_qty, i.max_qty, i.float_qty, i.free_qty, i.price, i.latest_cost, i.std_cost
 , i.std_mat, i.std_lab, i.std_ohd, i.usercompanyid, i.prod_group_id, i.type_code_id, i.uom_id, i.tax_rate_id
 , i.obsolete_date, i.std_osc, i.latest_mat, i.latest_lab, i.latest_osc, i.latest_ohd, i.qty_decimals
 , p.product_group||' - '||p.description as product_group
 , t.type_code, u.uom_name, tr.taxrate
   FROM st_items i
   JOIN st_productgroups p ON i.prod_group_id = p.id
   JOIN st_uoms u ON i.uom_id = u.id
   JOIN st_typecodes t ON i.type_code_id = t.id
   JOIN taxrates tr ON i.tax_rate_id = tr.id;

ALTER TABLE st_itemsoverview OWNER TO "www-data";