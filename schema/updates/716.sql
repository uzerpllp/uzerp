--
-- $Revision: 1.3 $
--

--

ALTER TABLE ar_master ALTER COLUMN plmaster_id DROP NOT NULL;

ALTER TABLE ar_master ADD COLUMN disposal_value numeric(15,2);

ALTER TABLE ar_master ALTER COLUMN disposal_value SET DEFAULT 0;

ALTER TABLE ar_master ADD COLUMN leased BOOLEAN DEFAULT FALSE;

CREATE INDEX ar_master_code
  ON ar_master
  USING btree
  (code);

CREATE INDEX ar_master_code_comp
  ON ar_master
  USING btree
  (code varchar_pattern_ops);

CREATE INDEX ar_master_description_comp
  ON ar_master
  USING btree
  (description varchar_pattern_ops);

DROP VIEW assetsoverview;

CREATE OR REPLACE VIEW assetsoverview AS 
 SELECT am.*
 , g.description AS argroup, l.description AS arlocation
 , a.description AS aranalysis, s.payee_name AS supplier
   FROM ar_master am
   JOIN ar_groups g ON am.argroup_id = g.id
   JOIN ar_locations l ON am.arlocation_id = l.id
   LEFT JOIN plmaster s ON am.plmaster_id = s.id
   LEFT JOIN ar_analysis a ON am.aranalysis_id = a.id;

ALTER TABLE assetsoverview OWNER TO "www-data";

DROP VIEW ar_transactions_overview;

CREATE OR REPLACE VIEW ar_transactions_overview AS 
 SELECT at.*
 , am.code AS armaster, fg.description AS from_group, fl.description AS from_location, tg.description AS to_group, tl.description AS to_location
   FROM ar_transactions at
   JOIN ar_master am ON am.id = at.armaster_id
   LEFT JOIN ar_groups fg ON at.from_group_id = fg.id
   LEFT JOIN ar_locations fl ON at.from_location_id = fl.id
   LEFT JOIN ar_groups tg ON at.to_group_id = tg.id
   LEFT JOIN ar_locations tl ON at.to_location_id = tl.id;

ALTER TABLE ar_transactions_overview OWNER TO "www-data";

INSERT INTO module_components
 (name, "type", location, module_id)
 SELECT 'assetssearch', 'M', location||'/models/assetsSearch.php', id
   FROM modules m
  WHERE name = 'asset_register';

