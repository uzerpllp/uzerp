--
-- $Revision: 1.1 $
--

--

ALTER TABLE output_details ADD COLUMN print_copies int;
 
ALTER TABLE output_details ALTER COLUMN print_copies SET DEFAULT 1;

UPDATE permissions
   SET description = 'Awaiting Despatch'
     , title = null
 WHERE permission = 'viewawaitingdespatch';
