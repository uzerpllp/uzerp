ALTER TABLE pi_header DROP CONSTRAINT pi_header_plmaster_id_fkey;

ALTER TABLE pi_header
  ADD CONSTRAINT pi_header_plmaster_id_fkey FOREIGN KEY (plmaster_id)
      REFERENCES plmaster (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

ALTER TABLE po_header DROP CONSTRAINT po_header_plmaster_id_fkey;

ALTER TABLE po_header
  ADD CONSTRAINT po_header_plmaster_id_fkey FOREIGN KEY (plmaster_id)
      REFERENCES plmaster (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

ALTER TABLE pltransactions DROP CONSTRAINT pltransactions_plmaster_id_fkey;

ALTER TABLE pltransactions
  ADD CONSTRAINT pltransactions_plmaster_id_fkey FOREIGN KEY (plmaster_id)
      REFERENCES plmaster (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

ALTER TABLE po_receivedlines DROP CONSTRAINT po_receivedlines_plmaster_id_fkey;

ALTER TABLE po_receivedlines
  ADD CONSTRAINT po_receivedlines_plmaster_id_fkey FOREIGN KEY (plmaster_id)
      REFERENCES plmaster (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

ALTER TABLE po_product_lines DROP CONSTRAINT po_product_lines_plmaster_id_fkey;

ALTER TABLE po_product_lines
  ADD CONSTRAINT po_product_lines_plmaster_id_fkey FOREIGN KEY (plmaster_id)
      REFERENCES plmaster (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

ALTER TABLE si_header DROP CONSTRAINT si_header_slmaster_id_fkey;

ALTER TABLE si_header
  ADD CONSTRAINT si_header_slmaster_id_fkey FOREIGN KEY (slmaster_id)
      REFERENCES slmaster (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

ALTER TABLE so_header DROP CONSTRAINT so_header_slmaster_id_fkey;

ALTER TABLE so_header
  ADD CONSTRAINT so_header_slmaster_id_fkey FOREIGN KEY (slmaster_id)
      REFERENCES slmaster (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

ALTER TABLE sltransactions DROP CONSTRAINT sltransactions_slmaster_id_fkey;

ALTER TABLE sltransactions
  ADD CONSTRAINT sltransactions_slmaster_id_fkey FOREIGN KEY (slmaster_id)
      REFERENCES slmaster (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

ALTER TABLE so_despatchlines DROP CONSTRAINT so_despatchlines_slmaster_id_fkey;

ALTER TABLE so_despatchlines
  ADD CONSTRAINT so_despatchlines_slmaster_id_fkey FOREIGN KEY (slmaster_id)
      REFERENCES slmaster (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

ALTER TABLE so_product_lines DROP CONSTRAINT so_product_lines_slmaster_id_fkey;

ALTER TABLE so_product_lines
  ADD CONSTRAINT so_product_lines_slmaster_id_fkey FOREIGN KEY (slmaster_id)
      REFERENCES slmaster (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;