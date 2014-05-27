CREATE TABLE system_types
(
  id serial NOT NULL,
  system_type varchar NOT NULL,
  type_name varchar NOT NULL,
  description varchar NOT NULL,
  created timestamp NOT NULL default now(),
  lastupdated timestamp NOT NULL default now(),
  alteredby varchar,
  usercompanyid int8 NOT NULL,
  CONSTRAINT system_types_pkey PRIMARY KEY (id),
  CONSTRAINT system_types_unique_key UNIQUE (system_type, type_name)
);