--
-- $Revision: 1.1 $
--

--
-- Modules/Components
--

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'complaintscontroller', 'C', location||'/controllers/ComplaintsController.php', id, 'Complaints'
   FROM modules m
  WHERE name = 'quality';
