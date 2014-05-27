--
-- $Revision: 1.1 $
--

ALTER TABLE audit_lines ADD COLUMN memory_used bigint;
ALTER TABLE audit_lines ALTER COLUMN memory_used SET STORAGE PLAIN;