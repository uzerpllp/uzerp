--
-- $Revision: 1.1 $
--

ALTER TABLE pi_header ALTER COLUMN tax_status_id SET NOT NULL;

ALTER TABLE pi_lines ALTER COLUMN tax_rate_id SET NOT NULL;

ALTER TABLE si_header ALTER COLUMN tax_status_id SET NOT NULL;

ALTER TABLE si_lines ALTER COLUMN tax_rate_id SET NOT NULL;