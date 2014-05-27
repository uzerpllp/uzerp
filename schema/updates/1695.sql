--
-- $Revision: 1.2 $
--

--

CREATE VIEW uzlet_modules_overview AS
SELECT u.*, um.module_id, m.name as module
  FROM uzlets u
  JOIN uzlet_modules um on u.id = um.uzlet_id
  JOIN modules m on m.id = um.module_id;

UPDATE uzlets
   SET preset = false
 WHERE name = 'CompanySelectorEGlet';
