CREATE TABLE datasets
(
  id bigserial NOT NULL,
  name varchar,
  usercompanyid int8 NOT NULL,
  CONSTRAINT datasets_pkey PRIMARY KEY (id),
  CONSTRAINT datasets_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE datasetlines
(
  id bigserial NOT NULL,
  name varchar,
  "type" varchar,
  description varchar,
  length int4,
  dataset_id int8 NOT NULL,
  usercompanyid int8 NOT NULL,
  CONSTRAINT datasetlines_pkey PRIMARY KEY (id),
  CONSTRAINT datasetlines_dataset_id_fkey FOREIGN KEY (dataset_id)
      REFERENCES datasets (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT datasetlines_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);