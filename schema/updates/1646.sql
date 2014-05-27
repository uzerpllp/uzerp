--
-- $Revision: 1.3 $
--

DROP VIEW st_transactionsoverview;

CREATE OR REPLACE VIEW st_transactionsoverview AS 
 SELECT t.id, t.balance, t.created, t.latest_cost, t.std_cost, t.stitem_id, t.whlocation_id
 , t.whbin_id, t.transfer_id, t.whaction_id, t.process_name, t.process_id, t.qty
 , t.usercompanyid, t.std_mat, t.std_lab, t.std_osc, t.std_ohd, t.latest_mat, t.latest_lab
 , t.latest_osc, t.latest_ohd, t.remarks, t.error_qty, t.status
 , (l.whstore::text || '/'::text) || l.description::text AS whlocation
 , l.has_balance
 , b.description AS whbin
 , (si.item_code::text || ' - '::text) || si.description::text AS stitem
 , (fl.whstore::text || '/'::text) || fl.description::text AS flocation
 , fb.description AS fbin
   FROM st_transactions t
   JOIN st_transactions f ON t.transfer_id = f.transfer_id AND t.id <> f.id AND t.created = f.created
   JOIN wh_locationsoverview l ON t.whlocation_id = l.id
   LEFT JOIN wh_bins b ON t.whbin_id = b.id
   JOIN st_items si ON t.stitem_id = si.id
   JOIN wh_locationsoverview fl ON f.whlocation_id = fl.id
   LEFT JOIN wh_bins fb ON f.whbin_id = fb.id;

ALTER TABLE st_transactionsoverview OWNER TO "www-data";

-- Index: st_transactions_process

-- DROP INDEX st_transactions_process;

CREATE INDEX st_transactions_process
  ON st_transactions
  USING btree
  (process_name, process_id);

-- Index: st_transactions_stitem

-- DROP INDEX st_transactions_stitem;

CREATE INDEX st_transactions_stitem
  ON st_transactions
  USING btree
  (stitem_id);

DELETE
  FROM uzlets
 WHERE name = 'WOrdersBookOverUnderNewEGlet';

UPDATE uzlets
   SET title = 'works_orders'
 WHERE name = 'WOrdersBookProductionNewEGlet';
 