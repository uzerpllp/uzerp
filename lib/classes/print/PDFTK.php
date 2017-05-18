<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PDFTK {

	protected $version='$Revision: 1.1 $';
	
	public function __construct() {}

	/**
	 * 
	 * 
	 * @param string $file, the file you want to append
	 * @param string $output, the file you want to append to
	 */
	public function append($file,$output) {
		
		// no point in continuing if the file doesn't exist
		if(!file_exists($file)) {
			return FALSE;
		}
		
		// set a few vars
		$command	= 'pdftk %s %s cat output %s';
		$append		= '';
		
		// if the output file exists, we want to start appending
		if(file_exists($output)) {
			$append=$output;
		}
		
		// use a different name to output the file, to prevent conflicting paths (PDFTK feature)
		$output=$output.'.cat';
		
		// generate the command...
		$command=sprintf($command,$append,$file,$output);
		
		// ... and execute it
		exec($command);
		
		// has the output been generated? Note that this condition doesn't actually
		// check if append was successful, just if the output was created successfully 
		
		if(file_exists($output)) {
			
			// file exists... rename it back to what it was
			return rename($output, substr($output, 0, -4));
						
		} else {
			return FALSE;
		}
				
	}
}

?>