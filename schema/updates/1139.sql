DROP VIEW so_productline_items;

CREATE OR REPLACE VIEW so_productline_items AS 
 SELECT DISTINCT so.stitem_id AS id, so.stitem_id, so.stuom_id, so.usercompanyid, uom.uom_name, (st.item_code::text || ' - '::text) || st.description::text AS stitem, st.prod_group_id
   FROM so_product_lines so
   JOIN st_items st ON so.stitem_id = st.id
   JOIN st_uoms uom ON so.stuom_id = uom.id
  ORDER BY so.stitem_id, so.stuom_id, so.usercompanyid, uom.uom_name, (st.item_code::text || ' - '::text) || st.description::text, so.stitem_id, st.prod_group_id;

DROP VIEW po_productline_items;

CREATE OR REPLACE VIEW po_productline_items AS 
 SELECT DISTINCT po.stitem_id AS id, po.stitem_id, po.stuom_id, po.usercompanyid, uom.uom_name, (st.item_code::text || ' - '::text) || st.description::text AS stitem, st.prod_group_id
   FROM po_product_lines po
   JOIN st_items st ON po.stitem_id = st.id
   JOIN st_uoms uom ON po.stuom_id = uom.id
  ORDER BY po.stitem_id, po.stuom_id, po.usercompanyid, uom.uom_name, (st.item_code::text || ' - '::text) || st.description::text, po.stitem_id, st.prod_group_id;

DROP VIEW po_items;

CREATE OR REPLACE VIEW po_items AS 
 SELECT pl.stitem_id, pl.usercompanyid, u.uom_name, (i.item_code::text || ' - '::text) || i.description::text AS stitem, i.prod_group_id, sum(pl.os_qty) AS on_order
   FROM po_lines pl
   JOIN st_items i ON i.id = pl.stitem_id
   JOIN st_uoms u ON pl.stuom_id = u.id
  WHERE pl.stitem_id IS NOT NULL AND (pl.status::text = ANY (ARRAY['A'::text, 'N'::text, 'P'::text]))
  GROUP BY pl.stitem_id, (i.item_code::text || ' - '::text) || i.description::text, u.uom_name, i.prod_group_id, pl.usercompanyid;

DROP VIEW so_items;

CREATE OR REPLACE VIEW so_items AS 
 SELECT sl.stitem_id, sl.usercompanyid, u.uom_name, (i.item_code::text || ' - '::text) || i.description::text AS stitem, i.prod_group_id, sum(sl.revised_qty) AS required
   FROM so_lines sl
   JOIN st_items i ON i.id = sl.stitem_id
   JOIN st_uoms u ON sl.stuom_id = u.id
  WHERE sl.stitem_id IS NOT NULL AND (sl.status::text = ANY (ARRAY['N'::character varying, 'R'::character varying]::text[]))
  GROUP BY sl.stitem_id, (i.item_code::text || ' - '::text) || i.description::text, u.uom_name, i.prod_group_id, sl.usercompanyid;
