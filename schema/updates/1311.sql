CREATE OR REPLACE VIEW tax_eu_despatches AS 
 SELECT sod.id, sod.despatch_date, sod.despatch_qty, sol.item_description, sod.invoice_number, c.name AS customer, soh.order_number, uom.uom_name, sod.usercompanyid
   FROM so_despatchlines sod
   JOIN so_header soh ON soh.id = sod.order_id
   JOIN so_lines sol ON sol.id = sod.orderline_id
   JOIN slmaster slm ON slm.id = sod.slmaster_id
   JOIN company c ON slm.company_id = c.id
   JOIN tax_statuses tst ON tst.id = slm.tax_status_id AND tst.eu_tax = true
   LEFT JOIN st_uoms uom ON sod.stuom_id = uom.id
  WHERE sod.status::text = 'D'::text
  ORDER BY sod.despatch_date, c.name, sol.item_description;
