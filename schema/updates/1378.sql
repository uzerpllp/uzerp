--
-- $Revision: 1.3 $
--

CREATE TABLE gl_period_end_balances
(
  id bigserial NOT NULL,
  glaccount_id bigint NOT NULL,
  glcentre_id bigint NOT NULL,
  glperiods_id bigint NOT NULL,
  mth_actual numeric NOT NULL DEFAULT 0.00,
  mth_budget numeric NOT NULL DEFAULT 0.00,
  ytd_actual numeric NOT NULL DEFAULT 0.00,
  ytd_budget numeric NOT NULL DEFAULT 0.00,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  usercompanyid bigint NOT NULL,
  CONSTRAINT gl_period_end_balance_pkey PRIMARY KEY (id),
  CONSTRAINT gl_period_end_balance_account_id_fkey FOREIGN KEY (glaccount_id)
      REFERENCES gl_accounts (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT gl_period_end_balance_centre_id_fkey FOREIGN KEY (glcentre_id)
      REFERENCES gl_centres (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT gl_period_end_balance_periods_id_fkey FOREIGN KEY (glperiods_id)
      REFERENCES gl_periods (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT gl_period_end_balance_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
);

ALTER TABLE gl_period_end_balances OWNER TO "www-data";

CREATE VIEW gl_period_end_balances_overview AS
SELECT b.*
     , p.year
     , p.period
     , s.sub_group
     , s.summary
     , a.analysis
     , (ac.account::text || ' - '::text) || ac.description::text as account
     , ac.actype
     , (cc.cost_centre::text || ' - '::text) || cc.description::text AS centre
  FROM gl_period_end_balances b
  JOIN gl_periods p ON p.id=b.glperiods_id
  JOIN gl_accounts ac ON ac.id=b.glaccount_id
  JOIN gl_centres cc ON cc.id=b.glcentre_id
  LEFT JOIN gl_analysis a ON ac.glanalysis_id = a.id
  LEFT JOIN gl_summaries s ON a.glsummary_id = s.id;

ALTER TABLE gl_period_end_balances_overview OWNER TO "www-data";

--
-- Insert the year-to-date values
--
-- Ensures entries are inserted where there are no monthly values
-- but balances exist for earlier months in the year
--
INSERT INTO gl_period_end_balances
( glaccount_id,
  glcentre_id,
  glperiods_id,
  mth_actual,
  mth_budget,
  ytd_actual,
  ytd_budget,
  usercompanyid
)
SELECT glaccount_id
     , glcentre_id
     , p1.id
     , 0.00
     , 0.00
     , sum(value)
     , 0.00
     , b.usercompanyid
  FROM gl_balances b
  JOIN gl_periods p1 ON p1.closed
                    AND p1.period>0
  JOIN gl_periods p2 ON p2.id = b.glperiods_id
                    AND p2.year = p1.year
                    AND p2.period<= p1.period
 GROUP BY glaccount_id
        , glcentre_id
        , p1.id
        , b.usercompanyid
 ORDER BY glaccount_id
        , glcentre_id
        , p1.id
        , b.usercompanyid;

--
-- Now add the monthly values
--
UPDATE gl_period_end_balances b1
   SET mth_actual = (SELECT value
                       FROM gl_balances b2
                      WHERE b1.glperiods_id=b2.glperiods_id
                        AND b1.glaccount_id=b2.glaccount_id
                        AND b1.glcentre_id=b2.glcentre_id)
 WHERE EXISTS (SELECT id
                 FROM gl_balances b2
                WHERE b1.glperiods_id=b2.glperiods_id
                  AND b1.glaccount_id=b2.glaccount_id
                  AND b1.glcentre_id=b2.glcentre_id);

DROP VIEW glbalancesoverview;

CREATE OR REPLACE VIEW glbalancesoverview AS 
 SELECT gl_balances.id, gl_balances.usercompanyid, gl_balances.glaccount_id
 , gl_balances.glcentre_id, gl_balances.glperiods_id, gl_balances.value
 , (gl_accounts.account::text || ' - '::text) || gl_accounts.description::text AS account
 , gl_accounts.actype
 , (gl_centres.cost_centre::text || ' - '::text) || gl_centres.description::text AS centre
 , (gl_periods.year::text || ' - Period '::text) || gl_periods.period::text AS periods
 , gl_periods.year, gl_periods.period
   FROM gl_balances
   JOIN gl_accounts ON gl_balances.glaccount_id = gl_accounts.id
   JOIN gl_centres ON gl_balances.glcentre_id = gl_centres.id
   JOIN gl_periods ON gl_balances.glperiods_id = gl_periods.id;

ALTER TABLE glbalancesoverview OWNER TO "www-data";

CREATE VIEW gl_year_to_date_summary as
SELECT b.glaccount_id
     , b.glcentre_id
     , p1.id as glperiods_id
     , p1.year
     , p1.period
     , b.usercompanyid
     , coalesce(m.value, 0.00) as mth_actual
     , sum(b.value) as ytd_actual
   FROM gl_periods p1
   JOIN gl_periods p2 ON p2.year = p1.year
                     AND p2.period <= p1.period
   FULL JOIN gl_balances b ON b.glperiods_id = p2.id
   FULL JOIN gl_balances m ON m.glaccount_id = b.glaccount_id
                          AND m.glcentre_id  = b.glcentre_id
                          AND m.glperiods_id = p1.id
 GROUP BY b.glaccount_id
     , b.glcentre_id
     , p1.id
     , p1.year
     , p1.period
     , m.value
     , b.usercompanyid
 ORDER BY b.glaccount_id
     , b.glcentre_id
     , b.usercompanyid;

ALTER TABLE gl_year_to_date_summary OWNER TO "www-data";

INSERT INTO module_components
  ("name", "type", location, module_id)
  SELECT 'glperiodendbalance', 'M', m.location||'/models/GLPeriodEndBalance.php', id
    FROM modules m
   WHERE m.name = 'ledger_setup';

INSERT INTO module_components
  ("name", "type", location, module_id)
  SELECT 'glperiodendbalancecollection', 'M', m.location||'/models/GLPeriodEndBalanceCollection.php', id
    FROM modules m
   WHERE m.name = 'ledger_setup';
