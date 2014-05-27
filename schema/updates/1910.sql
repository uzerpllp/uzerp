--
-- $Revision: 1.2 $
--

-- Table: mf_workorders

-- Column: order_number

DROP VIEW mf_workordersoverview;

ALTER TABLE mf_workorders DROP COLUMN order_no;

-- Column: order_id

ALTER TABLE mf_workorders ADD COLUMN order_id integer;

ALTER TABLE mf_workorders
  ADD CONSTRAINT mf_workorders_order_id_fkey FOREIGN KEY (order_id)
      REFERENCES so_header (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

-- Column: order_line

ALTER TABLE mf_workorders RENAME COLUMN order_line TO orderline_id;

ALTER TABLE mf_workorders
  ADD CONSTRAINT mf_workorders_orderline_id_fkey FOREIGN KEY (orderline_id)
      REFERENCES so_lines (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

-- View: mf_workordersoverview

CREATE OR REPLACE VIEW mf_workordersoverview AS 
 SELECT w.*
 , s.item_code || ' - ' || s.description AS stitem, s.item_code, s.type_code_id
 , soh.order_number, soh.customer, soh.person
 , sol.line_number, sol.description
   FROM mf_workorders w
   JOIN st_items s ON w.stitem_id = s.id
   LEFT JOIN so_headeroverview soh ON w.order_id = soh.id
   LEFT JOIN so_lines sol ON w.orderline_id = sol.id;

ALTER TABLE mf_workordersoverview OWNER TO "www-data";