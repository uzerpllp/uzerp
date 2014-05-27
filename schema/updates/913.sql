create or replace view customer_service_summary as
 select despatch_date
      , usercompanyid
      , to_char(despatch_date, 'YYYY/MM') as year_month
      , CASE WHEN despatch_date>due_despatch_date THEN 0
             ELSE 1
        END as ontime
      , CASE WHEN order_qty>despatch_qty THEN 0
             ELSE 1
        END as infull
      , CASE WHEN despatch_date>due_despatch_date THEN 0
             WHEN order_qty>despatch_qty THEN 0
             ELSE 1
        END as otif
      , 1 as count
    from customer_service;