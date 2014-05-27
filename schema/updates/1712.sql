--
-- $Revision: 1.4 $
--


-- MODULES

INSERT INTO module_components
 ("name", "type", location, module_id)
 SELECT 'permissionparameterscollection', 'M', m.location||'/models/PermissionParametersCollection.php', id
  FROM modules m
 WHERE m.name = 'system_admin';
 
ALTER TABLE permissions ADD COLUMN module_id integer;
ALTER TABLE permissions ADD COLUMN component_id integer;
ALTER TABLE permissions ADD COLUMN display_in_sidebar boolean;

ALTER TABLE permissions
  ADD CONSTRAINT permissions_component_id_fkey FOREIGN KEY (component_id)
      REFERENCES module_components (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE;
      
ALTER TABLE permissions
  ADD CONSTRAINT permissions_module_id_fkey FOREIGN KEY (module_id)
      REFERENCES modules (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE;