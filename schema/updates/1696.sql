--
-- $Revision: 1.4 $
--

CREATE OR REPLACE VIEW po_auth_summary AS 
 SELECT h.id, h.order_number, h.status, h.type, a.username, h.order_date, h.due_date, h.payee_name, h.supplier
   FROM ( SELECT o1.order_id, pa.username, count(*) AS authlines
           FROM ( SELECT po_lines.order_id, po_lines.glaccount_id, po_lines.glcentre_id, sum(po_lines.base_net_value) AS value
                   FROM po_lines
                  GROUP BY po_lines.order_id, po_lines.glaccount_id, po_lines.glcentre_id) o1
      JOIN po_authlist pa ON pa.glaccount_id = o1.glaccount_id AND pa.glcentre_id = o1.glcentre_id
     WHERE o1.value <= pa.order_limit
     GROUP BY o1.order_id, pa.username) a
   JOIN ( SELECT o2.order_id, count(*) AS totallines
           FROM ( SELECT po_lines.order_id, po_lines.glaccount_id, po_lines.glcentre_id
                   FROM po_lines
                  GROUP BY po_lines.order_id, po_lines.glaccount_id, po_lines.glcentre_id) o2
          GROUP BY o2.order_id) b ON a.order_id = b.order_id AND a.authlines = b.totallines
   JOIN po_headeroverview h ON h.id = a.order_id;
   
ALTER TABLE po_auth_summary OWNER TO "www-data";