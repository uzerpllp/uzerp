CREATE OR REPLACE VIEW tax_eu_arrivals AS 
  SELECT por.id, por.received_date, por.received_qty, por.item_description, por.delivery_note, por.invoice_number
, plm.name AS supplier, poh.order_number
, uom.uom_name, por.usercompanyid
   FROM po_receivedlines por
   JOIN po_header poh ON poh.id = por.order_id
   JOIN plmaster plm ON plm.id = por.plmaster_id
   JOIN tax_statuses tst ON tst.id = plm.tax_status_id
                        AND tst.eu_tax = true
   LEFT JOIN st_uoms uom ON por.stuom_id = uom.id
 ORDER BY por.received_date, plm.name, por.item_description;

CREATE OR REPLACE VIEW tax_eu_despatches AS 
 SELECT sod.id, sod.despatch_date, sod.despatch_qty, sol.item_description, sod.invoice_number
, slm.name AS customer, soh.order_number
, uom.uom_name, sod.usercompanyid
   FROM so_despatchlines sod
   JOIN so_header soh ON soh.id = sod.order_id
   JOIN so_lines sol ON sol.id = sod.orderline_id
   JOIN slmaster slm ON slm.id = sod.slmaster_id
   JOIN tax_statuses tst ON tst.id = slm.tax_status_id
                        AND tst.eu_tax = true
   LEFT JOIN st_uoms uom ON sod.stuom_id = uom.id
 ORDER BY sod.despatch_date, slm.name, sol.item_description;

CREATE OR REPLACE VIEW tax_eu_saleslist AS 
 SELECT si.id, si.invoice_number, si.sales_order_number, si.invoice_date, si.transaction_type, si.ext_reference
, si.currency_id, si.rate, si.settlement_discount, si.gross_value, si.tax_value, si.net_value
, si.twin_currency_id AS twin_currency, si.twin_rate, si.twin_gross_value, si.twin_tax_value
, si.twin_net_value, si.base_gross_value, si.base_tax_value, si.base_net_value, si.payment_term_id
, si.due_date, si.status, si.description, si.tax_status_id, si.delivery_note, si.despatch_date
, si.date_printed, si.print_count, si.usercompanyid, slm.name AS customer, cum.currency, twc.currency AS twin
, syt.description AS payment_terms, ts.description AS tax_status, coy.vatnumber as vat_number, cad.countrycode as country
   FROM si_header si
   JOIN slmaster slm ON si.slmaster_id = slm.id
   JOIN company coy ON slm.company_id = coy.id
   JOIN companyaddress cad ON coy.party_id = cad.party_id
   LEFT JOIN sl_analysis sla ON slm.sl_analysis_id = sla.id
   JOIN cumaster cum ON si.currency_id = cum.id
   JOIN cumaster twc ON si.twin_currency_id = twc.id
   JOIN tax_statuses ts ON si.tax_status_id = ts.id
   JOIN syterms syt ON si.payment_term_id = syt.id
  WHERE ts.eu_tax = true;

