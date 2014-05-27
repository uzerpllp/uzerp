--
-- $Revision: 1.1 $
--

update so_product_lines
   set prod_group_id=(select i.prod_group_id
	                from st_items i
	               where so_product_lines.stitem_id = i.id)
 where stitem_id is not null
   and (prod_group_id!=(select i.prod_group_id
	                from st_items i
	               where so_product_lines.stitem_id = i.id)
        or prod_group_id is null);
