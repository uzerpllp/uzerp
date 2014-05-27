--
-- $Revision: 1.3 $
--

-- View: wh_locationsoverview

-- DROP VIEW wh_locationsoverview;

CREATE OR REPLACE VIEW wh_locationsoverview AS 
 SELECT l.*
 , s.description AS whstore
 , a.account || ' - ' || a.description as glaccount
 , c.cost_centre || ' - ' || c.description as glcentre
   FROM wh_locations l
   JOIN wh_stores s ON l.whstore_id = s.id
   JOIN gl_accounts a ON l.glaccount_id = a.id
   JOIN gl_centres c ON l.glcentre_id = c.id;

ALTER TABLE wh_locationsoverview OWNER TO "www-data";

-- View: st_balancesoverview

DROP VIEW st_balancesoverview;

CREATE OR REPLACE VIEW st_balancesoverview AS 
 SELECT bl.*
 , l.whstore_id, (l.whstore::text || '/'::text) || l.description::text AS whlocation
 , l.supply_demand, l.pickable
 , (b.bin_code::text || ' - '::text) || b.description::text AS whbin
 , (si.item_code::text || ' - '::text) || si.description::text AS stitem
 , u.uom_name
 , round(bl.balance * si.std_cost, 2) AS valuation
 , round(bl.balance * si.std_mat, 2) AS matvaluation
 , round(bl.balance * si.std_osc, 2) AS oscvaluation
 , round(bl.balance * si.std_lab, 2) AS labvaluation
 , round(bl.balance * si.std_ohd, 2) AS ohdvaluation
 , round(bl.balance * si.latest_cost, 2) AS revvaluation
   FROM st_balances bl
   JOIN wh_locationsoverview l ON bl.whlocation_id = l.id
   JOIN st_items si ON bl.stitem_id = si.id
   JOIN st_uoms u ON u.id = si.uom_id
   LEFT JOIN wh_bins b ON bl.whbin_id = b.id;

ALTER TABLE st_balancesoverview OWNER TO "www-data";

-- View: wh_binsoverview

DROP VIEW wh_binsoverview;

CREATE OR REPLACE VIEW wh_binsoverview AS 
 SELECT b.*
 , l.description AS whlocation
   FROM wh_bins b
   JOIN wh_locations l ON b.whlocation_id = l.id;

ALTER TABLE wh_binsoverview OWNER TO "www-data";
