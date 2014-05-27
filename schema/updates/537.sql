DROP VIEW gl_taxoverview;

CREATE OR REPLACE VIEW glparams_vat_input AS 
 SELECT a.id, a.usercompanyid
   FROM gl_params p
   JOIN gl_accounts a ON a.account::text = p.paramvalue::text
  WHERE p.paramdesc::text = 'VAT Input'::text;

CREATE OR REPLACE VIEW glparams_vat_output AS 
 SELECT a.id, a.usercompanyid
   FROM gl_params p
   JOIN gl_accounts a ON a.account::text = p.paramvalue::text
  WHERE p.paramdesc::text = 'VAT Output'::text;

CREATE OR REPLACE VIEW glparams_vat_control AS 
 SELECT a.id, a.usercompanyid
   FROM gl_params p
   JOIN gl_accounts a ON a.account::text = p.paramvalue::text
  WHERE p.paramdesc::text = 'VAT Control Account'::text;

CREATE OR REPLACE VIEW glparams_eu_acquisitions AS 
 SELECT a.id, a.usercompanyid
   FROM gl_params p
   JOIN gl_accounts a ON a.account::text = p.paramvalue::text
  WHERE p.paramdesc::text = 'VAT EU Acquisitions'::text;

CREATE OR REPLACE VIEW gltransactions_noncontrol AS 
 SELECT gl_transactions.docref, gl_transactions.source, gl_transactions."type", gl_transactions.glperiods_id, gl_transactions.transaction_date AS trandate, sum(gl_transactions.value) AS net, gl_transactions.usercompanyid
   FROM gl_transactions, gl_accounts ac
  WHERE gl_transactions.glaccount_id = ac.id AND ac.control = false
  GROUP BY gl_transactions.docref, gl_transactions.source, gl_transactions."type", gl_transactions.glperiods_id, gl_transactions.transaction_date, gl_transactions.usercompanyid;

CREATE OR REPLACE VIEW gltransactions_vat AS 
 SELECT tr1.id, tr1.docref, tr1.glaccount_id, tr1.glcentre_id, tr1.glperiods_id, tr1.transaction_date AS trandate, tr1.source, tr1."comment", tr1."type", tr1.value AS vat, tr2.net, tr1.usercompanyid, a.account
   FROM gl_transactions tr1
   JOIN gl_accounts a ON a.id = tr1.glaccount_id
                    AND a.control = true
   JOIN gltransactions_noncontrol tr2 ON tr1.docref::text = tr2.docref::text AND tr1.source::text = tr2.source::text AND tr1."type"::text = tr2."type"::text;

DROP VIEW gl_taxeupurchases;

CREATE OR REPLACE VIEW gl_taxeupurchases AS 
 SELECT tr1.id, tr1.docref, tr1.glaccount_id, tr1.glcentre_id, tr1.glperiods_id, tr1.transaction_date AS trandate
, tr1.source, tr1."comment", tr1."type", tr1.value AS vat, tr2.net, tr1.usercompanyid, a.account
   FROM gl_transactions tr1
   JOIN gl_accounts a ON a.id = tr1.glaccount_id
                    AND a.control = true
   JOIN gltransactions_noncontrol tr2 ON tr1.docref::text = tr2.docref::text
                                     AND tr1.source::text = tr2.source::text
                                     AND tr1."type"::text = tr2."type"::text
   JOIN pi_header pih ON pih.invoice_number = cast(tr1.docref as integer)
   JOIN tax_statuses ts ON ts.id = pih.tax_status_id
                       AND ts.eu_tax = true
  WHERE tr1.source='P'
    AND ((tr1."type" ='I' AND tr1.value > 0) or (tr1."type" = 'C' and tr1.value < 0));

DROP VIEW gl_taxeusales;

CREATE OR REPLACE VIEW gl_taxeusales AS 
 SELECT tr1.id, tr1.docref, tr1.glaccount_id, tr1.glcentre_id, tr1.glperiods_id, tr1.transaction_date AS trandate
, tr1.source, tr1."comment", tr1."type", tr1.value AS vat, tr2.net, tr1.usercompanyid, a.account
   FROM gl_transactions tr1
   JOIN gl_accounts a ON a.id = tr1.glaccount_id
                    AND a.control = true
   JOIN gltransactions_noncontrol tr2 ON tr1.docref::text = tr2.docref::text
                                     AND tr1.source::text = tr2.source::text
                                     AND tr1."type"::text = tr2."type"::text
   JOIN si_header sih ON sih.invoice_number = cast(tr1.docref as integer)
   JOIN tax_statuses ts ON ts.id = sih.tax_status_id
                       AND ts.eu_tax = true
  WHERE tr1.source='S'
    AND ((tr1."type" ='I' AND tr1.value <= 0) or (tr1."type" = 'C' and tr1.value >= 0));
