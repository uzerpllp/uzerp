<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class MFWorkorder extends DataObject
{

	protected $version='$Revision: 1.19 $';
	
	protected $defaultDisplayFields = array('wo_number'
											,'order_qty'
											,'item_code'
											,'stitem'	=> 'Description'
											,'start_date'
											,'required_by'
											,'made_qty'
											,'status'
											,'stitem_id'
											,'data_sheet_id'
											);
	
	protected $hasAttachmentOutputs = ['tag' => 'workorder', 'name' => 'Work Orders', ];
											
	function __construct($tablename='mf_workorders')
	{
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField='id';
		$this->identifierField='wo_number';
		$this->orderby='required_by';
		$this->orderdir='desc';
		
// Define validation
		
// Define relationships
		$st_filter = new ConstraintChain();
        $st_filter->add(new Constraint('obsolete_date', 'is', 'NULL'));
		$st_filter->add(new Constraint('comp_class', '=', 'M'));

		$this->belongsTo('STItem', 'stitem_id', 'stitem', $st_filter); 
		$this->belongsTo('Project', 'project_id', 'project'); 
		$this->belongsTo('MFDataSheet', 'data_sheet_id', 'data_sheet'); 
		$this->belongsTo('SOrder', 'order_id', 'order_number', null, array('order_number', 'customer', 'person'));
		$this->belongsTo('SOrderLine', 'orderline_id', 'order_line', null, array('line_number', 'description'));
		
		$this->hasMany('MFWOStructure', 'structureitems', 'work_order_id'); 
		$this->hasMany('STTransaction', 'transactions', 'process_id'); 
		$this->hasMany('POrderLine', 'purchases', 'mf_workorders_id');
		
		$this->hasOne('STItem', 'stitem_id', 'stock_item'); 

// Define field formats

// set formatters, more set in load() function

// Define system defaults
		$this->getField('status')->setDefault('N');
				
// Define enumerated types
		$this->setEnum('status',array('N'=>'New'
									 ,'R'=>'Released'
									 ,'O'=>'Open'
									 ,'C'=>'Complete'));
		
// Define link rules including disallowing links
		// disallow adding new structure items and transactions
		$this->linkRules=array('structureitems'=>array('actions'=>array()
													  ,'rules'=>array()
													  ,'label'=>'show structure_items'
											 )
							  ,'transactions'=>array('newtab'=>array('new'=>true)
													,'actions'=>array('link')
													,'rules'=>array()
											 )
							  ,'purchases'=>array('newtab'=>array('new'=>true)
											 ,'actions'=>array('link')
											 ,'rules'=>array()
											 , 'label' => 'Outside OP Purchases'
									  )
							);
									 
	}
	
	static function Factory($data, &$errors = [], $do = null)
	{
		if (!isset($data['id']) || $data['id']=='')
		{
		
			$generator = new MFWorkorderNumberHandler();
			$data['wo_number'] = $generator->handle(new $do);
		
		}
		
		return parent::Factory($data, $errors, $do);

	}

	public static function getAttachmentOutputsDefinition() {
		$model = new self;
		return $model->hasAttachmentOutputs;
	}

	public static function getBalances($field, $type="All")
	{
		$db=&DB::Instance();
		if($field instanceof ConstraintChain)
		{
			$where = $field->__toString()." AND ";
		}
		
		$where .="status!='C' AND order_qty>made_qty";
		
		if ($type=='All')
		{
			$query="SELECT stitem_id
						 , stitem
						 , SUM(order_qty-made_qty) as sumbalance
				  	FROM mf_workordersoverview";
			$groupBy=" GROUP BY stitem_id
						 	  , stitem";
		}
		elseif ($type=='byDate')
		{
			$query="SELECT stitem_id
						 , stitem
						 , required_by
						 , SUM(order_qty-made_qty) as sumbalance
				  	FROM mf_workordersoverview";
			$groupBy=" GROUP BY stitem_id
						 	  , stitem
						 	  , required_by";
		}
		
		$query=$query." WHERE ".$where.$groupBy;
		
		$result=$db->Execute($query);
		
		if (!$result)
		{
			return false;
		}
		else
		{
			return $result->getRows();
		}
	}

	function outstandingQty()
	{
		$osqty=$this->order_qty-$this->made_qty;
		
		if ($osqty<0)
		{
			$osqty=0;
		}
		
		return $osqty;
	}

	function getDocumentList()
	{
		return InjectorClass::unserialize($this->documentation);
	}
	
	function getUomList ($stitem_id)
	{
		$stitem = DataObjectFactory::Factory('STItem');
		
		if ($this->isLoaded() && empty($stitem_id))
		{
			$stitem_id=$this->stitem_id;
		}
		
		if (empty($stitem_id))
		{
			return '';
		}
		else
		{
			$stitem->load($stitem_id);
			return $stitem->getUomList();
		}
	}

	/**
	 * Return a **count** of structure lines fully issued
	 * to this work order and a count of all structure lines
	 * 
	 * @return array  ['issued' => x, 'required' => n]
	 */
	function materialStatus()
	{
		$elements = new MFWOStructureCollection();
		$sh = new SearchHandler($elements, false);
		$sh->addConstraint(new Constraint('work_order_id', '=', $this->id));
		$elements->load($sh);

		$issued = 0;
		foreach ($elements as $mtl) {
			$used = $mtl->getTransactionBalance(TRUE);
			if ($used >= $mtl->qty) {
				$issued += 1;
			}
		}

		$count = (is_countable($mtl)) ? count($mtl) : 0;
		return ['issued' => $issued, 'required' => $count];
	}

}

// End of MFWorkorder
