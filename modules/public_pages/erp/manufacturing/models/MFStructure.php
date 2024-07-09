<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class MFStructure extends DataObject
{

	protected $version = '$Revision: 1.10 $';

	protected $defaultDisplayFields = array('line_no'
											,'ststructure'	=>'stock item'
											,'start_date'
											,'end_date'
											,'qty'			=>'quantity'
											,'uom'
											,'waste_pc'		=>'waste %'
											);

	function __construct($tablename = 'mf_structures')
	{
	    $this->orderby = ['line_no'];

		// Register non-persistent attributes
	    $this->setAdditional('latest_cost', 'numeric');
	    $this->setAdditional('std_cost', 'numeric');

		// Contruct the object
		parent::__construct($tablename);

		// Set specific characteristics
		$this->idField			= 'id';
		$this->identifierField	= 'stitem_id';

 		// Define relationships
 		$this->belongsTo('STuom', 'uom_id', 'uom');
		$this->belongsTo('STItem', 'stitem_id', 'stitem');
		$this->belongsTo('STItem', 'ststructure_id', 'ststructure');
		$this->hasOne('STItem', 'ststructure_id', 'ststr_item');

		// Define field formats

		// Define Validation
		$this->validateUniquenessOf(array('stitem_id', 'line_no', 'start_date'));

		// set formatters, more set in load() function

		// Define enumerated types

		// Define link rules for related items
	}

	function cb_loaded()
	{
	    $this->latest_cost = add(
	        $this->latest_mat,
	        $this->latest_lab,
	        $this->latest_ohd,
	        $this->latest_osc
	        );

	    $this->std_cost = add(
	        $this->std_mat,
	        $this->std_lab,
	        $this->std_ohd,
	        $this->std_osc
	        );
	}

	public function isActive()
	{
		if (!$this->end_date)
		{
			return true;
		}

		$timestamp = mktime(0, 0, 0, date('m'), date('d'), date('Y'));

		return (strtotime($this->end_date) >= $timestamp);
	}

	public function getSubstitute()
	{
		$cc = new ConstraintChain;

		$cc->add(new Constraint('line_no', '=', $this->line_no));
		$cc->add(new Constraint('stitem_id', '=', $this->stitem_id));

		$date = $this->end_date;

		if (!$this->end_date)
		{
			$date = date('Y-m-d');
		}

		$cc->add(new Constraint('start_date', '>', $date));

		$substitute = DataObjectFactory::Factory('MFStructure');

		return $substitute->loadBy($cc);
	}

	public function getRequirement ($qty=1)
	{
		return bcmul(round($this->qty*$qty*100/(100-$this->waste_pc),$this->ststr_item->qty_decimals),1,$this->ststr_item->qty_decimals);
	}

	public static function globalRollOver()
	{
		$db = DB::Instance();

		$date = date('Y-m-d');

		$query = "UPDATE mf_structures
					SET std_cost=latest_cost,std_mat=latest_mat,std_lab=latest_lab,std_osc=latest_osc,std_ohd=latest_ohd
					WHERE (start_date <= '".$date."' OR start_date IS NULL) AND (end_date > '".$date."' OR end_date IS NULL) AND usercompanyid=".EGS_COMPANY_ID;

		return ($db->Execute($query) !== false);
	}

	static function backflush($data, $elements, &$errors = [])
	{

		$db = DB::Instance();
		//$db->StartTrans();
		$success = true;
		// Get the required action for the Item if enabled on Item Product Type
		$stitem = DataObjectFactory::Factory('STItem');

		if ($stitem->load($data['stitem_id']))
		{
// TODO: need a check to ensure that there is only one transfer rule for action
			$staction=$stitem->getAction('backflush');

			if (!is_null($staction))
			{
// Get the transfer rule for the action
				$data['whaction_id']=$staction;

				$transrule = DataObjectFactory::Factory('WHTransferrule');

				$cc = new ConstraintChain();

				$cc->add(new Constraint('whaction_id','=', $staction));

				$transrule->loadBy($cc);

				foreach ($elements as $structure)
				{
// Insert transaction pair for WIP Update
					$data['from_whlocation_id']	= $transrule->from_whlocation_id;
					$data['to_whlocation_id']	= $transrule->to_whlocation_id;
// Currently cannot define bins for backflushing
					$data['from_whbin_id']	= '';
					$data['to_whbin_id']	= '';

// Calculate the quantity used of the sub-component
					$data['qty']		= $data['book_qty']*$structure->qty*100/(100-$structure->waste_pc);
					$data['stitem_id']	= $structure->ststructure_id;
					// Status is initially OK
					$data['status'] = 'O';

					$stitem = DataObjectFactory::Factory('STItem');

					if ($stitem->load($structure->ststructure_id))
					{
						$converted = $stitem->convertToUoM($structure->uom_id
														,$stitem->uom_id
														,$data['qty']);

						if ($converted === false)
						{
							$errors[] = 'UoM Conversion Error on item '.$stitem->item_code.' - '.$stitem->description;
						}
						else
						{
// Validate the data
							$data['qty'] = round($converted, $stitem->qty_decimals);

							$models = STTransaction::prepareMove($data, $errors);
						}
					}
					else
					{
						$errors[] = 'Cannot find structure Item';
					}
// If errors occurred creating transaction pair then exit loop
					if (count($errors) > 0)
					{
						$success = false;
						break;
					}
					// Save transaction pair
					$success = true;

					$db->StartTrans();

					foreach ($models as $model)
					{
						$ignored_errors = array();
						// If transaction was saved then go to next transaction
						if ($model->save($ignored_errors) !== false)
						{
							continue;
						}
						// Error saving transaction
						$success = false;
						$db->FailTrans();
						break;
					}

					$db->CompleteTrans();

					// If transactions were saved then go to next transaction pair
					if ($success)
					{
						continue;
					}

					// Save transaction pair with error status
					$success = true;

					$db->StartTrans();

					foreach ($models as $model)
					{
						$model->error_qty	= $model->qty;
						$model->qty			= 0;
						$model->status		= 'E';

						// If transaction was saved then go to next transaction
						if ($model->save($errors) !== false)
						{
							continue;
						}

						// Error saving transaction
						$success = false;
						$db->FailTrans();
						break;
					}

					$db->CompleteTrans();

					// If errors occurred saving transaction pairs then exit loop
					if (!$success)
					{
						break;
					}
				}
			}
		}
		else
		{
			$errors[] = 'Cannot find stock item';
			$success = false;
		}
		return $success;
		//return $db->completeTrans();
	}

	public function getBalance ()
	{
		return $this->ststr_item->balance;
	}

	public function getCurrentBalance ()
	{
		return $this->ststr_item->currentBalance();
	}
}

// End of MFStructure

