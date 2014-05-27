CREATE TABLE sl_analysis
(
  id serial NOT NULL,
  name varchar NOT NULL,
  CONSTRAINT sl_analysis_pkey PRIMARY KEY (id)
);

ALTER TABLE slmaster ADD COLUMN sl_analysis_id integer;

ALTER TABLE slmaster
  ADD CONSTRAINT slmaster_sl_analysis_id_fkey FOREIGN KEY (sl_analysis_id)
      REFERENCES sl_analysis (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;