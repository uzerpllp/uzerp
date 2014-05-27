DROP VIEW st_balancesoverview;

CREATE OR REPLACE VIEW st_balancesoverview AS 
 SELECT bl.id, bl.balance, bl.whlocation_id, bl.whbin_id, bl.stitem_id, bl.usercompanyid, l.whstore_id
, (l.whstore::text || '/'::text) || l.description::text AS whlocation, b.bin_code ||' - '|| b.description AS whbin
, (si.item_code::text || ' - '::text) || si.description::text AS stitem
, round(bl.balance * si.std_cost, 2) AS valuation, round(bl.balance * si.std_mat, 2) AS matvaluation
, round(bl.balance * si.std_osc, 2) AS oscvaluation, round(bl.balance * si.std_lab, 2) AS labvaluation
, round(bl.balance * si.std_ohd, 2) AS ohdvaluation, round(bl.balance * si.latest_cost, 2) AS revvaluation
   FROM st_balances bl
   JOIN wh_locationsoverview l ON bl.whlocation_id = l.id
   JOIN st_items si ON bl.stitem_id = si.id
   LEFT JOIN wh_bins b ON bl.whbin_id = b.id;