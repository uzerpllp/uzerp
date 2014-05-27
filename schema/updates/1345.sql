--
-- $Revision: 1.1 $
--
  
ALTER TABLE si_lines ADD COLUMN tax_rate_percent numeric;

DROP VIEW si_linesoverview;

CREATE OR REPLACE VIEW si_linesoverview AS 
 SELECT sl.*
 , sh.invoice_date, sh.invoice_number, sh.transaction_type, sh.slmaster_id
 , soh.order_number
 , c.name AS customer
 , i.item_code, (i.item_code::text || ' - '::text) || i.description::text AS stitem
 , uom.uom_name
   FROM si_lines sl
   JOIN si_header sh ON sh.id = sl.invoice_id
   JOIN slmaster slm ON sh.slmaster_id = slm.id
   JOIN company c ON slm.company_id = c.id
   LEFT JOIN so_header soh ON sl.sales_order_id = soh.id
   LEFT JOIN st_uoms uom ON sl.stuom_id = uom.id
   LEFT JOIN st_items i ON i.id = sl.stitem_id;

update si_lines
   set tax_rate_percent=(
select 
  case when h.invoice_date between '2008-12-01' and '2009-12-31'
       then 15
       when h.invoice_date > '2011-01-03'
       then 20
       else 17.5
  end
  from si_lines l
  join si_header h on h.id = l.invoice_id
  where l.gross_value!=0
    and l.id = si_lines.id
  );