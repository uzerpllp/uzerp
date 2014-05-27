--
-- $Revision: 1.2 $
--

UPDATE periodic_payments
   SET glaccount_id = null
     , glcentre_id = null
 WHERE source IN ('SR', 'PP')
   AND (glaccount_id IS NOT NULL
        OR glcentre_id IS NOT NULL);
