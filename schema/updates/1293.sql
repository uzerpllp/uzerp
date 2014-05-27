--
-- $Revision: 1.4 $
--

ALTER TABLE so_product_lines DROP CONSTRAINT so_product_lines_key;

ALTER TABLE so_product_lines
  ADD CONSTRAINT so_product_lines_key UNIQUE(slmaster_id, customer_product_code, start_date, usercompanyid);

DROP VIEW so_productlines_overview;

CREATE OR REPLACE VIEW so_productlines_overview AS 
 SELECT so.*
 , c.name AS customer
 , uom.uom_name
 , (st.item_code::text || ' - '::text) || st.description::text AS stitem
 , gla.account AS glaccount
 , glc.cost_centre AS glcentre
 , cu.currency
 , tax.description AS taxrate
 , pt.name AS so_price_type
 , pg.description AS stproductgroup
   FROM so_product_lines so
   LEFT JOIN slmaster slm ON so.slmaster_id = slm.id
   LEFT JOIN company c ON slm.company_id = c.id
   LEFT JOIN st_items st ON so.stitem_id = st.id
   LEFT JOIN st_uoms uom ON so.stuom_id = uom.id
   LEFT JOIN st_productgroups pg ON so.prod_group_id = pg.id
   JOIN cumaster cu ON so.currency_id = cu.id
   JOIN taxrates tax ON so.tax_rate_id = tax.id
   JOIN gl_accounts gla ON so.glaccount_id = gla.id
   JOIN gl_centres glc ON so.glcentre_id = glc.id
   LEFT JOIN so_price_types pt ON so.so_price_type_id = pt.id;

insert into permissions
(permission, type, title, display, parent_id, position)
select 'price_uplift', 'a', 'Bulk Price Change', true, id, a.position
  from permissions
     , (select max(c.position)+1 as position
          from permissions c
          join permissions p on p.id = c.parent_id
                            and p.type='c'
                            and p.permission='soproductlines') a
 where type='c'
   and permission='soproductlines';
