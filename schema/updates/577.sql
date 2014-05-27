CREATE TABLE address
(
  id serial NOT NULL,
  street1 varchar NOT NULL,
  street2 varchar,
  street3 varchar,
  town varchar NOT NULL,
  county varchar,
  postcode varchar NOT NULL,
  countrycode char(2) NOT NULL,
  usercompanyid int8 NOT NULL,
  CONSTRAINT address_pkey PRIMARY KEY (id),
  CONSTRAINT cc_fk FOREIGN KEY (countrycode)
      REFERENCES countries (code) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT address_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE contact_methods
(
  id serial NOT NULL,
  contact varchar,
  usercompanyid int8 NOT NULL,
  CONSTRAINT contact_methods_pkey PRIMARY KEY (id),
  CONSTRAINT contact_methods_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE party
(
  id bigserial NOT NULL,
  parent_id int8,
  "type" varchar,
  usercompanyid int8 NOT NULL,
  CONSTRAINT party__pkey PRIMARY KEY (id),
  CONSTRAINT party_parent_id FOREIGN KEY (parent_id)
      REFERENCES party (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT party_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE partyaddress
(
  id bigserial NOT NULL,
  address_id int8 NOT NULL,
  parent_id int4,
  name varchar NOT NULL DEFAULT 'MAIN'::character varying,
  main bool NOT NULL DEFAULT false,
  billing bool NOT NULL DEFAULT false,
  shipping bool NOT NULL DEFAULT false,
  payment bool NOT NULL DEFAULT false,
  technical bool NOT NULL DEFAULT false,
  party_id int8,
  usercompanyid int8 NOT NULL,
  CONSTRAINT partyaddres_pkey PRIMARY KEY (id),
  CONSTRAINT partyaddres_address_id_fkey FOREIGN KEY (address_id)
      REFERENCES address (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT partyaddress_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE

);

CREATE TABLE party_contact_methods
(
  id bigserial NOT NULL,
  contactmethod_id int8 NOT NULL,
  name varchar NOT NULL DEFAULT 'MAIN'::character varying,
  main bool NOT NULL DEFAULT false,
  billing bool NOT NULL DEFAULT false,
  shipping bool NOT NULL DEFAULT false,
  payment bool NOT NULL DEFAULT false,
  technical bool NOT NULL DEFAULT false,
  party_id int8,
  "type" varchar NOT NULL,
  usercompanyid int8 NOT NULL,
  CONSTRAINT party_contact_methods_pkey PRIMARY KEY (id),
  CONSTRAINT party_contact_methods_id_fkey FOREIGN KEY (contactmethod_id)
      REFERENCES contact_methods (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT party_contact_methods_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE

);

CREATE TABLE party_notes
(
  id serial NOT NULL,
  title varchar NOT NULL,
  note text NOT NULL,
  party_id int8 NOT NULL,
  "owner" varchar NOT NULL,
  alteredby varchar NOT NULL,
  lastupdated timestamp NOT NULL DEFAULT now(),
  created timestamp NOT NULL DEFAULT now(),
  usercompanyid int8 NOT NULL,
  CONSTRAINT party_notes_pkey PRIMARY KEY (id),
  CONSTRAINT party_notes_alteredby_fkey FOREIGN KEY (alteredby)
      REFERENCES users (username) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT party_notes_owner_fkey FOREIGN KEY ("owner")
      REFERENCES users (username) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT party_notes_party_id_fkey FOREIGN KEY (party_id)
      REFERENCES party (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT party_notes_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

ALTER TABLE company ADD COLUMN party_id int8;

ALTER TABLE company
  ADD CONSTRAINT company_party_id_fk FOREIGN KEY (party_id)
      REFERENCES party (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE person ADD COLUMN party_id int8;

ALTER TABLE person
  ADD CONSTRAINT person_party_id_fk FOREIGN KEY (party_id)
      REFERENCES party (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE company ADD COLUMN classification_id int4;

ALTER TABLE company ADD COLUMN source_id int4;

ALTER TABLE company ADD COLUMN industry_id int4;

ALTER TABLE company ADD COLUMN status_id int4;

ALTER TABLE company ADD COLUMN rating_id int4;

ALTER TABLE company ADD COLUMN account_status_id int4;

ALTER TABLE company ADD COLUMN type_id int4;

ALTER TABLE company
  ADD CONSTRAINT company_account_status_id_fkey FOREIGN KEY (account_status_id)
      REFERENCES account_statuses (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

ALTER TABLE company
  ADD CONSTRAINT company_classification_id_fkey FOREIGN KEY (classification_id)
      REFERENCES company_classifications (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

ALTER TABLE company
  ADD CONSTRAINT company_industry_id_fkey FOREIGN KEY (industry_id)
      REFERENCES company_industries (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

ALTER TABLE company
  ADD CONSTRAINT company_rating_id_fkey FOREIGN KEY (rating_id)
      REFERENCES company_ratings (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

ALTER TABLE company
  ADD CONSTRAINT company_source_id_fkey FOREIGN KEY (source_id)
      REFERENCES company_sources (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

ALTER TABLE company
  ADD CONSTRAINT company_status_id_fkey FOREIGN KEY (status_id)
      REFERENCES company_statuses (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

ALTER TABLE company
  ADD CONSTRAINT company_type_id_fkey FOREIGN KEY (type_id)
      REFERENCES company_types (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

update company
   set classification_id
   = (select classification_id
       FROM company_crm
      WHERE company_id = company.id);

update company
   set source_id
   = (select source_id
       FROM company_crm
      WHERE company_id = company.id);

update company
   set industry_id
   = (select industry_id
       FROM company_crm
      WHERE company_id = company.id);

update company
   set status_id
   = (select status_id
       FROM company_crm
      WHERE company_id = company.id);

update company
   set rating_id
   = (select rating_id
       FROM company_crm
      WHERE company_id = company.id);

update company
   set account_status_id
   = (select account_status_id
       FROM company_crm
      WHERE company_id = company.id);

update company
   set type_id
   = (select type_id
       FROM company_crm
      WHERE company_id = company.id);

ALTER TABLE po_header DROP CONSTRAINT po_header_companyaddress_id_fkey;

ALTER TABLE po_header
  ADD CONSTRAINT po_header_del_address_id_fkey FOREIGN KEY (del_address_id)
      REFERENCES address (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE so_header DROP CONSTRAINT so_header_companyaddress_id_fkey;

ALTER TABLE so_header
  ADD CONSTRAINT so_header_del_address_id_fkey FOREIGN KEY (del_address_id)
      REFERENCES address (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE;

update wh_stores
set address_id=null;

ALTER TABLE wh_stores DROP CONSTRAINT wh_stores_companyaddress_id_fkey;

ALTER TABLE wh_stores
  ADD CONSTRAINT wh_stores_address_id_fkey FOREIGN KEY (address_id)
      REFERENCES address (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

ALTER TABLE wh_transfers DROP CONSTRAINT wh_transfers_from_address_id_fkey;

ALTER TABLE wh_transfers
  ADD CONSTRAINT wh_transfers_from_address_id_fkey FOREIGN KEY (from_address_id)
      REFERENCES address (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

ALTER TABLE wh_transfers DROP CONSTRAINT wh_transfers_to_address_id_fkey;

ALTER TABLE wh_transfers
  ADD CONSTRAINT wh_transfers_to_address_id_fkey FOREIGN KEY (to_address_id)
      REFERENCES address (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

-- Views

DROP VIEW companyaddressoverview;

ALTER TABLE companyaddress RENAME TO companyaddressold;

DROP VIEW companyoverview;

DROP VIEW company_notesoverview;

DROP VIEW personaddress_overview;

ALTER TABLE personaddress RENAME TO personaddressold;

DROP VIEW personoverview;

DROP VIEW person_notesoverview;

CREATE OR REPLACE VIEW party_notesoverview AS 
 SELECT n.id, n.title, n.note, n.party_id, n."owner", n.alteredby, n.lastupdated, n.created, n.usercompanyid, COALESCE(c.name::text || per.surname::text) AS party
   FROM party_notes n
   JOIN party p ON p.id = n.party_id
   LEFT JOIN company c ON p.id = c.party_id
   LEFT JOIN person per ON p.id = per.party_id;

CREATE OR REPLACE VIEW partyaddressoverview AS 
 SELECT ((((((COALESCE(a.street1, ''::character varying)::text || ','::text) || (COALESCE(a.street2, ''::character varying)::text || ','::text)) || (COALESCE(a.street3, ''::character varying)::text || ','::text)) || (COALESCE(a.town, ''::character varying)::text || ','::text)) || (COALESCE(a.county, ''::character varying)::text || ','::text)) || (COALESCE(a.postcode, ''::character varying)::text || ','::text)) || COALESCE(a.countrycode, ''::character varying::bpchar)::text AS fulladdress, a.street1, a.street2, a.street3, a.town, a.county, a.postcode, a.countrycode, p.id, p.address_id, p.name, p.main, p.billing, p.shipping, p.payment, p.technical, p.party_id, p.parent_id, p.usercompanyid
   FROM partyaddress p
   JOIN address a ON p.address_id = a.id;

CREATE OR REPLACE VIEW partycontactmethodoverview AS 
 SELECT c.contact, p.id, p.contactmethod_id, p.name, p.main, p.billing, p.shipping, p.payment, p.technical, p.party_id, p."type", p.usercompanyid
   FROM party_contact_methods p
   JOIN contact_methods c ON p.contactmethod_id = c.id;

CREATE OR REPLACE VIEW companyoverview AS 
 SELECT c.id, c.name, c.accountnumber, c.creditlimit, c.vatnumber, c.companynumber, c.website, c.employees, c.usercompanyid, c.parent_id, c."owner", c.assigned, c.created, c.lastupdated, c.alteredby, c.description, c.is_lead, c.party_id, pa.address_id, a.street1, a.street2, a.street3, a.town, a.county, a.countrycode, a.postcode
   FROM company c
   LEFT JOIN party p ON c.party_id = p.id
   LEFT JOIN partyaddress pa ON p.id = pa.party_id AND pa.main
   LEFT JOIN address a ON a.id = pa.address_id;

CREATE OR REPLACE VIEW companyaddress AS 
 SELECT a.id, a.street1, a.street2, a.street3, a.town, a.county, a.postcode, a.countrycode
, pa.name, pa.main, pa.billing, pa.shipping, pa.payment, pa.technical
, pa.party_id
   FROM address a
   JOIN partyaddress pa ON pa.address_id = a.id
   JOIN party p ON p.id = pa.party_id
    AND p."type" = 'Company';

CREATE OR REPLACE VIEW companyaddressoverview AS 
 SELECT ca.street1, ca.street2, ca.street3, ca.town, ca.county, ca.postcode, ca.countrycode
, c.id as company_id, ca.name, ca.main, ca.billing, ca.shipping, ca.payment, ca.technical, ca.id
, c.name AS company
, (((((((((((ca.street1::text || ', '::text) || 
COALESCE(ca.street2, ''::character varying)::text) || ', '::text) || 
COALESCE(ca.street3, ''::character varying)::text) || ', '::text) || 
ca.town::text) || ', '::text) || 
COALESCE(ca.county, ''::character varying)::text) || ', '::text) || 
COALESCE(ca.postcode, ''::character varying)::text) || ', '::text) || 
co.name::text AS address, co.name AS country
   FROM companyaddress ca
   JOIN company c ON c.party_id = ca.party_id
   JOIN countries co ON ca.countrycode = co.code;

CREATE OR REPLACE VIEW company_notesoverview AS 
 SELECT n.id, n.title, n.note, n.party_id, n."owner", n.alteredby, n.lastupdated, n.created
, n.usercompanyid, c.name AS company, c.id as company_id
   FROM party_notes n
   JOIN party p ON p.id = n.party_id
   JOIN company c ON p.id = c.party_id;

CREATE OR REPLACE VIEW personaddress AS 
 SELECT pa.fulladdress, pa.street1, pa.street2, pa.street3, pa.town, pa.county, pa.postcode, pa.countrycode, pa.id, pa.address_id, pa.name, pa.main, pa.billing, pa.shipping, pa.payment, pa.technical, pa.party_id, pa.parent_id, pe.id AS person_id
   FROM person pe
   JOIN party p ON p.id = pe.party_id AND p."type"::text = 'Person'::text
   JOIN partyaddressoverview pa ON p.id = pa.party_id;

CREATE OR REPLACE VIEW personaddress_overview AS 
 SELECT personaddress.id, (((((((((personaddress.street1::text || ', '::text) || personaddress.street2::text) || ', '::text) || personaddress.street3::text) || ', '::text) || personaddress.town::text) || ', '::text) || personaddress.county::text) || ', '::text) || personaddress.postcode::text AS address, personaddress.countrycode, personaddress.person_id, personaddress.name, personaddress.main, personaddress.billing, personaddress.shipping, personaddress.payment, personaddress.technical
   FROM personaddress personaddress;

CREATE OR REPLACE VIEW personoverview AS 
 SELECT per.id, per.title, per.firstname, per.middlename, per.surname, per.suffix, per.department, per.jobtitle, per.dob, per.ni, per.marital, per.lang, per.company_id, per."owner", per.userdetail, per.reports_to, per.can_call, per.can_email, per.assigned_to, per.created, per.lastupdated, per.alteredby, per.usercompanyid, per.crm_source, per.published_username, per.allow_publish, per.party_id, (((((((COALESCE(per.title, ''::character varying)::text || ' '::text) || per.firstname::text) || ' '::text) || COALESCE(per.middlename, ''::character varying)::text) || ' '::text) || per.surname::text) || ' '::text) || COALESCE(per.suffix, ''::character varying)::text AS name, pa.address_id, a.street1, a.street2, a.street3, a.town, a.county, a.countrycode, a.postcode, c.name AS company
   FROM person per
   LEFT JOIN company c ON per.company_id = c.id
   LEFT JOIN party p ON per.party_id = p.id
   LEFT JOIN partyaddress pa ON p.id = pa.party_id AND pa.main
   LEFT JOIN address a ON a.id = pa.address_id;

CREATE OR REPLACE VIEW person_notesoverview AS 
 SELECT n.id, n.title, n.note, n.party_id, n."owner", n.alteredby, n.lastupdated
, n.created, n.usercompanyid
, per.id as person_id
, (per.firstname::text || ' '::text) || per.surname::text AS person
   FROM party_notes n
   JOIN party p ON p.id = n.party_id
   JOIN person per ON p.id = per.party_id;

-- Functions

CREATE OR REPLACE FUNCTION migrate_companies_to_parties()
  RETURNS void AS
$BODY$
DECLARE
  partyid int;
  usercompanyid int;
  addressid int;
  contactid int;
  address record;
  companydetail record;
  contactdetails record;
  notes record;
  main varchar;
  billing varchar;
  shipping varchar;
  payment varchar;
  technical varchar;

BEGIN

  FOR companydetail IN SELECT * FROM company
  LOOP
    EXECUTE 'SELECT nextval(''party_id_seq'')' INTO partyid;
    EXECUTE 'INSERT INTO party
               (id, "type", usercompanyid)
             VALUES
               ('||partyid||',''Company'','||companydetail.usercompanyid||')';
    EXECUTE 'UPDATE company
                SET party_id='||partyid||'
              WHERE id='||companydetail.id;
  END LOOP;

  FOR address IN SELECT * FROM companyaddressold
  LOOP
    IF address.main THEN main='true'; ELSE main='false'; END IF;
    if address.billing then billing='true'; else billing='false'; end if;
    if address.shipping then shipping='true'; else shipping='false'; end if;
    if address.payment then payment='true'; else payment='false'; end if;
    if address.technical then technical='true'; else technical='false'; end if;
    EXECUTE 'SELECT party_id, usercompanyid FROM company
      WHERE id = '''|| address.company_id ||'''' INTO partyid, usercompanyid;
    EXECUTE 'SELECT nextval(''address_id_seq'')' INTO addressid;
    execute 'insert into address
               (id
               ,street1
               ,street2
               ,street3
               ,town
               ,county
               ,countrycode
               ,postcode
               ,usercompanyid)
             values
               ('|| 
               addressid || ','''||
               address.street1 ||''','''||
               coalesce(address.street2,'') ||''','''||
               coalesce(address.street3,'') ||''','''||
               address.town ||''','''||
               coalesce(address.county,'') ||''','''||
               address.countrycode ||''','''||
               address.postcode ||''','||
               usercompanyid ||')';
    execute 'insert into partyaddress
               (address_id
               ,name
               ,main
               ,billing
               ,shipping
               ,payment
               ,technical
               ,party_id
               ,usercompanyid
               )
             values
               ('||
               addressid ||','''||
               address.name ||''','||
               main ||','||
               billing ||','||
               shipping ||','||
               payment ||','||
               technical ||','||
               partyid ||','||
               usercompanyid ||')';
  END LOOP;

  FOR contactdetails IN SELECT * FROM company_contact_methods
  LOOP
    IF contactdetails.main THEN
       main='true';
    ELSE
       main='false';
    END IF;
    if contactdetails.billing then billing='true'; else billing='false'; end if;
    if contactdetails.shipping then shipping='true'; else shipping='false'; end if;
    if contactdetails.payment then payment='true'; else payment='false'; end if;
    if contactdetails.technical then technical='true'; else technical='false'; end if;
    EXECUTE 'SELECT party_id, usercompanyid FROM company
      WHERE id = '''|| contactdetails.company_id ||'''' INTO partyid, usercompanyid;
    EXECUTE 'SELECT nextval(''contact_methods_id_seq'')' INTO contactid;
    execute 'insert into contact_methods
               (id ,contact, usercompanyid)
             values
               ('|| contactid || ','''|| contactdetails.contact ||''','||usercompanyid||')';
    execute 'insert into party_contact_methods
               (contactmethod_id
               ,name
               ,"type"
               ,main
               ,billing
               ,shipping
               ,payment
               ,technical
               ,party_id
               ,usercompanyid
               )
             values
               ('||
               contactid ||','''||
               contactdetails.name ||''','''||
               contactdetails."type" ||''','||
               main ||','||
               billing ||','||
               shipping ||','||
               payment ||','||
               technical ||','||
               partyid ||','||
               usercompanyid ||')';
  END LOOP;

  FOR notes IN SELECT * FROM company_notes
  LOOP
    EXECUTE 'SELECT party_id, usercompanyid FROM company
      WHERE id = '''|| notes.company_id ||'''' INTO partyid, usercompanyid;
    execute 'insert into party_notes
               (title
               ,note
               ,"owner"
               ,alteredby
               ,lastupdated
               ,created
               ,party_id
               ,usercompanyid
               )
             values
               ('''||
               notes.title ||''','''||
               notes.note ||''','''||
               notes."owner" ||''','''||
               notes.alteredby ||''','''||
               notes.lastupdated ||''','''||
               notes.created ||''','||
               partyid ||','||
               usercompanyid ||')';
  END LOOP;

END;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE;

CREATE OR REPLACE FUNCTION migrate_people_to_parties()
  RETURNS void AS
$BODY$
DECLARE
  partyid int;
  usercompanyid int;
  addressid int;
  contactid int;
  address record;
  persondetail record;
  contactdetails record;
  notes record;
  main varchar;
  billing varchar;
  shipping varchar;
  payment varchar;
  technical varchar;

BEGIN

  FOR persondetail IN SELECT * FROM person
  LOOP
    EXECUTE 'SELECT nextval(''party_id_seq'')' INTO partyid;
    EXECUTE 'INSERT INTO party
               (id, "type", usercompanyid)
             VALUES
               ('||partyid||',''Person'','||persondetail.usercompanyid||')';
    EXECUTE 'UPDATE person
                SET party_id='||partyid||'
              WHERE id='||persondetail.id;
  END LOOP;

  FOR address IN SELECT * FROM personaddressold
  LOOP
    IF address.main THEN main='true'; ELSE main='false'; END IF;
    if address.billing then billing='true'; else billing='false'; end if;
    if address.shipping then shipping='true'; else shipping='false'; end if;
    if address.payment then payment='true'; else payment='false'; end if;
    if address.technical then technical='true'; else technical='false'; end if;
    EXECUTE 'SELECT party_id, usercompanyid FROM person
      WHERE id = '''|| address.person_id ||'''' INTO partyid, usercompanyid;
    EXECUTE 'SELECT nextval(''address_id_seq'')' INTO addressid;
    execute 'insert into address
               (id
               ,street1
               ,street2
               ,street3
               ,town
               ,county
               ,countrycode
               ,postcode
               ,usercompanyid)
             values
               ('|| 
               addressid || ','''||
               address.street1 ||''','''||
               coalesce(address.street2,'') ||''','''||
               coalesce(address.street3,'') ||''','''||
               address.town ||''','''||
               coalesce(address.county,'') ||''','''||
               address.countrycode ||''','''||
               address.postcode ||''','||
               usercompanyid ||')';
    execute 'insert into partyaddress
               (address_id
               ,name
               ,main
               ,billing
               ,shipping
               ,payment
               ,technical
               ,party_id
               ,usercompanyid
               )
             values
               ('||
               addressid ||','''||
               address.name ||''','||
               main ||','||
               billing ||','||
               shipping ||','||
               payment ||','||
               technical ||','||
               partyid ||','||
               usercompanyid ||')';
  END LOOP;

  FOR contactdetails IN SELECT * FROM person_contact_methods
  LOOP
    IF contactdetails.main THEN main='true'; ELSE main='false'; END IF;
    IF contactdetails.billing THEN billing='true'; ELSE billing='false'; END IF;
    IF contactdetails.shipping THEN shipping='true'; ELSE shipping='false'; END IF;
    IF contactdetails.payment THEN payment='true'; ELSE payment='false'; END IF;
    IF contactdetails.technical THEN technical='true'; ELSE technical='false'; END IF;
    EXECUTE 'SELECT party_id, usercompanyid FROM person
      WHERE id = '''|| contactdetails.person_id ||'''' INTO partyid, usercompanyid;
    EXECUTE 'SELECT nextval(''contact_methods_id_seq'')' INTO contactid;
    execute 'insert into contact_methods
               (id ,contact, usercompanyid)
             values
               ('|| contactid || ','''|| contactdetails.contact ||''','||usercompanyid||')';
    execute 'insert into party_contact_methods
               (contactmethod_id
               ,name
               ,"type"
               ,main
               ,billing
               ,shipping
               ,payment
               ,technical
               ,party_id
               ,usercompanyid
               )
             values
               ('||
               contactid ||','''||
               contactdetails.name ||''','''||
               contactdetails."type" ||''','||
               main ||','||
               billing ||','||
               shipping ||','||
               payment ||','||
               technical ||','||
               partyid ||','||
               usercompanyid ||')';
  END LOOP;

  FOR notes IN SELECT * FROM person_notes
  LOOP
    EXECUTE 'SELECT party_id, usercompanyid FROM person
      WHERE id = '''|| notes.person_id ||'''' INTO partyid, usercompanyid;
    execute 'insert into party_notes
               (title
               ,note
               ,"owner"
               ,alteredby
               ,lastupdated
               ,created
               ,party_id
               ,usercompanyid
               )
             values
               ('''||
               notes.title ||''','''||
               notes.note ||''','''||
               notes."owner" ||''','''||
               notes.alteredby ||''','''||
               notes.lastupdated ||''','''||
               notes.created ||''','||
               partyid ||','||
               usercompanyid ||')';
  END LOOP;

END;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE;
