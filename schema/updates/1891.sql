--
-- $Revision: 1.3 $
--

-- Table: datasets

-- DROP TABLE datasets;

CREATE TABLE datasets
(
  id bigserial NOT NULL,
  name character varying NOT NULL,
  title character varying NOT NULL,
  description character varying,
  "owner" character varying NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  alteredby character varying,
  usercompanyid bigint NOT NULL,
  CONSTRAINT datasets_pkey PRIMARY KEY (id),
  CONSTRAINT datasets_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

ALTER TABLE datasets OWNER TO "www-data";

-- Table: dataset_fields

-- DROP TABLE dataset_fields;

CREATE TABLE dataset_fields
(
  id bigserial NOT NULL,
  name character varying NOT NULL,
  "type" character varying NOT NULL,
  title character varying NOT NULL,
  description character varying,
  length int,
  default_value character varying,
  module_component_id bigint,
  mandatory boolean,
  searchable boolean,
  display_in_list boolean,
  position int NOT NULL,
  dataset_id bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  alteredby character varying,
  usercompanyid bigint NOT NULL,
  CONSTRAINT dataset_fields_pkey PRIMARY KEY (id),
  CONSTRAINT dataset_fields_links_to_fkey FOREIGN KEY (module_component_id)
      REFERENCES module_components (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT dataset_fields_dataset_id_fkey FOREIGN KEY (dataset_id)
      REFERENCES datasets (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT dataset_fields_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

ALTER TABLE dataset_fields OWNER TO "www-data";

-- Schema: "user_tables"

-- DROP SCHEMA user_tables;

CREATE SCHEMA user_tables
  AUTHORIZATION "www-data";

--
-- Modules/Components
--

INSERT INTO modules
 (name, description, location, registered, enabled)
 VALUES
 ('developer', 'Developer module for user space tables', 'modules/public_pages/developer', TRUE, TRUE);

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'indexcontroller', 'C', location||'/controllers/IndexController.php', id, 'User Space Tables'
   FROM modules m
  WHERE name = 'developer';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'datasetscontroller', 'C', location||'/controllers/DatasetsController.php', id, 'User Space Tables'
   FROM modules m
  WHERE name = 'developer';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'dataset', 'M', location||'/models/Dataset.php', id, 'Dataset Detail'
   FROM modules m
  WHERE name = 'developer';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'datasetcollection', 'M', location||'/models/DatasetCollection.php', id, 'Dataset List'
   FROM modules m
  WHERE name = 'developer';

  INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'datasetfield', 'M', location||'/models/DatasetField.php', id, 'Dataset Field Detail'
   FROM modules m
  WHERE name = 'developer';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'datasetfieldcollection', 'M', location||'/models/DatasetFieldCollection.php', id, 'Dataset Field List'
   FROM modules m
  WHERE name = 'developer';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'datasetssearch', 'M', location||'/models/datasetsSearch.php', id, 'Dataset Search'
   FROM modules m
  WHERE name = 'developer';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'developer', 'J', location||'/resources/js/developer.js', id, 'Dataset Javascript Library'
   FROM modules m
  WHERE name = 'developer';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'reportssearch', 'M', location||'/models/reportsSearch.php', id, 'Reports Search'
   FROM modules m
  WHERE name = 'reporting';

--
-- Permissions
--

insert into permissions
(permission, type, title, display, position)
select 'developer', 'm', 'User Space Tables', true, next.position
  from (select max(c.position)+1 as position
          from permissions c
         where c.parent_id is null) as next   
 where not exists (select 1
                     from permissions
                    where permission = 'developer');

insert into permissions
(permission, type, title, display, parent_id, position)
select 'datasets', 'c', 'User Space Tables', true, id, 1
  from permissions
 where type='m'
   and permission='developer'
   and not exists (select 1
                     from permissions
                    where type='c'
                      and permission = 'datasets');
