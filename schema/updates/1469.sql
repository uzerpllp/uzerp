--
-- $Revision: 1.2 $
--

CREATE SEQUENCE bacs_file_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9999;

ALTER TABLE _id_seq OWNER TO "www-data";