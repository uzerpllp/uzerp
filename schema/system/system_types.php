<?php
	$tablename='system_types';
	$fields="id I8 NOTNULL AUTOINCREMENT PRIMARY,
			 system_type C NOTNULL,
			 type_name C NOTNULL,
			 description C NOTNULL,
			 created T NOTNULL DEFAULT NOW(),
			 lastupdated T NOTNULL DEFAULT NOW(),
			 alteredby C,
			 usercompanyid I8 NOTNULL";
	$taboptions = array('constraints'=>',CONSTRAINT system_settings_unique_key UNIQUE (system_type, type_name)');

?>