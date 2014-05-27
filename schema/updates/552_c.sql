DROP VIEW user_company_accessoverview;

ALTER TABLE user_company_access
RENAME COLUMN company_id TO usercompanyid;

ALTER TABLE user_company_access DROP CONSTRAINT user_company_access_company_id_fkey;

UPDATE user_company_access
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

ALTER TABLE user_company_access
  ADD CONSTRAINT user_company_access_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE;

CREATE OR REPLACE VIEW user_company_accessoverview AS 
 SELECT u.username, u.usercompanyid, u.id, u.enabled, c.name AS company
   FROM user_company_access u
   LEFT JOIN system_companies s ON u.usercompanyid = s.id
   LEFT JOIN company c ON s.company_id = c.id;

UPDATE account_statuses
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE activities
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE activity_notes
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE activitytype
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE calendar_events
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE campaigns
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE campaignstatus
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE campaigntype
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE cb_accounts
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE company
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE company_classifications
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE company_crm
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE company_industries
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE company_ratings
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE company_sources
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE company_statuses
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE company_types
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE companyparams
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE companypermissions
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE contact_categories
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE cs_failurecodes
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE cumaster
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE curate
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE customer_type_discounts
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE customer_types
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE dynamic_section_criteria
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE employees
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE expenses
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE faq
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE file
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE galleries
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE gl_account_centres
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE gl_accounts
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE gl_analysis
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE gl_balances
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE gl_budgets
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE gl_centres
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE gl_journals
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE gl_params
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE gl_periods
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE gl_summaries
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE gl_transactions
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE holiday_entitlements
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE holiday_extra_days
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE holiday_requests
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE hour_type_groups
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE hour_types
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE hours
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE intranet_config
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE intranet_layouts
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE intranet_page_types
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE intranet_pages
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE intranet_postings
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE intranet_sections
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE logged_calls
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE mf_centres
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE mf_depts
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE mf_operations
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE mf_outside_ops
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE mf_resources
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE mf_structures
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE mf_wo_structures
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE mf_workorders
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE news_items
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE newsletter_recipients
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE newsletter_url_clicks
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE newsletter_urls
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE newsletter_views
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE newsletters
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE opportunities
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE opportunity_notes
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE opportunitysource
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE opportunitystatus
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE opportunitytype
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE party
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE party_contact_methods
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE party_notes
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE partyaddress
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE person
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE pi_header
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE pi_lines
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE plmaster
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE pltransactions
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE po_auth_accounts
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE po_auth_limits
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE po_header
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE po_lines
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE po_product_lines
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE po_receivedlines
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE poll_options
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE poll_votes
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE polls
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE product_bundles
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE project_categories
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE project_equipment
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE project_issue_statuses
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE project_issues
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE project_notes
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE project_work_types
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE projects
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE resource_templates
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE resource_types
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE resources
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE roles
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE shipping_methods
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE si_header
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE si_lines
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE slmaster
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE sltransactions
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE so_despatchlines
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE so_header
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE so_lines
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE so_product_lines
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE st_balances
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE st_costs
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE st_items
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE st_productgroups
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE st_transactions
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE st_typecodes
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE st_uom_conversions
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE st_uoms
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE store_baskets
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE store_config
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE store_dynamic_sections
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE store_offer_codes
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE store_order_extras
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE store_order_selected_extras
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE store_orders
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE store_product_information_requests
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE store_products
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE store_section_discounts
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE store_sections
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE store_suppliers
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE store_vouchers
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE sy_uom_conversions
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE sypaytypes
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE syterms
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE task_notes
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE tax_statuses
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE taxperiods
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE taxrates
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE templates
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE ticket_categories
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE ticket_configurations
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE ticket_priorities
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE ticket_queues
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE ticket_severities
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE ticket_slas
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE ticket_statuses
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE tickets
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE userpreferences
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE websites
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE wh_actions
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE wh_bins
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE wh_locations
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE wh_stores
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE wh_transfer_lines
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE wh_transfer_rules
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

UPDATE wh_transfers
   SET usercompanyid = (SELECT id
                          FROM system_companies
                         WHERE usercompanyid = company_id);

CREATE OR REPLACE VIEW useroverview AS 
 SELECT u.username, u."password", u.lastcompanylogin, u.person_id, u.last_login, u.audit_enabled, u.debug_enabled, u.access_enabled, uca.usercompanyid, c.name AS company, (p.firstname::text || ' '::text) || p.surname::text AS person
   FROM users u
   LEFT JOIN company c ON u.lastcompanylogin = c.id
   LEFT JOIN user_company_access uca ON u.username::text = uca.username::text
   LEFT JOIN person p ON u.person_id = p.id;

DROP VIEW hasrolesoverview;

CREATE OR REPLACE VIEW hasrolesoverview AS 
 SELECT hasrole.*, users."password", users.lastcompanylogin, users.person_id, roles.name as "role"
   FROM hasrole
   JOIN roles ON roles.id = hasrole.roleid
   JOIN users ON users.username::text = hasrole.username::text;

CREATE OR REPLACE VIEW companyroles AS 
 SELECT objectroles.id, objectroles.object_id AS company_id, objectroles.role_id, objectroles."read", objectroles."write"
   FROM objectroles
  WHERE objectroles.object_type::text = 'company'::text;

DROP VIEW companyrolesoverview;

CREATE OR REPLACE VIEW companyrolesoverview AS 
 SELECT cr.id, cr.object_id AS company_id, cr.role_id, cr."read", cr."write", c.name AS company, r.name AS "role"
   FROM objectroles cr
   JOIN company c ON cr.object_id = c.id AND cr.object_type::text = 'company'::text
   JOIN roles r ON cr.role_id = r.id;
