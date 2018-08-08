<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class GLTransactionCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.12 $';
	
	public $field;
	
	function __construct($do = 'GLTransaction', $tablename = 'gltransactionsoverview')
	{
		parent::__construct($do, $tablename);
		
		$this->orderby = array('glperiod', 'account', 'cost_centre');

	}

	function getVAT ($data, $glperiod_ids, $control_accounts, $sum = false, $paging = false)
	{
		if (count($glperiod_ids) > 0)
		{
			$glperiods = implode(', ', $glperiod_ids);
			
			// Set data source
			switch ($data['box'])
			{
				// Tax inputs	
					case 4:
					case 7:
						$this->_tablename = 'gltransactions_vat_inputs';
						break;
				// Tax outputs
					case 1:
					case 6:
						$this->_tablename = 'gltransactions_vat_outputs';
						break;
				// EU sales
					case 8:
						$this->_tablename = 'gl_taxeusales';
						break;
				// EU purchases
					case 2:
					case 9:
						$this->_tablename = 'gl_taxeupurchases';
						break;
			}
		
		// Set constraints
			$cc = new ConstraintChain;
			
			$cc->add(new Constraint('glperiods_id', ' IN', '('.$glperiods.')'));
			
			switch ($data['box'])
			{
				// VAT output
				case 1:
					$cc->add(new Constraint('glaccount_id', '=', $control_accounts['vat_output']));
					break;
				// EU acquisitions
				case 2:
				case 9:
					$cc->add(new Constraint('glaccount_id', '=', $control_accounts['eu_acquisitions']));
					break;
				// VAT input - also requires value from box 2
				case 4:
					$cc->add(new Constraint('glaccount_id', '=', $control_accounts['vat_input']));
					break;
				// VAT output
				case 6:
				case 8:
					$cc->add(new Constraint('glaccount_id', '=', $control_accounts['vat_output']));
					break;
				// VAT input
				case 7:
					$cc->add(new Constraint('glaccount_id', '=', $control_accounts['vat_input']));
					break;
			}
			
			if (isset($data['page']))
			{
				$sh = new SearchHandler($this, true);
			}
			else
			{
				$sh = new SearchHandler($this, false);
			}
			
			if ($sum)
			{
				$fields = array('1');
				
				$sh->setGroupBy($fields);
				
				$sh->setOrderby($fields);
				
				// Set aggregate field	
				switch ($data['box'])
				{
				// VAT output
					case 1:
						$fields[] = 'SUM(vat) AS sum';
						break;
				// EU acquisitions (positive values only)
					case 2:
				// VAT input/EU acquisitions (positive values only)
					case 4:
						$fields[] = 'SUM(vat) AS sum';
						break;
				// VAT output
					case 6:
						$fields[] = 'SUM(net) AS sum';
						break;
					case 8:
						$fields[] = 'SUM(net) AS sum';
						break;
				// VAT input
					case 7:
					case 9:
						$fields[] = 'SUM(net) AS sum';
						break;
				}
			}
			else
			{
//				$this->num_pages = 1;
//				$this->cur_page = 1;
				$fields = array('id',
								'docref',
								'glaccount_id',
								'glcentre_id',
								'glperiods_id',
								'transaction_date',
								'source',
								'comment',
								'type',
								'usercompanyid'
								);
				switch ($data['box']) {
				// VAT output
					case 1:
					case 6:
					case 8:
						$fields[] = 'vat * -1 AS vat';
						$fields[] = 'net * -1 AS net';
						break;
					default:
						$fields[] = 'vat';
						$fields[] = 'net';
						break;
				}
				
				$sh->setOrderby(array('transaction_date', 'docref'));
				
				if ($paging)
				{
					$sh->extract();
				}
				
			}
			
			$sh->setFields($fields);
			
			$sh->addConstraintChain($cc);
			
			$this->load($sh);
		}
	}
	
}

// End of GLTransactionCollection
