--
-- $Revision: 1.1 $
--

CREATE OR REPLACE VIEW so_items AS 
 SELECT sl.stitem_id
      , sl.usercompanyid
      , u.uom_name
      , (i.item_code::text || ' - '::text) || i.description::text AS stitem
      , i.prod_group_id
      , sum(sl.revised_qty) AS required
   FROM so_lines sl
   JOIN st_items i ON i.id = sl.stitem_id
   JOIN st_uoms u ON sl.stuom_id = u.id
  WHERE sl.stitem_id IS NOT NULL
    AND sl.status::text IN ('N', 'R', 'S')
  GROUP BY sl.stitem_id
      , (i.item_code::text || ' - '::text) || i.description::text
      , u.uom_name
      , i.prod_group_id
      , sl.usercompanyid;

