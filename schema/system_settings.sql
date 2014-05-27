CREATE TABLE system_settings
(
  id serial NOT NULL,
  setting_type_id int4 NOT NULL,
  setting_name varchar NOT NULL,
  setting_value varchar NOT NULL,
  usercompanyid int8 NOT NULL,
  CONSTRAINT system_settings_pkey PRIMARY KEY (id),
  CONSTRAINT system_settings_setting_type_fkey FOREIGN KEY (setting_type_id)
      REFERENCES system_types (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE NO ACTION,
  CONSTRAINT system_settings_unique_key UNIQUE (setting_type_id, setting_name)
);

/*
$tablename='system_settings';
$fields="  id I8 NOTNULL AUTOINCREMENT PRIMARY',
  setting_type_id I4 NOTNULL CONSTRAINTS 'FOREIGN KEY REFERENCES system_types',
  setting_name C NOTNULL,
  setting_value C NOTNULL,
  usercompanyid I8 NOTNULL";
$taboptions = array('constraints'=>'CONSTRAINT system_settings_unique_key UNIQUE (setting_type_id, setting_name)');

*/