DROP VIEW sl_over_creditlimit_overview;

CREATE OR REPLACE VIEW sl_over_creditlimit_overview AS 
 SELECT sl.id, sl.company_id, sl.name, sl.outstanding_balance, c.creditlimit, sl.usercompanyid, sum(so.base_net_value) AS outstanding_orders
   FROM slmaster sl
   LEFT JOIN company c ON c.id = sl.company_id
   LEFT JOIN so_header so ON so.slmaster_id = sl.id AND so.despatch_date < now() AND (so.status::text = ANY (ARRAY['N'::character varying, 'O'::character varying, 'P'::character varying]::text[])) AND so.type::text = 'O'::text
  WHERE sl.outstanding_balance > c.creditlimit::numeric
  GROUP BY sl.name, sl.id, sl.company_id, sl.outstanding_balance, c.creditlimit, sl.usercompanyid;
