--
-- $Revision: 1.1 $
--

-- View: pl_allocation_details_overview

-- DROP VIEW pl_allocation_details_overview;

CREATE OR REPLACE VIEW pl_allocation_details_overview AS 
 SELECT pad.id, pad.allocation_id, pad.transaction_id, pad.payment_value, pad.payment_id, plt.transaction_date
 , plt.transaction_type, plt.status, plt.our_reference, plt.ext_reference, plt.currency_id, plt.rate, plt.gross_value
 , plt.tax_value, plt.net_value, plt.twin_currency_id, plt.twin_rate, plt.twin_tax_value, plt.twin_net_value
 , plt.twin_gross_value, plt.base_tax_value, plt.base_gross_value, plt.base_net_value, plt.payment_term_id, plt.due_date
 , plt.cross_ref, plt.os_value, plt.twin_os_value, plt.base_os_value, plt.description, plt.usercompanyid, plt.plmaster_id
 , plt.created, plt.createdby, plt.alteredby, plt.lastupdated, plt.original_due_date, plt.for_payment, plt.payee_name
 , plt.supplier, plt.payment_type_id, plt.company_id, plt.sort_code, plt.account_number, plt.currency, plt.twin
 , plt.payment_terms, plt.payment_type, plt.email_order, plt.email_remittance, plt.remittance_advice, pad.created as allocation_date
   FROM pl_allocation_details pad
   JOIN pltransactionsoverview plt ON plt.id = pad.transaction_id;

ALTER TABLE pl_allocation_details_overview OWNER TO "www-data";

-- View: sl_allocation_details_overview

-- DROP VIEW sl_allocation_details_overview;

CREATE OR REPLACE VIEW sl_allocation_details_overview AS 
 SELECT sad.id, slt.transaction_date, slt.transaction_type, slt.status, slt.our_reference, slt.ext_reference
 , slt.currency_id, slt.rate, slt.gross_value, slt.tax_value, slt.net_value, slt.twin_currency, slt.twin_rate
 , slt.twin_gross_value, slt.twin_tax_value, slt.twin_net_value, slt.base_gross_value, slt.base_tax_value
 , slt.base_net_value, slt.payment_term_id, slt.due_date, slt.cross_ref, slt.os_value, slt.twin_os_value, slt.base_os_value
 , slt.description, slt.usercompanyid, slt.slmaster_id, slt.customer, slt.currency, slt.twin, slt.payment_terms
 , sad.allocation_id, sad.transaction_id, sad.payment_value, sad.created as allocation_date
   FROM sl_allocation_details sad
   JOIN sltransactionsoverview slt ON slt.id = sad.transaction_id;

ALTER TABLE sl_allocation_details_overview OWNER TO "www-data";
