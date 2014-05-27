ALTER TABLE mf_operations ALTER COLUMN start_date SET DEFAULT now();

ALTER TABLE mf_outside_ops ALTER COLUMN start_date SET DEFAULT now();