DROP VIEW so_itemorders;

DROP VIEW so_headeroverview;

CREATE OR REPLACE VIEW so_headeroverview AS 
 SELECT so.*
 , slm.account_status, slm.name AS customer, cum.currency, twc.currency AS twin_currency
   FROM so_header so
   JOIN slmaster slm ON so.slmaster_id = slm.id
   JOIN cumaster cum ON so.currency_id = cum.id
   JOIN cumaster twc ON so.twin_currency_id = twc.id;

CREATE OR REPLACE VIEW so_itemorders AS 
 SELECT sl.*
 , sh.despatch_date, sh.customer, sh.order_number, sh.slmaster_id, sh.type, sh.account_status
 , sh.despatch_action
 , (i.item_code::text || ' - '::text) || i.description::text AS stitem
 , sl.revised_qty AS required
 , u.uom_name AS stuom
   FROM so_lines sl
   JOIN st_items i ON i.id = sl.stitem_id
   JOIN st_uoms u ON u.id = sl.stuom_id
   JOIN so_headeroverview sh ON sh.id = sl.order_id
  WHERE sl.stitem_id IS NOT NULL AND (sl.status::text = ANY (ARRAY['N'::character varying, 'R'::character varying]::text[]));