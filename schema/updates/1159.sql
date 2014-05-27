ALTER TABLE modules ADD COLUMN defaults_enabled boolean;

CREATE TABLE module_defaults
(
  id bigserial NOT NULL,
  field_name character varying NOT NULL,
  default_value character varying,
  enabled boolean DEFAULT false,
  module_components_id bigint,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  lastupdated timestamp without time zone,
  alteredby character varying,
  CONSTRAINT module_defaults_pkey PRIMARY KEY (id),
  CONSTRAINT module_defaults_alteredby_fkey FOREIGN KEY (alteredby)
      REFERENCES users (username) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT module_defaults_createdby_fkey FOREIGN KEY (createdby)
      REFERENCES users (username) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT module_defaults_module_components_id_fkey FOREIGN KEY (module_components_id)
      REFERENCES module_components (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT module_defaults_ukey UNIQUE (module_components_id, field_name)
);