DROP VIEW po_linesoverview;

CREATE OR REPLACE VIEW po_linesoverview AS 
 SELECT ph.due_date, ph.order_date, ph.order_number, ph.plmaster_id, ph.receive_action, pl.id, pl.order_id, pl.line_number, pl.productline_id, pl.stuom_id, ph.type, ph.net_value AS order_value, pl.item_description, pl.order_qty, pl.price, pl.currency_id, cu.currency, pl.rate, pl.net_value, pl.twin_currency_id, pl.twin_rate, pl.twin_net_value, pl.base_net_value, pl.glcentre_id, (glc.cost_centre::text || ' - '::text) || glc.description::text AS glcentre, pl.glaccount_id, (gla.account::text || ' - '::text) || gla.description::text AS glaccount, pl.line_discount, pl.os_qty, pl.revised_qty, pl.del_qty, pl.due_delivery_date, pl.actual_delivery_date, pl.gr_note, pl.status, ph.status AS order_status, pl.usercompanyid, pl.stitem_id, pl.tax_rate_id, plm.name AS supplier, (i.item_code::text || ' - '::text) || i.description::text AS stitem, u.uom_name
   FROM po_lines pl
   JOIN gl_centres glc ON glc.id = pl.glcentre_id
   JOIN gl_accounts gla ON gla.id = pl.glaccount_id
   JOIN cumaster cu ON cu.id = pl.currency_id
   JOIN po_header ph ON ph.id = pl.order_id
   JOIN plmaster plm ON ph.plmaster_id = plm.id
   LEFT JOIN st_items i ON i.id = pl.stitem_id
   LEFT JOIN st_uoms u ON u.id = pl.stuom_id;


DROP VIEW po_no_auth_user;

DROP VIEW po_auth_requisitions;

CREATE OR REPLACE VIEW po_auth_requisitions AS 
 SELECT h.id, a.order_number, h.status, a.username, h.order_date, h.due_date, h.supplier
   FROM ( SELECT o.order_number, a.username, count(*) AS authlines
           FROM po_linesum o
      JOIN po_authlist a ON a.glaccount_id = o.glaccount_id AND a.glcentre_id = o.glcentre_id
     WHERE o.value <= a.order_limit AND o.type::text = 'R'::text
     GROUP BY o.order_number, a.username) a
   JOIN ( SELECT o.order_number, count(*) AS totallines
           FROM po_linesum o
          GROUP BY o.order_number) b ON a.order_number = b.order_number
   JOIN po_headeroverview h ON h.order_number = a.order_number
  WHERE a.authlines = b.totallines;

CREATE OR REPLACE VIEW po_no_auth_user AS 
 SELECT h.id, h.order_number, h.order_date, h.due_date, h.supplier, h.status
   FROM po_headeroverview h
   LEFT JOIN po_auth_requisitions a ON a.order_number = h.order_number
  WHERE h.type::text = 'R'::text AND a.username IS NULL;