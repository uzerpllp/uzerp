--
-- $Revision: 1.27 $
--

-- TABLE: contact_categories;

ALTER TABLE contact_categories ADD COLUMN company boolean;
ALTER TABLE contact_categories ALTER COLUMN company SET DEFAULT false;

ALTER TABLE contact_categories ADD COLUMN person boolean;
ALTER TABLE contact_categories ALTER COLUMN person SET DEFAULT false;

-- View: ledger_categories_overview

-- DROP VIEW ledger_categories_overview;

CREATE OR REPLACE VIEW ledger_categories_overview AS 
 SELECT lc.*
      , cc.name AS category, cc.company, cc.person
   FROM ledger_categories lc
   JOIN contact_categories cc ON cc.id = lc.category_id;

ALTER TABLE ledger_categories_overview OWNER TO "www-data";

-- TABLE: hr_parameters;

-- DROP TABLE hr_parameters;

CREATE TABLE hr_parameters
(
  id serial NOT NULL,
  week_start_day character varying NOT NULL,
  week_start_time character varying NOT NULL,
  created timestamp without time zone NOT NULL DEFAULT now(),
  createdby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  alteredby character varying,
  usercompanyid bigint NOT NULL,
  CONSTRAINT hr_parameters_pkey PRIMARY KEY (id),
  CONSTRAINT hr_parameters_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

ALTER TABLE hr_parameters OWNER TO "www-data";

-- Table: hr_authorisers

-- DROP TABLE hr_authorisers;

CREATE TABLE hr_authorisers
(
  id serial NOT NULL,
  employee_id bigint NOT NULL,
  authorisation_type character varying NOT NULL,
  created timestamp without time zone NOT NULL DEFAULT now(),
  createdby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  alteredby character varying,
  usercompanyid bigint NOT NULL,
  CONSTRAINT hr_authorisers_pkey PRIMARY KEY (id),
  CONSTRAINT hr_authorisers_employee_id_fkey FOREIGN KEY (employee_id)
      REFERENCES employees (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT hr_authorisers_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

ALTER TABLE hr_authorisers OWNER TO "www-data";

-- TABLE: employee_grades;

-- DROP TABLE employee_grades;

CREATE TABLE employee_grades
(
  id serial NOT NULL,
  "name" character varying NOT NULL,
  description character varying NOT NULL,
  created timestamp without time zone NOT NULL DEFAULT now(),
  createdby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  alteredby character varying,
  usercompanyid bigint NOT NULL,
  CONSTRAINT employee_grades_pkey PRIMARY KEY (id),
  CONSTRAINT employee_grades_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

ALTER TABLE employee_grades OWNER TO "www-data";

-- TABLE: employee_payment_types;

-- DROP TABLE employee_payment_types;

CREATE TABLE employee_payment_types
(
  id serial NOT NULL,
  position int NOT NULL,
  "name" character varying NOT NULL,
  description character varying,
  allow_zero_units boolean default FALSE,
  created timestamp without time zone NOT NULL DEFAULT now(),
  createdby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  alteredby character varying,
  usercompanyid bigint NOT NULL,
  CONSTRAINT employee_payment_types_pkey PRIMARY KEY (id),
  CONSTRAINT employee_payment_types_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

ALTER TABLE employee_payment_types OWNER TO "www-data";

-- TABLE: hours_payment_types;

-- DROP TABLE hours_payment_types;

CREATE TABLE hours_payment_types
(
  id serial NOT NULL,
  hours_type_id bigint NOT NULL,
  payment_type_id bigint NOT NULL,
  created timestamp without time zone NOT NULL DEFAULT now(),
  createdby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  alteredby character varying,
  usercompanyid bigint NOT NULL,
  CONSTRAINT hours_payment_types_pkey PRIMARY KEY (id),
  CONSTRAINT hours_payment_types_hours_type_id_fkey FOREIGN KEY (hours_type_id)
      REFERENCES hour_types (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT hours_payment_types_payment_type_id_fkey FOREIGN KEY (payment_type_id)
      REFERENCES employee_payment_types (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION,
  CONSTRAINT hours_payment_types_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

ALTER TABLE hours_payment_types OWNER TO "www-data";

-- Table: employee_pay_frequencies

-- DROP TABLE employee_pay_frequencies;

CREATE TABLE employee_pay_frequencies
(
  id serial NOT NULL,
  "name" character varying NOT NULL,
  description character varying,
  created timestamp without time zone NOT NULL DEFAULT now(),
  createdby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  alteredby character varying,
  usercompanyid bigint NOT NULL,
  CONSTRAINT employee_pay_frequencies_pkey PRIMARY KEY (id),
  CONSTRAINT employee_pay_frequencies_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

ALTER TABLE employee_pay_frequencies OWNER TO "www-data";

INSERT INTO employee_pay_frequencies
  ("name", usercompanyid)
SELECT 'Hourly', id
  FROM system_companies;

INSERT INTO employee_pay_frequencies
  ("name", usercompanyid)
SELECT 'Weekly', id
  FROM system_companies;

INSERT INTO employee_pay_frequencies
  ("name", usercompanyid)
SELECT 'Monthly', id
  FROM system_companies;

INSERT INTO employee_pay_frequencies
  ("name", usercompanyid)
SELECT 'Annual', id
  FROM system_companies;

-- Table: employee_rates

-- DROP TABLE employee_rates;

CREATE TABLE employee_rates
(
  id serial NOT NULL,
  employee_id bigint NOT NULL,
  payment_type_id bigint NOT NULL,
  default_units numeric NOT NULL,
  units_variable boolean NOT NULL default TRUE,
  rate_value numeric NOT NULL,
  rate_variable boolean NOT NULL default FALSE,
  pay_frequency_id bigint NOT NULL,
  start_date date NOT NULL,
  end_date date,
  created timestamp without time zone NOT NULL DEFAULT now(),
  createdby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  alteredby character varying,
  usercompanyid bigint NOT NULL,
  CONSTRAINT employee_rates_pkey PRIMARY KEY (id),
  CONSTRAINT employee_rates_employee_id_fkey FOREIGN KEY (employee_id)
      REFERENCES employees (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT employee_rates_pay_frequency_id_fkey FOREIGN KEY (pay_frequency_id)
      REFERENCES employee_pay_frequencies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT employee_rates_payment_type_id_fkey FOREIGN KEY (payment_type_id)
      REFERENCES employee_payment_types (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION,
  CONSTRAINT employee_rates_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

ALTER TABLE employee_rates OWNER TO "www-data";

-- Table: employee_hours_details

-- DROP TABLE employee_contract_details;

CREATE TABLE employee_contract_details
(
  id serial NOT NULL,
  employee_id bigint,
  from_pay_frequency_id bigint NOT NULL,
  to_pay_frequency_id bigint NOT NULL,
  start_date date NOT NULL,
  end_date date,
  std_value numeric NOT NULL,
  created timestamp without time zone NOT NULL DEFAULT now(),
  createdby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  alteredby character varying,
  usercompanyid bigint NOT NULL,
  CONSTRAINT employee_contract_details_pkey PRIMARY KEY (id),
  CONSTRAINT employee_contract_details_employee_id_fkey FOREIGN KEY (employee_id)
      REFERENCES employees (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT employee_contract_details_from_pay_frequency_id_fkey FOREIGN KEY (from_pay_frequency_id)
      REFERENCES employee_pay_frequencies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION,
  CONSTRAINT employee_contract_details_to_pay_frequency_id_fkey FOREIGN KEY (to_pay_frequency_id)
      REFERENCES employee_pay_frequencies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION,
  CONSTRAINT employee_contract_details_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

ALTER TABLE employee_contract_details OWNER TO "www-data";

-- Table: employee_pay_periods

-- DROP TABLE employee_pay_periods;

CREATE TABLE employee_pay_periods
(
  id serial NOT NULL,
  period_start_date timestamp without time zone NOT NULL,
  period_end_date timestamp without time zone NOT NULL,
  pay_basis character varying NOT NULL,
  tax_year integer NOT NULL,
  tax_month integer NOT NULL,
  tax_week integer NOT NULL,
  calendar_week integer NOT NULL,
  processed_period integer,
  processed_date date,
  closed boolean NOT NULL DEFAULT FALSE,
  created timestamp without time zone NOT NULL DEFAULT now(),
  createdby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  alteredby character varying,
  usercompanyid bigint NOT NULL,
  CONSTRAINT employee_pay_periods_pkey PRIMARY KEY (id),
  CONSTRAINT employee_pay_periods_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION
);

ALTER TABLE employee_pay_periods OWNER TO "www-data";

-- Table: employee_pay_history

-- DROP TABLE employee_pay_history;

CREATE TABLE employee_pay_history
(
  id serial NOT NULL,
  employee_id bigint NOT NULL,
  employee_pay_periods_id bigint NOT NULL,
  hours_type_id bigint,
  payment_type_id bigint,
  pay_frequency_id bigint NOT NULL,
  pay_rate numeric NOT NULL,
  pay_units numeric NOT NULL,
  comment character varying,
  created timestamp without time zone NOT NULL DEFAULT now(),
  createdby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  alteredby character varying,
  usercompanyid bigint NOT NULL,
  CONSTRAINT employee_pay_history_pkey PRIMARY KEY (id),
  CONSTRAINT employee_payment_history_employee_id_fkey FOREIGN KEY (employee_id)
      REFERENCES employees (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION,
  CONSTRAINT employee_payment_history_pay_periods_id_fkey FOREIGN KEY (employee_pay_periods_id)
      REFERENCES employee_pay_periods (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION,
  CONSTRAINT employee_payment_history_hours_type_id_fkey FOREIGN KEY (hours_type_id)
      REFERENCES hour_types (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION,
  CONSTRAINT employee_payment_history_payment_type_id_fkey FOREIGN KEY (payment_type_id)
      REFERENCES employee_payment_types (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION,
  CONSTRAINT employee_pay_history_pay_frequency_id_fkey FOREIGN KEY (pay_frequency_id)
      REFERENCES employee_pay_frequencies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION,
  CONSTRAINT employee_pay_history_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION
);

ALTER TABLE employee_pay_history OWNER TO "www-data";

-- Table: expenses

ALTER TABLE expenses ADD COLUMN createdby character varying;
ALTER TABLE expenses ADD COLUMN alteredby character varying;

-- Table: holiday_entitlements

ALTER TABLE holiday_entitlements ALTER COLUMN num_days type numeric;
ALTER TABLE holiday_entitlements RENAME COLUMN updatedby TO alteredby;

-- Table: holiday_extra_days

ALTER TABLE holiday_extra_days ALTER COLUMN num_days type numeric;

-- Table: hours

--ALTER TABLE hours ADD COLUMN createdby character varying;
--ALTER TABLE hours ADD COLUMN alteredby character varying;

-- Table: hour_types

ALTER TABLE hour_types ADD COLUMN created timestamp without time zone;

UPDATE hour_types
   SET created = now()
 WHERE created IS NULL;

ALTER TABLE hour_types ADD COLUMN position integer;

ALTER TABLE hour_types ALTER COLUMN created SET NOT NULL;
ALTER TABLE hour_types ALTER COLUMN created SET DEFAULT now();

ALTER TABLE hour_types ADD COLUMN createdby character varying;

ALTER TABLE hour_types ADD COLUMN lastupdated timestamp without time zone;

UPDATE hour_types
   SET lastupdated = created
 WHERE created IS NULL;

ALTER TABLE hour_types ALTER COLUMN lastupdated SET DEFAULT now();

ALTER TABLE hour_types ADD COLUMN alteredby character varying;

-- Table: hour_type_groups

ALTER TABLE hour_type_groups ADD COLUMN position integer;

ALTER TABLE hour_type_groups ADD COLUMN created timestamp without time zone;

UPDATE hour_type_groups
   SET created = now()
 WHERE created IS NULL;

ALTER TABLE hour_type_groups ALTER COLUMN created SET NOT NULL;
ALTER TABLE hour_type_groups ALTER COLUMN created SET DEFAULT now();

ALTER TABLE hour_type_groups ADD COLUMN createdby character varying;

ALTER TABLE hour_type_groups ADD COLUMN lastupdated timestamp without time zone;

UPDATE hour_type_groups
   SET lastupdated = created
 WHERE created IS NULL;

ALTER TABLE hour_type_groups ALTER COLUMN lastupdated SET DEFAULT now();

ALTER TABLE hour_type_groups ADD COLUMN alteredby character varying;

-- Table: person

ALTER TABLE person ADD COLUMN end_date date;

-- Table: person

DROP VIEW personoverview;

ALTER TABLE person DROP COLUMN ni;
ALTER TABLE person DROP COLUMN dob;

-- View: personoverview

CREATE OR REPLACE VIEW personoverview AS 
 SELECT per.*
 , (per.surname::text || ', '::text) || per.firstname::text AS name
 , pa.address_id
 , a.street1, a.street2, a.street3, a.town, a.county, a.countrycode, a.postcode
 , c.name AS company, c.accountnumber
 , cm.contact AS phone, m.contact AS mobile, e.contact AS email
   FROM person per
   LEFT JOIN company c ON per.company_id = c.id
   LEFT JOIN party p ON per.party_id = p.id
   LEFT JOIN partyaddress pa ON p.id = pa.party_id AND pa.main
   LEFT JOIN address a ON a.id = pa.address_id
   LEFT JOIN party_contact_methods pcm ON p.id = pcm.party_id AND pcm.main AND pcm.type::text = 'T'::text
   LEFT JOIN contact_methods cm ON cm.id = pcm.contactmethod_id
   LEFT JOIN party_contact_methods mcm ON p.id = mcm.party_id AND mcm.main AND mcm.type::text = 'M'::text
   LEFT JOIN contact_methods m ON m.id = mcm.contactmethod_id
   LEFT JOIN party_contact_methods ecm ON p.id = ecm.party_id AND ecm.main AND ecm.type::text = 'E'::text
   LEFT JOIN contact_methods e ON e.id = ecm.contactmethod_id;

ALTER TABLE personoverview OWNER TO "www-data";

-- Views

-- Drop views that exist

DROP VIEW IF EXISTS employee_rates_overview;

DROP VIEW IF EXISTS employeeoverview;

-- Table: employees

ALTER TABLE employees ADD COLUMN works_number integer;
ALTER TABLE employees ADD COLUMN employee_grade_id integer;
ALTER TABLE employees ADD COLUMN pay_frequency_id bigint;
ALTER TABLE employees ADD COLUMN address_id bigint;
ALTER TABLE employees ADD COLUMN contact_phone_id bigint;
ALTER TABLE employees ADD COLUMN contact_mobile_id bigint;
ALTER TABLE employees ADD COLUMN mfdept_id bigint;

ALTER TABLE employees
  ADD CONSTRAINT employees_employee_grade_id_fkey FOREIGN KEY (employee_grade_id)
      REFERENCES employee_grades (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

UPDATE employees
   SET pay_frequency_id = (SELECT id
                             FROM employee_pay_frequencies f
                            WHERE substr(f.name, 1, 1) = employees.pay_frequency);

ALTER TABLE employees ALTER COLUMN pay_frequency_id SET NOT NULL;

ALTER TABLE employees DROP COLUMN pay_frequency;

ALTER TABLE employees
  ADD CONSTRAINT employees_pay_frequency_id_fkey FOREIGN KEY (pay_frequency_id)
      REFERENCES employee_pay_frequencies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

ALTER TABLE employees
  ADD CONSTRAINT employees_mfdept_id_fkey FOREIGN KEY (mfdept_id)
      REFERENCES mf_depts (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

ALTER TABLE employees ALTER COLUMN employee_number TYPE integer USING cast(employee_number AS integer);

-- View: employeeoverview

CREATE OR REPLACE VIEW employeeoverview AS 
 SELECT ee.*
      , epf.name AS pay_frequency
      , (p.surname::text || ' '::text) || p.firstname::text AS employee
      , p.reports_to
      , p.department
      , eg.name || ' - ' || eg.description AS employee_grade
   FROM employees ee
   JOIN person p ON p.id = ee.person_id
   JOIN employee_pay_frequencies epf ON epf.id = ee.pay_frequency_id
   LEFT JOIN employee_grades eg ON eg.id = ee.employee_grade_id;

ALTER TABLE employeeoverview OWNER TO "www-data";

-- View: employee_rates_overview

DROP VIEW IF EXISTS employee_rates_overview;

CREATE OR REPLACE VIEW employee_rates_overview AS
SELECT er.*
     , epf.name AS pay_frequency
     , ept.name AS payment_type
     , eo.employee
  FROM employee_rates er
  JOIN employee_pay_frequencies epf ON epf.id = er.pay_frequency_id
  JOIN employee_payment_types ept ON ept.id = er.payment_type_id
  JOIN employeeoverview eo ON eo.id = er.employee_id;

ALTER TABLE employee_rates_overview OWNER TO "www-data";

-- View: employee_contract_details_overview

DROP VIEW IF EXISTS employee_contract_details_overview;

CREATE OR REPLACE VIEW employee_contract_details_overview AS
SELECT ecd.*
     , epf1.name AS from_pay_frequency
     , epf2.name AS to_pay_frequency
     , eo.employee, eo.person_id
  FROM employee_contract_details ecd
  JOIN employee_pay_frequencies epf1 ON epf1.id = ecd.from_pay_frequency_id
  JOIN employee_pay_frequencies epf2 ON epf2.id = ecd.to_pay_frequency_id
  LEFT JOIN employeeoverview eo ON eo.id = ecd.employee_id;

ALTER TABLE employee_contract_details_overview OWNER TO "www-data";

-- View: hoursoverview

DROP VIEW IF EXISTS hoursoverview;

CREATE OR REPLACE VIEW hoursoverview AS 
 SELECT h.*, (u.firstname::text || ' '::text) || u.surname::text AS person
 , ht.name AS type, p.name AS project, t.name AS task, k.*::name AS ticket, o.name AS opportunity
   FROM hours h
   JOIN person u ON u.id = h.person_id
   JOIN hour_types ht ON ht.id = h.type_id
   LEFT JOIN projects p ON p.id = h.project_id
   LEFT JOIN tasks t ON t.id = h.task_id
   LEFT JOIN tickets k ON k.id = h.ticket_id
   LEFT JOIN opportunities o ON o.id = h.opportunity_id;

ALTER TABLE hoursoverview OWNER TO "www-data";

-- View: hours_payment_types_overview

DROP VIEW IF EXISTS hours_payment_types_overview;

CREATE OR REPLACE VIEW hours_payment_types_overview AS
SELECT hpt.*
     , ht.name as hours_type
     , ept.name as payment_type
  FROM hours_payment_types hpt
  JOIN hour_types ht ON ht.id = hpt.hours_type_id
  JOIN employee_payment_types ept ON ept.id = hpt.payment_type_id;

ALTER TABLE hours_payment_types_overview OWNER TO "www-data";

-- View: employee_rate_hours_overview

DROP VIEW IF EXISTS employee_rate_hours_overview;

CREATE OR REPLACE VIEW employee_rate_hours_overview AS 
 SELECT er.*
 , ept.name AS payment_type, ept.position, ept.allow_zero_units
 , hpt.hours_type_id, hpt.hours_type
 , eo.employee
 , epf.name AS pay_frequency
   FROM employee_rates er
   JOIN employee_payment_types ept ON ept.id = er.payment_type_id
   LEFT JOIN hours_payment_types_overview hpt ON hpt.payment_type_id = er.payment_type_id
   JOIN employeeoverview eo ON eo.id = er.employee_id
   JOIN employee_pay_frequencies epf ON epf.id = er.pay_frequency_id;

ALTER TABLE employee_rate_hours_overview OWNER TO "www-data";

-- View: employee_pay_history_overview

DROP VIEW IF EXISTS employee_pay_history_overview;

CREATE OR REPLACE VIEW employee_pay_history_overview AS 
 SELECT eph.*
 , eph.pay_rate * eph.pay_units AS pay_value
 , epp.period_start_date, epp.period_end_date, epp.pay_basis, epp.tax_year, epp.tax_month, epp.tax_week, epp.calendar_week
 , epf.name AS pay_frequency
 , ht.name AS hours_type
 , ept.name AS payment_type, ept.allow_zero_units
 , eo.employee
   FROM employee_pay_history eph
   JOIN employee_pay_periods epp ON epp.id = eph.employee_pay_periods_id
   JOIN employeeoverview eo ON eo.id = eph.employee_id
   JOIN employee_pay_frequencies epf ON epf.id = eph.pay_frequency_id
   LEFT JOIN hour_types ht ON ht.id = eph.hours_type_id
   LEFT JOIN employee_payment_types ept ON ept.id = eph.payment_type_id;

ALTER TABLE employee_pay_history_overview OWNER TO "www-data";

-- View: holiday_requests_overview

DROP VIEW IF EXISTS holiday_requests_overview;

ALTER TABLE holiday_requests ALTER COLUMN num_days type numeric;
ALTER TABLE holiday_requests ADD COLUMN all_day boolean;
ALTER TABLE holiday_requests ALTER COLUMN all_day SET DEFAULT true;

CREATE OR REPLACE VIEW holiday_requests_overview AS 
 SELECT hr.*
 , eo.employee, eo.employee_grade_id, eo.employee_grade, eo.department
   FROM holiday_requests hr
   JOIN employeeoverview eo ON eo.id = hr.employee_id;

ALTER TABLE holiday_requests_overview OWNER TO "www-data";

-- View: hr_authorisers_overview

-- DROP VIEW hr_authorisers_overview;

CREATE OR REPLACE VIEW hr_authorisers_overview AS 
 SELECT a.*, ee.employee
   FROM hr_authorisers a
   JOIN employeeoverview ee ON ee.id = a.employee_id;

ALTER TABLE hr_authorisers_overview OWNER TO "www-data";

-- View: expense_authorisers_overview

-- DROP VIEW expense_authorisers_overview;

CREATE OR REPLACE VIEW expense_authorisers_overview
AS
SELECT ea.*, ee.employee, au.employee as authoriser
  FROM expense_authorisers ea
  JOIN employeeoverview ee ON ee.id = ea.employee_id
  JOIN employeeoverview au ON au.id = ea.authoriser_id;

ALTER TABLE expense_authorisers_overview OWNER TO "www-data";

-- View: holiday_authorisers_overview

-- DROP VIEW holiday_authorisers_overview;

CREATE OR REPLACE VIEW holiday_authorisers_overview
AS
SELECT ea.*, ee.employee, au.employee as authoriser
  FROM holiday_authorisers ea
  JOIN employeeoverview ee ON ee.id = ea.employee_id
  JOIN employeeoverview au ON au.id = ea.authoriser_id;

ALTER TABLE holiday_authorisers_overview OWNER TO "www-data";

-- View: sys_object_policies_overview

DROP VIEW sys_object_policies_overview;

CREATE OR REPLACE VIEW sys_object_policies_overview AS 
 SELECT op.id, op.name, op.fieldname, op.operator, coalesce(op.value, 'NULL') as value, op.usercompanyid, op.created, op.createdby
 , op.alteredby, op.lastupdated, op.is_id_field, op.module_components_id, mc.name AS module_component
   FROM sys_object_policies op
   JOIN module_components mc ON mc.id = op.module_components_id;

ALTER TABLE sys_object_policies_overview OWNER TO "www-data";

--
-- Modules/Components
--

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'employeegrade', 'M', location||'/models/EmployeeGrade.php', id, 'Employee Grade'
   FROM modules m
  WHERE name = 'hr';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'employeegradecollection', 'M', location||'/models/EmployeeGradeCollection.php', id, 'Employee Grades'
   FROM modules m
  WHERE name = 'hr';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'employeepayfrequency', 'M', location||'/models/EmployeePayFrequency.php', id, 'Employee Pay Frequency'
   FROM modules m
  WHERE name = 'hr';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'employeepayfrequencycollection', 'M', location||'/models/EmployeePayFrequencyCollection.php', id, 'Employee Pay Frequencies'
   FROM modules m
  WHERE name = 'hr';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'employeepaymenttype', 'M', location||'/models/EmployeePaymentType.php', id, 'Employee Payment Type'
   FROM modules m
  WHERE name = 'hr';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'employeepaymenttypecollection', 'M', location||'/models/EmployeePaymentTypeCollection.php', id, 'Employee Payment Types'
   FROM modules m
  WHERE name = 'hr';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'hourpaymenttype', 'M', location||'/models/HourPaymentType.php', id, 'Hour Payment Type'
   FROM modules m
  WHERE name = 'hr';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'hourpaymenttypecollection', 'M', location||'/models/HourPaymentTypeCollection.php', id, 'Hour Payment Types'
   FROM modules m
  WHERE name = 'hr';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'employeeratescontroller', 'C', location||'/controllers/EmployeeratesController.php', id, 'Employee Rates'
   FROM modules m
  WHERE name = 'hr';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'employeerate', 'M', location||'/models/EmployeeRate.php', id, 'Employee Rate'
   FROM modules m
  WHERE name = 'hr';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'employeeratecollection', 'M', location||'/models/EmployeeRateCollection.php', id, 'Employee Rates'
   FROM modules m
  WHERE name = 'hr';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'employeecontractdetailscontroller', 'C', location||'/controllers/EmployeecontractdetailsController.php', id, 'Employee Contract Details'
   FROM modules m
  WHERE name = 'hr';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'employeecontractdetail', 'M', location||'/models/EmployeeContractDetail.php', id, 'Employee Contract Detail'
   FROM modules m
  WHERE name = 'hr';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'employeecontractdetailcollection', 'M', location||'/models/EmployeeContractDetailCollection.php', id, 'Employee Contract Details'
   FROM modules m
  WHERE name = 'hr';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'employeehour', 'M', location||'/models/EmployeeHour.php', id, 'Employee Hour Detail'
   FROM modules m
  WHERE name = 'hr';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'employeehourcollection', 'M', location||'/models/EmployeeHourCollection.php', id, 'Employee Hour Details'
   FROM modules m
  WHERE name = 'hr';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'hrparameters', 'M', location||'/models/HRParameters.php', id, 'HR Parameters'
   FROM modules m
  WHERE name = 'hr';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'employeepayperiodscontroller', 'C', location||'/controllers/EmployeepayperiodsController.php', id, 'Employee Pay Periods'
   FROM modules m
  WHERE name = 'hr';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'employeepayperiod', 'M', location||'/models/EmployeePayPeriod.php', id, 'Employee Pay Period'
   FROM modules m
  WHERE name = 'hr';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'employeepayperiodcollection', 'M', location||'/models/EmployeePayPeriodCollection.php', id, 'Employee Pay Periods'
   FROM modules m
  WHERE name = 'hr';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'employeepayhistoryscontroller', 'C', location||'/controllers/EmployeepayhistorysController.php', id, 'Employee History'
   FROM modules m
  WHERE name = 'hr';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'employeepayhistory', 'M', location||'/models/EmployeePayHistory.php', id, 'Employee History'
   FROM modules m
  WHERE name = 'hr';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'employeepayhistorycollection', 'M', location||'/models/EmployeePayHistoryCollection.php', id, 'Employee History List'
   FROM modules m
  WHERE name = 'hr';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'employeesearch', 'M', location||'/models/employeeSearch.php', id, 'Employee Search'
   FROM modules m
  WHERE name = 'hr';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'holidaysearch', 'M', location||'/models/holidaySearch.php', id, 'Holiday Search'
   FROM modules m
  WHERE name = 'hr';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'hrauthoriser', 'M', location||'/models/HRAuthoriser.php', id, 'HR Authoriser'
   FROM modules m
  WHERE name = 'hr';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'hrauthorisercollection', 'M', location||'/models/HRAuthoriserCollection.php', id, 'HR Authoriser List'
   FROM modules m
  WHERE name = 'hr';

--
-- Permissions
--

INSERT INTO permissions
 (permission, type, title, display, parent_id, position, module_id, component_id)
SELECT 'employeerates', 'c', 'Employee Rates', false, per.id, pos.position, mod.module_id, mod.id
  FROM permissions per
    , (SELECT max(c.position)+1 as position
         FROM permissions c
            , permissions p
        WHERE p.type='m'
          AND p.permission='hr'
          AND p.id=c.parent_id) pos
   , (SELECT module_id, id
        FROM module_components mc
       WHERE mc.name = 'employeeratescontroller') mod
 WHERE type='m'
   AND permission='hr'
   AND parent_id is null;

INSERT INTO permissions
 (permission, type, title, display, parent_id, position, module_id, component_id)
SELECT 'employeecontractdetails', 'c', 'Employee Contract Details', false, per.id, pos.position, mod.module_id, mod.id
  FROM permissions per
    , (SELECT max(c.position)+1 as position
         FROM permissions c
            , permissions p
        WHERE p.type='m'
          AND p.permission='hr'
          AND p.id=c.parent_id) pos
   , (SELECT module_id, id
        FROM module_components mc
       WHERE mc.name = 'employeecontractdetailscontroller') mod
 WHERE type='m'
   AND permission='hr'
   AND parent_id is null;

INSERT INTO permissions
 (permission, type, title, display, parent_id, position, module_id, component_id)
SELECT 'employeepayhistorys', 'c', 'Employee Pay History', true, per.id, pos.position, mod.module_id, mod.id
  FROM permissions per
    , (SELECT max(c.position)+1 as position
         FROM permissions c
            , permissions p
        WHERE p.type='m'
          AND p.permission='hr'
          AND p.id=c.parent_id) pos
   , (SELECT module_id, id
        FROM module_components mc
       WHERE mc.name = 'employeepayhistoryscontroller') mod
 WHERE type='m'
   AND permission='hr'
   AND parent_id is null;

INSERT INTO permissions
 (permission, type, title, display, parent_id, position, module_id, component_id)
SELECT '_new', 'a', 'Enter Payments', true, per.id, pos.position, mod.module_id, mod.id
  FROM permissions per
    , (SELECT coalesce(max(c.position)+1, 1) as position
         FROM permissions c
            , permissions p
        WHERE p.type='c'
          AND p.permission='employeepayhistorys'
          AND p.id=c.parent_id) pos
   , (SELECT module_id, id
        FROM module_components mc
       WHERE mc.name = 'employeepayhistoryscontroller') mod
 WHERE type='c'
   AND permission='employeepayhistorys';

INSERT INTO permissions
 (permission, type, title, display, parent_id, position, module_id, component_id)
SELECT 'employeepayperiods', 'c', 'Employee Pay Periods', true, per.id, pos.position, mod.module_id, mod.id
  FROM permissions per
    , (SELECT max(c.position)+1 as position
         FROM permissions c
            , permissions p
        WHERE p.type='m'
          AND p.permission='hr'
          AND p.id=c.parent_id) pos
   , (SELECT module_id, id
        FROM module_components mc
       WHERE mc.name = 'employeepayperiodscontroller') mod
 WHERE type='m'
   AND permission='hr'
   AND parent_id is null;
