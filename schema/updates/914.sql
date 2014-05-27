DROP VIEW customer_service_summary;

CREATE OR REPLACE VIEW customer_service_summary AS 
 SELECT customer_service.slmaster_id, customer_service.customer, customer_service.product_group, customer_service.despatch_date, customer_service.usercompanyid, to_char(customer_service.despatch_date::timestamp with time zone, 'YYYY/MM'::text) AS year_month, 
        CASE
            WHEN customer_service.despatch_date > customer_service.due_despatch_date THEN 0
            ELSE 1
        END AS ontime, 
        CASE
            WHEN customer_service.order_qty > customer_service.despatch_qty THEN 0
            ELSE 1
        END AS infull, 
        CASE
            WHEN customer_service.despatch_date > customer_service.due_despatch_date THEN 0
            WHEN customer_service.order_qty > customer_service.despatch_qty THEN 0
            ELSE 1
        END AS otif, 1 AS count
   FROM customer_service;