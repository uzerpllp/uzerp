CREATE TABLE so_price_types
(
  id bigserial NOT NULL,
  name character varying NOT NULL,
  description character varying,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT so_price_types_pkey PRIMARY KEY (id),
  CONSTRAINT so_price_types_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

ALTER TABLE slmaster ADD COLUMN so_price_type_id bigint;

ALTER TABLE slmaster
  ADD CONSTRAINT slmaster_so_price_type_id_fkey FOREIGN KEY (so_price_type_id)
      REFERENCES so_price_types (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE so_product_lines ADD COLUMN so_price_type_id bigint;

ALTER TABLE so_product_lines
  ADD CONSTRAINT so_product_lines_so_price_type_id_fkey FOREIGN KEY (so_price_type_id)
      REFERENCES so_price_types (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE;

DROP VIEW so_productlines_overview;

CREATE OR REPLACE VIEW so_productlines_overview AS 
 SELECT so.*
 , slm.name AS customer
 , uom.uom_name
 , (st.item_code::text || ' - '::text) || st.description::text AS stitem
 , gla.account AS glaccount
 , glc.cost_centre AS glcentre
 , cu.currency
 , tax.description AS taxrate
 , pt.name as so_price_type
   FROM so_product_lines so
   LEFT JOIN slmaster slm ON so.slmaster_id = slm.id
   LEFT JOIN st_items st ON so.stitem_id = st.id
   LEFT JOIN st_uoms uom ON so.stuom_id = uom.id
   JOIN cumaster cu ON so.currency_id = cu.id
   JOIN taxrates tax ON so.tax_rate_id = tax.id
   JOIN gl_accounts gla ON so.glaccount_id = gla.id
   JOIN gl_centres glc ON so.glcentre_id = glc.id
   LEFT JOIN so_price_types pt ON so.so_price_type_id = pt.id;

insert into permissions
(permission, type, description, title, display, parent_id)
select 'sopricetypes', 'c', 'Sales Order', 'Price Types', true, parent_id
from permissions
where type='c'
and permission='soproductlines';

insert into permissions
(permission, type, title, display, parent_id)
select '_new', 'a', 'Add Price Type', true, id
from permissions
where type='c'
and permission='sopricetypes';

insert into permissions
(permission, type, title, display, parent_id)
select 'view', 'a', 'View Price Type', false, id
from permissions
where type='c'
and permission='sopricetypes';

insert into permissions
(permission, type, title, display, parent_id)
select 'delete', 'a', 'Delete Price Type', false, id
from permissions
where type='c'
and permission='sopricetypes';

insert into permissions
(permission, type, title, display, parent_id)
select 'edit', 'a', 'Edit Price Type', false, id
from permissions
where type='c'
and permission='sopricetypes';