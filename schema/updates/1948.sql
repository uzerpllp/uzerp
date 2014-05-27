--
-- $Revision: 1.1 $
--

-- TABLE cb_accounts 

-- Column: primary_account

-- ALTER TABLE cb_accounts DROP COLUMN primary_account;

ALTER TABLE cb_accounts ADD COLUMN primary_account boolean;
ALTER TABLE cb_accounts ALTER COLUMN primary_account SET DEFAULT FALSE;

UPDATE cb_accounts
   SET primary_account = FALSE;

-- View: cb_accountsoverview

DROP VIEW validate.validate_cb_to_gl;

DROP VIEW cb_accountsoverview;

CREATE OR REPLACE VIEW cb_accountsoverview AS 
 SELECT acc.*
 , (glaccount.account::text || ' - '::text) || glaccount.description::text AS revalue
 , (glcentre.cost_centre::text || ' - '::text) || glcentre.description::text AS glcentre
 , cumaster.currency
   FROM cb_accounts acc
   JOIN gl_accounts glaccount ON acc.glaccount_id = glaccount.id
   JOIN gl_centres glcentre ON acc.glcentre_id = glcentre.id
   JOIN cumaster ON acc.currency_id = cumaster.id;

ALTER TABLE cb_accountsoverview OWNER TO "www-data";

-- View: validate.validate_cb_to_gl

CREATE OR REPLACE VIEW validate.validate_cb_to_gl AS 
 SELECT cb_accountsoverview.name, glbalancesoverview.glaccount_id, glbalancesoverview.account, glbalancesoverview.glcentre_id, glbalancesoverview.centre, cb_accountsoverview.balance AS balance_per_cbaccounts, sum(glbalancesoverview.value) AS balance_per_glbalances, ( SELECT sum(gltransactionsoverview.value) AS sum
           FROM gltransactionsoverview
          WHERE gltransactionsoverview.glaccount_id = glbalancesoverview.glaccount_id AND gltransactionsoverview.glcentre_id = glbalancesoverview.glcentre_id) AS balance_per_gltransactions
   FROM glbalancesoverview
   JOIN cb_accountsoverview ON glbalancesoverview.glaccount_id = cb_accountsoverview.glaccount_id AND glbalancesoverview.glcentre_id = cb_accountsoverview.glcentre_id
  WHERE glbalancesoverview.glperiods_id >= 14
  GROUP BY glbalancesoverview.glaccount_id, cb_accountsoverview.name, glbalancesoverview.account, glbalancesoverview.glcentre_id, glbalancesoverview.centre, cb_accountsoverview.balance
  ORDER BY glbalancesoverview.glaccount_id;

ALTER TABLE validate.validate_cb_to_gl OWNER TO "www-data";

