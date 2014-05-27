-- DROP TABLE uzlets;

CREATE TABLE uzlets
(
  id bigserial NOT NULL,
  "name" character varying NOT NULL,
  title character varying NOT NULL,
  preset boolean,
  enabled boolean,
  dashboard boolean,
  uses character varying,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT uzlets__pkey PRIMARY KEY (id),
  CONSTRAINT uzlets_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT uzlets_name_composite_key UNIQUE (name, usercompanyid)
);



-- DROP TABLE uzlet_modules;

CREATE TABLE uzlet_modules
(
  id bigserial NOT NULL,
  uzlet_id bigserial NOT NULL,
  module_id bigserial NOT NULL,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT uzlet_modules_id_pkey PRIMARY KEY (id),
  CONSTRAINT module_id_fkey FOREIGN KEY (module_id)
      REFERENCES modules (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT uzlet_id_fkey FOREIGN KEY (uzlet_id)
      REFERENCES uzlets (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT uzlet_modules_composite_key UNIQUE (uzlet_id, module_id, usercompanyid)
);



-- DROP TABLE uzlet_calls;

CREATE TABLE uzlet_calls
(
  id bigserial NOT NULL,
  uzlet_id bigserial NOT NULL,
  func character varying NOT NULL,
  arg character varying NOT NULL,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT uzlet_calls_id_pkey PRIMARY KEY (id),
  CONSTRAINT uzlet_id_fkey FOREIGN KEY (uzlet_id)
      REFERENCES uzlets (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);


-- INSERT DATA

INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'ContactsQuickLinks', 'quick_links', true, false, false, 'StaticContentEGlet', id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='ContactsQuickLinks'
	      and m.name='contacts';

INSERT INTO uzlet_calls (uzlet_id, func, arg, usercompanyid) 
		   select id, 'setTemplate', 'contacts_quick_links.tpl',usercompanyid
		     from uzlets
		    where name='ContactsQuickLinks';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'RecentlyViewedCompaniesEGlet', 'recently_viewed_companies', true, true, false, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='RecentlyViewedCompaniesEGlet'
	      and m.name='contacts';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'RecentlyViewedPeopleEGlet', 'recently_viewed_people', true, true, false, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='RecentlyViewedPeopleEGlet'
	      and m.name='contacts';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'RecentlyViewedLeadsEGlet', 'recently_viewed_leads', true, true, false, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='RecentlyViewedLeadsEGlet'
	      and m.name='contacts';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'RecentlyAddedCompaniesEGlet', 'recently_added_companies', true, true, false, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='RecentlyAddedCompaniesEGlet'
	      and m.name='contacts';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'RecentlyAddedLeadsEGlet', 'recently_added_leads', true, true, false, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='RecentlyAddedLeadsEGlet'
	      and m.name='contacts';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'CompaniesAddedTodayEGlet', 'companies_added_today', true, true, false, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='CompaniesAddedTodayEGlet'
	      and m.name='contacts';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'LeadsAddedTodayEGlet', 'leads_added_today', true, true, false, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='LeadsAddedTodayEGlet'
	      and m.name='contacts';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'OpportunitiesBySourceGrapher', 'opportunity_source_by_value', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='OpportunitiesBySourceGrapher'
	      and m.name='crm';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'OpportunitiesHistoryGrapher', 'opportunity_history', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='OpportunitiesHistoryGrapher'
	      and m.name='crm';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'CRMQuickLinks', 'quick_links', true, false, false, 'StaticContentEGlet', id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='CRMQuickLinks'
	      and m.name='crm';

INSERT INTO uzlet_calls (uzlet_id, func, arg, usercompanyid) 
		   select id, 'setTemplate', 'crm_quick_links.tpl',usercompanyid
		     from uzlets
		    where name='CRMQuickLinks';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'OpenOpportunitiesEGlet', 'my_open_opportunities', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='OpenOpportunitiesEGlet'
	      and m.name='crm';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'OpportunitiesWeeklyByStatusGrapher', 'my_pipeline_for_week', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='OpportunitiesWeeklyByStatusGrapher'
	      and m.name='crm';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'OpportunitiesMonthlyByStatusGrapher', 'my_pipeline_for_month', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='OpportunitiesMonthlyByStatusGrapher'
	      and m.name='crm';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'OpportunitiesQuarterlyByStatusGrapher', 'my_pipeline_for_quarter', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='OpportunitiesQuarterlyByStatusGrapher'
	      and m.name='crm';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'OpportunitiesYearlyByStatusGrapher', 'my_pipeline_for_year', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='OpportunitiesYearlyByStatusGrapher'
	      and m.name='crm';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'SalesTeamYearlySummaryEGlet', 'team_pipeline_for_year', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='SalesTeamYearlySummaryEGlet'
	      and m.name='crm';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'SalesTeamMonthlySummaryEGlet', 'team_pipeline_for_month', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='SalesTeamMonthlySummaryEGlet'
	      and m.name='crm';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'SalesTeamWeeklySummaryEGlet', 'team_pipeline_for_week', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='SalesTeamWeeklySummaryEGlet'
	      and m.name='crm';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'CurrentActivitiesEGlet', 'Current Activities', true, true, true, 'CurrentActivitiesEGlet', id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='CurrentActivitiesEGlet'
	      and m.name='crm';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'SystemAdminQuickLinks', 'quick_links', true, false, false, 'StaticContentEGlet', id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='SystemAdminQuickLinks'
	      and m.name='system_admin';

INSERT INTO uzlet_calls (uzlet_id, func, arg, usercompanyid) 
		   select id, 'setTemplate', 'system_admin_quick_links.tpl',usercompanyid
		     from uzlets
		    where name='SystemAdminQuickLinks';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'ProjectsQuickLinks', 'quick_links', true, false, false, 'StaticContentEGlet', id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='ProjectsQuickLinks'
	      and m.name='projects';

INSERT INTO uzlet_calls (uzlet_id, func, arg, usercompanyid) 
		   select id, 'setTemplate', 'project_quick_links.tpl',usercompanyid
		     from uzlets
		    where name='ProjectsQuickLinks';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'CurrentProjectsEGlet', 'my_current_projects', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='CurrentProjectsEGlet'
	      and m.name='projects';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'CurrentTasksEGlet', 'my_current_tasks', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='CurrentTasksEGlet'
	      and m.name='projects';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'LoggedHoursPerWeekEGlet', 'logged_hours_this_week', true, true, false, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='LoggedHoursPerWeekEGlet'
	      and m.name='projects';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'NewIssuesEGlet', 'new_issues', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='NewIssuesEGlet'
	      and m.name='projects';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'MyCurrentIssuesEGlet', 'current_issues', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='MyCurrentIssuesEGlet'
	      and m.name='projects';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'EquipmentUtilisationEGlet', 'equipment_utilisation', true, true, false, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='EquipmentUtilisationEGlet'
	      and m.name='projects';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'WebsiteQuickLinks', 'Quick Links', true, false, false, 'StaticContentEGlet', id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='WebsiteQuickLinks'
	      and m.name='websites';

INSERT INTO uzlet_calls (uzlet_id, func, arg, usercompanyid) 
		   select id, 'setTemplate', 'website_quick_links.tpl',usercompanyid
		     from uzlets
		    where name='WebsiteQuickLinks';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'MyWebsitesEGlet', 'my_websites', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='MyWebsitesEGlet'
	      and m.name='websites';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'EcommerceQuickLinks', 'Quick Links', true, false, false, 'StaticContentEGlet', id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='EcommerceQuickLinks'
	      and m.name='ecommerce';

INSERT INTO uzlet_calls (uzlet_id, func, arg, usercompanyid) 
		   select id, 'setTemplate', 'ecommerce_quick_links.tpl',usercompanyid
		     from uzlets
		    where name='EcommerceQuickLinks';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'CompanySelectorEGlet', 'company_selector', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='CompanySelectorEGlet'
	      and m.name='dashboard';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'HRQuickLinks', 'quick_links', true, false, false, 'StaticContentEGlet', id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='HRQuickLinks'
	      and m.name='hr';

INSERT INTO uzlet_calls (uzlet_id, func, arg, usercompanyid) 
		   select id, 'setTemplate', 'hr_quick_links.tpl',usercompanyid
		     from uzlets
		    where name='HRQuickLinks';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'ClientTicketQuickEntryEGlet', 'Quick Ticket Entry', true, false, true, 'ClientTicketQuickEntryEGlet', id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='ClientTicketQuickEntryEGlet'
	      and m.name='ticketing';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'TicketingQuickLinks', 'quick_links', true, false, false, 'StaticContentEGlet', id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='TicketingQuickLinks'
	      and m.name='ticketing';

INSERT INTO uzlet_calls (uzlet_id, func, arg, usercompanyid) 
		   select id, 'setTemplate', 'ticketing_quick_links.tpl',usercompanyid
		     from uzlets
		    where name='TicketingQuickLinks';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'TicketsWeeklyByStatusGrapher', 'ticket_statuses_for_current_week', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='TicketsWeeklyByStatusGrapher'
	      and m.name='ticketing';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'TicketsWeeklyByPriorityGrapher', 'current_ticket_priorities_for_current_week', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='TicketsWeeklyByPriorityGrapher'
	      and m.name='ticketing';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'TicketsWeeklyBySeverityGrapher', 'current_ticket_severities_for_current_week', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='TicketsWeeklyBySeverityGrapher'
	      and m.name='ticketing';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'MyTicketsEGlet', 'my_tickets', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='MyTicketsEGlet'
	      and m.name='ticketing';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'UnassignedTicketsEGlet', 'unassigned_tickets', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='UnassignedTicketsEGlet'
	      and m.name='ticketing';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'erp_quick_links', 'Accounts/ERP Quick Links', true, false, false, 'StaticContentEGlet', id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='erp_quick_links'
	      and m.name='erp';

INSERT INTO uzlet_calls (uzlet_id, func, arg, usercompanyid) 
		   select id, 'setTemplate', 'erp_quick_links.tpl',usercompanyid
		     from uzlets
		    where name='erp_quick_links';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'ar_quick_links', 'Asset Register Quick Links', true, false, false, 'StaticContentEGlet', id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='ar_quick_links'
	      and m.name='asset_register';

INSERT INTO uzlet_calls (uzlet_id, func, arg, usercompanyid) 
		   select id, 'setTemplate', 'ar_quick_links.tpl',usercompanyid
		     from uzlets
		    where name='ar_quick_links';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'cashbook_quick_links', 'Cashbook Quick Links', true, false, false, 'StaticContentEGlet', id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='cashbook_quick_links'
	      and m.name='cashbook';

INSERT INTO uzlet_calls (uzlet_id, func, arg, usercompanyid) 
		   select id, 'setTemplate', 'cashbook_quick_links.tpl',usercompanyid
		     from uzlets
		    where name='cashbook_quick_links';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'PPOverdueEGlet', 'overdue_periodic_payments', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='PPOverdueEGlet'
	      and m.name='cashbook';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'customer_service_quick_links', 'Customer Service Quick Links', true, false, false, 'StaticContentEGlet', id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='customer_service_quick_links'
	      and m.name='customer_service';

INSERT INTO uzlet_calls (uzlet_id, func, arg, usercompanyid) 
		   select id, 'setTemplate', 'customer_service_quick_links.tpl',usercompanyid
		     from uzlets
		    where name='customer_service_quick_links';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'CustomerServiceGrapher', 'customer_service_graph', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='CustomerServiceGrapher'
	      and m.name='customer_service';

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='CustomerServiceGrapher'
	      and m.name='sales_invoicing';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'despatch_quick_links', 'Goods Despatch Quick Links', true, false, false, 'StaticContentEGlet', id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='despatch_quick_links'
	      and m.name='despatch';

INSERT INTO uzlet_calls (uzlet_id, func, arg, usercompanyid) 
		   select id, 'setTemplate', 'despatch_quick_links.tpl',usercompanyid
		     from uzlets
		    where name='despatch_quick_links';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'gl_quick_links', 'General Ledger Quick Links', true, false, false, 'StaticContentEGlet', id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='gl_quick_links'
	      and m.name='general_ledger';

INSERT INTO uzlet_calls (uzlet_id, func, arg, usercompanyid) 
		   select id, 'setTemplate', 'gl_quick_links.tpl',usercompanyid
		     from uzlets
		    where name='gl_quick_links';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'gr_quick_links', 'Goods Received Quick Links', true, false, false, 'StaticContentEGlet', id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='gr_quick_links'
	      and m.name='goodsreceived';

INSERT INTO uzlet_calls (uzlet_id, func, arg, usercompanyid) 
		   select id, 'setTemplate', 'gr_quick_links.tpl',usercompanyid
		     from uzlets
		    where name='gr_quick_links';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'costing_quick_links', 'Costing Quick Links', true, false, false, 'StaticContentEGlet', id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='costing_quick_links'
	      and m.name='costing';

INSERT INTO uzlet_calls (uzlet_id, func, arg, usercompanyid) 
		   select id, 'setTemplate', 'costing_quick_links.tpl',usercompanyid
		     from uzlets
		    where name='costing_quick_links';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'MultiBinBalancesPrintEGlet', 'multi_bin_balances_print', true, true, true, 'MultiBinBalancesPrintEGlet', id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='MultiBinBalancesPrintEGlet'
	      and m.name='manufacturing';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'manufacturing_quick_links', 'Manufacturing Quick Links', true, false, false, 'StaticContentEGlet', id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='manufacturing_quick_links'
	      and m.name='manufacturing';

INSERT INTO uzlet_calls (uzlet_id, func, arg, usercompanyid) 
		   select id, 'setTemplate', 'manufacturing_quick_links.tpl',usercompanyid
		     from uzlets
		    where name='manufacturing_quick_links';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'WOrdersBookOverUnderNewEGlet', 'Book_Over/Under_Usage', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='WOrdersBookOverUnderNewEGlet'
	      and m.name='manufacturing';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'WOrdersBookProductionNewEGlet', 'Book_Production', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='WOrdersBookProductionNewEGlet'
	      and m.name='manufacturing';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'WOrdersPrintPaperworkNewEGlet', 'Print_Works_Order', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='WOrdersPrintPaperworkNewEGlet'
	      and m.name='manufacturing';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'WHActionsEGlet', 'Store_transfer_actions', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='WHActionsEGlet'
	      and m.name='manufacturing';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'WOrdersBackflushErrorsEGlet', 'backflush_errors', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='WOrdersBackflushErrorsEGlet'
	      and m.name='manufacturing';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'pi_quick_links', 'Purchase Invoicing Quick Links', true, false, false, 'StaticContentEGlet', id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='pi_quick_links'
	      and m.name='purchase_invoicing';

INSERT INTO uzlet_calls (uzlet_id, func, arg, usercompanyid) 
		   select id, 'setTemplate', 'pi_quick_links.tpl',usercompanyid
		     from uzlets
		    where name='pi_quick_links';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'POQueryInvoicesEGlet', 'po_invoices_in_query', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='POQueryInvoicesEGlet'
	      and m.name='purchase_invoicing';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'POoverdueInvoicesEGlet', 'po_overdue_invoices', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='POoverdueInvoicesEGlet'
	      and m.name='purchase_invoicing';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'pl_quick_links', 'Purchase Ledger Quick Links', true, false, false, 'StaticContentEGlet', id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='pl_quick_links'
	      and m.name='purchase_ledger';

INSERT INTO uzlet_calls (uzlet_id, func, arg, usercompanyid) 
		   select id, 'setTemplate', 'pl_quick_links.tpl',usercompanyid
		     from uzlets
		    where name='pl_quick_links';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'agedCreditorsSummaryEGlet', 'aged_creditors_summary', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='agedCreditorsSummaryEGlet'
	      and m.name='purchase_ledger';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'po_quick_links', 'Purchase Order Quick Links', true, false, false, 'StaticContentEGlet', id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='po_quick_links'
	      and m.name='purchase_order';

INSERT INTO uzlet_calls (uzlet_id, func, arg, usercompanyid) 
		   select id, 'setTemplate', 'po_quick_links.tpl',usercompanyid
		     from uzlets
		    where name='po_quick_links';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'POrdersDueTodayEGlet', 'Purchase_Orders_due_today', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='POrdersDueTodayEGlet'
	      and m.name='purchase_order';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'POrdersOverdueEGlet', 'Overdue_Purchase_Orders', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='POrdersOverdueEGlet'
	      and m.name='purchase_order';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'POrdersNotAcknowledgedEGlet', 'Purchase_Orders_not_acknowledged', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='POrdersNotAcknowledgedEGlet'
	      and m.name='purchase_order';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'POrdersReceivedValueEGlet', 'Purchases_by_value_received', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='POrdersReceivedValueEGlet'
	      and m.name='purchase_order';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'POrdersAuthRequisitionEGlet', 'Orders_awaiting_Authorisation', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='POrdersAuthRequisitionEGlet'
	      and m.name='purchase_order';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'POrdersNoAuthUserEGlet', 'Orders_with_no_Authorisor', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='POrdersNoAuthUserEGlet'
	      and m.name='purchase_order';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'si_quick_links', 'Sales Invoicing Quick Links', true, false, false, 'StaticContentEGlet', id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='si_quick_links'
	      and m.name='sales_invoicing';

INSERT INTO uzlet_calls (uzlet_id, func, arg, usercompanyid) 
		   select id, 'setTemplate', 'si_quick_links.tpl',usercompanyid
		     from uzlets
		    where name='si_quick_links';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'TopSalesInvoicesEGlet', 'top_10_sales_invoices_for_month', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='TopSalesInvoicesEGlet'
	      and m.name='sales_invoicing';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'SalesHistoryGrapher', 'sales_history', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='SalesHistoryGrapher'
	      and m.name='sales_invoicing';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'sl_quick_links', 'Sales Ledger Quick Links', true, false, false, 'StaticContentEGlet', id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='sl_quick_links'
	      and m.name='sales_ledger';

INSERT INTO uzlet_calls (uzlet_id, func, arg, usercompanyid) 
		   select id, 'setTemplate', 'sl_quick_links.tpl',usercompanyid
		     from uzlets
		    where name='sl_quick_links';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'AccountsOnStopEGlet', 'accounts_on_stop', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='AccountsOnStopEGlet'
	      and m.name='sales_ledger';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'OverDueAccountsEGlet', 'overdue_accounts', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='OverDueAccountsEGlet'
	      and m.name='sales_ledger';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'OverCreditLimitEGlet', 'accounts_over_credit_limit', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='OverCreditLimitEGlet'
	      and m.name='sales_ledger';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'agedDebtorsSummaryEGlet', 'aged_debtors_summary', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='agedDebtorsSummaryEGlet'
	      and m.name='sales_ledger';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'so_quick_links', 'Sales Order Quick Links', true, false, false, 'StaticContentEGlet', id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='so_quick_links'
	      and m.name='sales_order';

INSERT INTO uzlet_calls (uzlet_id, func, arg, usercompanyid) 
		   select id, 'setTemplate', 'so_quick_links.tpl',usercompanyid
		     from uzlets
		    where name='so_quick_links';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'SOrdersOverdueEGlet', 'overdue_sales_orders', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='SOrdersOverdueEGlet'
	      and m.name='sales_order';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'TopSalesOrdersEGlet', 'top_10_sales_orders_for_month', true, true, true, NULL, id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='TopSalesOrdersEGlet'
	      and m.name='sales_order';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'sales_orders_summary', 'sales_orders_summary', true, true, true, 'SalesOrdersSummaryEGlet', id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='sales_orders_summary'
	      and m.name='sales_order';

INSERT INTO uzlet_calls (uzlet_id, func, arg, usercompanyid) 
		   select id, 'setParameters', '{"params":{"type":"O"}}',usercompanyid
		     from uzlets
		    where name='sales_orders_summary';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'sales_quotes_summary_eglet', 'sales_quotes_summary', true, true, true, 'SalesOrdersSummaryEGlet', id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='sales_quotes_summary_eglet'
	      and m.name='sales_order';

INSERT INTO uzlet_calls (uzlet_id, func, arg, usercompanyid) 
		   select id, 'setParameters', '{"params":{"type":"Q"}}',usercompanyid
		     from uzlets
		    where name='sales_quotes_summary_eglet';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'sales_orders_item_summary', 'sales_orders_item_summary', true, true, true, 'SOrdersItemSummaryEGlet', id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='sales_orders_item_summary'
	      and m.name='sales_order';

INSERT INTO uzlet_calls (uzlet_id, func, arg, usercompanyid) 
		   select id, 'setParameters', '{"params":{"type":"O"}}',usercompanyid
		     from uzlets
		    where name='sales_orders_item_summary';





INSERT INTO uzlets ( name, title, preset, enabled, dashboard, uses, usercompanyid) 
   select 'vat_quick_links', 'Vat Links', true, false, false, 'StaticContentEGlet', id
     from system_companies;

INSERT INTO uzlet_modules ( uzlet_id, module_id, usercompanyid) 
	   select u.id, m.id, u.usercompanyid
	     from uzlets u
	        , modules m
	    where u.name='vat_quick_links'
	      and m.name='vat';

INSERT INTO uzlet_calls (uzlet_id, func, arg, usercompanyid) 
		   select id, 'setTemplate', 'vat_quick_links.tpl',usercompanyid
		     from uzlets
		    where name='vat_quick_links';
