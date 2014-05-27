<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class GLAnalysis extends DataObject {

	protected $version='$Revision: 1.4 $';
	
	protected $defaultDisplayFields=array('analysis'=>'Analysis'
										 ,'summary'=>'Summary');

	function __construct($tablename='gl_analysis') {
		parent::__construct($tablename);
		$this->idField='id';
		$this->identifierField='analysis';
		$this->orderby='analysis';
		
 		$this->belongsTo('GLSummary', 'glsummary_id', 'summary'); 
		$this->validateUniquenessOf('analysis');
	}

}
?>