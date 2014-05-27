CREATE TABLE gl_budget_years
(
  id bigserial NOT NULL,
  "year" smallint NOT NULL,
  closed boolean DEFAULT false,
  usercompanyid bigint NOT NULL,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT gl_budget_years_pkey PRIMARY KEY (id),
  CONSTRAINT gl_budget_years_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
)

INSERT INTO gl_budget_years (year, closed, usercompanyid, created, createdby, alteredby, lastupdated) VALUES (2007, true, 1, '2010-08-27 15:47:03.051933', NULL, NULL, '2010-08-27 15:47:03.051933');
INSERT INTO gl_budget_years (year, closed, usercompanyid, created, createdby, alteredby, lastupdated) VALUES (2008, true, 1, '2010-08-27 15:47:13.333746', NULL, NULL, '2010-08-27 15:47:13.333746');
INSERT INTO gl_budget_years (year, closed, usercompanyid, created, createdby, alteredby, lastupdated) VALUES (2009, true, 1, '2010-08-27 15:47:13.333746', NULL, NULL, '2010-08-27 15:47:13.333746');
INSERT INTO gl_budget_years (year, closed, usercompanyid, created, createdby, alteredby, lastupdated) VALUES (2010, false, 1, '2010-08-27 15:47:13.333746', NULL, NULL, '2010-08-27 15:47:13.333746');
