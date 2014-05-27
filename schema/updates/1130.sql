ALTER TABLE plmaster
  ADD CONSTRAINT plmaster_company_id_key UNIQUE(company_id);

ALTER TABLE slmaster
  ADD CONSTRAINT slmaster_company_id_key UNIQUE(company_id);