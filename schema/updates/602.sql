CREATE TABLE injector_classes
(
  id serial NOT NULL,
  name varchar NOT NULL,
  category varchar NOT NULL,
  class_name varchar NOT NULL,
  description varchar,
  usercompanyid int8 NOT NULL,
  CONSTRAINT injector_classes_pkey PRIMARY KEY (id),
  CONSTRAINT injector_classes_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT injector_classes_name_key UNIQUE (name, class_name)
);

ALTER TABLE mf_workorders ADD COLUMN documentation varchar;