--
-- $Revision: 1.1 $
--

-- View: po_productlines_header_overview

DROP VIEW po_productlines_header_overview;

CREATE OR REPLACE VIEW po_productlines_header_overview AS 
 SELECT plh.id, plh.stitem_id, plh.stuom_id, plh.description, plh.glaccount_id
 , plh.glcentre_id, plh.tax_rate_id, plh.prod_group_id, plh.start_date, plh.end_date
 , plh.usercompanyid, plh.created, plh.createdby, plh.alteredby, plh.lastupdated
 , (st.item_code::text || ' - '::text) || st.description::text AS stitem
 , st.latest_cost, st.std_cost
 , uom.uom_name, pg.description AS product_group, tax.description AS tax_rate
 , gla.account AS gl_account, glc.cost_centre AS gl_centre
   FROM po_product_lines_header plh
   LEFT JOIN st_items st ON plh.stitem_id = st.id
   LEFT JOIN st_uoms uom ON plh.stuom_id = uom.id
   LEFT JOIN st_productgroups pg ON plh.prod_group_id = pg.id
   JOIN taxrates tax ON plh.tax_rate_id = tax.id
   JOIN gl_accounts gla ON plh.glaccount_id = gla.id
   JOIN gl_centres glc ON plh.glcentre_id = glc.id;

ALTER TABLE po_productlines_header_overview OWNER TO "www-data";

-- View: so_productlines_header_overview

DROP VIEW so_productlines_header_overview;

CREATE OR REPLACE VIEW so_productlines_header_overview AS 
 SELECT plh.id, plh.stitem_id, plh.stuom_id, plh.description, plh.glaccount_id
 , plh.glcentre_id, plh.tax_rate_id, plh.prod_group_id, plh.start_date, plh.end_date
 , plh.usercompanyid, plh.created, plh.createdby, plh.alteredby, plh.lastupdated
 , (st.item_code::text || ' - '::text) || st.description::text AS stitem
 , st.latest_cost, st.std_cost
 , uom.uom_name, pg.description AS product_group, tax.description AS tax_rate
 , gla.account AS gl_account, glc.cost_centre AS gl_centre
   FROM so_product_lines_header plh
   LEFT JOIN st_items st ON plh.stitem_id = st.id
   LEFT JOIN st_uoms uom ON plh.stuom_id = uom.id
   LEFT JOIN st_productgroups pg ON plh.prod_group_id = pg.id
   JOIN taxrates tax ON plh.tax_rate_id = tax.id
   JOIN gl_accounts gla ON plh.glaccount_id = gla.id
   JOIN gl_centres glc ON plh.glcentre_id = glc.id;

ALTER TABLE so_productlines_header_overview OWNER TO "www-data";

