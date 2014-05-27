--
-- $Revision: 1.1 $
--

DROP VIEW po_productline_items;

CREATE OR REPLACE VIEW po_productline_items AS 
 SELECT DISTINCT po.stitem_id AS id, po.stitem_id, po.stuom_id, po.usercompanyid, st.comp_class
 , uom.uom_name
 , (st.item_code::text || ' - '::text) || st.description::text AS stitem
 , st.prod_group_id
   FROM po_product_lines po
   JOIN st_items st ON po.stitem_id = st.id
   JOIN st_uoms uom ON po.stuom_id = uom.id
  ORDER BY po.stitem_id, po.stuom_id, po.usercompanyid, uom.uom_name, (st.item_code::text || ' - '::text) || st.description::text, st.prod_group_id;

ALTER TABLE po_productline_items OWNER TO "www-data";

DROP VIEW po_productline_items;

CREATE OR REPLACE VIEW po_productline_items AS 
 SELECT DISTINCT po.stitem_id AS id, po.stitem_id, po.stuom_id, po.usercompanyid, st.comp_class
 , uom.uom_name
 , (st.item_code::text || ' - '::text) || st.description::text AS stitem
 , st.prod_group_id
   FROM po_product_lines po
   JOIN st_items st ON po.stitem_id = st.id
   JOIN st_uoms uom ON po.stuom_id = uom.id
  ORDER BY po.stitem_id, po.stuom_id, po.usercompanyid, uom.uom_name, (st.item_code::text || ' - '::text) || st.description::text, st.prod_group_id;

ALTER TABLE po_productline_items OWNER TO "www-data";
