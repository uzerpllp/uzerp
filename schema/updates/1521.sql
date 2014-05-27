--
-- $Revision: 1.3 $
--

-- Table: gl_transactions

-- DROP TABLE gl_transactions_header;

CREATE TABLE gl_transactions_header
(
  id bigserial NOT NULL,
  docref character varying NOT NULL DEFAULT 0,
  glperiods_id bigint NOT NULL,
  accrual boolean,
  accrual_period_id bigint,
  transaction_date date NOT NULL DEFAULT now(),
  status character varying NOT NULL,
  "comment" text,
  reference character varying,
  "type" character varying NOT NULL,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT gl_transactions_header_pkey PRIMARY KEY (id),
  CONSTRAINT gl_transactions_header_glperiods_id_fkey FOREIGN KEY (glperiods_id)
      REFERENCES gl_periods (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT gl_transactions_header_accrual_period_id_fkey FOREIGN KEY (accrual_period_id)
      REFERENCES gl_periods (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT gl_transactions_header_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT gl_transactions_header_docref_ukey UNIQUE (docref)
);

ALTER TABLE gl_transactions_header OWNER TO "www-data";

-- Table: gl_transactions

-- DROP TABLE gl_unposted_transactions;

CREATE TABLE gl_unposted_transactions
(
  id bigserial NOT NULL,
  docref character varying NOT NULL DEFAULT 0,
  glaccount_id bigint NOT NULL,
  glcentre_id bigint NOT NULL,
  source character varying NOT NULL,
  "comment" text,
  reference character varying,
  "type" character varying NOT NULL,
  "value" numeric NOT NULL,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT gl_unposted_transaction_pkey PRIMARY KEY (id),
  CONSTRAINT gl_unposted_transaction_header_fkey FOREIGN KEY (docref)
      REFERENCES gl_transactions_header (docref) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT gl_unposted_transaction_glaccount_id_fkey FOREIGN KEY (glaccount_id)
      REFERENCES gl_accounts (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT gl_unposted_transaction_glcentre_id_fkey FOREIGN KEY (glcentre_id)
      REFERENCES gl_centres (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT gl_unposted_transaction_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
);

ALTER TABLE gl_unposted_transactions OWNER TO "www-data";

-- Foreign Key: gltransaction_glaccount_id_fkey

ALTER TABLE gl_transactions DROP CONSTRAINT gltransaction_glaccount_id_fkey;

ALTER TABLE gl_transactions
  ADD CONSTRAINT gltransaction_glaccount_id_fkey FOREIGN KEY (glaccount_id)
      REFERENCES gl_accounts (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

-- View: gl_transactions_header_overview

DROP VIEW IF EXISTS gl_transactions_header_overview;

CREATE OR REPLACE VIEW gl_transactions_header_overview AS 
 SELECT glth.*
 , (glp1.year::text || ' - Period '::text) || glp1.period::text AS glperiod
 , (glp2.year::text || ' - Period '::text) || glp2.period::text AS accrual_period
   FROM gl_transactions_header glth
   JOIN gl_periods glp1 ON glth.glperiods_id = glp1.id
   LEFT JOIN gl_periods glp2 ON glth.accrual_period_id = glp2.id;

ALTER TABLE gl_transactions_header_overview OWNER TO "www-data";

-- View: gl_unposted_transactions_overview

DROP VIEW IF EXISTS gl_unposted_transactions_overview;

CREATE OR REPLACE VIEW gl_unposted_transactions_overview AS 
 SELECT glt.*
 , case when value<0 then value*-1 else null end as credit
 , case when value<0 then null else value end as debit
 , (gla.account::text || ' - '::text) || gla.description::text AS account
 , (glc.cost_centre::text || ' - '::text) || glc.description::text AS cost_centre
   FROM gl_unposted_transactions glt
   JOIN gl_accounts gla ON glt.glaccount_id = gla.id
   JOIN gl_centres glc ON glt.glcentre_id = glc.id;

ALTER TABLE gl_unposted_transactions_overview OWNER TO "www-data";

-- Drop views dependant on gltransactionsoverview and glbalancesoverview

DROP VIEW validate.validate_sl_to_gl;

DROP VIEW validate.validate_pl_to_gl;

DROP VIEW validate.validate_gl;

DROP VIEW validate.validate_cb_to_gl;

-- View: gltransactionsoverview

DROP VIEW gltransactionsoverview;

CREATE OR REPLACE VIEW gltransactionsoverview AS 
 SELECT glt.*
 , case when glt.value<0 then null else glt.value end as debit
 , case when glt.value<0 then glt.value*-1 else null end as credit
 , (gl_accounts.account::text || ' - '::text) || gl_accounts.description::text AS account
 , (gl_centres.cost_centre::text || ' - '::text) || gl_centres.description::text AS cost_centre
 , (gl_periods.year::text || ' - Period '::text) || gl_periods.period::text AS glperiod
 , cumaster.currency AS twincurrency
 , ((gl_periods.year::integer || ''::text) || lpad(gl_periods.period::text, 2, '0'::text))::integer AS year_period
   FROM gl_transactions glt
   JOIN gl_accounts ON glt.glaccount_id = gl_accounts.id
   JOIN gl_centres ON glt.glcentre_id = gl_centres.id
   JOIN gl_periods ON glt.glperiods_id = gl_periods.id
   JOIN cumaster ON glt.twin_currency_id = cumaster.id;

ALTER TABLE gltransactionsoverview OWNER TO "www-data";

-- View: glbalancesoverview

DROP VIEW glbalancesoverview;

CREATE OR REPLACE VIEW glbalancesoverview AS 
 SELECT b.*,
        CASE
            WHEN b.value < 0::numeric THEN b.value * (-1)::numeric
            ELSE NULL::numeric
        END AS credit, 
        CASE
            WHEN b.value < 0::numeric THEN NULL::numeric
            ELSE b.value
        END AS debit
 , (a.account::text || ' - '::text) || a.description::text AS account
 , a.actype, (c.cost_centre::text || ' - '::text) || c.description::text AS centre
 , (p.year::text || ' - Period '::text) || p.period::text AS periods
 , p.year, p.period
   FROM gl_balances b
   JOIN gl_accounts a ON b.glaccount_id = a.id
   JOIN gl_centres c ON b.glcentre_id = c.id
   JOIN gl_periods p ON b.glperiods_id = p.id;

ALTER TABLE glbalancesoverview OWNER TO "www-data";
GRANT ALL ON TABLE glbalancesoverview TO "www-data";

-- Recreate views dependant on gltransactionsoverview and glbalancesoverview

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

ALTER TABLE validate.validate_gl OWNER TO "www-data";

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

ALTER TABLE validate.validate_pl_to_gl OWNER TO "www-data";

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

ALTER TABLE validate.validate_sl_to_gl OWNER TO "www-data";

--
-- Update gl_transactions to change the type for Allocation Currency Adjustments
--

UPDATE gl_transactions
   SET type = 'CA'
 WHERE source = 'G'
   AND type = 'J'
   AND comment LIKE '%Allocation Currency Adjustment%';

--
-- Populate the gl_transactions_header
-- from the existing data in gl_transactions
-- ignoring any currency adjustments
--

INSERT INTO gl_transactions_header
(docref, glperiods_id, accrual, accrual_period_id, transaction_date, status, "type", usercompanyid, created)
SELECT DISTINCT CAST(t1.docref AS integer), t1.glperiods_id, (t1.docref = coalesce(t2.docref,'X')), t2.glperiods_id, t1.transaction_date, 'O', 'S', t1.usercompanyid, t1.transaction_date
  FROM gl_transactions t1
  LEFT JOIN gl_transactions t2 ON t1.docref = t2.docref
                              AND t1.transaction_date = t2.transaction_date
                              AND t1.glperiods_id < t2.glperiods_id
                              AND t1.id != t2.id
 WHERE t1.source = 'G'
   AND t1.type = 'J'
   AND t1.comment not like '%Currency%'
   AND NOT EXISTS (SELECT 1
                     FROM gl_transactions t3
                    WHERE t1.docref = t3.docref
                      AND t1.transaction_date = t3.transaction_date
                      AND t1.glperiods_id > t3.glperiods_id
                      AND t1.id != t3.id)
 ORDER BY 1;

-- View: gl_taxeupurchases

DROP VIEW gl_taxeupurchases;

CREATE OR REPLACE VIEW gl_taxeupurchases AS 
 SELECT tr1.id, tr1.docref, tr1.glaccount_id, tr1.glcentre_id, tr1.glperiods_id, tr1.transaction_date, tr1.source
 , tr1.comment, tr1.type, tr1.value AS vat, tr2.net, tr1.usercompanyid, a.account
   FROM gl_transactions tr1
   JOIN gl_accounts a ON a.id = tr1.glaccount_id AND a.control = true
   JOIN gltransactions_noncontrol tr2 ON tr1.docref::text = tr2.docref::text AND tr1.source::text = tr2.source::text AND tr1.type::text = tr2.type::text
   JOIN pi_header pih ON pih.invoice_number = tr1.docref::integer
   JOIN tax_statuses ts ON ts.id = pih.tax_status_id AND ts.eu_tax = true
  WHERE tr1.source::text = 'P'::text AND (tr1.type::text = 'I'::text AND tr1.value > 0::numeric OR tr1.type::text = 'C'::text AND tr1.value < 0::numeric);

ALTER TABLE gl_taxeupurchases OWNER TO "www-data";

-- View: gl_taxeusales

DROP VIEW gl_taxeusales;

CREATE OR REPLACE VIEW gl_taxeusales AS 
 SELECT tr1.id, tr1.docref, tr1.glaccount_id, tr1.glcentre_id, tr1.glperiods_id, tr1.transaction_date
 , tr1.source, tr1.comment, tr1.type, tr1.value AS vat, tr2.net, tr1.usercompanyid, a.account
   FROM gl_transactions tr1
   JOIN gl_accounts a ON a.id = tr1.glaccount_id AND a.control = true
   JOIN gltransactions_noncontrol tr2 ON tr1.docref::text = tr2.docref::text AND tr1.source::text = tr2.source::text AND tr1.type::text = tr2.type::text
   JOIN si_header sih ON sih.invoice_number = tr1.docref::integer
   JOIN tax_statuses ts ON ts.id = sih.tax_status_id AND ts.eu_tax = true
  WHERE tr1.source::text = 'S'::text AND (tr1.type::text = 'I'::text AND tr1.value <= 0::numeric OR tr1.type::text = 'C'::text AND tr1.value >= 0::numeric);

ALTER TABLE gl_taxeusales OWNER TO "www-data";

-- View: gltransactions_vat

DROP VIEW gltransactions_vat;

CREATE OR REPLACE VIEW gltransactions_vat AS 
 SELECT tr1.id, tr1.docref, tr1.glaccount_id, tr1.glcentre_id, tr1.glperiods_id, tr1.transaction_date, tr1.source
 , tr1.comment, tr1.type, tr1.value AS vat, tr2.net, tr1.usercompanyid, a.account
   FROM gl_transactions tr1
   JOIN gl_accounts a ON a.id = tr1.glaccount_id AND a.control = true
   JOIN gltransactions_noncontrol tr2 ON tr1.docref::text = tr2.docref::text AND tr1.source::text = tr2.source::text AND tr1.type::text = tr2.type::text;

ALTER TABLE gltransactions_vat OWNER TO "www-data";

--
-- Report Definition : VatTransaction
--

UPDATE report_definitions
   SET definition = '﻿<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
	<xsl:template match="/">
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:layout-master-set>
				<fo:simple-page-master master-name="all-pages"
						page-height="21cm"
						page-width="29.7cm"
			                        margin="0.5cm" >
					<fo:region-body margin-top="2cm" margin-bottom="7mm"/>
					<fo:region-before extent="2cm"  />
					<fo:region-after extent="5mm"/>
				</fo:simple-page-master>
				<fo:page-sequence-master master-name="default-sequence">
					<fo:repeatable-page-master-reference master-reference="all-pages"/>
				</fo:page-sequence-master>
			</fo:layout-master-set>
			<fo:page-sequence master-reference="default-sequence">
				<!-- PAGE HEADER -->
				<fo:static-content flow-name="xsl-region-before" >
					<fo:block font-size="16px" text-align="center">
						<xsl:value-of select="/data/extra/title" />
					</fo:block>
					<fo:block font-size="10px" text-align="center" border-bottom-width="1pt" border-bottom-style="solid" padding="2mm" >Account: <xsl:value-of select="data/extra/account" />,    Cost Centre: <xsl:value-of select="data/extra/centre" /></fo:block>
				</fo:static-content>
				<!-- PAGE FOOTER -->
				<fo:static-content flow-name="xsl-region-after" font-size="8px">
					<fo:block>Page <!--[page_position]--></fo:block>
				</fo:static-content>
				<!-- BODY -->
				<fo:flow flow-name="xsl-region-body" font-size="8px">
					<fo:table table-layout="fixed" width="100%" >
						<fo:table-column column-width="proportional-column-width(70)"/>
						<fo:table-column column-width="proportional-column-width(70)"/>
						<fo:table-column column-width="proportional-column-width(100)"/>
						<fo:table-column column-width="proportional-column-width(150)"/>
						<fo:table-column column-width="proportional-column-width(100)"/>
						<fo:table-column column-width="proportional-column-width(70)"/>
						<fo:table-column column-width="proportional-column-width(70)"/>
						<fo:table-column column-width="proportional-column-width(100)"/>
						<fo:table-column column-width="proportional-column-width(70)"/>
						<fo:table-header>
							<fo:table-row>
								<xsl:attribute name="background-color"><!--[table_header_colour]--></xsl:attribute>
								<fo:table-cell padding="1mm">
									<fo:block>Date</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm">
									<fo:block>Doc Ref</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm">
									<fo:block>Ext Ref</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm">
									<fo:block>Company</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm">
									<fo:block>Comment</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm">
									<fo:block text-align="right">VAT</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm">
									<fo:block text-align="right">Net</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm">
									<fo:block>Source</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm">
									<fo:block>Type</fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-header>
						<fo:table-body>
							<xsl:for-each select="data/Vat">
								<fo:table-row>
									<!-- this condition is to provide us with alternate row colours -->
									<xsl:if test="(position() mod 2 = 1)">
										<xsl:attribute name="background-color"><!--[table_row_alternate_colour]--></xsl:attribute>
									</xsl:if>
									<fo:table-cell padding="1mm">
										<fo:block>
											<xsl:value-of select="transaction_date" />
										</fo:block>
									</fo:table-cell>
									<fo:table-cell padding="1mm">
										<fo:block>
											<xsl:value-of select="docref" />
										</fo:block>
									</fo:table-cell>
									<fo:table-cell padding="1mm">
										<fo:block>
											<xsl:value-of select="ext_reference" />
										</fo:block>
									</fo:table-cell>
									<fo:table-cell padding="1mm">
										<fo:block>
											<xsl:value-of select="company" />
										</fo:block>
									</fo:table-cell>
									<fo:table-cell padding="1mm">
										<fo:block>
											<xsl:value-of select="comment" />
										</fo:block>
									</fo:table-cell>
									<fo:table-cell padding="1mm">
										<fo:block text-align="right">
											<xsl:value-of select="vat" />
										</fo:block>
									</fo:table-cell>
									<fo:table-cell padding="1mm">
										<fo:block text-align="right">
											<xsl:value-of select="net" />
										</fo:block>
									</fo:table-cell>
									<fo:table-cell padding="1mm">
										<fo:block>
											<xsl:value-of select="source" />
										</fo:block>
									</fo:table-cell>
									<fo:table-cell padding="1mm">
										<fo:block>
											<xsl:value-of select="type" />
										</fo:block>
									</fo:table-cell>
								</fo:table-row>
							</xsl:for-each>
							<fo:table-row>
								<xsl:attribute name="background-color"><!--[table_header_colour]--></xsl:attribute>
								<fo:table-cell padding="1mm" number-columns-spanned="5" text-align="right">
									<fo:block>Total</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm" text-align="right">
									<fo:block>
										<xsl:value-of select="data/extra/total_vat" />
									</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm" text-align="right">
									<fo:block>
										<xsl:value-of select="data/extra/total_net" />
									</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm" number-columns-spanned="2" >
									<fo:block />
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
					<fo:block id="last-page"/>
				</fo:flow>
			</fo:page-sequence>
		</fo:root>
	</xsl:template>
</xsl:stylesheet>'
 WHERE name = 'VatTransaction';
--
-- Modules/Components
--

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'gltransactionheaderscontroller', 'C', location||'/controllers/GltransactionheadersController.php', id, 'GL Journal Header'
   FROM modules m
  WHERE name = 'general_ledger';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'gltransactionheader', 'M', location||'/models/GLTransactionHeader.php', id, 'GL Journal Header'
   FROM modules m
  WHERE name = 'general_ledger';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'gltransactionheadercollection', 'M', location||'/models/GLTransactionHeaderCollection.php', id, 'GL Journal Header'
   FROM modules m
  WHERE name = 'general_ledger';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'glunpostedtransaction', 'M', location||'/models/GLUnpostedTransaction.php', id, 'GL Unposted Journal'
   FROM modules m
  WHERE name = 'general_ledger';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'glunpostedtransactioncollection', 'M', location||'/models/GLUnpostedTransactionCollection.php', id, 'GL Unposted Journal'
   FROM modules m
  WHERE name = 'general_ledger';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'gltransactionheaderssearch', 'M', location||'/models/gltransactionheadersSearch.php', id, 'GL Transactions Header Search'
   FROM modules m
  WHERE name = 'general_ledger';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'unpostedjournalsuzlet', 'E', location||'/uzlets/unpostedJournalsUZlet.php', id, 'Unposted Journals UZlet'
   FROM modules m
  WHERE name = 'general_ledger';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'templatejournalsuzlet', 'E', location||'/uzlets/templateJournalsUZlet.php', id, 'Recurring Journals UZlet'
   FROM modules m
  WHERE name = 'general_ledger';

  --
-- UZlets
--

-- unpostedJournalsUZlet

INSERT INTO uzlets
       ("name", title, preset,enabled, dashboard, usercompanyid)
SELECT 'unpostedJournalsUZlet', 'Unposted Journals UZlet', FALSE, TRUE, TRUE, id
  FROM system_companies;

INSERT INTO uzlet_modules
       (uzlet_id, module_id, usercompanyid)
SELECT u.id, m.id, u.usercompanyid
  FROM uzlets u
     , modules m
 WHERE u.name = 'unpostedJournalsUZlet'
   AND m.name = 'general_ledger';

-- templateJournalsUZlet

INSERT INTO uzlets
       ("name", title, preset,enabled, dashboard, usercompanyid)
SELECT 'templateJournalsUZlet', 'Template Journals UZlet', FALSE, TRUE, TRUE, id
  FROM system_companies;

INSERT INTO uzlet_modules
       (uzlet_id, module_id, usercompanyid)
SELECT u.id, m.id, u.usercompanyid
  FROM uzlets u
     , modules m
 WHERE u.name = 'templateJournalsUZlet'
   AND m.name = 'general_ledger';

--
-- Permissions
--

-- permission:	gltransactionheaders
-- title:		GL Journal Headers

INSERT INTO permissions
(permission, type, title, display, parent_id, position)
SELECT 'gltransactionheaders', 'c', 'GL Journal Headers', true, m.id, next.position
  FROM permissions m
     , (SELECT coalesce(max(c.position), 0)+1 as position
          FROM permissions c
          JOIN permissions m ON c.parent_id = m.id
                            AND m.type = 'm'
                            AND m.permission='general_ledger'
         WHERE c.type = 'c') next
 WHERE m.type='m'
   AND m.permission='general_ledger'
   AND NOT EXISTS (SELECT 1
                    FROM permissions c
                   WHERE c.type = 'c'
                     AND c.permission = 'gltransactionheaders'
                     AND m.id = c.parent_id);

-- permission:	gltransactionheaders
-- title:		GL Journal Headers

UPDATE permissions a
   SET display = FALSE
 WHERE a.permission = '_new'
   AND parent_id = (SELECT id
                      FROM permissions c
                     WHERE c.permission = 'gltransactions'
                       AND c.type='c');

INSERT INTO permissions
(permission, type, title, display, parent_id, position)
SELECT '_new', 'a', 'New Journal Header', true, c.id, next.position
  FROM permissions c
     , (SELECT coalesce(max(a.position), 0)+1 as position
          FROM permissions a
          JOIN permissions c ON a.parent_id = c.id
                            AND c.type = 'c'
                            AND c.permission='gltransactionheaders'
         WHERE a.type = 'a') next
 WHERE c.type='c'
   AND c.permission='gltransactionheaders'
   AND NOT EXISTS (SELECT 1
                    FROM permissions a
                   WHERE a.type = 'a'
                     AND a.permission = '_new'
                     AND a.id = c.parent_id);

INSERT INTO permissions
(permission, type, title, display, parent_id, position)
SELECT 'delete', 'a', 'Delete Journal Header', false, c.id, next.position
  FROM permissions c
     , (SELECT coalesce(max(a.position), 0)+1 as position
          FROM permissions a
          JOIN permissions c ON a.parent_id = c.id
                            AND c.type = 'c'
                            AND c.permission='gltransactionheaders'
         WHERE a.type = 'a') next
 WHERE c.type='c'
   AND c.permission='gltransactionheaders'
   AND NOT EXISTS (SELECT 1
                    FROM permissions a
                   WHERE a.type = 'a'
                     AND a.permission = 'delete'
                     AND a.id = c.parent_id);
