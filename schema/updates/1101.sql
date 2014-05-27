CREATE TABLE debug_options
(
  id serial NOT NULL,
  username character varying,
  company_id integer,
  options character varying NOT NULL,
  created timestamp without time zone NOT NULL DEFAULT now(),
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT debug_options_pkey PRIMARY KEY (id)
);