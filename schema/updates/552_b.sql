ALTER TABLE account_statuses DROP CONSTRAINT account_statuses_usercompanyid_fkey;

ALTER TABLE activities DROP CONSTRAINT "$7";

ALTER TABLE activity_notes DROP CONSTRAINT activity_notes_usercompanyid_fkey;

ALTER TABLE activitytype DROP CONSTRAINT "$1";

ALTER TABLE calendar_events DROP CONSTRAINT calendar_events_usercompanyid_fkey;

ALTER TABLE campaigns DROP CONSTRAINT campaigns_usercompanyid_fkey;

ALTER TABLE campaignstatus DROP CONSTRAINT "$1";

ALTER TABLE campaigntype DROP CONSTRAINT "$1";

ALTER TABLE cb_accounts DROP CONSTRAINT cbaccount_usercompanyid_fkey;

ALTER TABLE company DROP CONSTRAINT company_usercompanyid_fkey;

ALTER TABLE company_classifications DROP CONSTRAINT company_classifications_usercompanyid_fkey;

ALTER TABLE company_crm DROP CONSTRAINT company_crm_usercompanyid_fkey;

ALTER TABLE company_industries DROP CONSTRAINT company_industries_usercompanyid_fkey;

ALTER TABLE company_ratings DROP CONSTRAINT company_ratings_usercompanyid_fkey;

ALTER TABLE company_sources DROP CONSTRAINT company_sources_usercompanyid_fkey;

ALTER TABLE company_statuses DROP CONSTRAINT company_statuses_usercompanyid_fkey;

ALTER TABLE company_types DROP CONSTRAINT company_types_usercompanyid_fkey;

ALTER TABLE companyparams DROP CONSTRAINT companyparams_usercompanyid_fkey;

ALTER TABLE companypermissions DROP CONSTRAINT companypermissions_usercompanyid_fkey;

ALTER TABLE contact_categories DROP CONSTRAINT "$1";

ALTER TABLE cs_failurecodes DROP CONSTRAINT cs_failurecodes_usercompanyid_fkey;

ALTER TABLE cumaster DROP CONSTRAINT cumaster_usercompanyid_fkey;

ALTER TABLE curate DROP CONSTRAINT curate_usercompanyid_fkey;

ALTER TABLE customer_type_discounts DROP CONSTRAINT customer_type_discounts_usercompanyid_fkey;

ALTER TABLE customer_types DROP CONSTRAINT customer_types_usercompanyid_fkey;

ALTER TABLE dynamic_section_criteria DROP CONSTRAINT dynamic_section_criteria_usercompanyid_fkey;

ALTER TABLE employees DROP CONSTRAINT employees_usercompanyid_fkey;

ALTER TABLE expenses DROP CONSTRAINT expenses_usercompanyid_fkey;

ALTER TABLE faq DROP CONSTRAINT faq_usercompanyid_fkey;

ALTER TABLE file DROP CONSTRAINT file_usercompanyid_fkey;

ALTER TABLE galleries DROP CONSTRAINT galleries_usercompanyid_fkey;

ALTER TABLE gl_account_centres DROP CONSTRAINT glaccountcentres_usercompanyid_fkey;

ALTER TABLE gl_accounts DROP CONSTRAINT glmaster_usercompanyid_fkey;

ALTER TABLE gl_analysis DROP CONSTRAINT glanalysis_usercompanyid_fkey;

ALTER TABLE gl_balances DROP CONSTRAINT glbalance_usercompanyid_fkey;

ALTER TABLE gl_budgets DROP CONSTRAINT glbudget_usercompanyid_fkey;

ALTER TABLE gl_centres DROP CONSTRAINT glcentre_usercompanyid_fkey;

ALTER TABLE gl_journals DROP CONSTRAINT glstandard_usercompanyid_fkey;

ALTER TABLE gl_params DROP CONSTRAINT glparams_usercompanyid_fkey;

ALTER TABLE gl_periods DROP CONSTRAINT glperiods_usercompanyid_fkey;

ALTER TABLE gl_summaries DROP CONSTRAINT glsummary_usercompanyid_fkey;

ALTER TABLE gl_transactions DROP CONSTRAINT gltransaction_usercompanyid_fkey;

ALTER TABLE holiday_entitlements DROP CONSTRAINT holiday_entitlements_usercompanyid_fkey;

ALTER TABLE holiday_extra_days DROP CONSTRAINT holiday_extra_days_usercompanyid_fkey;

ALTER TABLE holiday_requests DROP CONSTRAINT holiday_requests_usercompanyid_fkey;

ALTER TABLE hour_type_groups DROP CONSTRAINT hour_type_groups_usercompanyid_fkey;

ALTER TABLE hour_types DROP CONSTRAINT hour_types_usercompanyid_fkey;

ALTER TABLE hours DROP CONSTRAINT hours_usercompanyid_fkey;

ALTER TABLE intranet_config DROP CONSTRAINT intranet_config_usercompanyid_fkey;

ALTER TABLE intranet_layouts DROP CONSTRAINT intranet_layouts_usercompanyid_fkey;

ALTER TABLE intranet_page_types DROP CONSTRAINT intranet_page_types_usercompanyid_fkey;

ALTER TABLE intranet_pages DROP CONSTRAINT intranet_pages_usercompanyid_fkey;

ALTER TABLE intranet_postings DROP CONSTRAINT intranet_postings_usercompanyid_fkey;

ALTER TABLE intranet_sections DROP CONSTRAINT intranet_sections_usercompanyid_fkey;

ALTER TABLE logged_calls DROP CONSTRAINT logged_calls_usercompanyid_fkey;

ALTER TABLE mf_centres DROP CONSTRAINT mf_centres_usercompanyid_fkey;

ALTER TABLE mf_depts DROP CONSTRAINT mf_depts_usercompanyid_fkey;

ALTER TABLE mf_operations DROP CONSTRAINT mf_operations_usercompanyid_fkey;

ALTER TABLE mf_outside_ops DROP CONSTRAINT mf_outside_ops_usercompanyid_fkey;

ALTER TABLE mf_resources DROP CONSTRAINT mf_resources_usercompanyid_fkey;

ALTER TABLE mf_structures DROP CONSTRAINT mf_structures_usercompanyid_fkey;

ALTER TABLE mf_wo_structures DROP CONSTRAINT mf_wo_structures_usercompanyid_fkey;

ALTER TABLE mf_workorders DROP CONSTRAINT mf_workorders_usercompanyid_fkey;

ALTER TABLE news_items DROP CONSTRAINT news_items_usercompanyid_fkey;

ALTER TABLE newsletter_recipients DROP CONSTRAINT newsletter_recipients_usercompanyid_fkey;

ALTER TABLE newsletter_url_clicks DROP CONSTRAINT newsletter_url_clicks_usercompanyid_fkey;

ALTER TABLE newsletter_urls DROP CONSTRAINT newsletter_urls_usercompanyid_fkey;

ALTER TABLE newsletter_views DROP CONSTRAINT newsletter_views_usercompanyid_fkey;

ALTER TABLE newsletters DROP CONSTRAINT newsletters_usercompanyid_fkey;

ALTER TABLE opportunities DROP CONSTRAINT "$6";

ALTER TABLE opportunity_notes DROP CONSTRAINT opportunity_notes_usercompanyid_fkey;

ALTER TABLE opportunitysource DROP CONSTRAINT "$1";

ALTER TABLE opportunitystatus DROP CONSTRAINT "$1";

ALTER TABLE opportunitytype DROP CONSTRAINT opportunitytype_usercompanyid_fkey;

ALTER TABLE party ADD COLUMN usercompanyid int8;

UPDATE party
       SET usercompanyid=1;

ALTER TABLE party ALTER COLUMN usercompanyid SET NOT NULL;

ALTER TABLE party_contact_methods ADD COLUMN usercompanyid int8;

UPDATE party_contact_methods
       SET usercompanyid=1;

ALTER TABLE party_contact_methods ALTER COLUMN usercompanyid SET NOT NULL;

ALTER TABLE party_notes DROP CONSTRAINT party_notes_usercompanyid_fkey;

ALTER TABLE partyaddress ADD COLUMN usercompanyid int8;

UPDATE partyaddress
       SET usercompanyid=1;

ALTER TABLE partyaddress ALTER COLUMN usercompanyid SET NOT NULL;

ALTER TABLE person DROP CONSTRAINT person_usercompanyid_fkey;

ALTER TABLE pi_header DROP CONSTRAINT pi_header_usercompanyid_fkey;

ALTER TABLE pi_lines DROP CONSTRAINT pi_lines_usercompanyid_fkey;

ALTER TABLE plmaster DROP CONSTRAINT plmaster_usercompanyid_fkey;

ALTER TABLE pltransactions DROP CONSTRAINT pltransactions_usercompanyid_fkey;

ALTER TABLE po_auth_accounts DROP CONSTRAINT po_auth_accounts_usercompanyid_fkey;

ALTER TABLE po_auth_limits DROP CONSTRAINT po_auth_limits_usercompanyid_fkey;

ALTER TABLE po_header DROP CONSTRAINT po_header_usercompanyid_fkey;

ALTER TABLE po_lines DROP CONSTRAINT po_lines_usercompanyid_fkey;

ALTER TABLE po_product_lines DROP CONSTRAINT po_product_lines_usercompanyid_fkey;

ALTER TABLE po_receivedlines DROP CONSTRAINT po_receivedlines_usercompanyid_fkey;

ALTER TABLE poll_options DROP CONSTRAINT poll_options_usercompanyid_fkey;

ALTER TABLE poll_votes DROP CONSTRAINT poll_votes_usercompanyid_fkey;

ALTER TABLE polls DROP CONSTRAINT polls_usercompanyid_fkey;

ALTER TABLE product_bundles DROP CONSTRAINT product_bundles_usercompanyid_fkey;

ALTER TABLE project_categories DROP CONSTRAINT "$1";

ALTER TABLE project_equipment DROP CONSTRAINT project_equipment_usercompanyid_fkey;

ALTER TABLE project_issue_statuses DROP CONSTRAINT project_issue_statuses_usercompanyid_fkey;

ALTER TABLE project_issues DROP CONSTRAINT project_issues_usercompanyid_fkey;

ALTER TABLE project_notes DROP CONSTRAINT project_notes_usercompanyid_fkey;

ALTER TABLE project_work_types DROP CONSTRAINT project_work_types_companyid_fkey;

ALTER TABLE projects DROP CONSTRAINT "$2";

ALTER TABLE resource_templates DROP CONSTRAINT resource_templates_usercompanyid_fkey;

ALTER TABLE resource_types DROP CONSTRAINT resource_types_usercompanyid_fkey;

ALTER TABLE resources DROP CONSTRAINT "$2";

ALTER TABLE roles DROP CONSTRAINT roles_usercompanyid_fkey;

ALTER TABLE shipping_methods DROP CONSTRAINT shipping_methods_usercompanyid_fkey;

ALTER TABLE si_header DROP CONSTRAINT si_header_usercompanyid_fkey;

ALTER TABLE si_lines DROP CONSTRAINT si_lines_usercompanyid_fkey;

ALTER TABLE slmaster DROP CONSTRAINT slmaster_usercompanyid_fkey;

ALTER TABLE sltransactions DROP CONSTRAINT sltransactions_usercompanyid_fkey;

ALTER TABLE so_despatchlines DROP CONSTRAINT so_despatchlines_usercompanyid_fkey;

ALTER TABLE so_header DROP CONSTRAINT so_header_usercompanyid_fkey;

ALTER TABLE so_lines DROP CONSTRAINT so_lines_usercompanyid_fkey;

ALTER TABLE so_product_lines DROP CONSTRAINT so_product_lines_usercompanyid_fkey;

ALTER TABLE st_balances DROP CONSTRAINT st_balances_usercompanyid_fkey;

ALTER TABLE st_costs DROP CONSTRAINT st_costs_usercompanyid_fkey;

ALTER TABLE st_items DROP CONSTRAINT st_items_usercompanyid_fkey;

ALTER TABLE st_productgroups DROP CONSTRAINT st_productgroups_usercompanyid_fkey;

ALTER TABLE st_transactions DROP CONSTRAINT st_transactions_usercompanyid_fkey;

ALTER TABLE st_typecodes DROP CONSTRAINT st_typecodes_usercompanyid_fkey;

ALTER TABLE st_uom_conversions DROP CONSTRAINT st_uom_conversions_usercompanyid_fkey;

ALTER TABLE st_uoms DROP CONSTRAINT uoms_usercompanyid_fkey;

ALTER TABLE store_baskets DROP CONSTRAINT store_baskets_usercompanyid_fkey;

ALTER TABLE store_config DROP CONSTRAINT store_config_usercompanyid_fkey;

ALTER TABLE store_dynamic_sections DROP CONSTRAINT store_dynamic_sections_usercompanyid_fkey;

ALTER TABLE store_offer_codes DROP CONSTRAINT store_offer_codes_usercompanyid_fkey;

ALTER TABLE store_order_extras DROP CONSTRAINT store_order_extras_usercompanyid_fkey;

ALTER TABLE store_order_selected_extras DROP CONSTRAINT store_order_selected_extras_usercompanyid_fkey;

ALTER TABLE store_orders DROP CONSTRAINT store_order_companyid_fkey;

ALTER TABLE store_product_information_requests DROP CONSTRAINT store_product_information_requests_usercompanyid_fkey;

ALTER TABLE store_products DROP CONSTRAINT store_products_usercompanyid_fkey;

ALTER TABLE store_section_discounts DROP CONSTRAINT store_section_discounts_usercompanyid_fkey;

ALTER TABLE store_sections DROP CONSTRAINT store_section_companyid_fkey;

ALTER TABLE store_suppliers DROP CONSTRAINT store_suppliers_usercompanyid_fkey;

ALTER TABLE store_vouchers DROP CONSTRAINT store_vouchers_usercompanyid_fkey;

ALTER TABLE sy_uom_conversions DROP CONSTRAINT sy_uom_conversions_usercompanyid_fkey;

ALTER TABLE sypaytypes DROP CONSTRAINT sypaytypes_usercompanyid_fkey;

ALTER TABLE syterms DROP CONSTRAINT syterms_usercompanyid_fkey;

ALTER TABLE task_notes DROP CONSTRAINT task_notes_usercompanyid_fkey;

ALTER TABLE tax_statuses DROP CONSTRAINT tax_statuses_usercompanyid_fkey;

ALTER TABLE taxperiods DROP CONSTRAINT taxperiods_usercompanyid_fkey;

ALTER TABLE taxrates DROP CONSTRAINT taxrates_usercompanyid_fkey;

ALTER TABLE templates DROP CONSTRAINT templates_usercompanyid_fkey;

ALTER TABLE ticket_categories DROP CONSTRAINT ticket_categories_usercompanyid_fkey;

ALTER TABLE ticket_configurations DROP CONSTRAINT ticket_configurations_usercompanyid_fkey;

ALTER TABLE ticket_priorities DROP CONSTRAINT ticket_priorities_usercompanyid_fkey;

ALTER TABLE ticket_queues DROP CONSTRAINT ticket_queues_usercompanyid_fkey;

ALTER TABLE ticket_severities DROP CONSTRAINT ticket_severities_usercompanyid_fkey;

ALTER TABLE ticket_slas DROP CONSTRAINT usercompanyid_fkey;

ALTER TABLE ticket_statuses DROP CONSTRAINT ticket_statuses_usercompanyid_fkey;

ALTER TABLE tickets DROP CONSTRAINT tickets_usercompanyid_fkey;

ALTER TABLE userpreferences DROP CONSTRAINT userpreferences_usercompanyid_fkey;

ALTER TABLE websites DROP CONSTRAINT websites_usercompanyid_fkey;

ALTER TABLE wh_actions DROP CONSTRAINT wh_actions_usercompanyid_fkey;

ALTER TABLE wh_bins DROP CONSTRAINT wh_bins_usercompanyid_fkey;

ALTER TABLE wh_locations DROP CONSTRAINT wh_locations_usercompanyid_fkey;

ALTER TABLE wh_stores DROP CONSTRAINT wh_stores_usercompanyid_fkey;

ALTER TABLE wh_transfer_lines DROP CONSTRAINT wh_transfers_usercompanyid_fkey;

ALTER TABLE wh_transfer_rules DROP CONSTRAINT st_transfer_rules_usercompanyid_fkey;

ALTER TABLE wh_transfers DROP CONSTRAINT wh_transfers_usercompanyid_fkey;

