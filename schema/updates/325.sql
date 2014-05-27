ALTER TABLE gl_params ADD COLUMN paramvalue_id bigint;

UPDATE gl_params
   SET paramvalue=''
          ,paramvalue_id = (SELECT id
                                     FROM gl_centres
                                   WHERE cost_centre=gl_params.paramvalue)
 WHERE paramdesc='Balance Sheet Cost Centre';

UPDATE gl_params
   SET paramvalue=''
          ,paramvalue_id = (SELECT id
                                     FROM gl_accounts
                                   WHERE account=gl_params.paramvalue)
 WHERE paramdesc='Retained Profits Account';

UPDATE gl_params
   SET paramvalue=''
          ,paramvalue_id = (SELECT id
                                     FROM gl_accounts
                                   WHERE account=gl_params.paramvalue)
 WHERE paramdesc='Purchase Ledger Control Account';

UPDATE gl_params
   SET paramvalue=''
          ,paramvalue_id = (SELECT id
                                     FROM gl_accounts
                                   WHERE account=gl_params.paramvalue)
 WHERE paramdesc='Sales Ledger Control Account';

UPDATE gl_params
   SET paramvalue=''
          ,paramvalue_id = (SELECT id
                                     FROM cumaster
                                   WHERE currency=gl_params.paramvalue)
 WHERE paramdesc='Base Currency';

UPDATE gl_params
   SET paramvalue=''
          ,paramvalue_id = (SELECT id
                                     FROM cumaster
                                   WHERE currency=gl_params.paramvalue)
 WHERE paramdesc='Twin Currency';

UPDATE gl_params
   SET paramvalue=''
          ,paramvalue_id = (SELECT id
                                     FROM gl_accounts
                                   WHERE account=gl_params.paramvalue)
 WHERE paramdesc='VAT Input';

UPDATE gl_params
   SET paramvalue=''
          ,paramvalue_id = (SELECT id
                                     FROM gl_accounts
                                   WHERE account=gl_params.paramvalue)
 WHERE paramdesc='VAT Output';

UPDATE gl_params
   SET paramvalue=''
          ,paramvalue_id = (SELECT id
                                     FROM gl_accounts
                                   WHERE account=gl_params.paramvalue)
 WHERE paramdesc='VAT Control Account';

UPDATE gl_params
   SET paramvalue=''
          ,paramvalue_id = (SELECT id
                                     FROM gl_accounts
                                   WHERE account=gl_params.paramvalue)
 WHERE paramdesc='VAT EU Acquisitions';

UPDATE gl_params
   SET paramvalue=''
          ,paramvalue_id = (SELECT id
                                     FROM gl_accounts
                                   WHERE account=gl_params.paramvalue)
 WHERE paramdesc='Default Product Account Code';

UPDATE gl_params
   SET paramvalue=''
          ,paramvalue_id = (SELECT id
                                     FROM gl_centres
                                   WHERE cost_centre=gl_params.paramvalue)
 WHERE paramdesc='Default Product Centre Code';

UPDATE gl_params
   SET paramvalue=''
          ,paramvalue_id = (SELECT id
                                     FROM gl_accounts
                                   WHERE account=gl_params.paramvalue)
 WHERE paramdesc='Asset Purchases Suspense GL Account';

UPDATE gl_params
   SET paramvalue=''
          ,paramvalue_id = (SELECT id
                                     FROM gl_centres
                                   WHERE cost_centre=gl_params.paramvalue)
 WHERE paramdesc='Asset Purchases Suspense GL Cost Centre';

UPDATE gl_params
   SET paramvalue=''
          ,paramvalue_id = (SELECT id
                                     FROM gl_accounts
                                   WHERE account=gl_params.paramvalue)
 WHERE paramdesc='Asset Disposal Proceeds GL Account';

UPDATE gl_params
   SET paramvalue=''
          ,paramvalue_id = (SELECT id
                                     FROM gl_centres
                                   WHERE cost_centre=gl_params.paramvalue)
 WHERE paramdesc='Asset Disposal Proceeds Cost Centre';

UPDATE gl_params
   SET paramvalue=''
          ,paramvalue_id = (SELECT id
                                     FROM gl_centres
                                   WHERE cost_centre=gl_params.paramvalue)
 WHERE paramdesc='PandL Account Centre';

UPDATE gl_params
   SET paramvalue=''
          ,paramvalue_id = (SELECT id
                                     FROM gl_accounts
                                   WHERE account=gl_params.paramvalue)
 WHERE paramdesc='Expenses Control Account';

ALTER TABLE gl_params ALTER COLUMN paramvalue DROP NOT NULL;

DROP VIEW validate.validate_pl_to_gl;

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

DROP VIEW validate.validate_sl_to_gl;

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

DROP VIEW validate.control_account_ids;

CREATE OR REPLACE VIEW validate.control_account_ids AS 
 SELECT gl_params.paramdesc AS description
 , gl_params.paramvalue_id AS value
 , ( SELECT gl_accounts.id
           FROM gl_accounts
          WHERE gl_accounts.id::text = gl_params.paramvalue_id::text) AS glaccount_id
 , ( SELECT gl_params.paramvalue_id
           FROM gl_params
          WHERE gl_params.paramdesc::text = 'Balance Sheet Cost Centre'::text) AS cost_centre
 , ( SELECT gl_centres.id
           FROM gl_centres
          WHERE gl_centres.id::text = ((( SELECT gl_params.paramvalue_id
                   FROM gl_params
                  WHERE gl_params.paramdesc::text = 'Balance Sheet Cost Centre'::text))::text)) AS glcentre_id
   FROM gl_params
  WHERE gl_params.paramdesc::text = 'Sales Ledger Control Account'::text OR gl_params.paramdesc::text = 'Purchase Ledger Control Account'::text OR gl_params.paramdesc::text ~~ 'VAT%'::text
  ORDER BY gl_params.paramdesc;

DROP VIEW reports.ms_gl_outputtaxoverview;

CREATE OR REPLACE VIEW reports.ms_gl_outputtaxoverview AS 
 SELECT tr1.id, tr1.docref, tr1.glaccount_id, tr1.glcentre_id, tr1.glperiods_id, tr1.transaction_date AS trandate, tr1.source, tr1.comment, tr1.type, tr1.value AS vat, sum(tr2.value) AS net, tr1.usercompanyid
   FROM gl_transactions tr1, gl_transactions tr2, gl_accounts ac
  WHERE tr1.source::text = tr2.source::text AND tr1.docref::text = tr2.docref::text AND tr1.type::text = tr2.type::text AND tr2.glaccount_id = ac.id AND ac.control = false AND tr1.glaccount_id =
   (( SELECT gl_accounts.id
           FROM gl_accounts
          WHERE gl_accounts.id::text = ((( SELECT gl_params.paramvalue_id
                   FROM gl_params
                  WHERE gl_params.paramdesc::text = 'VAT Output'::text))::text))) AND tr1.glcentre_id =
                   (( SELECT gl_centres.id
           FROM gl_centres
          WHERE gl_centres.id::text = ((( SELECT gl_params.paramvalue_id
                   FROM gl_params
                  WHERE gl_params.paramdesc::text = 'Balance Sheet Cost Centre'::text))::text)))
  GROUP BY tr1.id, tr1.docref, tr1.glaccount_id, tr1.glcentre_id, tr1.glperiods_id, tr1.transaction_date, tr1.source, tr1.comment, tr1.type, tr1.value, tr1.usercompanyid;

DROP VIEW reports.ms_gl_inputtaxoverview;

CREATE OR REPLACE VIEW reports.ms_gl_inputtaxoverview AS 
 SELECT tr1.id, tr1.docref, tr1.glaccount_id, tr1.glcentre_id, tr1.glperiods_id, tr1.transaction_date AS trandate, tr1.source, tr1.comment, tr1.type, tr1.value AS vat, sum(tr2.value) AS net, tr1.usercompanyid
   FROM gl_transactions tr1, gl_transactions tr2, gl_accounts ac
  WHERE tr1.source::text = tr2.source::text AND tr1.docref::text = tr2.docref::text AND tr1.type::text = tr2.type::text AND tr2.glaccount_id = ac.id AND ac.control = false AND tr1.glaccount_id = 
  (( SELECT gl_accounts.id
           FROM gl_accounts
          WHERE gl_accounts.id::text = ((( SELECT gl_params.paramvalue_id
                   FROM gl_params
                  WHERE gl_params.paramdesc::text = 'VAT Input'::text))::text))) AND tr1.glcentre_id = (( SELECT gl_centres.id
           FROM gl_centres
          WHERE gl_centres.id::text = ((( SELECT gl_params.paramvalue_id
                   FROM gl_params
                  WHERE gl_params.paramdesc::text = 'Balance Sheet Cost Centre'::text))::text)))
  GROUP BY tr1.id, tr1.docref, tr1.glaccount_id, tr1.glcentre_id, tr1.glperiods_id, tr1.transaction_date, tr1.source, tr1.comment, tr1.type, tr1.value, tr1.usercompanyid;

DROP VIEW reports.ms_gl_eutaxoverview;

CREATE OR REPLACE VIEW reports.ms_gl_eutaxoverview AS 
 SELECT tr1.id, tr1.docref, tr1.glaccount_id, tr1.glcentre_id, tr1.glperiods_id, tr1.transaction_date AS trandate, tr1.source, tr1.comment, tr1.type, tr1.value AS vat, sum(tr2.value) AS net, tr1.usercompanyid
   FROM gl_transactions tr1, gl_transactions tr2, gl_accounts ac
  WHERE tr1.source::text = tr2.source::text AND tr1.docref::text = tr2.docref::text AND tr1.type::text = tr2.type::text AND tr2.glaccount_id = ac.id AND ac.control = false AND tr1.glaccount_id =
   (( SELECT gl_accounts.id
           FROM gl_accounts
          WHERE gl_accounts.id::text = ((( SELECT gl_params.paramvalue_id
                   FROM gl_params
                  WHERE gl_params.paramdesc::text = 'VAT EU Acquisitions'::text))::text))) AND tr1.glcentre_id = (( SELECT gl_centres.id
           FROM gl_centres
          WHERE gl_centres.id::text = ((( SELECT gl_params.paramvalue_id
                   FROM gl_params
                  WHERE gl_params.paramdesc::text = 'Balance Sheet Cost Centre'::text))::text)))
  GROUP BY tr1.id, tr1.docref, tr1.glaccount_id, tr1.glcentre_id, tr1.glperiods_id, tr1.transaction_date, tr1.source, tr1.comment, tr1.type, tr1.value, tr1.usercompanyid;

DROP VIEW glparams_vat_output;

CREATE OR REPLACE VIEW glparams_vat_output AS 
 SELECT p.paramvalue_id, p.usercompanyid
   FROM gl_params p
  WHERE p.paramdesc::text = 'VAT Output'::text;

DROP VIEW glparams_vat_input;

CREATE OR REPLACE VIEW glparams_vat_input AS 
 SELECT p.paramvalue_id, p.usercompanyid
   FROM gl_params p
  WHERE p.paramdesc::text = 'VAT Input'::text;

DROP VIEW glparams_vat_control;

CREATE OR REPLACE VIEW glparams_vat_control AS 
 SELECT p.paramvalue_id, p.usercompanyid
   FROM gl_params p
  WHERE p.paramdesc::text = 'VAT Control Account'::text;

DROP VIEW glparams_eu_acquisitions;

CREATE OR REPLACE VIEW glparams_eu_acquisitions AS 
 SELECT p.paramvalue_id, p.usercompanyid
   FROM gl_params p
  WHERE p.paramdesc::text = 'VAT EU Acquisitions'::text;