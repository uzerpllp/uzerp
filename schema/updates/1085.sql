ALTER TABLE so_lines ADD COLUMN line_value numeric;
ALTER TABLE so_lines ADD COLUMN line_tradedisc_percentage numeric;
ALTER TABLE so_lines ADD COLUMN line_qtydisc_percentage numeric;

CREATE TABLE sl_discounts
(
  id bigserial NOT NULL,
  slmaster_id bigint NOT NULL,
  prod_group_id bigint NOT NULL,
  discount_percentage numeric NOT NULL,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT sl_discounts_pkey PRIMARY KEY (id),
  CONSTRAINT sl_discounts_slmaster_id_fkey FOREIGN KEY (slmaster_id)
      REFERENCES slmaster (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT sl_discounts_prod_group_id_fkey FOREIGN KEY (prod_group_id)
      REFERENCES st_productgroups (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT sl_discounts_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE OR REPLACE VIEW sl_discounts_overview AS 
 SELECT sd.*
 , slm.name AS customer
 , (pg.product_group::text || ' - '::text) || pg.description::text AS product_group
   FROM sl_discounts sd
   JOIN slmaster slm ON sd.slmaster_id = slm.id
   JOIN st_productgroups pg ON pg.id = sd.prod_group_id;

insert into permissions
(permission, type, title, display, parent_id)
select 'sldiscounts', 'c', 'Customer Discounts', true, parent_id
from permissions
where type='c'
and permission='slcustomers';

insert into permissions
(permission, type, title, display, parent_id)
select '_new', 'a', 'Add Customer Discount', true, id
from permissions
where type='c'
and permission='sldiscounts';

insert into permissions
(permission, type, title, display, parent_id)
select 'delete', 'a', 'Delete Customer Discount', false, id
from permissions
where type='c'
and permission='sldiscounts';

insert into permissions
(permission, type, title, display, parent_id)
select 'edit', 'a', 'Edit Customer Discount', false, id
from permissions
where type='c'
and permission='sldiscounts';