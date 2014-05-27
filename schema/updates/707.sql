ALTER TABLE account_statuses ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE account_statuses ADD COLUMN createdby character varying;
ALTER TABLE account_statuses ADD COLUMN alteredby character varying;
ALTER TABLE account_statuses ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE address ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE address ADD COLUMN createdby character varying;
ALTER TABLE address ADD COLUMN alteredby character varying;
ALTER TABLE address ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE ar_analysis ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE ar_analysis ADD COLUMN createdby character varying;
ALTER TABLE ar_analysis ADD COLUMN alteredby character varying;
ALTER TABLE ar_analysis ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE ar_groups ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE ar_groups ADD COLUMN createdby character varying;
ALTER TABLE ar_groups ADD COLUMN alteredby character varying;
ALTER TABLE ar_groups ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE ar_locations ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE ar_locations ADD COLUMN createdby character varying;
ALTER TABLE ar_locations ADD COLUMN alteredby character varying;
ALTER TABLE ar_locations ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE ar_master ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE ar_master ADD COLUMN createdby character varying;
ALTER TABLE ar_master ADD COLUMN alteredby character varying;
ALTER TABLE ar_master ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE ar_transactions ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE ar_transactions ADD COLUMN createdby character varying;
ALTER TABLE ar_transactions ADD COLUMN alteredby character varying;
ALTER TABLE ar_transactions ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE cb_accounts ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE cb_accounts ADD COLUMN createdby character varying;
ALTER TABLE cb_accounts ADD COLUMN alteredby character varying;
ALTER TABLE cb_accounts ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE cb_transactions ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE cb_transactions ADD COLUMN createdby character varying;
ALTER TABLE cb_transactions ADD COLUMN alteredby character varying;
ALTER TABLE cb_transactions ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE companies_in_categories ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE companies_in_categories ADD COLUMN createdby character varying;
ALTER TABLE companies_in_categories ADD COLUMN alteredby character varying;
ALTER TABLE companies_in_categories ADD COLUMN lastupdated timestamp DEFAULT now();
ALTER TABLE company ADD COLUMN createdby character varying;
 
ALTER TABLE company_classifications ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE company_classifications ADD COLUMN createdby character varying;
ALTER TABLE company_classifications ADD COLUMN alteredby character varying;
ALTER TABLE company_classifications ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE company_industries ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE company_industries ADD COLUMN createdby character varying;
ALTER TABLE company_industries ADD COLUMN alteredby character varying;
ALTER TABLE company_industries ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE companyparams ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE companyparams ADD COLUMN createdby character varying;
ALTER TABLE companyparams ADD COLUMN alteredby character varying;
ALTER TABLE companyparams ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE companypermissions ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE companypermissions ADD COLUMN createdby character varying;
ALTER TABLE companypermissions ADD COLUMN alteredby character varying;
ALTER TABLE companypermissions ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE company_ratings ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE company_ratings ADD COLUMN createdby character varying;
ALTER TABLE company_ratings ADD COLUMN alteredby character varying;
ALTER TABLE company_ratings ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE company_slas ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE company_slas ADD COLUMN createdby character varying;
ALTER TABLE company_slas ADD COLUMN alteredby character varying;
ALTER TABLE company_slas ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE company_sources ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE company_sources ADD COLUMN createdby character varying;
ALTER TABLE company_sources ADD COLUMN alteredby character varying;
ALTER TABLE company_sources ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE company_statuses ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE company_statuses ADD COLUMN createdby character varying;
ALTER TABLE company_statuses ADD COLUMN alteredby character varying;
ALTER TABLE company_statuses ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE company_types ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE company_types ADD COLUMN createdby character varying;
ALTER TABLE company_types ADD COLUMN alteredby character varying;
ALTER TABLE company_types ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE contact_categories ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE contact_categories ADD COLUMN createdby character varying;
ALTER TABLE contact_categories ADD COLUMN alteredby character varying;
ALTER TABLE contact_categories ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE contact_methods ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE contact_methods ADD COLUMN createdby character varying;
ALTER TABLE contact_methods ADD COLUMN alteredby character varying;
ALTER TABLE contact_methods ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE countries ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE countries ADD COLUMN createdby character varying;
ALTER TABLE countries ADD COLUMN alteredby character varying;
ALTER TABLE countries ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE cs_failurecodes ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE cs_failurecodes ADD COLUMN createdby character varying;
ALTER TABLE cs_failurecodes ADD COLUMN alteredby character varying;
ALTER TABLE cs_failurecodes ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE cumaster ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE cumaster ADD COLUMN createdby character varying;
ALTER TABLE cumaster ADD COLUMN alteredby character varying;
ALTER TABLE cumaster ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE curate ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE curate ADD COLUMN createdby character varying;
ALTER TABLE curate ADD COLUMN alteredby character varying;
ALTER TABLE curate ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE gl_account_centres ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE gl_account_centres ADD COLUMN createdby character varying;
ALTER TABLE gl_account_centres ADD COLUMN alteredby character varying;
ALTER TABLE gl_account_centres ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE gl_accounts ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE gl_accounts ADD COLUMN createdby character varying;
ALTER TABLE gl_accounts ADD COLUMN alteredby character varying;
ALTER TABLE gl_accounts ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE gl_analysis ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE gl_analysis ADD COLUMN createdby character varying;
ALTER TABLE gl_analysis ADD COLUMN alteredby character varying;
ALTER TABLE gl_analysis ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE gl_balances ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE gl_balances ADD COLUMN createdby character varying;
ALTER TABLE gl_balances ADD COLUMN alteredby character varying;
ALTER TABLE gl_balances ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE gl_budgets ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE gl_budgets ADD COLUMN createdby character varying;
ALTER TABLE gl_budgets ADD COLUMN alteredby character varying;
ALTER TABLE gl_budgets ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE gl_centres ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE gl_centres ADD COLUMN createdby character varying;
ALTER TABLE gl_centres ADD COLUMN alteredby character varying;
ALTER TABLE gl_centres ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE gl_journals ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE gl_journals ADD COLUMN createdby character varying;
ALTER TABLE gl_journals ADD COLUMN alteredby character varying;
ALTER TABLE gl_journals ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE gl_params ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE gl_params ADD COLUMN createdby character varying;
ALTER TABLE gl_params ADD COLUMN alteredby character varying;
ALTER TABLE gl_params ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE gl_periods ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE gl_periods ADD COLUMN createdby character varying;
ALTER TABLE gl_periods ADD COLUMN alteredby character varying;
ALTER TABLE gl_periods ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE gl_summaries ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE gl_summaries ADD COLUMN createdby character varying;
ALTER TABLE gl_summaries ADD COLUMN alteredby character varying;
ALTER TABLE gl_summaries ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE gl_transactions ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE gl_transactions ADD COLUMN createdby character varying;
ALTER TABLE gl_transactions ADD COLUMN alteredby character varying;
ALTER TABLE gl_transactions ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE mf_centres ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE mf_centres ADD COLUMN createdby character varying;
ALTER TABLE mf_centres ADD COLUMN alteredby character varying;
ALTER TABLE mf_centres ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE mf_data_sheets ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE mf_data_sheets ADD COLUMN createdby character varying;
ALTER TABLE mf_data_sheets ADD COLUMN alteredby character varying;
ALTER TABLE mf_data_sheets ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE mf_depts ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE mf_depts ADD COLUMN createdby character varying;
ALTER TABLE mf_depts ADD COLUMN alteredby character varying;
ALTER TABLE mf_depts ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE mf_operations ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE mf_operations ADD COLUMN createdby character varying;
ALTER TABLE mf_operations ADD COLUMN alteredby character varying;
ALTER TABLE mf_operations ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE mf_outside_ops ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE mf_outside_ops ADD COLUMN createdby character varying;
 
ALTER TABLE mf_resources ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE mf_resources ADD COLUMN createdby character varying;
ALTER TABLE mf_resources ADD COLUMN alteredby character varying;
ALTER TABLE mf_resources ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE mf_structures ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE mf_structures ADD COLUMN createdby character varying;
 
ALTER TABLE mf_workorders ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE mf_workorders ADD COLUMN createdby character varying;
 
ALTER TABLE mf_wo_structures ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE mf_wo_structures ADD COLUMN createdby character varying;
ALTER TABLE mf_wo_structures ADD COLUMN alteredby character varying;
ALTER TABLE mf_wo_structures ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE objectroles ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE objectroles ADD COLUMN createdby character varying;
ALTER TABLE objectroles ADD COLUMN alteredby character varying;
ALTER TABLE objectroles ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE party ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE party ADD COLUMN createdby character varying;
ALTER TABLE party ADD COLUMN alteredby character varying;
ALTER TABLE party ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE partyaddress ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE partyaddress ADD COLUMN createdby character varying;
ALTER TABLE partyaddress ADD COLUMN alteredby character varying;
ALTER TABLE partyaddress ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE party_contact_methods ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE party_contact_methods ADD COLUMN createdby character varying;
ALTER TABLE party_contact_methods ADD COLUMN alteredby character varying;
ALTER TABLE party_contact_methods ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE periodic_payments ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE periodic_payments ADD COLUMN createdby character varying;
ALTER TABLE periodic_payments ADD COLUMN alteredby character varying;
ALTER TABLE periodic_payments ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE pi_header ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE pi_header ADD COLUMN createdby character varying;
ALTER TABLE pi_header ADD COLUMN alteredby character varying;
ALTER TABLE pi_header ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE pi_lines ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE pi_lines ADD COLUMN createdby character varying;
ALTER TABLE pi_lines ADD COLUMN alteredby character varying;
ALTER TABLE pi_lines ADD COLUMN lastupdated timestamp DEFAULT now();

ALTER TABLE plmaster ADD COLUMN createdby character varying;
ALTER TABLE plmaster ADD COLUMN alteredby character varying;
ALTER TABLE plmaster ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE po_auth_accounts ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE po_auth_accounts ADD COLUMN createdby character varying;
ALTER TABLE po_auth_accounts ADD COLUMN alteredby character varying;
ALTER TABLE po_auth_accounts ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE po_auth_limits ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE po_auth_limits ADD COLUMN createdby character varying;
ALTER TABLE po_auth_limits ADD COLUMN alteredby character varying;
ALTER TABLE po_auth_limits ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE po_awaiting_auth ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE po_awaiting_auth ADD COLUMN createdby character varying;
ALTER TABLE po_awaiting_auth ADD COLUMN alteredby character varying;
ALTER TABLE po_awaiting_auth ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE po_lines ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE po_lines ADD COLUMN createdby character varying;
ALTER TABLE po_lines ADD COLUMN alteredby character varying;
ALTER TABLE po_lines ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE po_product_lines ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE po_product_lines ADD COLUMN createdby character varying;
ALTER TABLE po_product_lines ADD COLUMN alteredby character varying;
ALTER TABLE po_product_lines ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE po_receivedlines ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE po_receivedlines ADD COLUMN createdby character varying;
ALTER TABLE po_receivedlines ADD COLUMN alteredby character varying;
ALTER TABLE po_receivedlines ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE qc_complaint_codes ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE qc_complaint_codes ADD COLUMN createdby character varying;
ALTER TABLE qc_complaint_codes ADD COLUMN alteredby character varying;
ALTER TABLE qc_complaint_codes ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE qc_complaints ADD COLUMN createdby character varying;
 
ALTER TABLE qc_complaint_type ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE qc_complaint_type ADD COLUMN createdby character varying;
ALTER TABLE qc_complaint_type ADD COLUMN alteredby character varying;
ALTER TABLE qc_complaint_type ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE qc_supplementary_complaint_codes ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE qc_supplementary_complaint_codes ADD COLUMN createdby character varying;
ALTER TABLE qc_supplementary_complaint_codes ADD COLUMN alteredby character varying;
ALTER TABLE qc_supplementary_complaint_codes ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE qc_volume ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE qc_volume ADD COLUMN createdby character varying;
ALTER TABLE qc_volume ADD COLUMN alteredby character varying;
ALTER TABLE qc_volume ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE si_header ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE si_header ADD COLUMN createdby character varying;
ALTER TABLE si_header ADD COLUMN alteredby character varying;
ALTER TABLE si_header ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE si_lines ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE si_lines ADD COLUMN createdby character varying;
ALTER TABLE si_lines ADD COLUMN alteredby character varying;
ALTER TABLE si_lines ADD COLUMN lastupdated timestamp DEFAULT now();

ALTER TABLE slmaster ADD COLUMN createdby character varying;
ALTER TABLE slmaster ADD COLUMN alteredby character varying;
ALTER TABLE slmaster ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE so_despatchlines ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE so_despatchlines ADD COLUMN createdby character varying;
ALTER TABLE so_despatchlines ADD COLUMN alteredby character varying;
ALTER TABLE so_despatchlines ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE so_header ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE so_header ADD COLUMN createdby character varying;
ALTER TABLE so_header ADD COLUMN alteredby character varying;
ALTER TABLE so_header ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE so_lines ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE so_lines ADD COLUMN createdby character varying;
ALTER TABLE so_lines ADD COLUMN alteredby character varying;
ALTER TABLE so_lines ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE so_product_lines ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE so_product_lines ADD COLUMN createdby character varying;
ALTER TABLE so_product_lines ADD COLUMN alteredby character varying;
ALTER TABLE so_product_lines ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE st_balances ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE st_balances ADD COLUMN createdby character varying;
ALTER TABLE st_balances ADD COLUMN alteredby character varying;
ALTER TABLE st_balances ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE st_costs ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE st_costs ADD COLUMN createdby character varying;
 
ALTER TABLE st_items ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE st_items ADD COLUMN createdby character varying;
ALTER TABLE st_items ADD COLUMN alteredby character varying;
ALTER TABLE st_items ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE st_productgroups ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE st_productgroups ADD COLUMN createdby character varying;
ALTER TABLE st_productgroups ADD COLUMN alteredby character varying;
ALTER TABLE st_productgroups ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE st_transactions ADD COLUMN createdby character varying;
 
ALTER TABLE st_typecodes ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE st_typecodes ADD COLUMN createdby character varying;
ALTER TABLE st_typecodes ADD COLUMN alteredby character varying;
ALTER TABLE st_typecodes ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE st_uom_conversions ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE st_uom_conversions ADD COLUMN createdby character varying;
ALTER TABLE st_uom_conversions ADD COLUMN alteredby character varying;
ALTER TABLE st_uom_conversions ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE st_uoms ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE st_uoms ADD COLUMN createdby character varying;
ALTER TABLE st_uoms ADD COLUMN alteredby character varying;
ALTER TABLE st_uoms ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE sypaytypes ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE sypaytypes ADD COLUMN createdby character varying;
ALTER TABLE sypaytypes ADD COLUMN alteredby character varying;
ALTER TABLE sypaytypes ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE syterms ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE syterms ADD COLUMN createdby character varying;
ALTER TABLE syterms ADD COLUMN alteredby character varying;
ALTER TABLE syterms ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE sy_uom_conversions ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE sy_uom_conversions ADD COLUMN createdby character varying;
ALTER TABLE sy_uom_conversions ADD COLUMN alteredby character varying;
ALTER TABLE sy_uom_conversions ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE taxperiods ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE taxperiods ADD COLUMN createdby character varying;
ALTER TABLE taxperiods ADD COLUMN alteredby character varying;
ALTER TABLE taxperiods ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE taxrates ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE taxrates ADD COLUMN createdby character varying;
ALTER TABLE taxrates ADD COLUMN alteredby character varying;
 
ALTER TABLE wh_actions ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE wh_actions ADD COLUMN createdby character varying;
ALTER TABLE wh_actions ADD COLUMN alteredby character varying;
ALTER TABLE wh_actions ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE wh_bins ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE wh_bins ADD COLUMN createdby character varying;
ALTER TABLE wh_bins ADD COLUMN alteredby character varying;
ALTER TABLE wh_bins ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE wh_locations ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE wh_locations ADD COLUMN createdby character varying;
ALTER TABLE wh_locations ADD COLUMN alteredby character varying;
ALTER TABLE wh_locations ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE wh_stores ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE wh_stores ADD COLUMN createdby character varying;
ALTER TABLE wh_stores ADD COLUMN alteredby character varying;
ALTER TABLE wh_stores ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE wh_transfer_lines ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE wh_transfer_lines ADD COLUMN createdby character varying;
ALTER TABLE wh_transfer_lines ADD COLUMN alteredby character varying;
ALTER TABLE wh_transfer_lines ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE wh_transfer_rules ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE wh_transfer_rules ADD COLUMN createdby character varying;
ALTER TABLE wh_transfer_rules ADD COLUMN alteredby character varying;
ALTER TABLE wh_transfer_rules ADD COLUMN lastupdated timestamp DEFAULT now();
 
ALTER TABLE wh_transfers ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE wh_transfers ADD COLUMN createdby character varying;
ALTER TABLE wh_transfers ADD COLUMN alteredby character varying;
ALTER TABLE wh_transfers ADD COLUMN lastupdated timestamp DEFAULT now();
 
------------------------------
-- check if created is null --
------------------------------

UPDATE account_statuses SET created = '2009-01-01' WHERE  created is null;
UPDATE address SET created = '2009-01-01' WHERE  created is null;
UPDATE ar_analysis SET created = '2009-01-01' WHERE  created is null;
UPDATE ar_groups SET created = '2009-01-01' WHERE  created is null;
UPDATE ar_locations SET created = '2009-01-01' WHERE  created is null;
UPDATE ar_master SET created = '2009-01-01' WHERE  created is null;
UPDATE ar_transactions SET created = '2009-01-01' WHERE  created is null;
UPDATE cb_accounts SET created = '2009-01-01' WHERE  created is null;
UPDATE cb_transactions SET created = '2009-01-01' WHERE  created is null;
UPDATE companies_in_categories SET created = '2009-01-01' WHERE  created is null;
UPDATE company_classifications SET created = '2009-01-01' WHERE  created is null;
UPDATE company_industries SET created = '2009-01-01' WHERE  created is null;
UPDATE companyparams SET created = '2009-01-01' WHERE  created is null;
UPDATE companypermissions SET created = '2009-01-01' WHERE  created is null;
UPDATE company_ratings SET created = '2009-01-01' WHERE  created is null;
UPDATE company_slas SET created = '2009-01-01' WHERE  created is null;
UPDATE company_sources SET created = '2009-01-01' WHERE  created is null;
UPDATE company_statuses SET created = '2009-01-01' WHERE  created is null;
UPDATE company_types SET created = '2009-01-01' WHERE  created is null;
UPDATE contact_categories SET created = '2009-01-01' WHERE  created is null;
UPDATE contact_methods SET created = '2009-01-01' WHERE  created is null;
UPDATE countries SET created = '2009-01-01' WHERE  created is null;
UPDATE cs_failurecodes SET created = '2009-01-01' WHERE  created is null;
UPDATE cumaster SET created = '2009-01-01' WHERE  created is null;
UPDATE curate SET created = '2009-01-01' WHERE  created is null;
UPDATE gl_account_centres SET created = '2009-01-01' WHERE  created is null;
UPDATE gl_accounts SET created = '2009-01-01' WHERE  created is null;
UPDATE gl_analysis SET created = '2009-01-01' WHERE  created is null;
UPDATE gl_balances SET created = '2009-01-01' WHERE  created is null;
UPDATE gl_budgets SET created = '2009-01-01' WHERE  created is null;
UPDATE gl_centres SET created = '2009-01-01' WHERE  created is null;
UPDATE gl_journals SET created = '2009-01-01' WHERE  created is null;
UPDATE gl_params SET created = '2009-01-01' WHERE  created is null;
UPDATE gl_periods SET created = '2009-01-01' WHERE  created is null;
UPDATE gl_summaries SET created = '2009-01-01' WHERE  created is null;
UPDATE gl_transactions SET created = '2009-01-01' WHERE  created is null;
UPDATE mf_centres SET created = '2009-01-01' WHERE  created is null;
UPDATE mf_data_sheets SET created = '2009-01-01' WHERE  created is null;
UPDATE mf_depts SET created = '2009-01-01' WHERE  created is null;
UPDATE mf_operations SET created = '2009-01-01' WHERE  created is null;
UPDATE mf_outside_ops SET created = '2009-01-01' WHERE  created is null;
UPDATE mf_resources SET created = '2009-01-01' WHERE  created is null;
UPDATE mf_structures SET created = '2009-01-01' WHERE  created is null;
UPDATE mf_workorders SET created = '2009-01-01' WHERE  created is null;
UPDATE mf_wo_structures SET created = '2009-01-01' WHERE  created is null;
UPDATE objectroles SET created = '2009-01-01' WHERE  created is null;
UPDATE party SET created = '2009-01-01' WHERE  created is null;
UPDATE partyaddress SET created = '2009-01-01' WHERE  created is null;
UPDATE party_contact_methods SET created = '2009-01-01' WHERE  created is null;
UPDATE party_notes SET created = '2009-01-01' WHERE  created is null;
UPDATE periodic_payments SET created = '2009-01-01' WHERE  created is null;
UPDATE pi_header SET created = '2009-01-01' WHERE  created is null;
UPDATE pi_lines SET created = '2009-01-01' WHERE  created is null;
UPDATE pltransactions SET created = '2009-01-01' WHERE  created is null;
UPDATE po_auth_accounts SET created = '2009-01-01' WHERE  created is null;
UPDATE po_auth_limits SET created = '2009-01-01' WHERE  created is null;
UPDATE po_awaiting_auth SET created = '2009-01-01' WHERE  created is null;
UPDATE po_lines SET created = '2009-01-01' WHERE  created is null;
UPDATE po_product_lines SET created = '2009-01-01' WHERE  created is null;
UPDATE po_receivedlines SET created = '2009-01-01' WHERE  created is null;
UPDATE qc_complaint_codes SET created = '2009-01-01' WHERE  created is null;
UPDATE qc_complaint_type SET created = '2009-01-01' WHERE  created is null;
UPDATE qc_supplementary_complaint_codes SET created = '2009-01-01' WHERE  created is null;
UPDATE qc_volume SET created = '2009-01-01' WHERE  created is null;
UPDATE si_header SET created = '2009-01-01' WHERE  created is null;
UPDATE si_lines SET created = '2009-01-01' WHERE  created is null;
UPDATE so_despatchlines SET created = '2009-01-01' WHERE  created is null;
UPDATE so_header SET created = '2009-01-01' WHERE  created is null;
UPDATE so_lines SET created = '2009-01-01' WHERE  created is null;
UPDATE so_product_lines SET created = '2009-01-01' WHERE  created is null;
UPDATE st_balances SET created = '2009-01-01' WHERE  created is null;
UPDATE st_costs SET created = '2009-01-01' WHERE  created is null;
UPDATE st_items SET created = '2009-01-01' WHERE  created is null;
UPDATE st_productgroups SET created = '2009-01-01' WHERE  created is null;
UPDATE st_typecodes SET created = '2009-01-01' WHERE  created is null;
UPDATE st_uom_conversions SET created = '2009-01-01' WHERE  created is null;
UPDATE st_uoms SET created = '2009-01-01' WHERE  created is null;
UPDATE sypaytypes SET created = '2009-01-01' WHERE  created is null;
UPDATE syterms SET created = '2009-01-01' WHERE  created is null;
UPDATE sy_uom_conversions SET created = '2009-01-01' WHERE  created is null;
UPDATE taxperiods SET created = '2009-01-01' WHERE  created is null;
UPDATE taxrates SET created = '2009-01-01' WHERE  created is null;
UPDATE wh_actions SET created = '2009-01-01' WHERE  created is null;
UPDATE wh_bins SET created = '2009-01-01' WHERE  created is null;
UPDATE wh_locations SET created = '2009-01-01' WHERE  created is null;
UPDATE wh_stores SET created = '2009-01-01' WHERE  created is null;
UPDATE wh_transfer_lines SET created = '2009-01-01' WHERE  created is null;
UPDATE wh_transfer_rules SET created = '2009-01-01' WHERE  created is null;
UPDATE wh_transfers SET created = '2009-01-01' WHERE  created is null;