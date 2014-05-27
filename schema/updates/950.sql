ALTER TABLE permissions DROP CONSTRAINT permissions_permission_key;

ALTER TABLE permissions
  ADD CONSTRAINT permissions_permission_key UNIQUE(parent_id, permission, type, title);

ALTER TABLE permissions ADD COLUMN has_parameters boolean;
ALTER TABLE permissions ALTER COLUMN has_parameters SET DEFAULT false;

UPDATE permissions
   SET has_parameters=false;

ALTER TABLE permissions ALTER COLUMN has_parameters SET NOT NULL;

CREATE TABLE permission_parameters
(
  id bigserial NOT NULL,
  permissionsid bigint NOT NULL,
  "name" character varying,
  "value" character varying,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  alteredby character varying,
  CONSTRAINT permission_parameters_pkey PRIMARY KEY (id),
  CONSTRAINT permission_parameters_permissionsid_fkey FOREIGN KEY (permissionsid)
      REFERENCES permissions (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);