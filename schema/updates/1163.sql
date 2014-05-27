-- partyaddress

CREATE INDEX partyaddress_address_id
  ON partyaddress
  USING btree
  (address_id);

CREATE INDEX partyaddress_party_id
  ON partyaddress
  USING btree
  (party_id);

-- so_product_lines

CREATE INDEX so_product_lines_prod_group_id_idx
  ON so_product_lines
  USING btree
  (prod_group_id);

CREATE INDEX so_product_lines_slmaster_id_idx
  ON so_product_lines
  USING btree
  (slmaster_id);

CREATE INDEX so_product_lines_so_price_type_id_idx
  ON so_product_lines
  USING btree
  (so_price_type_id);

CREATE INDEX so_product_lines_stitem_id_idx
  ON so_product_lines
  USING btree
  (stitem_id);