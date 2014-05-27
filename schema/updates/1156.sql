UPDATE po_product_lines
   SET stuom_id = (select id
                     from st_uoms
                    where uom_name='Each')
 WHERE stuom_id is null;

ALTER TABLE po_product_lines ALTER COLUMN stuom_id SET NOT NULL;

UPDATE so_product_lines
   SET stuom_id = (select id
                     from st_uoms
                    where uom_name='Each')
 WHERE stuom_id is null;

ALTER TABLE so_product_lines ALTER COLUMN stuom_id SET NOT NULL;