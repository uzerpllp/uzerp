<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class UniqueNumberHandler extends AutoHandler {

	protected $version='$Revision: 1.1 $';
	
	public function __construct($onupdate=false, $ascending=true) {
		parent::__construct($onupdate);
		
		$this->function	= ($ascending)?'max':'min';
		$this->counter	= ($ascending)?'+1':'-1';
		
	}

	function handle(DataObject $model) {
		
		$jn = $model->identifierField;
		
		$unique	= $model->checkUniqueness($jn);
		
		$value	= $model->getIdentifierValue();
		
		if(!empty($jn) && $unique && empty($value))
		{
			$db		= DB::Instance();
			
			$query	= 'SELECT '.$this->function.'('.$jn.') FROM '.
						$model->getTableName().
						' WHERE usercompanyid='.EGS_COMPANY_ID;
			
			$current = $db->GetOne($query);
			
//			$current = ($current==0)?$this->counter:$current;
			
			return bcadd($current, $this->counter, 0);
		
		}
		
	}
	
}

// End of UniqueNumberHandler
