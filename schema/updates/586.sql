--
-- $Revision: 1.1 $
--

-- Column: manage_uzlets

-- ALTER TABLE roles DROP COLUMN manage_uzlets;

ALTER TABLE roles ADD COLUMN manage_uzlets boolean DEFAULT true;

UPDATE roles
   SET manage_uzlets = true;

ALTER TABLE roles ALTER COLUMN manage_uzlets SET NOT NULL;

ALTER TABLE roles ADD COLUMN created timestamp without time zone;
ALTER TABLE roles ALTER COLUMN created SET DEFAULT now();
ALTER TABLE roles ADD COLUMN createdby character varying;
ALTER TABLE roles ADD COLUMN alteredby character varying;
ALTER TABLE roles ADD COLUMN lastupdated timestamp without time zone;
ALTER TABLE roles ALTER COLUMN lastupdated SET DEFAULT now();

