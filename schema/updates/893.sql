DROP VIEW po_no_auth_user;

DROP VIEW po_auth_requisitions;

CREATE OR REPLACE VIEW po_auth_requisitions AS 
 SELECT h.id, a.order_number, a.username, h.order_date, h.due_date, h.supplier
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
 SELECT h.id, h.order_number, h.order_date, h.due_date, h.supplier
   FROM po_headeroverview h
   LEFT JOIN po_auth_requisitions a ON a.order_number = h.order_number
  WHERE h.type::text = 'R'::text AND a.username IS NULL;
