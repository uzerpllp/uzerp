--
-- $Revision: 1.1 $
--

-- View: wh_actions_overview

-- DROP VIEW wh_actions_overview;

CREATE OR REPLACE VIEW wh_actions_overview AS 
 SELECT wa.*
      , COALESCE(tr.rules_count, 0::bigint) AS defined_rules
   FROM wh_actions wa
   LEFT JOIN ( SELECT wh_transfer_rules.whaction_id, count(*) AS rules_count
                 FROM wh_transfer_rules
                GROUP BY wh_transfer_rules.whaction_id) tr ON tr.whaction_id = wa.id;

ALTER TABLE wh_actions_overview OWNER TO "www-data";