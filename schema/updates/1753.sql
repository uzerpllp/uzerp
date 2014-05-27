--
-- $Revision: 1.1 $
--

--

CREATE OR REPLACE VIEW sl_discounts_overview AS 
 SELECT sd.id, sd.slmaster_id, sd.prod_group_id, sd.discount_percentage, sd.usercompanyid
 , sd.created, sd.createdby, sd.alteredby, sd.lastupdated, c.name AS customer
 , (pg.product_group::text || ' - '::text) || pg.description::text AS product_group
   FROM sl_discounts sd
   JOIN slmaster slm ON sd.slmaster_id = slm.id
   JOIN company c ON c.id = slm.company_id
   JOIN st_productgroups pg ON pg.id = sd.prod_group_id;

ALTER TABLE sl_discounts_overview OWNER TO "www-data";