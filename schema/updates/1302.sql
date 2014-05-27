--
-- $Revision: 1.1 $
--

-- View: st_typecodes_overview

-- DROP VIEW st_typecodes_overview;

CREATE OR REPLACE VIEW st_typecodes_overview AS 
 SELECT tc.*
      , a1.action_name || ' - ' || a1.description as backflush_action
      , a2.action_name || ' - ' || a2.description as complete_action
      , a3.action_name || ' - ' || a3.description as issue_action
   FROM st_typecodes tc
   LEFT JOIN wh_actions a1 ON a1.id = tc.backflush_action_id
   LEFT JOIN wh_actions a2 ON a2.id = tc.complete_action_id
   LEFT JOIN wh_actions a3 ON a3.id = tc.issue_action_id;

ALTER TABLE st_typecodes_overview OWNER TO "www-data";
