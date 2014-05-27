--
-- $Revision: 1.15 $
--

-------------------------------------------------------------------------------

-- Projects
-------------------------------------------------------------------------------

ALTER TABLE projects ADD COLUMN createdby character varying;

UPDATE projects
   SET createdby = owner;

ALTER TABLE projects ADD COLUMN status character varying;
ALTER TABLE projects ALTER COLUMN status SET DEFAULT 'N'::character varying;

UPDATE projects
   SET status='A';

UPDATE projects
   SET status='C'
 WHERE completed;

DROP VIEW projectsoverview;

ALTER TABLE projects ALTER COLUMN status SET NOT NULL;
ALTER TABLE projects DROP COLUMN completed;

CREATE OR REPLACE VIEW projectsoverview AS 
 SELECT pr.*
 , c.name AS company
 , (p.firstname::text || ' '::text) || p.surname::text AS person
 , cat.name AS category
 , wt.title AS work_type
 , ph.name AS phase
 , u.username AS usernameaccess
   FROM projects pr
   LEFT JOIN company c ON pr.company_id = c.id
   LEFT JOIN person p ON pr.person_id = p.id
   LEFT JOIN project_categories cat ON pr.category_id = c.id
   LEFT JOIN project_work_types wt ON pr.work_type_id = wt.id
   LEFT JOIN project_phases ph ON pr.phase_id = ph.id
   LEFT JOIN users u ON pr.person_id = u.person_id;

ALTER TABLE projectsoverview OWNER TO "www-data";


-------------------------------------------------------------------------------

-- Project Equipment
-------------------------------------------------------------------------------

DROP VIEW project_equipment_overview;

ALTER TABLE project_equipment RENAME COLUMN hourly_cost TO cost_rate;
ALTER TABLE project_equipment ADD COLUMN uom_id bigint;
ALTER TABLE project_equipment ADD COLUMN created character varying;
ALTER TABLE project_equipment ADD COLUMN createdby character varying;
ALTER TABLE project_equipment ADD COLUMN lastupdated character varying;
ALTER TABLE project_equipment ADD COLUMN alteredby character varying;
ALTER TABLE project_equipment DROP COLUMN red;
ALTER TABLE project_equipment DROP COLUMN amber;
ALTER TABLE project_equipment DROP COLUMN green;
ALTER TABLE project_equipment DROP COLUMN usable_hours;

ALTER TABLE project_equipment
  ADD CONSTRAINT project_equipment_uom_id FOREIGN KEY (uom_id)
      REFERENCES st_uoms (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE;

CREATE OR REPLACE VIEW project_equipment_overview AS 
 SELECT e.*
 , u.uom_name
   FROM project_equipment e
   JOIN st_uoms u ON u.id = e.uom_id;

ALTER TABLE project_equipment_overview OWNER TO "www-data";

CREATE TABLE project_equipment_allocation
(
  id bigserial NOT NULL,
  project_id bigint NOT NULL,
  task_id bigint,
  project_equipment_id bigint NOT NULL,
  start_date date NOT NULL,
  end_date date NOT NULL,
  charging_period_uom_id bigint NOT NULL,
  setup_charge numeric NOT NULL DEFAULT 0,
  charge_rate numeric NOT NULL DEFAULT 0,
  charge_rate_uom_id bigint NOT NULL,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT project_equipment_allocation_pkey PRIMARY KEY (id),
  CONSTRAINT project_equipment_allocation_project_id_fkey FOREIGN KEY (project_id)
      REFERENCES projects (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT project_equipment_allocation_task_id_fkey FOREIGN KEY (task_id)
      REFERENCES tasks (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT project_equipment_allocation_charging_period_uom_id_fkey FOREIGN KEY (charging_period_uom_id)
      REFERENCES st_uoms (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT project_equipment_allocation_uom_id_fkey FOREIGN KEY (charge_rate_uom_id)
      REFERENCES st_uoms (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT project_equipment_allocation_project_equipment_id_fkey FOREIGN KEY (project_equipment_id)
      REFERENCES project_equipment (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT project_equipment_allocation_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
);

ALTER TABLE project_equipment_allocation OWNER TO "www-data";

INSERT INTO project_equipment_allocation
  (project_id, task_id, project_equipment_id, start_date, end_date, setup_charge, charge_rate)
SELECT project_id, id, equipment_id, start_date, end_date, equipment_setup_cost, equipment_hourly_cost
  FROM tasks
 WHERE equipment_id is not null;

DROP VIEW IF EXISTS project_equipment_allocation_overview;

CREATE OR REPLACE VIEW project_equipment_allocation_overview AS 
 SELECT pea.*
 , u1.uom_name as charging_period_uom
 , u2.uom_name as charge_rate_uom
 , p.name AS project
 , t.name AS task
 , pe.name AS equipment
   FROM project_equipment_allocation pea
   JOIN st_uoms u1 ON u1.id = pea.charging_period_uom_id
   JOIN st_uoms u2 ON u2.id = pea.charge_rate_uom_id
   JOIN projects p ON p.id = pea.project_id
   JOIN project_equipment pe ON pe.id = pea.project_equipment_id
   LEFT JOIN tasks t ON t.id = pea.task_id;
   
ALTER TABLE project_equipment_allocation_overview OWNER TO "www-data";

DROP VIEW IF EXISTS project_equipment_charges;

CREATE OR REPLACE VIEW project_equipment_charges AS
SELECT pea.*
, u1.uom_name as charging_period
, u2.uom_name
, pe.name as equipment
, (end_date-start_date+1)*conversion_factor as quantity
, setup_cost+(cost_rate*(end_date-start_date+1)*conversion_factor) as total_costs
, setup_charge+(charge_rate*(end_date-start_date+1)*conversion_factor) as total_charges
  FROM project_equipment_allocation pea
  JOIN project_equipment pe ON pe.id = pea.project_equipment_id
  JOIN st_uoms u1 ON u1.id = pea.charging_period_uom_id
  JOIN st_uoms u2 ON u2.id = pea.charge_rate_uom_id
  JOIN sy_uom_conversions syu ON syu.from_uom_id = pea.charging_period_uom_id
                             AND syu.to_uom_id = pea.charge_rate_uom_id;

ALTER TABLE project_equipment_charges OWNER TO "www-data";


-------------------------------------------------------------------------------

-- Tasks
-------------------------------------------------------------------------------

ALTER TABLE tasks ADD COLUMN usercompanyid bigint;

UPDATE tasks
   SET usercompanyid = (select min(id)
                          from system_companies);

ALTER TABLE tasks ALTER COLUMN usercompanyid SET NOT NULL;

DROP VIEW tasksoverview;

ALTER TABLE tasks DROP COLUMN budget;
ALTER TABLE tasks DROP COLUMN equipment_id;
ALTER TABLE tasks DROP COLUMN equipment_setup_cost;
ALTER TABLE tasks DROP COLUMN equipment_hourly_cost;

CREATE OR REPLACE VIEW tasksoverview AS 
 SELECT t.*
 , t.end_date - t.start_date AS difference
 , pr.name AS project, pri.name AS priority, pt.name AS parent
   FROM tasks t
   LEFT JOIN projects pr ON t.project_id = pr.id
   LEFT JOIN tasks pt ON t.parent_id = pt.id
   LEFT JOIN task_priorities pri ON t.priority_id = pri.id;

ALTER TABLE tasksoverview OWNER TO "www-data";


-------------------------------------------------------------------------------

-- Project Resources
-------------------------------------------------------------------------------

DROP VIEW projectsoverview;

DROP VIEW resourcesoverview;

DROP VIEW task_resources_overview;

DROP TABLE task_resources;

DROP TABLE resources;

CREATE TABLE project_resources
(
  id bigserial NOT NULL,
  person_id bigint,
  project_id bigint NOT NULL,
  task_id bigint,
  resource_id bigint NOT NULL,
  start_date timestamp NOT NULL,
  end_date timestamp NOT NULL,
  quantity bigint NOT NULL default 1,
  usercompanyid bigint,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  lastupdated timestamp without time zone,
  alteredby character varying,
  CONSTRAINT project_resources_pkey PRIMARY KEY (id),
  CONSTRAINT project_resources_person_id FOREIGN KEY (person_id)
      REFERENCES person (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT project_resources_project_id FOREIGN KEY (project_id)
      REFERENCES projects (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT project_resources_task_id FOREIGN KEY (task_id)
      REFERENCES tasks (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT project_resources_resources_id_fkey FOREIGN KEY (resource_id)
      REFERENCES mf_resources (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT project_resources_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT project_resources_unq UNIQUE (project_id, resource_id, start_date, task_id, person_id, usercompanyid)
);

ALTER TABLE project_resources OWNER TO "www-data";

CREATE OR REPLACE VIEW project_resources_overview AS 
 SELECT prs.*
     , prj.name AS project
     , tsk.name AS task
     , mfr.resource_code||' - '||mfr.description AS resource, mfr.resource_rate
     , (per.firstname::text || ' '::text) || per.surname::text AS person
   FROM project_resources prs
   JOIN projects prj ON prs.project_id = prj.id
   JOIN mf_resources mfr ON prs.resource_id =mfr.id
   LEFT JOIN tasks tsk ON prs.task_id = tsk.id
   LEFT JOIN person per ON prs.person_id = per.id;

ALTER TABLE project_resources_overview OWNER TO "www-data";

CREATE OR REPLACE VIEW projectsoverview AS 
 SELECT pr.*
 , c.name AS company
 , (p.firstname::text || ' '::text) || p.surname::text AS person
 , cat.name AS category
 , wt.title AS work_type
 , ph.name AS phase
 , u.username AS usernameaccess
   FROM projects pr
   LEFT JOIN company c ON pr.company_id = c.id
   LEFT JOIN person p ON pr.person_id = p.id
   LEFT JOIN project_categories cat ON pr.category_id = c.id
   LEFT JOIN project_work_types wt ON pr.work_type_id = wt.id
   LEFT JOIN project_phases ph ON pr.phase_id = ph.id
   LEFT JOIN users u ON pr.person_id = u.person_id;

ALTER TABLE projectsoverview OWNER TO "www-data";


-------------------------------------------------------------------------------

-- Project Hours
-------------------------------------------------------------------------------

DROP VIEW IF EXISTS project_hours_overview;
DROP VIEW IF EXISTS task_hours_overview;

ALTER TABLE hours ADD COLUMN person_id integer;
ALTER TABLE hours ADD COLUMN createdby character varying;
ALTER TABLE hours ADD COLUMN updatedby character varying;
ALTER TABLE hours DROP COLUMN equipment;

ALTER TABLE hours
  ADD CONSTRAINT hours_person_id FOREIGN KEY (person_id)
      REFERENCES person (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE;

update hours
   set person_id = (select u.person_id
                      from users u
                     where u.username = hours.owner);

ALTER TABLE hours DROP COLUMN "owner";

DROP VIEW IF EXISTS hoursoverview;

CREATE OR REPLACE VIEW hoursoverview AS 
 SELECT h.*
 , u.firstname||' '||u.surname as person
 , p.name as project, t.name as task, k.name as ticket, o.name as opportunity
   FROM hours h
   JOIN person u ON u.id = h.person_id
   LEFT JOIN projects p ON p.id = h.project_id
   LEFT JOIN tasks t ON t.id = h.task_id
   LEFT JOIN tickets k ON k.id = h.ticket_id
   LEFT JOIN opportunities o ON o.id = h.opportunity_id;

ALTER TABLE hoursoverview OWNER TO "www-data";

DROP VIEW IF EXISTS project_hours_overview;

CREATE OR REPLACE VIEW project_hours_overview AS 
 SELECT h.start_time||' '||h.person_id as id
 , p.id as project_id, p.name, p.usercompanyid
 , h.id as hour_id, h.start_time, h.duration
 , m.resource_rate
 , u.id as person_id, u.firstname ||' '||u.surname as person
   FROM projects p
   JOIN hours h ON h.project_id = p.id
   JOIN person u ON u.id = h.person_id
   JOIN project_resources r ON r.person_id = h.person_id
                           AND r.project_id = p.id
   JOIN mf_resources m ON m.id = r.resource_id;

ALTER TABLE project_hours_overview OWNER TO "www-data";

DROP VIEW IF EXISTS task_hours_overview;

CREATE OR REPLACE VIEW task_hours_overview AS 
 SELECT h.start_time||' '||h.person_id as id
 , t.id as task_id, t.name, t.usercompanyid
 , h.id as hour_id, h.start_time, h.duration
 , m.resource_rate
 , u.id as person_id, u.firstname ||' '||u.surname as person
   FROM tasks t
   JOIN hours h ON h.task_id = t.id
   JOIN person u ON u.id = h.person_id
   JOIN project_resources r ON r.person_id = h.person_id
                           AND r.task_id = t.id
   JOIN mf_resources m ON m.id = r.resource_id;

ALTER TABLE task_hours_overview OWNER TO "www-data";

INSERT INTO permissions
	(permission, type, title, display, parent_id, position)
	SELECT 'hours', 'c', 'Hours', true, id, p.position
	FROM permissions
	    ,(select max(position)+1 as position
	        from permissions
	       where type='c'
	         and parent_id=(select id
	                          from permissions
	                         WHERE type='m'
	                           AND permission='hr'
	                           AND parent_id is null)) p
	WHERE type='m'
	AND permission='hr'
	AND parent_id is null;

INSERT INTO permissions
	(permission, type, title, display, parent_id, position)
	SELECT 'index', 'a', 'Hours', true, c.id, 1
	FROM permissions c
	   , permissions p
	WHERE c.type='c'
	  AND c.permission='hours'
	  and p.permission='hr'
	  and c.parent_id=p.id;

INSERT INTO permissions
	(permission, type, title, display, parent_id, position)
	SELECT 'hours', 'c', 'Hours', true, id, p.position
	FROM permissions c
	    ,(select max(position)+1 as position
	        from permissions
	       where type='c'
	         and parent_id=(select id
	                          from permissions
	                         WHERE type='m'
	                           AND permission='hr'
	                           AND parent_id is not null)) p
	WHERE type='m'
	AND permission='hr'
	AND parent_id is not null;

INSERT INTO permissions
	(permission, type, title, display, parent_id, position)
	SELECT 'view_my_hours', 'a', 'My Hours', true, id, p.position
	FROM permissions c
	    ,(select coalesce(max(position)+1,1) as position
	        from permissions
	       where type='a'
	         and parent_id=(select a.id
	                          from permissions a
	                              ,permissions b
	                         WHERE a.type='c'
	                           AND a.permission='hours'
	                           AND b.permission='hr'
	                           AND a.parent_id = b.id
	                           AND b.parent_id is not null)) p
	WHERE type='c'
	AND permission='hours'
	AND exists (select id
	              from permissions
	             where type='m'
	               and permission='hr'
	               and parent_id is not null
	               and id=c.parent_id);

-------------------------------------------------------------------------------

-- Project Expenses
-------------------------------------------------------------------------------

ALTER TABLE expenses_header ADD COLUMN project_id bigint;
ALTER TABLE expenses_header ADD COLUMN task_id bigint;

ALTER TABLE expenses_header
  ADD CONSTRAINT expenses_header_project_id FOREIGN KEY (project_id)
      REFERENCES projects (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE expenses_header
  ADD CONSTRAINT expenses_header_task_id FOREIGN KEY (task_id)
      REFERENCES tasks (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE;
      
DROP VIEW expenses_header_overview;

CREATE OR REPLACE VIEW expenses_header_overview AS 
 SELECT eh.*
 , p.firstname || ' ' || p.surname as person, cum.currency, twc.currency AS twin
   FROM expenses_header eh
   JOIN employees e ON eh.employee_id = e.id
   JOIN person p ON e.person_id = p.id
   JOIN cumaster cum ON eh.currency_id = cum.id
   JOIN cumaster twc ON eh.twin_currency_id = twc.id;

ALTER TABLE expenses_header_overview OWNER TO "www-data";

DROP VIEW expenses_lines_overview;

CREATE OR REPLACE VIEW expenses_lines_overview AS 
 SELECT el.id, el.expenses_header_id, el.line_number
 , el.item_description, el.qty, el.purchase_price, el.currency_id, el.rate, el.gross_value
 , el.tax_value, el.tax_rate_id, el.net_value, el.twin_currency_id, el.twin_rate
 , el.twin_gross_value, el.twin_tax_value, el.twin_net_value, el.base_gross_value
 , el.base_tax_value, el.base_net_value, el.glaccount_id, el.glcentre_id, el.created
 , el.createdby, el.alteredby, el.lastupdated, el.usercompanyid
 , eh.expense_date, eh.expense_number, eh.project_id, eh.task_id
 , p.firstname || ' ' || p.surname as person
  , cu.currency
 , (glc.cost_centre::text || ' - '::text) || glc.description::text AS glcentre
 , (gla.account::text || ' - '::text) || gla.description::text AS glaccount
 , t.taxrate
   FROM expenses_lines el
   JOIN gl_centres glc ON glc.id = el.glcentre_id
   JOIN gl_accounts gla ON gla.id = el.glaccount_id
   JOIN cumaster cu ON cu.id = el.currency_id
   JOIN expenses_header eh ON eh.id = el.expenses_header_id
   JOIN employees e ON eh.employee_id = e.id
   JOIN person p ON e.person_id = p.id
   left JOIN taxrates t ON t.id = el.tax_rate_id;

ALTER TABLE expenses_lines_overview OWNER TO "www-data";


-------------------------------------------------------------------------------

-- Project Budgets
-------------------------------------------------------------------------------

CREATE TABLE project_budgets
(
  id bigserial NOT NULL,
  project_id bigint NOT NULL,
  task_id bigint,
  budget_item_id bigint,
  budget_item_type character varying NOT NULL,
  quantity numeric NOT NULL DEFAULT 0,
  uom_id bigint NOT NULL,
  setup_cost numeric DEFAULT 0.00,
  cost_rate numeric DEFAULT 0.00,
  total_cost_rate numeric DEFAULT 0.00,
  setup_charge numeric DEFAULT 0.00,
  charge_rate numeric DEFAULT 0.00,
  total_charge_rate numeric DEFAULT 0.00,
  description character varying NOT NULL,
  usercompanyid bigint,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  lastupdated timestamp without time zone,
  alteredby character varying,
  CONSTRAINT project_budgets_pkey PRIMARY KEY (id),
  CONSTRAINT project_budgets_project_id FOREIGN KEY (project_id)
      REFERENCES projects (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT project_budgets_task_id FOREIGN KEY (task_id)
      REFERENCES tasks (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT project_budgets_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT project_budgets_unq UNIQUE (project_id, task_id, budget_item_id, usercompanyid)
);

ALTER TABLE project_budgets OWNER TO "www-data";

CREATE OR REPLACE VIEW project_budgets_overview AS 
 SELECT b.*
 , u.uom_name
 , p.name as project
 , t.name as task
   FROM project_budgets b
   JOIN st_uoms u ON u.id = b.uom_id
   JOIN projects p ON p.id = b.project_id
   LEFT JOIN tasks t ON t.id = b.task_id;

ALTER TABLE project_budgets_overview OWNER TO "www-data";


-------------------------------------------------------------------------------

-- Project Costs/Charges
-------------------------------------------------------------------------------

DROP VIEW IF EXISTS project_purchase_orders;
DROP VIEW IF EXISTS project_sales_invoices;
DROP TABLE IF EXISTS project_costs_charges;

CREATE TABLE project_costs_charges
(
  id bigserial NOT NULL,
  project_id bigint NOT NULL,
  task_id bigint,
  item_id bigint,
  item_type character varying NOT NULL,
  source_id bigint,
  source_type character varying NOT NULL,
  stitem_id bigint,
  description character varying,
  quantity numeric,
  unit_price numeric,
  net_value numeric,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT project_costs_charges_pkey PRIMARY KEY (id),
  CONSTRAINT project_costs_charges_project_id_fkey FOREIGN KEY (project_id)
      REFERENCES projects (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT project_costs_charges_task_id_fkey FOREIGN KEY (task_id)
      REFERENCES tasks (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT project_costs_charges_stitem_id_fkey FOREIGN KEY (stitem_id)
      REFERENCES st_items (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT project_costs_charges_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
);

ALTER TABLE project_costs_charges OWNER TO "www-data";

CREATE OR REPLACE VIEW project_purchase_orders AS 
 SELECT pcc.*
 , pol.order_id, pol.order_number, pol.line_number, pol.plmaster_id, pol.supplier
 , pol.description as line_description, pol.net_value as order_value, pol.order_date, pol.due_delivery_date
   FROM project_costs_charges pcc
   JOIN po_linesoverview pol ON pol.id = pcc.item_id
                            AND pcc.item_type='PO';

ALTER TABLE project_purchase_orders OWNER TO "www-data";

CREATE OR REPLACE VIEW project_sales_invoices AS 
 SELECT pcc.*
 , sil.invoice_id, sil.invoice_number, sil.line_number, sil.slmaster_id, sil.customer
 , sil.description as line_description, sil.net_value as invoice_value, sil.tax_value, sil.gross_value, sil.invoice_date
   FROM project_costs_charges pcc
   JOIN si_linesoverview sil ON sil.id = pcc.item_id
                            AND pcc.item_type='SI';

ALTER TABLE project_sales_invoices OWNER TO "www-data";


----------------------------
-- PROJECT NOTES
----------------------------

-- Remove old overview, should have an _ before 'overview'
DROP VIEW IF EXISTS project_notesoverview;
DROP VIEW IF EXISTS project_notes_overview;

-- DROP TABLE project_note_types;

CREATE TABLE project_note_types
(
  id bigserial NOT NULL,
  "name" character varying NOT NULL,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  usercompanyid bigint NOT NULL,
  CONSTRAINT project_note_types_pkey PRIMARY KEY (id),
  CONSTRAINT project_note_types_usercompanyid_fkey FOREIGN KEY (usercompanyid)
    REFERENCES system_companies (id) MATCH SIMPLE
    ON UPDATE CASCADE ON DELETE CASCADE
);

INSERT INTO project_note_types (name, usercompanyid) 
	SELECT 'Objectives', id
	FROM system_companies;

INSERT INTO project_note_types (name, usercompanyid) 
	SELECT 'Requirements', id
	FROM system_companies;
	
INSERT INTO project_note_types (name, usercompanyid) 
	SELECT 'Exclusions', id
	FROM system_companies;
	
INSERT INTO project_note_types (name, usercompanyid) 
	SELECT 'Constraints', id
	FROM system_companies;
	
INSERT INTO project_note_types (name, usercompanyid) 
	SELECT 'Key Assumptions', id
	FROM system_companies;
	
INSERT INTO project_note_types (name, usercompanyid) 
	SELECT 'Slippage', id
	FROM system_companies;
	

-- constraint on note name / project id?
ALTER TABLE project_notes ADD COLUMN "type_id" bigint;

-------------------------
-- MIGRATE OLD DATA OVER
-------------------------

INSERT INTO project_notes
	(project_id, title, note, type_id, usercompanyid)
     SELECT p.id, 'Imported Note', p.objectives, t.id, u.id
       FROM projects p
  LEFT JOIN project_note_types t ON t.name = 'Objectives'
       JOIN system_companies u ON 1=1
      WHERE p.objectives IS NOT NULL
        AND p.objectives != '';

INSERT INTO project_notes
	(project_id, title, note, type_id, usercompanyid)
     SELECT p.id, 'Imported Note', p.requirements, t.id, u.id
       FROM projects p
  LEFT JOIN project_note_types t ON t.name = 'Requirements'
       JOIN system_companies u ON 1=1
      WHERE p.requirements IS NOT NULL
        AND p.requirements != '';

INSERT INTO project_notes
	(project_id, title, note, type_id, usercompanyid)
     SELECT p.id, 'Imported Note', p.exclusions, t.id, u.id
       FROM projects p
  LEFT JOIN project_note_types t ON t.name = 'Exclusions'
       JOIN system_companies u ON 1=1
      WHERE p.exclusions IS NOT NULL
        AND p.exclusions != '';

INSERT INTO project_notes
	(project_id, title, note, type_id, usercompanyid)
     SELECT p.id, 'Imported Note', p.constraints, t.id, u.id
       FROM projects p
  LEFT JOIN project_note_types t ON t.name = 'Constraints'
       JOIN system_companies u ON 1=1
      WHERE p.constraints IS NOT NULL
        AND p.constraints != '';

INSERT INTO project_notes
	(project_id, title, note, type_id, usercompanyid)
     SELECT p.id, 'Imported Note', p.key_assumptions, t.id, u.id
       FROM projects p
  LEFT JOIN project_note_types t ON t.name = 'Key Assumptions'
       JOIN system_companies u ON 1=1
      WHERE p.key_assumptions IS NOT NULL
        AND p.key_assumptions != '';

INSERT INTO project_notes
	(project_id, title, note, type_id, usercompanyid)
     SELECT p.id, 'Imported Note', p.slippage, t.id, u.id
       FROM projects p
  LEFT JOIN project_note_types t ON t.name = 'Slippage'
       JOIN system_companies u ON 1=1
      WHERE p.slippage IS NOT NULL
        AND p.slippage != '';
        
-- Now we've migrated that legacy notes out of project, remove the fields
DROP VIEW projectsoverview;

ALTER TABLE projects DROP COLUMN objectives;
ALTER TABLE projects DROP COLUMN requirements;
ALTER TABLE projects DROP COLUMN exclusions;
ALTER TABLE projects DROP COLUMN constraints;
ALTER TABLE projects DROP COLUMN key_assumptions;
ALTER TABLE projects DROP COLUMN slippage;


CREATE OR REPLACE VIEW projectsoverview AS 
 SELECT pr.*
 , c.name AS company
 , (p.firstname::text || ' '::text) || p.surname::text AS person
 , cat.name AS category
 , wt.title AS work_type
 , ph.name AS phase
 , u.username AS usernameaccess
   FROM projects pr
   LEFT JOIN company c ON pr.company_id = c.id
   LEFT JOIN person p ON pr.person_id = p.id
   LEFT JOIN project_categories cat ON pr.category_id = c.id
   LEFT JOIN project_work_types wt ON pr.work_type_id = wt.id
   LEFT JOIN project_phases ph ON pr.phase_id = ph.id
   LEFT JOIN users u ON pr.person_id = u.person_id;

ALTER TABLE projectsoverview OWNER TO "www-data";

ALTER TABLE project_notes
   ALTER COLUMN project_id SET NOT NULL;

CREATE OR REPLACE VIEW project_notes_overview AS 
 SELECT pn.id, pn.title, pn.note, pn.project_id, p.name AS project_name, nt.id as type_id, nt.name as type, pn.usercompanyid
   FROM project_notes pn
   LEFT JOIN project_note_types nt ON pn.type_id = nt.id
   LEFT JOIN projects p ON pn.project_id = p.id;
   
-- END OF PROJECT NOTES

-----------------------------
-- PROJECT ISSUES
-----------------------------

-- REMOVE FILES FROM COMPONENTS
DELETE FROM module_components WHERE name = 'projectissue';
DELETE FROM module_components WHERE name = 'projectissuecollection';
DELETE FROM module_components WHERE name = 'projectissuestatus';
DELETE FROM module_components WHERE name = 'projectissuestatuscollection';

--INSERT INTO module_components
--  ("name", "type", location, module_id)
--  SELECT 'projectissueheader', 'M', m.location||'/models/ProjectIssueHeader.php', id
--    FROM modules m
--   WHERE m.name = 'projects';
   
--INSERT INTO module_components
--  ("name", "type", location, module_id)
--  SELECT 'projectissueheadercollection', 'M', m.location||'/models/ProjectIssueHeaderCollection.php', id
--    FROM modules m
--   WHERE m.name = 'projects';

--INSERT INTO module_components
--  ("name", "type", location, module_id)
--  SELECT 'projectissueline', 'M', m.location||'/models/ProjectIssueLine.php', id
--    FROM modules m
--   WHERE m.name = 'projects';
 
--INSERT INTO module_components
--  ("name", "type", location, module_id)
--  SELECT 'projectissuelinecollection', 'M', m.location||'/models/ProjectIssueLineCollection.php', id
--    FROM modules m
--   WHERE m.name = 'projects';

--INSERT INTO module_components
--  ("name", "type", location, module_id)
--  SELECT 'projectissuelinescontroller', 'C', m.location||'/controllers/ProjectissuelinesController.php', id
--    FROM modules m
--   WHERE m.name = 'projects';



-- DROP VIEWS
DROP VIEW IF EXISTS project_issuesoverview;
DROP VIEW IF EXISTS project_issue_header_overview;
DROP VIEW IF EXISTS project_issue_line_overview;
DROP VIEW IF EXISTS project_issue_lines_overview;


-- RENAME HEADER
ALTER TABLE project_issues RENAME TO project_issue_header;


-- DROP ISSUE STATUS FIELD + CONSTRAINT (STATUSES DEPENDS ON IT)
ALTER TABLE project_issue_header DROP CONSTRAINT project_issues_status_id_fkey;
ALTER TABLE project_issue_header DROP COLUMN status_id;


-- DROP TABLES
DROP TABLE project_issue_statuses;


-- RENAME CONSTRAINTS
ALTER TABLE project_issue_header DROP CONSTRAINT project_issues_pkey;
ALTER TABLE project_issue_header ADD CONSTRAINT project_issue_header_pkey PRIMARY KEY(id);
  
ALTER TABLE project_issue_header DROP CONSTRAINT project_issues_assigned_to_fkey;
ALTER TABLE project_issue_header
  ADD CONSTRAINT project_issue_header_assigned_to_fkey FOREIGN KEY (assigned_to)
      REFERENCES users (username) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE;
      
ALTER TABLE project_issue_header DROP CONSTRAINT project_issues_project_id_fkey;
ALTER TABLE project_issue_header
  ADD CONSTRAINT project_issue_header_project_id_fkey FOREIGN KEY (project_id)
      REFERENCES projects (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE project_issue_header DROP CONSTRAINT project_issues_usercompanyid_fkey;
ALTER TABLE project_issue_header
  ADD CONSTRAINT project_issue_header_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE;


-- CREATE LINES TABLE
CREATE TABLE project_issue_lines
(
  id bigserial NOT NULL,
  header_id integer NOT NULL,
  title character varying NOT NULL,
  location character varying,
  description character varying,
  actions character varying,
  created timestamp without time zone NOT NULL DEFAULT now(),
  completed timestamp,
  completed_by character varying,
  usercompanyid bigint NOT NULL,
  lastupdated timestamp without time zone NOT NULL DEFAULT now(),
  alteredby character varying NOT NULL,
  CONSTRAINT project_issue_lines_pkey PRIMARY KEY (id),
  CONSTRAINT project_issue_lines_header_id_fkey FOREIGN KEY (header_id)
      REFERENCES project_issue_header (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
);


-- MIGRATE DATA FROM PROJECT ISSUES TO PROJECT ISSUE LINES
INSERT INTO project_issue_lines
	(header_id, title, location, description, created, completed, usercompanyid, lastupdated, alteredby)
     SELECT i.id, 'Migrated Issue', i.problem_location, i.problem_description, i.created, i.time_fixed, i.usercompanyid, i.lastupdated, i.alteredby
       FROM project_issue_header i;


-- ADD FIELDS TO HEADER
ALTER TABLE project_issue_header ADD COLUMN title character varying;
ALTER TABLE project_issue_header ADD COLUMN status character varying;


-- UPDATE HEADER VALUES (TO SATISFY NOT NULL)

UPDATE project_issue_header
   SET title = 'Migrated Title'
  FROM project_issue_header i
 WHERE i.id = project_issue_header.id
   AND project_issue_header.title IS NULL;

-- SET STATUS TO FALSE...
UPDATE project_issue_header
   SET status = FALSE
  FROM project_issue_header i
 WHERE i.id = project_issue_header.id;

-- BUT CHANGE TO TRUE IF TIME_FIXED HAS BEEN SET
UPDATE project_issue_header
   SET status = TRUE
  FROM project_issue_header i
 WHERE i.id = project_issue_header.id
   AND project_issue_header.time_fixed IS NOT NULL;


-- APPLY NOT NULL CONSTRAINTS
ALTER TABLE project_issue_header ALTER COLUMN title SET NOT NULL;
ALTER TABLE project_issue_header ALTER COLUMN status SET NOT NULL;


-- DROP CONSTRAINTS FROM HEADER
ALTER TABLE project_issue_header DROP CONSTRAINT project_issues_alteredby_fkey;
ALTER TABLE project_issue_header DROP CONSTRAINT project_issues_owner_fkey;


-- DROP FIELDS FROM HEADER
ALTER TABLE project_issue_header DROP COLUMN problem_location;
ALTER TABLE project_issue_header DROP COLUMN problem_description;
ALTER TABLE project_issue_header DROP COLUMN created;
ALTER TABLE project_issue_header DROP COLUMN time_fixed;
ALTER TABLE project_issue_header DROP COLUMN owner;
ALTER TABLE project_issue_header DROP COLUMN assigned_to;


-- CREATE VIEWS

CREATE OR REPLACE VIEW project_issue_header_overview AS 
 SELECT ph.id, ph.project_id, ph.title, ph.status, ph.usercompanyid, p.name AS project
   FROM project_issue_header ph
   LEFT JOIN projects p ON ph.project_id = p.id;

CREATE OR REPLACE VIEW project_issue_lines_overview AS 
 SELECT pl.id, pl.header_id, pl.title, pl.location, pl.description, pl.actions, pl.created, pl.completed, pl.completed_by, pl.usercompanyid, pl.lastupdated, pl.alteredby
   FROM project_issue_lines pl
   LEFT JOIN project_issue_header ph ON pl.header_id = ph.id;
