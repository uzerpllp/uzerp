--
-- $Revision: 1.1 $
--

-- Column: non_failure

-- ALTER TABLE cs_failurecodes DROP COLUMN non_failure;

ALTER TABLE cs_failurecodes ADD COLUMN non_failure boolean;
ALTER TABLE cs_failurecodes ALTER COLUMN non_failure SET DEFAULT FALSE;

-- Column: in_use

-- ALTER TABLE cs_failurecodes DROP COLUMN in_use;

ALTER TABLE cs_failurecodes ADD COLUMN in_use boolean;
ALTER TABLE cs_failurecodes ALTER COLUMN in_use SET DEFAULT TRUE;

UPDATE cs_failurecodes
   SET non_failure = FALSE
     , in_use = TRUE;

DROP VIEW customer_service_summary;

CREATE OR REPLACE VIEW customer_service_summary AS 
 SELECT cs.slmaster_id, cs.customer, cs.product_group
 , cs.despatch_date, cs.usercompanyid
 , to_char(cs.despatch_date::timestamp with time zone, 'YYYY/MM'::text) AS year_month, 
        CASE
            WHEN fc.non_failure THEN 1
            WHEN cs.despatch_date > cs.due_despatch_date THEN 0
            ELSE 1
        END AS ontime, 
        CASE
            WHEN fc.non_failure THEN 1
            WHEN cs.order_qty > cs.despatch_qty THEN 0
            ELSE 1
        END AS infull, 
        CASE
            WHEN fc.non_failure THEN 1
            WHEN cs.despatch_date > cs.due_despatch_date THEN 0
            WHEN cs.order_qty > cs.despatch_qty THEN 0
            ELSE 1
        END AS otif, 1 AS count, cs.failurecode
   FROM customer_service cs
   LEFT JOIN cs_failurecodes fc ON fc.id = cs.cs_failurecode_id;

ALTER TABLE customer_service_summary OWNER TO "www-data";
