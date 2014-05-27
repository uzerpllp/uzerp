--
-- $Revision: 1.2 $
--

-- View: mf_operationsoverview

DROP VIEW mf_operationsoverview;

CREATE OR REPLACE VIEW mf_operationsoverview AS 
 SELECT o.*
 , s.item_code || ' - ' || s.description AS stitem
 , s.obsolete_date
 , u.uom_name AS volume_uom
 , c.centre
 , r.description AS resource
   FROM mf_operations o
   JOIN st_items s ON o.stitem_id = s.id
   JOIN st_uoms u ON o.volume_uom_id = u.id
   JOIN mf_centres c ON o.mfcentre_id = c.id
   JOIN mf_resources r ON o.mfresource_id = r.id;

ALTER TABLE mf_operationsoverview OWNER TO "www-data";
