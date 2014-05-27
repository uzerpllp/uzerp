--
-- $Revision: 1.2 $
--

-- Column: payment_address_id

-- ALTER TABLE plmaster DROP COLUMN payment_address_id;

ALTER TABLE plmaster ADD COLUMN payment_address_id bigint;

UPDATE plmaster
   SET payment_address_id = (SELECT address_id
                               FROM partyaddress pa
                               JOIN company c ON c.party_id = pa.party_id
                                             AND c.id = plmaster.company_id
                              WHERE payment IS TRUE
                                AND main IS true);

UPDATE plmaster
   SET payment_address_id = (SELECT address_id
                               FROM partyaddress pa
                               JOIN company c ON c.party_id = pa.party_id
                                             AND c.id = plmaster.company_id
                              WHERE payment IS TRUE
                                AND main IS NOT true
                              LIMIT 1)
 WHERE payment_address_id IS NULL;

UPDATE plmaster
   SET payment_address_id = (SELECT address_id
                               FROM partyaddress pa
                               JOIN company c ON c.party_id = pa.party_id
                                             AND c.id = plmaster.company_id
                              WHERE main IS true)
 WHERE payment_address_id IS NULL;

ALTER TABLE plmaster ALTER COLUMN payment_address_id SET NOT NULL;


-- Column: billing_address_id

-- ALTER TABLE slmaster DROP COLUMN billing_address_id;

ALTER TABLE slmaster ADD COLUMN billing_address_id bigint;

UPDATE slmaster
   SET billing_address_id = (SELECT address_id
                               FROM partyaddress pa
                               JOIN company c ON c.party_id = pa.party_id
                                             AND c.id = slmaster.company_id
                              WHERE billing IS TRUE
                                AND main IS true);

UPDATE slmaster
   SET billing_address_id = (SELECT address_id
                               FROM partyaddress pa
                               JOIN company c ON c.party_id = pa.party_id
                                             AND c.id = slmaster.company_id
                              WHERE billing IS TRUE
                                AND main IS NOT true
                              LIMIT 1)
 WHERE billing_address_id IS NULL;

UPDATE slmaster
   SET billing_address_id = (SELECT address_id
                               FROM partyaddress pa
                               JOIN company c ON c.party_id = pa.party_id
                                             AND c.id = slmaster.company_id
                              WHERE main IS TRUE)
 WHERE billing_address_id IS NULL;

ALTER TABLE slmaster ALTER COLUMN billing_address_id SET NOT NULL;
