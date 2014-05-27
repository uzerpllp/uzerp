ALTER TABLE haspermission
  ADD CONSTRAINT haspermission_ukey1 UNIQUE(roleid, permissionsid);