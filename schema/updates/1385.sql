--
-- $Revision: 1.3 $
--

CREATE INDEX st_items_item_code
  ON st_items
  USING btree
  (item_code);

CREATE INDEX st_items_item_code_comp
  ON st_items
  USING btree
  (item_code varchar_pattern_ops);

DROP VIEW po_productlines_overview;

ALTER TABLE po_product_lines ADD COLUMN prod_group_id bigint;

UPDATE po_product_lines
   SET prod_group_id=(SELECT prod_group_id
                        FROM st_items i
                       WHERE i.id = po_product_lines.stitem_id)
 WHERE stitem_id is not null;
 
CREATE OR REPLACE VIEW po_productlines_overview AS 
 SELECT po.id, po.glaccount_id, po.glcentre_id, po.plmaster_id
 , po.stitem_id, po.stuom_id, po.supplier_product_code, po.description
 , po.price, po.usercompanyid, po.tax_rate_id, po.currency_id
 , po.created, po.createdby, po.alteredby, po.lastupdated
 , po.start_date, po.end_date, po.prod_group_id, plm.payee_name, c.name AS supplier
 , uom.uom_name
 , (st.item_code::text || ' - '::text) || st.description::text AS stitem
 , st.comp_class, gla.account AS glaccount, glc.cost_centre AS glcentre, cur.currency
 , pg.description AS stproductgroup
   FROM po_product_lines po
   LEFT JOIN plmaster plm ON po.plmaster_id = plm.id
   LEFT JOIN company c ON plm.company_id = c.id
   LEFT JOIN st_items st ON po.stitem_id = st.id
   LEFT JOIN st_uoms uom ON po.stuom_id = uom.id
   LEFT JOIN st_productgroups pg ON po.prod_group_id = pg.id
   JOIN cumaster cur ON po.currency_id = cur.id
   JOIN gl_accounts gla ON po.glaccount_id = gla.id
   JOIN gl_centres glc ON po.glcentre_id = glc.id;


ALTER TABLE po_productlines_overview OWNER TO "www-data";

CREATE INDEX mf_workorders_wo_number_idx
  ON mf_workorders
  USING btree
  (wo_number);
