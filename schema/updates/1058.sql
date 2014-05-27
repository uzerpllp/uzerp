--
-- $Revision: 1.3 $
--

--
-- Party not linked to Company or Person
--

select count(*)
  from party p
 where (p.type = 'Company'
	and not exists (select 1
                     from company c
                    where c.party_id = p.id))
    or (p.type = 'Person'
	and not exists (select 1
                     from person c
                    where c.party_id = p.id));

--
-- Party not linked to anything
--

select *
  from party p
 where ((p.type = 'Company'
	and not exists (select 1
                     from company c
                    where c.party_id = p.id))
    or (p.type = 'Person'
	and not exists (select 1
                     from person c
                    where c.party_id = p.id)))
   and not exists (select 1
                     from partyaddress c
                    where c.party_id = p.id)
   and not exists (select 1
                     from party_notes c
                    where c.party_id = p.id)
   and not exists (select 1
                     from party_contact_methods c
                    where c.party_id = p.id);

--
-- Party Address with no Party
--

select count(*)
  from partyaddress c
 where not exists (select 1
                     from party p
                    where c.party_id = p.id);

--
-- Party Notes with no Party
--

select count(*)
  from party_notes c
 where not exists (select 1
                     from party p
                    where c.party_id = p.id);

--
-- Party Contact Methods with no Party
--

select count(*)
  from party_contact_methods c
 where not exists (select 1
                     from party p
                    where c.party_id = p.id);

--
-- Address with no Party Address
--

select count(*)
  from address a
 where not exists (select 1
                     from partyaddress p
                    where p.address_id = a.id);

--
-- Contact Method with no Party Contact Method
--

select count(*)
  from contact_methods cm
 where not exists (select 1
                     from party_contact_methods pcm
                    where pcm.contactmethod_id = cm.id);

--
-- Duplicate Contact Methods where one has a Party Contact Method and the other does not
--

select count(*)
  from contact_methods cm1
  join contact_methods cm2 ON cm1.contact = cm2.contact
                          AND cm1.id != cm2.id
  join party_contact_methods pcm1 ON pcm1.contactmethod_id = cm1.id
 where not exists (select 1
                     from party_contact_methods pcm2
                    where pcm2.contactmethod_id = cm2.id);

--
-- Duplicate Addresses where one has a Party Address and the other does not
--

select count(*)
  from address a1
  join address a2 ON a1.street1 = a2.street1
                 AND a1.town = a2.town
                 AND a1.postcode = a2.postcode
                 AND a1.id != a2.id
  join partyaddress pa1 ON pa1.address_id = a1.id
 where not exists (select 1
                     from partyaddress pa2
                    where pa2.address_id = a2.id);


--
-- Resolve duplicate addresses
--

create table temp_partyaddressoverview as
select min(pao1.address_id) as address_id, pao1.fulladdress
  from partyaddressoverview pao1
 where exists (select 1
                 from partyaddressoverview pao2
                where pao1.fulladdress = pao2.fulladdress
                  and pao1.address_id != pao2.address_id
                  and pao1.id != pao2.id)
 group by pao1.fulladdress;

ALTER TABLE partyaddress ADD COLUMN new_address_id bigint;
ALTER TABLE partyaddress ADD COLUMN old_address_id bigint;

update partyaddress pa
   set old_address_id = address_id
     , new_address_id = (select t.address_id
                           from temp_partyaddressoverview t
                           join partyaddressoverview pao ON pao.id = pa.id
                                                        AND pao.fulladdress = t.fulladdress)
 where exists (select 1
                 from temp_partyaddressoverview t
                 join partyaddressoverview pao ON pao.id = pa.id
                                              AND pao.fulladdress = t.fulladdress);

update partyaddress
   set address_id = new_address_id
 where address_id != new_address_id
   and new_address_id is not null;

delete from address a
 where not exists (select 1
                     from partyaddress p
                    where p.address_id = a.id);

DROP TABLE temp_partyaddressoverview;
ALTER TABLE partyaddress DROP COLUMN new_address_id;
ALTER TABLE partyaddress DROP COLUMN old_address_id;

--
-- resolve duplicate contact_methods
--

create table temp_partycontactmethodoverview as
select min(pcmo1.contactmethod_id) as contactmethod_id, pcmo1.contact
  from partycontactmethodoverview pcmo1
 where exists (select 1
                 from partycontactmethodoverview pcmo2
                where pcmo1.contact = pcmo2.contact
                  and pcmo1.contactmethod_id != pcmo2.contactmethod_id
                  and pcmo1.id != pcmo2.id)
 group by pcmo1.contact;

ALTER TABLE party_contact_methods ADD COLUMN new_contactmethod_id bigint;
ALTER TABLE party_contact_methods ADD COLUMN old_contactmethod_id bigint;

update party_contact_methods pcm
   set old_contactmethod_id = contactmethod_id
     , new_contactmethod_id = (select t.contactmethod_id
                                 from temp_partycontactmethodoverview t
                                 join partycontactmethodoverview pcmo ON pcmo.id = pcm.id
                                                                     AND pcmo.contact = t.contact)
 where exists (select 1
                 from temp_partycontactmethodoverview t
                 join partycontactmethodoverview pcmo ON pcmo.id = pcm.id
                                                     AND pcmo.contact = t.contact);

update party_contact_methods
   set contactmethod_id = new_contactmethod_id
 where contactmethod_id != new_contactmethod_id
   and new_contactmethod_id is not null;

delete from contact_methods cm
 where exists (select 1
                 from party_contact_methods pcm
                where pcm.old_contactmethod_id = cm.id
                  and pcm.contactmethod_id != cm.id);

DROP TABLE temp_partycontactmethodoverview;
ALTER TABLE party_contact_methods DROP COLUMN new_contactmethod_id;
ALTER TABLE party_contact_methods DROP COLUMN old_contactmethod_id;

--
-- Delete party records where no company or person exists
--

select count(*)
  from party p
 where p.parent_id is null
   and ((p.type = 'Company'
	 and not exists (select 1
                           from company c
                          where c.party_id = p.id))
        or (p.type = 'Person'
	    and not exists (select 1
                              from person c
                             where c.party_id = p.id)))
   and not exists (select 1
                     from partyaddress c
                    where c.party_id = p.id)
   and not exists (select 1
                     from party_notes c
                    where c.party_id = p.id)
   and not exists (select 1
                     from party_contact_methods c
                    where c.party_id = p.id);

select count(*)
  from partyaddressoverview pao
  join party p on p.id = pao.party_id
 where p.parent_id is null
   and ((p.type = 'Company'
	 and not exists (select 1
                           from company c
                          where c.party_id = p.id))
        or (p.type = 'Person'
	    and not exists (select 1
                              from person c
                             where c.party_id = p.id)));

select count(*)
  from partycontactmethodoverview pao
  join party p on p.id = pao.party_id
 where p.parent_id is null
   and ((p.type = 'Company'
	 and not exists (select 1
                           from company c
                          where c.party_id = p.id))
        or (p.type = 'Person'
	    and not exists (select 1
                              from person c
                             where c.party_id = p.id)));

select count(*)
  from party_notes pao
  join party p on p.id = pao.party_id
 where p.parent_id is null
   and ((p.type = 'Company'
	 and not exists (select 1
                           from company c
                          where c.party_id = p.id))
        or (p.type = 'Person'
	    and not exists (select 1
                              from person c
                             where c.party_id = p.id)));

delete
  from party p
 where p.parent_id is null
   and ((p.type = 'Company'
	 and not exists (select 1
                           from company c
                          where c.party_id = p.id))
        or (p.type = 'Person'
	    and not exists (select 1
                              from person c
                             where c.party_id = p.id)))
   and not exists (select 1
                     from partyaddress c
                    where c.party_id = p.id)
   and not exists (select 1
                     from party_notes c
                    where c.party_id = p.id)
   and not exists (select 1
                     from party_contact_methods c
                    where c.party_id = p.id);

delete
  from partyaddress c
 where not exists (select 1
                     from party p
                    where c.party_id = p.id);

delete
  from party_notes c
 where not exists (select 1
                     from party p
                    where c.party_id = p.id);

delete
  from party_contact_methods c
 where not exists (select 1
                     from party p
                    where c.party_id = p.id);

delete
  from address a
 where not exists (select 1
                     from partyaddress p
                    where p.address_id = a.id);

delete
  from contact_methods cm
 where not exists (select 1
                     from party_contact_methods pcm
                    where pcm.contactmethod_id = cm.id);

--
-- Set constraints to stop database level cascading delete
--

-- ALTER TABLE partyaddress
--  DROP CONSTRAINT partyaddress_party_id_fkey;
 
ALTER TABLE partyaddress
  ADD CONSTRAINT partyaddress_party_id_fkey FOREIGN KEY (party_id)
      REFERENCES party (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

-- ALTER TABLE party_contact_methods
--  DROP CONSTRAINT party_contact_methods_party_id_fkey;

ALTER TABLE party_contact_methods
  ADD CONSTRAINT party_contact_methods_party_id_fkey FOREIGN KEY (party_id)
      REFERENCES party (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

