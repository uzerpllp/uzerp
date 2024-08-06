<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class LoggedHoursPerWeekEGlet extends SimpleGraphEGlet
{

	protected $timeframe='week';

	function populate() {
		self::checkSetup();

		$db=&DB::Instance();

		$query = 'SELECT p.name, to_char(sum(h.duration),\'HH\')::float+(to_char(sum(h.duration),\'MI\')::float/60) AS hours 
				FROM hours h JOIN projects p ON (h.project_id=p.id) 
				WHERE h.owner = '.$db->qstr(EGS_USERNAME).
				' AND h.start_time > (now() - interval \'7 days\') GROUP BY p.name';

		$results = $db->GetAssoc($query);

		if ($results)
		{
			foreach	($results as $name => $hours)
			{

				$query = 'SELECT COALESCE(sum(cost),0) FROM opportunities o WHERE o.assigned='.$db->qstr('').' AND o.status_id='.$db->qstr('').' AND o.usercompanyid='.EGS_COMPANY_ID.' AND extract(\''.$this->timeframe.'\' FROM o.enddate)=extract(\''.$this->timeframe.'\' FROM now())';

				$data['x'][] = $name;
				$data['y'][] = (float) $hours;

			}
		}

		$options['seriesList'][] = array(
			'label'			=> '',
			'legendEntry'	=> FALSE,
			'data'			=> $data
		);

		$options['type']		= 'bar';
		$options['identifier']	= __CLASS__;

		$this->contents = json_encode($options);

	}

}

// end of LoggedHoursPerWeekEGlet.php