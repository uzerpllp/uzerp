--
-- $Revision: 1.2 $
--

DROP TABLE taxperiods;

--
-- Remove Tax Periods from Permissions
--
DELETE FROM permissions
 WHERE parent_id=(SELECT id
                    FROM permissions
                   WHERE permission='taxperiods');

DELETE FROM permissions
 WHERE permission='taxperiods';

--
-- Remove Tax Periods from Module Components
--
DELETE FROM module_components
 WHERE lower(location) like '%taxperiod%';

--
-- Remove Currency Rates from Permissions
--
DELETE FROM permissions
 WHERE parent_id=(SELECT id
                    FROM permissions
                   WHERE permission='currencyrates');

DELETE FROM permissions
 WHERE permission='currencyrates';
