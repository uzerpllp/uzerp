--
-- $Revision: 1.3 $
--

-- Security/access policies

DROP TABLE IF EXISTS sys_policy_control_lists;

DROP TABLE IF EXISTS sys_policy_access_lists;

DROP TABLE IF EXISTS sys_object_policies;

-- Table: sys_object_policies

CREATE TABLE sys_object_policies
(
  id bigserial NOT NULL,
  "name" character varying NOT NULL,
  module_components_id bigint NOT NULL,
  fieldname character varying NOT NULL,
  "operator" character varying NOT NULL,
  "value" character varying NOT NULL,
  is_id_field boolean NOT NULL DEFAULT FALSE,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT sys_object_policies_pkey PRIMARY KEY (id),
  CONSTRAINT sys_object_policies_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT sys_object_policies_module_components_id_fkey FOREIGN KEY (module_components_id)
      REFERENCES module_components (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE

);

ALTER TABLE sys_object_policies OWNER TO "www-data";

-- Table: sys_policy_access_lists

CREATE TABLE sys_policy_access_lists
(
  id bigserial NOT NULL,
  access_type character varying NOT NULL,
  access_object_id bigint NOT NULL,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT sys_policy_access_lists_pkey PRIMARY KEY (id),
  CONSTRAINT sys_policy_access_lists_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

ALTER TABLE sys_policy_access_lists OWNER TO "www-data";

-- Table: sys_policy_control_lists

CREATE TABLE sys_policy_control_lists
(
  id bigserial NOT NULL,
  object_policies_id bigint NOT NULL,
  access_lists_id bigint NOT NULL,
  "type" character varying NOT NULL,
  allowed boolean NOT NULL DEFAULT true,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT sys_policy_control_lists_pkey PRIMARY KEY (id),
  CONSTRAINT sys_policy_control_lists_object_policies_fkey FOREIGN KEY (object_policies_id)
      REFERENCES sys_object_policies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT sys_policy_control_lists_access_lists_id_fkey FOREIGN KEY (access_lists_id)
      REFERENCES sys_policy_access_lists (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT sys_policy_control_lists_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

ALTER TABLE sys_policy_control_lists OWNER TO "www-data";

-- View: sys_object_policies_overview

CREATE OR REPLACE VIEW sys_object_policies_overview AS 
 SELECT op.*
      , mc.name AS module_component
   FROM sys_object_policies op
   JOIN module_components mc ON mc.id = op.module_components_id;

ALTER TABLE sys_object_policies_overview OWNER TO "www-data";

-- View: sys_policy_access_lists_overview

CREATE OR REPLACE VIEW sys_policy_access_lists_overview AS 
         SELECT pal.*
              , r.name
           FROM sys_policy_access_lists pal
      JOIN roles r ON pal.access_object_id = r.id
     WHERE pal.access_type::text = 'Role'::text
UNION 
         SELECT pal.*
              , p.permission AS name
           FROM sys_policy_access_lists pal
      JOIN permissions p ON pal.access_object_id = p.id
     WHERE pal.access_type::text = 'Permission'::text;

ALTER TABLE sys_policy_access_lists_overview OWNER TO "www-data";

-- View: sys_policy_control_lists_overview

CREATE OR REPLACE VIEW sys_policy_control_lists_overview AS 
 SELECT pcl.*
      , op.name AS policy
      , pal.access_type, pal.name
   FROM sys_policy_control_lists pcl
   JOIN sys_object_policies op ON op.id = pcl.object_policies_id
   JOIN sys_policy_access_lists_overview pal ON pal.id = pcl.access_lists_id;

ALTER TABLE sys_policy_control_lists_overview OWNER TO "www-data";

-- View: sys_object_access_control_list

CREATE OR REPLACE VIEW sys_object_access_control_list AS 
 SELECT op.*
      , pcl.type, pcl.allowed
      , pal.access_type, pal.access_object_id, pal.name AS permission
      , mc.name AS module_component
   FROM sys_object_policies op
   JOIN sys_policy_control_lists pcl ON pcl.object_policies_id = op.id
   JOIN sys_policy_access_lists_overview pal ON pal.id = pcl.access_lists_id
   JOIN module_components mc ON mc.id = op.module_components_id;

ALTER TABLE sys_object_access_control_list OWNER TO "www-data";

--
-- Modules/Components
--

INSERT INTO module_components
 (name, "type", location, module_id)
 SELECT 'systemobjectpolicyscontroller', 'C', location||'/controllers/SystemobjectpolicysController.php', id
   FROM modules m
  WHERE name = 'system_admin';

INSERT INTO module_components
 (name, "type", location, module_id)
 SELECT 'systemobjectpolicy', 'M', location||'/models/SystemObjectPolicy.php', id
   FROM modules m
  WHERE name = 'system_admin';

INSERT INTO module_components
 (name, "type", location, module_id)
 SELECT 'systemobjectpolicycollection', 'M', location||'/models/SystemObjectPolicyCollection.php', id
   FROM modules m
  WHERE name = 'system_admin';

INSERT INTO module_components
 (name, "type", location, module_id)
 SELECT 'systempolicyaccesslistscontroller', 'C', location||'/controllers/SystempolicyaccesslistsController.php', id
   FROM modules m
  WHERE name = 'system_admin';

INSERT INTO module_components
 (name, "type", location, module_id)
 SELECT 'systempolicyaccesslist', 'M', location||'/models/SystemPolicyAccessList.php', id
   FROM modules m
  WHERE name = 'system_admin';

INSERT INTO module_components
 (name, "type", location, module_id)
 SELECT 'systempolicyaccesslistcollection', 'M', location||'/models/SystemPolicyAccessListCollection.php', id
   FROM modules m
  WHERE name = 'system_admin';

INSERT INTO module_components
 (name, "type", location, module_id)
 SELECT 'systempolicycontrollistscontroller', 'C', location||'/controllers/SystempolicycontrollistsController.php', id
   FROM modules m
  WHERE name = 'system_admin';

INSERT INTO module_components
 (name, "type", location, module_id)
 SELECT 'systempolicycontrollist', 'M', location||'/models/SystemPolicyControlList.php', id
   FROM modules m
  WHERE name = 'system_admin';

INSERT INTO module_components
 (name, "type", location, module_id)
 SELECT 'systempolicycontrollistcollection', 'M', location||'/models/SystemPolicyControlListCollection.php', id
   FROM modules m
  WHERE name = 'system_admin';

--
-- Permissions
--

INSERT INTO permissions
 (permission, type, title, display, parent_id, position, module_id, component_id)
SELECT 'systemobjectpolicys', 'c', 'System Policies', true, per.id, pos.position, mod.module_id, mod.id
  FROM permissions per
    , (SELECT max(c.position)+1 as position
         FROM permissions c
            , permissions p
        WHERE p.type='m'
          AND p.permission='system_admin'
          AND p.id=c.parent_id) pos
   , (SELECT module_id, id
        FROM module_components mc
       WHERE mc.name = 'systemobjectpolicyscontroller') mod
 WHERE type='m'
   AND permission='system_admin';

INSERT INTO permissions
 (permission, type, title, display, parent_id, position, module_id, component_id)
 SELECT 'systempolicyaccesslists', 'c', 'System Policy Access Lists', true, per.id, pos.position, mod.module_id, mod.id
  FROM permissions per
    , (SELECT max(c.position)+1 as position
         FROM permissions c
            , permissions p
        WHERE p.type='m'
          AND p.permission='system_admin'
          AND p.id=c.parent_id) pos
   , (SELECT module_id, id
        FROM module_components mc
       WHERE mc.name = 'systempolicyaccesslistscontroller') mod
 WHERE type='m'
   AND permission='system_admin';

DELETE FROM module_components
 WHERE name = 'xml_swf_chart';

DELETE FROM module_components
 WHERE location like '%ecommerce%';

DELETE FROM module_components
 WHERE location like '%website%';

DELETE FROM module_components
 WHERE location like '%style.css';

ALTER TABLE module_components ADD COLUMN title character varying;
ALTER TABLE module_components ADD COLUMN description character varying;

update module_components
   set title = 'Purchase Order Authorisation Limits'
 where name = 'poauthlimitscontroller'
   and location = 'modules/public_pages/erp/order/purchase_order/controllers/PoauthlimitsController.php'
   and type = 'C';
update module_components
   set title = 'Purchase Orders'
 where name = 'porderscontroller'
   and location = 'modules/public_pages/erp/order/purchase_order/controllers/PordersController.php'
   and type = 'C';
update module_components
   set title = 'view data definition'
 where name = 'viewdatadefinition'
   and location = 'modules/public_pages/edi/templates/datadefinitiondetails/viewdatadefinition.tpl'
   and type = 'T';
update module_components
   set title = 'Opportunity Hours'
 where name = 'hourscontroller'
   and location = 'modules/public_pages/shared/controllers/HoursController.php'
   and type = 'C';
update module_components
   set title = 'Logged Calls'
 where name = 'loggedcallscontroller'
   and location = 'modules/public_pages/shared/controllers/LoggedcallsController.php'
   and type = 'C';
update module_components
   set title = 'Accounts'
 where name = 'companyscontroller'
   and location = 'modules/public_pages/contacts/controllers/CompanysController.php'
   and type = 'C';
update module_components
   set title = 'Tickets'
 where name = 'ticketscontroller'
   and location = 'modules/public_pages/ticketing/controllers/TicketsController.php'
   and type = 'C';
update module_components
   set title = 'Bank Accounts'
 where name = 'bankaccountscontroller'
   and location = 'modules/public_pages/erp/cashbook/controllers/BankaccountsController.php'
   and type = 'C';
update module_components
   set title = 'Ledger Setup Currencies'
 where name = 'currencyscontroller'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/controllers/CurrencysController.php'
   and type = 'C';
update module_components
   set title = 'EDI Data Mappings'
 where name = 'datamappingscontroller'
   and location = 'modules/public_pages/edi/controllers/DatamappingsController.php'
   and type = 'C';
update module_components
   set title = 'EDI Data Mapping Rules'
 where name = 'datamappingrulescontroller'
   and location = 'modules/public_pages/edi/controllers/DatamappingrulesController.php'
   and type = 'C';
update module_components
   set title = 'EDI External Systems'
 where name = 'externalsystemscontroller'
   and location = 'modules/public_pages/edi/controllers/ExternalsystemsController.php'
   and type = 'C';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/asset_register/asset_setup/templates/aranalysiss/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/asset_register/asset_setup/templates/aranalysiss/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/asset_register/asset_setup/templates/argroups/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/asset_register/asset_setup/templates/argroups/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/asset_register/asset_setup/templates/arlocations/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/asset_register/asset_setup/templates/arlocations/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/asset_register/asset_setup/templates/assets/new.tpl'
   and type = 'T';
update module_components
   set title = 'General Ledger Setup Analysis Codes'
 where name = 'glanalysisscontroller'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/controllers/GlanalysissController.php'
   and type = 'C';
update module_components
   set title = 'General Ledger Setup Budgets'
 where name = 'glbudgetscontroller'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/controllers/GlbudgetsController.php'
   and type = 'C';
update module_components
   set title = 'General Ledger Setup Centres'
 where name = 'glcentrescontroller'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/controllers/GlcentresController.php'
   and type = 'C';
update module_components
   set title = 'General Ledger Setup Parameters'
 where name = 'glparamsscontroller'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/controllers/GlparamssController.php'
   and type = 'C';
update module_components
   set title = 'General Ledger Setup Summaries'
 where name = 'glsummaryscontroller'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/controllers/GlsummarysController.php'
   and type = 'C';
update module_components
   set title = 'General Ledger Setup Periods'
 where name = 'glperiodscontroller'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/controllers/GlperiodsController.php'
   and type = 'C';
update module_components
   set title = 'Ledger Setup Payment Terms'
 where name = 'paymenttermscontroller'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/controllers/PaymenttermsController.php'
   and type = 'C';
update module_components
   set title = 'Ledger Setup Payment Types'
 where name = 'paymenttypescontroller'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/controllers/PaymenttypesController.php'
   and type = 'C';
update module_components
   set title = 'People'
 where name = 'personscontroller'
   and location = 'modules/public_pages/contacts/controllers/PersonsController.php'
   and type = 'C';
update module_components
   set title = 'Purchase Order Product Lines'
 where name = 'poproductlinescontroller'
   and location = 'modules/public_pages/erp/order/purchase_order/controllers/PoproductlinesController.php'
   and type = 'C';
update module_components
   set title = 'General Ledger Setup'
 where name = 'setupcontroller'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/controllers/SetupController.php'
   and type = 'C';
update module_components
   set title = 'Contacts Setup'
 where name = 'setupcontroller'
   and location = 'modules/public_pages/contacts/controllers/SetupController.php'
   and type = 'C';
update module_components
   set title = 'General Ledger Tax Rates'
 where name = 'taxratescontroller'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/controllers/TaxratesController.php'
   and type = 'C';
update module_components
   set title = 'Sales Order Price Types'
 where name = 'sopricetypescontroller'
   and location = 'modules/public_pages/erp/order/sales_order/controllers/SopricetypesController.php'
   and type = 'C';
update module_components
   set title = 'Sales Product Selector Link'
 where name = 'soproductselectorscontroller'
   and location = 'modules/public_pages/erp/order/sales_order/controllers/SoproductselectorsController.php'
   and type = 'C';
update module_components
   set title = 'Sales Order Product Lines'
 where name = 'soproductlinescontroller'
   and location = 'modules/public_pages/erp/order/sales_order/controllers/SoproductlinesController.php'
   and type = 'C';
update module_components
   set title = 'General Ledger Tax Statuses'
 where name = 'taxstatusscontroller'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/controllers/TaxstatussController.php'
   and type = 'C';
update module_components
   set title = 'Engineering Work Schedules'
 where name = 'workschedulescontroller'
   and location = 'modules/public_pages/engineering/controllers/WorkschedulesController.php'
   and type = 'C';
update module_components
   set title = 'Sales Order Products'
 where name = 'soproductlineheaderscontroller'
   and location = 'modules/public_pages/erp/order/sales_order/controllers/SoproductlineheadersController.php'
   and type = 'C';
update module_components
   set title = 'Sales Invoices'
 where name = 'sinvoicescontroller'
   and location = 'modules/public_pages/erp/invoicing/sales_invoicing/controllers/SinvoicesController.php'
   and type = 'C';
update module_components
   set title = 'Activity Attachments'
 where name = 'activityattachmentscontroller'
   and location = 'modules/public_pages/crm/controllers/ActivityattachmentsController.php'
   and type = 'C';
update module_components
   set title = 'Opportunities'
 where name = 'opportunityscontroller'
   and location = 'modules/public_pages/crm/controllers/OpportunitysController.php'
   and type = 'C';
update module_components
   set title = 'Activities'
 where name = 'activityscontroller'
   and location = 'modules/public_pages/crm/controllers/ActivitysController.php'
   and type = 'C';
update module_components
   set title = 'Activity Notes'
 where name = 'activitynotescontroller'
   and location = 'modules/public_pages/crm/controllers/ActivitynotesController.php'
   and type = 'C';
update module_components
   set title = 'Opportunity Attachments'
 where name = 'opportunityattachmentscontroller'
   and location = 'modules/public_pages/crm/controllers/OpportunityattachmentsController.php'
   and type = 'C';
update module_components
   set title = 'Opportunity Notes'
 where name = 'opportunitynotescontroller'
   and location = 'modules/public_pages/crm/controllers/OpportunitynotesController.php'
   and type = 'C';
update module_components
   set title = 'Campaigns'
 where name = 'campaignscontroller'
   and location = 'modules/public_pages/crm/controllers/CampaignsController.php'
   and type = 'C';
update module_components
   set title = 'Customer Service'
 where name = 'customerservicescontroller'
   and location = 'modules/public_pages/erp/customer_service/controllers/CustomerservicesController.php'
   and type = 'C';
update module_components
   set title = 'My Details'
 where name = 'detailscontroller'
   and location = 'modules/public_pages/dashboard/controllers/DetailsController.php'
   and type = 'C';
update module_components
   set title = 'My Data'
 where name = 'mydatacontroller'
   and location = 'modules/public_pages/dashboard/controllers/MydataController.php'
   and type = 'C';
update module_components
   set title = 'Report Definitions'
 where name = 'reportdefinitionscontroller'
   and location = 'modules/public_pages/output/output_setup/controllers/ReportDefinitionsController.php'
   and type = 'C';
update module_components
   set title = 'Goods Received'
 where name = 'poreceivedlinescontroller'
   and location = 'modules/public_pages/erp/goodsreceived/controllers/PoreceivedlinesController.php'
   and type = 'C';
update module_components
   set title = 'Delivery and Despatch'
 where name = 'sodespatcheventscontroller'
   and location = 'modules/public_pages/erp/despatch/controllers/SodespatcheventsController.php'
   and type = 'C';
update module_components
   set title = 'Ticket Release Versions'
 where name = 'ticketreleaseversionscontroller'
   and location = 'modules/public_pages/ticketing/controllers/TicketreleaseversionsController.php'
   and type = 'C';
update module_components
   set title = 'Equipment'
 where name = 'equipmentcontroller'
   and location = 'modules/public_pages/projects/controllers/EquipmentController.php'
   and type = 'C';
update module_components
   set title = 'Projects'
 where name = 'projectscontroller'
   and location = 'modules/public_pages/projects/controllers/ProjectsController.php'
   and type = 'C';
update module_components
   set title = 'Resources'
 where name = 'resourcetemplatecontroller'
   and location = 'modules/public_pages/projects/controllers/ResourcetemplateController.php'
   and type = 'C';
update module_components
   set title = 'Type Codes'
 where name = 'sttypecodescontroller'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/controllers/SttypecodesController.php'
   and type = 'C';
update module_components
   set title = 'Purchase Order Product header'
 where name = 'poproductlineheader'
   and location = 'modules/public_pages/erp/order/purchase_order/models/POProductlineHeader.php'
   and type = 'M';
update module_components
   set title = 'Customer Service Failure Codes'
 where name = 'csfailurecodescontroller'
   and location = 'modules/public_pages/erp/customer_service/controllers/CsfailurecodesController.php'
   and type = 'C';
update module_components
   set title = 'Manufacturing Setup Centres'
 where name = 'mfcentrescontroller'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/controllers/MfcentresController.php'
   and type = 'C';
update module_components
   set title = 'Cash Book Periodic Payments'
 where name = 'periodicpaymentscontroller'
   and location = 'modules/public_pages/erp/cashbook/controllers/PeriodicpaymentsController.php'
   and type = 'C';
update module_components
   set title = 'My Preferences'
 where name = 'preferencescontroller'
   and location = 'modules/public_pages/dashboard/controllers/PreferencesController.php'
   and type = 'C';
update module_components
   set title = 'Projects Setup'
 where name = 'setupcontroller'
   and location = 'modules/public_pages/projects/controllers/SetupController.php'
   and type = 'C';
update module_components
   set title = 'CRM Setup'
 where name = 'setupcontroller'
   and location = 'modules/public_pages/crm/controllers/SetupController.php'
   and type = 'C';
update module_components
   set title = 'Despatch Notes'
 where name = 'sodespatchlinescontroller'
   and location = 'modules/public_pages/erp/despatch/controllers/SodespatchlinesController.php'
   and type = 'C';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/edi/templates/externalsystems/new.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/edi/templates/externalsystems/view.tpl'
   and type = 'T';
update module_components
   set title = 'Ticket Status'
 where name = 'ticketstatus'
   and location = 'modules/public_pages/ticketing/models/TicketStatus.php'
   and type = 'M';
update module_components
   set title = 'Ticket Search'
 where name = 'ticketssearch'
   and location = 'modules/public_pages/ticketing/models/TicketsSearch.php'
   and type = 'M';
update module_components
   set title = 'Ticket Statuses'
 where name = 'ticketstatuscollection'
   and location = 'modules/public_pages/ticketing/models/TicketStatusCollection.php'
   and type = 'M';
update module_components
   set title = 'Standard Costing Cost Changes'
 where name = 'stcostscontroller'
   and location = 'modules/public_pages/erp/costing/controllers/StcostsController.php'
   and type = 'C';
update module_components
   set title = 'Project Tasks'
 where name = 'taskscontroller'
   and location = 'modules/public_pages/projects/controllers/TasksController.php'
   and type = 'C';
update module_components
   set title = 'Despatch Warehouse Transfers'
 where name = 'whtransferscontroller'
   and location = 'modules/public_pages/erp/despatch/controllers/WhtransfersController.php'
   and type = 'C';
update module_components
   set title = 'Engineering Work Schedule Notes'
 where name = 'workschedulenotescontroller'
   and location = 'modules/public_pages/engineering/controllers/WorkschedulenotesController.php'
   and type = 'C';
update module_components
   set title = 'EDI Data Mapping Search'
 where name = 'datamappingssearch'
   and location = 'modules/public_pages/edi/models/datamappingsSearch.php'
   and type = 'M';
update module_components
   set title = 'System Uom Conversions'
 where name = 'syuomconversionscontroller'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/controllers/SyuomconversionsController.php'
   and type = 'C';
update module_components
   set title = 'Actions'
 where name = 'whactionscontroller'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/controllers/WhactionsController.php'
   and type = 'C';
update module_components
   set title = 'Uoms'
 where name = 'stuomscontroller'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/controllers/StuomsController.php'
   and type = 'C';
update module_components
   set title = 'Employees'
 where name = 'employeescontroller'
   and location = 'modules/public_pages/hr/controllers/EmployeesController.php'
   and type = 'C';
update module_components
   set title = 'Holiday Entitlements'
 where name = 'holidayentitlementscontroller'
   and location = 'modules/public_pages/hr/controllers/HolidayentitlementsController.php'
   and type = 'C';
update module_components
   set title = 'Transfer Rules'
 where name = 'whtransferrulescontroller'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/controllers/WhtransferrulesController.php'
   and type = 'C';
update module_components
   set title = 'Product Groups'
 where name = 'stproductgroupscontroller'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/controllers/StproductgroupsController.php'
   and type = 'C';
update module_components
   set title = 'VAT'
 where name = 'vat'
   and location = 'modules/public_pages/erp/ledger/vat/models/Vat.php'
   and type = 'M';
update module_components
   set title = 'Report Parts'
 where name = 'reportpartscontroller'
   and location = 'modules/public_pages/output/output_setup/controllers/ReportPartsController.php'
   and type = 'C';
update module_components
   set title = 'Leads'
 where name = 'leadscontroller'
   and location = 'modules/public_pages/contacts/controllers/LeadsController.php'
   and type = 'C';
update module_components
   set title = 'Party Address'
 where name = 'partyaddressscontroller'
   and location = 'modules/public_pages/contacts/controllers/PartyaddresssController.php'
   and type = 'C';
update module_components
   set title = 'Holiday Extra Days'
 where name = 'holidayextradayscontroller'
   and location = 'modules/public_pages/hr/controllers/HolidayextradaysController.php'
   and type = 'C';
update module_components
   set title = 'Asset Register Assets'
 where name = 'assetscontroller'
   and location = 'modules/public_pages/erp/asset_register/controllers/AssetsController.php'
   and type = 'C';
update module_components
   set title = 'Roles'
 where name = 'rolescontroller'
   and location = 'modules/public_pages/admin/controllers/RolesController.php'
   and type = 'C';
update module_components
   set title = 'Users'
 where name = 'userscontroller'
   and location = 'modules/public_pages/admin/controllers/UsersController.php'
   and type = 'C';
update module_components
   set title = 'Stock Items'
 where name = 'stitemscontroller'
   and location = 'modules/public_pages/erp/manufacturing/controllers/StitemsController.php'
   and type = 'C';
update module_components
   set title = 'Stock Transactions'
 where name = 'sttransactionscontroller'
   and location = 'modules/public_pages/erp/manufacturing/controllers/SttransactionsController.php'
   and type = 'C';
update module_components
   set title = 'Stock Uom Conversions'
 where name = 'stuomconversionscontroller'
   and location = 'modules/public_pages/erp/manufacturing/controllers/StuomconversionsController.php'
   and type = 'C';
update module_components
   set title = 'Engineering Work Schedule'
 where name = 'workschedule'
   and location = 'modules/public_pages/engineering/models/WorkSchedule.php'
   and type = 'M';
update module_components
   set title = 'uzLETs'
 where name = 'uzletscontroller'
   and location = 'modules/public_pages/uzlets/uzlet_setup/controllers/UzletsController.php'
   and type = 'C';
update module_components
   set title = 'Holiday Requests'
 where name = 'holidayrequestscontroller'
   and location = 'modules/public_pages/hr/controllers/HolidayrequestsController.php'
   and type = 'C';
update module_components
   set title = 'HR Expenses'
 where name = 'expensescontroller'
   and location = 'modules/public_pages/hr/controllers/ExpensesController.php'
   and type = 'C';
update module_components
   set title = 'Manufacturing Setup Departments'
 where name = 'mfdeptscontroller'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/controllers/MfdeptsController.php'
   and type = 'C';
update module_components
   set title = 'Product Recording Downtime Codes'
 where name = 'mfdowntimecodescontroller'
   and location = 'modules/public_pages/erp/production_recording/controllers/MfdowntimecodesController.php'
   and type = 'C';
update module_components
   set title = 'Production Recording Shifts'
 where name = 'mfshiftscontroller'
   and location = 'modules/public_pages/erp/production_recording/controllers/MfshiftsController.php'
   and type = 'C';
update module_components
   set title = 'Manufacturing Stock Structures'
 where name = 'mfstructurescontroller'
   and location = 'modules/public_pages/erp/manufacturing/controllers/MfstructuresController.php'
   and type = 'C';
update module_components
   set title = 'Manufacturing Works Orders'
 where name = 'mfworkorderscontroller'
   and location = 'modules/public_pages/erp/manufacturing/controllers/MfworkordersController.php'
   and type = 'C';
update module_components
   set title = 'Manufacturing Works Order Structures'
 where name = 'mfwostructurescontroller'
   and location = 'modules/public_pages/erp/manufacturing/controllers/MfwostructuresController.php'
   and type = 'C';
update module_components
   set title = 'Quality RR Complaints'
 where name = 'rrcomplaintscontroller'
   and location = 'modules/public_pages/quality/controllers/RrcomplaintsController.php'
   and type = 'C';
update module_components
   set title = 'Quality SD Complaints'
 where name = 'sdcomplaintscontroller'
   and location = 'modules/public_pages/quality/controllers/SdcomplaintsController.php'
   and type = 'C';
update module_components
   set title = 'HR Setup'
 where name = 'setupcontroller'
   and location = 'modules/public_pages/hr/controllers/SetupController.php'
   and type = 'C';
update module_components
   set title = 'Stock Balances'
 where name = 'stbalancescontroller'
   and location = 'modules/public_pages/erp/manufacturing/controllers/StbalancesController.php'
   and type = 'C';
update module_components
   set title = 'Manufacturing Warehouse Bins'
 where name = 'whbinscontroller'
   and location = 'modules/public_pages/erp/manufacturing/controllers/WhbinsController.php'
   and type = 'C';
update module_components
   set title = 'Manufacturing Warehouse Locations'
 where name = 'whlocationscontroller'
   and location = 'modules/public_pages/erp/manufacturing/controllers/WhlocationsController.php'
   and type = 'C';
update module_components
   set title = 'Manufacturing Setup Warehouse Stores'
 where name = 'whstorescontroller'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/controllers/WhstoresController.php'
   and type = 'C';
update module_components
   set title = 'Manufacturing Warehouse Transfer Lines'
 where name = 'whtransferlinescontroller'
   and location = 'modules/public_pages/erp/manufacturing/controllers/WhtransferlinesController.php'
   and type = 'C';
update module_components
   set title = 'Manufacturing Warehouse Transfers'
 where name = 'whtransferscontroller'
   and location = 'modules/public_pages/erp/manufacturing/controllers/WhtransfersController.php'
   and type = 'C';
update module_components
   set title = 'Engineering Work Schedule Parts'
 where name = 'workschedulepartscontroller'
   and location = 'modules/public_pages/engineering/controllers/WorkschedulepartsController.php'
   and type = 'C';
update module_components
   set title = 'VAT Reporting'
 where name = 'vatcontroller'
   and location = 'modules/public_pages/erp/ledger/vat/controllers/VatController.php'
   and type = 'C';
update module_components
   set title = 'General Ledger Balances'
 where name = 'glbalancescontroller'
   and location = 'modules/public_pages/erp/ledger/general_ledger/controllers/GlbalancesController.php'
   and type = 'C';
update module_components
   set title = 'General Ledger Budgets'
 where name = 'glbudgetscontroller'
   and location = 'modules/public_pages/erp/ledger/general_ledger/controllers/GlbudgetsController.php'
   and type = 'C';
update module_components
   set title = 'General Ledger Setup Categories'
 where name = 'ledgercategoryscontroller'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/controllers/LedgercategorysController.php'
   and type = 'C';
update module_components
   set title = 'Production Recording Waste Types'
 where name = 'mfwastetypescontroller'
   and location = 'modules/public_pages/erp/production_recording/controllers/MfwastetypesController.php'
   and type = 'C';
update module_components
   set title = 'Addressses'
 where name = 'addressscontroller'
   and location = 'modules/public_pages/contacts/controllers/AddresssController.php'
   and type = 'C';
update module_components
   set title = 'Ledger Allocations'
 where name = 'allocationscontroller'
   and location = 'modules/public_pages/erp/ledger/controllers/AllocationsController.php'
   and type = 'C';
update module_components
   set title = 'Ticket Attachments'
 where name = 'attachmentscontroller'
   and location = 'modules/public_pages/ticketing/controllers/AttachmentsController.php'
   and type = 'C';
update module_components
   set title = 'Calendar Event Attachments'
 where name = 'calendareventattachmentscontroller'
   and location = 'modules/public_pages/calendar/controllers/CalendareventattachmentsController.php'
   and type = 'C';
update module_components
   set title = 'Cash Book Transactions'
 where name = 'cbtransactionscontroller'
   and location = 'modules/public_pages/erp/cashbook/controllers/CbtransactionsController.php'
   and type = 'C';
update module_components
   set title = 'Company Contact Methods'
 where name = 'companycontactmethodscontroller'
   and location = 'modules/public_pages/contacts/controllers/CompanycontactmethodsController.php'
   and type = 'C';
update module_components
   set title = 'Company Permissions'
 where name = 'companypermissionscontroller'
   and location = 'modules/public_pages/admin/controllers/CompanypermissionsController.php'
   and type = 'C';
update module_components
   set title = 'Quality Complaint Codes'
 where name = 'complaintcodescontroller'
   and location = 'modules/public_pages/quality/controllers/ComplaintcodesController.php'
   and type = 'C';
update module_components
   set title = 'Quality Complaint Volumes'
 where name = 'complaintvolumescontroller'
   and location = 'modules/public_pages/quality/controllers/ComplaintvolumesController.php'
   and type = 'C';
update module_components
   set title = 'EDI'
 where name = 'edicontroller'
   and location = 'modules/public_pages/edi/controllers/EdiController.php'
   and type = 'C';
update module_components
   set title = 'Engineering Resources'
 where name = 'engineeringresourcescontroller'
   and location = 'modules/public_pages/engineering/controllers/EngineeringresourcesController.php'
   and type = 'C';
update module_components
   set title = 'General Ledger SetupL Accounts'
 where name = 'glaccountscontroller'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/controllers/GlaccountsController.php'
   and type = 'C';
update module_components
   set title = 'HR Index'
 where name = 'indexcontroller'
   and location = 'modules/public_pages/hr/controllers/IndexController.php'
   and type = 'C';
update module_components
   set title = 'CRM Index'
 where name = 'indexcontroller'
   and location = 'modules/public_pages/crm/controllers/IndexController.php'
   and type = 'C';
update module_components
   set title = 'Contacts Index'
 where name = 'indexcontroller'
   and location = 'modules/public_pages/contacts/controllers/IndexController.php'
   and type = 'C';
update module_components
   set title = 'Default Index'
 where name = 'indexcontroller'
   and location = 'modules/common/controllers/IndexController.php'
   and type = 'C';
update module_components
   set title = 'Manufacturing'
 where name = 'manufacturingcontroller'
   and location = 'modules/public_pages/erp/manufacturing/controllers/ManufacturingController.php'
   and type = 'C';
update module_components
   set title = 'erp quick links'
 where name = 'erp_quick_links'
   and location = 'modules/common/templates/eglets/erp_quick_links.tpl'
   and type = 'T';
update module_components
   set title = 'Default'
 where name = 'defaultcontroller'
   and location = 'modules/common/controllers/DefaultController.php'
   and type = 'C';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/common/templates/index.tpl'
   and type = 'T';
update module_components
   set title = 'Manufacturing Setup Resources'
 where name = 'mfresourcescontroller'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/controllers/MfresourcesController.php'
   and type = 'C';
update module_components
   set title = 'Production Recording Shift Downtimes'
 where name = 'mfshiftdowntimescontroller'
   and location = 'modules/public_pages/erp/production_recording/controllers/MfshiftdowntimesController.php'
   and type = 'C';
update module_components
   set title = 'Ledger Setup Periodic Payments'
 where name = 'periodicpaymentscontroller'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/controllers/PeriodicpaymentsController.php'
   and type = 'C';
update module_components
   set title = 'Purchase Invoice Lines'
 where name = 'pinvoicelinescontroller'
   and location = 'modules/public_pages/erp/invoicing/purchase_invoicing/controllers/PinvoicelinesController.php'
   and type = 'C';
update module_components
   set title = 'Sales Order Lines'
 where name = 'sorderlinescontroller'
   and location = 'modules/public_pages/erp/order/sales_order/controllers/SorderlinesController.php'
   and type = 'C';
update module_components
   set title = 'Manufacturing Setup Warehouse Locations'
 where name = 'whlocationscontroller'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/controllers/WhlocationsController.php'
   and type = 'C';
update module_components
   set title = 'Manufacturing Warehouse Stores'
 where name = 'whstorescontroller'
   and location = 'modules/public_pages/erp/manufacturing/controllers/WhstoresController.php'
   and type = 'C';
update module_components
   set title = 'Current Activities uzlet'
 where name = 'currentactivitieseglet'
   and location = 'modules/public_pages/crm/eglets/CurrentActivitiesEGlet.php'
   and type = 'E';
update module_components
   set title = 'Manufacturing Warehouse Multi-bin Balances Print uzlet'
 where name = 'multibinbalancesprinteglet'
   and location = 'modules/public_pages/erp/manufacturing/eglets/MultiBinBalancesPrintEGlet.php'
   and type = 'E';
update module_components
   set title = 'CRM Newsletter Clicks by url Grapher'
 where name = 'newsletterclicksbyurlgrapher'
   and location = 'modules/public_pages/crm/eglets/NewsletterClicksByUrlGrapher.php'
   and type = 'E';
update module_components
   set title = 'Purchase Order Overdue Invoices uzlet'
 where name = 'pooverdueinvoiceseglet'
   and location = 'modules/public_pages/erp/order/purchase_order/eglets/POoverdueInvoicesEGlet.php'
   and type = 'E';
update module_components
   set title = 'Purchase Order Invoices on Query uzlet'
 where name = 'poqueryinvoiceseglet'
   and location = 'modules/public_pages/erp/order/purchase_order/eglets/POQueryInvoicesEGlet.php'
   and type = 'E';
update module_components
   set title = 'Purchase Requisitions Requiring Authorisation uzlet'
 where name = 'pordersauthrequisitioneglet'
   and location = 'modules/public_pages/erp/order/purchase_order/eglets/POrdersAuthRequisitionEGlet.php'
   and type = 'E';
update module_components
   set title = 'Tickets Weekly by Priority Grapher'
 where name = 'ticketsweeklybyprioritygrapher'
   and location = 'modules/public_pages/ticketing/eglets/TicketsWeeklyByPriorityGrapher.php'
   and type = 'E';
update module_components
   set title = 'Calendar'
 where name = 'calendar'
   and location = 'modules/public_pages/calendar/models/Calendar.php'
   and type = 'M';
update module_components
   set title = 'Calendar Share List'
 where name = 'calendarsharecollection'
   and location = 'modules/public_pages/calendar/models/CalendarShareCollection.php'
   and type = 'M';
update module_components
   set title = 'CRM Campaign Statuses'
 where name = 'campaignstatuscollection'
   and location = 'modules/public_pages/crm/models/CampaignstatusCollection.php'
   and type = 'M';
update module_components
   set title = 'Quality Complaint'
 where name = 'complaint'
   and location = 'modules/public_pages/quality/models/Complaint.php'
   and type = 'M';
update module_components
   set title = 'Quality Complaint Code'
 where name = 'complaintcode'
   and location = 'modules/public_pages/quality/models/ComplaintCode.php'
   and type = 'M';
update module_components
   set title = 'EDI Transaction Log Entries'
 where name = 'editransactionlogcollection'
   and location = 'modules/public_pages/edi/models/EDITransactionLogCollection.php'
   and type = 'M';
update module_components
   set title = 'General Ledger Account Centre List'
 where name = 'glaccountcentrecollection'
   and location = 'modules/public_pages/erp/ledger/general_ledger/models/GLAccountCentreCollection.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Centre Downtime Codes'
 where name = 'mfcentredowntimecodecollection'
   and location = 'modules/public_pages/erp/production_recording/models/MFCentreDowntimeCodeCollection.php'
   and type = 'M';
update module_components
   set title = 'Purchase Order Lines'
 where name = 'porderlinecollection'
   and location = 'modules/public_pages/erp/order/purchase_order/models/POrderLineCollection.php'
   and type = 'M';
update module_components
   set title = 'Sales Ledger Transaction'
 where name = 'sltransaction'
   and location = 'modules/public_pages/erp/ledger/sales_ledger/models/SLTransaction.php'
   and type = 'M';
update module_components
   set title = 'Sales Order Packing Slips'
 where name = 'sopackingslipcollection'
   and location = 'modules/public_pages/erp/order/sales_order/models/SOPackingSlipCollection.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Stock Transactions'
 where name = 'sttransactioncollection'
   and location = 'modules/public_pages/erp/manufacturing/models/STTransactionCollection.php'
   and type = 'M';
update module_components
   set title = 'Bench Pack Traceability Record Form'
 where name = 'benchpacktraceabilityrecordform'
   and location = 'modules/public_pages/erp/manufacturing/reports/BenchPackTraceabilityRecordForm.php'
   and type = 'R';
update module_components
   set title = 'HSBC Bacs'
 where name = 'hsbc_bacs'
   and location = 'modules/public_pages/erp/ledger/purchase_ledger/reports/HSBC_BACS.php'
   and type = 'R';
update module_components
   set title = 'view contact methods'
 where name = 'viewcontact_methods'
   and location = 'modules/public_pages/erp/ledger/purchase_ledger/templates/plsuppliers/viewcontact_methods.tpl'
   and type = 'T';
update module_components
   set title = 'project quick links'
 where name = 'project_quick_links'
   and location = 'modules/common/templates/eglets/project_quick_links.tpl'
   and type = 'T';
update module_components
   set title = 'Company Notes'
 where name = 'companynotecollection'
   and location = 'modules/public_pages/contacts/models/CompanyNoteCollection.php'
   and type = 'M';
update module_components
   set title = 'Company Parameters'
 where name = 'companyparams'
   and location = 'modules/public_pages/contacts/models/CompanyParams.php'
   and type = 'M';
update module_components
   set title = 'Company Rating'
 where name = 'companyrating'
   and location = 'modules/public_pages/contacts/models/CompanyRating.php'
   and type = 'M';
update module_components
   set title = 'Company Rating List'
 where name = 'companyratingcollection'
   and location = 'modules/public_pages/contacts/models/CompanyRatingCollection.php'
   and type = 'M';
update module_components
   set title = 'Sales Order'
 where name = 'sorder'
   and location = 'modules/public_pages/erp/order/sales_order/models/SOrder.php'
   and type = 'M';
update module_components
   set title = 'change status'
 where name = 'changestatus'
   and location = 'modules/public_pages/erp/manufacturing/templates/sttransactions/changestatus.tpl'
   and type = 'T';
update module_components
   set title = 'General Ledger Account Search'
 where name = 'glaccountssearch'
   and location = 'modules/public_pages/erp/ledger/general_ledger/models/glaccountsSearch.php'
   and type = 'M';
update module_components
   set title = 'General Ledger Budgets'
 where name = 'glbudgetcollection'
   and location = 'modules/public_pages/erp/ledger/general_ledger/models/GLBudgetCollection.php'
   and type = 'M';
update module_components
   set title = 'General Ledger Periods'
 where name = 'glperiodcollection'
   and location = 'modules/public_pages/erp/ledger/general_ledger/models/GLPeriodCollection.php'
   and type = 'M';
update module_components
   set title = 'General Ledger Summaries'
 where name = 'glsummarycollection'
   and location = 'modules/public_pages/erp/ledger/general_ledger/models/GLSummaryCollection.php'
   and type = 'M';
update module_components
   set title = 'General Ledger Transaction'
 where name = 'gltransaction'
   and location = 'modules/public_pages/erp/ledger/general_ledger/models/GLTransaction.php'
   and type = 'M';
update module_components
   set title = 'VAT Returns'
 where name = 'vatcollection'
   and location = 'modules/public_pages/erp/ledger/vat/models/VatCollection.php'
   and type = 'M';
update module_components
   set title = 'Ledger Setup Bank Accounts'
 where name = 'bankaccountscontroller'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/controllers/BankaccountsController.php'
   and type = 'C';
update module_components
   set title = 'Company Attachments'
 where name = 'companyattachmentscontroller'
   and location = 'modules/public_pages/contacts/controllers/CompanyattachmentsController.php'
   and type = 'C';
update module_components
   set title = 'Ledger Setup Currency Rates'
 where name = 'currencyratescontroller'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/controllers/CurrencyratesController.php'
   and type = 'C';
update module_components
   set title = 'General Ledger Setup Balances'
 where name = 'glbalancescontroller'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/controllers/GlbalancesController.php'
   and type = 'C';
update module_components
   set title = 'Shared Preferences'
 where name = 'sharedpreferences'
   and location = 'modules/public_pages/shared/controllers/SharedPreferences.php'
   and type = 'C';
update module_components
   set title = 'VAT Return Search'
 where name = 'vatsearch'
   and location = 'modules/public_pages/erp/ledger/vat/models/VatSearch.php'
   and type = 'M';
update module_components
   set title = 'add response'
 where name = 'add_response'
   and location = 'modules/public_pages/ticketing/templates/tickets/add_response.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/ledger/vat/templates/index.tpl'
   and type = 'T';
update module_components
   set title = 'calendar'
 where name = 'calendar'
   and location = 'modules/public_pages/calendar/resources/js/calendar.js'
   and type = 'J';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/ticketing/templates/tickets/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/ticketing/templates/tickets/new.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/ticketing/templates/tickets/view.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/projects/templates/tasks/new.tpl'
   and type = 'T';
update module_components
   set title = 'Project Issue Header'
 where name = 'projectissueheader'
   and location = 'modules/public_pages/projects/models/ProjectIssueHeader.php'
   and type = 'M';
update module_components
   set title = 'Queues'
 where name = 'queuescontroller'
   and location = 'modules/public_pages/ticketing/controllers/QueuesController.php'
   and type = 'C';
update module_components
   set title = 'Project Issue Line'
 where name = 'projectissueline'
   and location = 'modules/public_pages/projects/models/ProjectIssueLine.php'
   and type = 'M';
update module_components
   set title = 'Client Tickets'
 where name = 'clientcontroller'
   and location = 'modules/public_pages/ticketing/controllers/ClientController.php'
   and type = 'C';
update module_components
   set title = 'Ticketing Setup'
 where name = 'setupcontroller'
   and location = 'modules/public_pages/ticketing/controllers/SetupController.php'
   and type = 'C';
update module_components
   set title = 'view resources'
 where name = 'viewresources'
   and location = 'modules/public_pages/projects/templates/tasks/viewresources.tpl'
   and type = 'T';
update module_components
   set title = 'view resource'
 where name = 'viewresource'
   and location = 'modules/public_pages/projects/templates/tasks/viewresource.tpl'
   and type = 'T';
update module_components
   set title = 'Calendar Events'
 where name = 'calendareventscontroller'
   and location = 'modules/public_pages/calendar/controllers/CalendareventsController.php'
   and type = 'C';
update module_components
   set title = 'Calendars'
 where name = 'calendarscontroller'
   and location = 'modules/public_pages/calendar/controllers/CalendarsController.php'
   and type = 'C';
update module_components
   set title = 'Purchase Invoices'
 where name = 'pinvoicescontroller'
   and location = 'modules/public_pages/erp/invoicing/purchase_invoicing/controllers/PinvoicesController.php'
   and type = 'C';
update module_components
   set title = 'Client Ticket Quick Entry uzlet'
 where name = 'clientticketquickentryeglet'
   and location = 'modules/public_pages/ticketing/eglets/ClientTicketQuickEntryEGlet.php'
   and type = 'E';
update module_components
   set title = 'My Current Tickets uzlet'
 where name = 'mycurrentticketseglet'
   and location = 'modules/public_pages/ticketing/eglets/MyCurrentTicketsEGlet.php'
   and type = 'E';
update module_components
   set title = 'Project Resource Types'
 where name = 'resourcetypecollection'
   and location = 'modules/public_pages/projects/models/ResourcetypeCollection.php'
   and type = 'M';
update module_components
   set title = 'CRM Campaign Search'
 where name = 'campaignsearch'
   and location = 'modules/public_pages/crm/models/CampaignSearch.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Structure'
 where name = 'mfstructure'
   and location = 'modules/public_pages/erp/manufacturing/models/MFStructure.php'
   and type = 'M';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/contacts/templates/partyaddresss/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/contacts/templates/partyaddresss/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/contacts/templates/partyattachments/new.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/contacts/templates/partyattachments/view.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/contacts/templates/partycontactmethods/new.tpl'
   and type = 'T';
update module_components
   set title = 'Purchase Ledger Aged Creditors Summary uzlet'
 where name = 'agedcreditorssummaryeglet'
   and location = 'modules/public_pages/erp/ledger/purchase_ledger/eglets/agedCreditorsSummaryEGlet.php'
   and type = 'E';
update module_components
   set title = 'Bank Account'
 where name = 'cbaccount'
   and location = 'modules/public_pages/erp/cashbook/models/CBAccount.php'
   and type = 'M';
update module_components
   set title = 'Purchase Ledger Allocation'
 where name = 'plallocation'
   and location = 'modules/public_pages/erp/ledger/purchase_ledger/models/PLAllocation.php'
   and type = 'M';
update module_components
   set title = 'Purchase Ledger Allocation List'
 where name = 'plallocationcollection'
   and location = 'modules/public_pages/erp/ledger/purchase_ledger/models/PLAllocationCollection.php'
   and type = 'M';
update module_components
   set title = 'Purchase Ledger Payment'
 where name = 'plpayment'
   and location = 'modules/public_pages/erp/ledger/purchase_ledger/models/PLPayment.php'
   and type = 'M';
update module_components
   set title = 'Purchase Ledger Supplier'
 where name = 'plsupplier'
   and location = 'modules/public_pages/erp/ledger/purchase_ledger/models/PLSupplier.php'
   and type = 'M';
update module_components
   set title = 'Purchase Ledger Suppliers'
 where name = 'plsuppliercollection'
   and location = 'modules/public_pages/erp/ledger/purchase_ledger/models/PLSupplierCollection.php'
   and type = 'M';
update module_components
   set title = 'allocate'
 where name = 'allocate'
   and location = 'modules/public_pages/erp/ledger/sales_ledger/templates/slcustomers/allocate.tpl'
   and type = 'T';
update module_components
   set title = 'Customer Service Grapher'
 where name = 'customerservicegrapher'
   and location = 'modules/public_pages/erp/customer_service/eglets/CustomerServiceGrapher.php'
   and type = 'E';
update module_components
   set title = 'Sales Orders not Invoiced uzlet'
 where name = 'sordersnotinvoiceduzlet'
   and location = 'modules/public_pages/erp/order/sales_order/eglets/SOrdersNotInvoicedUZlet.php'
   and type = 'E';
update module_components
   set title = 'Customer Service Failure Code'
 where name = 'csfailurecode'
   and location = 'modules/public_pages/erp/customer_service/models/CSFailureCode.php'
   and type = 'M';
update module_components
   set title = 'Customer Service Failure Codes'
 where name = 'csfailurecodecollection'
   and location = 'modules/public_pages/erp/customer_service/models/CSFailureCodeCollection.php'
   and type = 'M';
update module_components
   set title = 'Customer Service List'
 where name = 'customerservicecollection'
   and location = 'modules/public_pages/erp/customer_service/models/CustomerServiceCollection.php'
   and type = 'M';
update module_components
   set title = 'EDI External System'
 where name = 'externalsystem'
   and location = 'modules/public_pages/edi/models/ExternalSystem.php'
   and type = 'M';
update module_components
   set title = 'EDI External Systems'
 where name = 'externalsystemcollection'
   and location = 'modules/public_pages/edi/models/ExternalSystemCollection.php'
   and type = 'M';
update module_components
   set title = 'Project Equipment List'
 where name = 'projectequipmentcollection'
   and location = 'modules/public_pages/projects/models/ProjectEquipmentCollection.php'
   and type = 'M';
update module_components
   set title = 'edi'
 where name = 'edi'
   and location = 'modules/public_pages/edi/resources/css/edi.css'
   and type = 'S';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/dashboard/templates/mydata/index.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/dashboard/templates/preferences/index.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/dashboard/templates/details/view.tpl'
   and type = 'T';
update module_components
   set title = 'Project Cost Charges'
 where name = 'projectcostchargescontroller'
   and location = 'modules/public_pages/projects/controllers/ProjectcostchargesController.php'
   and type = 'C';
update module_components
   set title = 'Project Equipment Allocations'
 where name = 'projectequipmentallocationscontroller'
   and location = 'modules/public_pages/projects/controllers/ProjectequipmentallocationsController.php'
   and type = 'C';
update module_components
   set title = 'Project Issue Lines'
 where name = 'projectissuelinescontroller'
   and location = 'modules/public_pages/projects/controllers/ProjectissuelinesController.php'
   and type = 'C';
update module_components
   set title = 'Project Issues'
 where name = 'projectissuescontroller'
   and location = 'modules/public_pages/projects/controllers/ProjectissuesController.php'
   and type = 'C';
update module_components
   set title = 'Project Notes'
 where name = 'projectnotescontroller'
   and location = 'modules/public_pages/projects/controllers/ProjectnotesController.php'
   and type = 'C';
update module_components
   set title = 'Project Task Attachments'
 where name = 'taskattachmentscontroller'
   and location = 'modules/public_pages/projects/controllers/TaskattachmentsController.php'
   and type = 'C';
update module_components
   set title = 'Current Projects uzlet'
 where name = 'currentprojectseglet'
   and location = 'modules/public_pages/projects/eglets/CurrentProjectsEGlet.php'
   and type = 'E';
update module_components
   set title = 'Current Tasks uzlet'
 where name = 'currenttaskseglet'
   and location = 'modules/public_pages/projects/eglets/CurrentTasksEGlet.php'
   and type = 'E';
update module_components
   set title = 'Project Equipment Utilisation uzlet'
 where name = 'equipmentutilisationeglet'
   and location = 'modules/public_pages/projects/eglets/EquipmentUtilisationEGlet.php'
   and type = 'E';
update module_components
   set title = 'Project Logged Hours per Week uzlet'
 where name = 'loggedhoursperweekeglet'
   and location = 'modules/public_pages/projects/eglets/LoggedHoursPerWeekEGlet.php'
   and type = 'E';
update module_components
   set title = 'Project My Current Issues uzlet'
 where name = 'mycurrentissueseglet'
   and location = 'modules/public_pages/projects/eglets/MyCurrentIssuesEGlet.php'
   and type = 'E';
update module_components
   set title = 'Employee Training Plans'
 where name = 'employeetrainingplanscontroller'
   and location = 'modules/public_pages/hr/controllers/EmployeetrainingplansController.php'
   and type = 'C';
update module_components
   set title = 'HR Expense Lines'
 where name = 'expenselinescontroller'
   and location = 'modules/public_pages/hr/controllers/ExpenselinesController.php'
   and type = 'C';
update module_components
   set title = 'edit post'
 where name = 'edit_post'
   and location = 'modules/public_pages/hr/templates/expenses/edit_post.tpl'
   and type = 'T';
update module_components
   set title = 'Asset Register Analysis'
 where name = 'aranalysis'
   and location = 'modules/public_pages/erp/asset_register/models/ARAnalysis.php'
   and type = 'M';
update module_components
   set title = 'confirm request'
 where name = 'confirmrequest'
   and location = 'modules/public_pages/hr/templates/holidayrequests/confirmrequest.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/hr/templates/holidayrequests/new.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/hr/templates/holidayrequests/view.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/hr/templates/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/whbins/new.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/whbins/view.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/whbins/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/whlocations/new.tpl'
   and type = 'T';
update module_components
   set title = 'Report Parts'
 where name = 'reportpartcollection'
   and location = 'modules/public_pages/output/output_setup/models/ReportPartCollection.php'
   and type = 'M';
update module_components
   set title = 'Sales Invoice Lines'
 where name = 'sinvoicelinecollection'
   and location = 'modules/public_pages/erp/invoicing/sales_invoicing/models/SInvoiceLineCollection.php'
   and type = 'M';
update module_components
   set title = 'Sales Invoice Search'
 where name = 'sinvoicessearch'
   and location = 'modules/public_pages/erp/invoicing/sales_invoicing/models/sinvoicesSearch.php'
   and type = 'M';
update module_components
   set title = 'Asset Register Transactions'
 where name = 'artransactionscontroller'
   and location = 'modules/public_pages/erp/asset_register/controllers/ArtransactionsController.php'
   and type = 'C';
update module_components
   set title = 'Asset Register Groups'
 where name = 'argroupscontroller'
   and location = 'modules/public_pages/erp/asset_register/controllers/ArgroupsController.php'
   and type = 'C';
update module_components
   set title = 'Purchase Ledger Suppliers'
 where name = 'plsupplierscontroller'
   and location = 'modules/public_pages/erp/ledger/purchase_ledger/controllers/PlsuppliersController.php'
   and type = 'C';
update module_components
   set title = 'Purchase Ledger Transactions'
 where name = 'pltransactionscontroller'
   and location = 'modules/public_pages/erp/ledger/purchase_ledger/controllers/PltransactionsController.php'
   and type = 'C';
update module_components
   set title = 'Bank Accounts Summary uzlet'
 where name = 'bankaccountssummary'
   and location = 'modules/public_pages/erp/cashbook/eglets/BankAccountsSummary.php'
   and type = 'E';
update module_components
   set title = 'Cash Book Overdue Periodic Payments uzlet'
 where name = 'ppoverdueeglet'
   and location = 'modules/public_pages/erp/cashbook/eglets/PPOverdueEGlet.php'
   and type = 'E';
update module_components
   set title = 'Bank Accounts'
 where name = 'cbaccountcollection'
   and location = 'modules/public_pages/erp/cashbook/models/CBAccountCollection.php'
   and type = 'M';
update module_components
   set title = 'output setup'
 where name = 'output_setup'
   and location = 'modules/public_pages/output/output_setup/resources/css/output_setup.css'
   and type = 'S';
update module_components
   set title = 'Asses Register Locations'
 where name = 'arlocationscontroller'
   and location = 'modules/public_pages/erp/asset_register/controllers/ArlocationsController.php'
   and type = 'C';
update module_components
   set title = 'Asset Register Index'
 where name = 'indexcontroller'
   and location = 'modules/public_pages/erp/asset_register/controllers/IndexController.php'
   and type = 'C';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/erp/ledger/sales_ledger/templates/sltransactions/view.tpl'
   and type = 'T';
update module_components
   set title = 'Company Addressses'
 where name = 'companyaddressscontroller'
   and location = 'modules/public_pages/contacts/controllers/CompanyaddresssController.php'
   and type = 'C';
update module_components
   set title = 'Stock Unit of Measure Conversion'
 where name = 'stuomconversion'
   and location = 'modules/public_pages/erp/manufacturing/models/STuomconversion.php'
   and type = 'M';
update module_components
   set title = 'Stock Unit of Measure Conversions'
 where name = 'stuomconversioncollection'
   and location = 'modules/public_pages/erp/manufacturing/models/STuomconversionCollection.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Operations'
 where name = 'mfoperationscontroller'
   and location = 'modules/public_pages/erp/manufacturing/controllers/MfoperationsController.php'
   and type = 'C';
update module_components
   set title = 'Manufacturing Outside Operations'
 where name = 'mfoutsideoperationscontroller'
   and location = 'modules/public_pages/erp/manufacturing/controllers/MfoutsideoperationsController.php'
   and type = 'C';
update module_components
   set title = 'System Unit of Measure Conversion'
 where name = 'syuomconversion'
   and location = 'modules/public_pages/erp/manufacturing/models/SYuomconversion.php'
   and type = 'M';
update module_components
   set title = 'System Unit of Measure Conversions'
 where name = 'syuomconversioncollection'
   and location = 'modules/public_pages/erp/manufacturing/models/SYuomconversionCollection.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Warehoue Action'
 where name = 'whaction'
   and location = 'modules/public_pages/erp/manufacturing/models/WHAction.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Warehoue Actions'
 where name = 'whactioncollection'
   and location = 'modules/public_pages/erp/manufacturing/models/WHActionCollection.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Warehouse Bin'
 where name = 'whbin'
   and location = 'modules/public_pages/erp/manufacturing/models/WHBin.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Warehouse Bins'
 where name = 'whbincollection'
   and location = 'modules/public_pages/erp/manufacturing/models/WHBinCollection.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Warehouse Bin Search'
 where name = 'whbinssearch'
   and location = 'modules/public_pages/erp/manufacturing/models/whbinsSearch.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Warehouse Location'
 where name = 'whlocation'
   and location = 'modules/public_pages/erp/manufacturing/models/WHLocation.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Warehouse Locations'
 where name = 'whlocationcollection'
   and location = 'modules/public_pages/erp/manufacturing/models/WHLocationCollection.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Warehouse Location Search'
 where name = 'whlocationssearch'
   and location = 'modules/public_pages/erp/manufacturing/models/whlocationsSearch.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Warehouse Store'
 where name = 'whstore'
   and location = 'modules/public_pages/erp/manufacturing/models/WHStore.php'
   and type = 'M';
update module_components
   set title = 'new note'
 where name = 'newnote'
   and location = 'modules/public_pages/contacts/templates/persons/newnote.tpl'
   and type = 'T';
update module_components
   set title = 'Sales Orders'
 where name = 'sorderscontroller'
   and location = 'modules/public_pages/erp/order/sales_order/controllers/SordersController.php'
   and type = 'C';
update module_components
   set title = 'Sales Ledger Analysis'
 where name = 'slanalysisscontroller'
   and location = 'modules/public_pages/erp/ledger/sales_ledger/controllers/SlanalysissController.php'
   and type = 'C';
update module_components
   set title = 'Customer Discounts'
 where name = 'sldiscountscontroller'
   and location = 'modules/public_pages/erp/ledger/sales_ledger/controllers/SldiscountsController.php'
   and type = 'C';
update module_components
   set title = 'Sales Ledger Transactions'
 where name = 'sltransactionscontroller'
   and location = 'modules/public_pages/erp/ledger/sales_ledger/controllers/SltransactionsController.php'
   and type = 'C';
update module_components
   set title = 'Sales Ledger Customers'
 where name = 'slcustomerscontroller'
   and location = 'modules/public_pages/erp/ledger/sales_ledger/controllers/SlcustomersController.php'
   and type = 'C';
update module_components
   set title = 'Manufacturing Centres'
 where name = 'mfcentrecollection'
   and location = 'modules/public_pages/erp/manufacturing/models/MFCentreCollection.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Data Sheet'
 where name = 'mfdatasheet'
   and location = 'modules/public_pages/erp/manufacturing/models/MFDataSheet.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Data Sheets'
 where name = 'mfdatasheetcollection'
   and location = 'modules/public_pages/erp/manufacturing/models/MFDataSheetCollection.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Department'
 where name = 'mfdept'
   and location = 'modules/public_pages/erp/manufacturing/models/MFDept.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Departments'
 where name = 'mfdeptcollection'
   and location = 'modules/public_pages/erp/manufacturing/models/MFDeptCollection.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Departments Search'
 where name = 'mfdeptssearch'
   and location = 'modules/public_pages/erp/manufacturing/models/mfdeptsSearch.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Operation'
 where name = 'mfoperation'
   and location = 'modules/public_pages/erp/manufacturing/models/MFOperation.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Operations'
 where name = 'mfoperationcollection'
   and location = 'modules/public_pages/erp/manufacturing/models/MFOperationCollection.php'
   and type = 'M';
update module_components
   set title = 'Engineering Work Schedule Note'
 where name = 'workschedulenote'
   and location = 'modules/public_pages/engineering/models/WorkScheduleNote.php'
   and type = 'M';
update module_components
   set title = 'Engineering Work Schedule Notes'
 where name = 'workschedulenotecollection'
   and location = 'modules/public_pages/engineering/models/WorkScheduleNoteCollection.php'
   and type = 'M';
update module_components
   set title = 'Engineering Work Schedule Part'
 where name = 'workschedulepart'
   and location = 'modules/public_pages/engineering/models/WorkSchedulePart.php'
   and type = 'M';
update module_components
   set title = 'view by items'
 where name = 'viewbyitems'
   and location = 'modules/public_pages/erp/order/sales_order/templates/sorders/viewbyitems.tpl'
   and type = 'T';
update module_components
   set title = 'main nav'
 where name = 'main_nav'
   and location = 'modules/common/templates/elements/main_nav.tpl'
   and type = 'T';
update module_components
   set title = 'info message'
 where name = 'info_message'
   and location = 'modules/common/templates/elements/info_message.tpl'
   and type = 'T';
update module_components
   set title = 'linkbox'
 where name = 'linkbox'
   and location = 'modules/common/templates/elements/linkbox.tpl'
   and type = 'T';
update module_components
   set title = 'options'
 where name = 'options'
   and location = 'modules/common/templates/elements/options.tpl'
   and type = 'T';
update module_components
   set title = 'Project Categories'
 where name = 'projectcategorycollection'
   and location = 'modules/public_pages/projects/models/ProjectcategoryCollection.php'
   and type = 'M';
update module_components
   set title = 'General Ledger Accounts'
 where name = 'glaccountscontroller'
   and location = 'modules/public_pages/erp/ledger/general_ledger/controllers/GlaccountsController.php'
   and type = 'C';
update module_components
   set title = 'General Ledger Cost Centres'
 where name = 'glcentrescontroller'
   and location = 'modules/public_pages/erp/ledger/general_ledger/controllers/GlcentresController.php'
   and type = 'C';
update module_components
   set title = 'General Ledger Transactions'
 where name = 'gltransactionscontroller'
   and location = 'modules/public_pages/erp/ledger/general_ledger/controllers/GltransactionsController.php'
   and type = 'C';
update module_components
   set title = 'My Tickets uzlet'
 where name = 'myticketseglet'
   and location = 'modules/public_pages/ticketing/eglets/MyTicketsEGlet.php'
   and type = 'E';
update module_components
   set title = 'Employee Training Plan'
 where name = 'employeetrainingplan'
   and location = 'modules/public_pages/hr/models/EmployeeTrainingPlan.php'
   and type = 'M';
update module_components
   set title = 'General Ledger Budget'
 where name = 'glbudget'
   and location = 'modules/public_pages/erp/ledger/general_ledger/models/GLBudget.php'
   and type = 'M';
update module_components
   set title = 'General Ledger Centre'
 where name = 'glcentre'
   and location = 'modules/public_pages/erp/ledger/general_ledger/models/GLCentre.php'
   and type = 'M';
update module_components
   set title = 'General Ledger Parameters'
 where name = 'glparamscollection'
   and location = 'modules/public_pages/erp/ledger/general_ledger/models/GLParamsCollection.php'
   and type = 'M';
update module_components
   set title = 'Stock Type Code'
 where name = 'sttypecode'
   and location = 'modules/public_pages/erp/manufacturing/models/STTypecode.php'
   and type = 'M';
update module_components
   set title = 'VAT Intrastat Transaction Type'
 where name = 'intrastattranstype'
   and location = 'modules/public_pages/erp/ledger/vat/models/IntrastatTransType.php'
   and type = 'M';
update module_components
   set title = 'Cache'
 where name = 'cachecontroller'
   and location = 'modules/public_pages/cache/cache_management/controllers/CacheController.php'
   and type = 'C';
update module_components
   set title = 'Injector Classes'
 where name = 'injectorclassscontroller'
   and location = 'modules/public_pages/system_admin/controllers/InjectorclasssController.php'
   and type = 'C';
update module_components
   set title = 'System AdDmin Index'
 where name = 'indexcontroller'
   and location = 'modules/public_pages/system_admin/controllers/IndexController.php'
   and type = 'C';
update module_components
   set title = 'System Admin Module Adminstration'
 where name = 'moduleadminscontroller'
   and location = 'modules/public_pages/system_admin/controllers/ModuleadminsController.php'
   and type = 'C';
update module_components
   set title = 'System Admin Module Components'
 where name = 'modulecomponentscontroller'
   and location = 'modules/public_pages/system_admin/controllers/ModulecomponentsController.php'
   and type = 'C';
update module_components
   set title = 'Purchase Order Preferences'
 where name = 'purchase_orderpreferences'
   and location = 'modules/public_pages/erp/order/purchase_order/models/Purchase_orderPreferences.php'
   and type = 'M';
update module_components
   set title = 'select warehouse transfers'
 where name = 'selectwhtransfers'
   and location = 'modules/public_pages/erp/despatch/templates/whtransfers/selectwhtransfers.tpl'
   and type = 'T';
update module_components
   set title = 'System Module Administration List'
 where name = 'moduleadmincollection'
   and location = 'modules/public_pages/system_admin/models/ModuleAdminCollection.php'
   and type = 'M';
update module_components
   set title = 'view permissions'
 where name = 'view_permissions'
   and location = 'modules/public_pages/system_admin/templates/moduleobjects/view_permissions.tpl'
   and type = 'T';
update module_components
   set title = 'System Companies'
 where name = 'systemcompanyscontroller'
   and location = 'modules/public_pages/system_admin/controllers/SystemcompanysController.php'
   and type = 'C';
update module_components
   set title = 'System Policies'
 where name = 'systemobjectpolicyscontroller'
   and location = 'modules/public_pages/system_admin/controllers/SystemobjectpolicysController.php'
   and type = 'C';
update module_components
   set title = 'System Policy Access'
 where name = 'systempolicyaccesslistscontroller'
   and location = 'modules/public_pages/system_admin/controllers/SystempolicyaccesslistsController.php'
   and type = 'C';
update module_components
   set title = 'User Company Access'
 where name = 'usercompanyaccessscontroller'
   and location = 'modules/public_pages/system_admin/controllers/UsercompanyaccesssController.php'
   and type = 'C';
update module_components
   set title = 'System Admin Module Defaults'
 where name = 'moduledefaultscontroller'
   and location = 'modules/public_pages/system_admin/controllers/ModuledefaultsController.php'
   and type = 'C';
update module_components
   set title = 'System Admin Modules'
 where name = 'moduleobjectscontroller'
   and location = 'modules/public_pages/system_admin/controllers/ModuleobjectsController.php'
   and type = 'C';
update module_components
   set title = 'System Admin Permissions'
 where name = 'permissionscontroller'
   and location = 'modules/public_pages/system_admin/controllers/PermissionsController.php'
   and type = 'C';
update module_components
   set title = 'System Policy Control Lists'
 where name = 'systempolicycontrollistscontroller'
   and location = 'modules/public_pages/system_admin/controllers/SystempolicycontrollistsController.php'
   and type = 'C';
update module_components
   set title = 'System Admin Systems'
 where name = 'systemscontroller'
   and location = 'modules/public_pages/system_admin/controllers/SystemsController.php'
   and type = 'C';
update module_components
   set title = 'System Injector Class'
 where name = 'injectorclass'
   and location = 'modules/public_pages/system_admin/models/InjectorClass.php'
   and type = 'M';
update module_components
   set title = 'System Injector Classes'
 where name = 'injectorclasscollection'
   and location = 'modules/public_pages/system_admin/models/InjectorClassCollection.php'
   and type = 'M';
update module_components
   set title = 'System Injector Class Search'
 where name = 'injectorsearch'
   and location = 'modules/public_pages/system_admin/models/InjectorSearch.php'
   and type = 'M';
update module_components
   set title = 'view components'
 where name = 'view_components'
   and location = 'modules/public_pages/system_admin/templates/moduleobjects/view_components.tpl'
   and type = 'T';
update module_components
   set title = 'System Module Administration'
 where name = 'moduleadmin'
   and location = 'modules/public_pages/system_admin/models/ModuleAdmin.php'
   and type = 'M';
update module_components
   set title = 'view as dataobject'
 where name = 'view_as_dataobject'
   and location = 'modules/public_pages/system_admin/templates/moduleobjects/view_as_dataobject.tpl'
   and type = 'T';
update module_components
   set title = 'permissions tree'
 where name = 'permissions_tree'
   and location = 'modules/public_pages/system_admin/templates/moduleobjects/permissions_tree.tpl'
   and type = 'T';
update module_components
   set title = 'new schema'
 where name = 'newschema'
   and location = 'modules/public_pages/system_admin/templates/newschema.tpl'
   and type = 'T';
update module_components
   set title = 'System Module Components'
 where name = 'modulecomponentcollection'
   and location = 'modules/public_pages/system_admin/models/ModuleComponentCollection.php'
   and type = 'M';
update module_components
   set title = 'System Module Defaults'
 where name = 'moduledefaultcollection'
   and location = 'modules/public_pages/system_admin/models/ModuleDefaultCollection.php'
   and type = 'M';
update module_components
   set title = 'Schema'
 where name = 'schema'
   and location = 'modules/public_pages/system_admin/models/Schema.php'
   and type = 'M';
update module_components
   set title = 'System Modules'
 where name = 'moduleobjectcollection'
   and location = 'modules/public_pages/system_admin/models/ModuleObjectCollection.php'
   and type = 'M';
update module_components
   set title = 'System Permission'
 where name = 'permission'
   and location = 'modules/public_pages/system_admin/models/Permission.php'
   and type = 'M';
update module_components
   set title = 'System Permissions'
 where name = 'permissioncollection'
   and location = 'modules/public_pages/system_admin/models/PermissionCollection.php'
   and type = 'M';
update module_components
   set title = 'System Company'
 where name = 'systemcompany'
   and location = 'modules/public_pages/system_admin/models/Systemcompany.php'
   and type = 'M';
update module_components
   set title = 'System Permission Parameter'
 where name = 'permissionparameters'
   and location = 'modules/public_pages/system_admin/models/PermissionParameters.php'
   and type = 'M';
update module_components
   set title = 'System Permission Parameters'
 where name = 'permissionparameterscollection'
   and location = 'modules/public_pages/system_admin/models/PermissionParametersCollection.php'
   and type = 'M';
update module_components
   set title = 'System Permission Parameters Search'
 where name = 'permissionssearch'
   and location = 'modules/public_pages/system_admin/models/permissionsSearch.php'
   and type = 'M';
update module_components
   set title = 'System Companies'
 where name = 'systemcompanycollection'
   and location = 'modules/public_pages/system_admin/models/SystemcompanyCollection.php'
   and type = 'M';
update module_components
   set title = 'System Object Policy'
 where name = 'systemobjectpolicy'
   and location = 'modules/public_pages/system_admin/models/SystemObjectPolicy.php'
   and type = 'M';
update module_components
   set title = 'System Object Policies'
 where name = 'systemobjectpolicycollection'
   and location = 'modules/public_pages/system_admin/models/SystemObjectPolicyCollection.php'
   and type = 'M';
update module_components
   set title = 'System Policy Access List'
 where name = 'systempolicyaccesslist'
   and location = 'modules/public_pages/system_admin/models/SystemPolicyAccessList.php'
   and type = 'M';
update module_components
   set title = 'System Policy Access Lists'
 where name = 'systempolicyaccesslistcollection'
   and location = 'modules/public_pages/system_admin/models/SystemPolicyAccessListCollection.php'
   and type = 'M';
update module_components
   set title = 'System Policy Control List'
 where name = 'systempolicycontrollist'
   and location = 'modules/public_pages/system_admin/models/SystemPolicyControlList.php'
   and type = 'M';
update module_components
   set title = 'System Policy Control Lists'
 where name = 'systempolicycontrollistcollection'
   and location = 'modules/public_pages/system_admin/models/SystemPolicyControlListCollection.php'
   and type = 'M';
update module_components
   set title = 'User Company Accesses'
 where name = 'usercompanyaccesscollection'
   and location = 'modules/public_pages/system_admin/models/UsercompanyaccessCollection.php'
   and type = 'M';
update module_components
   set title = 'Asset Locations'
 where name = 'arlocationscontroller'
   and location = 'modules/public_pages/erp/asset_register/asset_setup/controllers/ArlocationsController.php'
   and type = 'C';
update module_components
   set title = 'Reports'
 where name = 'reportscontroller'
   and location = 'modules/public_pages/reporting/controllers/ReportsController.php'
   and type = 'C';
update module_components
   set title = 'Asset Groups'
 where name = 'argroupscontroller'
   and location = 'modules/public_pages/erp/asset_register/asset_setup/controllers/ArgroupsController.php'
   and type = 'C';
update module_components
   set title = 'Asset Analysis Codes'
 where name = 'aranalysisscontroller'
   and location = 'modules/public_pages/erp/asset_register/asset_setup/controllers/AranalysissController.php'
   and type = 'C';
update module_components
   set title = 'Party Attachments'
 where name = 'partyattachmentscontroller'
   and location = 'modules/public_pages/contacts/controllers/PartyattachmentsController.php'
   and type = 'C';
update module_components
   set title = 'Party Contact Methods'
 where name = 'partycontactmethodscontroller'
   and location = 'modules/public_pages/contacts/controllers/PartycontactmethodsController.php'
   and type = 'C';
update module_components
   set title = 'Party Notes'
 where name = 'partynotescontroller'
   and location = 'modules/public_pages/contacts/controllers/PartynotesController.php'
   and type = 'C';
update module_components
   set title = 'Asset Register Setup'
 where name = 'assetscontroller'
   and location = 'modules/public_pages/erp/asset_register/asset_setup/controllers/AssetsController.php'
   and type = 'C';
update module_components
   set title = 'EDI Data Definition Details'
 where name = 'datadefinitiondetailscontroller'
   and location = 'modules/public_pages/edi/controllers/DatadefinitiondetailsController.php'
   and type = 'C';
update module_components
   set title = 'EDI Data Definitions'
 where name = 'datadefinitionscontroller'
   and location = 'modules/public_pages/edi/controllers/DatadefinitionsController.php'
   and type = 'C';
update module_components
   set title = 'EDI Data Mapping Details'
 where name = 'datamappingdetailscontroller'
   and location = 'modules/public_pages/edi/controllers/DatamappingdetailsController.php'
   and type = 'C';
update module_components
   set title = 'Purchase Order Products'
 where name = 'poproductlineheaderscontroller'
   and location = 'modules/public_pages/erp/order/purchase_order/controllers/PoproductlineheadersController.php'
   and type = 'C';
update module_components
   set title = 'search'
 where name = 'search'
   and location = 'modules/common/templates/elements/search.tpl'
   and type = 'T';
update module_components
   set title = 'Sales Ledger Analyses'
 where name = 'slanalysiscollection'
   and location = 'modules/public_pages/erp/ledger/sales_ledger/models/SLAnalysisCollection.php'
   and type = 'M';
update module_components
   set title = 'Purchase Order Product headers'
 where name = 'poproductlineheadercollection'
   and location = 'modules/public_pages/erp/order/purchase_order/models/POProductlineHeaderCollection.php'
   and type = 'M';
update module_components
   set title = 'Report'
 where name = 'report'
   and location = 'modules/public_pages/reporting/models/Report.php'
   and type = 'M';
update module_components
   set title = 'Reports'
 where name = 'reportcollection'
   and location = 'modules/public_pages/reporting/models/ReportCollection.php'
   and type = 'M';
update module_components
   set title = 'Sales Ledger Allocation'
 where name = 'slallocation'
   and location = 'modules/public_pages/erp/ledger/sales_ledger/models/SLAllocation.php'
   and type = 'M';
update module_components
   set title = 'Sales Ledger Allocations'
 where name = 'slallocationcollection'
   and location = 'modules/public_pages/erp/ledger/sales_ledger/models/SLAllocationCollection.php'
   and type = 'M';
update module_components
   set title = 'Sales Ledger Analysis'
 where name = 'slanalysis'
   and location = 'modules/public_pages/erp/ledger/sales_ledger/models/SLAnalysis.php'
   and type = 'M';
update module_components
   set title = 'Sales Ledger Customer'
 where name = 'slcustomer'
   and location = 'modules/public_pages/erp/ledger/sales_ledger/models/SLCustomer.php'
   and type = 'M';
update module_components
   set title = 'Sales Ledger Customers'
 where name = 'slcustomercollection'
   and location = 'modules/public_pages/erp/ledger/sales_ledger/models/SLCustomerCollection.php'
   and type = 'M';
update module_components
   set title = 'Sales Order Product Header'
 where name = 'soproductlineheader'
   and location = 'modules/public_pages/erp/order/sales_order/models/SOProductlineHeader.php'
   and type = 'M';
update module_components
   set title = 'Sales Order Product Headers'
 where name = 'soproductlineheadercollection'
   and location = 'modules/public_pages/erp/order/sales_order/models/SOProductlineHeaderCollection.php'
   and type = 'M';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/asset_register/asset_setup/templates/assets/index.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/edi/templates/externalsystems/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/reporting/templates/reports/new.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/reporting/templates/reports/view.tpl'
   and type = 'T';
update module_components
   set title = 'run'
 where name = 'run'
   and location = 'modules/public_pages/reporting/templates/reports/run.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/reporting/templates/reports/index.tpl'
   and type = 'T';
update module_components
   set title = 'Parties'
 where name = 'partyscontroller'
   and location = 'modules/public_pages/contacts/controllers/PartysController.php'
   and type = 'C';
update module_components
   set title = 'Sales Ledger Aged Debtors Summary uzlet'
 where name = 'ageddebtorssummaryeglet'
   and location = 'modules/public_pages/erp/ledger/sales_ledger/eglets/agedDebtorsSummaryEGlet.php'
   and type = 'E';
update module_components
   set title = 'Sales Ledger Overcredit Limit uzlet'
 where name = 'overcreditlimiteglet'
   and location = 'modules/public_pages/erp/ledger/sales_ledger/eglets/OverCreditLimitEGlet.php'
   and type = 'E';
update module_components
   set title = 'Sales Ledger Overdue Accounts uzlet'
 where name = 'overdueaccountseglet'
   and location = 'modules/public_pages/erp/ledger/sales_ledger/eglets/OverDueAccountsEGlet.php'
   and type = 'E';
update module_components
   set title = 'General Ledger Account'
 where name = 'glaccount'
   and location = 'modules/public_pages/erp/ledger/general_ledger/models/GLAccount.php'
   and type = 'M';
update module_components
   set title = 'General Ledger Accounts'
 where name = 'glaccountcollection'
   and location = 'modules/public_pages/erp/ledger/general_ledger/models/GLAccountCollection.php'
   and type = 'M';
update module_components
   set title = 'General Ledger Analyses'
 where name = 'glanalysiscollection'
   and location = 'modules/public_pages/erp/ledger/general_ledger/models/GLAnalysisCollection.php'
   and type = 'M';
update module_components
   set title = 'General Ledger Centres'
 where name = 'glcentrecollection'
   and location = 'modules/public_pages/erp/ledger/general_ledger/models/GLCentreCollection.php'
   and type = 'M';
update module_components
   set title = 'General Ledger Period'
 where name = 'glperiod'
   and location = 'modules/public_pages/erp/ledger/general_ledger/models/GLPeriod.php'
   and type = 'M';
update module_components
   set title = 'General Ledger Transactions'
 where name = 'gltransactioncollection'
   and location = 'modules/public_pages/erp/ledger/general_ledger/models/GLTransactionCollection.php'
   and type = 'M';
update module_components
   set title = 'General Ledger Transactions Search'
 where name = 'gltransactionssearch'
   and location = 'modules/public_pages/erp/ledger/general_ledger/models/gltransactionsSearch.php'
   and type = 'M';
update module_components
   set title = 'report columns'
 where name = 'report_columns'
   and location = 'modules/public_pages/reporting/templates/reports/report_columns.tpl'
   and type = 'T';
update module_components
   set title = 'definition tree'
 where name = 'definition_tree'
   and location = 'modules/public_pages/edi/templates/datadefinitions/definition_tree.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/admin/templates/haspermissions/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/admin/templates/haspermissions/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/admin/templates/hasroles/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/admin/templates/hasroles/index.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/admin/templates/objectroles/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/admin/templates/roles/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/admin/templates/roles/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/admin/templates/users/new.tpl'
   and type = 'T';
update module_components
   set title = 'Ticket'
 where name = 'ticket'
   and location = 'modules/public_pages/ticketing/models/Ticket.php'
   and type = 'M';
update module_components
   set title = 'Ticket Category'
 where name = 'ticketcategory'
   and location = 'modules/public_pages/ticketing/models/TicketCategory.php'
   and type = 'M';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/admin/templates/index.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/templates/taxstatuss/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/templates/taxstatuss/new.tpl'
   and type = 'T';
update module_components
   set title = 'EDI Transaction Logs'
 where name = 'editransactionlogscontroller'
   and location = 'modules/public_pages/edi/controllers/EditransactionlogsController.php'
   and type = 'C';
update module_components
   set title = 'Person addresses'
 where name = 'personaddressscontroller'
   and location = 'modules/public_pages/contacts/controllers/PersonaddresssController.php'
   and type = 'C';
update module_components
   set title = 'Person Attachments'
 where name = 'personattachmentscontroller'
   and location = 'modules/public_pages/contacts/controllers/PersonattachmentsController.php'
   and type = 'C';
update module_components
   set title = 'Person Contact Methods'
 where name = 'personcontactmethodscontroller'
   and location = 'modules/public_pages/contacts/controllers/PersoncontactmethodsController.php'
   and type = 'C';
update module_components
   set title = 'Company Selectoreg uzlet'
 where name = 'companyselectoreglet'
   and location = 'modules/public_pages/contacts/eglets/CompanySelectorEGlet.php'
   and type = 'E';
update module_components
   set title = 'Companies Added Todayeguzlet'
 where name = 'companiesaddedtodayeglet'
   and location = 'modules/public_pages/contacts/eglets/CompaniesAddedTodayEGlet.php'
   and type = 'E';
update module_components
   set title = 'Recently Added Companies uzlet'
 where name = 'recentlyaddedcompanieseglet'
   and location = 'modules/public_pages/contacts/eglets/RecentlyAddedCompaniesEGlet.php'
   and type = 'E';
update module_components
   set title = 'Recently Viewed Companies uzlet'
 where name = 'recentlyviewedcompanieseglet'
   and location = 'modules/public_pages/contacts/eglets/RecentlyViewedCompaniesEGlet.php'
   and type = 'E';
update module_components
   set title = 'Recently Viewed People uzlet'
 where name = 'recentlyviewedpeopleeglet'
   and location = 'modules/public_pages/contacts/eglets/RecentlyViewedPeopleEGlet.php'
   and type = 'E';
update module_components
   set title = 'Tickets Weekly by Severity Grapher'
 where name = 'ticketsweeklybyseveritygrapher'
   and location = 'modules/public_pages/ticketing/eglets/TicketsWeeklyBySeverityGrapher.php'
   and type = 'E';
update module_components
   set title = 'Tickets Weekly by Status Grapher'
 where name = 'ticketsweeklybystatusgrapher'
   and location = 'modules/public_pages/ticketing/eglets/TicketsWeeklyByStatusGrapher.php'
   and type = 'E';
update module_components
   set title = 'Unassigned Tickets uzlet'
 where name = 'unassignedticketseglet'
   and location = 'modules/public_pages/ticketing/eglets/UnassignedTicketsEGlet.php'
   and type = 'E';
update module_components
   set title = 'Company Address'
 where name = 'companyaddress'
   and location = 'modules/public_pages/contacts/models/Companyaddress.php'
   and type = 'M';
update module_components
   set title = 'Company Account Statuses'
 where name = 'accountstatuscollection'
   and location = 'modules/public_pages/contacts/models/AccountStatusCollection.php'
   and type = 'M';
update module_components
   set title = 'Company Addresses'
 where name = 'companyaddresscollection'
   and location = 'modules/public_pages/contacts/models/CompanyaddressCollection.php'
   and type = 'M';
update module_components
   set title = 'Company Classification'
 where name = 'companyclassification'
   and location = 'modules/public_pages/contacts/models/CompanyClassification.php'
   and type = 'M';
update module_components
   set title = 'Company Classifications'
 where name = 'companyclassificationcollection'
   and location = 'modules/public_pages/contacts/models/CompanyClassificationCollection.php'
   and type = 'M';
update module_components
   set title = 'Tickets'
 where name = 'ticketcollection'
   and location = 'modules/public_pages/ticketing/models/TicketCollection.php'
   and type = 'M';
update module_components
   set title = 'Ticket Configuration'
 where name = 'ticketconfiguration'
   and location = 'modules/public_pages/ticketing/models/TicketConfiguration.php'
   and type = 'M';
update module_components
   set title = 'Ticket Module Version'
 where name = 'ticketmoduleversion'
   and location = 'modules/public_pages/ticketing/models/TicketModuleVersion.php'
   and type = 'M';
update module_components
   set title = 'Ticket Priority'
 where name = 'ticketpriority'
   and location = 'modules/public_pages/ticketing/models/TicketPriority.php'
   and type = 'M';
update module_components
   set title = 'Ticket Module Versions'
 where name = 'ticketmoduleversioncollection'
   and location = 'modules/public_pages/ticketing/models/TicketModuleVersionCollection.php'
   and type = 'M';
update module_components
   set title = 'Ticket Configurations'
 where name = 'ticketconfigurationcollection'
   and location = 'modules/public_pages/ticketing/models/TicketConfigurationCollection.php'
   and type = 'M';
update module_components
   set title = 'Ticket Categories'
 where name = 'ticketcategorycollection'
   and location = 'modules/public_pages/ticketing/models/TicketCategoryCollection.php'
   and type = 'M';
update module_components
   set title = 'Import Ticket'
 where name = 'xmlrpcticket'
   and location = 'modules/public_pages/ticketing/models/xmlrpcTicket.php'
   and type = 'M';
update module_components
   set title = 'view sales orders'
 where name = 'viewsales_orders'
   and location = 'modules/public_pages/erp/manufacturing/templates/stitems/viewsales_orders.tpl'
   and type = 'T';
update module_components
   set title = 'Preference Object'
 where name = 'preferenceobject'
   and location = 'modules/common/models/PreferenceObject.php'
   and type = 'M';
update module_components
   set title = 'eglet'
 where name = 'eglet'
   and location = 'modules/common/templates/eglets/eglet.tpl'
   and type = 'T';
update module_components
   set title = 'Companies'
 where name = 'companycollection'
   and location = 'modules/public_pages/contacts/models/CompanyCollection.php'
   and type = 'M';
update module_components
   set title = 'Company Contact Method'
 where name = 'companycontactmethod'
   and location = 'modules/public_pages/contacts/models/Companycontactmethod.php'
   and type = 'M';
update module_components
   set title = 'Country'
 where name = 'country'
   and location = 'modules/common/models/Country.php'
   and type = 'M';
update module_components
   set title = 'Countries'
 where name = 'countrycollection'
   and location = 'modules/common/models/CountryCollection.php'
   and type = 'M';
update module_components
   set title = 'Currency'
 where name = 'currency'
   and location = 'modules/common/models/Currency.php'
   and type = 'M';
update module_components
   set title = 'Currencies'
 where name = 'currencycollection'
   and location = 'modules/common/models/CurrencyCollection.php'
   and type = 'M';
update module_components
   set title = 'Entity Attachment'
 where name = 'entityattachment'
   and location = 'modules/common/models/EntityAttachment.php'
   and type = 'M';
update module_components
   set title = 'Entity Attachments'
 where name = 'entityattachmentcollection'
   and location = 'modules/common/models/EntityAttachmentCollection.php'
   and type = 'M';
update module_components
   set title = 'System File'
 where name = 'file'
   and location = 'modules/common/models/File.php'
   and type = 'M';
update module_components
   set title = 'Language'
 where name = 'language'
   and location = 'modules/common/models/Language.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Warehouse Actions'
 where name = 'whactions'
   and location = 'modules/common/templates/eglets/whactions.tpl'
   and type = 'T';
update module_components
   set title = 'system admin quick links'
 where name = 'system_admin_quick_links'
   and location = 'modules/common/templates/eglets/system_admin_quick_links.tpl'
   and type = 'T';
update module_components
   set title = 'sales order summary'
 where name = 'sorders_summary'
   and location = 'modules/common/templates/eglets/sorders_summary.tpl'
   and type = 'T';
update module_components
   set title = 'sales order overview'
 where name = 'sorders_overview'
   and location = 'modules/common/templates/eglets/sorders_overview.tpl'
   and type = 'T';
update module_components
   set title = 'sales ledger quick links'
 where name = 'sl_quick_links'
   and location = 'modules/common/templates/eglets/sl_quick_links.tpl'
   and type = 'T';
update module_components
   set title = 'sales invoice quick links'
 where name = 'si_quick_links'
   and location = 'modules/common/templates/eglets/si_quick_links.tpl'
   and type = 'T';
update module_components
   set title = 'purchase order setup quick links'
 where name = 'posetup_quick_links'
   and location = 'modules/common/templates/eglets/posetup_quick_links.tpl'
   and type = 'T';
update module_components
   set title = 'purchase orders list'
 where name = 'porders_list'
   and location = 'modules/common/templates/eglets/porders_list.tpl'
   and type = 'T';
update module_components
   set title = 'purchase orders auth requisition'
 where name = 'porders_auth_requisition'
   and location = 'modules/common/templates/eglets/porders_auth_requisition.tpl'
   and type = 'T';
update module_components
   set title = 'purchase order quick links'
 where name = 'po_quick_links'
   and location = 'modules/common/templates/eglets/po_quick_links.tpl'
   and type = 'T';
update module_components
   set title = 'purchase ledger quick links'
 where name = 'pl_quick_links'
   and location = 'modules/common/templates/eglets/pl_quick_links.tpl'
   and type = 'T';
update module_components
   set title = 'purchase invoice quick links'
 where name = 'pi_quick_links'
   and location = 'modules/common/templates/eglets/pi_quick_links.tpl'
   and type = 'T';
update module_components
   set title = 'opsetup quick links'
 where name = 'opsetup_quick_links'
   and location = 'modules/common/templates/eglets/opsetup_quick_links.tpl'
   and type = 'T';
update module_components
   set title = 'multi bin balances print'
 where name = 'multi_bin_balances_print'
   and location = 'modules/common/templates/eglets/multi_bin_balances_print.tpl'
   and type = 'T';
update module_components
   set title = 'menu eglet'
 where name = 'menu_eglet'
   and location = 'modules/common/templates/eglets/menu_eglet.tpl'
   and type = 'T';
update module_components
   set title = 'manufacturing quick links'
 where name = 'manufacturing_quick_links'
   and location = 'modules/common/templates/eglets/manufacturing_quick_links.tpl'
   and type = 'T';
update module_components
   set title = 'mansetup quick links'
 where name = 'mansetup_quick_links'
   and location = 'modules/common/templates/eglets/mansetup_quick_links.tpl'
   and type = 'T';
update module_components
   set title = 'list eglet'
 where name = 'list_eglet'
   and location = 'modules/common/templates/eglets/list_eglet.tpl'
   and type = 'T';
update module_components
   set title = 'invoice list'
 where name = 'invoice_list'
   and location = 'modules/common/templates/eglets/invoice_list.tpl'
   and type = 'T';
update module_components
   set title = 'image eglet'
 where name = 'image_eglet'
   and location = 'modules/common/templates/eglets/image_eglet.tpl'
   and type = 'T';
update module_components
   set title = 'eglet paging'
 where name = 'egletpaging'
   and location = 'modules/common/templates/eglets/egletpaging.tpl'
   and type = 'T';
update module_components
   set title = 'general ledger quick links'
 where name = 'gl_quick_links'
   and location = 'modules/common/templates/eglets/gl_quick_links.tpl'
   and type = 'T';
update module_components
   set title = 'general ledger setup quick links'
 where name = 'glsetup_quick_links'
   and location = 'modules/common/templates/eglets/glsetup_quick_links.tpl'
   and type = 'T';
update module_components
   set title = 'customer service quick links'
 where name = 'customer_service_quick_links'
   and location = 'modules/common/templates/eglets/customer_service_quick_links.tpl'
   and type = 'T';
update module_components
   set title = 'current tickets client'
 where name = 'current_tickets_client'
   and location = 'modules/common/templates/eglets/current_tickets_client.tpl'
   and type = 'T';
update module_components
   set title = 'contacts quick links'
 where name = 'contacts_quick_links'
   and location = 'modules/common/templates/eglets/contacts_quick_links.tpl'
   and type = 'T';
update module_components
   set title = 'client ticket quick entry'
 where name = 'client_ticket_quick_entry'
   and location = 'modules/common/templates/eglets/client_ticket_quick_entry.tpl'
   and type = 'T';
update module_components
   set title = 'cashbook quick links'
 where name = 'cashbook_quick_links'
   and location = 'modules/common/templates/eglets/cashbook_quick_links.tpl'
   and type = 'T';
update module_components
   set title = 'aged balance summary'
 where name = 'agedbalancesummary'
   and location = 'modules/common/templates/eglets/agedBalanceSummary.tpl'
   and type = 'T';
update module_components
   set title = 'admin quick links'
 where name = 'admin_quick_links'
   and location = 'modules/common/templates/eglets/admin_quick_links.tpl'
   and type = 'T';
update module_components
   set title = 'graph'
 where name = 'graph'
   and location = 'modules/common/templates/eglets/graph.tpl'
   and type = 'T';
update module_components
   set title = 'paging'
 where name = 'paging'
   and location = 'modules/common/templates/elements/paging.tpl'
   and type = 'T';
update module_components
   set title = 'sidebar'
 where name = 'sidebar'
   and location = 'modules/common/templates/elements/sidebar.tpl'
   and type = 'T';
update module_components
   set title = 'datatable'
 where name = 'datatable'
   and location = 'modules/common/templates/elements/datatable.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/common/templates/elements/index.tpl'
   and type = 'T';
update module_components
   set title = 'calendar'
 where name = 'calendar'
   and location = 'modules/common/templates/elements/calendar.tpl'
   and type = 'T';
update module_components
   set title = 'list'
 where name = 'list'
   and location = 'modules/common/templates/elements/list.tpl'
   and type = 'T';
update module_components
   set title = 'dashboard'
 where name = 'dashboard'
   and location = 'modules/common/templates/elements/dashboard.tpl'
   and type = 'T';
update module_components
   set title = 'works order book completed list'
 where name = 'worders_book_completed_list'
   and location = 'modules/common/templates/eglets/worders_book_completed_list.tpl'
   and type = 'T';
update module_components
   set title = 'works order book overunder list'
 where name = 'worders_book_overunder_list'
   and location = 'modules/common/templates/eglets/worders_book_overunder_list.tpl'
   and type = 'T';
update module_components
   set title = 'works order print paperwork list'
 where name = 'worders_print_paperwork_list'
   and location = 'modules/common/templates/eglets/worders_print_paperwork_list.tpl'
   and type = 'T';
update module_components
   set title = 'works order backflush errors list'
 where name = 'worders_backflush_errors_list'
   and location = 'modules/common/templates/eglets/worders_backflush_errors_list.tpl'
   and type = 'T';
update module_components
   set title = 'vat quick links'
 where name = 'vat_quick_links'
   and location = 'modules/common/templates/eglets/vat_quick_links.tpl'
   and type = 'T';
update module_components
   set title = 'ticketing quick links'
 where name = 'ticketing_quick_links'
   and location = 'modules/common/templates/eglets/ticketing_quick_links.tpl'
   and type = 'T';
update module_components
   set title = 'tax quick links'
 where name = 'tax_quick_links'
   and location = 'modules/common/templates/eglets/tax_quick_links.tpl'
   and type = 'T';
update module_components
   set title = 'status list eglet'
 where name = 'status_list_eglet'
   and location = 'modules/common/templates/eglets/status_list_eglet.tpl'
   and type = 'T';
update module_components
   set title = 'sales order list'
 where name = 'sorders_list'
   and location = 'modules/common/templates/eglets/sorders_list.tpl'
   and type = 'T';
update module_components
   set title = 'salses order quick links'
 where name = 'so_quick_links'
   and location = 'modules/common/templates/eglets/so_quick_links.tpl'
   and type = 'T';
update module_components
   set title = 'salses ledger customer list'
 where name = 'slcustomer_list'
   and location = 'modules/common/templates/eglets/slcustomer_list.tpl'
   and type = 'T';
update module_components
   set title = 'quick search'
 where name = 'quicksearch'
   and location = 'modules/common/templates/elements/quicksearch.tpl'
   and type = 'T';
update module_components
   set title = 'periodic payment list'
 where name = 'pp_list'
   and location = 'modules/common/templates/eglets/pp_list.tpl'
   and type = 'T';
update module_components
   set title = 'purchase order received value'
 where name = 'po_received_value'
   and location = 'modules/common/templates/eglets/po_received_value.tpl'
   and type = 'T';
update module_components
   set title = 'purchase order lines list'
 where name = 'porderlines_list'
   and location = 'modules/common/templates/eglets/porderlines_list.tpl'
   and type = 'T';
update module_components
   set title = 'overdue accounts'
 where name = 'overdue_accounts'
   and location = 'modules/common/templates/eglets/overdue_accounts.tpl'
   and type = 'T';
update module_components
   set title = 'over credit limit'
 where name = 'over_credit_limit'
   and location = 'modules/common/templates/eglets/over_credit_limit.tpl'
   and type = 'T';
update module_components
   set title = 'order item summary'
 where name = 'orderitemsummary'
   and location = 'modules/common/templates/eglets/orderitemsummary.tpl'
   and type = 'T';
update module_components
   set title = 'inline dashboard'
 where name = 'inline_dashboard'
   and location = 'modules/common/templates/elements/inline_dashboard.tpl'
   and type = 'T';
update module_components
   set title = 'hr quick links'
 where name = 'hr_quick_links'
   and location = 'modules/common/templates/eglets/hr_quick_links.tpl'
   and type = 'T';
update module_components
   set title = 'gr quick links'
 where name = 'gr_quick_links'
   and location = 'modules/common/templates/eglets/gr_quick_links.tpl'
   and type = 'T';
update module_components
   set title = 'despatch quick links'
 where name = 'despatch_quick_links'
   and location = 'modules/common/templates/eglets/despatch_quick_links.tpl'
   and type = 'T';
update module_components
   set title = 'datatable with delete'
 where name = 'datatable_with_delete'
   and location = 'modules/common/templates/elements/datatable_with_delete.tpl'
   and type = 'T';
update module_components
   set title = 'current tickets'
 where name = 'current_tickets'
   and location = 'modules/common/templates/eglets/current_tickets.tpl'
   and type = 'T';
update module_components
   set title = 'currency quick links'
 where name = 'currency_quick_links'
   and location = 'modules/common/templates/eglets/currency_quick_links.tpl'
   and type = 'T';
update module_components
   set title = 'crm quick links'
 where name = 'crm_quick_links'
   and location = 'modules/common/templates/eglets/crm_quick_links.tpl'
   and type = 'T';
update module_components
   set title = 'costing quick links'
 where name = 'costing_quick_links'
   and location = 'modules/common/templates/eglets/costing_quick_links.tpl'
   and type = 'T';
update module_components
   set title = 'company selector'
 where name = 'company_selector'
   and location = 'modules/common/templates/eglets/company_selector.tpl'
   and type = 'T';
update module_components
   set title = 'company selector'
 where name = 'companyselector'
   and location = 'modules/common/templates/elements/companyselector.tpl'
   and type = 'T';
update module_components
   set title = 'cash quick links'
 where name = 'cash_quick_links'
   and location = 'modules/common/templates/eglets/cash_quick_links.tpl'
   and type = 'T';
update module_components
   set title = 'audit fields'
 where name = 'auditfields'
   and location = 'modules/common/templates/elements/auditfields.tpl'
   and type = 'T';
update module_components
   set title = 'asset register setup quick links'
 where name = 'arsetup_quick_links'
   and location = 'modules/common/templates/eglets/arsetup_quick_links.tpl'
   and type = 'T';
update module_components
   set title = 'asset register quick links'
 where name = 'ar_quick_links'
   and location = 'modules/common/templates/eglets/ar_quick_links.tpl'
   and type = 'T';
update module_components
   set title = 'advanced search'
 where name = 'advancedsearch'
   and location = 'modules/common/templates/elements/advancedSearch.tpl'
   and type = 'T';
update module_components
   set title = 'tree'
 where name = 'tree'
   and location = 'modules/common/templates/elements/tree.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/login/templates/index.tpl'
   and type = 'T';
update module_components
   set title = 'password'
 where name = 'password'
   and location = 'modules/public_pages/login/templates/password.tpl'
   and type = 'T';
update module_components
   set title = 'Purchase Order Authority Account'
 where name = 'poauthaccount'
   and location = 'modules/public_pages/erp/order/purchase_order/models/POAuthAccount.php'
   and type = 'M';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/order/purchase_order/templates/porders/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/order/purchase_order/templates/porders/new.tpl'
   and type = 'T';
update module_components
   set title = 'profile'
 where name = 'profile'
   and location = 'modules/public_pages/erp/order/purchase_order/templates/porders/profile.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/erp/order/purchase_order/templates/porders/view.tpl'
   and type = 'T';
update module_components
   set title = 'Purchase Order Authority Accounts'
 where name = 'poauthaccountcollection'
   and location = 'modules/public_pages/erp/order/purchase_order/models/POAuthAccountCollection.php'
   and type = 'M';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/projects/templates/tasks/index.tpl'
   and type = 'T';
update module_components
   set title = 'Login'
 where name = 'indexcontroller'
   and location = 'modules/public_pages/login/controllers/IndexController.php'
   and type = 'C';
update module_components
   set title = 'Purchase Order Lines'
 where name = 'porderlinescontroller'
   and location = 'modules/public_pages/erp/order/purchase_order/controllers/PorderlinesController.php'
   and type = 'C';
update module_components
   set title = 'Sidebar'
 where name = 'sidebarcontroller'
   and location = 'modules/common/controllers/SidebarController.php'
   and type = 'C';
update module_components
   set title = 'Purchase Orders Due Today uzlet'
 where name = 'pordersduetodayeglet'
   and location = 'modules/public_pages/erp/order/purchase_order/eglets/POrdersDueTodayEGlet.php'
   and type = 'E';
update module_components
   set title = 'Purchase Orders with no Authoriser uzlet'
 where name = 'pordersnoauthusereglet'
   and location = 'modules/public_pages/erp/order/purchase_order/eglets/POrdersNoAuthUserEGlet.php'
   and type = 'E';
update module_components
   set title = 'Purchase Orders Not Acknowledged uzlet'
 where name = 'pordersnotacknowledgedeglet'
   and location = 'modules/public_pages/erp/order/purchase_order/eglets/POrdersNotAcknowledgedEGlet.php'
   and type = 'E';
update module_components
   set title = 'Purchase Orders Overdue uzlet'
 where name = 'pordersoverdueeglet'
   and location = 'modules/public_pages/erp/order/purchase_order/eglets/POrdersOverdueEGlet.php'
   and type = 'E';
update module_components
   set title = 'Purchase Orders Received by Value uzlet'
 where name = 'pordersreceivedvalueeglet'
   and location = 'modules/public_pages/erp/order/purchase_order/eglets/POrdersReceivedValueEGlet.php'
   and type = 'E';
update module_components
   set title = 'Contact Category'
 where name = 'contactcategory'
   and location = 'modules/public_pages/contacts/models/Contactcategory.php'
   and type = 'M';
update module_components
   set title = 'EDI Interface'
 where name = 'ediinterface'
   and location = 'modules/public_pages/edi/models/EdiInterface.php'
   and type = 'M';
update module_components
   set title = 'Engineering Resource'
 where name = 'engineeringresource'
   and location = 'modules/public_pages/engineering/models/EngineeringResource.php'
   and type = 'M';
update module_components
   set title = 'Languages'
 where name = 'languagecollection'
   and location = 'modules/common/models/LanguageCollection.php'
   and type = 'M';
update module_components
   set title = 'Purchase Order Authority Limit'
 where name = 'poauthlimit'
   and location = 'modules/public_pages/erp/order/purchase_order/models/POAuthLimit.php'
   and type = 'M';
update module_components
   set title = 'Purchase Order Authority Limits'
 where name = 'poauthlimitcollection'
   and location = 'modules/public_pages/erp/order/purchase_order/models/POAuthLimitCollection.php'
   and type = 'M';
update module_components
   set title = 'Purchase Order Authority Limits Search'
 where name = 'poauthlimitssearch'
   and location = 'modules/public_pages/erp/order/purchase_order/models/poauthlimitsSearch.php'
   and type = 'M';
update module_components
   set title = 'Project Resource Type'
 where name = 'resourcetype'
   and location = 'modules/public_pages/projects/models/Resourcetype.php'
   and type = 'M';
update module_components
   set title = 'System Company Settings'
 where name = 'systemcompanysettings'
   and location = 'modules/common/models/SystemCompanySettings.php'
   and type = 'M';
update module_components
   set title = 'Ticket Queue'
 where name = 'ticketqueue'
   and location = 'modules/public_pages/ticketing/models/TicketQueue.php'
   and type = 'M';
update module_components
   set title = 'Ticket Priorities'
 where name = 'ticketprioritycollection'
   and location = 'modules/public_pages/ticketing/models/TicketPriorityCollection.php'
   and type = 'M';
update module_components
   set title = 'User Company Access'
 where name = 'usercompanyaccess'
   and location = 'modules/common/models/Usercompanyaccess.php'
   and type = 'M';
update module_components
   set title = 'view by worders'
 where name = 'viewbyworders'
   and location = 'modules/public_pages/erp/order/purchase_order/templates/porders/viewbyworders.tpl'
   and type = 'T';
update module_components
   set title = 'view by items'
 where name = 'viewbyitems'
   and location = 'modules/public_pages/erp/order/purchase_order/templates/porders/viewbyitems.tpl'
   and type = 'T';
update module_components
   set title = 'view by dates'
 where name = 'viewbydates'
   and location = 'modules/public_pages/erp/order/purchase_order/templates/porders/viewbydates.tpl'
   and type = 'T';
update module_components
   set title = 'update lines'
 where name = 'updatelines'
   and location = 'modules/public_pages/erp/order/purchase_order/templates/porders/updatelines.tpl'
   and type = 'T';
update module_components
   set title = 'sub nav'
 where name = 'sub_nav'
   and location = 'modules/common/templates/elements/sub_nav.tpl'
   and type = 'T';
update module_components
   set title = 'review orders'
 where name = 'revieworders'
   and location = 'modules/public_pages/erp/order/purchase_order/templates/porders/revieworders.tpl'
   and type = 'T';
update module_components
   set title = 'get accounts'
 where name = 'getaccounts'
   and location = 'modules/public_pages/erp/order/purchase_order/templates/poauthlimits/getaccounts.tpl'
   and type = 'T';
update module_components
   set title = 'datatable collapsible'
 where name = 'datatable_collapsible'
   and location = 'modules/common/templates/elements/datatable_collapsible.tpl'
   and type = 'T';
update module_components
   set title = 'create invoice'
 where name = 'createinvoice'
   and location = 'modules/public_pages/erp/order/purchase_order/templates/porders/createinvoice.tpl'
   and type = 'T';
update module_components
   set title = 'chained select'
 where name = 'chained_select'
   and location = 'modules/common/templates/elements/chained_select.tpl'
   and type = 'T';
update module_components
   set title = 'cancel form'
 where name = 'cancelform'
   and location = 'modules/common/templates/elements/cancelForm.tpl'
   and type = 'T';
update module_components
   set title = 'Purchase Order Awaiting Authorisation'
 where name = 'poawaitingauth'
   and location = 'modules/public_pages/erp/order/purchase_order/models/POAwaitingAuth.php'
   and type = 'M';
update module_components
   set title = 'Calendar Event Attendees'
 where name = 'calendareventattendeescontroller'
   and location = 'modules/public_pages/calendar/controllers/CalendareventattendeesController.php'
   and type = 'C';
update module_components
   set title = 'Company Contact Methods'
 where name = 'companycontactmethodcollection'
   and location = 'modules/public_pages/contacts/models/CompanycontactmethodCollection.php'
   and type = 'M';
update module_components
   set title = 'Sales Order Packing Slips'
 where name = 'sopackingslipscontroller'
   and location = 'modules/public_pages/erp/order/sales_order/controllers/SopackingslipsController.php'
   and type = 'C';
update module_components
   set title = 'Company CRM'
 where name = 'companycrm'
   and location = 'modules/public_pages/contacts/models/CompanyCrm.php'
   and type = 'M';
update module_components
   set title = 'Company in Categories List'
 where name = 'companyincategoriescollection'
   and location = 'modules/public_pages/contacts/models/CompanyInCategoriesCollection.php'
   and type = 'M';
update module_components
   set title = 'Company Industry'
 where name = 'companyindustry'
   and location = 'modules/public_pages/contacts/models/CompanyIndustry.php'
   and type = 'M';
update module_components
   set title = 'Company Categories'
 where name = 'companyincategories'
   and location = 'modules/public_pages/contacts/models/CompanyInCategories.php'
   and type = 'M';
update module_components
   set title = 'Company Industry List'
 where name = 'companyindustrycollection'
   and location = 'modules/public_pages/contacts/models/CompanyIndustryCollection.php'
   and type = 'M';
update module_components
   set title = 'Company Note'
 where name = 'companynote'
   and location = 'modules/public_pages/contacts/models/CompanyNote.php'
   and type = 'M';
update module_components
   set title = 'Company Search'
 where name = 'companysearch'
   and location = 'modules/public_pages/contacts/models/CompanySearch.php'
   and type = 'M';
update module_components
   set title = 'Company Source'
 where name = 'companysource'
   and location = 'modules/public_pages/contacts/models/CompanySource.php'
   and type = 'M';
update module_components
   set title = 'Company Sources'
 where name = 'companysourcecollection'
   and location = 'modules/public_pages/contacts/models/CompanySourceCollection.php'
   and type = 'M';
update module_components
   set title = 'Company Status'
 where name = 'companystatus'
   and location = 'modules/public_pages/contacts/models/CompanyStatus.php'
   and type = 'M';
update module_components
   set title = 'Company Statuses'
 where name = 'companystatuscollection'
   and location = 'modules/public_pages/contacts/models/CompanyStatusCollection.php'
   and type = 'M';
update module_components
   set title = 'Company Type'
 where name = 'companytype'
   and location = 'modules/public_pages/contacts/models/CompanyType.php'
   and type = 'M';
update module_components
   set title = 'Company Types'
 where name = 'companytypecollection'
   and location = 'modules/public_pages/contacts/models/CompanyTypeCollection.php'
   and type = 'M';
update module_components
   set title = 'Contact Categories'
 where name = 'contactcategorycollection'
   and location = 'modules/public_pages/contacts/models/ContactcategoryCollection.php'
   and type = 'M';
update module_components
   set title = 'People in Categories'
 where name = 'peopleincategories'
   and location = 'modules/public_pages/contacts/models/PeopleInCategories.php'
   and type = 'M';
update module_components
   set title = 'People Search'
 where name = 'peoplesearch'
   and location = 'modules/public_pages/contacts/models/PeopleSearch.php'
   and type = 'M';
update module_components
   set title = 'Person'
 where name = 'person'
   and location = 'modules/public_pages/contacts/models/Person.php'
   and type = 'M';
update module_components
   set title = 'Person Address'
 where name = 'personaddress'
   and location = 'modules/public_pages/contacts/models/Personaddress.php'
   and type = 'M';
update module_components
   set title = 'Person Addresses'
 where name = 'personaddresscollection'
   and location = 'modules/public_pages/contacts/models/PersonaddressCollection.php'
   and type = 'M';
update module_components
   set title = 'Purchase Orders Awaiting Authorisation'
 where name = 'poawaitingauthcollection'
   and location = 'modules/public_pages/erp/order/purchase_order/models/POAwaitingAuthCollection.php'
   and type = 'M';
update module_components
   set title = 'Purchase Order Product Line'
 where name = 'poproductline'
   and location = 'modules/public_pages/erp/order/purchase_order/models/POProductline.php'
   and type = 'M';
update module_components
   set title = 'Purchase Order Product Lines'
 where name = 'poproductlinecollection'
   and location = 'modules/public_pages/erp/order/purchase_order/models/POProductlineCollection.php'
   and type = 'M';
update module_components
   set title = 'Purchase Order'
 where name = 'porder'
   and location = 'modules/public_pages/erp/order/purchase_order/models/POrder.php'
   and type = 'M';
update module_components
   set title = 'Purchase Orders'
 where name = 'pordercollection'
   and location = 'modules/public_pages/erp/order/purchase_order/models/POrderCollection.php'
   and type = 'M';
update module_components
   set title = 'Purchase Order Line'
 where name = 'porderline'
   and location = 'modules/public_pages/erp/order/purchase_order/models/POrderLine.php'
   and type = 'M';
update module_components
   set title = 'Purchase Orders Search'
 where name = 'porderssearch'
   and location = 'modules/public_pages/erp/order/purchase_order/models/pordersSearch.php'
   and type = 'M';
update module_components
   set title = 'Projects'
 where name = 'projectcollection'
   and location = 'modules/public_pages/projects/models/ProjectCollection.php'
   and type = 'M';
update module_components
   set title = 'Project Cost Charge'
 where name = 'projectcostcharge'
   and location = 'modules/public_pages/projects/models/ProjectCostCharge.php'
   and type = 'M';
update module_components
   set title = 'Project Cost Charges'
 where name = 'projectcostchargecollection'
   and location = 'modules/public_pages/projects/models/ProjectCostChargeCollection.php'
   and type = 'M';
update module_components
   set title = 'Project Cost Charges Search'
 where name = 'projectcostchargesearch'
   and location = 'modules/public_pages/projects/models/ProjectcostchargeSearch.php'
   and type = 'M';
update module_components
   set title = 'Project Equipment'
 where name = 'projectequipment'
   and location = 'modules/public_pages/projects/models/ProjectEquipment.php'
   and type = 'M';
update module_components
   set title = 'Project Equipment Allocation'
 where name = 'projectequipmentallocation'
   and location = 'modules/public_pages/projects/models/ProjectEquipmentAllocation.php'
   and type = 'M';
update module_components
   set title = 'Project Equipment Allocations'
 where name = 'projectequipmentallocationcollection'
   and location = 'modules/public_pages/projects/models/ProjectEquipmentAllocationCollection.php'
   and type = 'M';
update module_components
   set title = 'Ticket Queues'
 where name = 'ticketqueuecollection'
   and location = 'modules/public_pages/ticketing/models/TicketQueueCollection.php'
   and type = 'M';
update module_components
   set title = 'Ticket Response'
 where name = 'ticketresponse'
   and location = 'modules/public_pages/ticketing/models/TicketResponse.php'
   and type = 'M';
update module_components
   set title = 'Productlines Search'
 where name = 'productlinessearch'
   and location = 'modules/public_pages/erp/order/models/productlinesSearch.php'
   and type = 'M';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/erp/manufacturing/templates/whstores/view.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/manufacturing/templates/whtransfers/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/manufacturing/templates/whtransfers/new.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/calendar/templates/calendareventattachments/new.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/calendar/templates/calendareventattachments/view.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/calendar/templates/calendareventattendees/new.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/calendar/templates/calendarevents/new.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/calendar/templates/calendarevents/view.tpl'
   and type = 'T';
update module_components
   set title = 'Calendar Index'
 where name = 'indexcontroller'
   and location = 'modules/public_pages/calendar/controllers/IndexController.php'
   and type = 'C';
update module_components
   set title = 'Calendars'
 where name = 'calendarcollection'
   and location = 'modules/public_pages/calendar/models/CalendarCollection.php'
   and type = 'M';
update module_components
   set title = 'Calendar Event'
 where name = 'calendarevent'
   and location = 'modules/public_pages/calendar/models/CalendarEvent.php'
   and type = 'M';
update module_components
   set title = 'Calendar Event Attendee'
 where name = 'calendareventattendee'
   and location = 'modules/public_pages/calendar/models/CalendarEventAttendee.php'
   and type = 'M';
update module_components
   set title = 'Calendar Event Attendees'
 where name = 'calendareventattendeecollection'
   and location = 'modules/public_pages/calendar/models/CalendarEventAttendeeCollection.php'
   and type = 'M';
update module_components
   set title = 'Calendar Events'
 where name = 'calendareventcollection'
   and location = 'modules/public_pages/calendar/models/CalendarEventCollection.php'
   and type = 'M';
update module_components
   set title = 'Calendar Preferences'
 where name = 'calendarpreferences'
   and location = 'modules/public_pages/calendar/models/CalendarPreferences.php'
   and type = 'M';
update module_components
   set title = 'Calendar Search'
 where name = 'calendarsearch'
   and location = 'modules/public_pages/calendar/models/CalendarSearch.php'
   and type = 'M';
update module_components
   set title = 'Calendar Share'
 where name = 'calendarshare'
   and location = 'modules/public_pages/calendar/models/CalendarShare.php'
   and type = 'M';
update module_components
   set title = 'Holiday Extra Day'
 where name = 'holidayextraday'
   and location = 'modules/public_pages/hr/models/Holidayextraday.php'
   and type = 'M';
update module_components
   set title = 'Party Note Search'
 where name = 'partynotessearch'
   and location = 'modules/public_pages/contacts/models/PartynotesSearch.php'
   and type = 'M';
update module_components
   set title = 'People'
 where name = 'personcollection'
   and location = 'modules/public_pages/contacts/models/PersonCollection.php'
   and type = 'M';
update module_components
   set title = 'Person Contact Method'
 where name = 'personcontactmethod'
   and location = 'modules/public_pages/contacts/models/Personcontactmethod.php'
   and type = 'M';
update module_components
   set title = 'Person Contact Methods'
 where name = 'personcontactmethodcollection'
   and location = 'modules/public_pages/contacts/models/PersoncontactmethodCollection.php'
   and type = 'M';
update module_components
   set title = 'Person Note'
 where name = 'personnote'
   and location = 'modules/public_pages/contacts/models/PersonNote.php'
   and type = 'M';
update module_components
   set title = 'Person Notes'
 where name = 'personnotecollection'
   and location = 'modules/public_pages/contacts/models/PersonNoteCollection.php'
   and type = 'M';
update module_components
   set title = 'Sales/Purchase Order'
 where name = 'sporder'
   and location = 'modules/public_pages/erp/order/models/SPOrder.php'
   and type = 'M';
update module_components
   set title = 'Sales Purchase Order Line'
 where name = 'sporderline'
   and location = 'modules/public_pages/erp/order/models/SPOrderLine.php'
   and type = 'M';
update module_components
   set title = 'Ticket Responses'
 where name = 'ticketresponsecollection'
   and location = 'modules/public_pages/ticketing/models/TicketResponseCollection.php'
   and type = 'M';
update module_components
   set title = 'Ticket Severity'
 where name = 'ticketseverity'
   and location = 'modules/public_pages/ticketing/models/TicketSeverity.php'
   and type = 'M';
update module_components
   set title = 'Bulk Order Form'
 where name = 'blkorderform'
   and location = 'modules/public_pages/erp/manufacturing/reports/BLKOrderForm.php'
   and type = 'R';
update module_components
   set title = 'DFC Order Form'
 where name = 'dfcorderform'
   and location = 'modules/public_pages/erp/manufacturing/reports/DFCOrderForm.php'
   and type = 'R';
update module_components
   set title = 'Dry Wipe Quality Control Sheet'
 where name = 'drywipequalitycontrolsheet'
   and location = 'modules/public_pages/erp/manufacturing/reports/DrywipeQualityControlSheet.php'
   and type = 'R';
update module_components
   set title = 'Hurricane Order Form'
 where name = 'hurricaneorderform'
   and location = 'modules/public_pages/erp/manufacturing/reports/HurricaneOrderForm.php'
   and type = 'R';
update module_components
   set title = 'view work orders'
 where name = 'viewworkorders'
   and location = 'modules/public_pages/erp/manufacturing/templates/stitems/viewworkorders.tpl'
   and type = 'T';
update module_components
   set title = 'view uom conversions'
 where name = 'viewuom_conversions'
   and location = 'modules/public_pages/erp/manufacturing/templates/stitems/viewuom_conversions.tpl'
   and type = 'T';
update module_components
   set title = 'view transactions'
 where name = 'viewtransactions'
   and location = 'modules/public_pages/erp/manufacturing/templates/whlocations/viewtransactions.tpl'
   and type = 'T';
update module_components
   set title = 'view balances'
 where name = 'viewbalances'
   and location = 'modules/public_pages/erp/manufacturing/templates/whlocations/viewbalances.tpl'
   and type = 'T';
update module_components
   set title = 'view balance'
 where name = 'view_balance'
   and location = 'modules/public_pages/erp/manufacturing/templates/sttransactions/view_balance.tpl'
   and type = 'T';
update module_components
   set title = 'edit gcal'
 where name = 'edit_gcal'
   and location = 'modules/public_pages/calendar/templates/calendars/edit_gcal.tpl'
   and type = 'T';
update module_components
   set title = 'confirm collision'
 where name = 'confirm_collision'
   and location = 'modules/public_pages/calendar/templates/calendarevents/confirm_collision.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/calendar/templates/index/index.tpl'
   and type = 'T';
update module_components
   set title = 'Sales Ledger Customer Search'
 where name = 'slcustomersearch'
   and location = 'modules/public_pages/erp/ledger/sales_ledger/models/SLCustomerSearch.php'
   and type = 'M';
update module_components
   set title = 'calendar'
 where name = 'calendar'
   and location = 'modules/public_pages/calendar/resources/css/calendar.css'
   and type = 'S';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/erp/ledger/general_ledger/templates/glaccounts/view.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/ledger/general_ledger/templates/glaccounts/index.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/ledger/general_ledger/templates/glbalances/index.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/ledger/general_ledger/templates/glbudgets/index.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/ledger/general_ledger/templates/index.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/erp/ledger/general_ledger/templates/glcentres/view.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/ledger/general_ledger/templates/glcentres/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/ledger/general_ledger/templates/gltransactions/new.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/erp/ledger/general_ledger/templates/gltransactions/view.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/ledger/general_ledger/templates/gltransactions/index.tpl'
   and type = 'T';
update module_components
   set title = 'Ticket Severities'
 where name = 'ticketseveritycollection'
   and location = 'modules/public_pages/ticketing/models/TicketSeverityCollection.php'
   and type = 'M';
update module_components
   set title = 'Currency Rate'
 where name = 'currencyrate'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/models/CurrencyRate.php'
   and type = 'M';
update module_components
   set title = 'HR Expense'
 where name = 'expense'
   and location = 'modules/public_pages/hr/models/Expense.php'
   and type = 'M';
update module_components
   set title = 'General Ledger Account Centre'
 where name = 'glaccountcentre'
   and location = 'modules/public_pages/erp/ledger/general_ledger/models/GLAccountCentre.php'
   and type = 'M';
update module_components
   set title = 'General Ledger Analysis'
 where name = 'glanalysis'
   and location = 'modules/public_pages/erp/ledger/general_ledger/models/GLAnalysis.php'
   and type = 'M';
update module_components
   set title = 'General Ledger Balance'
 where name = 'glbalance'
   and location = 'modules/public_pages/erp/ledger/general_ledger/models/GLBalance.php'
   and type = 'M';
update module_components
   set title = 'General Ledger Balances'
 where name = 'glbalancecollection'
   and location = 'modules/public_pages/erp/ledger/general_ledger/models/GLBalanceCollection.php'
   and type = 'M';
update module_components
   set title = 'General Ledger Balances Search'
 where name = 'glbalancessearch'
   and location = 'modules/public_pages/erp/ledger/general_ledger/models/glbalancesSearch.php'
   and type = 'M';
update module_components
   set title = 'General Ledger Centres Search'
 where name = 'glcentressearch'
   and location = 'modules/public_pages/erp/ledger/general_ledger/models/glcentresSearch.php'
   and type = 'M';
update module_components
   set title = 'General Ledger Parameter'
 where name = 'glparams'
   and location = 'modules/public_pages/erp/ledger/general_ledger/models/GLParams.php'
   and type = 'M';
update module_components
   set title = 'General Ledger Summary'
 where name = 'glsummary'
   and location = 'modules/public_pages/erp/ledger/general_ledger/models/GLSummary.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Structure Report'
 where name = 'mfstructurereport'
   and location = 'modules/public_pages/erp/manufacturing/reports/MFStructureReport.php'
   and type = 'R';
update module_components
   set title = 'Parent Mill Roll Traceability Record'
 where name = 'parentmillrolltraceabilityrecord'
   and location = 'modules/public_pages/erp/manufacturing/reports/ParentMillRollTraceabilityRecord.php'
   and type = 'R';
update module_components
   set title = 'PCMC Order Form'
 where name = 'pcmcorderform'
   and location = 'modules/public_pages/erp/manufacturing/reports/PCMCOrderForm.php'
   and type = 'R';
update module_components
   set title = 'Wet Mix Sheet Form'
 where name = 'wetmixsheetform'
   and location = 'modules/public_pages/erp/manufacturing/reports/WetMixSheetForm.php'
   and type = 'R';
update module_components
   set title = 'trial balance'
 where name = 'trialbalance'
   and location = 'modules/public_pages/erp/ledger/general_ledger/templates/glbalances/trialbalance.tpl'
   and type = 'T';
update module_components
   set title = 'select list'
 where name = 'selectlist'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/templates/glparamss/selectlist.tpl'
   and type = 'T';
update module_components
   set title = 'review materials'
 where name = 'reviewmaterials'
   and location = 'modules/public_pages/erp/manufacturing/templates/mfworkorders/reviewmaterials.tpl'
   and type = 'T';
update module_components
   set title = 'reset status'
 where name = 'resetstatus'
   and location = 'modules/public_pages/erp/manufacturing/templates/mfworkorders/resetstatus.tpl'
   and type = 'T';
update module_components
   set title = 'pre-order'
 where name = 'preorder'
   and location = 'modules/public_pages/erp/manufacturing/templates/mfstructures/preorder.tpl'
   and type = 'T';
update module_components
   set title = 'new personal'
 where name = 'new_personal'
   and location = 'modules/public_pages/calendar/templates/calendars/new_personal.tpl'
   and type = 'T';
update module_components
   set title = 'new group'
 where name = 'new_group'
   and location = 'modules/public_pages/calendar/templates/calendars/new_group.tpl'
   and type = 'T';
update module_components
   set title = 'new gcal'
 where name = 'new_gcal'
   and location = 'modules/public_pages/calendar/templates/calendars/new_gcal.tpl'
   and type = 'T';
update module_components
   set title = 'edit personal'
 where name = 'edit_personal'
   and location = 'modules/public_pages/calendar/templates/calendars/edit_personal.tpl'
   and type = 'T';
update module_components
   set title = 'edit group'
 where name = 'edit_group'
   and location = 'modules/public_pages/calendar/templates/calendars/edit_group.tpl'
   and type = 'T';
update module_components
   set title = 'book production'
 where name = 'bookproduction'
   and location = 'modules/public_pages/erp/manufacturing/templates/mfworkorders/bookproduction.tpl'
   and type = 'T';
update module_components
   set title = 'book over under'
 where name = 'bookoverunder'
   and location = 'modules/public_pages/erp/manufacturing/templates/mfworkorders/bookoverunder.tpl'
   and type = 'T';
update module_components
   set title = 'edit'
 where name = 'edit'
   and location = 'modules/public_pages/ticketing/templates/edit.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/production_recording/templates/mfshiftoutputs/new.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/erp/production_recording/templates/mfshiftoutputs/view.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/production_recording/templates/mfshifts/index.tpl'
   and type = 'T';
update module_components
   set title = 'Project Issue Headers'
 where name = 'projectissueheadercollection'
   and location = 'modules/public_pages/projects/models/ProjectIssueHeaderCollection.php'
   and type = 'M';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/projects/templates/tasks/view.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/production_recording/templates/mfshifts/new.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/erp/production_recording/templates/mfshifts/view.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/production_recording/templates/mfshiftwastes/new.tpl'
   and type = 'T';
update module_components
   set title = 'Company Notes'
 where name = 'companynotescontroller'
   and location = 'modules/public_pages/contacts/controllers/CompanynotesController.php'
   and type = 'C';
update module_components
   set title = 'Currency Rates'
 where name = 'currencyratecollection'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/models/CurrencyRateCollection.php'
   and type = 'M';
update module_components
   set title = 'EDI Data Definition'
 where name = 'datadefinition'
   and location = 'modules/public_pages/edi/models/DataDefinition.php'
   and type = 'M';
update module_components
   set title = 'EDI Data Definitions'
 where name = 'datadefinitioncollection'
   and location = 'modules/public_pages/edi/models/DataDefinitionCollection.php'
   and type = 'M';
update module_components
   set title = 'EDI Data Definition Detail'
 where name = 'datadefinitiondetail'
   and location = 'modules/public_pages/edi/models/DataDefinitionDetail.php'
   and type = 'M';
update module_components
   set title = 'EDI Data Definition Details'
 where name = 'datadefinitiondetailcollection'
   and location = 'modules/public_pages/edi/models/DataDefinitionDetailCollection.php'
   and type = 'M';
update module_components
   set title = 'EDI Data Mapping'
 where name = 'datamapping'
   and location = 'modules/public_pages/edi/models/DataMapping.php'
   and type = 'M';
update module_components
   set title = 'EDI Data Mappings'
 where name = 'datamappingcollection'
   and location = 'modules/public_pages/edi/models/DataMappingCollection.php'
   and type = 'M';
update module_components
   set title = 'Delivery Term'
 where name = 'deliveryterm'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/models/DeliveryTerm.php'
   and type = 'M';
update module_components
   set title = 'Delivery Terms'
 where name = 'deliverytermcollection'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/models/DeliveryTermCollection.php'
   and type = 'M';
update module_components
   set title = 'Employee'
 where name = 'employee'
   and location = 'modules/public_pages/hr/models/Employee.php'
   and type = 'M';
update module_components
   set title = 'Payment Term'
 where name = 'paymentterm'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/models/PaymentTerm.php'
   and type = 'M';
update module_components
   set title = 'Payment Terms'
 where name = 'paymenttermcollection'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/models/PaymentTermCollection.php'
   and type = 'M';
update module_components
   set title = 'Payment Type'
 where name = 'paymenttype'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/models/PaymentType.php'
   and type = 'M';
update module_components
   set title = 'Payment Types'
 where name = 'paymenttypecollection'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/models/PaymentTypeCollection.php'
   and type = 'M';
update module_components
   set title = 'Project Issue Lines'
 where name = 'projectissuelinecollection'
   and location = 'modules/public_pages/projects/models/ProjectIssueLineCollection.php'
   and type = 'M';
update module_components
   set title = 'Project Note'
 where name = 'projectnote'
   and location = 'modules/public_pages/projects/models/ProjectNote.php'
   and type = 'M';
update module_components
   set title = 'Project Notes'
 where name = 'projectnotecollection'
   and location = 'modules/public_pages/projects/models/ProjectNoteCollection.php'
   and type = 'M';
update module_components
   set title = 'Project Note Type'
 where name = 'projectnotetype'
   and location = 'modules/public_pages/projects/models/ProjectNoteType.php'
   and type = 'M';
update module_components
   set title = 'Project Note Types'
 where name = 'projectnotetypecollection'
   and location = 'modules/public_pages/projects/models/ProjectNoteTypeCollection.php'
   and type = 'M';
update module_components
   set title = 'Project Phase'
 where name = 'projectphase'
   and location = 'modules/public_pages/projects/models/Projectphase.php'
   and type = 'M';
update module_components
   set title = 'Project Phases'
 where name = 'projectphasecollection'
   and location = 'modules/public_pages/projects/models/ProjectphaseCollection.php'
   and type = 'M';
update module_components
   set title = 'Projects Search'
 where name = 'projectsearch'
   and location = 'modules/public_pages/projects/models/ProjectSearch.php'
   and type = 'M';
update module_components
   set title = 'Sales Invoice'
 where name = 'sinvoice'
   and location = 'modules/public_pages/erp/invoicing/sales_invoicing/models/SInvoice.php'
   and type = 'M';
update module_components
   set title = 'Tax Rate'
 where name = 'taxrate'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/models/TaxRate.php'
   and type = 'M';
update module_components
   set title = 'Tax Rates'
 where name = 'taxratecollection'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/models/TaxRateCollection.php'
   and type = 'M';
update module_components
   set title = 'Tax Status'
 where name = 'taxstatus'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/models/TaxStatus.php'
   and type = 'M';
update module_components
   set title = ' Tax Statuses'
 where name = 'taxstatuscollection'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/models/TaxStatusCollection.php'
   and type = 'M';
update module_components
   set title = 'WEB EDI'
 where name = 'webedi'
   and location = 'modules/public_pages/edi/implementations/WebEdi.php'
   and type = 'M';
update module_components
   set title = 'view manufacturing shift'
 where name = 'viewmfshift'
   and location = 'modules/public_pages/erp/production_recording/templates/mfshiftoutputs/viewmfshift.tpl'
   and type = 'T';
update module_components
   set title = 'new resource'
 where name = 'newresource'
   and location = 'modules/public_pages/projects/templates/tasks/newresource.tpl'
   and type = 'T';
update module_components
   set title = 'move money'
 where name = 'move_money'
   and location = 'modules/public_pages/erp/cashbook/templates/cbtransactions/move_money.tpl'
   and type = 'T';
update module_components
   set title = 'Project Preferences'
 where name = 'projectspreferences'
   and location = 'modules/public_pages/projects/models/ProjectsPreferences.php'
   and type = 'M';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/cashbook/templates/periodicpayments/index.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/erp/cashbook/templates/periodicpayments/view.tpl'
   and type = 'T';
update module_components
   set title = 'output_setup'
 where name = 'output_setup'
   and location = 'modules/public_pages/output/output_setup/resources/js/output_setup.js'
   and type = 'J';
update module_components
   set title = 'Project Work Type'
 where name = 'projectworktype'
   and location = 'modules/public_pages/projects/models/Projectworktype.php'
   and type = 'M';
update module_components
   set title = 'Ticketing Index'
 where name = 'indexcontroller'
   and location = 'modules/public_pages/ticketing/controllers/IndexController.php'
   and type = 'C';
update module_components
   set title = 'Sales Invoice Lines'
 where name = 'sinvoicelinescontroller'
   and location = 'modules/public_pages/erp/invoicing/sales_invoicing/controllers/SinvoicelinesController.php'
   and type = 'C';
update module_components
   set title = 'Ticket Configurations'
 where name = 'ticketconfigurationscontroller'
   and location = 'modules/public_pages/ticketing/controllers/TicketconfigurationsController.php'
   and type = 'C';
update module_components
   set title = 'Ticketing Utils'
 where name = 'ticketingutils'
   and location = 'modules/public_pages/ticketing/controllers/TicketingUtils.php'
   and type = 'C';
update module_components
   set title = 'Tickets'
 where name = 'ticketingcontroller'
   and location = 'modules/public_pages/ticketing/controllers/TicketingController.php'
   and type = 'C';
update module_components
   set title = 'Ticket Module Versions'
 where name = 'ticketmoduleversionscontroller'
   and location = 'modules/public_pages/ticketing/controllers/TicketmoduleversionsController.php'
   and type = 'C';
update module_components
   set title = 'Company'
 where name = 'company'
   and location = 'modules/public_pages/contacts/models/Company.php'
   and type = 'M';
update module_components
   set title = 'Contact Preferences'
 where name = 'contactspreferences'
   and location = 'modules/public_pages/contacts/models/ContactsPreferences.php'
   and type = 'M';
update module_components
   set title = 'Has Report'
 where name = 'hasreport'
   and location = 'modules/public_pages/admin/models/HasReport.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Outside Operations'
 where name = 'mfoutsideoperationcollection'
   and location = 'modules/public_pages/erp/manufacturing/models/MFOutsideOperationCollection.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Resource'
 where name = 'mfresource'
   and location = 'modules/public_pages/erp/manufacturing/models/MFResource.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Resources'
 where name = 'mfresourcecollection'
   and location = 'modules/public_pages/erp/manufacturing/models/MFResourceCollection.php'
   and type = 'M';
update module_components
   set title = 'Purchase Invoice'
 where name = 'pinvoice'
   and location = 'modules/public_pages/erp/invoicing/purchase_invoicing/models/PInvoice.php'
   and type = 'M';
update module_components
   set title = 'Purchase Invoices'
 where name = 'pinvoicecollection'
   and location = 'modules/public_pages/erp/invoicing/purchase_invoicing/models/PInvoiceCollection.php'
   and type = 'M';
update module_components
   set title = 'Purchase Invoice Line'
 where name = 'pinvoiceline'
   and location = 'modules/public_pages/erp/invoicing/purchase_invoicing/models/PInvoiceLine.php'
   and type = 'M';
update module_components
   set title = 'Purchase Invoice Lines'
 where name = 'pinvoicelinecollection'
   and location = 'modules/public_pages/erp/invoicing/purchase_invoicing/models/PInvoiceLineCollection.php'
   and type = 'M';
update module_components
   set title = 'Purchase Invoices Search'
 where name = 'pinvoicessearch'
   and location = 'modules/public_pages/erp/invoicing/purchase_invoicing/models/pinvoicesSearch.php'
   and type = 'M';
update module_components
   set title = 'Project Work Types'
 where name = 'projectworktypecollection'
   and location = 'modules/public_pages/projects/models/ProjectworktypeCollection.php'
   and type = 'M';
update module_components
   set title = 'Project Resource'
 where name = 'resource'
   and location = 'modules/public_pages/projects/models/Resource.php'
   and type = 'M';
update module_components
   set title = 'Project Resources'
 where name = 'resourcecollection'
   and location = 'modules/public_pages/projects/models/ResourceCollection.php'
   and type = 'M';
update module_components
   set title = 'Project Resource Template'
 where name = 'resourcetemplate'
   and location = 'modules/public_pages/projects/models/Resourcetemplate.php'
   and type = 'M';
update module_components
   set title = 'Project Resource Templates'
 where name = 'resourcetemplatecollection'
   and location = 'modules/public_pages/projects/models/ResourcetemplateCollection.php'
   and type = 'M';
update module_components
   set title = 'Sales Ledger Customer Discounts'
 where name = 'sldiscountcollection'
   and location = 'modules/public_pages/erp/ledger/sales_ledger/models/SLDiscountCollection.php'
   and type = 'M';
update module_components
   set title = 'Sales Ledger Customer Discount'
 where name = 'sldiscount'
   and location = 'modules/public_pages/erp/ledger/sales_ledger/models/SLDiscount.php'
   and type = 'M';
update module_components
   set title = 'view sales invoices'
 where name = 'viewsales_invoices'
   and location = 'modules/public_pages/erp/manufacturing/templates/stitems/viewsales_invoices.tpl'
   and type = 'T';
update module_components
   set title = 'view outside operations'
 where name = 'viewoutside_operations'
   and location = 'modules/public_pages/erp/manufacturing/templates/stitems/viewoutside_operations.tpl'
   and type = 'T';
update module_components
   set title = 'view operations'
 where name = 'viewoperations'
   and location = 'modules/public_pages/erp/manufacturing/templates/stitems/viewoperations.tpl'
   and type = 'T';
update module_components
   set title = 'view actions'
 where name = 'viewactions'
   and location = 'modules/public_pages/erp/manufacturing/templates/stitems/viewactions.tpl'
   and type = 'T';
update module_components
   set title = 'show parts'
 where name = 'showparts'
   and location = 'modules/public_pages/erp/manufacturing/templates/stitems/showparts.tpl'
   and type = 'T';
update module_components
   set title = 'show fulfilled'
 where name = 'showfulfilled'
   and location = 'modules/public_pages/erp/manufacturing/templates/mfworkorders/showfulfilled.tpl'
   and type = 'T';
update module_components
   set title = 'review resources'
 where name = 'reviewresources'
   and location = 'modules/public_pages/erp/manufacturing/templates/mfworkorders/reviewresources.tpl'
   and type = 'T';
update module_components
   set title = 'receive payment'
 where name = 'receive_payment'
   and location = 'modules/public_pages/erp/cashbook/templates/cbtransactions/receive_payment.tpl'
   and type = 'T';
update module_components
   set title = 'make payments'
 where name = 'makepayments'
   and location = 'modules/public_pages/erp/cashbook/templates/periodicpayments/makepayments.tpl'
   and type = 'T';
update module_components
   set title = 'clone item'
 where name = 'clone_item'
   and location = 'modules/public_pages/erp/manufacturing/templates/stitems/clone_item.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/contacts/templates/companyattachments/new.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/contacts/templates/companycontactmethods/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/contacts/templates/companycontactmethods/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/contacts/templates/companynotes/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/contacts/templates/companynotes/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/contacts/templates/companys/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/contacts/templates/companys/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/contacts/templates/leads/new.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/contacts/templates/leads/view.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/contacts/templates/leads/index.tpl'
   and type = 'T';
update module_components
   set title = 'sharing'
 where name = 'sharing'
   and location = 'modules/public_pages/contacts/templates/leads/sharing.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/contacts/templates/partycontactmethods/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/contacts/templates/partynotes/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/contacts/templates/partynotes/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/contacts/templates/partys/new.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/contacts/templates/partys/view.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/contacts/templates/partys/index.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/contacts/templates/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/contacts/templates/personaddresss/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/contacts/templates/personaddresss/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/contacts/templates/personattachments/new.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/contacts/templates/personattachments/view.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/contacts/templates/personcontactmethods/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/contacts/templates/personcontactmethods/index.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/invoicing/purchase_invoicing/templates/pinvoices/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/invoicing/purchase_invoicing/templates/pinvoices/new.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/erp/invoicing/purchase_invoicing/templates/pinvoices/view.tpl'
   and type = 'T';
update module_components
   set title = 'Sales Ledger Customer Discounts Search'
 where name = 'sldiscountsearch'
   and location = 'modules/public_pages/erp/ledger/sales_ledger/models/SLDiscountSearch.php'
   and type = 'M';
update module_components
   set title = 'General Ledger'
 where name = 'ledgercontroller'
   and location = 'modules/public_pages/erp/ledger/controllers/LedgerController.php'
   and type = 'C';
update module_components
   set title = 'Sales History Grapher'
 where name = 'saleshistorygrapher'
   and location = 'modules/public_pages/erp/invoicing/sales_invoicing/eglets/SalesHistoryGrapher.php'
   and type = 'E';
update module_components
   set title = 'Sales History Summary'
 where name = 'saleshistorysummary'
   and location = 'modules/public_pages/erp/invoicing/sales_invoicing/eglets/SalesHistorySummary.php'
   and type = 'E';
update module_components
   set title = 'Top Sales Invoices uzlet'
 where name = 'topsalesinvoiceseglet'
   and location = 'modules/public_pages/erp/invoicing/sales_invoicing/eglets/TopSalesInvoicesEGlet.php'
   and type = 'E';
update module_components
   set title = 'Engineering Resources'
 where name = 'engineeringresourcecollection'
   and location = 'modules/public_pages/engineering/models/EngineeringResourceCollection.php'
   and type = 'M';
update module_components
   set title = 'Invoice Line'
 where name = 'invoiceline'
   and location = 'modules/public_pages/erp/invoicing/models/InvoiceLine.php'
   and type = 'M';
update module_components
   set title = 'Ledger Transaction'
 where name = 'ledgertransaction'
   and location = 'modules/public_pages/erp/ledger/models/LedgerTransaction.php'
   and type = 'M';
update module_components
   set title = 'Ledger Period Handling'
 where name = 'periodhandling'
   and location = 'modules/public_pages/erp/ledger/models/periodHandling.php'
   and type = 'M';
update module_components
   set title = 'Sales Ledger Transactions'
 where name = 'sltransactioncollection'
   and location = 'modules/public_pages/erp/ledger/sales_ledger/models/SLTransactionCollection.php'
   and type = 'M';
update module_components
   set title = 'Sales Ledger Transactions Search'
 where name = 'sltransactionssearch'
   and location = 'modules/public_pages/erp/ledger/sales_ledger/models/sltransactionsSearch.php'
   and type = 'M';
update module_components
   set title = 'Project Task'
 where name = 'task'
   and location = 'modules/public_pages/projects/models/Task.php'
   and type = 'M';
update module_components
   set title = 'Project Tasks'
 where name = 'taskcollection'
   and location = 'modules/public_pages/projects/models/TaskCollection.php'
   and type = 'M';
update module_components
   set title = 'select invoices'
 where name = 'selectinvoices'
   and location = 'modules/public_pages/erp/invoicing/purchase_invoicing/templates/pinvoices/selectinvoices.tpl'
   and type = 'T';
update module_components
   set title = 'change due date'
 where name = 'change_due_date'
   and location = 'modules/public_pages/erp/invoicing/purchase_invoicing/templates/pinvoices/change_due_date.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/ledger/sales_ledger/templates/sldiscounts/new.tpl'
   and type = 'T';
update module_components
   set title = 'allocate'
 where name = 'allocate'
   and location = 'modules/public_pages/erp/ledger/purchase_ledger/templates/plsuppliers/allocate.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/costing/templates/stcosts/index.tpl'
   and type = 'T';
update module_components
   set title = 'Purchase Ledger Transaction'
 where name = 'pltransaction'
   and location = 'modules/public_pages/erp/ledger/purchase_ledger/models/PLTransaction.php'
   and type = 'M';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/production_recording/templates/mfwastetypes/index.tpl'
   and type = 'T';
update module_components
   set title = 'Purchase Ledger Transactions'
 where name = 'pltransactioncollection'
   and location = 'modules/public_pages/erp/ledger/purchase_ledger/models/PLTransactionCollection.php'
   and type = 'M';
update module_components
   set title = 'CRM Newsletters'
 where name = 'newsletterscontroller'
   and location = 'modules/public_pages/crm/controllers/NewslettersController.php'
   and type = 'C';
update module_components
   set title = 'Sales People'
 where name = 'salespersonscontroller'
   and location = 'modules/public_pages/crm/controllers/SalespersonsController.php'
   and type = 'C';
update module_components
   set title = 'Current Pipeline Grapher'
 where name = 'currentpipelinegrapher'
   and location = 'modules/public_pages/crm/eglets/CurrentPipelineGrapher.php'
   and type = 'E';
update module_components
   set title = 'Leads Added Today uzlet'
 where name = 'leadsaddedtodayeglet'
   and location = 'modules/public_pages/crm/eglets/LeadsAddedTodayEGlet.php'
   and type = 'E';
update module_components
   set title = 'CRM Opportunities by Source Grapher'
 where name = 'opportunitiesbysourcegrapher'
   and location = 'modules/public_pages/crm/eglets/OpportunitiesBySourceGrapher.php'
   and type = 'E';
update module_components
   set title = 'CRM Opportunities History Grapher'
 where name = 'opportunitieshistorygrapher'
   and location = 'modules/public_pages/crm/eglets/OpportunitiesHistoryGrapher.php'
   and type = 'E';
update module_components
   set title = 'CRM Opportunities Quarterly by Status Grapher'
 where name = 'opportunitiesquarterlybystatusgrapher'
   and location = 'modules/public_pages/crm/eglets/OpportunitiesQuarterlyByStatusGrapher.php'
   and type = 'E';
update module_components
   set title = 'CRM Opportunities Weekly by Status Grapher'
 where name = 'opportunitiesweeklybystatusgrapher'
   and location = 'modules/public_pages/crm/eglets/OpportunitiesWeeklyByStatusGrapher.php'
   and type = 'E';
update module_components
   set title = 'CRM Opportunities Yearly by Status Grapher'
 where name = 'opportunitiesyearlybystatusgrapher'
   and location = 'modules/public_pages/crm/eglets/OpportunitiesYearlyByStatusGrapher.php'
   and type = 'E';
update module_components
   set title = 'Recently Added Leads uzlet'
 where name = 'recentlyaddedleadseglet'
   and location = 'modules/public_pages/crm/eglets/RecentlyAddedLeadsEGlet.php'
   and type = 'E';
update module_components
   set title = 'Recently Viewed Leads uzlet'
 where name = 'recentlyviewedleadseglet'
   and location = 'modules/public_pages/crm/eglets/RecentlyViewedLeadsEGlet.php'
   and type = 'E';
update module_components
   set title = 'Sales Team Monthly Summary uzlet'
 where name = 'salesteammonthlysummaryeglet'
   and location = 'modules/public_pages/crm/eglets/SalesTeamMonthlySummaryEGlet.php'
   and type = 'E';
update module_components
   set title = 'Sales Team Weekly Summary uzlet'
 where name = 'salesteamweeklysummaryeglet'
   and location = 'modules/public_pages/crm/eglets/SalesTeamWeeklySummaryEGlet.php'
   and type = 'E';
update module_components
   set title = 'Sales Team Yearly Summary uzlet'
 where name = 'salesteamyearlysummaryeglet'
   and location = 'modules/public_pages/crm/eglets/SalesTeamYearlySummaryEGlet.php'
   and type = 'E';
update module_components
   set title = 'Invoice'
 where name = 'invoice'
   and location = 'modules/public_pages/erp/invoicing/models/Invoice.php'
   and type = 'M';
update module_components
   set title = 'CRM Leads'
 where name = 'leadcollection'
   and location = 'modules/public_pages/crm/models/LeadCollection.php'
   and type = 'M';
update module_components
   set title = 'Purchase Ledger Supplier Search'
 where name = 'plsuppliersearch'
   and location = 'modules/public_pages/erp/ledger/purchase_ledger/models/PLSupplierSearch.php'
   and type = 'M';
update module_components
   set title = 'Purchase Ledger Transaction Search'
 where name = 'pltransactionssearch'
   and location = 'modules/public_pages/erp/ledger/purchase_ledger/models/pltransactionsSearch.php'
   and type = 'M';
update module_components
   set title = 'Sales Invoice Line'
 where name = 'sinvoiceline'
   and location = 'modules/public_pages/erp/invoicing/sales_invoicing/models/SInvoiceLine.php'
   and type = 'M';
update module_components
   set title = 'Sales Invoices'
 where name = 'sinvoicecollection'
   and location = 'modules/public_pages/erp/invoicing/sales_invoicing/models/SInvoiceCollection.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Stock Cost'
 where name = 'stcost'
   and location = 'modules/public_pages/erp/costing/models/STCost.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Stock Costs'
 where name = 'stcostcollection'
   and location = 'modules/public_pages/erp/costing/models/STCostCollection.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Stock Cost Search'
 where name = 'stcostssearch'
   and location = 'modules/public_pages/erp/costing/models/stcostsSearch.php'
   and type = 'M';
update module_components
   set title = 'Project Task Priority'
 where name = 'taskpriority'
   and location = 'modules/public_pages/projects/models/Taskpriority.php'
   and type = 'M';
update module_components
   set title = 'view contact methods'
 where name = 'viewcontact_methods'
   and location = 'modules/public_pages/erp/ledger/sales_ledger/templates/slcustomers/viewcontact_methods.tpl'
   and type = 'T';
update module_components
   set title = 'select for payment'
 where name = 'select_for_payment'
   and location = 'modules/public_pages/erp/ledger/purchase_ledger/templates/plsuppliers/select_for_payment.tpl'
   and type = 'T';
update module_components
   set title = 'remittance list'
 where name = 'remittance_list'
   and location = 'modules/public_pages/erp/ledger/purchase_ledger/templates/plsuppliers/remittance_list.tpl'
   and type = 'T';
update module_components
   set title = 'enter payment reference'
 where name = 'enter_payment_reference'
   and location = 'modules/public_pages/erp/ledger/purchase_ledger/templates/plsuppliers/enter_payment_reference.tpl'
   and type = 'T';
update module_components
   set title = 'cost sheet'
 where name = 'costsheet'
   and location = 'modules/public_pages/erp/costing/templates/stcosts/costsheet.tpl'
   and type = 'T';
update module_components
   set title = 'batch payment history detail'
 where name = 'batch_payment_history_detail'
   and location = 'modules/public_pages/erp/ledger/purchase_ledger/templates/plsuppliers/batch_payment_history_detail.tpl'
   and type = 'T';
update module_components
   set title = 'batch payment history'
 where name = 'batch_payment_history'
   and location = 'modules/public_pages/erp/ledger/purchase_ledger/templates/plsuppliers/batch_payment_history.tpl'
   and type = 'T';
update module_components
   set title = 'Sales Person'
 where name = 'salesperson'
   and location = 'modules/public_pages/crm/models/SalesPerson.php'
   and type = 'M';
update module_components
   set title = 'Sales People'
 where name = 'salespersoncollection'
   and location = 'modules/public_pages/crm/models/SalesPersonCollection.php'
   and type = 'M';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/crm/templates/activityattachments/new.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/crm/templates/activityattachments/view.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/crm/templates/activitynotes/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/crm/templates/activitynotes/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/crm/templates/activitys/new.tpl'
   and type = 'T';
update module_components
   set title = 'CRM Open Opportunities euzlet'
 where name = 'openopportunitieseglet'
   and location = 'modules/public_pages/crm/eglets/OpenOpportunitiesEGlet.php'
   and type = 'E';
update module_components
   set title = 'CRM Opportunities Monthly by Status Grapher'
 where name = 'opportunitiesmonthlybystatusgrapher'
   and location = 'modules/public_pages/crm/eglets/OpportunitiesMonthlyByStatusGrapher.php'
   and type = 'E';
update module_components
   set title = 'Sales Team Summary uzlet'
 where name = 'salesteamsummaryeglet'
   and location = 'modules/public_pages/crm/eglets/SalesTeamSummaryEGlet.php'
   and type = 'E';
update module_components
   set title = 'CRM Activity'
 where name = 'activity'
   and location = 'modules/public_pages/crm/models/Activity.php'
   and type = 'M';
update module_components
   set title = 'CRM Activities'
 where name = 'activitycollection'
   and location = 'modules/public_pages/crm/models/ActivityCollection.php'
   and type = 'M';
update module_components
   set title = 'CRM Activity Note'
 where name = 'activitynote'
   and location = 'modules/public_pages/crm/models/ActivityNote.php'
   and type = 'M';
update module_components
   set title = 'CRM Activity Notes'
 where name = 'activitynotecollection'
   and location = 'modules/public_pages/crm/models/ActivityNoteCollection.php'
   and type = 'M';
update module_components
   set title = 'CRM Activity Search'
 where name = 'activitysearch'
   and location = 'modules/public_pages/crm/models/ActivitySearch.php'
   and type = 'M';
update module_components
   set title = 'CRM Activity Type'
 where name = 'activitytype'
   and location = 'modules/public_pages/crm/models/Activitytype.php'
   and type = 'M';
update module_components
   set title = 'CRM Activity Types'
 where name = 'activitytypecollection'
   and location = 'modules/public_pages/crm/models/ActivitytypeCollection.php'
   and type = 'M';
update module_components
   set title = 'CRM Campaign'
 where name = 'campaign'
   and location = 'modules/public_pages/crm/models/Campaign.php'
   and type = 'M';
update module_components
   set title = 'CRM Campaigns'
 where name = 'campaigncollection'
   and location = 'modules/public_pages/crm/models/CampaignCollection.php'
   and type = 'M';
update module_components
   set title = 'CRM Campaign Types'
 where name = 'campaigntypecollection'
   and location = 'modules/public_pages/crm/models/CampaigntypeCollection.php'
   and type = 'M';
update module_components
   set title = 'CRM Campaign Status'
 where name = 'campaignstatus'
   and location = 'modules/public_pages/crm/models/Campaignstatus.php'
   and type = 'M';
update module_components
   set title = 'CRM Campaign Type'
 where name = 'campaigntype'
   and location = 'modules/public_pages/crm/models/Campaigntype.php'
   and type = 'M';
update module_components
   set title = 'Customer Service Search'
 where name = 'customerservicessearch'
   and location = 'modules/public_pages/erp/customer_service/models/customerServicesSearch.php'
   and type = 'M';
update module_components
   set title = 'CRM Lead'
 where name = 'lead'
   and location = 'modules/public_pages/crm/models/Lead.php'
   and type = 'M';
update module_components
   set title = 'CRM Newsletters'
 where name = 'newslettercollection'
   and location = 'modules/public_pages/crm/models/NewsletterCollection.php'
   and type = 'M';
update module_components
   set title = 'CRM Newsletter'
 where name = 'newsletter'
   and location = 'modules/public_pages/crm/models/Newsletter.php'
   and type = 'M';
update module_components
   set title = 'CRM Newsletter Unique url Clicks'
 where name = 'newsletteruniqueurlclickcollection'
   and location = 'modules/public_pages/crm/models/NewsletteruniqueurlclickCollection.php'
   and type = 'M';
update module_components
   set title = 'CRM Newsletter url Clicks'
 where name = 'newsletterurlclickcollection'
   and location = 'modules/public_pages/crm/models/NewsletterurlclickCollection.php'
   and type = 'M';
update module_components
   set title = 'CRM Newsletter url Click'
 where name = 'newsletterurlclick'
   and location = 'modules/public_pages/crm/models/Newsletterurlclick.php'
   and type = 'M';
update module_components
   set title = 'CRM Newsletter View'
 where name = 'newsletterview'
   and location = 'modules/public_pages/crm/models/Newsletterview.php'
   and type = 'M';
update module_components
   set title = 'CRM Newsletter Views'
 where name = 'newsletterviewcollection'
   and location = 'modules/public_pages/crm/models/NewsletterviewCollection.php'
   and type = 'M';
update module_components
   set title = 'CRM Opportunity'
 where name = 'opportunity'
   and location = 'modules/public_pages/crm/models/Opportunity.php'
   and type = 'M';
update module_components
   set title = 'CRM Opportunities'
 where name = 'opportunitycollection'
   and location = 'modules/public_pages/crm/models/OpportunityCollection.php'
   and type = 'M';
update module_components
   set title = 'CRM Opportunity Note'
 where name = 'opportunitynote'
   and location = 'modules/public_pages/crm/models/OpportunityNote.php'
   and type = 'M';
update module_components
   set title = 'CRM Opportunity Notes'
 where name = 'opportunitynotecollection'
   and location = 'modules/public_pages/crm/models/OpportunityNoteCollection.php'
   and type = 'M';
update module_components
   set title = 'CRM Opportunity Search'
 where name = 'opportunitysearch'
   and location = 'modules/public_pages/crm/models/OpportunitySearch.php'
   and type = 'M';
update module_components
   set title = 'CRM Opportunity Source'
 where name = 'opportunitysource'
   and location = 'modules/public_pages/crm/models/Opportunitysource.php'
   and type = 'M';
update module_components
   set title = 'CRM Opportunity Sources'
 where name = 'opportunitysourcecollection'
   and location = 'modules/public_pages/crm/models/OpportunitysourceCollection.php'
   and type = 'M';
update module_components
   set title = 'CRM Opportunity Status'
 where name = 'opportunitystatus'
   and location = 'modules/public_pages/crm/models/Opportunitystatus.php'
   and type = 'M';
update module_components
   set title = 'CRM Opportunity Statuses'
 where name = 'opportunitystatuscollection'
   and location = 'modules/public_pages/crm/models/OpportunitystatusCollection.php'
   and type = 'M';
update module_components
   set title = 'CRM Opportunity Type'
 where name = 'opportunitytype'
   and location = 'modules/public_pages/crm/models/Opportunitytype.php'
   and type = 'M';
update module_components
   set title = 'CRM Opportunity Types'
 where name = 'opportunitytypecollection'
   and location = 'modules/public_pages/crm/models/OpportunitytypeCollection.php'
   and type = 'M';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/crm/templates/activitys/view.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/crm/templates/activitys/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/crm/templates/campaigns/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/crm/templates/campaigns/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/crm/templates/newsletters/new.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/crm/templates/newsletters/view.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/crm/templates/newsletters/index.tpl'
   and type = 'T';
update module_components
   set title = 'edit'
 where name = 'edit'
   and location = 'modules/public_pages/crm/templates/edit.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/crm/templates/opportunityattachments/new.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/crm/templates/opportunityattachments/view.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/crm/templates/opportunitynotes/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/crm/templates/opportunitynotes/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/crm/templates/opportunitys/new.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/crm/templates/opportunitys/view.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/crm/templates/opportunitys/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/crm/templates/salespersons/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/crm/templates/salespersons/index.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/crm/templates/index.tpl'
   and type = 'T';
update module_components
   set title = 'Purchase Order Goods Received Search'
 where name = 'pogoodsreceivedsearch'
   and location = 'modules/public_pages/erp/goodsreceived/models/pogoodsreceivedSearch.php'
   and type = 'M';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/customer_service/templates/csfailurecodes/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/customer_service/templates/csfailurecodes/index.tpl'
   and type = 'T';
update module_components
   set title = 'detail'
 where name = 'detail'
   and location = 'modules/public_pages/erp/customer_service/templates/detail.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/customer_service/templates/index.tpl'
   and type = 'T';
update module_components
   set title = 'permissions'
 where name = 'permissions'
   and location = 'modules/public_pages/dashboard/templates/details/permissions.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/output/output_setup/templates/index.tpl'
   and type = 'T';
update module_components
   set title = 'Dashboard'
 where name = 'indexcontroller'
   and location = 'modules/public_pages/dashboard/controllers/IndexController.php'
   and type = 'C';
update module_components
   set title = 'EDI Transaction Log History'
 where name = 'editransactionloghistory'
   and location = 'modules/public_pages/edi/models/EDITransactionLogHistory.php'
   and type = 'M';
update module_components
   set title = 'EDI Transaction Log Entry'
 where name = 'editransactionlog'
   and location = 'modules/public_pages/edi/models/EDITransactionLog.php'
   and type = 'M';
update module_components
   set title = 'HR Expense Line'
 where name = 'expenseline'
   and location = 'modules/public_pages/hr/models/ExpenseLine.php'
   and type = 'M';
update module_components
   set title = 'HR Expense Lines'
 where name = 'expenselinecollection'
   and location = 'modules/public_pages/hr/models/ExpenseLineCollection.php'
   and type = 'M';
update module_components
   set title = 'HR Holiday Authoriser'
 where name = 'holidayauthoriser'
   and location = 'modules/public_pages/hr/models/HolidayAuthoriser.php'
   and type = 'M';
update module_components
   set title = 'HR Holiday Authorisers'
 where name = 'holidayauthorisercollection'
   and location = 'modules/public_pages/hr/models/HolidayAuthoriserCollection.php'
   and type = 'M';
update module_components
   set title = 'Holiday Entitlement'
 where name = 'holidayentitlement'
   and location = 'modules/public_pages/hr/models/Holidayentitlement.php'
   and type = 'M';
update module_components
   set title = 'Purchase Order Received Line'
 where name = 'poreceivedline'
   and location = 'modules/public_pages/erp/goodsreceived/models/POReceivedLine.php'
   and type = 'M';
update module_components
   set title = 'Purchase Order Received Lines'
 where name = 'poreceivedlinecollection'
   and location = 'modules/public_pages/erp/goodsreceived/models/POReceivedLineCollection.php'
   and type = 'M';
update module_components
   set title = 'Project Task Priorities'
 where name = 'taskprioritycollection'
   and location = 'modules/public_pages/projects/models/TaskpriorityCollection.php'
   and type = 'M';
update module_components
   set title = 'Project Task Resource'
 where name = 'taskresource'
   and location = 'modules/public_pages/projects/models/TaskResource.php'
   and type = 'M';
update module_components
   set title = 'Project Task Resources'
 where name = 'taskresourcecollection'
   and location = 'modules/public_pages/projects/models/TaskResourceCollection.php'
   and type = 'M';
update module_components
   set title = 'update failure'
 where name = 'updatefailure'
   and location = 'modules/public_pages/erp/customer_service/templates/updatefailure.tpl'
   and type = 'T';
update module_components
   set title = 'summary report'
 where name = 'summary_report'
   and location = 'modules/public_pages/crm/templates/opportunitys/summary_report.tpl'
   and type = 'T';
update module_components
   set title = 'show views'
 where name = 'showviews'
   and location = 'modules/public_pages/crm/templates/newsletters/showviews.tpl'
   and type = 'T';
update module_components
   set title = 'failure code summary'
 where name = 'failurecodesummary'
   and location = 'modules/public_pages/erp/customer_service/templates/failurecodesummary.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/goodsreceived/templates/index.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/erp/goodsreceived/templates/poreceivedlines/view.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/goodsreceived/templates/poreceivedlines/index.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/admin/templates/users/view.tpl'
   and type = 'T';
update module_components
   set title = 'Project'
 where name = 'project'
   and location = 'modules/public_pages/projects/models/Project.php'
   and type = 'M';
update module_components
   set title = 'allocate'
 where name = 'allocate'
   and location = 'modules/public_pages/hr/templates/employees/allocate.tpl'
   and type = 'T';
update module_components
   set title = 'edit'
 where name = 'edit'
   and location = 'modules/public_pages/hr/templates/employees/edit.tpl'
   and type = 'T';
update module_components
   set title = 'Project Budget'
 where name = 'projectbudget'
   and location = 'modules/public_pages/projects/models/ProjectBudget.php'
   and type = 'M';
update module_components
   set title = 'Projects Index'
 where name = 'indexcontroller'
   and location = 'modules/public_pages/projects/controllers/IndexController.php'
   and type = 'C';
update module_components
   set title = 'Project Attachments'
 where name = 'projectattachmentscontroller'
   and location = 'modules/public_pages/projects/controllers/ProjectattachmentsController.php'
   and type = 'C';
update module_components
   set title = 'Project Budgets'
 where name = 'projectbudgetscontroller'
   and location = 'modules/public_pages/projects/controllers/ProjectbudgetsController.php'
   and type = 'C';
update module_components
   set title = 'Quality Supplementary Complaint Codes'
 where name = 'supplementarycomplaintcodescontroller'
   and location = 'modules/public_pages/quality/controllers/SupplementarycomplaintcodesController.php'
   and type = 'C';
update module_components
   set title = 'Manufacturing Warehouse Transfer Lines'
 where name = 'whtransferlinescontroller'
   and location = 'modules/public_pages/erp/despatch/controllers/WhtransferlinesController.php'
   and type = 'C';
update module_components
   set title = 'Manufacturing Setup Warehouse Bins'
 where name = 'whbinscontroller'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/controllers/WhbinsController.php'
   and type = 'C';
update module_components
   set title = 'Project New Issues uzlet'
 where name = 'newissueseglet'
   and location = 'modules/public_pages/projects/eglets/NewIssuesEGlet.php'
   and type = 'E';
update module_components
   set title = 'Sales Order Price Check uzlet'
 where name = 'pricecheckuzlet'
   and location = 'modules/public_pages/erp/order/sales_order/eglets/PriceCheckuzLET.php'
   and type = 'E';
update module_components
   set title = 'General Ledger Period End Balances'
 where name = 'glperiodendbalancecollection'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/models/GLPeriodEndBalanceCollection.php'
   and type = 'M';
update module_components
   set title = 'General Ledger Period End Balance'
 where name = 'glperiodendbalance'
   and location = 'modules/public_pages/erp/ledger/ledger_setup/models/GLPeriodEndBalance.php'
   and type = 'M';
update module_components
   set title = 'Has Reports'
 where name = 'hasreportcollection'
   and location = 'modules/public_pages/admin/models/HasReportCollection.php'
   and type = 'M';
update module_components
   set title = 'Holiday Entitlements'
 where name = 'holidayentitlementcollection'
   and location = 'modules/public_pages/hr/models/HolidayentitlementCollection.php'
   and type = 'M';
update module_components
   set title = 'Holiday Extra Days'
 where name = 'holidayextradaycollection'
   and location = 'modules/public_pages/hr/models/HolidayextradayCollection.php'
   and type = 'M';
update module_components
   set title = 'Holiday Request'
 where name = 'holidayrequest'
   and location = 'modules/public_pages/hr/models/Holidayrequest.php'
   and type = 'M';
update module_components
   set title = 'Holiday Requests'
 where name = 'holidayrequestcollection'
   and location = 'modules/public_pages/hr/models/HolidayrequestCollection.php'
   and type = 'M';
update module_components
   set title = 'Employee Hour'
 where name = 'hour'
   and location = 'modules/public_pages/hr/models/Hour.php'
   and type = 'M';
update module_components
   set title = 'Employee Hours'
 where name = 'hourcollection'
   and location = 'modules/public_pages/hr/models/HourCollection.php'
   and type = 'M';
update module_components
   set title = 'Employee Hour Type'
 where name = 'hourtype'
   and location = 'modules/public_pages/hr/models/HourType.php'
   and type = 'M';
update module_components
   set title = 'Employee Hour Types'
 where name = 'hourtypecollection'
   and location = 'modules/public_pages/hr/models/HourTypeCollection.php'
   and type = 'M';
update module_components
   set title = 'Employee Hour Type Group'
 where name = 'hourtypegroup'
   and location = 'modules/public_pages/hr/models/HourTypeGroup.php'
   and type = 'M';
update module_components
   set title = 'Employee Hour Type Groups'
 where name = 'hourtypegroupcollection'
   and location = 'modules/public_pages/hr/models/HourTypeGroupCollection.php'
   and type = 'M';
update module_components
   set title = 'Project Budgets'
 where name = 'projectbudgetcollection'
   and location = 'modules/public_pages/projects/models/ProjectBudgetCollection.php'
   and type = 'M';
update module_components
   set title = 'Project Category'
 where name = 'projectcategory'
   and location = 'modules/public_pages/projects/models/Projectcategory.php'
   and type = 'M';
update module_components
   set title = 'Quality RR Complaint'
 where name = 'rrcomplaint'
   and location = 'modules/public_pages/quality/models/RRComplaint.php'
   and type = 'M';
update module_components
   set title = 'Sales Order Despatch Event'
 where name = 'sodespatchevent'
   and location = 'modules/public_pages/erp/despatch/models/SODespatchEvent.php'
   and type = 'M';
update module_components
   set title = 'Sales Order Despatch Events'
 where name = 'sodespatcheventcollection'
   and location = 'modules/public_pages/erp/despatch/models/SODespatchEventCollection.php'
   and type = 'M';
update module_components
   set title = 'Sales Order Despatch Line'
 where name = 'sodespatchline'
   and location = 'modules/public_pages/erp/despatch/models/SODespatchLine.php'
   and type = 'M';
update module_components
   set title = 'Ticket Release Version'
 where name = 'ticketreleaseversion'
   and location = 'modules/public_pages/ticketing/models/TicketReleaseVersion.php'
   and type = 'M';
update module_components
   set title = 'Ticket Release Versions'
 where name = 'ticketreleaseversioncollection'
   and location = 'modules/public_pages/ticketing/models/TicketReleaseVersionCollection.php'
   and type = 'M';
update module_components
   set title = 'Employee Training Objective'
 where name = 'trainingobjective'
   and location = 'modules/public_pages/hr/models/TrainingObjective.php'
   and type = 'M';
update module_components
   set title = 'Employee Training Objectives'
 where name = 'trainingobjectivecollection'
   and location = 'modules/public_pages/hr/models/TrainingObjectiveCollection.php'
   and type = 'M';
update module_components
   set title = 'make payment'
 where name = 'make_payment'
   and location = 'modules/public_pages/hr/templates/employees/make_payment.tpl'
   and type = 'T';
update module_components
   set title = 'confirm receipt'
 where name = 'confirmreceipt'
   and location = 'modules/public_pages/erp/goodsreceived/templates/poreceivedlines/confirmreceipt.tpl'
   and type = 'T';
update module_components
   set title = 'Quality RR Complaints'
 where name = 'rrcomplaintcollection'
   and location = 'modules/public_pages/quality/models/RRComplaintCollection.php'
   and type = 'M';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/mfcentres/new.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/mfcentres/view.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/mfcentres/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/mfdepts/new.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/mfdepts/view.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/mfdepts/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/mfresources/new.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/mfresources/view.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/mfresources/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/stproductgroups/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/stproductgroups/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/sttypecodes/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/sttypecodes/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/stuoms/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/stuoms/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/syuomconversions/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/syuomconversions/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/whactions/new.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/whactions/view.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/whactions/index.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/whlocations/view.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/whlocations/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/whstores/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/whstores/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/whtransferrules/new.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/whtransferrules/view.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/whtransferrules/index.tpl'
   and type = 'T';
update module_components
   set title = 'Quality SD Complaint'
 where name = 'sdcomplaint'
   and location = 'modules/public_pages/quality/models/SDComplaint.php'
   and type = 'M';
update module_components
   set title = 'Quality Complaint Codes'
 where name = 'complaintcodecollection'
   and location = 'modules/public_pages/quality/models/ComplaintCodeCollection.php'
   and type = 'M';
update module_components
   set title = 'Quality Complaints'
 where name = 'complaintcollection'
   and location = 'modules/public_pages/quality/models/ComplaintCollection.php'
   and type = 'M';
update module_components
   set title = 'Quality Complaint Search'
 where name = 'complaintsearch'
   and location = 'modules/public_pages/quality/models/ComplaintSearch.php'
   and type = 'M';
update module_components
   set title = 'Quality Complaint Type'
 where name = 'complainttype'
   and location = 'modules/public_pages/quality/models/ComplaintType.php'
   and type = 'M';
update module_components
   set title = 'Quality Complaint Types'
 where name = 'complainttypecollection'
   and location = 'modules/public_pages/quality/models/ComplaintTypeCollection.php'
   and type = 'M';
update module_components
   set title = 'Quality Complaint Volume'
 where name = 'complaintvolume'
   and location = 'modules/public_pages/quality/models/ComplaintVolume.php'
   and type = 'M';
update module_components
   set title = 'Quality Complaint Volumes'
 where name = 'complaintvolumecollection'
   and location = 'modules/public_pages/quality/models/ComplaintVolumeCollection.php'
   and type = 'M';
update module_components
   set title = 'Quality Supplementary Complaint Code'
 where name = 'supplementarycomplaintcode'
   and location = 'modules/public_pages/quality/models/SupplementaryComplaintCode.php'
   and type = 'M';
update module_components
   set title = 'Quality Supplementary Complaint Codes'
 where name = 'supplementarycomplaintcodecollection'
   and location = 'modules/public_pages/quality/models/SupplementaryComplaintCodeCollection.php'
   and type = 'M';
update module_components
   set title = 'actions menu'
 where name = 'actionsmenu'
   and location = 'modules/public_pages/erp/manufacturing/manufacturing_setup/templates/whactions/actionsmenu.tpl'
   and type = 'T';
update module_components
   set title = 'Publish'
 where name = 'publish'
   and location = 'modules/common/models/Publish.php'
   and type = 'M';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/production_recording/templates/mfwastetypes/new.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/quality/templates/complaintcodes/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/quality/templates/complaintcodes/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/quality/templates/complaintvolumes/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/quality/templates/complaintvolumes/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/quality/templates/rrcomplaints/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/quality/templates/rrcomplaints/index.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/quality/templates/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/quality/templates/sdcomplaints/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/quality/templates/sdcomplaints/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/quality/templates/supplementarycomplaintcodes/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/quality/templates/supplementarycomplaintcodes/index.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/erp/production_recording/templates/mfwastetypes/view.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/production_recording/templates/mfshiftdowntimes/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/output/output_setup/templates/reportdefinitions/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/output/output_setup/templates/reportdefinitions/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/output/output_setup/templates/reportparts/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/output/output_setup/templates/reportparts/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/production_recording/templates/mfshiftoutputs/index.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/ledger/purchase_ledger/templates/pltransactions/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/ledger/purchase_ledger/templates/pltransactions/new.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/erp/ledger/purchase_ledger/templates/pltransactions/view.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/invoicing/sales_invoicing/templates/sinvoices/index.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/ledger/sales_ledger/templates/sltransactions/index.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/asset_register/templates/index.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/erp/asset_register/templates/arlocations/view.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/asset_register/templates/arlocations/index.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/asset_register/templates/artransactions/index.tpl'
   and type = 'T';
update module_components
   set title = 'Asset Register Group'
 where name = 'argroup'
   and location = 'modules/public_pages/erp/asset_register/models/ARGroup.php'
   and type = 'M';
update module_components
   set title = 'Asset Register Asset Handling'
 where name = 'assethandling'
   and location = 'modules/public_pages/erp/asset_register/models/assetHandling.php'
   and type = 'M';
update module_components
   set title = 'Report Definition'
 where name = 'reportdefinition'
   and location = 'modules/public_pages/output/output_setup/models/ReportDefinition.php'
   and type = 'M';
update module_components
   set title = 'Report Definitions'
 where name = 'reportdefinitioncollection'
   and location = 'modules/public_pages/output/output_setup/models/ReportDefinitionCollection.php'
   and type = 'M';
update module_components
   set title = 'Report Part'
 where name = 'reportpart'
   and location = 'modules/public_pages/output/output_setup/models/ReportPart.php'
   and type = 'M';
update module_components
   set title = 'Quality SD Complaints'
 where name = 'sdcomplaintcollection'
   and location = 'modules/public_pages/quality/models/SDComplaintCollection.php'
   and type = 'M';
update module_components
   set title = 'view manufacturing shift'
 where name = 'viewmfshift'
   and location = 'modules/public_pages/erp/production_recording/templates/mfshiftdowntimes/viewmfshift.tpl'
   and type = 'T';
update module_components
   set title = 'selected payments list'
 where name = 'selected_payments_list'
   and location = 'modules/public_pages/erp/ledger/purchase_ledger/templates/plsuppliers/selected_payments_list.tpl'
   and type = 'T';
update module_components
   set title = 'selected payments'
 where name = 'selected_payments'
   and location = 'modules/public_pages/erp/ledger/purchase_ledger/templates/plsuppliers/selected_payments.tpl'
   and type = 'T';
update module_components
   set title = 'make payment'
 where name = 'make_payment'
   and location = 'modules/public_pages/erp/cashbook/templates/cbtransactions/make_payment.tpl'
   and type = 'T';
update module_components
   set title = 'change due date'
 where name = 'change_due_date'
   and location = 'modules/public_pages/erp/invoicing/sales_invoicing/templates/sinvoices/change_due_date.tpl'
   and type = 'T';
update module_components
   set title = 'Role'
 where name = 'role'
   and location = 'modules/public_pages/admin/models/Role.php'
   and type = 'M';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/asset_register/templates/assets/new.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/erp/asset_register/templates/assets/view.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/asset_register/templates/assets/index.tpl'
   and type = 'T';
update module_components
   set title = 'disposal'
 where name = 'disposal'
   and location = 'modules/public_pages/erp/asset_register/templates/assets/disposal.tpl'
   and type = 'T';
update module_components
   set title = 'Roles'
 where name = 'rolecollection'
   and location = 'modules/public_pages/admin/models/RoleCollection.php'
   and type = 'M';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/admin/templates/users/index.tpl'
   and type = 'T';
update module_components
   set title = 'edit'
 where name = 'edit'
   and location = 'modules/public_pages/admin/templates/users/edit.tpl'
   and type = 'T';
update module_components
   set title = 'Admin'
 where name = 'admincontroller'
   and location = 'modules/public_pages/admin/controllers/AdminController.php'
   and type = 'C';
update module_components
   set title = 'Company Roles'
 where name = 'companyrolescontroller'
   and location = 'modules/public_pages/admin/controllers/CompanyrolesController.php'
   and type = 'C';
update module_components
   set title = 'Has Permissions'
 where name = 'haspermissionscontroller'
   and location = 'modules/public_pages/admin/controllers/HaspermissionsController.php'
   and type = 'C';
update module_components
   set title = 'Has Roles'
 where name = 'hasrolescontroller'
   and location = 'modules/public_pages/admin/controllers/HasrolesController.php'
   and type = 'C';
update module_components
   set title = 'Admin Index'
 where name = 'indexcontroller'
   and location = 'modules/public_pages/admin/controllers/IndexController.php'
   and type = 'C';
update module_components
   set title = 'Admin Object Roles'
 where name = 'objectrolescontroller'
   and location = 'modules/public_pages/admin/controllers/ObjectrolesController.php'
   and type = 'C';
update module_components
   set title = 'Company Account Status'
 where name = 'accountstatus'
   and location = 'modules/public_pages/contacts/models/AccountStatus.php'
   and type = 'M';
update module_components
   set title = 'Contact Addresses'
 where name = 'addresscollection'
   and location = 'modules/public_pages/contacts/models/AddressCollection.php'
   and type = 'M';
update module_components
   set title = 'Contact Address'
 where name = 'address'
   and location = 'modules/public_pages/contacts/models/Address.php'
   and type = 'M';
update module_components
   set title = 'Admin Search'
 where name = 'adminsearch'
   and location = 'modules/public_pages/admin/models/AdminSearch.php'
   and type = 'M';
update module_components
   set title = 'Asset Register Analyses'
 where name = 'aranalysiscollection'
   and location = 'modules/public_pages/erp/asset_register/models/ARAnalysisCollection.php'
   and type = 'M';
update module_components
   set title = 'Asset Register Groups'
 where name = 'argroupcollection'
   and location = 'modules/public_pages/erp/asset_register/models/ARGroupCollection.php'
   and type = 'M';
update module_components
   set title = 'Asset Register Location'
 where name = 'arlocation'
   and location = 'modules/public_pages/erp/asset_register/models/ARLocation.php'
   and type = 'M';
update module_components
   set title = 'Asset Register Locations'
 where name = 'arlocationcollection'
   and location = 'modules/public_pages/erp/asset_register/models/ARLocationCollection.php'
   and type = 'M';
update module_components
   set title = 'Asset Register Transaction'
 where name = 'artransaction'
   and location = 'modules/public_pages/erp/asset_register/models/ARTransaction.php'
   and type = 'M';
update module_components
   set title = 'Asset Register Transactions'
 where name = 'artransactioncollection'
   and location = 'modules/public_pages/erp/asset_register/models/ARTransactionCollection.php'
   and type = 'M';
update module_components
   set title = 'Asset Register Asset'
 where name = 'asset'
   and location = 'modules/public_pages/erp/asset_register/models/Asset.php'
   and type = 'M';
update module_components
   set title = 'Asset Register Assets'
 where name = 'assetcollection'
   and location = 'modules/public_pages/erp/asset_register/models/AssetCollection.php'
   and type = 'M';
update module_components
   set title = 'Company Permission'
 where name = 'companypermission'
   and location = 'modules/public_pages/admin/models/Companypermission.php'
   and type = 'M';
update module_components
   set title = 'Company Permissions'
 where name = 'companypermissioncollection'
   and location = 'modules/public_pages/admin/models/CompanypermissionCollection.php'
   and type = 'M';
update module_components
   set title = 'Company Role'
 where name = 'companyrole'
   and location = 'modules/public_pages/admin/models/CompanyRole.php'
   and type = 'M';
update module_components
   set title = 'Company Roles'
 where name = 'companyrolecollection'
   and location = 'modules/public_pages/admin/models/CompanyRoleCollection.php'
   and type = 'M';
update module_components
   set title = 'Contact Method'
 where name = 'contactmethod'
   and location = 'modules/public_pages/contacts/models/Contactmethod.php'
   and type = 'M';
update module_components
   set title = 'Contact Methods'
 where name = 'contactmethodcollection'
   and location = 'modules/public_pages/contacts/models/ContactmethodCollection.php'
   and type = 'M';
update module_components
   set title = 'Has Permission'
 where name = 'haspermission'
   and location = 'modules/public_pages/admin/models/HasPermission.php'
   and type = 'M';
update module_components
   set title = 'Has Permissions'
 where name = 'haspermissioncollection'
   and location = 'modules/public_pages/admin/models/HasPermissionCollection.php'
   and type = 'M';
update module_components
   set title = 'Has Role'
 where name = 'hasrole'
   and location = 'modules/public_pages/admin/models/HasRole.php'
   and type = 'M';
update module_components
   set title = 'Has Roles'
 where name = 'hasrolecollection'
   and location = 'modules/public_pages/admin/models/HasRoleCollection.php'
   and type = 'M';
update module_components
   set title = 'System Object Role'
 where name = 'objectrole'
   and location = 'modules/public_pages/admin/models/ObjectRole.php'
   and type = 'M';
update module_components
   set title = 'Contact Note'
 where name = 'note'
   and location = 'modules/public_pages/contacts/models/Note.php'
   and type = 'M';
update module_components
   set title = 'System Object Roles'
 where name = 'objectrolecollection'
   and location = 'modules/public_pages/admin/models/ObjectRoleCollection.php'
   and type = 'M';
update module_components
   set title = 'Party'
 where name = 'party'
   and location = 'modules/public_pages/contacts/models/Party.php'
   and type = 'M';
update module_components
   set title = 'Party Address'
 where name = 'partyaddress'
   and location = 'modules/public_pages/contacts/models/PartyAddress.php'
   and type = 'M';
update module_components
   set title = 'Party Addresses'
 where name = 'partyaddresscollection'
   and location = 'modules/public_pages/contacts/models/PartyAddressCollection.php'
   and type = 'M';
update module_components
   set title = 'permissions tree'
 where name = 'permissions_tree'
   and location = 'modules/public_pages/admin/templates/roles/permissions_tree.tpl'
   and type = 'T';
update module_components
   set title = 'sharing'
 where name = 'sharing'
   and location = 'modules/public_pages/contacts/templates/partys/sharing.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/contacts/templates/persons/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/contacts/templates/persons/new.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/contacts/templates/persons/view.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/dashboard/templates/index.tpl'
   and type = 'T';
update module_components
   set title = 'Manufacturing Stock Balance'
 where name = 'stbalance'
   and location = 'modules/public_pages/erp/manufacturing/models/STBalance.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Stock Balances'
 where name = 'stbalancecollection'
   and location = 'modules/public_pages/erp/manufacturing/models/STBalanceCollection.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Tree'
 where name = 'tree'
   and location = 'modules/public_pages/erp/manufacturing/controllers/Tree.php'
   and type = 'C';
update module_components
   set title = 'Manufacturing Warehouse Actions uzlet'
 where name = 'whactionseglet'
   and location = 'modules/public_pages/erp/manufacturing/eglets/WHActionsEGlet.php'
   and type = 'E';
update module_components
   set title = 'Manufacturing Structures'
 where name = 'mfstructurecollection'
   and location = 'modules/public_pages/erp/manufacturing/models/MFStructureCollection.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Works Order'
 where name = 'mfworkorder'
   and location = 'modules/public_pages/erp/manufacturing/models/MFWorkorder.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Works Orders'
 where name = 'mfworkordercollection'
   and location = 'modules/public_pages/erp/manufacturing/models/MFWorkorderCollection.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Works Order Structure'
 where name = 'mfwostructure'
   and location = 'modules/public_pages/erp/manufacturing/models/MFWOStructure.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Works Order Structures'
 where name = 'mfwostructurecollection'
   and location = 'modules/public_pages/erp/manufacturing/models/MFWOStructureCollection.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Operations Search'
 where name = 'operationssearch'
   and location = 'modules/public_pages/erp/manufacturing/models/operationsSearch.php'
   and type = 'M';
update module_components
   set title = 'Parties'
 where name = 'partycollection'
   and location = 'modules/public_pages/contacts/models/PartyCollection.php'
   and type = 'M';
update module_components
   set title = 'Party Contact Method'
 where name = 'partycontactmethod'
   and location = 'modules/public_pages/contacts/models/PartyContactMethod.php'
   and type = 'M';
update module_components
   set title = 'Party Contact Methods'
 where name = 'partycontactmethodcollection'
   and location = 'modules/public_pages/contacts/models/PartyContactMethodCollection.php'
   and type = 'M';
update module_components
   set title = 'Party Note'
 where name = 'partynote'
   and location = 'modules/public_pages/contacts/models/PartyNote.php'
   and type = 'M';
update module_components
   set title = 'Party Notes'
 where name = 'partynotecollection'
   and location = 'modules/public_pages/contacts/models/PartyNoteCollection.php'
   and type = 'M';
update module_components
   set title = 'People in Categories List'
 where name = 'peopleincategoriescollection'
   and location = 'modules/public_pages/contacts/models/PeopleInCategoriesCollection.php'
   and type = 'M';
update module_components
   set title = 'Stock Item'
 where name = 'stitem'
   and location = 'modules/public_pages/erp/manufacturing/models/STItem.php'
   and type = 'M';
update module_components
   set title = 'Stock Items'
 where name = 'stitemcollection'
   and location = 'modules/public_pages/erp/manufacturing/models/STItemCollection.php'
   and type = 'M';
update module_components
   set title = 'Stock Item Search'
 where name = 'stitemssearch'
   and location = 'modules/public_pages/erp/manufacturing/models/stitemsSearch.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Stock Product Groups'
 where name = 'stproductgroupcollection'
   and location = 'modules/public_pages/erp/manufacturing/models/STProductgroupCollection.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Stock Product Group'
 where name = 'stproductgroup'
   and location = 'modules/public_pages/erp/manufacturing/models/STProductgroup.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Stock Transaction'
 where name = 'sttransaction'
   and location = 'modules/public_pages/erp/manufacturing/models/STTransaction.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Structures Search'
 where name = 'structuressearch'
   and location = 'modules/public_pages/erp/manufacturing/models/structuresSearch.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Stock Transactions Search'
 where name = 'sttransactionssearch'
   and location = 'modules/public_pages/erp/manufacturing/models/sttransactionsSearch.php'
   and type = 'M';
update module_components
   set title = 'Stock Type Codes'
 where name = 'sttypecodecollection'
   and location = 'modules/public_pages/erp/manufacturing/models/STTypecodeCollection.php'
   and type = 'M';
update module_components
   set title = 'Stock Unit of Measure'
 where name = 'stuom'
   and location = 'modules/public_pages/erp/manufacturing/models/STuom.php'
   and type = 'M';
update module_components
   set title = 'Stock Units of Measure'
 where name = 'stuomcollection'
   and location = 'modules/public_pages/erp/manufacturing/models/STuomCollection.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Warehouse Stores'
 where name = 'whstorecollection'
   and location = 'modules/public_pages/erp/manufacturing/models/WHStoreCollection.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Warehouse Transfer Rule'
 where name = 'whtransferrule'
   and location = 'modules/public_pages/erp/manufacturing/models/WHTransferrule.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Warehouse Transfer Rules'
 where name = 'whtransferrulecollection'
   and location = 'modules/public_pages/erp/manufacturing/models/WHTransferruleCollection.php'
   and type = 'M';
update module_components
   set title = 'Works Order Search'
 where name = 'workorderssearch'
   and location = 'modules/public_pages/erp/manufacturing/models/workordersSearch.php'
   and type = 'M';
update module_components
   set title = 'view related'
 where name = 'view_related'
   and location = 'modules/public_pages/contacts/templates/personcontactmethods/view_related.tpl'
   and type = 'T';
update module_components
   set title = 'reset passwords'
 where name = 'reset_passwords'
   and location = 'modules/public_pages/admin/templates/users/reset_passwords.tpl'
   and type = 'T';
update module_components
   set title = 'new user'
 where name = 'newuser'
   and location = 'modules/public_pages/admin/templates/users/newuser.tpl'
   and type = 'T';
update module_components
   set title = 'convert to account'
 where name = 'converttoaccount'
   and location = 'modules/public_pages/contacts/templates/leads/converttoaccount.tpl'
   and type = 'T';
update module_components
   set title = 'Sales Order Packing Slip'
 where name = 'sopackingslip'
   and location = 'modules/public_pages/erp/order/sales_order/models/SOPackingSlip.php'
   and type = 'M';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/erp/order/sales_order/templates/sorders/view.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/order/sales_order/templates/sorders/index.tpl'
   and type = 'T';
update module_components
   set title = 'Sales Ledger Accounts On Stop uzlet'
 where name = 'accountsonstopeglet'
   and location = 'modules/public_pages/erp/ledger/sales_ledger/eglets/AccountsOnStopEGlet.php'
   and type = 'E';
update module_components
   set title = 'Sales Ledger Customers uzlet'
 where name = 'customereglet'
   and location = 'modules/public_pages/erp/ledger/sales_ledger/eglets/CustomerEGlet.php'
   and type = 'E';
update module_components
   set title = 'Sales Orders Summary uzlet'
 where name = 'salesorderssummaryeglet'
   and location = 'modules/public_pages/erp/order/sales_order/eglets/SalesOrdersSummaryEGlet.php'
   and type = 'E';
update module_components
   set title = 'Sales Orders Item Summary uzlet'
 where name = 'sordersitemsummaryeglet'
   and location = 'modules/public_pages/erp/order/sales_order/eglets/SOrdersItemSummaryEGlet.php'
   and type = 'E';
update module_components
   set title = 'Salses Orders Overdue uzlet'
 where name = 'sordersoverdueeglet'
   and location = 'modules/public_pages/erp/order/sales_order/eglets/SOrdersOverdueEGlet.php'
   and type = 'E';
update module_components
   set title = 'Top Sales Orders uzlet'
 where name = 'topsalesorderseglet'
   and location = 'modules/public_pages/erp/order/sales_order/eglets/TopSalesOrdersEGlet.php'
   and type = 'E';
update module_components
   set title = 'Works Orders Backflush Errors uzlet'
 where name = 'wordersbackflusherrorseglet'
   and location = 'modules/public_pages/erp/manufacturing/eglets/WOrdersBackflushErrorsEGlet.php'
   and type = 'E';
update module_components
   set title = 'Works Orders Book Over/Under uzlet'
 where name = 'wordersbookoverunderneweglet'
   and location = 'modules/public_pages/erp/manufacturing/eglets/WOrdersBookOverUnderNewEGlet.php'
   and type = 'E';
update module_components
   set title = 'Works Orders Book Production uzlet'
 where name = 'wordersbookproductionneweglet'
   and location = 'modules/public_pages/erp/manufacturing/eglets/WOrdersBookProductionNewEGlet.php'
   and type = 'E';
update module_components
   set title = 'Works Orders Print Paper Work uzlet'
 where name = 'wordersprintpaperworkneweglet'
   and location = 'modules/public_pages/erp/manufacturing/eglets/WOrdersPrintPaperworkNewEGlet.php'
   and type = 'E';
update module_components
   set title = 'Employee Training Plans'
 where name = 'employeetrainingplancollection'
   and location = 'modules/public_pages/hr/models/EmployeeTrainingPlanCollection.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Cost Sheet Search'
 where name = 'costsheetsearch'
   and location = 'modules/public_pages/erp/manufacturing/models/costSheetSearch.php'
   and type = 'M';
update module_components
   set title = 'HR Expense Authoriser'
 where name = 'expenseauthoriser'
   and location = 'modules/public_pages/hr/models/ExpenseAuthoriser.php'
   and type = 'M';
update module_components
   set title = 'HR Expense Authorisers'
 where name = 'expenseauthorisercollection'
   and location = 'modules/public_pages/hr/models/ExpenseAuthoriserCollection.php'
   and type = 'M';
update module_components
   set title = 'HR Expenses'
 where name = 'expensecollection'
   and location = 'modules/public_pages/hr/models/ExpenseCollection.php'
   and type = 'M';
update module_components
   set title = 'Ledger Category'
 where name = 'ledgercategory'
   and location = 'modules/public_pages/erp/ledger/models/LedgerCategory.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Centre'
 where name = 'mfcentre'
   and location = 'modules/public_pages/erp/manufacturing/models/MFCentre.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Outside Operation'
 where name = 'mfoutsideoperation'
   and location = 'modules/public_pages/erp/manufacturing/models/MFOutsideOperation.php'
   and type = 'M';
update module_components
   set title = 'Sales Order Price Type'
 where name = 'sopricetype'
   and location = 'modules/public_pages/erp/order/sales_order/models/SOPriceType.php'
   and type = 'M';
update module_components
   set title = 'Sales Order Price Types'
 where name = 'sopricetypecollection'
   and location = 'modules/public_pages/erp/order/sales_order/models/SOPriceTypeCollection.php'
   and type = 'M';
update module_components
   set title = 'Sales Order Product Line'
 where name = 'soproductline'
   and location = 'modules/public_pages/erp/order/sales_order/models/SOProductline.php'
   and type = 'M';
update module_components
   set title = 'Sales Order Product Lines'
 where name = 'soproductlinecollection'
   and location = 'modules/public_pages/erp/order/sales_order/models/SOProductlineCollection.php'
   and type = 'M';
update module_components
   set title = 'Sales Orders'
 where name = 'sordercollection'
   and location = 'modules/public_pages/erp/order/sales_order/models/SOrderCollection.php'
   and type = 'M';
update module_components
   set title = 'Sales Order Line'
 where name = 'sorderline'
   and location = 'modules/public_pages/erp/order/sales_order/models/SOrderLine.php'
   and type = 'M';
update module_components
   set title = 'Sales Order Lines'
 where name = 'sorderlinecollection'
   and location = 'modules/public_pages/erp/order/sales_order/models/SOrderLineCollection.php'
   and type = 'M';
update module_components
   set title = 'Engineering Work Schedules'
 where name = 'workschedulecollection'
   and location = 'modules/public_pages/engineering/models/WorkScheduleCollection.php'
   and type = 'M';
update module_components
   set title = 'Sales Order Search'
 where name = 'sorderssearch'
   and location = 'modules/public_pages/erp/order/sales_order/models/sordersSearch.php'
   and type = 'M';
update module_components
   set title = 'update lines'
 where name = 'updatelines'
   and location = 'modules/public_pages/erp/order/sales_order/templates/sorders/updatelines.tpl'
   and type = 'T';
update module_components
   set title = 'show products'
 where name = 'showproducts'
   and location = 'modules/public_pages/erp/order/sales_order/templates/sorders/showproducts.tpl'
   and type = 'T';
update module_components
   set title = 'select products'
 where name = 'select_products'
   and location = 'modules/public_pages/erp/order/sales_order/templates/sorders/select_products.tpl'
   and type = 'T';
update module_components
   set title = 'select for invoicing'
 where name = 'select_for_invoicing'
   and location = 'modules/public_pages/erp/order/sales_order/templates/sorders/select_for_invoicing.tpl'
   and type = 'T';
update module_components
   set title = 'review orders'
 where name = 'revieworders'
   and location = 'modules/public_pages/erp/order/sales_order/templates/sorders/revieworders.tpl'
   and type = 'T';
update module_components
   set title = 'payment terms'
 where name = 'payment_terms'
   and location = 'modules/public_pages/erp/order/sales_order/templates/sorders/payment_terms.tpl'
   and type = 'T';
update module_components
   set title = 'confirm sale'
 where name = 'confirm_sale'
   and location = 'modules/public_pages/erp/order/sales_order/templates/sorders/confirm_sale.tpl'
   and type = 'T';
update module_components
   set title = 'confirm pick list'
 where name = 'confirm_pick_list'
   and location = 'modules/public_pages/erp/order/sales_order/templates/sorders/confirm_pick_list.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/order/sales_order/templates/sorders/new.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/invoicing/sales_invoicing/templates/sinvoices/new.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/erp/invoicing/sales_invoicing/templates/sinvoices/view.tpl'
   and type = 'T';
update module_components
   set title = 'Ticket Attachment'
 where name = 'ticketattachment'
   and location = 'modules/public_pages/ticketing/models/TicketAttachment.php'
   and type = 'M';
update module_components
   set title = 'eltransaction'
 where name = 'eltransaction'
   and location = 'modules/public_pages/hr/models/ELTransaction.php'
   and type = 'M';
update module_components
   set title = 'eltransactioncollection'
 where name = 'eltransactioncollection'
   and location = 'modules/public_pages/hr/models/ELTransactionCollection.php'
   and type = 'M';
update module_components
   set title = 'dashboard'
 where name = 'dashboard'
   and location = 'modules/public_pages/shared/templates/dashboard.tpl'
   and type = 'T';
update module_components
   set title = 'reconcile'
 where name = 'reconcile'
   and location = 'modules/public_pages/erp/cashbook/templates/bankaccounts/reconcile.tpl'
   and type = 'T';
update module_components
   set title = 'import'
 where name = 'import'
   and location = 'modules/public_pages/shared/templates/import.tpl'
   and type = 'T';
update module_components
   set title = 'sharing'
 where name = 'sharing'
   and location = 'modules/public_pages/shared/templates/sharing.tpl'
   and type = 'T';
update module_components
   set title = 'Ticket Attachments'
 where name = 'ticketattachmentcollection'
   and location = 'modules/public_pages/ticketing/models/TicketAttachmentCollection.php'
   and type = 'M';
update module_components
   set title = 'Cash Book Transaction'
 where name = 'cbtransaction'
   and location = 'modules/public_pages/erp/cashbook/models/CBTransaction.php'
   and type = 'M';
update module_components
   set title = 'Cash Book Transactions'
 where name = 'cbtransactioncollection'
   and location = 'modules/public_pages/erp/cashbook/models/CBTransactionCollection.php'
   and type = 'M';
update module_components
   set title = 'Cash Book Transaction Search'
 where name = 'cbtransactionssearch'
   and location = 'modules/public_pages/erp/cashbook/models/cbtransactionsSearch.php'
   and type = 'M';
update module_components
   set title = 'EDI Data Mapping Detail'
 where name = 'datamappingdetail'
   and location = 'modules/public_pages/edi/models/DataMappingDetail.php'
   and type = 'M';
update module_components
   set title = 'EDI Data Mapping Details'
 where name = 'datamappingdetailcollection'
   and location = 'modules/public_pages/edi/models/DataMappingDetailCollection.php'
   and type = 'M';
update module_components
   set title = 'EDI Data Mapping Rule'
 where name = 'datamappingrule'
   and location = 'modules/public_pages/edi/models/DataMappingRule.php'
   and type = 'M';
update module_components
   set title = 'Engineering Search'
 where name = 'engineeringsearch'
   and location = 'modules/public_pages/engineering/models/EngineeringSearch.php'
   and type = 'M';
update module_components
   set title = 'Employee Hours Search'
 where name = 'hourssearch'
   and location = 'modules/public_pages/shared/models/HoursSearch.php'
   and type = 'M';
update module_components
   set title = 'Logged Call'
 where name = 'loggedcall'
   and location = 'modules/public_pages/shared/models/LoggedCall.php'
   and type = 'M';
update module_components
   set title = 'Logged Calls'
 where name = 'loggedcallcollection'
   and location = 'modules/public_pages/shared/models/LoggedCallCollection.php'
   and type = 'M';
update module_components
   set title = 'Periodic Payment'
 where name = 'periodicpayment'
   and location = 'modules/public_pages/erp/cashbook/models/PeriodicPayment.php'
   and type = 'M';
update module_components
   set title = 'Periodic Payments'
 where name = 'periodicpaymentcollection'
   and location = 'modules/public_pages/erp/cashbook/models/PeriodicPaymentCollection.php'
   and type = 'M';
update module_components
   set title = 'Periodic Payments Search'
 where name = 'periodicpaymentssearch'
   and location = 'modules/public_pages/erp/cashbook/models/PeriodicPaymentsSearch.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Warehouse Transfer Line'
 where name = 'whtransferline'
   and location = 'modules/public_pages/erp/despatch/models/WHTransferline.php'
   and type = 'M';
update module_components
   set title = 'uzLET'
 where name = 'uzlet'
   and location = 'modules/public_pages/uzlets/uzlet_setup/models/Uzlet.php'
   and type = 'M';
update module_components
   set title = 'uzLET Call'
 where name = 'uzletcall'
   and location = 'modules/public_pages/uzlets/uzlet_setup/models/UzletCall.php'
   and type = 'M';
update module_components
   set title = 'Engineering Work Schedule Parts'
 where name = 'workschedulepartcollection'
   and location = 'modules/public_pages/engineering/models/WorkSchedulePartCollection.php'
   and type = 'M';
update module_components
   set title = 'view related'
 where name = 'view_related'
   and location = 'modules/public_pages/shared/templates/view_related.tpl'
   and type = 'T';
update module_components
   set title = 'view by dates'
 where name = 'viewbydates'
   and location = 'modules/public_pages/erp/order/sales_order/templates/sorders/viewbydates.tpl'
   and type = 'T';
update module_components
   set title = 'select invoices'
 where name = 'selectinvoices'
   and location = 'modules/public_pages/erp/invoicing/sales_invoicing/templates/sinvoices/selectinvoices.tpl'
   and type = 'T';
update module_components
   set title = 'refresh eglet'
 where name = 'refresheglet'
   and location = 'modules/public_pages/shared/templates/refresheglet.tpl'
   and type = 'T';
update module_components
   set title = 'print action'
 where name = 'printaction'
   and location = 'modules/public_pages/shared/templates/printaction.tpl'
   and type = 'T';
update module_components
   set title = 'module setup view'
 where name = 'module_setup_view'
   and location = 'modules/public_pages/shared/templates/module_setup_view.tpl'
   and type = 'T';
update module_components
   set title = 'module setup index'
 where name = 'module_setup_index'
   and location = 'modules/public_pages/shared/templates/module_setup_index.tpl'
   and type = 'T';
update module_components
   set title = 'hours index'
 where name = 'hours_index'
   and location = 'modules/public_pages/shared/templates/hours_index.tpl'
   and type = 'T';
update module_components
   set title = 'hours new'
 where name = 'hours_new'
   and location = 'modules/public_pages/shared/templates/hours_new.tpl'
   and type = 'T';
update module_components
   set title = 'enter payment'
 where name = 'enter_payment'
   and location = 'modules/public_pages/shared/templates/enter_payment.tpl'
   and type = 'T';
update module_components
   set title = 'enter journal'
 where name = 'enter_journal'
   and location = 'modules/public_pages/shared/templates/enter_journal.tpl'
   and type = 'T';
update module_components
   set title = 'edit dashboard'
 where name = 'edit_dashboard'
   and location = 'modules/public_pages/shared/templates/edit_dashboard.tpl'
   and type = 'T';
update module_components
   set title = 'calls new'
 where name = 'calls_new'
   and location = 'modules/public_pages/shared/templates/calls_new.tpl'
   and type = 'T';
update module_components
   set title = 'Sales Order Despatch Lines'
 where name = 'sodespatchlinecollection'
   and location = 'modules/public_pages/erp/despatch/models/SODespatchLineCollection.php'
   and type = 'M';
update module_components
   set title = 'production_recording'
 where name = 'production_recording'
   and location = 'modules/public_pages/erp/production_recording/resources/js/production_recording.js'
   and type = 'J';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/production_recording/templates/mfdowntimecodes/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/erp/production_recording/templates/mfdowntimecodes/new.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/erp/production_recording/templates/mfdowntimecodes/view.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/production_recording/templates/mfshiftdowntimes/index.tpl'
   and type = 'T';
update module_components
   set title = 'Sales Order Despatch Search'
 where name = 'sodespatchsearch'
   and location = 'modules/public_pages/erp/despatch/models/sodespatchSearch.php'
   and type = 'M';
update module_components
   set title = 'output_setup'
 where name = 'output_setup'
   and location = 'modules/public_pages/uzlets/uzlet_setup/resources/js/output_setup.js'
   and type = 'J';
update module_components
   set title = 'Production Recording Shift Outputs'
 where name = 'mfshiftoutputscontroller'
   and location = 'modules/public_pages/erp/production_recording/controllers/MfshiftoutputsController.php'
   and type = 'C';
update module_components
   set title = 'Production Recording Shift Wastes'
 where name = 'mfshiftwastescontroller'
   and location = 'modules/public_pages/erp/production_recording/controllers/MfshiftwastesController.php'
   and type = 'C';
update module_components
   set title = 'Production Recording'
 where name = 'productionrecordingcontroller'
   and location = 'modules/public_pages/erp/production_recording/controllers/ProductionRecordingController.php'
   and type = 'C';
update module_components
   set title = 'Asset Register Search'
 where name = 'assetssearch'
   and location = 'modules/public_pages/erp/asset_register/models/assetsSearch.php'
   and type = 'M';
update module_components
   set title = 'EDI Data Mapping Rules'
 where name = 'datamappingrulecollection'
   and location = 'modules/public_pages/edi/models/DataMappingRuleCollection.php'
   and type = 'M';
update module_components
   set title = 'Employees'
 where name = 'employeecollection'
   and location = 'modules/public_pages/hr/models/EmployeeCollection.php'
   and type = 'M';
update module_components
   set title = 'VAT Intrastat Transaction Types'
 where name = 'intrastattranstypecollection'
   and location = 'modules/public_pages/erp/ledger/vat/models/IntrastatTransTypeCollection.php'
   and type = 'M';
update module_components
   set title = 'Ledger Categories'
 where name = 'ledgercategorycollection'
   and location = 'modules/public_pages/erp/ledger/models/LedgerCategoryCollection.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Shifts'
 where name = 'mfshiftcollection'
   and location = 'modules/public_pages/erp/production_recording/models/MFShiftCollection.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Centre Downtime Code'
 where name = 'mfcentredowntimecode'
   and location = 'modules/public_pages/erp/production_recording/models/MFCentreDowntimeCode.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Shift Downtime'
 where name = 'mfshiftdowntime'
   and location = 'modules/public_pages/erp/production_recording/models/MFShiftDowntime.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Centre Waste Type'
 where name = 'mfcentrewastetype'
   and location = 'modules/public_pages/erp/production_recording/models/MFCentreWasteType.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Downtime Code'
 where name = 'mfdowntimecode'
   and location = 'modules/public_pages/erp/production_recording/models/MFDowntimeCode.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Shift'
 where name = 'mfshift'
   and location = 'modules/public_pages/erp/production_recording/models/MFShift.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Centre Waste Types'
 where name = 'mfcentrewastetypecollection'
   and location = 'modules/public_pages/erp/production_recording/models/MFCentreWasteTypeCollection.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Shift Downtimes'
 where name = 'mfshiftdowntimecollection'
   and location = 'modules/public_pages/erp/production_recording/models/MFShiftDowntimeCollection.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Shift Output'
 where name = 'mfshiftoutput'
   and location = 'modules/public_pages/erp/production_recording/models/MFShiftOutput.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Shift Outputs'
 where name = 'mfshiftoutputcollection'
   and location = 'modules/public_pages/erp/production_recording/models/MFShiftOutputCollection.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Shift Output Search'
 where name = 'mfshiftssearch'
   and location = 'modules/public_pages/erp/production_recording/models/mfshiftsSearch.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Shift Waste'
 where name = 'mfshiftwaste'
   and location = 'modules/public_pages/erp/production_recording/models/MFShiftWaste.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Shift Waste List'
 where name = 'mfshiftwastecollection'
   and location = 'modules/public_pages/erp/production_recording/models/MFShiftWasteCollection.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Waste Type'
 where name = 'mfwastetype'
   and location = 'modules/public_pages/erp/production_recording/models/MFWasteType.php'
   and type = 'M';
update module_components
   set title = 'uzLET Calls'
 where name = 'uzletcallcollection'
   and location = 'modules/public_pages/uzlets/uzlet_setup/models/UzletCallCollection.php'
   and type = 'M';
update module_components
   set title = 'uzLETs'
 where name = 'uzletcollection'
   and location = 'modules/public_pages/uzlets/uzlet_setup/models/UzletCollection.php'
   and type = 'M';
update module_components
   set title = 'uzLET Module'
 where name = 'uzletmodule'
   and location = 'modules/public_pages/uzlets/uzlet_setup/models/UzletModule.php'
   and type = 'M';
update module_components
   set title = 'uzLET Modules'
 where name = 'uzletmodulecollection'
   and location = 'modules/public_pages/uzlets/uzlet_setup/models/UzletModuleCollection.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Warehouse Transfer'
 where name = 'whtransfer'
   and location = 'modules/public_pages/erp/despatch/models/WHTransfer.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Warehouse Transfers'
 where name = 'whtransfercollection'
   and location = 'modules/public_pages/erp/despatch/models/WHTransferCollection.php'
   and type = 'M';
update module_components
   set title = 'uzlet setup'
 where name = 'uzlet_setup'
   and location = 'modules/public_pages/uzlets/uzlet_setup/resources/css/uzlet_setup.css'
   and type = 'S';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/uzlets/uzlet_setup/templates/uzlets/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/uzlets/uzlet_setup/templates/uzlets/new.tpl'
   and type = 'T';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/erp/despatch/templates/sodespatchlines/index.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/erp/despatch/templates/whtransferlines/view.tpl'
   and type = 'T';
update module_components
   set title = 'system_admin'
 where name = 'system_admin'
   and location = 'modules/public_pages/system_admin/resources/js/system_admin.js'
   and type = 'J';
update module_components
   set title = 'index'
 where name = 'index'
   and location = 'modules/public_pages/system_admin/templates/usercompanyaccesss/index.tpl'
   and type = 'T';
update module_components
   set title = 'new'
 where name = 'new'
   and location = 'modules/public_pages/system_admin/templates/usercompanyaccesss/new.tpl'
   and type = 'T';
update module_components
   set title = 'view'
 where name = 'view'
   and location = 'modules/public_pages/system_admin/templates/usercompanyaccesss/view.tpl'
   and type = 'T';
update module_components
   set title = 'Manufacturing Warehouse Transfer Lines'
 where name = 'whtransferlinecollection'
   and location = 'modules/public_pages/erp/despatch/models/WHTransferlineCollection.php'
   and type = 'M';
update module_components
   set title = 'Manufacturing Warehouse Transfer Search'
 where name = 'whtransferssearch'
   and location = 'modules/public_pages/erp/despatch/models/whtransfersSearch.php'
   and type = 'M';
update module_components
   set title = 'view permissions'
 where name = 'viewpermissions'
   and location = 'modules/public_pages/system_admin/templates/permissions/viewpermissions.tpl'
   and type = 'T';
update module_components
   set title = 'view by orders'
 where name = 'viewbyorders'
   and location = 'modules/public_pages/erp/despatch/templates/sodespatchlines/viewbyorders.tpl'
   and type = 'T';
update module_components
   set title = 'system error'
 where name = 'systemerror'
   and location = 'modules/public_pages/system_admin/templates/systemerror.tpl'
   and type = 'T';
update module_components
   set title = 'summary by orders'
 where name = 'summarybyorders'
   and location = 'modules/public_pages/erp/despatch/templates/sodespatchlines/summarybyorders.tpl'
   and type = 'T';
update module_components
   set title = 'confirm despatch'
 where name = 'confirmdespatch'
   and location = 'modules/public_pages/erp/despatch/templates/sodespatchlines/confirmdespatch.tpl'
   and type = 'T';
update module_components
   set title = 'cancel despatch'
 where name = 'canceldespatch'
   and location = 'modules/public_pages/erp/despatch/templates/sodespatchlines/canceldespatch.tpl'
   and type = 'T';

UPDATE module_components
   SET title = name
 WHERE title is null;

