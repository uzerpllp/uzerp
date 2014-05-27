ALTER TABLE wh_locations ADD COLUMN pickable boolean;

ALTER TABLE wh_locations ALTER COLUMN pickable SET DEFAULT false;

UPDATE wh_locations
   SET pickable=false;

DROP VIEW reports.st_balancesoverview_detail;

DROP VIEW reports.st_stockreceivedoverview;

DROP VIEW st_movementsoverview;

DROP VIEW st_transactionsoverview;

DROP VIEW wh_transfer_rules_overview;

DROP VIEW st_balancesoverview;

DROP VIEW wh_transfers_overview;

DROP VIEW wh_locationsoverview;

CREATE OR REPLACE VIEW wh_locationsoverview AS 
 SELECT l.*
 , s.description AS whstore
   FROM wh_locations l
   JOIN wh_stores s ON l.whstore_id = s.id;
   
CREATE OR REPLACE VIEW st_balancesoverview AS 
 SELECT bl.id, bl.balance, bl.whlocation_id, bl.whbin_id, bl.stitem_id, bl.usercompanyid
 , l.whstore_id, (l.whstore::text || '/'::text) || l.description::text AS whlocation
 , l.supply_demand , l.pickable, (b.bin_code::text || ' - '::text) || b.description::text AS whbin
 , (si.item_code::text || ' - '::text) || si.description::text AS stitem
 , round(bl.balance * si.std_cost, 2) AS valuation
 , round(bl.balance * si.std_mat, 2) AS matvaluation
 , round(bl.balance * si.std_osc, 2) AS oscvaluation
 , round(bl.balance * si.std_lab, 2) AS labvaluation
 , round(bl.balance * si.std_ohd, 2) AS ohdvaluation
 , round(bl.balance * si.latest_cost, 2) AS revvaluation
   FROM st_balances bl
   JOIN wh_locationsoverview l ON bl.whlocation_id = l.id
   JOIN st_items si ON bl.stitem_id = si.id
   LEFT JOIN wh_bins b ON bl.whbin_id = b.id;

CREATE OR REPLACE VIEW wh_transfers_overview AS 
 SELECT t.id, t.transfer_number, t.from_address_id, t.to_address_id, t.due_transfer_date
 , t.description, t.transfer_action, t.from_whlocation_id, t.to_whlocation_id, t.status
 , t.usercompanyid, t.actual_transfer_date
 , (((l1.whstore::text || '/'::text) || l1.location::text) || '-'::text) || l1.description::text AS from_location
 , (((l2.whstore::text || '/'::text) || l2.location::text) || '-'::text) || l2.description::text AS to_location
 , l1.bin_controlled AS from_bin_controlled, l2.bin_controlled AS to_bin_controlled, a.action_name
   FROM wh_transfers t
   JOIN wh_locationsoverview l1 ON l1.id = t.from_whlocation_id
   JOIN wh_locationsoverview l2 ON l2.id = t.to_whlocation_id
   JOIN wh_actions a ON a.id = t.transfer_action;

CREATE OR REPLACE VIEW wh_transfer_rules_overview AS 
 SELECT t.id, t.usercompanyid, t.from_whlocation_id, t.to_whlocation_id, t.whaction_id, t.created
 , t.createdby, t.alteredby, t.lastupdated
 , (((l1.whstore::text || '/'::text) || l1.location::text) || '-'::text) || l1.description::text AS from_location
 , (((l2.whstore::text || '/'::text) || l2.location::text) || '-'::text) || l2.description::text AS to_location
 , l1.bin_controlled AS from_bin_controlled, l2.bin_controlled AS to_bin_controlled, a.action_name, a.type
   FROM wh_transfer_rules t
   JOIN wh_locationsoverview l1 ON l1.id = t.from_whlocation_id
   JOIN wh_locationsoverview l2 ON l2.id = t.to_whlocation_id
   JOIN wh_actions a ON a.id = t.whaction_id;

CREATE OR REPLACE VIEW st_transactionsoverview AS 
 SELECT t.id, t.balance, t.created, t.latest_cost, t.std_cost, t.stitem_id, t.whlocation_id
 , t.whbin_id, t.transfer_id, t.whaction_id, t.process_name, t.process_id, t.qty, t.usercompanyid
 , t.std_mat, t.std_lab, t.std_osc, t.std_ohd, t.latest_mat, t.latest_lab, t.latest_osc, t.latest_ohd
 , t.remarks, t.error_qty, t.status
 , (l.whstore::text || '/'::text) || l.description::text AS whlocation, b.description AS whbin
 , (si.item_code::text || ' - '::text) || si.description::text AS stitem
 , (fl.whstore::text || '/'::text) || fl.description::text AS flocation, fb.description AS fbin
   FROM st_transactions t
   JOIN st_transactions f ON t.transfer_id = f.transfer_id AND t.id <> f.id
   JOIN wh_locationsoverview l ON t.whlocation_id = l.id
   LEFT JOIN wh_bins b ON t.whbin_id = b.id
   JOIN st_items si ON t.stitem_id = si.id
   JOIN wh_locationsoverview fl ON f.whlocation_id = fl.id
   LEFT JOIN wh_bins fb ON f.whbin_id = fb.id;

CREATE OR REPLACE VIEW st_movementsoverview AS 
 SELECT t.id, t.balance, t.created, t.latest_cost, t.std_cost, t.whlocation_id, t.stitem_id
 , t.usercompanyid, t.transfer_id, t.whbin_id, t.whaction_id, t.process_name, t.process_id, t.qty
 , t.std_mat, t.std_lab, t.std_osc, t.std_ohd, t.latest_mat, t.latest_lab, t.latest_osc, t.latest_ohd
 , t.remarks, t.error_qty, t.status, t.glaccount_id, t.glcentre_id, l.whstore
 , l.description::text AS whlocation, b.description AS whbin
 , (si.item_code::text || ' - '::text) || si.description::text AS stitem, p.product_group, u.uom_name
 , t.latest_cost::double precision * (t.qty + t.error_qty)::double precision AS total_cost
 , t.latest_mat::double precision * (t.qty + t.error_qty)::double precision AS total_mat, a.account
 , c.cost_centre
   FROM st_transactions t
   JOIN wh_locationsoverview l ON t.whlocation_id = l.id
   LEFT JOIN wh_bins b ON t.whbin_id = b.id
   JOIN st_items si ON t.stitem_id = si.id
   JOIN st_productgroups p ON si.prod_group_id = p.id
   JOIN st_uoms u ON si.uom_id = u.id
   LEFT JOIN gl_accounts a ON t.glaccount_id = a.id
   LEFT JOIN gl_centres c ON t.glcentre_id = c.id;

CREATE OR REPLACE VIEW reports.st_stockreceivedoverview AS 
 SELECT t.id, t.balance, t.created, t.latest_cost, t.std_cost, t.whlocation_id, t.stitem_id, t.usercompanyid, t.transfer_id, t.whbin_id, t.whaction_id, t.process_name, t.process_id, t.qty, t.std_mat, t.std_lab, t.std_osc, t.std_ohd, t.latest_mat, t.latest_lab, t.latest_osc, t.latest_ohd, t.remarks, t.error_qty, t.status, t.glaccount_id, t.glcentre_id, l.whstore, l.description::text AS whlocation, b.description AS whbin, (si.item_code::text || ' - '::text) || si.description::text AS stitem, p.product_group, u.uom_name, t.latest_cost::double precision * (t.qty + t.error_qty)::double precision AS total_cost, t.latest_mat::double precision * (t.qty + t.error_qty)::double precision AS total_mat, a.account, c.cost_centre, date_part('year'::text, t.created) AS year, date_part('month'::text, t.created) AS month
   FROM st_transactions t
   JOIN wh_locationsoverview l ON t.whlocation_id = l.id
   LEFT JOIN wh_bins b ON t.whbin_id = b.id
   JOIN st_items si ON t.stitem_id = si.id
   JOIN st_productgroups p ON si.prod_group_id = p.id
   JOIN st_uoms u ON si.uom_id = u.id
   LEFT JOIN gl_accounts a ON t.glaccount_id = a.id
   LEFT JOIN gl_centres c ON t.glcentre_id = c.id
  WHERE t.process_name::text = 'GR'::text;

GRANT SELECT ON TABLE reports.st_stockreceivedoverview TO "ooo-data";

CREATE OR REPLACE VIEW reports.st_balancesoverview_detail AS 
 SELECT (l.whstore::text || '/'::text) || l.description::text AS whlocation
 , (b.bin_code::text || ' - '::text) || b.description::text AS whbin
 , (si.item_code::text || ' - '::text) || si.description::text AS stitem
 , si.alpha_code, p.product_group, t.type_code, bl.balance, u.uom_name
 , round(bl.balance * si.std_cost, 2) AS valuation, round(bl.balance * si.std_mat, 2) AS matvaluation
 , round(bl.balance * si.std_osc, 2) AS oscvaluation, round(bl.balance * si.std_lab, 2) AS labvaluation
 , round(bl.balance * si.std_ohd, 2) AS ohdvaluation, round(bl.balance * si.latest_cost, 2) AS revvaluation
 , date_part('year'::text, now()) AS year, date_part('month'::text, now()) AS month
   FROM st_balances bl
   JOIN wh_locationsoverview l ON bl.whlocation_id = l.id
   JOIN st_items si ON bl.stitem_id = si.id
   LEFT JOIN wh_bins b ON bl.whbin_id = b.id
   JOIN st_productgroups p ON si.prod_group_id = p.id
   JOIN st_uoms u ON si.uom_id = u.id
   JOIN st_typecodes t ON si.type_code_id = t.id
  WHERE bl.balance <> 0::numeric;

GRANT SELECT ON TABLE reports.st_balancesoverview_detail TO "ooo-data";