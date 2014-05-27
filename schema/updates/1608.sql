--
-- $Revision: 1.16 $
--

-------------------------------------------------------------------------------------------------
--
--                SO Product Lines
--
-------------------------------------------------------------------------------------------------

CREATE TABLE so_product_lines_header
(
  id bigserial NOT NULL,
  stitem_id integer,
  stuom_id integer NOT NULL,
  description text NOT NULL,
  glaccount_id integer,
  glcentre_id integer,
  tax_rate_id integer NOT NULL,
  prod_group_id bigint,
  start_date date NOT NULL DEFAULT now(),
  end_date date,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT so_product_lines_header_pkey PRIMARY KEY (id),
  CONSTRAINT so_product_lines_header_prod_group_id_fkey FOREIGN KEY (prod_group_id)
      REFERENCES st_productgroups (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT so_product_lines_header_stitem_id_fkey FOREIGN KEY (stitem_id)
      REFERENCES st_items (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT so_product_lines_header_stuom_id_fkey FOREIGN KEY (stuom_id)
      REFERENCES st_uoms (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT so_product_lines_header_glaccount_id_fkey FOREIGN KEY (glaccount_id)
      REFERENCES gl_accounts (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT so_product_lines_header_glcentre_id_fkey FOREIGN KEY (glcentre_id)
      REFERENCES gl_centres (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT so_product_lines_header_tax_rate_id_fkey FOREIGN KEY (tax_rate_id)
      REFERENCES taxrates (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT so_product_lines_header_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

ALTER TABLE so_product_lines_header OWNER TO "www-data";

-- Index: so_product_lines_header_description

-- DROP INDEX so_product_lines_header_description;

CREATE INDEX so_product_lines_header_description
  ON so_product_lines_header
  USING btree
  (description);

-- Index: so_product_lines_header_description_comp

-- DROP INDEX so_product_lines_header_description_comp;

CREATE INDEX so_product_lines_header_description_comp
  ON so_product_lines_header
  USING btree
  (description varchar_pattern_ops);

-- Index: so_product_lines_header_prod_group_id_idx

-- DROP INDEX so_product_lines_header_prod_group_id_idx;

CREATE INDEX so_product_lines_header_prod_group_id_idx
  ON so_product_lines_header
  USING btree
  (prod_group_id);

-- Index: so_product_lines_header_stitem_id_idx

-- DROP INDEX so_product_lines_header_stitem_id_idx;

CREATE INDEX so_product_lines_header_stitem_id_idx
  ON so_product_lines_header
  USING btree
  (stitem_id);

CREATE INDEX so_lines_productline_id
  ON so_lines
  USING btree
  (productline_id);

CREATE INDEX si_lines_productline_id
  ON si_lines
  USING btree
  (productline_id);

CREATE SCHEMA archive
  AUTHORIZATION "www-data";

CREATE TABLE archive.archive_so_product_lines
(
  id bigint NOT NULL,
  currency_id integer NOT NULL,
  glaccount_id integer NOT NULL,
  glcentre_id integer NOT NULL,
  slmaster_id integer,
  customer_product_code character varying,
  description text NOT NULL,
  price numeric,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  so_price_type_id bigint,
  start_date date NOT NULL DEFAULT now(),
  end_date date,
  ean character varying,
  productline_header_id integer);

ALTER TABLE archive.archive_so_product_lines OWNER TO "www-data";

-- check and cleanse the data!

-- sales order lines exist with an stitem but product line stitem is blank
-- so update the product line with the stitem
UPDATE so_product_lines pl
   SET stitem_id = (SELECT stitem_id
                         FROM so_lines sol
                        WHERE pl.id = sol.productline_id
                          AND sol.stitem_id is not null
                        LIMIT 1)
 WHERE pl.stitem_id IS NULL
   AND EXISTS (SELECT stitem_id
                 FROM so_lines sol
                WHERE pl.id = sol.productline_id
                  AND sol.stitem_id is not null);

-- sales invoice lines exist with an stitem but product line stitem is blank
-- so update the product line with the stitem
UPDATE so_product_lines pl
   SET stitem_id = (SELECT stitem_id
                         FROM si_lines sil
                        WHERE pl.id = sil.productline_id
                          AND sil.stitem_id is not null
                        LIMIT 1)
 WHERE pl.stitem_id IS NULL
   AND EXISTS (SELECT stitem_id
                 FROM si_lines sil
                WHERE pl.id = sil.productline_id
                  AND sil.stitem_id is not null);

-- set the end date on any product line with description obsolete
-- where the end date is not null
-- if the product line matches to a stock item, use the obsolete date
-- from the stock item if it exists
UPDATE so_product_lines pl
   SET end_date = (SELECT coalesce(obsolete_date, now())
                        FROM st_items st
                       WHERE st.id = pl.stitem_id
                         AND obsolete_date IS NOT NULL)
 WHERE pl.stitem_id IS NOT NULL
   AND pl.end_date IS NULL
   AND (description LIKE 'obs%'
       OR description LIKE 'OBS%'
       OR description = 'DO NOT USE');

UPDATE so_product_lines pl
   SET end_date = now()
 WHERE pl.stitem_id IS NULL
   AND pl.end_date IS NULL
   AND (description LIKE 'obs%'
       OR description LIKE 'OBS%'
       OR description = 'DO NOT USE');

-- on any product line with description obsolete
-- where the stock item is not null
-- set the description to the stock item code/description
UPDATE so_product_lines pl
   SET description = (SELECT item_code||' - '||description
                        FROM st_items st
                       WHERE st.id = pl.stitem_id)
 WHERE pl.stitem_id IS NOT NULL
   AND (description LIKE 'obs%'
       OR description LIKE 'OBS%'
       OR description = 'DO NOT USE');

-- Now insert rows into so_product_lines_header
-- (1) where the end date is null and it is for a stock item 
INSERT INTO so_product_lines_header
      (description, stitem_id, stuom_id, prod_group_id, tax_rate_id, usercompanyid)
SELECT distinct st.item_code||' - '||st.description, st.id, st.uom_id, st.prod_group_id, st.tax_rate_id, st.usercompanyid
  FROM so_product_lines pl
  JOIN st_items st on st.id = pl.stitem_id
 WHERE pl.end_date IS NULL
   AND pl.description != 'DO NOT USE'
   AND pl.description NOT LIKE 'obs%'
   AND pl.description NOT LIKE 'OBS%';

-- (2) where the end date is null and it is not a stock item 
INSERT INTO so_product_lines_header
      (description, stitem_id, stuom_id, prod_group_id, tax_rate_id, usercompanyid)
SELECT distinct description, stitem_id, stuom_id, prod_group_id, tax_rate_id, usercompanyid
  FROM so_product_lines pl
 WHERE pl.end_date IS NULL
   AND pl.stitem_id is null
   AND pl.description != 'DO NOT USE'
   AND pl.description NOT LIKE 'obs%'
   AND pl.description NOT LIKE 'OBS%';

-- (3) where no product lines header exists from (1) above and it is for a stock item
-- and sales order lines or sales invoice exist (i.e. an obsoleted product line)
INSERT INTO so_product_lines_header
      (description, stitem_id, stuom_id, prod_group_id, tax_rate_id, usercompanyid)
SELECT distinct st.item_code||' - '||st.description, st.id, st.uom_id, st.prod_group_id, st.tax_rate_id, st.usercompanyid
  FROM so_product_lines pl
  JOIN st_items st on st.id = pl.stitem_id
 WHERE NOT EXISTS (SELECT stitem_id
                     FROM so_product_lines_header ph
                    WHERE ph.stitem_id = pl.stitem_id)
   AND (EXISTS (SELECT id
                 FROM so_lines sol
                WHERE sol.productline_id = pl.id)
   	OR EXISTS (SELECT id
                 FROM si_lines sil
                WHERE sil.productline_id = pl.id)
       )
   AND pl.description != 'DO NOT USE'
   AND pl.description NOT LIKE 'obs%'
   AND pl.description NOT LIKE 'OBS%';

-- (4) where no product lines header exists from (2) above and it is not a stock item
-- and sales order or sales invoice lines exist (i.e. an obsoleted product line)
INSERT INTO so_product_lines_header
      (description, stitem_id, stuom_id, prod_group_id, tax_rate_id, usercompanyid)
SELECT distinct description, stitem_id, stuom_id, prod_group_id, tax_rate_id, usercompanyid
  FROM so_product_lines pl
 WHERE pl.stitem_id is null
   AND NOT EXISTS (SELECT id
                     FROM so_product_lines_header ph
                    WHERE ph.description = pl.description)
   AND (EXISTS (SELECT id
                 FROM so_lines sol
                WHERE sol.productline_id = pl.id)
   	OR EXISTS (SELECT id
                 FROM si_lines sil
                WHERE sil.productline_id = pl.id)
       )
   AND pl.description != 'DO NOT USE'
   AND pl.description NOT LIKE 'obs%'
   AND pl.description NOT LIKE 'OBS%';

-- add foreign key productline_header_id to product lines
ALTER TABLE so_product_lines ADD COLUMN productline_header_id integer;

ALTER TABLE so_product_lines
  ADD CONSTRAINT so_product_lines_header_id_fkey FOREIGN KEY (productline_header_id)
      REFERENCES so_product_lines_header (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE;

CREATE INDEX so_product_lines_header_id
  ON so_product_lines
  USING btree
  (productline_header_id);

-- Now match the product lines to the product header
UPDATE so_product_lines pl
   SET productline_header_id = (SELECT id
                                  FROM so_product_lines_header ph
                                 WHERE ph.stitem_id = pl.stitem_id)
 WHERE pl.stitem_id is not null;

UPDATE so_product_lines pl
   SET productline_header_id = (SELECT id
                                  FROM so_product_lines_header ph
                                 WHERE ph.description = pl.description
                                   and ph.stitem_id is null)
 WHERE pl.stitem_id is null;

UPDATE so_product_lines pl
   SET productline_header_id = (SELECT id
                                  FROM so_product_lines_header ph
                                 WHERE ph.description = pl.description)
 WHERE productline_header_id is null;

UPDATE so_product_lines pl
   SET productline_header_id = (SELECT distinct ph.id
                                  FROM so_product_lines_header ph
                                  JOIN so_lines sol ON sol.productline_id = pl.id
                                                   AND ph.description = sol.description)
 WHERE productline_header_id IS NULL
   AND EXISTS (SELECT 1
                 FROM so_product_lines_header ph
                 JOIN so_lines sol ON sol.productline_id = pl.id
                                  AND ph.description = sol.description);

-- (5) where no product lines header exists from above and it is for a stock item
-- and sales order lines or sales invoice exist (i.e. an obsoleted product line)
INSERT INTO so_product_lines_header
      (description, stitem_id, stuom_id, prod_group_id, tax_rate_id, usercompanyid)
SELECT distinct st.item_code||' - '||st.alpha_code, st.id, st.uom_id, st.prod_group_id, st.tax_rate_id, st.usercompanyid
  FROM so_product_lines pl
  JOIN si_lines sol ON sol.productline_id = pl.id
  JOIN st_items st on sol.item_description like st.item_code||'%'
                  AND st.item_code != 'DO NOT USE'
 WHERE pl.productline_header_id IS NULL
   AND (pl.description = 'DO NOT USE'
       OR pl.description LIKE 'obs%'
       OR pl.description LIKE 'OBS%')
   AND NOT EXISTS (SELECT id
                     FROM so_product_lines_header ph
                    WHERE ph.description = st.item_code||' - '||st.alpha_code);

-- (6) where no product lines header exists from above and it is not a stock item
-- and sales order lines or sales invoice exist (i.e. an obsoleted product line)
INSERT INTO so_product_lines_header
      (description, stuom_id, prod_group_id, tax_rate_id, usercompanyid)
SELECT distinct sol.item_description, pl.stuom_id, pl.prod_group_id, pl.tax_rate_id, pl.usercompanyid
  FROM so_product_lines pl
  JOIN si_lines sol ON sol.productline_id = pl.id
 WHERE pl.productline_header_id IS NULL
   AND (pl.description = 'DO NOT USE'
       OR pl.description LIKE 'obs%'
       OR pl.description LIKE 'OBS%')
   AND NOT EXISTS (SELECT id
                     FROM so_product_lines_header ph
                    WHERE ph.description = sol.item_description);

UPDATE so_product_lines pl
   SET productline_header_id = (SELECT distinct ph.id
                                  FROM so_product_lines_header ph
                                  JOIN si_lines sol ON sol.productline_id = pl.id
                                  JOIN st_items st on sol.item_description like st.item_code||'%'
                                                  AND st.item_code != 'DO NOT USE'
                                                  AND ph.stitem_id  = st.id)
 WHERE productline_header_id IS NULL
   AND EXISTS (SELECT distinct 1
                 FROM so_product_lines_header ph
                 JOIN si_lines sol ON sol.productline_id = pl.id
                                  JOIN st_items st on sol.item_description like st.item_code||'%'
                                                  AND st.item_code != 'DO NOT USE'
                                                  AND ph.stitem_id  = st.id);

UPDATE so_product_lines pl
   SET productline_header_id = (SELECT distinct ph.id
                                  FROM so_product_lines_header ph
                                  JOIN si_lines sol ON sol.productline_id = pl.id
                                  AND ph.description = sol.item_description)
 WHERE productline_header_id IS NULL
   AND EXISTS (SELECT distinct 1
                 FROM so_product_lines_header ph
                 JOIN si_lines sol ON sol.productline_id = pl.id
                                  AND ph.description = sol.item_description);

-- Any product line not matching to a product line header should be obsolete
-- and not exist in sales order/sales invoice lines
SELECT count(*)
  FROM so_product_lines pl
 WHERE productline_header_id IS NULL
   AND (EXISTS (SELECT id
                  FROM so_lines sol
                 WHERE sol.productline_id = pl.id)
     OR EXISTS (SELECT id
                   FROM si_lines sil
                  WHERE sil.productline_id = pl.id));

-- set the start date on the header to be the earliest the lines
UPDATE so_product_lines_header ph
   SET start_date = (SELECT min(pl.start_date)
                       FROM so_product_lines pl
                      WHERE pl.productline_header_id = ph.id)
 WHERE EXISTS (SELECT pl.id
                     FROM so_product_lines pl
                    WHERE pl.productline_header_id = ph.id);

-- set the end date on the header if all lines have ended
UPDATE so_product_lines_header ph
   SET end_date = (SELECT max(end_date)
                     FROM so_product_lines pl
                    WHERE pl.productline_header_id = ph.id)
 WHERE NOT EXISTS (SELECT pl.id
                     FROM so_product_lines pl
                    WHERE pl.productline_header_id = ph.id
                      AND pl.end_date is null);
                    
-- now delete any unmatched product lines
select count(*)
  FROM so_product_lines pl
 WHERE productline_header_id IS null;

CREATE TABLE so_product_lines_unmatched
as
SELECT *
  FROM so_product_lines pl
 WHERE productline_header_id IS null;

SELECT count(*)
  FROM so_product_lines_unmatched;

DELETE FROM so_product_lines
 WHERE productline_header_id IS NULL;

-- Sanity Checks!

-- Unmatched product lines (should have been deleted above!)
select count(*)
  from so_product_lines pl
  where productline_header_id is null;

ALTER TABLE so_product_lines ALTER COLUMN productline_header_id SET NOT NULL;

-- Overlapping product lines (could run this before starting)
-- lists all current product lines pairs where both lines are current
-- and lines are for same customer, stock item and price type
select pl1.id, pl2.id, c.name, pl1.description, pl1.price, pl1.start_date, pl1.end_date, pl2.price, pl2.start_date, pl2.end_date
  from so_product_lines pl1
  join so_product_lines pl2 on pl1.stitem_id = pl2.stitem_id
  left join slmaster slm on pl1.slmaster_id = slm.id
  left join company c on c.id = slm.company_id
 where pl1.end_date is null
   and pl2.end_date is null
   and pl1.id < pl2.id
   and ((pl1.slmaster_id is null and pl2.slmaster_id is null)
       or pl1.slmaster_id = pl2.slmaster_id)
   and ((pl1.so_price_type_id is null and pl2.so_price_type_id is null)
       or pl1.so_price_type_id = pl2.so_price_type_id)
   and exists (select sol1.id
                 from so_lines sol1
                where pl1.id = sol1.productline_id)
   and exists (select sol2.id
                 from so_lines sol2
                where pl2.id = sol2.productline_id)
 order by pl1.slmaster_id, pl1.stitem_id;

-- check for header starting after any of its lines (should be zero)
select count(*)
  from so_product_lines_header plh
 where exists (select 1
                 from so_product_lines pl
                where plh.id = pl.productline_header_id
                  and pl.start_date < plh.start_date);

-- check for header ending before any of its lines (should be zero)
select count(*)
  from so_product_lines_header plh
 where exists (select 1
                 from so_product_lines pl
                where plh.id = pl.productline_header_id
                  and coalesce(pl.end_date, now()) > coalesce(plh.end_date, now()));

-- update the header with the latest glaccount/glcentre from a linked line
update so_product_lines_header plh
   set glaccount_id = (select spl1.glaccount_id
                         from so_product_lines spl1
                            , (select productline_header_id, max(start_date) as start_date
                                 from so_product_lines
                                group by productline_header_id
                               ) spl2
                        where spl1.productline_header_id = spl2.productline_header_id
                          and spl1.start_date = spl2.start_date
                          and plh.id = spl1.productline_header_id
                        limit 1)
     , glcentre_id = (select spl1.glcentre_id
                         from so_product_lines spl1
                            , (select productline_header_id, max(start_date) as start_date
                                 from so_product_lines
                                group by productline_header_id
                               ) spl2
                        where spl1.productline_header_id = spl2.productline_header_id
                          and spl1.start_date = spl2.start_date
                          and plh.id = spl1.productline_header_id
                        limit 1);

-- and check that all are matched
select count(*)
  from so_product_lines_header splh
 where not exists (select id
                     from so_product_lines spl
                    where spl.productline_header_id = splh.id
                      and spl.glaccount_id = splh.glaccount_id
                      and spl.glcentre_id  = splh.glcentre_id);

-- if above count is zero, then set the account/centre as not null
ALTER TABLE so_product_lines_header ALTER COLUMN glaccount_id SET NOT NULL;
ALTER TABLE so_product_lines_header ALTER COLUMN glcentre_id SET NOT NULL;

-- now, drop the obsolete columns on product lines

DROP VIEW IF EXISTS so_productline_items;

DROP VIEW IF EXISTS so_productlines_overview;

DROP view IF EXISTS so_product_line_link_overview;

ALTER TABLE so_product_lines DROP COLUMN stitem_id;
ALTER TABLE so_product_lines DROP COLUMN stuom_id;
ALTER TABLE so_product_lines DROP COLUMN tax_rate_id;
ALTER TABLE so_product_lines DROP COLUMN prod_group_id;

-- and create the overviews

DROP VIEW IF EXISTS so_productlines_header_overview;

DROP VIEW IF EXISTS so_productline_items;

DROP VIEW IF EXISTS so_productlines_overview;

CREATE OR REPLACE VIEW so_productlines_header_overview AS 
 SELECT plh.*
 , (st.item_code::text || ' - '::text) || st.description::text AS stitem
 , uom.uom_name
 , pg.product_group || ' - ' || pg.description AS product_group
 , tax.description AS tax_rate
 , gla.account AS gl_account
 , glc.cost_centre AS gl_centre
   FROM so_product_lines_header plh
   LEFT JOIN st_items st ON plh.stitem_id = st.id
   LEFT JOIN st_uoms uom ON plh.stuom_id = uom.id
   LEFT JOIN st_productgroups pg ON plh.prod_group_id = pg.id
   JOIN taxrates tax ON plh.tax_rate_id = tax.id
   JOIN gl_accounts gla ON plh.glaccount_id = gla.id
   JOIN gl_centres glc ON plh.glcentre_id = glc.id;

ALTER TABLE so_productlines_header_overview OWNER TO "www-data";

CREATE OR REPLACE VIEW so_productline_items AS 
 SELECT DISTINCT plh.stitem_id AS id, plh.stitem_id, plh.stuom_id, plh.usercompanyid
 , uom.uom_name
 , (st.item_code::text || ' - '::text) || st.description::text AS stitem
 , st.prod_group_id
   FROM so_product_lines_header plh
   JOIN st_items st ON plh.stitem_id = st.id
   JOIN st_uoms uom ON plh.stuom_id = uom.id
  ORDER BY plh.stitem_id, plh.stuom_id, plh.usercompanyid, uom.uom_name, (st.item_code::text || ' - '::text) || st.description::text, st.prod_group_id;

ALTER TABLE so_productline_items OWNER TO "www-data";

CREATE OR REPLACE VIEW so_productlines_overview AS 
 SELECT pl.*
 , plh.description as product, plh.stitem_id, plh.stuom_id, plh.tax_rate_id, plh.prod_group_id
 , c.name AS customer
 , uom.uom_name
 , (st.item_code::text || ' - '::text) || st.description::text AS stitem
 , gla.account AS glaccount, glc.cost_centre AS glcentre
 , cu.currency
 , tax.description AS taxrate
 , pt.name AS so_price_type
 , pg.product_group||' - '||pg.description AS stproductgroup
   FROM so_product_lines pl
   JOIN so_product_lines_header plh ON pl.productline_header_id = plh.id
   LEFT JOIN slmaster slm ON pl.slmaster_id = slm.id
   LEFT JOIN company c ON slm.company_id = c.id
   LEFT JOIN st_items st ON plh.stitem_id = st.id
   LEFT JOIN st_uoms uom ON plh.stuom_id = uom.id
   LEFT JOIN st_productgroups pg ON plh.prod_group_id = pg.id
   JOIN cumaster cu ON pl.currency_id = cu.id
   JOIN taxrates tax ON plh.tax_rate_id = tax.id
   JOIN gl_accounts gla ON pl.glaccount_id = gla.id
   JOIN gl_centres glc ON pl.glcentre_id = glc.id
   LEFT JOIN so_price_types pt ON pl.so_price_type_id = pt.id;

ALTER TABLE so_productlines_overview OWNER TO "www-data";

-- Update the product line link table
ALTER TABLE so_product_line_link DROP CONSTRAINT so_product_line_link_productline_id_fkey;

DROP INDEX so_product_line_link_target_id;

DROP INDEX so_product_line_link_product_selector;

ALTER TABLE so_product_line_link DROP CONSTRAINT so_product_line_link_selector_id_fkey;

ALTER TABLE so_product_line_link DROP CONSTRAINT so_product_line_link_target_id_fkey;

delete from so_product_line_link pll
 where not exists (select id
                          from so_product_lines pl
                         where pl.id = pll.target_id);

update so_product_line_link pll
   set target_id = (select productline_header_id
                          from so_product_lines pl
                         where pl.id = pll.target_id);

select count(*)
  from so_product_line_link;

create table so_product_line_link_copy
as
select *
  from so_product_line_link;

select count(*)
  from so_product_line_link_copy;

truncate table so_product_line_link;

select resetsequences();

insert into so_product_line_link
(item_id, target_id)
select distinct item_id, target_id
  from so_product_line_link_copy;

ALTER TABLE so_product_line_link
  ADD CONSTRAINT so_product_line_link_selector_id_fkey FOREIGN KEY (item_id)
      REFERENCES so_product_selector (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE so_product_line_link
  ADD CONSTRAINT so_product_line_link_target_id_fkey FOREIGN KEY (target_id)
      REFERENCES so_product_lines_header (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE;

CREATE INDEX so_product_line_link_target_id
  ON so_product_line_link
  USING btree
  (target_id);
  
CREATE INDEX so_product_line_link_item_id
  ON so_product_line_link
  USING btree
  (item_id);

-- Views to get orders/invoices for product line header
DROP VIEW IF EXISTS so_product_orders;

CREATE OR REPLACE VIEW so_product_orders AS
SELECT sh.*, sl.id as orderline_id, sl.productline_id, sl.status as line_status, spl.productline_header_id
  FROM so_headeroverview sh
  JOIN so_lines sl ON sh.id = sl.order_id
  JOIN so_product_lines spl ON spl.id = sl.productline_id;

ALTER TABLE so_product_orders OWNER TO "www-data";

DROP VIEW IF EXISTS so_product_invoices;

CREATE OR REPLACE VIEW so_product_invoices AS
SELECT sh.*, sl.id as invoiceline_id, sl.productline_id, spl.productline_header_id
  FROM si_headeroverview sh
  JOIN si_lines sl ON sh.id = sl.invoice_id
  JOIN so_product_lines spl ON spl.id = sl.productline_id;

ALTER TABLE so_product_invoices OWNER TO "www-data";

DROP VIEW project_sales_invoices;

DROP VIEW si_linesoverview;

DROP VIEW so_linesoverview;

CREATE OR REPLACE VIEW so_linesoverview AS 
 SELECT sl.*, sh.due_date, sh.order_date, sh.order_number, sh.slmaster_id, sh.type
 , c.name AS customer, (gla.account::text || ' - '::text) || gla.description::text AS glaccount
 , (glc.cost_centre::text || ' - '::text) || glc.description::text AS glcentre, tax.description AS taxrate
 , (i.item_code::text || ' - '::text) || i.description::text AS stitem, i.item_code, uom.uom_name
   FROM so_lines sl
   JOIN so_header sh ON sh.id = sl.order_id
   JOIN slmaster slm ON sh.slmaster_id = slm.id
   JOIN company c ON slm.company_id = c.id
   JOIN taxrates tax ON sl.tax_rate_id = tax.id
   JOIN gl_accounts gla ON sl.glaccount_id = gla.id
   JOIN gl_centres glc ON sl.glcentre_id = glc.id
   LEFT JOIN st_uoms uom ON sl.stuom_id = uom.id
   LEFT JOIN st_items i ON i.id = sl.stitem_id;

ALTER TABLE so_linesoverview OWNER TO "www-data";

CREATE OR REPLACE VIEW si_linesoverview AS 
 SELECT sl.*
 , sh.invoice_date, sh.invoice_number, sh.transaction_type, sh.slmaster_id, sh.status
 , soh.order_number
 , c.name AS customer, i.item_code, (i.item_code::text || ' - '::text) || i.description::text AS stitem, uom.uom_name
   FROM si_lines sl
   JOIN si_header sh ON sh.id = sl.invoice_id
   JOIN slmaster slm ON sh.slmaster_id = slm.id
   JOIN company c ON slm.company_id = c.id
   LEFT JOIN so_header soh ON sl.sales_order_id = soh.id
   LEFT JOIN st_uoms uom ON sl.stuom_id = uom.id
   LEFT JOIN st_items i ON i.id = sl.stitem_id;

ALTER TABLE si_linesoverview OWNER TO "www-data";

CREATE OR REPLACE VIEW project_sales_invoices AS 
 SELECT pcc.id, pcc.project_id, pcc.task_id, pcc.item_id, pcc.item_type, pcc.source_id, pcc.source_type, pcc.stitem_id, pcc.description, pcc.quantity, pcc.unit_price, pcc.net_value, pcc.usercompanyid, pcc.created, pcc.createdby, pcc.alteredby, pcc.lastupdated, sil.invoice_id, sil.invoice_number, sil.line_number, sil.slmaster_id, sil.customer, sil.description AS line_description, sil.net_value AS invoice_value, sil.tax_value, sil.gross_value, sil.invoice_date
   FROM project_costs_charges pcc
   JOIN si_linesoverview sil ON sil.id = pcc.item_id AND pcc.item_type::text = 'SI'::text;

ALTER TABLE project_sales_invoices OWNER TO "www-data";

ALTER TABLE si_lines
  ADD CONSTRAINT si_lines_productline_id_fkey FOREIGN KEY (productline_id)
      REFERENCES so_product_lines (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

-- add the new module components
INSERT INTO module_components
  ("name", "type", location, module_id)
  SELECT 'soproductlineheaderscontroller', 'C', m.location||'/controllers/SoproductlineheadersController.php', id
    FROM modules m
   WHERE m.name = 'sales_order';

INSERT INTO module_components
  ("name", "type", location, module_id)
  SELECT 'soproductlineheader', 'M', m.location||'/models/SOProductlineHeader.php', id
    FROM modules m
   WHERE m.name = 'sales_order';

INSERT INTO module_components
  ("name", "type", location, module_id)
  SELECT 'soproductlineheadercollection', 'M', m.location||'/models/SOProductlineHeaderCollection.php', id
    FROM modules m
   WHERE m.name = 'sales_order';

-- Update permissions

UPDATE permissions
   SET position = position+1
 WHERE position > 1
   AND parent_id = (SELECT id
                      FROM permissions y
                     WHERE y.permission = 'sales_order'
                       AND y.type = 'm'
                       AND (y.parent_id is null
                           OR y.parent_id = (select z.id
                                             from permissions z
                                            where parent_id is null
                                              and z.id = y.parent_id)));

INSERT INTO permissions
  (permission, type, description, title, display, position, parent_id)
SELECT 'soproductlineheaders', 'c', 'Sales Order Products', 'Products', TRUE, 2, id
  FROM permissions y
 WHERE y.permission = 'sales_order'
   AND y.type = 'm'
   AND (y.parent_id IS NULL
       OR y.parent_id = (SELECT z.id
                           FROM permissions z
                          WHERE parent_id IS NULL
                            AND z.id = y.parent_id));

INSERT INTO permissions
  (permission, type, description, title, display, position, parent_id)
SELECT '_new', 'a', 'Add Product', 'Add Product', TRUE, 1, id
  FROM permissions y
 WHERE y.permission = 'soproductlineheaders'
   AND y.type = 'c';

INSERT INTO permissions
  (permission, type, description, title, display, position, parent_id)
SELECT 'edit', 'a', 'Edit Product', 'Edit Product', FALSE, 2, id
  FROM permissions y
 WHERE y.permission = 'soproductlineheaders'
   AND y.type = 'c';

INSERT INTO permissions
  (permission, type, description, title, display, position, parent_id)
SELECT 'delete', 'a', 'Delete Product', 'Delete Product', FALSE, 3, id
  FROM permissions y
 WHERE y.permission = 'soproductlineheaders'
   AND y.type = 'c';

-------------------------------------------------------------------------------------------------
--
--                PO Product Lines
--
-------------------------------------------------------------------------------------------------

CREATE TABLE po_product_lines_header
(
  id bigserial NOT NULL,
  stitem_id integer,
  stuom_id integer NOT NULL,
  description text NOT NULL,
  glaccount_id integer,
  glcentre_id integer,
  tax_rate_id integer NOT NULL,
  prod_group_id bigint,
  start_date date NOT NULL DEFAULT now(),
  end_date date,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT po_product_lines_header_pkey PRIMARY KEY (id),
  CONSTRAINT po_product_lines_header_prod_group_id_fkey FOREIGN KEY (prod_group_id)
      REFERENCES st_productgroups (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT po_product_lines_header_stitem_id_fkey FOREIGN KEY (stitem_id)
      REFERENCES st_items (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT po_product_lines_header_stuom_id_fkey FOREIGN KEY (stuom_id)
      REFERENCES st_uoms (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT po_product_lines_header_glaccount_id_fkey FOREIGN KEY (glaccount_id)
      REFERENCES gl_accounts (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT po_product_lines_header_glcentre_id_fkey FOREIGN KEY (glcentre_id)
      REFERENCES gl_centres (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT po_product_lines_header_tax_rate_id_fkey FOREIGN KEY (tax_rate_id)
      REFERENCES taxrates (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT po_product_lines_header_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

ALTER TABLE po_product_lines_header OWNER TO "www-data";

-- Index: po_product_lines_header_description

-- DROP INDEX po_product_lines_header_description;

CREATE INDEX po_product_lines_header_description
  ON po_product_lines_header
  USING btree
  (description);

-- Index: po_product_lines_header_description_comp

-- DROP INDEX po_product_lines_header_description_comp;

CREATE INDEX po_product_lines_header_description_comp
  ON po_product_lines_header
  USING btree
  (description varchar_pattern_ops);

-- Index: po_product_lines_header_prod_group_id_idx

-- DROP INDEX po_product_lines_header_prod_group_id_idx;

CREATE INDEX po_product_lines_header_prod_group_id_idx
  ON po_product_lines_header
  USING btree
  (prod_group_id);

-- Index: po_product_lines_header_stitem_id_idx

-- DROP INDEX po_product_lines_header_stitem_id_idx;

CREATE INDEX po_product_lines_header_stitem_id_idx
  ON po_product_lines_header
  USING btree
  (stitem_id);

CREATE INDEX po_lines_productline_id
  ON po_lines
  USING btree
  (productline_id);

-- check and cleanse the data!

-- purchase order lines exist with an stitem but product line stitem is blank
-- so update the product line with the stitem
UPDATE po_product_lines pl
   SET stitem_id = (SELECT stitem_id
                         FROM po_lines pol
                        WHERE pl.id = pol.productline_id
                        LIMIT 1)
 WHERE pl.stitem_id IS NULL
   AND EXISTS (SELECT stitem_id
                 FROM po_lines pol
                WHERE pl.id = pol.productline_id);

-- set the end date on any product line with description obsolete
-- where the end date is not null
-- if the product line matches to s stock item, use the obsolete date
-- from the stock item if it exists
UPDATE po_product_lines pl
   SET end_date = (SELECT coalesce(obsolete_date, now())
                        FROM st_items st
                       WHERE st.id = pl.stitem_id)
 WHERE pl.stitem_id IS NOT NULL
   AND pl.end_date IS NULL
   AND (description LIKE 'obs%'
       OR description LIKE 'OBS%'
       OR description = 'DO NOT USE');

UPDATE po_product_lines pl
   SET end_date = now()
 WHERE pl.stitem_id IS NULL
   AND pl.end_date IS NULL
   AND (description LIKE 'obs%'
       OR description LIKE 'OBS%'
       OR description = 'DO NOT USE');

-- on any product line with description obsolete
-- where the stock item is not null
-- set the description to the stock item code/description
UPDATE po_product_lines pl
   SET description = (SELECT item_code||' - '||description
                        FROM st_items st
                       WHERE st.id = pl.stitem_id)
 WHERE pl.stitem_id IS NOT NULL
   AND (description LIKE 'obs%'
       OR description LIKE 'OBS%'
       OR description = 'DO NOT USE');

-- Now insert rows into po_product_lines_header
-- (1) where the end date is null and it is for a stock item 
INSERT INTO po_product_lines_header
      (description, stitem_id, stuom_id, prod_group_id, tax_rate_id, usercompanyid)
SELECT distinct st.item_code||' - '||st.description, st.id, st.uom_id, st.prod_group_id, st.tax_rate_id, st.usercompanyid
  FROM po_product_lines pl
  JOIN st_items st on st.id = pl.stitem_id
 WHERE pl.end_date IS NULL
   AND pl.description != 'DO NOT USE'
   AND pl.description not like 'obs%'
   AND pl.description not like 'OBS%';

-- (2) where the end date is null and it is not a stock item 
INSERT INTO po_product_lines_header
      (description, stitem_id, stuom_id, prod_group_id, tax_rate_id, usercompanyid)
SELECT distinct description, stitem_id, stuom_id, prod_group_id, tax_rate_id, usercompanyid
  FROM po_product_lines pl
 WHERE pl.end_date IS NULL
   AND pl.stitem_id is null
   AND pl.description != 'DO NOT USE'
   AND pl.description not like 'obs%'
   AND pl.description not like 'OBS%';

-- (3) where no product lines header exists from (1) above and it is for a stock item
-- and sales order lines exist (i.e. an obsoleted product line)
INSERT INTO po_product_lines_header
      (description, stitem_id, stuom_id, prod_group_id, tax_rate_id, usercompanyid)
SELECT distinct st.item_code||' - '||st.description, st.id, st.uom_id, st.prod_group_id, st.tax_rate_id, st.usercompanyid
  FROM po_product_lines pl
  JOIN st_items st on st.id = pl.stitem_id
 WHERE NOT EXISTS (SELECT stitem_id
                     FROM po_product_lines_header ph
                    WHERE ph.stitem_id = pl.stitem_id)
   AND EXISTS (SELECT id
                 FROM po_lines pol
                WHERE pol.productline_id = pl.id);

-- (4) where no product lines header exists from (2) above and it is not a stock item
-- and sales order lines exist (i.e. an obsoleted product line)
INSERT INTO po_product_lines_header
      (description, stitem_id, stuom_id, prod_group_id, tax_rate_id, usercompanyid)
SELECT distinct description, stitem_id, stuom_id, prod_group_id, tax_rate_id, usercompanyid
  FROM po_product_lines pl
 WHERE pl.stitem_id is null
   AND NOT EXISTS (SELECT id
                     FROM po_product_lines_header ph
                    WHERE ph.description = pl.description)
   AND EXISTS (SELECT id
                 FROM po_lines pol
                WHERE pol.productline_id = pl.id);

-- add foreign key productline_header_id to product lines
ALTER TABLE po_product_lines ADD COLUMN productline_header_id integer;

ALTER TABLE po_product_lines
  ADD CONSTRAINT po_product_lines_header_id_fkey FOREIGN KEY (productline_header_id)
      REFERENCES po_product_lines_header (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE;

-- Now match the product lines to the product header
UPDATE po_product_lines pl
   SET productline_header_id = (SELECT id
                                  FROM po_product_lines_header ph
                                 WHERE ph.stitem_id = pl.stitem_id)
 WHERE pl.stitem_id is not null;

UPDATE po_product_lines pl
   SET productline_header_id = (SELECT id
                                  FROM po_product_lines_header ph
                                 WHERE ph.description = pl.description
                                   and ph.stitem_id is null)
 WHERE pl.stitem_id is null;

-- Any product line not matching to a product line header should be obsolete
-- and not exist in sales order/sales invoice lines
SELECT count(*)
  FROM po_product_lines pl
 WHERE productline_header_id IS NULL
   AND (EXISTS (SELECT id
                  FROM po_lines pol
                 WHERE pol.productline_id = pl.id));

-- set the start date on the header to be the earliest the lines
UPDATE po_product_lines_header ph
   SET start_date = (SELECT min(pl.start_date)
                       FROM po_product_lines pl
                      WHERE pl.productline_header_id = ph.id)
 WHERE EXISTS (SELECT pl.id
                     FROM po_product_lines pl
                    WHERE pl.productline_header_id = ph.id);

-- set the end date on the header if all lines have ended
UPDATE po_product_lines_header ph
   SET end_date = (SELECT max(end_date)
                     FROM po_product_lines pl
                    WHERE pl.productline_header_id = ph.id)
 WHERE NOT EXISTS (SELECT pl.id
                     FROM po_product_lines pl
                    WHERE pl.productline_header_id = ph.id
                      AND pl.end_date is null);
                    
-- now delete any unmatched product lines
select count(*)
  FROM po_product_lines pl
 WHERE productline_header_id IS null;

CREATE TABLE po_product_lines_unmatched
as
SELECT *
  FROM po_product_lines pl
 WHERE productline_header_id IS null;

SELECT count(*)
  FROM po_product_lines_unmatched;

DELETE FROM po_product_lines
 WHERE productline_header_id IS NULL;

-- Sanity Checks!

-- Unmatched product lines (should have been deleted above!)
select count(*)
  from po_product_lines pl
  where productline_header_id is null;

ALTER TABLE po_product_lines ALTER COLUMN productline_header_id SET NOT NULL;

-- Overlapping product lines (could run this before starting)
-- lists all current product lines pairs where both lines are current
-- and lines are for same customer, stock item and price type
select pl1.id, pl2.id, c.name, pl1.description, pl1.price, pl1.start_date, pl1.end_date, pl2.price, pl2.start_date, pl2.end_date
  from po_product_lines pl1
  join po_product_lines pl2 on pl1.stitem_id = pl2.stitem_id
  left join plmaster plm on pl1.plmaster_id = plm.id
  left join company c on c.id = plm.company_id
 where pl1.end_date is null
   and pl2.end_date is null
   and pl1.id < pl2.id
   and ((pl1.plmaster_id is null and pl2.plmaster_id is null)
       or pl1.plmaster_id = pl2.plmaster_id)
   and exists (select pol1.id
                 from po_lines pol1
                where pl1.id = pol1.productline_id)
   and exists (select pol2.id
                 from po_lines pol2
                where pl2.id = pol2.productline_id)
 order by pl1.plmaster_id, pl1.stitem_id;

-- check for header starting after any of its lines (should be zero)
select count(*)
  from po_product_lines_header plh
 where exists (select 1
                 from po_product_lines pl
                where plh.id = pl.productline_header_id
                  and pl.start_date < plh.start_date);

-- check for header ending before any of its lines (should be zero)
select count(*)
  from po_product_lines_header plh
 where exists (select 1
                 from po_product_lines pl
                where plh.id = pl.productline_header_id
                  and coalesce(pl.end_date, now()) > coalesce(plh.end_date, now()));

-- update the header with the latest glaccount/glcentre from a linked line
update po_product_lines_header plh
   set glaccount_id = (select spl1.glaccount_id
                         from po_product_lines spl1
                            , (select productline_header_id, max(start_date) as start_date
                                 from po_product_lines
                                group by productline_header_id
                               ) spl2
                        where spl1.productline_header_id = spl2.productline_header_id
                          and spl1.start_date = spl2.start_date
                          and plh.id = spl1.productline_header_id
                        limit 1)
     , glcentre_id = (select spl1.glcentre_id
                         from po_product_lines spl1
                            , (select productline_header_id, max(start_date) as start_date
                                 from po_product_lines
                                group by productline_header_id
                               ) spl2
                        where spl1.productline_header_id = spl2.productline_header_id
                          and spl1.start_date = spl2.start_date
                          and plh.id = spl1.productline_header_id
                        limit 1);

-- and check that all are matched
select count(*)
  from po_product_lines_header splh
 where not exists (select id
                     from po_product_lines spl
                    where spl.productline_header_id = splh.id
                      and spl.glaccount_id = splh.glaccount_id
                      and spl.glcentre_id  = splh.glcentre_id);

-- if above count is zero, then set the account/centre as not null
ALTER TABLE po_product_lines_header ALTER COLUMN glaccount_id SET NOT NULL;
ALTER TABLE po_product_lines_header ALTER COLUMN glcentre_id SET NOT NULL;

-- now, drop the obsolete columns on product lines
DROP VIEW po_productline_items;

DROP VIEW po_productlines_overview;

ALTER TABLE po_product_lines DROP COLUMN stitem_id;
ALTER TABLE po_product_lines DROP COLUMN stuom_id;
ALTER TABLE po_product_lines DROP COLUMN tax_rate_id;
ALTER TABLE po_product_lines DROP COLUMN prod_group_id;

ALTER TABLE po_product_lines DROP CONSTRAINT po_product_lines_key;

ALTER TABLE po_product_lines
  ADD CONSTRAINT po_product_lines_key UNIQUE(plmaster_id, supplier_product_code, start_date, usercompanyid);
  
-- and create the overviews
DROP VIEW IF EXISTS po_productlines_header_overview;

CREATE OR REPLACE VIEW po_productlines_header_overview AS 
 SELECT plh.*
 , (st.item_code::text || ' - '::text) || st.description::text AS stitem
 , uom.uom_name
 , pg.product_group || ' - ' || pg.description AS product_group
 , tax.description AS tax_rate
 , gla.account AS gl_account
 , glc.cost_centre AS gl_centre
   FROM po_product_lines_header plh
   LEFT JOIN st_items st ON plh.stitem_id = st.id
   LEFT JOIN st_uoms uom ON plh.stuom_id = uom.id
   LEFT JOIN st_productgroups pg ON plh.prod_group_id = pg.id
   JOIN taxrates tax ON plh.tax_rate_id = tax.id
   JOIN gl_accounts gla ON plh.glaccount_id = gla.id
   JOIN gl_centres glc ON plh.glcentre_id = glc.id;

ALTER TABLE po_productlines_header_overview OWNER TO "www-data";

DROP VIEW IF EXISTS po_productline_items;

CREATE OR REPLACE VIEW po_productline_items AS 
 SELECT DISTINCT plh.stitem_id AS id, plh.stitem_id, plh.stuom_id, plh.usercompanyid
 , st.comp_class, uom.uom_name
 , (st.item_code::text || ' - '::text) || st.description::text AS stitem, st.prod_group_id
   FROM po_product_lines_header plh
   JOIN st_items st ON plh.stitem_id = st.id
   JOIN st_uoms uom ON plh.stuom_id = uom.id
  ORDER BY plh.stitem_id, plh.stuom_id, plh.usercompanyid, uom.uom_name
  , (st.item_code::text || ' - '::text) || st.description::text, st.prod_group_id;

ALTER TABLE po_productline_items OWNER TO "www-data";

DROP VIEW IF EXISTS po_productlines_overview;

CREATE OR REPLACE VIEW po_productlines_overview AS 
 SELECT pl.*
 , plh.description as product, plh.stitem_id, plh.stuom_id, plh.tax_rate_id, plh.prod_group_id
 , plm.payee_name
 , c.name AS supplier
 , uom.uom_name
 , (st.item_code::text || ' - '::text) || st.description::text AS stitem, st.comp_class
 , gla.account AS glaccount, glc.cost_centre AS glcentre, cur.currency
 , pg.description AS stproductgroup
   FROM po_product_lines pl
   JOIN po_product_lines_header plh ON pl.productline_header_id = plh.id
   LEFT JOIN plmaster plm ON pl.plmaster_id = plm.id
   LEFT JOIN company c ON plm.company_id = c.id
   LEFT JOIN st_items st ON plh.stitem_id = st.id
   LEFT JOIN st_uoms uom ON plh.stuom_id = uom.id
   LEFT JOIN st_productgroups pg ON plh.prod_group_id = pg.id
   JOIN cumaster cur ON pl.currency_id = cur.id
   JOIN gl_accounts gla ON pl.glaccount_id = gla.id
   JOIN gl_centres glc ON pl.glcentre_id = glc.id;

ALTER TABLE po_productlines_overview OWNER TO "www-data";

-- Views to get orders/invoices for product line header
DROP VIEW IF EXISTS po_product_orders;

ALTER TABLE pi_lines ADD COLUMN productline_id integer;

ALTER TABLE pi_lines
  ADD CONSTRAINT pi_lines_productline_id_fkey FOREIGN KEY (productline_id)
      REFERENCES po_product_lines (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

CREATE OR REPLACE VIEW po_product_orders AS
SELECT ph.*, pl.id as orderline_id, pl.productline_id, pl.status as line_status, ppl.productline_header_id
  FROM po_headeroverview ph
  JOIN po_lines pl ON ph.id = pl.order_id
  JOIN po_product_lines ppl ON ppl.id = pl.productline_id;

ALTER TABLE po_product_orders OWNER TO "www-data";

DROP VIEW IF EXISTS po_product_invoices;

CREATE OR REPLACE VIEW po_product_invoices AS
SELECT ph.*, pl.id as invoiceline_id, pl.productline_id, ppl.productline_header_id
  FROM pi_headeroverview ph
  JOIN pi_lines pl ON ph.id = pl.invoice_id
  JOIN po_product_lines ppl ON ppl.id = pl.productline_id;

ALTER TABLE po_product_invoices OWNER TO "www-data";

DROP VIEW project_purchase_orders;

DROP VIEW pi_linesoverview;

DROP VIEW po_linesoverview;

CREATE OR REPLACE VIEW po_linesoverview AS 
 SELECT pl.*, ph.due_date, ph.order_date
 , ph.order_number, ph.plmaster_id, ph.receive_action, ph.type, ph.net_value AS order_value, cu.currency
 , (glc.cost_centre::text || ' - '::text) || glc.description::text AS glcentre
 , (gla.account::text || ' - '::text) || gla.description::text AS glaccount, tax.description AS taxrate
 , ph.status AS order_status, plm.payee_name, c.name AS supplier
 , (i.item_code::text || ' - '::text) || i.description::text AS stitem, u.uom_name
   FROM po_lines pl
   JOIN gl_centres glc ON glc.id = pl.glcentre_id
   JOIN gl_accounts gla ON gla.id = pl.glaccount_id
   JOIN taxrates tax ON tax.id = pl.tax_rate_id
   JOIN cumaster cu ON cu.id = pl.currency_id
   JOIN po_header ph ON ph.id = pl.order_id
   JOIN plmaster plm ON ph.plmaster_id = plm.id
   JOIN company c ON plm.company_id = c.id
   LEFT JOIN st_items i ON i.id = pl.stitem_id
   LEFT JOIN st_uoms u ON u.id = pl.stuom_id;

ALTER TABLE po_linesoverview OWNER TO "www-data";

CREATE OR REPLACE VIEW pi_linesoverview AS 
 SELECT pl.*
 , ph.invoice_date, ph.invoice_number, ph.transaction_type, ph.plmaster_id, poh.order_number
 , c.name AS supplier, i.item_code
 , (i.item_code::text || ' - '::text) || i.description::text AS stitem
   FROM pi_lines pl
   JOIN pi_header ph ON ph.id = pl.invoice_id
   JOIN plmaster plm ON ph.plmaster_id = plm.id
   JOIN company c ON plm.company_id = c.id
   LEFT JOIN po_header poh ON pl.purchase_order_id = poh.id
   LEFT JOIN st_items i ON i.id = pl.stitem_id;

ALTER TABLE pi_linesoverview OWNER TO "www-data";

CREATE OR REPLACE VIEW project_purchase_orders AS 
 SELECT pcc.id, pcc.project_id, pcc.task_id, pcc.item_id, pcc.item_type, pcc.source_id, pcc.source_type, pcc.stitem_id, pcc.description, pcc.quantity, pcc.unit_price, pcc.net_value, pcc.usercompanyid, pcc.created, pcc.createdby, pcc.alteredby, pcc.lastupdated, pol.order_id, pol.order_number, pol.line_number, pol.plmaster_id, pol.supplier, pol.description AS line_description, pol.net_value AS order_value, pol.order_date, pol.due_delivery_date
   FROM project_costs_charges pcc
   JOIN po_linesoverview pol ON pol.id = pcc.item_id AND pcc.item_type::text = 'PO'::text;

ALTER TABLE project_purchase_orders OWNER TO "www-data";

-- add the new module components
INSERT INTO module_components
  ("name", "type", location, module_id)
  SELECT 'poproductlineheaderscontroller', 'C', m.location||'/controllers/PoproductlineheadersController.php', id
    FROM modules m
   WHERE m.name = 'purchase_order';

INSERT INTO module_components
  ("name", "type", location, module_id)
  SELECT 'poproductlineheader', 'M', m.location||'/models/POProductlineHeader.php', id
    FROM modules m
   WHERE m.name = 'purchase_order';

INSERT INTO module_components
  ("name", "type", location, module_id)
  SELECT 'poproductlineheadercollection', 'M', m.location||'/models/POProductlineHeaderCollection.php', id
    FROM modules m
   WHERE m.name = 'purchase_order';

-- update permissions
DELETE FROM permissions
 WHERE permission IN ('viewbyitems', 'printsupplydemand', 'printsupplydemanddetail') 
   AND parent_id IN (SELECT id
                       FROM permissions
                      WHERE permission in ('soproductlines', 'poproductlines')
                        AND type = 'c');

UPDATE permissions
   SET position = position+1
 WHERE position > 1
   AND parent_id = (SELECT id
                      FROM permissions y
                     WHERE y.permission = 'purchase_order'
                       AND y.type = 'm'
                       AND (y.parent_id is null
                           OR y.parent_id = (select z.id
                                             from permissions z
                                            where parent_id is null
                                              and z.id = y.parent_id)));

INSERT INTO permissions
  (permission, type, description, title, display, position, parent_id)
SELECT 'poproductlineheaders', 'c', 'Purchase Order Products', 'Products', TRUE, 2, id
  FROM permissions y
 WHERE y.permission = 'purchase_order'
   AND y.type = 'm'
   AND (y.parent_id IS NULL
       OR y.parent_id = (SELECT z.id
                           FROM permissions z
                          WHERE parent_id IS NULL
                            AND z.id = y.parent_id));

INSERT INTO permissions
  (permission, type, description, title, display, position, parent_id)
SELECT '_new', 'a', 'Add Product', 'Add Product', TRUE, 1, id
  FROM permissions y
 WHERE y.permission = 'poproductlineheaders'
   AND y.type = 'c';

INSERT INTO permissions
  (permission, type, description, title, display, position, parent_id)
SELECT 'edit', 'a', 'Edit Product', 'Edit Product', FALSE, 2, id
  FROM permissions y
 WHERE y.permission = 'poproductlineheaders'
   AND y.type = 'c';

INSERT INTO permissions
  (permission, type, description, title, display, position, parent_id)
SELECT 'delete', 'a', 'Delete Product', 'Delete Product', FALSE, 3, id
  FROM permissions y
 WHERE y.permission = 'poproductlineheaders'
   AND y.type = 'c';

INSERT INTO report_definitions
(name, definition, usercompanyid)
SELECT 'selector_components',
'<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
	<xsl:template match="/">
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:layout-master-set>
				<fo:simple-page-master master-name="all-pages"
						page-height="21cm"
						page-width="29.7cm"
						margin="1cm" >
					<fo:region-body margin-top="1cm" margin-bottom="1.1cm"/>
					<fo:region-before extent="1cm"/>
					<fo:region-after extent="5mm"/>
	  			</fo:simple-page-master>
		  	</fo:layout-master-set>
			<!-- format is the style of page numbering, 1 for 1,2,3, i for roman numerals (sp?)-->
			<fo:page-sequence master-reference="all-pages" format="1">
				<!-- header with running glossary entries -->
				<fo:static-content flow-name="xsl-region-before">
					<fo:block><!--[REPORT_TITLE]--></fo:block>
				</fo:static-content>
				<fo:static-content flow-name="xsl-region-after">
					<fo:table table-layout="fixed" width="100%">
						<fo:table-column column-width="proportional-column-width(1)"/>
						<fo:table-column column-width="proportional-column-width(1)"/>
						<fo:table-body>
							<fo:table-row>
								<fo:table-cell>
									<fo:block>Page <!--[page_position]--></fo:block>
								</fo:table-cell>
								<fo:table-cell>
									<fo:block text-align="right"><!--[FOOTER_STRING]--></fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:static-content>
				<fo:flow flow-name="xsl-region-body" >
					<fo:table border-style="none" margin-bottom="5mm" font-size="8pt">
						<fo:table-body>
							<!--[ITEM_LIST]-->
						</fo:table-body>
					</fo:table>
					<fo:table table-layout="fixed" width="100%" font-size="8pt">
						<!--[REPORT_COLUMN_DEFINITIONS]-->
						<fo:table-header>
							<fo:table-row>
								<!-- loop through headings -->
								<xsl:attribute name="font-weight">bold</xsl:attribute>
								<xsl:attribute name="background-color"><!--[table_header_colour]--></xsl:attribute>
								<!--[REPORT_COLUMN_HEADINGS]-->
							</fo:table-row>
						</fo:table-header>
						<fo:table-body>
							<xsl:for-each select="data/<!--[COLLECTION_NAME]-->">
								<fo:table-row>
									<!-- this condition is to provide us with alternate row colours -->
									<xsl:if test="(position() mod 2 = 1)">
										<xsl:attribute name="background-color"><!--[table_row_alternate_colour]--></xsl:attribute>
									</xsl:if>
									<!-- check if we''re dealing with a total row -->
									<xsl:if test="@sub_total=''true''">
										<xsl:attribute name="font-weight">bold</xsl:attribute>
										<xsl:attribute name="background-color"><!--[table_header_colour]--></xsl:attribute>
									</xsl:if>
									<!--[REPORT_ROW_CELLS]-->
								</fo:table-row>
							</xsl:for-each>
							<!-- just in case we don''t have any rows -->
							<fo:table-row>
								<fo:table-cell>
									<fo:block></fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
					<!-- this is required to calculate the last page number -->
					<fo:block id="last-page"/>
				</fo:flow>
			</fo:page-sequence>
		</fo:root>
	</xsl:template>
</xsl:stylesheet>',
  id
  FROM system_companies;
