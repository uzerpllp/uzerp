--
-- $Revision: 1.1 $
--

-- Table:  companies

ALTER TABLE company ADD COLUMN date_inactive date;

-- Table:  slmaster

ALTER TABLE slmaster ADD COLUMN date_inactive date;

-- View: slmaster_overview

DROP VIEW validate.validate_sl;

DROP VIEW slmaster_overview;

CREATE OR REPLACE VIEW slmaster_overview AS 
 SELECT sl.*
 , p1.contact AS email_invoice
 , p2.contact AS email_statement
 , c.name, cu.currency
 , st.description AS payment_term
 , sy.name AS payment_type
 , sa.name AS sl_analysis
   FROM slmaster sl
   JOIN company c ON c.id = sl.company_id
   JOIN sypaytypes sy ON sy.id = sl.payment_type_id
   JOIN syterms st ON st.id = sl.payment_term_id
   JOIN cumaster cu ON cu.id = sl.currency_id
   LEFT JOIN sl_analysis sa ON sa.id = sl.sl_analysis_id
   LEFT JOIN partycontactmethodoverview p1 ON p1.id = sl.email_invoice_id
   LEFT JOIN partycontactmethodoverview p2 ON p2.id = sl.email_statement_id;

ALTER TABLE slmaster_overview OWNER TO "www-data";

CREATE OR REPLACE VIEW validate.validate_sl AS 
 SELECT slmaster_overview.name, slmaster_overview.currency, slmaster_overview.outstanding_balance - (( SELECT sum(sltransactionsoverview.gross_value) AS sum
           FROM sltransactionsoverview
          WHERE slmaster_overview.id = sltransactionsoverview.slmaster_id AND sltransactionsoverview.status::text = 'O'::text)) AS difference
   FROM slmaster_overview
  ORDER BY slmaster_overview.name;

ALTER TABLE validate.validate_sl OWNER TO "www-data";

-- Table:  plmaster

ALTER TABLE plmaster ADD COLUMN date_inactive date;

-- View: plmaster_overview

DROP VIEW validate.validate_pl;

DROP VIEW plmaster_overview;

CREATE OR REPLACE VIEW plmaster_overview AS 
 SELECT pl.*
 , c.name
 , sy.name AS payment_type
 , st.description AS payment_term
 , cu.currency
 , p1.contact AS email_order
 , p2.contact AS email_remittance
   FROM plmaster pl
   JOIN company c ON pl.company_id = c.id
   JOIN sypaytypes sy ON sy.id = pl.payment_type_id
   JOIN syterms st ON st.id = pl.payment_term_id
   JOIN cumaster cu ON cu.id = pl.currency_id
   LEFT JOIN partycontactmethodoverview p1 ON p1.id = pl.email_order_id
   LEFT JOIN partycontactmethodoverview p2 ON p2.id = pl.email_remittance_id;

ALTER TABLE plmaster_overview OWNER TO "www-data";

CREATE OR REPLACE VIEW validate.validate_pl AS 
 SELECT plmaster_overview.name, plmaster_overview.id, plmaster_overview.currency, plmaster_overview.outstanding_balance - (( SELECT sum(pltransactionsoverview.gross_value) AS sum
           FROM pltransactionsoverview
          WHERE plmaster_overview.id = pltransactionsoverview.plmaster_id AND pltransactionsoverview.status::text <> 'P'::text)) AS difference
   FROM plmaster_overview
  GROUP BY plmaster_overview.name, plmaster_overview.id, plmaster_overview.currency, plmaster_overview.outstanding_balance - (( SELECT sum(pltransactionsoverview.gross_value) AS sum
           FROM pltransactionsoverview
          WHERE plmaster_overview.id = pltransactionsoverview.plmaster_id AND pltransactionsoverview.status::text <> 'P'::text))
  ORDER BY plmaster_overview.name;

ALTER TABLE validate.validate_pl OWNER TO "www-data";
