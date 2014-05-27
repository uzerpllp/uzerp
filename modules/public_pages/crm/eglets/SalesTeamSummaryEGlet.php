<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

abstract class SalesTeamSummaryEGlet extends SimpleGraphEGlet{
	
	protected $version='$Revision: 1.5 $';
	
	function populate() {
		
		if(!isModuleAdmin()) {
			$flash = Flash::Instance();
			$flash->addError('You don\'t have permission to view the Sales Team summary EGlets');
			$this->should_render=false;
			return false;
		}
		$db=&DB::Instance();

		$query = 'SELECT s.id,s.name FROM opportunitystatus s WHERE usercompanyid='.$db->qstr(EGS_COMPANY_ID).' ORDER BY position DESC';
		$statuses = $db->GetAssoc($query);
		
		$query = 'SELECT DISTINCT assigned FROM opportunities o WHERE o.usercompanyid='.EGS_COMPANY_ID.' AND extract(\''.$this->timeframe.'\' FROM o.enddate)=extract(\''.$this->timeframe.'\' FROM now())';
		$users = $db->GetCol($query);

		$options = array();
		
		foreach ($users as $username) {
			
			if (empty($username)) {
				continue;
			}
			
			$data = array();
			
			foreach	($statuses as $id => $status) {
				
				$query = 'SELECT COALESCE(sum(cost),0) FROM opportunities o WHERE o.assigned='.$db->qstr($username).' AND o.status_id='.$db->qstr($id).' AND o.usercompanyid='.EGS_COMPANY_ID.' AND extract(\''.$this->timeframe.'\' FROM o.enddate)=extract(\''.$this->timeframe.'\' FROM now())';
				
				$data['x'][] = $status;
				$data['y'][] = (float) $db->GetOne($query);
				
			}
			
			$options['seriesList'][] = array(
				'label'			=> $username,
				'legendEntry'	=> TRUE,
				'data'			=> $data
			);
			
		}
		
		if (!isset($options['seriesList']) || empty($options['seriesList'])) {
			return false;
		}
		
		$options['type']		= 'bar';
		$options['identifier']	= __CLASS__ . $this->timeframe;
		
		$this->contents = json_encode($options);	
		
	}
	
}

// end of SalesTeamSummaryEGlet.php