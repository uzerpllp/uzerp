ALTER TABLE module_defaults ADD COLUMN usercompanyid bigint;

ALTER TABLE module_defaults
  ADD CONSTRAINT module_defaults_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE;

update module_defaults
   set usercompanyid=1;

ALTER TABLE module_defaults ALTER COLUMN usercompanyid SET NOT NULL;
