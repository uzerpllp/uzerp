--
-- $Revision: 1.2 $
--

ALTER TABLE periodic_payments ADD COLUMN glaccount_centre_id integer;

ALTER TABLE pi_lines ADD COLUMN glaccount_centre_id integer;

ALTER TABLE po_lines ADD COLUMN glaccount_centre_id integer;

ALTER TABLE si_lines ADD COLUMN glaccount_centre_id integer;

ALTER TABLE so_lines ADD COLUMN glaccount_centre_id integer;

ALTER TABLE cumaster
  ADD CONSTRAINT cumaster_glaccount_id_fkey FOREIGN KEY (writeoff_glaccount_id)
      REFERENCES gl_accounts (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE gl_account_centres
  ADD CONSTRAINT glaccountcentres_uk1 UNIQUE(glaccount_id, glcentre_id);

ALTER TABLE cb_accounts
  ADD CONSTRAINT cbaccount_glaccount_centre_id_fkey FOREIGN KEY (glaccount_id, glcentre_id)
      REFERENCES gl_account_centres (glaccount_id, glcentre_id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE cumaster
  ADD CONSTRAINT cumaster_glaccount_centre_id_fkey FOREIGN KEY (writeoff_glaccount_id, glcentre_id)
      REFERENCES gl_account_centres (glaccount_id, glcentre_id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE periodic_payments
  ADD CONSTRAINT periodic_payments_glaccount_centre_id_fkey FOREIGN KEY (glaccount_centre_id)
      REFERENCES gl_account_centres (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE pi_lines
  ADD CONSTRAINT pi_lines_glaccount_centre_id_fkey FOREIGN KEY (glaccount_centre_id)
      REFERENCES gl_account_centres (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE po_lines
  ADD CONSTRAINT po_lines_glaccount_centre_id_fkey FOREIGN KEY (glaccount_centre_id)
      REFERENCES gl_account_centres (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE si_lines
  ADD CONSTRAINT si_lines_glaccount_centre_id_fkey FOREIGN KEY (glaccount_centre_id)
      REFERENCES gl_account_centres (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE so_lines
  ADD CONSTRAINT so_lines_glaccount_centre_id_fkey FOREIGN KEY (glaccount_centre_id)
      REFERENCES gl_account_centres (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE wh_locations
  ADD CONSTRAINT wh_locations_glaccount_centre_id_fkey FOREIGN KEY (glaccount_id, glcentre_id)
      REFERENCES gl_account_centres (glaccount_id, glcentre_id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

UPDATE periodic_payments
   SET glaccount_centre_id = (SELECT id
                                FROM gl_account_centres ac
                               WHERE ac.glaccount_id = periodic_payments.glaccount_id
                                 AND ac.glcentre_id = periodic_payments.glcentre_id)
 WHERE status = 'A'
   AND source != 'PP'
   AND source != 'SR';

UPDATE periodic_payments
   SET glaccount_id = null
     , glcentre_id = null
 WHERE source IN ('PP', 'SR');

UPDATE po_lines
   SET glaccount_centre_id = (SELECT id
                                FROM gl_account_centres ac
                               WHERE ac.glaccount_id = po_lines.glaccount_id
                                 AND ac.glcentre_id = po_lines.glcentre_id)
 WHERE status not in ('X','I');

UPDATE pi_lines
   SET glaccount_centre_id = (SELECT id
                                FROM gl_account_centres ac
                               WHERE ac.glaccount_id = pi_lines.glaccount_id
                                 AND ac.glcentre_id = pi_lines.glcentre_id)
 WHERE exists (SELECT id
                 FROM pi_header ph
                WHERE ph.id = pi_lines.invoice_id
                  AND ph.status = 'N');

UPDATE so_lines
   SET glaccount_centre_id = (SELECT id
                                FROM gl_account_centres ac
                               WHERE ac.glaccount_id = so_lines.glaccount_id
                                 AND ac.glcentre_id = so_lines.glcentre_id)
 WHERE status not in ('X','I');

UPDATE si_lines
   SET glaccount_centre_id = (SELECT id
                                FROM gl_account_centres ac
                               WHERE ac.glaccount_id = si_lines.glaccount_id
                                 AND ac.glcentre_id = si_lines.glcentre_id)
 WHERE exists (SELECT id
                 FROM si_header sh
                WHERE sh.id = si_lines.invoice_id
                  AND sh.status = 'N');
