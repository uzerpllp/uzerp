ALTER TABLE employees ADD COLUMN ni varchar;
ALTER TABLE employees ADD COLUMN dob date;
ALTER TABLE employees ADD COLUMN expenses_balance numeric;
ALTER TABLE employees ALTER COLUMN expenses_balance SET DEFAULT 0;

DROP VIEW employeeoverview;

CREATE OR REPLACE VIEW employeeoverview AS 
 SELECT employees.id, employees.person_id, employees.employee_number, employees.next_of_kin, employees.nok_address, employees.nok_phone, employees.nok_relationship, employees.bank_name, employees.bank_address, employees.bank_account_name, employees.bank_account_number, employees.bank_sort_code, employees.start_date, employees.finished_date, employees.pay_frequency, employees.created, employees.lastupdated, employees.alteredby, employees.usercompanyid, employees.ni, employees.dob, employees.expenses_balance, (person.firstname::text || ' '::text) || person.surname::text AS person
   FROM employees
   JOIN person ON employees.person_id = person.id;

CREATE TABLE expense_authorisers
(
  id serial NOT NULL,
  employee_id int8 NOT NULL,
  authoriser_id int8 NOT NULL,
  created timestamp DEFAULT now(),
  createdby varchar,
  alteredby varchar,
  lastupdated timestamp DEFAULT now(),
  CONSTRAINT expense_authorisers_pkey PRIMARY KEY (id),
  CONSTRAINT authoriser_id_fkey FOREIGN KEY (authoriser_id)
      REFERENCES employees (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT employee_id_fkey FOREIGN KEY (employee_id)
      REFERENCES employees (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE holiday_authorisers
(
  id serial NOT NULL,
  employee_id int8 NOT NULL,
  authoriser_id int8 NOT NULL,
  created timestamp DEFAULT now(),
  createdby varchar,
  alteredby varchar,
  lastupdated timestamp DEFAULT now(),
  CONSTRAINT holiday_authorisers_pkey PRIMARY KEY (id),
  CONSTRAINT authoriser_id_fkey FOREIGN KEY (authoriser_id)
      REFERENCES employees (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT employee_id_fkey FOREIGN KEY (employee_id)
      REFERENCES employees (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

ALTER TABLE holiday_requests DROP CONSTRAINT holiday_requests_approved_by_fkey;

ALTER TABLE holiday_requests ALTER COLUMN approved_by TYPE int4 USING cast(approved_by AS int4); 
ALTER TABLE holiday_requests DROP COLUMN approved;
ALTER TABLE holiday_requests ADD COLUMN status varchar;
ALTER TABLE holiday_requests ALTER COLUMN status SET NOT NULL;

ALTER TABLE holiday_requests
  ADD CONSTRAINT holiday_requests_approved_by_fkey FOREIGN KEY (approved_by)
      REFERENCES employees (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

CREATE TABLE expenses_header
( id bigserial NOT NULL,
  expense_number int8 NOT NULL,
  our_reference character varying,
  employee_id int4 NOT NULL,
  expense_date date NOT NULL DEFAULT now(),
  currency_id integer NOT NULL,
  rate numeric NOT NULL,
  gross_value numeric NOT NULL,
  tax_value numeric NOT NULL,
  net_value numeric NOT NULL,
  twin_currency_id integer NOT NULL,
  twin_rate numeric NOT NULL,
  twin_gross_value numeric NOT NULL,
  twin_tax_value numeric NOT NULL,
  twin_net_value numeric NOT NULL,
  base_gross_value numeric NOT NULL,
  base_tax_value numeric NOT NULL,
  base_net_value numeric NOT NULL,
  status character varying NOT NULL,
  description text,
  authorised_date date,
  authorised_by int4,
  created timestamp DEFAULT now(),
  createdby varchar,
  alteredby varchar,
  lastupdated timestamp DEFAULT now(),
  usercompanyid bigint NOT NULL,
  CONSTRAINT expenses_header_pkey PRIMARY KEY (id),
  CONSTRAINT expenses_header_authorised_by_fkey FOREIGN KEY (authorised_by)
      REFERENCES employees (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION,
  CONSTRAINT expenses_header_employee_id_fkey FOREIGN KEY (employee_id)
      REFERENCES employees (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION,
  CONSTRAINT expenses_header_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE expenses_lines
( id bigserial NOT NULL,
  expenses_header_id int8 NOT NULL,
  line_number integer NOT NULL,
  item_description character varying,
  qty numeric,
  purchase_price numeric,
  currency_id integer NOT NULL,
  rate numeric NOT NULL,
  gross_value numeric NOT NULL,
  tax_value numeric NOT NULL,
  tax_rate_id integer,
  net_value numeric NOT NULL,
  twin_currency_id integer NOT NULL,
  twin_rate numeric NOT NULL,
  twin_gross_value numeric NOT NULL,
  twin_tax_value numeric NOT NULL,
  twin_net_value numeric NOT NULL,
  base_gross_value numeric NOT NULL,
  base_tax_value numeric NOT NULL,
  base_net_value numeric NOT NULL,
  glaccount_id integer NOT NULL,
  glcentre_id integer NOT NULL,
  created timestamp DEFAULT now(),
  createdby varchar,
  alteredby varchar,
  lastupdated timestamp DEFAULT now(),
  usercompanyid bigint NOT NULL,
  CONSTRAINT expenses_lines_pkey PRIMARY KEY (id),
  CONSTRAINT expenses_lines_expenses_header_id_fkey FOREIGN KEY (expenses_header_id)
      REFERENCES expenses_header (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT expenses_lines_tax_rate_id_fkey FOREIGN KEY (tax_rate_id)
      REFERENCES taxrates (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION,
  CONSTRAINT expenses_lines_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT expenses_lines_line_number UNIQUE (expenses_header_id, line_number)
);

CREATE OR REPLACE VIEW expenses_header_overview AS 
 SELECT eh.*, (p.firstname::text || ' '::text) || p.surname::text, cum.currency, twc.currency AS twin
   FROM expenses_header eh
   JOIN employees e ON eh.employee_id = e.id
   JOIN person p ON e.person_id = p.id
   JOIN cumaster cum ON eh.currency_id = cum.id
   JOIN cumaster twc ON eh.twin_currency_id = twc.id;

CREATE OR REPLACE VIEW expenses_lines_overview AS 
 SELECT eh.expense_date, eh.expense_number, el.*
, cu.currency
, (glc.cost_centre::text || ' - '::text) || glc.description::text AS glcentre
, (gla.account::text || ' - '::text) || gla.description::text AS glaccount
, t.taxrate
   FROM expenses_lines el
   JOIN gl_centres glc ON glc.id = el.glcentre_id
   JOIN gl_accounts gla ON gla.id = el.glaccount_id
   JOIN cumaster cu ON cu.id = el.currency_id
   JOIN taxrates t ON t.id = el.tax_rate_id
   JOIN expenses_header eh ON eh.id = el.expenses_header_id;

CREATE TABLE eltransactions
(
  id serial NOT NULL,
  employee_id int4 NOT NULL,
  transaction_date date NOT NULL DEFAULT now(),
  transaction_type varchar NOT NULL,
  status varchar NOT NULL,
  our_reference varchar NOT NULL,
  ext_reference varchar,
  currency_id int4 NOT NULL,
  rate numeric NOT NULL,
  gross_value numeric NOT NULL,
  tax_value numeric NOT NULL,
  net_value numeric NOT NULL,
  twin_currency_id int4 NOT NULL,
  twin_rate numeric NOT NULL,
  twin_tax_value numeric NOT NULL,
  twin_net_value numeric NOT NULL,
  twin_gross_value numeric NOT NULL,
  base_tax_value numeric NOT NULL,
  base_gross_value numeric NOT NULL,
  base_net_value numeric NOT NULL,
  cross_ref varchar,
  os_value numeric NOT NULL,
  twin_os_value numeric NOT NULL,
  base_os_value numeric NOT NULL,
  description text,
  for_payment bool DEFAULT false,
  usercompanyid int8 NOT NULL,
  created timestamp DEFAULT now(),
  createdby varchar,
  alteredby varchar,
  lastupdated timestamp DEFAULT now(),
  CONSTRAINT eltransactions_pkey PRIMARY KEY (id),
  CONSTRAINT eltransactions_employee_id_fkey FOREIGN KEY (employee_id)
      REFERENCES employees (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT eltransactions_currency_id_fkey FOREIGN KEY (currency_id)
      REFERENCES cumaster (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT eltransactions_twin_currency_fkey FOREIGN KEY (twin_currency_id)
      REFERENCES cumaster (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT eltransactions_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE OR REPLACE VIEW eltransactionsoverview AS 
 SELECT elt.id, elt.employee_id, elt.transaction_date, elt.transaction_type, elt.status, elt.our_reference, elt.ext_reference, elt.currency_id, elt.rate, elt.gross_value, elt.tax_value, elt.net_value, elt.twin_currency_id, elt.twin_rate, elt.twin_tax_value, elt.twin_net_value, elt.twin_gross_value, elt.base_tax_value, elt.base_gross_value, elt.base_net_value, elt.cross_ref, elt.os_value, elt.twin_os_value, elt.base_os_value, elt.description, elt.for_payment, elt.usercompanyid, elt.created, elt.createdby, elt.alteredby, elt.lastupdated, (per.firstname::text || ' '::text) || per.surname::text AS employee, per.company_id, cum.currency, twc.currency AS twin
   FROM eltransactions elt
   JOIN employees emp ON elt.employee_id = emp.id
   JOIN person per ON emp.person_id = per.id
   JOIN cumaster cum ON elt.currency_id = cum.id
   JOIN cumaster twc ON elt.twin_currency_id = twc.id;

CREATE TABLE training_objectives
(
  id bigserial NOT NULL,
  name varchar NOT NULL,
  description varchar,
  created timestamp DEFAULT now(),
  createdby varchar,
  lastupdated timestamp DEFAULT now(),
  alteredby varchar,
  usercompanyid int8 NOT NULL,
  CONSTRAINT training_objectives_pkey PRIMARY KEY (id),
  CONSTRAINT training_objectives_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
);

CREATE TABLE employee_training_plans (
  id bigserial NOT NULL,
  employee_id int8 NOT NULL,
  training_objective_id int8 NOT NULL,
  progress int2,
  expected_start_date timestamp,
  expected_end_date timestamp,
  actual_start_date timestamp,
  actual_end_date timestamp,
  description varchar,
  created timestamp DEFAULT now(),
  createdby varchar,
  lastupdated timestamp DEFAULT now(),
  alteredby varchar,
  usercompanyid int8 NOT NULL,
  CONSTRAINT employee_training_plans_pkey PRIMARY KEY (id),
  CONSTRAINT employee_training_plans_employee_id_fkey FOREIGN KEY (employee_id)
      REFERENCES employees (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT employee_training_plans_training_objective_id_fkey FOREIGN KEY (training_objective_id)
      REFERENCES training_objectives (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT employee_training_plans_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
);

CREATE OR REPLACE VIEW employeetrainingplans_overview AS 
 SELECT etp.id, etp.employee_id, etp.training_objective_id, etp.progress, etp.expected_start_date, etp.expected_end_date, etp.actual_start_date, etp.actual_end_date, etp.description, etp.created, etp.createdby, etp.lastupdated, etp.alteredby, etp.usercompanyid, tro.name, (per.firstname::text || ' '::text) || per.surname::text AS person
   FROM employee_training_plans etp
   JOIN employees emp ON etp.employee_id = emp.id
   JOIN person per ON emp.person_id = per.id
   LEFT JOIN training_objectives tro ON etp.training_objective_id = tro.id;