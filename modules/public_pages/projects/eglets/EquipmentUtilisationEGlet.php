<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class EquipmentUtilisationEGlet extends SimpleEGlet
{

	protected $version='$Revision: 1.3 $';

	protected $template='status_list_eglet.tpl';
	protected $limit=10;
	protected $contents = array();

	function populate()
	{
		$db = DB::Instance();

		$query = "
			SELECT
				pe.name,
				pe.id,
				pe.red,
				pe.amber,
				pe.green,
				pe.usable_hours,
				pe.available,
				SUM(EXTRACT(HOURS FROM t.duration)) AS total
			FROM
				tasks t
			LEFT JOIN
				projects p ON (t.project_id = p.id)
			LEFT JOIN
				project_equipment pe ON (t.equipment_id = pe.id)
			WHERE
				p.usercompanyid = " . $db->qstr(EGS_COMPANY_ID) . "
			AND
				(t.start_date > NOW())
			AND
				(t.start_date < (NOW() + '30 days'::interval))
			AND
				t.equipment_id IS NOT NULL
			GROUP BY
				pe.id,
				pe.name,
				pe.red,
				pe.amber,
				pe.green,
				pe.usable_hours,
				pe.available
	;";

		$results = $db->GetAssoc($query);

		if ($results)
		{

			foreach ($results as $name => $result)
			{

				$result['name'] = $name;

				$utilisation = $result['total'] / ($result['usable_hours'] * 30); // 30 days of periods
//		var_dump(array($utilisation, $result['red'], $result['amber'], $result['green']));
				if ($utilisation >= ($result['red']/100))
				{
					$colour = 'red';
				}
				elseif ($utilisation >= ($result['amber']/100))
				{
					$colour = 'amber';
				}
				elseif ($utilisation >= ($result['green']/100))
				{
					$colour = 'green';
				}
				else
				{
					$colour = 'disabled';
				}

				$this->contents[] = array(
					'id' => $result['id'],
					'name' => $result['name'],
					'colour' => $colour,
					'disabled' => ($result['available'] == 't' ? false : true)
				);

			}

		}

		if(!empty($this->contents))
		{
			$this->contents=array_slice($this->contents,0,$this->limit);
		}

	}

	function setData($the_data)
	{
		$this->contents=$the_data;
	}

	function setLimit($limit)
	{
		$this->limit=$limit;
	}

}

// End of EquipmentUtilisationEGlet
