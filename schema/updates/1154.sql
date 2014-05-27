ALTER TABLE party_notes ADD COLUMN note_type character varying;

UPDATE party_notes SET note_type = 'contacts';

ALTER TABLE party_notes ALTER COLUMN note_type SET NOT NULL;

DROP VIEW party_notesoverview;

CREATE OR REPLACE VIEW party_notesoverview AS 
 SELECT n.*
, COALESCE(c.name::text || per.surname::text) AS party
   FROM party_notes n
   JOIN party p ON p.id = n.party_id
   LEFT JOIN company c ON p.id = c.party_id
   LEFT JOIN person per ON p.id = per.party_id;