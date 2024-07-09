<?php

/** 
 *	(c) 2022 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class MFWOStructure extends DataObject {
	
	protected $defaultDisplayFields = array('line_no'
											,'wo_number'=>'Works Order'
											,'ststructure'=>'Stock Item'
											,'qty'=>'quantity'
											,'uom'
											,'waste_pc'=>'waste_%'
											,'status'
											);

	protected $workorder;
	
	function __construct($tablename='mf_wo_structures') {
// Register non-persistent attributes
		
// Construct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField='id';
		$this->identifierField='ststructure_id';
		
		$this->orderby='line_no';
		
// Define validation
		
// Define relationships
		$this->validateUniquenessOf(array('work_order_id', 'line_no'));
		$this->belongsTo('STuom', 'uom_id', 'uom');
		$this->belongsTo('MFWorkorder', 'work_order_id', 'wo_number');
		$this->belongsTo('STItem', 'ststructure_id', 'ststructure');
		
		$this->hasOne('STItem', 'ststructure_id', 'ststr_item');
		 
// set formatters, more set in load() function
		
// Define field formats

// Define system defaults
		$this->getField('waste_pc')->setDefault('0');
				
// Define enumerated types
		
// Define link rules including disallowing links
	
	}

	/**
	 * Recursively explode phantom item structure
	 *
	 * @param MFStructure $phantom  Phantom MFStructure
	 * @param array $items  MFStructure lines to return
	 * @return array MFStructure  Children of the phantom
	 */
	public static function explodePhantom($phantom, &$items=[]){
		$phantom = MFStructureCollection::getCurrent($phantom->ststructure_id);
		foreach($phantom as $item) {
			if ($item->comp_class == 'P') {
				self::explodePhantom($item, $items);
			} else {
				$items[] = $item;
			}
		}
		return $items;
	}

	/**
	 * Get an array of MFWOStructure objects.
	 * 
	 * Phantoms are exploded, only children are copied.
	 * Top level items are copied as-is.
	 * Work Order structure line numbers start at 10
	 * and are incremented by 10.
	 *
	 * @param MFWorkOrder $data
	 * @param mixed $errors
	 * @return Array array of MFWOStructure objects
	 */
	public static function copyStructure($data, &$errors = []) {
		$mfstructures =  MFStructureCollection::getCurrent($data->stitem_id);;
		$wo_structure = [];
		$wo_structures = [];
		$copyfields = ['qty', 'uom_id' ,'remarks' ,'waste_pc','ststructure_id'];
		$line_no = '10';

		foreach($mfstructures as $input) {
			$wo_structure['work_order_id']=$data->id;
			
			if ($input->comp_class == 'P') {
				$phantom_items = self::explodePhantom($input);
				foreach ($phantom_items as $pitem) {
					$wo_structure['line_no'] = $line_no;
					foreach ($copyfields as $field) {
						$wo_structure[$field] = $pitem->$field;
					}
					$wo_structure['qty'] = $input->qty * $pitem->qty;
					$wo_structures[$line_no]=DataObject::Factory($wo_structure, $errors, 'MFWOStructure');
					$line_no = $line_no + 10;
				}

			} else {
				// Copy top level items
				foreach ($copyfields as $field) {
					$wo_structure['line_no'] = $line_no;
					$wo_structure[$field]=$input->$field;
				}
				$wo_structures[$line_no]=DataObject::Factory($wo_structure, $errors, 'MFWOStructure');
				$line_no = $line_no + 10;
			}
		}
		return $wo_structures;
	}

	public static function exists($work_order_id) {
		$db=&DB::Instance();
		$query="SELECT count(*) as st
				  FROM mf_wo_structures
				WHERE work_order_id=".$work_order_id;
		$result=$db->Execute($query);
		if ($result->fetchObj()->st == 0){
			return false;
		} else {
			return true;
		}
	}

	function getCurrentBalance($whlocation_id='', $whbin_id='') {
		$currentBalance=0;
		$stitem = DataObjectFactory::Factory('STITem');
		$stitem->load($this->ststructure_id);
		if ($stitem) {
			if (!empty($whlocation_id)) {
				$balance = DataObjectFactory::Factory('STBalance');
				$cc=new ConstraintChain();
				$cc->add(new Constraint('stitem_id', '=', $this->ststructure_id));
				$cc->add(new Constraint('whlocation_id', '=', $whlocation_id));
				$cc->add(new Constraint('supply_demand', 'is', 'TRUE'));
				if (!empty($whbin_id)) {
					$cc->add(new Constraint('whbin_id', '=', $whbin_id));
				}
				$balance->loadBy($cc);
				if ($balance) {
					$currentBalance=$balance->balance;
				}
			} else {
				$currentBalance=$stitem->currentBalance();
			}
			if ($this->uom_id<>$stitem->uom_id) {
				return round($stitem->convertToUoM($stitem->uom_id, $this->uom_id, $currentBalance),$stitem->qty_decimals);
			} else {
				return $currentBalance;			
			}
		}
		return 0;

	}

	function getTransactionBalance($_has_balance='') {

		$sttrans = DataObjectFactory::Factory('STTransaction');
		
		$cc = new ConstraintChain();
		
		$location = DataObjectFactory::Factory('WHLocation');
		if (($_has_balance === TRUE) || ($_has_balance === FALSE))
		{
			$cc->add(new Constraint('has_balance', 'is', $_has_balance));
		}
		$locations = $location->getAll($cc);
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('stitem_id', '=', $this->ststructure_id));
		$cc->add(new Constraint('process_name', '=', 'WO'));
		$cc->add(new Constraint('process_id', '=', $this->work_order_id));
		$cc->add(new Constraint('qty', '>', 0));
		if (count($locations)>0)
		{
			$cc->add(new Constraint('whlocation_id', 'in', '('.implode(',', array_keys($locations)).')'));
		}
		
		$issued_value = $sttrans->getSum('qty', $cc);
		
		if ($issued_value>0)
		{
			$ststructure = DataObjectFactory::Factory('STITem');
			if ($ststructure->load($this->ststructure_id))
			{
				if ($this->uom_id<>$ststructure->uom_id)
				{
					$issued_value = round($ststructure->convertToUoM($ststructure->uom_id, $this->uom_id, $issued_value),$ststructure->qty_decimals);
				}
			}
		}
		
		return $issued_value;
		
	}
	
	function getWorkorder() {
		$this->workorder = DataObjectFactory::Factory('MFWorkorder');
		$this->workorder->load($this->work_order_id);
	}
	
	function requiredQty() {
		if (!$this->workorder) {
			$this->getWorkorder();
		}
		if ($this->workorder) {
			return round($this->qty*$this->workorder->order_qty*100/(100-$this->waste_pc),$this->ststr_item->qty_decimals);
		} else {
			return 0;
		}
	}

	function outstandingQty() {
		if (!$this->workorder) {
			$this->getWorkorder();
		}
		if ($this->workorder) {
			$osqty=$this->workorder->order_qty-$this->workorder->made_qty;
			if ($osqty<0) {
				$osqty=0;
			}
			return $this->qty*$osqty*100/(100-$this->waste_pc);
		} else {
			return 0;
		}
	}

	function madeQty() {
		if (!$this->workorder) {
			$this->getWorkorder();
		}
		if ($this->workorder) {
			return round($this->qty*$this->workorder->made_qty*100/(100-$this->waste_pc),$this->ststr_item->qty_decimals);
		} else {
			return 0;
		}
	}

} 

// End of MFWOStructure
