<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class DFCCONVCartonChecksheet {
	
	protected $version='$Revision: 1.4 $';
	protected $controller;

	function __construct(&$_this) {
		// we're not extending an object, so let's get the callee model (printController) in to access it's methods
		$this->controller=$_this;
	}
	
	function buildReport($MFWorkorders, $data, $bulk=true) {
		
		// set a few vars
		$extra=array();
		
		// construct filler xml to prevent repeating xsl
		$dummy_tables=array('table_1'=>28);
		foreach($dummy_tables as $table => $rows) {
			for($i=0;$i<$rows;$i++) {
				$extra[$table][]['line']='';
			}
		}
		
		// generate the XML, include the extras array too
		$xml=$this->controller->generateXML(array('model'=>$MFWorkorders,'extra'=>$extra));
		
		// build a basic list of options
		$options=array('report'		=> 'MF_DFCCONVCartonChecksheet',
					   'xmlSource'	=> $xml
					  );
		
		return json_decode($this->controller->constructOutput($data,$options));
		
	}
}
?>
