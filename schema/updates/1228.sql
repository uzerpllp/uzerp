DROP VIEW so_itemorders;

DROP VIEW so_headeroverview;

CREATE OR REPLACE VIEW so_headeroverview AS 
 SELECT so.id, so.order_number, so.slmaster_id, so.del_address_id, so.order_date, so.due_date
 , so.despatch_date, so.ext_reference, so.currency_id, so.rate, so.net_value, so.twin_currency_id
 , so.twin_rate, so.twin_net_value, so.base_net_value, so.type, so.status, so.description
 , so.usercompanyid, so.despatch_action, so.inv_address_id, so.created, so.createdby, so.alteredby
 , so.lastupdated, so.person_id
 , slm.account_status
 , c.name AS customer
 , cum.currency
 , twc.currency AS twin_currency
 , p.firstname||' '||p.surname as person
   FROM so_header so
   JOIN slmaster slm ON so.slmaster_id = slm.id
   JOIN company c ON slm.company_id = c.id
   LEFT JOIN person p ON so.person_id = p.id
   JOIN cumaster cum ON so.currency_id = cum.id
   JOIN cumaster twc ON so.twin_currency_id = twc.id;

CREATE OR REPLACE VIEW so_itemorders AS 
 SELECT sl.id, sl.order_id, sl.line_number, sl.productline_id, sl.stuom_id, sl.item_description, sl.order_qty, sl.price, sl.currency_id, sl.rate, sl.net_value, sl.twin_currency_id, sl.twin_rate, sl.twin_net_value, sl.base_net_value, sl.glaccount_id, sl.glcentre_id, sl.line_discount, sl.os_qty, sl.revised_qty, sl.del_qty, sl.due_delivery_date, sl.due_despatch_date, sl.actual_despatch_date, sl.delivery_note, sl.status, sl.usercompanyid, sl.stitem_id, sl.tax_rate_id, sl.created, sl.createdby, sl.alteredby, sl.lastupdated, sl.line_value, sl.line_tradedisc_percentage, sl.line_qtydisc_percentage, sh.despatch_date, sh.customer, sh.order_number, sh.slmaster_id, sh.type, sh.account_status, sh.despatch_action, (i.item_code::text || ' - '::text) || i.description::text AS stitem, sl.revised_qty AS required, u.uom_name AS stuom
   FROM so_lines sl
   JOIN st_items i ON i.id = sl.stitem_id
   JOIN st_uoms u ON u.id = sl.stuom_id
   JOIN so_headeroverview sh ON sh.id = sl.order_id
  WHERE sl.stitem_id IS NOT NULL AND (sl.status::text = ANY (ARRAY['N'::character varying, 'R'::character varying]::text[]));

DROP VIEW si_headeroverview;

CREATE OR REPLACE VIEW si_headeroverview AS 
 SELECT si.*
 , c.name AS customer
 , cum.currency
 , twc.currency AS twin
 , syt.description AS payment_terms
 , ts.description AS tax_status
 , slm.sl_analysis_id
 , sla.name
 , p.firstname||' '||p.surname as person
   FROM si_header si
   JOIN slmaster slm ON si.slmaster_id = slm.id
   JOIN company c ON slm.company_id = c.id
   LEFT JOIN person p ON si.person_id = p.id
   LEFT JOIN sl_analysis sla ON slm.sl_analysis_id = sla.id
   JOIN cumaster cum ON si.currency_id = cum.id
   JOIN cumaster twc ON si.twin_currency_id = twc.id
   JOIN tax_statuses ts ON si.tax_status_id = ts.id
   JOIN syterms syt ON si.payment_term_id = syt.id;

DROP VIEW pi_linesoverview;

CREATE OR REPLACE VIEW pi_linesoverview AS 
 SELECT pl.id, pl.invoice_id, pl.line_number, pl.purchase_order_id, pl.order_line_id, pl.stitem_id
 , pl.item_description, pl.purchase_qty, pl.purchase_price, pl.currency_id, pl.rate, pl.gross_value
 , pl.tax_value, pl.tax_rate_id, pl.net_value, pl.twin_currency_id, pl.twin_rate, pl.twin_gross_value
 , pl.twin_tax_value, pl.twin_net_value, pl.base_gross_value, pl.base_tax_value, pl.base_net_value
 , pl.glaccount_id, pl.glcentre_id, pl.job, pl.description, pl.delivery_note, pl.usercompanyid
 , pl.created, pl.createdby, pl.alteredby, pl.lastupdated
 , ph.invoice_date, ph.invoice_number, ph.transaction_type, ph.plmaster_id
 , poh.order_number
 , c.name AS supplier
 , i.item_code, (i.item_code::text || ' - '::text) || i.description::text AS stitem
   FROM pi_lines pl
   JOIN pi_header ph ON ph.id = pl.invoice_id
   JOIN plmaster plm ON ph.plmaster_id = plm.id
   JOIN company c ON plm.company_id = c.id
   LEFT JOIN po_header poh ON pl.purchase_order_id = poh.id
   LEFT JOIN st_items i ON i.id = pl.stitem_id;