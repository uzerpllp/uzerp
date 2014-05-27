--
-- $Revision: 1.2 $
--

DROP VIEW pi_headeroverview;

ALTER TABLE pi_header DROP COLUMN purchase_order_id;
ALTER TABLE pi_header DROP COLUMN purchase_order_number;

CREATE OR REPLACE VIEW pi_headeroverview AS 
 SELECT pi.*, plm.payee_name
 , c.name AS supplier, cum.currency, twc.currency AS twin, syt.description AS payment_terms
   FROM pi_header pi
   JOIN plmaster plm ON pi.plmaster_id = plm.id
   JOIN company c ON plm.company_id = c.id
   JOIN cumaster cum ON pi.currency_id = cum.id
   JOIN cumaster twc ON pi.twin_currency_id = twc.id
   JOIN syterms syt ON pi.payment_term_id = syt.id;

ALTER TABLE pi_headeroverview OWNER TO "www-data";
