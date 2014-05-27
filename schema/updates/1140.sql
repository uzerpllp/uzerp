ALTER TABLE so_product_lines ADD COLUMN prod_group_id bigint;

ALTER TABLE so_product_lines
  ADD CONSTRAINT so_product_lines_prod_group_id_fkey FOREIGN KEY (prod_group_id)
      REFERENCES st_productgroups (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

DROP VIEW so_productlines_overview;

CREATE OR REPLACE VIEW so_productlines_overview AS 
 SELECT so.*
 , slm.name AS customer, uom.uom_name
 , (st.item_code::text || ' - '::text) || st.description::text AS stitem
 , gla.account AS glaccount, glc.cost_centre AS glcentre, cu.currency
 , tax.description AS taxrate, pt.name AS so_price_type
   FROM so_product_lines so
   LEFT JOIN slmaster slm ON so.slmaster_id = slm.id
   LEFT JOIN st_items st ON so.stitem_id = st.id
   LEFT JOIN st_uoms uom ON so.stuom_id = uom.id
   JOIN cumaster cu ON so.currency_id = cu.id
   JOIN taxrates tax ON so.tax_rate_id = tax.id
   JOIN gl_accounts gla ON so.glaccount_id = gla.id
   JOIN gl_centres glc ON so.glcentre_id = glc.id
   LEFT JOIN so_price_types pt ON so.so_price_type_id = pt.id;

DROP VIEW so_product_line_link_overview;

CREATE OR REPLACE VIEW so_product_line_link_overview AS 
 SELECT line.id, link.product_selector_id AS parent_id, line.currency_id, line.stuom_id
 , line.slmaster_id, line.stitem_id, line.description, line.price, line.usercompanyid
 , line.so_price_type_id, line.prod_group_id, pt.name AS price_type, uom.uom_name, cu.currency
   FROM so_product_line_link link
   JOIN so_product_lines line ON line.id = link.productline_id
   LEFT JOIN so_price_types pt ON pt.id = line.so_price_type_id
   JOIN st_uoms uom ON uom.id = line.stuom_id
   JOIN cumaster cu ON cu.id = line.currency_id
  WHERE line.start_date < now() AND (line.end_date > now() OR line.end_date IS NULL);