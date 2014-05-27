CREATE TABLE so_despatchevents
(
  id bigserial NOT NULL,
  start_time timestamp without time zone NOT NULL,
  end_time timestamp without time zone NOT NULL,
  status character varying NOT NULL,
  title character varying NOT NULL,
  usercompanyid bigint,
  CONSTRAINT so_despatchevents_pkey PRIMARY KEY (id)
);

insert into permissions
(permission, type, title, display, parent_id)
select 'sodespatchevents', 'c', 'Delivery and Despatch', true, id
from permissions
where type='m'
and permission='despatch';

insert into permissions
(permission, type, title, display, parent_id)
select 'edit', 'a', 'Edit Event', false, id
from permissions
where type='c'
and permission='sodespatchevents';

DROP VIEW calendars_overview;

ALTER TABLE calendars DROP COLUMN gcal_calendar_id;
ALTER TABLE calendars DROP COLUMN gcal_magic_cookie;

CREATE OR REPLACE VIEW calendars_overview AS 
 SELECT c.id, c.name, c.owner, c.colour, cs.username, c.type, c.gcal_url, c.usercompanyid
   FROM calendars c
   LEFT JOIN calendar_shares cs ON c.id = cs.calendar_id;

DROP VIEW calendar_events_overview;

CREATE OR REPLACE VIEW calendar_events_overview AS 
 SELECT ce.id, ce.start_time, ce.end_time, ce.all_day, c.colour, ce.title, ce.description, ce.location, ce.url, ce.status, ce.owner, ce.private, ce.usercompanyid, ce.company_id, ce.person_id, ce.calendar_id, ce.end_time - ce.start_time AS difference, c.name AS calendar
   FROM calendar_events ce
   LEFT JOIN calendars c ON ce.calendar_id = c.id
  ORDER BY ce.start_time;


ALTER TABLE mf_structures ALTER COLUMN ststructure_id SET NOT NULL;

ALTER TABLE mf_wo_structures ALTER COLUMN uom_id SET NOT NULL;
ALTER TABLE mf_wo_structures ALTER COLUMN ststructure_id SET NOT NULL;

DROP VIEW customer_service_summary;

DROP VIEW customer_service;

CREATE OR REPLACE VIEW customer_service AS 
 SELECT sd.id, sd.despatch_number, sd.order_id, sd.slmaster_id, sd.despatch_date, sd.despatch_qty
 , sd.orderline_id, sd.productline_id, sd.stuom_id, sd.stitem_id, sd.status, sd.usercompanyid
 , sd.cs_failurecode_id, sh.order_number, sl.due_despatch_date, sl.order_qty, st.prod_group_id
 , pg.product_group
 , st.item_code||' - '||st.description AS stitem
 , c.name AS customer
 , cs.code AS failurecode
 , cs.description AS failure_description
   FROM so_despatchlines sd
   JOIN slmaster sc ON sc.id = sd.slmaster_id
   JOIN company c ON sc.company_id = c.id
   JOIN so_lines sl ON sl.id = sd.orderline_id
   JOIN so_header sh ON sh.id = sl.order_id
   LEFT JOIN cs_failurecodes cs ON cs.id = sd.cs_failurecode_id
   JOIN st_items st ON st.id = sd.stitem_id
   JOIN st_productgroups pg ON pg.id = st.prod_group_id
  WHERE sd.status::text = 'D'::text;

CREATE OR REPLACE VIEW customer_service_summary AS 
 SELECT customer_service.slmaster_id, customer_service.customer, customer_service.product_group, customer_service.despatch_date, customer_service.usercompanyid, to_char(customer_service.despatch_date::timestamp with time zone, 'YYYY/MM'::text) AS year_month, 
        CASE
            WHEN customer_service.despatch_date > customer_service.due_despatch_date THEN 0
            ELSE 1
        END AS ontime, 
        CASE
            WHEN customer_service.order_qty > customer_service.despatch_qty THEN 0
            ELSE 1
        END AS infull, 
        CASE
            WHEN customer_service.despatch_date > customer_service.due_despatch_date THEN 0
            WHEN customer_service.order_qty > customer_service.despatch_qty THEN 0
            ELSE 1
        END AS otif, 1 AS count
   FROM customer_service;

ALTER TABLE cb_accounts ALTER COLUMN balance SET DEFAULT 0.00;
ALTER TABLE cb_accounts ALTER COLUMN statement_balance SET DEFAULT 0.00;

ALTER TABLE wh_stores ALTER COLUMN address_id SET NOT NULL;

ALTER TABLE po_product_lines ALTER COLUMN description SET NOT NULL;
ALTER TABLE so_product_lines ALTER COLUMN description SET NOT NULL;

ALTER TABLE module_components
  ADD CONSTRAINT module_components_unq1 UNIQUE(location);

insert into module_components
(name, "type", location, module_id)
select 'manufacturingcontroller', 'C', location||'/controllers/ManufacturingController.php', id
  from modules
 where name='manufacturing'
   and not exists (select id
                     from module_components
                    where name='manufacturingcontroller'
                      and location=(select location||'/controllers/ManufacturingController.php'
                                      from modules
                                     where name='manufacturing'));

insert into module_components
(name, "type", location, module_id)
select 'mfdeptssearch', 'M', location||'/models/mfdeptsSearch.php', id
  from modules
 where name='manufacturing'
   and not exists (select id
                     from module_components
                    where name='mfdeptssearch'
                      and location=(select location||'/models/mfdeptsSearch.php'
                                      from modules
                                     where name='manufacturing'));

insert into module_components
(name, "type", location, module_id)
select 'ledgercontroller', 'C', location||'/controllers/LedgerController.php', id
  from modules
 where name='ledger'
   and not exists (select id
                     from module_components
                    where name='ledgercontroller'
                      and location=(select location||'/controllers/LedgerController.php'
                                      from modules
                                     where name='ledger'));
                                     
-- concatinate year and period to allow ordering        
DROP VIEW reports.gl_control_invs;

DROP VIEW validate.validate_sl_to_gl;

DROP VIEW validate.validate_pl_to_gl;

DROP VIEW validate.validate_gl;

DROP VIEW validate.validate_cb_to_gl;

DROP VIEW gltransactionsoverview;

CREATE OR REPLACE VIEW gltransactionsoverview AS 
 SELECT gl_transactions.id, gl_transactions.docref, gl_transactions.usercompanyid
 , gl_transactions.glaccount_id, gl_transactions.glcentre_id, gl_transactions.glperiods_id
 , gl_transactions.transaction_date AS trandate, gl_transactions.source, gl_transactions.comment
 , gl_transactions.reference, gl_transactions.twin_currency_id AS twincurrency_id
 , gl_transactions.twin_rate AS twinrate, gl_transactions.type, gl_transactions.twinvalue
 , gl_transactions.value
 , (gl_accounts.account::text || ' - '::text) || gl_accounts.description::text AS account
 , (gl_centres.cost_centre::text || ' - '::text) || gl_centres.description::text AS cost_centre
 , (gl_periods.year::text || ' - Period '::text) || gl_periods.period::text AS glperiod
 , cumaster.currency AS twincurrency
 ,  CAST(gl_periods.year::int || '' || lpad(cast(gl_periods.period as text), 2, '0') AS INT) AS year_period
   FROM gl_transactions
   JOIN gl_accounts ON gl_transactions.glaccount_id = gl_accounts.id
   JOIN gl_centres ON gl_transactions.glcentre_id = gl_centres.id
   JOIN gl_periods ON gl_transactions.glperiods_id = gl_periods.id
   JOIN cumaster ON gl_transactions.twin_currency_id = cumaster.id;

CREATE OR REPLACE VIEW validate.validate_cb_to_gl AS 
 SELECT cb_accountsoverview.name, glbalancesoverview.glaccount_id, glbalancesoverview.account, glbalancesoverview.glcentre_id, glbalancesoverview.centre, cb_accountsoverview.balance AS balance_per_cbaccounts, sum(glbalancesoverview.value) AS balance_per_glbalances, ( SELECT sum(gltransactionsoverview.value) AS sum
           FROM gltransactionsoverview
          WHERE gltransactionsoverview.glaccount_id = glbalancesoverview.glaccount_id AND gltransactionsoverview.glcentre_id = glbalancesoverview.glcentre_id) AS balance_per_gltransactions
   FROM glbalancesoverview
   JOIN cb_accountsoverview ON glbalancesoverview.glaccount_id = cb_accountsoverview.glaccount_id AND glbalancesoverview.glcentre_id = cb_accountsoverview.glcentre_id
  WHERE glbalancesoverview.glperiods_id >= 14
  GROUP BY glbalancesoverview.glaccount_id, cb_accountsoverview.name, glbalancesoverview.account, glbalancesoverview.glcentre_id, glbalancesoverview.centre, cb_accountsoverview.balance
  ORDER BY glbalancesoverview.glaccount_id;

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

CREATE OR REPLACE VIEW validate.validate_pl_to_gl AS 
 SELECT glbalancesoverview.glaccount_id, glbalancesoverview.account, glbalancesoverview.glcentre_id, glbalancesoverview.centre, sum(glbalancesoverview.value) AS balance_per_glbalances, ( SELECT sum(gltransactionsoverview.value) AS sum
           FROM gltransactionsoverview
          WHERE gltransactionsoverview.glaccount_id = glbalancesoverview.glaccount_id AND gltransactionsoverview.glcentre_id = glbalancesoverview.glcentre_id) AS balance_per_gltransactions, - (( SELECT sum(pltransactionsoverview.base_gross_value) AS sum
           FROM pltransactionsoverview
          WHERE pltransactionsoverview.status::text <> 'P'::text)) AS outstanding_per_pltransactions
   FROM glbalancesoverview
  WHERE glbalancesoverview.glaccount_id = (( SELECT gl_accounts.id
           FROM gl_accounts
          WHERE gl_accounts.id::text = ((( SELECT gl_params.paramvalue_id
                   FROM gl_params
                  WHERE gl_params.paramdesc::text = 'Purchase Ledger Control Account'::text))::text))) AND glbalancesoverview.glcentre_id = (( SELECT gl_centres.id
           FROM gl_centres
          WHERE gl_centres.id::text = ((( SELECT gl_params.paramvalue_id
                   FROM gl_params
                  WHERE gl_params.paramdesc::text = 'Balance Sheet Cost Centre'::text))::text))) AND glbalancesoverview.glperiods_id >= 14
  GROUP BY glbalancesoverview.glaccount_id, glbalancesoverview.account, glbalancesoverview.glcentre_id, glbalancesoverview.centre;

CREATE OR REPLACE VIEW validate.validate_sl_to_gl AS 
 SELECT glbalancesoverview.glaccount_id, glbalancesoverview.account, glbalancesoverview.glcentre_id, glbalancesoverview.centre, sum(glbalancesoverview.value) AS balance_per_glbalances, ( SELECT sum(gltransactionsoverview.value) AS sum
           FROM gltransactionsoverview
          WHERE gltransactionsoverview.glaccount_id = glbalancesoverview.glaccount_id AND gltransactionsoverview.glcentre_id = glbalancesoverview.glcentre_id) AS balance_per_gltransactions, ( SELECT sum(sltransactionsoverview.base_gross_value) AS sum
           FROM sltransactionsoverview
          WHERE sltransactionsoverview.status::text <> 'P'::text) AS outstanding_per_sltransactions
   FROM glbalancesoverview
  WHERE glbalancesoverview.glaccount_id = (( SELECT gl_accounts.id
           FROM gl_accounts
          WHERE gl_accounts.id::text = ((( SELECT gl_params.paramvalue_id
                   FROM gl_params
                  WHERE gl_params.paramdesc::text = 'Sales Ledger Control Account'::text))::text))) AND glbalancesoverview.glcentre_id = (( SELECT gl_centres.id
           FROM gl_centres
          WHERE gl_centres.id::text = ((( SELECT gl_params.paramvalue_id
                   FROM gl_params
                  WHERE gl_params.paramdesc::text = 'Balance Sheet Cost Centre'::text))::text))) AND glbalancesoverview.glperiods_id >= 14
  GROUP BY glbalancesoverview.glaccount_id, glbalancesoverview.account, glbalancesoverview.glcentre_id, glbalancesoverview.centre;

CREATE OR REPLACE VIEW reports.gl_control_invs AS 
 SELECT gltransactionsoverview.glperiod, gltransactionsoverview.account, sum(gltransactionsoverview.value) AS sum
   FROM gltransactionsoverview
  WHERE (gltransactionsoverview.account = '9310 - Sales Ledger Control'::text OR gltransactionsoverview.account = '9510 - Purchase Ledger Control'::text) AND (gltransactionsoverview.type::text = ANY (ARRAY['I'::character varying, 'C'::character varying]::text[]))
  GROUP BY gltransactionsoverview.glperiod, gltransactionsoverview.account
  ORDER BY gltransactionsoverview.glperiod, gltransactionsoverview.account;

GRANT SELECT ON TABLE reports.gl_control_invs TO "ooo-data";


-- REPORTS

-- Just to catch any null values
UPDATE reports
SET description='No Description'
WHERE description IS NULL;

ALTER TABLE reports ALTER COLUMN description SET NOT NULL;

--
ALTER TABLE reports ADD COLUMN field_formatting character varying;
