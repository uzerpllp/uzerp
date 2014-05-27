CREATE INDEX permissions_parent_id
  ON permissions
  USING btree
  (parent_id);