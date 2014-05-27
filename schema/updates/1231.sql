--
-- $Revision: 1.1 $
--

-- View: addressoverview

-- DROP VIEW addressoverview;

CREATE OR REPLACE VIEW addressoverview AS 
 SELECT a.*, co.name AS country
   FROM address a
   JOIN countries co ON a.countrycode = co.code;

ALTER TABLE addressoverview OWNER TO "www-data";
