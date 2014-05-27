--
-- $Revision: 1.1 $
--

CREATE INDEX company_name_comp
  ON company
  USING btree
  (name varchar_pattern_ops);

CREATE INDEX st_productgroups_description_comp
  ON st_productgroups
  USING btree
  (description varchar_pattern_ops);

ALTER TABLE si_lines ALTER COLUMN tax_rate_id SET NOT NULL;