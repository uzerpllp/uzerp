DROP TABLE modules;

CREATE TABLE modules
(
  id bigserial NOT NULL,
  name varchar NOT NULL,
  description varchar,
  registered bool DEFAULT false,
  enabled bool DEFAULT false,
  location varchar,
  created timestamp without time zone NOT NULL DEFAULT now(),
  createdby  character varying,
  lastupdated timestamp without time zone,
  alteredby character varying,
  CONSTRAINT modules_pkey PRIMARY KEY (id),
  CONSTRAINT modules_name_key UNIQUE (name)
);

DROP TABLE module_components;

CREATE TABLE module_components
(
  id bigserial NOT NULL,
  name character varying NOT NULL,
  type character varying NOT NULL,
  controller character varying,
  location character varying,
  module_id bigint,
  created timestamp without time zone NOT NULL DEFAULT now(),
  createdby  character varying,
  lastupdated timestamp without time zone,
  alteredby character varying,
  CONSTRAINT module_components_pkey PRIMARY KEY (id),
  CONSTRAINT module_components_createdby_fkey FOREIGN KEY (createdby)
      REFERENCES users (username) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT module_components_alteredby_fkey FOREIGN KEY (alteredby)
      REFERENCES users (username) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT module_components_module_id_fkey FOREIGN KEY (module_id)
      REFERENCES modules (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE INDEX module_components_module_id
  ON module_components
  USING btree
  (module_id);

CREATE INDEX module_components_name
  ON module_components
  USING btree
  (name);

ALTER TABLE permissions ADD COLUMN "location" character varying;
ALTER TABLE permissions ADD COLUMN created timestamp;
ALTER TABLE permissions ALTER COLUMN created SET DEFAULT now();
ALTER TABLE permissions ADD COLUMN createdby character varying;
ALTER TABLE permissions ADD COLUMN lastupdated timestamp;
ALTER TABLE permissions ALTER COLUMN lastupdated SET DEFAULT now();
ALTER TABLE permissions ADD COLUMN alteredby character varying;

ALTER TABLE permissions
  ADD CONSTRAINT permissions_parent_id_fkey FOREIGN KEY (parent_id)
      REFERENCES permissions (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE;

insert into permissions
(permission, type, title, display, parent_id)
select 'moduleobjects', 'c', 'Modules', true, id
from permissions
where type='m'
and permission='system_admin';

update permissions
   set position=(select count(*)
                   from permissions p
                  where parent_id = (select id
                                       from permissions x
                                      where type='m'
                                        and permission='system_admin'))
  where type='c'
    and permission='moduleobjects';

insert into permissions
(permission, type, title, display, parent_id)
select '_new', 'a', 'Add Module', true, id
from permissions
where type='c'
and permission='moduleobjects';

insert into permissions
(permission, type, title, display, parent_id)
select 'delete', 'a', 'Delete Module', false, id
from permissions
where type='c'
and permission='moduleobjects';

insert into permissions
(permission, type, title, display, parent_id)
select 'edit', 'a', 'Edit Module', false, id
from permissions
where type='c'
and permission='moduleobjects';