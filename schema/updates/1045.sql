DROP VIEW po_linesoverview;

CREATE OR REPLACE VIEW po_linesoverview AS 
 SELECT pl.id, pl.order_id, pl.line_number, pl.productline_id, pl.stuom_id, pl.item_description, pl.order_qty, pl.price, pl.currency_id, pl.rate, pl.net_value, pl.twin_currency_id, pl.twin_rate, pl.twin_net_value, pl.base_net_value, pl.glaccount_id, pl.glcentre_id, pl.line_discount, pl.os_qty, pl.revised_qty, pl.del_qty, pl.due_delivery_date, pl.actual_delivery_date, pl.gr_note, pl.status, pl.usercompanyid, pl.stitem_id, pl.tax_rate_id, pl.created, pl.createdby, pl.alteredby, pl.lastupdated, ph.due_date, ph.order_date, ph.order_number, ph.plmaster_id, ph.receive_action, ph.type, ph.net_value AS order_value, cu.currency, (glc.cost_centre::text || ' - '::text) || glc.description::text AS glcentre, (gla.account::text || ' - '::text) || gla.description::text AS glaccount, ph.status AS order_status, plm.name AS supplier, (i.item_code::text || ' - '::text) || i.description::text AS stitem, u.uom_name
   FROM po_lines pl
   JOIN gl_centres glc ON glc.id = pl.glcentre_id
   JOIN gl_accounts gla ON gla.id = pl.glaccount_id
   JOIN cumaster cu ON cu.id = pl.currency_id
   JOIN po_header ph ON ph.id = pl.order_id
   JOIN plmaster plm ON ph.plmaster_id = plm.id
   LEFT JOIN st_items i ON i.id = pl.stitem_id
   LEFT JOIN st_uoms u ON u.id = pl.stuom_id;

DROP VIEW so_itemorders;

DROP VIEW so_headeroverview;

CREATE OR REPLACE VIEW so_headeroverview AS 
 SELECT so.*
 , slm.name AS customer
 , cum.currency
 , twc.currency AS twin_currency
   FROM so_header so
   JOIN slmaster slm ON so.slmaster_id = slm.id
   JOIN cumaster cum ON so.currency_id = cum.id
   JOIN cumaster twc ON so.twin_currency_id = twc.id;

CREATE OR REPLACE VIEW so_itemorders AS 
 SELECT sh.despatch_date, sh.customer, sh.order_number, sh.slmaster_id, sh.type, sh.despatch_action, sl.id, sl.order_id, sl.line_number, sl.productline_id, sl.stuom_id, sl.item_description, sl.order_qty, sl.price, sl.currency_id, sl.rate, sl.net_value, sl.twin_currency_id, sl.twin_rate, sl.twin_net_value, sl.base_net_value, sl.glaccount_id, sl.glcentre_id, sl.line_discount, sl.os_qty, sl.revised_qty, sl.del_qty, sl.due_delivery_date, sl.due_despatch_date, sl.actual_despatch_date, sl.delivery_note, sl.status, sl.usercompanyid, sl.stitem_id, (i.item_code::text || ' - '::text) || i.description::text AS stitem, sl.revised_qty AS required, u.uom_name AS stuom
   FROM so_lines sl
   JOIN st_items i ON i.id = sl.stitem_id
   JOIN st_uoms u ON u.id = sl.stuom_id
   JOIN so_headeroverview sh ON sh.id = sl.order_id
  WHERE sl.stitem_id IS NOT NULL AND (sl.status::text = ANY (ARRAY['N'::character varying, 'R'::character varying]::text[]));

DROP VIEW so_linesoverview;

CREATE OR REPLACE VIEW so_linesoverview AS 
 SELECT sl.*
 , sh.due_date, sh.order_date, sh.order_number, sh.slmaster_id, sh.type
 , slm.name AS customer
 , (i.item_code::text || ' - '::text) || i.description::text AS stitem
 , uom.uom_name
   FROM so_lines sl
   JOIN so_header sh ON sh.id = sl.order_id
   JOIN slmaster slm ON sh.slmaster_id = slm.id
   LEFT JOIN st_uoms uom ON sl.stuom_id = uom.id
   LEFT JOIN st_items i ON i.id = sl.stitem_id;

DROP VIEW so_itemorders;

CREATE OR REPLACE VIEW so_itemorders AS 
 SELECT sl.*
 , sh.despatch_date, sh.customer, sh.order_number, sh.slmaster_id, sh.type
 , sh.despatch_action
 , (i.item_code::text || ' - '::text) || i.description::text AS stitem
 , sl.revised_qty AS required
 , u.uom_name AS stuom
   FROM so_lines sl
   JOIN st_items i ON i.id = sl.stitem_id
   JOIN st_uoms u ON u.id = sl.stuom_id
   JOIN so_headeroverview sh ON sh.id = sl.order_id
  WHERE sl.stitem_id IS NOT NULL AND (sl.status::text = ANY (ARRAY['N'::character varying, 'R'::character varying]::text[]));

DROP VIEW pi_headeroverview;

CREATE OR REPLACE VIEW pi_headeroverview AS 
 SELECT pi.*
 , plm.name AS supplier
 , cum.currency
 , twc.currency AS twin
 , syt.description AS payment_terms
   FROM pi_header pi
   JOIN plmaster plm ON pi.plmaster_id = plm.id
   JOIN cumaster cum ON pi.currency_id = cum.id
   JOIN cumaster twc ON pi.twin_currency_id = twc.id
   JOIN syterms syt ON pi.payment_term_id = syt.id;

DROP VIEW si_headeroverview;

CREATE OR REPLACE VIEW si_headeroverview AS 
 SELECT si.*
 , slm.name AS customer
 , cum.currency
 , twc.currency AS twin
 , syt.description AS payment_terms
 , ts.description AS tax_status
 , slm.sl_analysis_id
 , sla.name
   FROM si_header si
   JOIN slmaster slm ON si.slmaster_id = slm.id
   LEFT JOIN sl_analysis sla ON slm.sl_analysis_id = sla.id
   JOIN cumaster cum ON si.currency_id = cum.id
   JOIN cumaster twc ON si.twin_currency_id = twc.id
   JOIN tax_statuses ts ON si.tax_status_id = ts.id
   JOIN syterms syt ON si.payment_term_id = syt.id;

DROP VIEW si_linesoverview;

CREATE OR REPLACE VIEW si_linesoverview AS 
 SELECT sl.*
 , sh.invoice_date, sh.invoice_number, sh.slmaster_id
 , soh.order_number
 , slm.name AS customer
 , (i.item_code::text || ' - '::text) || i.description::text AS stitem
 , uom.uom_name
   FROM si_lines sl
   JOIN si_header sh ON sh.id = sl.invoice_id
   JOIN slmaster slm ON sh.slmaster_id = slm.id
   LEFT JOIN so_header soh ON sl.sales_order_id = soh.id
   LEFT JOIN st_uoms uom ON sl.stuom_id = uom.id
   LEFT JOIN st_items i ON i.id = sl.stitem_id;

DROP VIEW sdl.gl_control_invs;

DROP VIEW validate.validate_cb_to_gl;

DROP VIEW validate.validate_gl;

DROP VIEW validate.validate_pl_to_gl;

DROP VIEW validate.validate_sl_to_gl;

DROP VIEW gltransactionsoverview;

CREATE OR REPLACE VIEW gltransactionsoverview AS 
 SELECT gl_transactions.*
 , gl_transactions.transaction_date AS trandate
 , gl_transactions.twin_currency_id AS twincurrency_id
 , gl_transactions.twin_rate AS twinrate
 , (gl_accounts.account::text || ' - '::text) || gl_accounts.description::text AS account
 , (gl_centres.cost_centre::text || ' - '::text) || gl_centres.description::text AS cost_centre
 , (gl_periods.year::text || ' - Period '::text) || gl_periods.period::text AS glperiod
 , cumaster.currency AS twincurrency
   FROM gl_transactions
   JOIN gl_accounts ON gl_transactions.glaccount_id = gl_accounts.id
   JOIN gl_centres ON gl_transactions.glcentre_id = gl_centres.id
   JOIN gl_periods ON gl_transactions.glperiods_id = gl_periods.id
   JOIN cumaster ON gl_transactions.twin_currency_id = cumaster.id;

CREATE OR REPLACE VIEW validate.validate_sl_to_gl AS 
 SELECT glbalancesoverview.glaccount_id, glbalancesoverview.account, glbalancesoverview.glcentre_id, glbalancesoverview.centre, sum(glbalancesoverview.value) AS balance_per_glbalances, ( SELECT sum(gltransactionsoverview.value) AS sum
           FROM gltransactionsoverview
          WHERE gltransactionsoverview.glaccount_id = glbalancesoverview.glaccount_id AND gltransactionsoverview.glcentre_id = glbalancesoverview.glcentre_id) AS balance_per_gltransactions, ( SELECT sum(sltransactionsoverview.base_gross_value) AS sum
           FROM sltransactionsoverview
          WHERE sltransactionsoverview.status::text <> 'P'::text) AS outstanding_per_sltransactions
   FROM glbalancesoverview
  WHERE glbalancesoverview.glaccount_id = (( SELECT gl_accounts.id
           FROM gl_accounts
          WHERE gl_accounts.account::text = ((( SELECT gl_params.paramvalue
                   FROM gl_params
                  WHERE gl_params.paramdesc::text = 'Sales Ledger Control Account'::text))::text))) AND glbalancesoverview.glcentre_id = (( SELECT gl_centres.id
           FROM gl_centres
          WHERE gl_centres.cost_centre::text = ((( SELECT gl_params.paramvalue
                   FROM gl_params
                  WHERE gl_params.paramdesc::text = 'Balance Sheet Cost Centre'::text))::text))) AND glbalancesoverview.glperiods_id >= 14
  GROUP BY glbalancesoverview.glaccount_id, glbalancesoverview.account, glbalancesoverview.glcentre_id, glbalancesoverview.centre;

CREATE OR REPLACE VIEW validate.validate_pl_to_gl AS 
 SELECT glbalancesoverview.glaccount_id, glbalancesoverview.account, glbalancesoverview.glcentre_id, glbalancesoverview.centre, sum(glbalancesoverview.value) AS balance_per_glbalances, ( SELECT sum(gltransactionsoverview.value) AS sum
           FROM gltransactionsoverview
          WHERE gltransactionsoverview.glaccount_id = glbalancesoverview.glaccount_id AND gltransactionsoverview.glcentre_id = glbalancesoverview.glcentre_id) AS balance_per_gltransactions, - (( SELECT sum(pltransactionsoverview.base_gross_value) AS sum
           FROM pltransactionsoverview
          WHERE pltransactionsoverview.status::text <> 'P'::text)) AS outstanding_per_pltransactions
   FROM glbalancesoverview
  WHERE glbalancesoverview.glaccount_id = (( SELECT gl_accounts.id
           FROM gl_accounts
          WHERE gl_accounts.account::text = ((( SELECT gl_params.paramvalue
                   FROM gl_params
                  WHERE gl_params.paramdesc::text = 'Purchase Ledger Control Account'::text))::text))) AND glbalancesoverview.glcentre_id = (( SELECT gl_centres.id
           FROM gl_centres
          WHERE gl_centres.cost_centre::text = ((( SELECT gl_params.paramvalue
                   FROM gl_params
                  WHERE gl_params.paramdesc::text = 'Balance Sheet Cost Centre'::text))::text))) AND glbalancesoverview.glperiods_id >= 14
  GROUP BY glbalancesoverview.glaccount_id, glbalancesoverview.account, glbalancesoverview.glcentre_id, glbalancesoverview.centre;

CREATE OR REPLACE VIEW validate.validate_gl AS 
 SELECT glbalancesoverview.glaccount_id, glbalancesoverview.account, glbalancesoverview.glcentre_id, glbalancesoverview.centre, sum(glbalancesoverview.value) AS balance_per_glbalances, ( SELECT sum(gltransactionsoverview.value) AS sum
           FROM gltransactionsoverview
          WHERE gltransactionsoverview.glaccount_id = glbalancesoverview.glaccount_id AND gltransactionsoverview.glcentre_id = glbalancesoverview.glcentre_id) AS balance_per_gltransactions, sum(glbalancesoverview.value) - (( SELECT sum(gltransactionsoverview.value) AS sum
           FROM gltransactionsoverview
          WHERE gltransactionsoverview.glaccount_id = glbalancesoverview.glaccount_id AND gltransactionsoverview.glcentre_id = glbalancesoverview.glcentre_id)) AS difference
   FROM glbalancesoverview
  WHERE glbalancesoverview.glperiods_id <> 27
  GROUP BY glbalancesoverview.account, glbalancesoverview.centre, glbalancesoverview.glaccount_id, glbalancesoverview.glcentre_id
  ORDER BY glbalancesoverview.account, glbalancesoverview.centre;

CREATE OR REPLACE VIEW validate.validate_cb_to_gl AS 
 SELECT cb_accountsoverview.name, glbalancesoverview.glaccount_id, glbalancesoverview.account, glbalancesoverview.glcentre_id, glbalancesoverview.centre, cb_accountsoverview.balance AS balance_per_cbaccounts, sum(glbalancesoverview.value) AS balance_per_glbalances, ( SELECT sum(gltransactionsoverview.value) AS sum
           FROM gltransactionsoverview
          WHERE gltransactionsoverview.glaccount_id = glbalancesoverview.glaccount_id AND gltransactionsoverview.glcentre_id = glbalancesoverview.glcentre_id) AS balance_per_gltransactions
   FROM glbalancesoverview
   JOIN cb_accountsoverview ON glbalancesoverview.glaccount_id = cb_accountsoverview.glaccount_id AND glbalancesoverview.glcentre_id = cb_accountsoverview.glcentre_id
  WHERE glbalancesoverview.glperiods_id >= 14
  GROUP BY glbalancesoverview.glaccount_id, cb_accountsoverview.name, glbalancesoverview.account, glbalancesoverview.glcentre_id, glbalancesoverview.centre, cb_accountsoverview.balance
  ORDER BY glbalancesoverview.glaccount_id;

CREATE OR REPLACE VIEW sdl.gl_control_invs AS 
 SELECT gltransactionsoverview.glperiod, gltransactionsoverview.account, sum(gltransactionsoverview.value) AS sum
   FROM gltransactionsoverview
  WHERE (gltransactionsoverview.account = '9310 - Sales Ledger Control'::text OR gltransactionsoverview.account = '9510 - Purchase Ledger Control'::text) AND (gltransactionsoverview.type::text = ANY (ARRAY['I'::character varying, 'C'::character varying]::text[]))
  GROUP BY gltransactionsoverview.glperiod, gltransactionsoverview.account
  ORDER BY gltransactionsoverview.glperiod, gltransactionsoverview.account;

GRANT SELECT ON TABLE sdl.gl_control_invs TO "ooo-data";