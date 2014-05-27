DROP VIEW po_structure_lines;

CREATE OR REPLACE VIEW po_structure_lines AS 
 SELECT pl.id, ph.due_date, ph.order_number, ph.plmaster_id, ph.receive_action, pl.order_id, pl.line_number
, pl.productline_id, pl.stuom_id, pl.item_description, pl.order_qty, pl.price, pl.currency_id, pl.rate, pl.net_value
, pl.twin_currency_id, pl.twin_rate, pl.twin_net_value, pl.base_net_value, pl.glaccount_id, pl.glcentre_id
, pl.line_discount, pl.os_qty, pl.revised_qty, pl.del_qty, pl.due_delivery_date, pl.actual_delivery_date, pl.gr_note
, pl.status, pl.usercompanyid, pl.stitem_id, pl.tax_rate_id, plm.name AS supplier
, (i.item_code::text || ' - '::text) || i.description::text AS stitem, u.uom_name
, i.item_code, st.ststructure_id, st.qty, st.qty * pl.os_qty::double precision AS required
   FROM po_lines pl
   JOIN po_header ph ON ph.id = pl.order_id
   JOIN plmaster plm ON ph.plmaster_id = plm.id
   JOIN mf_structures st ON st.stitem_id = pl.stitem_id
			AND (pl.due_delivery_date>=st.start_date AND (pl.due_delivery_date<=st.end_date or st.end_date is null))
   JOIN st_items i ON i.id = st.stitem_id
   JOIN st_uoms u ON pl.stuom_id = u.id;
