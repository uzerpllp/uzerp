create view pl_aged_creditors_summary as
SELECT extract(year from age(current_date-1, t.transaction_date))*12+extract(month from age(current_date-1, t.transaction_date)) as id
    ,usercompanyid, sum(t.base_gross_value) as value
   FROM pltransactionsoverview t
  WHERE t.status::text <> 'P'
  GROUP BY extract(year from age(current_date-1, t.transaction_date))*12+extract(month from age(current_date-1, t.transaction_date))
        ,usercompanyid
   order by 1;

create view sl_aged_creditors_summary as
SELECT extract(year from age(current_date-1, t.transaction_date))*12+extract(month from age(current_date-1, t.transaction_date)) as id
    ,usercompanyid, sum(t.base_gross_value) as value
   FROM sltransactionsoverview t
  WHERE t.status::text <> 'P'
  GROUP BY extract(year from age(current_date-1, t.transaction_date))*12+extract(month from age(current_date-1, t.transaction_date))
        ,usercompanyid
  order by 1;