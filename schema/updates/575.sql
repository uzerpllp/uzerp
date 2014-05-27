-- SQL LISTINGS FOR QUALITY CONTROL MODULE
-- LAST ADDIITION 31/10/08

CREATE TABLE qc_complaint_codes
(
  id serial NOT NULL,
  code character varying NOT NULL,
  description character varying NOT NULL,
  CONSTRAINT qc_complaint_codes_pkey PRIMARY KEY (id)
);

CREATE TABLE qc_supplementary_complaint_codes
(
  id serial NOT NULL,
  complaint_code_id bigint NOT NULL,
  code character varying NOT NULL,
  description character varying NOT NULL,
  CONSTRAINT supplementary_complaint_codes_pkey PRIMARY KEY (id),
  CONSTRAINT supplementary_complaint_codes_complaint_code_id_fkey FOREIGN KEY (complaint_code_id)
      REFERENCES qc_complaint_codes (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
);

CREATE TABLE qc_complaint_type
(
  id serial NOT NULL,
  code character varying NOT NULL,
  description character varying NOT NULL,
  CONSTRAINT complaint_type_pkey PRIMARY KEY (id)
);

CREATE TABLE qc_complaints
(
  id serial NOT NULL,
  date date NOT NULL DEFAULT now(),
  customer_id bigint NOT NULL,
  product_id bigint NOT NULL,
  complaint_code_id bigint,
  supplementary_code_id bigint,
  problem character varying,
  investigation character varying,
  outcome character varying,
  credit_amount numeric,
  credit_note_no character varying,
  invoice_debit_no character varying,
  date_complete date,
  "cost" numeric,
  usercompanyid bigint NOT NULL,
  lastupdated timestamp without time zone NOT NULL DEFAULT now(),
  alteredby character varying NOT NULL,
  created timestamp without time zone NOT NULL DEFAULT now(),
  assignedto character varying,
  "type" character varying DEFAULT now(),
  complaint_number bigint,
  CONSTRAINT complaints_pkey PRIMARY KEY (id),
  CONSTRAINT complaints_complaint_code_id_fkey FOREIGN KEY (complaint_code_id)
      REFERENCES qc_complaint_codes (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT complaints_customer_id_fkey FOREIGN KEY (customer_id)
      REFERENCES slmaster (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT complaints_product_id_fkey FOREIGN KEY (product_id)
      REFERENCES st_items (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT complaints_supplementary_code_id_fkey FOREIGN KEY (supplementary_code_id)
      REFERENCES qc_supplementary_complaint_codes (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT complaints_type_number_ukey UNIQUE (type, complaint_number)
);

CREATE TABLE qc_volume
(
  id bigserial NOT NULL,
  "year" smallint NOT NULL,
  period smallint NOT NULL,
  packs bigint NOT NULL,
  CONSTRAINT qc_volume_pkey PRIMARY KEY (id),
  CONSTRAINT qc_volume_year_key UNIQUE (year, period)
);

CREATE OR REPLACE VIEW qc_complaint_volume AS 
 SELECT qc_volume.id, qc_volume.year, qc_volume.period, qc_volume.packs
   FROM qc_volume
  ORDER BY qc_volume.year DESC, qc_volume.period DESC;

CREATE OR REPLACE VIEW qc_complaints_million AS 
  SELECT gl.year, gl.period, substr(gl.description::text, 1, 3) AS "month", qv.packs, count(qc.date) AS "complaints", round(count(qc.date)::numeric / (qv.packs::numeric / 1000000::numeric), 2) AS "For Month"
   FROM gl_periods gl
   LEFT OUTER JOIN qc_complaints qc 
     ON gl.year = int4(date_part('year'::text, qc.date)) 
     AND int4(date_part('month'::text, qc.date)) = gl.period
     AND qc.type::text = 'SD'::text 
   LEFT JOIN qc_volume qv 
     ON gl.year = qv.year 
     AND gl.period = qv.period
  WHERE gl.year = int4(date_part('year'::text, 'now'::text::date - '1 mon'::interval)) 
    AND gl.period > 0 
    AND gl.period <= int4(date_part('month'::text, 'now'::text::date - '1 mon'::interval))
  GROUP BY gl.period, gl.year, gl.description, qv.packs
  ORDER BY gl.period;


CREATE OR REPLACE VIEW qc_complaints_month AS 
 SELECT qv.year, qv.period, qc.complaints, qv.packs, substr(gl.description::text, 1, 3) AS month, round(qc.complaints::numeric / (qv.packs::numeric / 1000000::numeric), 2) AS round
   FROM qc_volume qv, ( SELECT int4(date_part('year'::text, qc_complaints.date)) AS year, int4(date_part('month'::text, qc_complaints.date)) AS period, count(qc_complaints.date) AS complaints
           FROM qc_complaints
          WHERE qc_complaints.type::text = 'SD'::text
          GROUP BY int4(date_part('year'::text, qc_complaints.date)), int4(date_part('month'::text, qc_complaints.date))
          ORDER BY int4(date_part('year'::text, qc_complaints.date)), int4(date_part('month'::text, qc_complaints.date))) qc
   JOIN gl_periods gl ON qc.period = gl.period AND qc.year = gl.year
  WHERE qc.year = qv.year AND qc.period = qv.period;

CREATE OR REPLACE VIEW qc_complaints_overview AS 
 SELECT qc.id, qc.date, qc.customer_id, sl.name AS customer, qc.product_id, st.description AS product, qc.complaint_code_id, qc.supplementary_code_id, qc.problem, qc.investigation, qc.outcome, qc.credit_amount, qc.credit_note_no, qc.invoice_debit_no, qc.date_complete, qc.cost, qc.usercompanyid, qc.lastupdated, qc.alteredby, qc.type, qc.complaint_number, qc.assignedto
   FROM qc_complaints qc
   JOIN slmaster sl ON sl.id = qc.customer_id
   JOIN st_items st ON st.id = qc.product_id;

CREATE OR REPLACE VIEW qc_production_complaints AS 
 SELECT gl.year, gl.period, substr(gl.description::text, 1, 3) AS "Month", count(qc.date) AS "For Month"
   FROM gl_periods gl
   LEFT JOIN qc_complaints qc ON gl.year = int4(date_part('year'::text, qc.date)) AND qc.complaint_code_id = 9 AND qc.type::text = 'SD'::text AND int4(date_part('month'::text, qc.date)) = gl.period
  WHERE gl.year = int4(date_part('year'::text, 'now'::text::date - '1 mon'::interval)) AND gl.period > 0 AND gl.period <= int4(date_part('month'::text, 'now'::text::date - '1 mon'::interval))
  GROUP BY gl.period, gl.year, gl.description
  ORDER BY gl.period;

CREATE OR REPLACE VIEW qc_supplementary_complaint_code_overview AS 
 SELECT qc_supplementary_complaint_codes.id, qc_supplementary_complaint_codes.complaint_code_id AS complaint_code, qc_supplementary_complaint_codes.code, qc_supplementary_complaint_codes.description
   FROM qc_supplementary_complaint_codes;

