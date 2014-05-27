--
-- $Revision: 1.1 $
--

--

UPDATE po_lines
   SET glaccount_centre_id = null
 WHERE status = 'X'
   AND glaccount_centre_id is not null;
 
