<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class MFStructureReport {

	protected $version='$Revision: 1.11 $';
	protected $controller;
	
	function __construct(&$_this) {
		// we're not extending an object, so let's get the callee model (printController) in to access it's methods
		$this->controller=$_this;
	}
	
	function buildReport($args) { // $MFWorkorders, $data, $bulk=true) {
		
		// specify default args
		$default_args=array(
			'bulk'	=>	TRUE
		);
		
		// build args from merged args + defaults
		$args=array_merge($default_args,$args);
		
		// make sure required items have been set		
		if(!isset($args['model']) || !isset($args['data'])) { return FALSE; }
							
		// set a few vars
		$MFWorkorders=$args['model'];
		$data=$args['data'];
		$bulk=$args['bulk'];
		$model_func=array();
		
		// specify functions we want to execute, value will act like a field
		$model_func['MFWOStructure']=array('requiredQty');
		
		$xml=$this->controller->generateXML(
			array(
				'model'						=>	$MFWorkorders,
				'relationship_whitelist'	=>	array('structureitems','stock_item'),
				'call_model_func'			=>	$model_func
			)
		);
		
		$options=array(
			'report'	=>	'MF_StructureReport',
			'xmlSource'	=>	$xml
		 );
		
		if(isset($args['merge_file_name'])) {
			$options['merge_file_name']=$args['merge_file_name'];
		}
		
		return json_decode($this->controller->generate_output($data,$options));

	}
}
?>