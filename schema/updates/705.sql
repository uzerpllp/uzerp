ALTER TABLE pi_header ADD COLUMN original_due_date date;

ALTER TABLE si_header ADD COLUMN original_due_date date;

ALTER TABLE pltransactions ADD COLUMN original_due_date date;

ALTER TABLE sltransactions ADD COLUMN original_due_date date;