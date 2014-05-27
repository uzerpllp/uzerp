--
-- $Revision: 1.1 $
--

update so_header
   set person_id=null
 where person_id=-1;

ALTER TABLE so_header
  ADD CONSTRAINT so_header_person_id_fkey FOREIGN KEY (person_id)
      REFERENCES person (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;

update si_header
   set person_id=null
 where person_id=-1;

ALTER TABLE si_header
  ADD CONSTRAINT si_header_person_id_fkey FOREIGN KEY (person_id)
      REFERENCES person (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION;