CREATE OR REPLACE VIEW po_productline_items AS 
 SELECT DISTINCT po.stitem_id AS id, po.stitem_id, po.stuom_id, po.usercompanyid, uom.uom_name, (st.item_code::text || ' - '::text) || st.description::text AS stitem
   FROM po_product_lines po
   JOIN st_items st ON po.stitem_id = st.id
   JOIN st_uoms uom ON po.stuom_id = uom.id;

CREATE OR REPLACE VIEW so_productline_items AS 
 SELECT DISTINCT so.stitem_id AS id, so.stitem_id, so.stuom_id, so.usercompanyid, uom.uom_name, (st.item_code::text || ' - '::text) || st.description::text AS stitem
   FROM so_product_lines so
   JOIN st_items st ON so.stitem_id = st.id
   JOIN st_uoms uom ON so.stuom_id = uom.id;
