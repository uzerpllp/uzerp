CREATE INDEX po_product_lines_description_comp
  ON po_product_lines
  USING btree
  (description varchar_pattern_ops);

CREATE INDEX so_product_lines_description_comp
  ON so_product_lines
  USING btree
  (description varchar_pattern_ops);