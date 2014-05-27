ALTER TABLE so_product_lines ADD COLUMN start_date date;
ALTER TABLE so_product_lines ALTER COLUMN start_date SET DEFAULT now();
ALTER TABLE so_product_lines ADD COLUMN end_date date;

UPDATE so_product_lines
   SET start_date = '2009-01-01'
 WHERE start_date is null;

ALTER TABLE so_product_lines ALTER COLUMN start_date SET NOT NULL;

DROP VIEW so_productlines_overview;

CREATE OR REPLACE VIEW so_productlines_overview AS 
 SELECT so.*
, slm.name AS customer
, uom.uom_name
, (st.item_code::text || ' - '::text) || st.description::text AS stitem
, gla.account AS glaccount
, glc.cost_centre AS glcentre
, cu.currency, tax.description AS taxrate
, pt.name AS so_price_type
   FROM so_product_lines so
   LEFT JOIN slmaster slm ON so.slmaster_id = slm.id
   LEFT JOIN st_items st ON so.stitem_id = st.id
   LEFT JOIN st_uoms uom ON so.stuom_id = uom.id
   JOIN cumaster cu ON so.currency_id = cu.id
   JOIN taxrates tax ON so.tax_rate_id = tax.id
   JOIN gl_accounts gla ON so.glaccount_id = gla.id
   JOIN gl_centres glc ON so.glcentre_id = glc.id
   LEFT JOIN so_price_types pt ON so.so_price_type_id = pt.id;

ALTER TABLE po_product_lines ADD COLUMN start_date date;
ALTER TABLE po_product_lines ALTER COLUMN start_date SET DEFAULT now();
ALTER TABLE po_product_lines ADD COLUMN end_date date;

UPDATE po_product_lines
   SET start_date = '2009-01-01'
 WHERE start_date is null;

ALTER TABLE po_product_lines ALTER COLUMN start_date SET NOT NULL;

DROP VIEW po_productlines_overview;

CREATE OR REPLACE VIEW po_productlines_overview AS 
 SELECT po.*
, plm.name AS supplier
, uom.uom_name
, (st.item_code::text || ' - '::text) || st.description::text AS stitem
, gla.account AS glaccount
, glc.cost_centre AS glcentre
, cur.currency
   FROM po_product_lines po
   LEFT JOIN plmaster plm ON po.plmaster_id = plm.id
   LEFT JOIN st_items st ON po.stitem_id = st.id
   LEFT JOIN st_uoms uom ON po.stuom_id = uom.id
   JOIN cumaster cur ON po.currency_id = cur.id
   JOIN gl_accounts gla ON po.glaccount_id = gla.id
   JOIN gl_centres glc ON po.glcentre_id = glc.id;