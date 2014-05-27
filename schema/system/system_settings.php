<?php
	$tablename='system_settings';
	$fields="id I8 NOTNULL AUTOINCREMENT PRIMARY,
			 setting_type_id I4 NOTNULL,
			 setting_name C NOTNULL,
			 setting_value C NOTNULL,
			 usercompanyid I8 NOTNULL";
	$taboptions = array('constraints'=>',CONSTRAINT system_settings_unique_key UNIQUE (setting_type_id, setting_name)
										,CONSTRAINT system_settings_setting_type_fkey FOREIGN KEY (setting_type_id) REFERENCES system_types (id) ON UPDATE CASCADE');

?>