<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PSQL_CSV {

	protected $version = '$Revision: 1.2 $';

	public function go($query, $params)
	{
	
		// create temp file for CSV
		$tmpcsv = tempnam('/tmp', 'CSV');
		chmod($tmpcsv, 0777);
		
		$header = "";
		
		if ($params['fieldnames'] === 'on')
		{
			$header = "HEADER";
		}
		
		// basic sql structure
		$sql = "psql -d %s -c \"\copy (%s) TO '%s' WITH DELIMITER '%s' CSV %s\" 2>&1";
		
		// populate sql structure with values
		$sql = sprintf(
			$sql,
			get_config('DB_NAME'),
			$query,
			$tmpcsv,
			$params['fieldseparater'],
			$header
		);
						
		// execute the SQL command
		exec($sql);
				
		// return the csv filepath
		return $tmpcsv;
	
	}
	
}

// end of PSQL_CSV.php