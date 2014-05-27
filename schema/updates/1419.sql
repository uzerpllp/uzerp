--
-- $Revision: 1.1 $
--


CREATE INDEX so_lines_order_id_idx
  ON so_lines
  USING btree
  (order_id);

CREATE INDEX so_lines_status_idx
  ON so_lines
  USING btree
  (status);
