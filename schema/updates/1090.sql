CREATE TABLE reports
(
  id bigserial NOT NULL,
  tablename character varying NOT NULL,
  description character varying,
  fields character varying NOT NULL,
  measure_fields character varying NOT NULL,
  aggregate_fields character varying NOT NULL,
  aggregate_methods character varying,
  search_fields character varying,
  search_types character varying,
  search_defaults character varying,
  display_order character varying,
  CONSTRAINT reports_pkey PRIMARY KEY (id)
);

insert into permissions
(permission, type, title, display)
values
('reporting', 'm', 'Reporting', true);

insert into permissions
(permission, type, title, display, parent_id)
select 'reports', 'c', 'Reports', true, id
from permissions
where type='m'
and permission='reporting';

insert into permissions
(permission, type, title, display, parent_id)
select '_new', 'a', 'Add Report', true, id
from permissions
where type='c'
and permission='reports';

insert into permissions
(permission, type, title, display, parent_id)
select 'delete', 'a', 'Delete Report', false, id
from permissions
where type='c'
and permission='reports';

insert into permissions
(permission, type, title, display, parent_id)
select 'edit', 'a', 'Edit Report', false, id
from permissions
where type='c'
and permission='reports';

insert into permissions
(permission, type, title, display, parent_id)
select 'run', 'a', 'Run Report', false, id
from permissions
where type='c'
and permission='reports';