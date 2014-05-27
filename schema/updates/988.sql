ALTER TABLE gl_account_centres DROP CONSTRAINT glaccountcentres_centreid_fkey;

ALTER TABLE gl_account_centres
  ADD CONSTRAINT glaccountcentres_centreid_fkey FOREIGN KEY (glcentre_id)
      REFERENCES gl_centres (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE CASCADE;

ALTER TABLE gl_account_centres DROP CONSTRAINT glaccountcentres_glaccountid_fkey;

ALTER TABLE gl_account_centres
  ADD CONSTRAINT glaccountcentres_glaccountid_fkey FOREIGN KEY (glaccount_id)
      REFERENCES gl_accounts (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE CASCADE;