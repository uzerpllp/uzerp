--
-- $Revision: 1.1 $
--

--

INSERT INTO module_components
 ("name", "type", location, module_id)
 SELECT 'campaignsearch', 'M', m.location||'/models/CampaignSearch.php', id
  FROM modules m
 WHERE m.name = 'crm';
 
