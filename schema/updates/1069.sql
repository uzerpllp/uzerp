CREATE TABLE so_product_selector
(
  id bigserial NOT NULL,
  "name" character varying NOT NULL,
  parent_id bigint,
  description character varying NOT NULL,
  usercompanyid bigint NOT NULL,
  CONSTRAINT so_product_selector_pk PRIMARY KEY (id),
  CONSTRAINT so_product_selector_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT so_product_selector_uk1 UNIQUE (name, description, parent_id)
);

CREATE INDEX product_selector_parent_id
  ON so_product_selector
  USING btree
  (parent_id);

CREATE TABLE so_product_line_link
(
  id bigserial NOT NULL,
  item_id bigint NOT NULL,
  target_id bigint NOT NULL,
  CONSTRAINT so_product_line_link_pkey PRIMARY KEY (id),
  CONSTRAINT so_product_line_link_item_id_fkey FOREIGN KEY (item_id)
      REFERENCES so_product_selector (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT so_product_line_link_target_id_fkey FOREIGN KEY (target_id)
      REFERENCES so_product_lines (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE INDEX so_product_line_link_item_id
  ON so_product_line_link
  USING btree
  (item_id);

CREATE INDEX so_product_line_link_target_id
  ON so_product_line_link
  USING btree
  (target_id);

insert into permissions
(permission, type, title, display, parent_id)
select 'select_products', 'a', 'Select Products', true, id
from permissions
where type='c'
and permission='sorders';

insert into permissions
(permission, type, title, display, parent_id)
select 'soproductselectors', 'c', 'Product Selector Link', true, id
from permissions
where type='m'
and permission='sales_order';

insert into permissions
(permission, type, title, display, parent_id)
select '_new', 'a', 'Add Selector', true, id
from permissions
where type='c'
and permission='soproductselectors';

insert into permissions
(permission, type, title, display, parent_id)
select 'delete', 'a', 'Delete Selector', false, id
from permissions
where type='c'
and permission='soproductselectors';

insert into permissions
(permission, type, title, display, parent_id)
select 'edit', 'a', 'Edit Selector', false, id
from permissions
where type='c'
and permission='soproductselectors';

insert into permissions
(permission, type, title, display, parent_id)
select 'select_items', 'a', 'Link Product Items', true, id
from permissions
where type='c'
and permission='soproductselectors';