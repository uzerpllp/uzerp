<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class STItem extends DataObject
{

	protected $version='$Revision: 1.45 $';

    protected $defaultDisplayFields = [
        'item_code',
        'description',
        'product_group' => 'Product Group',
        'type_code' => 'Type Code',
        'alpha_code',
        'comp_class',
        'abc_class',
        'ref1',
        'balance',
        'uom_name',
        'latest_cost',
        'std_cost',
        'prod_group_id',
        'type_code_id',
        'uom_id'
    ];

    protected $hidden = [
        'cost_basis' => 1
    ];

//	protected $parent;
//	protected $parent_structure;
	protected $parents;
	protected $parent_structures;
	protected $polines;
	protected $postructures;
	protected $solines;
	protected $worders;
	protected $wostructures;
	protected $children;
	protected $child_structures;
	protected $operations;
	protected $outside_ops;
	protected $linkRules;

	const ROLL_UP_MAX_LEVEL = 5;

	public function __construct($tablename='st_items')
	{

		// Register non-persistent attributes

		// Contruct the object
		parent::__construct($tablename);

		// Set specific characteristics
		$this->idField = 'id';

		$this->identifierField = array('item_code', 'description');
		$this->orderby = 'item_code';
		$this->validateUniquenessOf('item_code');

		// Define relationships
		$this->belongsTo('STProductgroup', 'prod_group_id', 'product_group');
		$this->belongsTo('STTypecode', 'type_code_id', 'type_code');
		$this->belongsTo('STuom', 'uom_id', 'uom_name');
		$this->belongsTo('TaxRate', 'tax_rate_id', 'tax_rate');

		$this->hasMany('STBalance','balances','stitem_id');
		$this->hasMany('STTransaction','transactions','stitem_id');
		$this->hasMany('STuomconversion','uom_conversions','stitem_id');
		$this->hasMany('MFStructure','structures','stitem_id');
		$this->hasMany('MFStructure','where_used','ststructure_id');
		$this->hasMany('MFOperation','operations','stitem_id');
		$this->hasMany('MFOutsideOperation','outside_operations','stitem_id');
		$this->hasMany('MFWorkorder','workorders','stitem_id');
		$this->hasMany('POrderLine','purchase_orders','stitem_id');
		$this->hasMany('PInvoiceLine','purchase_invoices','stitem_id');
		$this->hasMany('SOrderLine','sales_orders','stitem_id');
		$this->hasMany('SInvoiceLine','sales_invoices','stitem_id');
		$this->hasMany('MFWOStructure','wo_structures','ststructure_id');
		$this->hasMany('POProductLine','po_product_prices','stitem_id');
		$this->hasMany('SOProductLine','so_product_prices','stitem_id');
		$this->hasMany('POProductLineHeader','po_products','stitem_id');
		$this->hasMany('SOProductLineHeader','so_products','stitem_id');

		// Define field formats

		// set formatters, more set in load() function

		// Define enumerated types
		$this->setEnum('comp_class', array('B' => 'Bought In',
										   'K' => 'Sales Kit',
										   'M' => 'Manufactured',
										   'S' => 'Sub-Contracted'));
		$this->setEnum('abc_class', array( 'A' => 'A',
                                           'B'=>'B',
                                           'C'=>'C'));
		$this->setEnum('cost_basis', [
		    'VOLUME' => 'Volume',
		    'TIME' => 'Time'
		]);

		// Define link rules for related items
		$this->linkRules=array('balances'=>array('actions'=>array('link')
											 ,'rules'=>array())
						,'transactions'=>array('actions'=>array('link')
											   ,'rules'=>array())
						,'structures'=>array('actions'=>array('link','new')
											 ,'rules'=>array(array('field'=>'comp_class', 'criteria'=>"!='B'"))
											 )
						,'operations'=>array('actions'=>array('link','new')
											 ,'rules'=>array(array('field'=>'comp_class', 'criteria'=>"=='M'"))
											 )
						,'outside_operations'=>array('actions'=>array('link','new')
													,'rules'=>array(array('field'=>'comp_class', 'criteria'=>"=='M'")
																   ,array('field'=>'comp_class', 'criteria'=>"=='S'", 'logical'=>'||'))
													)
						,'workorders'=>array('actions'=>array('link','new')
											 ,'rules'=>array(array('field'=>'comp_class', 'criteria'=>"=='M'")
											 				,array('field'=>'latest_cost', 'criteria'=>'>0'))
											 )
						,'purchase_orders'=>array('actions'=>array('link')
												 ,'rules'=>array(array('field'=>'relatedCount("purchase_orders")', 'criteria'=>'>0'))
												 )
						,'purchase_invoices'=>array('actions'=>array('link')
												   ,'rules'=>array(array('field'=>'relatedCount("purchase_invoices")', 'criteria'=>'>0'))
												   )
						,'sales_orders'=>array('actions'=>array('link')
											  ,'rules'=>array(array('field'=>'relatedCount("sales_orders")', 'criteria'=>'>0'))
											  )
						,'sales_invoices'=>array('actions'=>array('link')
												,'rules'=>array(array('field'=>'relatedCount("sales_invoices")', 'criteria'=>'>0'))
												)
						,'po_product_prices'=>array('newtab'=>array('new'=>true)
											 ,'modules'=>array('new'=>array('module'=>'purchase_order'))
											 ,'actions'=>array('link','new')
											 ,'rules'=>array(array('field'=>'comp_class', 'criteria'=>"!='M'")
											 				,array('field'=>'comp_class', 'criteria'=>"!='K'")
											 				,array('field'=>'relatedCount("po_products")', 'criteria'=>'>0'))
											 )
   						,'so_product_prices'=>array('newtab'=>array('new'=>true)
											 ,'modules'=>array('new'=>array('module'=>'sales_order'))
											 ,'actions'=>array('link','new')
											 ,'rules'=>array(array('field'=>'relatedCount("so_products")', 'criteria'=>'>0'))
											 )
						,'po_products'=>array('newtab'=>array('new'=>true)
											 ,'modules'=>array('new'=>array('module'=>'purchase_order'))
											 ,'actions'=>array('new')
											 ,'rules'=>array(array('field'=>'comp_class', 'criteria'=>"!='M'")
											 				,array('field'=>'comp_class', 'criteria'=>"!='K'")
											 				,array('field'=>'relatedCount("po_products")', 'criteria'=>'==0'))
											 )
   						,'so_products'=>array('newtab'=>array('new'=>true)
											 ,'modules'=>array('new'=>array('module'=>'sales_order'))
											 ,'actions'=>array('new')
											 ,'rules'=>array(array('field'=>'relatedCount("so_products")', 'criteria'=>'==0'))
											 )
											 // Do not allow links to the following
						,'wo_structures'=>array('actions'=>array()
											   ,'rules'=>array()
											   )
						);

	}

	public function convertToUoM($fromUom, $toUom, $qty)
	{
		if ($fromUom == $toUom)
		{
			return $qty;
		}

		$STuomconversion = DataObjectFactory::Factory('STuomconversion');

		$converted = $STuomconversion->convertFrom($this->id
												, $fromUom
												, $toUom
												, $qty);

		if ($converted === false)
		{
			$SYuomconversion = DataObjectFactory::Factory('SYuomconversion');

			$converted = $SYuomconversion->convertFrom($fromUom
												, $toUom
												, $qty);
		}

		return $converted;
	}

	public function convertUomByName($quantity=1, $from_uom_name='', $to_uom_name='')
	{
		if (empty($from_uom_name) && empty($to_uom_name))
		{
			return $quantity;
		}

		if (!empty($from_uom_name))
		{
			$from_uom = DataObjectFactory::Factory('STuom');

			$from_uom->loadBy('uom_name', $from_uom_name);

			if (!$from_uom->isLoaded())
			{
				return $quantity;
			}

			$from_uom_id = $from_uom->id;
		}
		else
		{
			$from_uom_id = $this->uom_id;
		}

		if (!empty($to_uom_name))
		{
			$to_uom = DataObjectFactory::Factory('STuom');

			$to_uom->loadBy('uom_name', $to_uom_name);

			if (!$to_uom->isLoaded())
			{
				return $quantity;
			}

			$to_uom_id = $to_uom->id;
		}
		else
		{
			$to_uom_id = $this->uom_id;
		}

		return $this->convertToUoM($from_uom_id, $to_uom_id, $quantity);

	}

	public function getAction($type)
	{
		$typecode = DataObjectFactory::Factory('STTypecode');

		$typecode->load($this->type_code_id);

		$action_name = $type.'_action_id';

		return $typecode->{$action_name};
	}

	public function isSaleable()
	{
		$saleable=new SOProductlineCollection();

		$sh = new SearchHandler($saleable, false);

		$sh->addConstraint(new Constraint('stitem_id','=',$this->id));

		$saleable->load($sh);

		if (count($saleable)>0)
		{
			return true;
		}

		return false;

	}

	public static function nonObsoleteItems($date = null, $comp_class = null, $stitem_id = null)
	{
		if (!$date)
		{
			$date = Constraint::TODAY;
		}
		elseif (is_int($date))
		{
			$db = DB::Instance();
			$date = $db->DBDate($date);
		}

		$cc = new ConstraintChain;

		if (!is_null($stitem_id))
		{
			if (is_array($stitem_id))
			{
				$cc->add(new Constraint('id', 'in', '('.implode(',', $stitem_id).')'));
			}
			else
			{
				$cc->add(new Constraint('id', '=', $stitem_id));
			}
		}

		$cc1 = new ConstraintChain;

		$cc2 = new ConstraintChain;

		$cc2->add(new Constraint('obsolete_date', '=', 'NULL'));
		$cc2->add(new Constraint('obsolete_date', '>', $date), 'OR');

		$cc3 = new ConstraintChain;
		$cc3->add(new Constraint('latest_cost', '>', 0));

		if ($comp_class)
		{
			$cc3->add(new Constraint('comp_class', '=', $comp_class));
		}

		$cc1->add($cc2);

		$cc1->add($cc3);

		$cc->add($cc1);

		$stitem = DataObjectFactory::Factory('STItem');

		return $stitem->getAll($cc);
	}

	public function isObsolete()
	{
		if (!$this->obsolete_date)
		{
			return false;
		}

		return (strtotime($this->obsolete_date) <= time());
	}

	public function calcLatestCost()
	{
		switch ($this->comp_class) {
			case 'K':
				$var[] = $this->calcLatestMat();
				break;
			case 'M':
			case 'S':
				$var[] = $this->calcLatestMat();
				$var[] = $this->calcLatestLab();
				$var[] = $this->calcLatestOhd();
				$var[] = $this->calcLatestOsc();

				// if any of the previous 4 functions return an error...
				if(in_array("-1", $var)) {
					return false;
				}
//				$child_structures = $this->getChildStructures();
//				$operations = $this->getOperations();
//				foreach ($child_structures as $child_structure) {
//					$child_structure->latest_cost = $child_structure->latest_mat + $child_structure->latest_lab + $child_structure->latest_osc + $child_structure->latest_ohd;
//				}
//				foreach ($operations as $operation) {
//					$operation->latest_cost = $operation->latest_lab + $operation->latest_ohd;
//				}
				break;
			case 'B':
				$this->latest_lab = 0;
				$this->latest_osc = 0;
				$this->latest_ohd = 0;
				break;
		}
		$this->latest_cost = $this->latest_mat + $this->latest_lab + $this->latest_osc + $this->latest_ohd;
		//echo '<p>', $this->description, ' - latest cost: ', $this->latest_cost, '</p>';
		return $this->latest_cost;
	}

	protected function calcLatestMat()
	{
		$this->latest_mat = 0;

		$children = $this->getChildren();

		$child_structures = $this->getChildStructures();

		if (count($children) != count($child_structures))
		{
			return $this->latest_mat;
		}

		foreach ($children as $key => $child)
		{
			$child_structure = $child_structures[$key];

			$uom = $child->convertToUoM($child_structure->uom_id, $child->uom_id, $child->latest_mat);

			$cost = $uom * $child_structure->qty;

			$cost *= (100 / (100 - $child_structure->waste_pc));

			$child_structure->latest_mat = round($cost, $this->cost_decimals);
			$this->latest_mat += $child_structure->latest_mat;
		}

		return $this->latest_mat;
	}

	protected function calcLatestLab()
	{
		$this->latest_lab = 0;

		$children = $this->getChildren();

		$child_structures = $this->getChildStructures();

		if (count($children) != count($child_structures))
		{
			return $this->latest_lab;
		}

		$operations = $this->getOperations();

		foreach ($operations as $operation)
		{
			$mfresource = DataObjectFactory::Factory('MFResource');

			if (!$mfresource->load($operation->mfresource_id))
			{
				continue;
			}

			if ($this->cost_basis == 'VOLUME') {
    			// if values are NULL or 0
    			if(is_null($operation->uptime_target) || $operation->uptime_target<=0
    				|| is_null($operation->quality_target) || $operation->quality_target<=0
    				|| is_null($operation->volume_target) || $operation->volume_target<=0)
    			{
    					return -1;
    			}

    			$cost = $mfresource->resource_rate * $operation->resource_qty;

    			switch ($operation->volume_period) {
    				case 'S':
    					$cost /= 3600;
    					//echo ' / 3600';
    					break;
    				case 'M':
    					$cost /= 60;
    					//echo ' / 60';
    					break;
    			}

    			$cost *= (100 / $operation->uptime_target);

				$cost /= $operation->volume_target;
				
				// Divide by batch size before unit-of-measure conversion.
			    // Batch size is in the item's UOM.
			    if ($operation->type == 'B' && (!is_null($this->batch_size) || $this->batch_size > 0)) {
			        $cost /= $this->batch_size;
			    }

    			$uom = $this->convertToUoM($this->uom_id, $operation->volume_uom_id, $cost);

    			$cost = $uom;

    			$cost *= (100 / $operation->quality_target);

    			$operation->latest_lab = round($cost, $this->cost_decimals);

    			$this->latest_lab = add($this->latest_lab, $operation->latest_lab);
			} else {
			    // Time based calculation
			    if(is_null($operation->volume_target) || $operation->volume_target <= 0) {
			        return -1;
			    }

			    $operation_time = $operation->volume_target;
			    $cost = $mfresource->resource_rate * $operation->resource_qty;
			    $cost *= $operation_time;

			    switch ($operation->volume_period) {
			        case 'S':
			            $cost /= 3600;
			            break;
			        case 'M':
			            $cost /= 60;
			            break;
			    }

			    // Divide by batch size before unit-of-measure conversion.
			    // Batch size is in the item's UOM.
			    if ($operation->type == 'B' && (!is_null($this->batch_size) || $this->batch_size > 0)) {
			        $cost /= $this->batch_size;
			    }

			    $uom = $this->convertToUoM($this->uom_id, $operation->volume_uom_id, $cost);
			    $cost = $uom;

			    $operation->latest_lab = round($cost, $this->cost_decimals);
			    $this->latest_lab = add($this->latest_lab, $operation->latest_lab);
			}
		}

		foreach ($children as $key => $child)
		{
			$child_structure = $child_structures[$key];

			$uom = $child->convertToUoM($child_structure->uom_id, $child->uom_id, $child->latest_lab);

			$cost = $uom * $child_structure->qty;

			$child_structure->latest_lab = round($cost, $this->cost_decimals);

			$this->latest_lab = add($this->latest_lab,$child_structure->latest_lab);
		}

		return $this->latest_lab;
	}

	public function calcLatestOhd()
	{
		$this->latest_ohd = 0;

		$children = $this->getChildren();

		$child_structures = $this->getChildStructures();

		if (count($children) != count($child_structures))
		{
			return $this->latest_ohd;
		}

		$operations = $this->getOperations();

		$mfcentre = DataObjectFactory::Factory('MFCentre');

		foreach ($operations as $operation)
		{
			if (!$mfcentre->load($operation->mfcentre_id))
			{
				continue;
			}

			if ($this->cost_basis == 'VOLUME') {
    			if(is_null($operation->uptime_target) || $operation->uptime_target<=0
    				|| is_null($operation->quality_target) || $operation->quality_target<=0
    				|| is_null($operation->volume_target) || $operation->volume_target<=0)
    			{
    					return -1;
    			}

    			$cost = $mfcentre->centre_rate;

    			switch ($operation->volume_period) {
    				case 'S':
    					$cost /= 3600;
    					//echo ' / 3600';
    					break;
    				case 'M':
    					$cost /= 60;
    					//echo ' / 60';
    					break;
    			}

    			$cost *= (100 / $operation->uptime_target);

    			$cost /= $operation->volume_target;

				// Per order ops - e.g. setup a machine
			    if ($operation->type == 'B' && (!is_null($this->batch_size) || $this->batch_size > 0)) {
			        $cost /= $this->batch_size;
			    }

    			$uom = $this->convertToUoM($this->uom_id, $operation->volume_uom_id, $cost);
    			$cost = $uom;

    			$cost *= (100 / $operation->quality_target);

    			$operation->latest_ohd = round($cost, $this->cost_decimals);
    			$this->latest_ohd += $operation->latest_ohd;
			} else {
			    // Time based costing
			    if(is_null($operation->volume_target) || $operation->volume_target <= 0) {
			        return -1;
			    }

			    $operation_time = $operation->volume_target;
			    $cost = $mfcentre->centre_rate * $operation_time;

			    switch ($operation->volume_period) {
			        case 'S':
			            $cost /= 3600;
			            //echo ' / 3600';
			            break;
			        case 'M':
			            $cost /= 60;
			            //echo ' / 60';
			            break;
			    }

				// Per order ops - e.g. setup a machine
			    if ($operation->type == 'B' && (!is_null($this->batch_size) || $this->batch_size > 0)) {
			        $cost /= $this->batch_size;
			    }

			    $uom = $this->convertToUoM($this->uom_id, $operation->volume_uom_id, $cost);
			    $cost = $uom;

			    $operation->latest_ohd = round($cost, $this->cost_decimals);
			    $this->latest_ohd += $operation->latest_ohd;
			}
		}

		foreach ($children as $key => $child)
		{
			$child_structure = $child_structures[$key];
			$uom = $child->convertToUoM($child_structure->uom_id, $child->uom_id,$child->latest_ohd);

			$cost = $uom * $child_structure->qty;

			$child_structure->latest_ohd = round($cost, $this->cost_decimals);
			$this->latest_ohd += $child_structure->latest_ohd;
		}

		return $this->latest_ohd;
	}

	protected function calcLatestOsc() {
		$this->latest_osc = 0;

		$children = $this->getChildren();

		$child_structures = $this->getChildStructures();

		if (count($children) != count($child_structures))
		{
			return $this->latest_osc;
		}

		$outside_ops = $this->getOutsideOperations();
		$routing_outside_ops = $this->getOperations('O');

		foreach ($outside_ops as $outside_op)
		{

			$outside_op->latest_osc = round($outside_op->latest_osc, $this->cost_decimals);

			$this->latest_osc += $outside_op->latest_osc;
		}

		foreach ($routing_outside_ops as $outside_op)
		{

			$outside_op->latest_osc = round($outside_op->outside_processing_cost, $this->cost_decimals);

			$this->latest_osc += $outside_op->latest_osc;
		}

		foreach ($children as $key => $child)
		{
			$child_structure = $child_structures[$key];
			$uom = $child->convertToUoM($child_structure->uom_id, $child->uom_id, $child->latest_osc);

			$cost = $uom * $child_structure->qty;

			$child_structure->latest_osc = round($cost, $this->cost_decimals);
			$this->latest_osc += $child_structure->latest_osc;
		}

		return $this->latest_osc;
	}

	public function currentBalance()
	{
		$balance = 0;

		foreach ($this->balances as $stbalance)
		{
			if ($stbalance->supply_demand=='t')
			{
				$balance = bcadd($stbalance->balance,$balance,$this->qty_decimals);
			}
		}

		return $balance;
	}

	/**
	 * Stock available to Sell
	 *
	 * @return number pickable total balance
	 */
	public function pickableBalance()
	{
	    $balance = 0;

	    foreach ($this->balances as $stbalance)
	    {
	        if ($stbalance->pickable=='t')
	        {
	            $balance = bcadd($stbalance->balance,$balance,$this->qty_decimals);
	        }
	    }

	    return $balance;
	}

	public function getTreeArray($max_depth = -1)
	{

		static $depth = 0;

		static $stitems = array();

		if (($this->comp_class == 'B') || ($depth == $max_depth)) {
			return array();
		}

		$depth++;

		$cc = new ConstraintChain;

		$cc->add(new Constraint('stitem_id', '=', $this->id));

		$db = DB::Instance();

		$date = Constraint::TODAY;

		$between = $date.' BETWEEN '.$db->IfNull('start_date', $date).' AND '.$db->IfNull('end_date', $date);
		$cc->add(new Constraint('', '', '('.$between.')'));

		$mfstructure = DataObjectFactory::Factory('MFStructure');

		$parts = $mfstructure->getAll($cc);

		$array = array();

		foreach ($parts as $part_id => $stitem_id)
		{
			$mfstructure = DataObjectFactory::Factory('MFStructure');

			$mfstructure->load($part_id);

			$ststructure_id = $mfstructure->ststructure_id;

			if (!array_key_exists($ststructure_id, $stitems))
			{
				$stitem = DataObjectFactory::Factory('STItem');

				if (!$stitem->load($ststructure_id))
				{
					continue;
				}

				$stitems[$ststructure_id] = $stitem;
			}

			$array[$ststructure_id] = $stitems[$ststructure_id]->getTreeArray($max_depth);
		}

		$depth--;

		if ($depth == 0)
		{
			$stitems = array();

			return array($this->id => $array);
		}

		return $array;
	}

	public function getParents()
	{
		if ($this->parents)
		{
			return $this->parents;
		}

		$mfstructures = $this->getParentStructures();

		if (!$mfstructures)
		{
			return array();
		}

		$stitems = array();

		foreach ($mfstructures as $mfstructure)
		{

			$stitem = DataObjectFactory::Factory('STItem');

			if (!$stitem->load($mfstructure->stitem_id))
			{
				continue;
			}

			$stitems[] = $stitem;
		}

		$this->parents = $stitems;

		return $this->parents;

	}

	public function getChildren()
	{

		if ($this->children) {
			return $this->children;
		}

		$mfstructures = $this->getChildStructures();

		if (!$mfstructures)
		{
			return array();
		}

		$stitems = array();

		foreach ($mfstructures as $mfstructure)
		{
			$stitem = DataObjectFactory::Factory('STItem');

			if (!$stitem->load($mfstructure->ststructure_id))
			{
				continue;
			}
			$stitems[] = $stitem;
		}

		$this->children = $stitems;

		return $this->children;
	}

	public function getParentStructures()
	{
		if ($this->parent_structures)
		{
			return $this->parent_structures;
		}

		$cc = new ConstraintChain;

		$cc->add(new Constraint('ststructure_id', '=', $this->id));

		$db = DB::Instance();

		$date = Constraint::TODAY;
		$between = $date.' BETWEEN '.$db->IfNull('start_date', $date).' AND '.$db->IfNull('end_date', $date);

		$cc->add(new Constraint('', '', '('.$between.')'));

		$mfstructures = DataObjectFactory::Factory('MFStructure');

		$mfstructures = array_keys($mfstructures->getAll($cc));

		$total_mfstructures = count($mfstructures);

		for ($i = 0; $i < $total_mfstructures; $i++) {

			$id = $mfstructures[$i];

			$mfstructure = DataObjectFactory::Factory('MFStructure');

			if (!$mfstructure->load($id))
			{
				unset($mfstructures[$i]);
				continue;
			}
			$mfstructures[$i] = $mfstructure;
		}

		if (!$mfstructures)
		{
			return array();
		}

		$this->parent_structures = $mfstructures;

		return $this->parent_structures;
	}

	public function getPOrderLines()
	{
// This gets all Purchase Order Lines
		if ($this->polines)
		{
			return $this->polines;
		}

		$polines = new POrderLineCollection();

		$sh = new SearchHandler($polines,false);

		$sh->addConstraint(new Constraint('stitem_id', '=', $this->id));
		$sh->addConstraint(new Constraint('status', 'in', "('A', 'N', 'O', 'P')"));
		$sh->setOrderby('due_delivery_date');

		$polines->load($sh);

		if ($polines)
		{
			$this->polines = $polines;

			return $this->polines;
		}
		else
		{
			return array();
		}

	}

	public function getPOstructures()
	{
// This gets all Purchase Order Lines
		if ($this->postructures) {
			return $this->postructures;
		}

		$polines = new POrderLineCollection();

		$polines->_tablename = 'po_structure_lines';

		$sh = new SearchHandler($polines,false);

		$sh->addConstraint(new Constraint('ststructure_id', '=', $this->id));
		$sh->addConstraint(new Constraint('status', 'in', "('A', 'N', 'O', 'P')"));
		$sh->setOrderby('due_delivery_date');

		$polines->load($sh);

		if ($polines)
		{
			$this->postructures = $polines;

			return $this->postructures;
		}
		else
		{
			return array();
		}

	}

	public function getSOrderLines()
	{
// This gets all Sales Order Lines
		if ($this->solines)
		{
			return $this->solines;
		}

		$solines = new SOrderLineCollection();

		$sh = new SearchHandler($solines,false);

		$sh->addConstraint(new Constraint('stitem_id', '=', $this->id));
		$sh->addConstraint(new Constraint('status', 'in', "('N', 'R', 'P', 'S')"));
		$sh->addConstraintChain(new Constraint('type', '=', 'O'));
		$sh->setOrderby('due_despatch_date');

		$solines->load($sh);

		if ($solines)
		{
			$this->solines = $solines;

			return $this->solines;
		}
		else
		{
			return array();
		}

	}

	public function getWorkOrders() {
// This gets all Work Orders
		if ($this->worders)
		{
			return $this->worders;
		}

		$mfworders = new MFWorkorderCollection();

		$sh = new SearchHandler($mfworders,false);

		$sh->addConstraint(new Constraint('stitem_id', '=', $this->id));
		$sh->addConstraint(new Constraint('status', '!=', 'C'));
		$sh->setOrderby('required_by');

		$mfworders->load($sh);

		if ($mfworders)
		{
			$this->worders = $mfworders;

			return $this->worders;
		}
		else
		{
			return array();
		}

	}

	public function getWOStructures()
	{
// This gets all Work Orders
		if ($this->wostructures)
		{
			return $this->wostructures;
		}

		$mfwostructures = new MFWOStructureCollection();

		$sh = new SearchHandler($mfwostructures,false);
		$sh->addConstraint(new Constraint('ststructure_id', '=', $this->id));
		$sh->addConstraint(new Constraint('status', '!=', 'C'));
		$sh->setOrderby('required_by');

		$mfwostructures->load($sh);

		if ($mfwostructures)
		{
			$this->wostructures = $mfwostructures;

			return $this->wostructures;
		}
		else
		{
			return array();
		}

	}

	public function getPOProductlineHeader()
	{
// Get the current PO Product linked to the Stock Item, if it exists
		$productlineheader = DataObjectFactory::Factory('POProductlineHeader');

		$cc = new ConstraintChain();
		$cc->add(new Constraint('stitem_id', '=', $this->id));

		$cc->add(currentDateConstraint());

		$productlineheader->loadBy($cc);

		return $productlineheader;
	}

	public function getSOProductlineHeader()
	{
// Get the current SO Product linked to the Stock Item, if it exists
		$productlineheader = DataObjectFactory::Factory('SOProductlineHeader');

		$cc = new ConstraintChain();
		$cc->add(new Constraint('stitem_id', '=', $this->id));

		$cc->add(currentDateConstraint());

		$productlineheader->loadBy($cc);

		return $productlineheader;
	}

	public function getChildStructures()
	{
		if ($this->child_structures)
		{
			return $this->child_structures;
		}

		$cc = new ConstraintChain;

		$cc->add(new Constraint('stitem_id', '=', $this->id));

		$db = DB::Instance();

		$date = Constraint::TODAY;
		$between = $date.' BETWEEN '.$db->IfNull('start_date', $date).' AND '.$db->IfNull('end_date', $date);
		$cc->add(new Constraint('', '', '('.$between.')'));

		$mfstructures = DataObjectFactory::Factory('MFStructure');

		$mfstructures = array_keys($mfstructures->getAll($cc));

		$total_mfstructures = count($mfstructures);

		for ($i = 0; $i < $total_mfstructures; $i++)
		{
			$id = $mfstructures[$i];

			$mfstructure = DataObjectFactory::Factory('MFStructure');

			if (!$mfstructure->load($id))
			{
				unset($mfstructures[$i]);
				continue;
			}

			$mfstructures[$i] = $mfstructure;
		}
		if (!$mfstructures)
		{
			return array();
		}

		$this->child_structures = $mfstructures;

		return $this->child_structures;
	}

	/**
	 * Return an array of operations for the item
	 * 
	 * @param $type mixed
	 *     Operation type(s), @see MFOperation
	 */
	public function getOperations($type=['R', 'B'])
	{
		$cc = new ConstraintChain;
		if (!is_array($type)) {
			$cc->add(new Constraint('type', '=', $type));
		} else {
			$type_cc = new ConstraintChain;
			foreach ($type as $t) {
				$type_cc->add(new Constraint('type', '=', $t), 'OR');
			}
			$cc->add($type_cc);
		}
		
		$cc->add(new Constraint('stitem_id', '=', $this->id));

		$db = DB::Instance();
		$date = Constraint::TODAY;

		$between = $date.' BETWEEN '.$db->IfNull('start_date', $date).' AND '.$db->IfNull('end_date', $date);
		$cc->add(new Constraint('', '', '('.$between.')'));

		$mfoperations = DataObjectFactory::Factory('MFOperation');
		$mfoperations->identifierField = 'op_no';

		$mfoperations = array_keys($mfoperations->getAll($cc));

		$total_mfoperations = count($mfoperations);

		for ($i = 0; $i < $total_mfoperations; $i++)
		{
			$id = $mfoperations[$i];

			$mfoperation = DataObjectFactory::Factory('MFOperation');

			if (!$mfoperation->load($id))
			{
				unset($mfoperations[$i]);
				continue;
			}

			$mfoperations[$i] = $mfoperation;
		}

		if (!$mfoperations)
		{
			return array();
		}

		$this->operations = $mfoperations;

		return $this->operations;
	}

	public function getOutsideOperations()
	{
		if ($this->outside_ops)
		{
			return $this->outside_ops;
		}

		$cc = new ConstraintChain;
		$cc->add(new Constraint('stitem_id', '=', $this->id));

		$db = DB::Instance();

		$date = Constraint::TODAY;
		$between = $date.' BETWEEN '.$db->IfNull('start_date', $date).' AND '.$db->IfNull('end_date', $date);
		$cc->add(new Constraint('', '', '('.$between.')'));

		$mfoutsideops = DataObjectFactory::Factory('MFOutsideOperation');
		$mfoutsideops->identifierField = 'op_no';

		$mfoutsideops = array_keys($mfoutsideops->getAll($cc));

		$total_mfoutsideops = count($mfoutsideops);

		for ($i = 0; $i < $total_mfoutsideops; $i++)
		{
			$id = $mfoutsideops[$i];

			$mfoutsideop = DataObjectFactory::Factory('MFOutsideOperation');

			if (!$mfoutsideop->load($id))
			{
				unset($mfoutsideops[$i]);
				continue;
			}

			$mfoutsideops[$i] = $mfoutsideop;
		}

		if (!$mfoutsideops)
		{
			return array();
		}

		$this->outside_ops = $mfoutsideops;

		return $this->outside_ops;
	}

	public function saveChildStructures()
	{
		if (!$this->child_structures)
		{
			return true;
		}

		$db = DB::Instance();
		$db->StartTrans();

		$success = true;

		foreach ($this->child_structures as $child_structure)
		{
			if (!$child_structure->save())
			{
				$db->FailTrans();
				$success = false;
				break;
			}
		}

		$db->CompleteTrans();

		return $success;
	}

	public function saveOperations()
	{
		if (!$this->operations)
		{
			return true;
		}

		$db = DB::Instance();
		$db->StartTrans();

		$success = true;

		foreach ($this->operations as $operation)
		{
			if (!$operation->save())
			{
				$db->FailTrans();
				$success = false;
				break;
			}
		}

		$db->CompleteTrans();

		return $success;
	}

	public function saveOutsideOperations()
	{
		if (!$this->outside_ops)
		{
			return true;
		}

		$db = DB::Instance();
		$db->StartTrans();

		$success = true;

		foreach ($this->outside_ops as $outside_op)
		{
			if (!$outside_op->save())
			{
				$db->FailTrans();
				$success = false;
				break;
			}
		}

		$db->CompleteTrans();

		return $success;
	}

	public function saveCosts()
	{
		if ($this->comp_class == 'B')
		{
			return $this->save();
		}

		$db = DB::Instance();
		$db->StartTrans();

		$success = (($this->save()) &&
					($this->saveChildStructures()) &&
					($this->saveOperations()) &&
					($this->saveOutsideOperations()));

		if (!$success)
		{
			$db->FailTrans();
		}

		$db->CompleteTrans();

		return $success;
	}

	public function rollUp($max_level = -1, $parent_id = null)
	{
		static $level = 0;

		if ($level == $max_level)
		{
			return true;
		}

		if (empty($parent_id))
		{
			$parents = $this->getParents();
		}
		else
		{
			$stitem = DataObjectFactory::Factory('STItem');
			$parents[] = $stitem->load($parent_id);
		}

		if (count($parents) == 0)
		{
			return true;
		}

		$level++;
		$success = true;
		$db = DB::Instance();
		$db->StartTrans();

		foreach ($parents as $parent)
		{
			set_time_limit(5);
			$old_costs = array(
				$parent->latest_cost,
				$parent->latest_mat,
				$parent->latest_lab,
				$parent->latest_osc,
				$parent->latest_ohd
			);

			$calc_costs=$parent->calcLatestCost();

			if($calc_costs===false)
			{
				$db->FailTrans();
				$success = "operation";
				break;
			}

			$new_costs = array(
				$parent->latest_cost,
				$parent->latest_mat,
				$parent->latest_lab,
				$parent->latest_osc,
				$parent->latest_ohd
			);

			$equal_costs = true;
			$total_costs = count($old_costs);
			for ($i = 0; $i < $total_costs; $i++)
			{
				if (bccomp($old_costs[$i], $new_costs[$i], $parent->cost_decimals) != 0)
				{
					$equal_costs = false;
					break;
				}
			}

			if ((!$equal_costs) && ((!$parent->saveCosts()) || (!STCost::saveItemCost($parent))))
			{
				$db->FailTrans();
				$success = false;
				break;
			}

			if (!$parent->rollUp($max_level))
			{
				$db->FailTrans();
				$success = false;
				break;
			}
		}

		$db->CompleteTrans();

		$level--;

		return $success;
	}

	public function rollOver()
	{
		$std_costs = array(
			$this->std_cost,
			$this->std_mat,
			$this->std_lab,
			$this->std_osc,
			$this->std_ohd
		);

		$latest_costs = array(
			$this->latest_cost,
			$this->latest_mat,
			$this->latest_lab,
			$this->latest_osc,
			$this->latest_ohd
		);

		$equal_costs = true;

		$total_costs = count($std_costs);

		for ($i = 0; $i < $total_costs; $i++)
		{
			if (bccomp($std_costs[$i], $latest_costs[$i], $this->cost_decimals) != 0) {
				$equal_costs = false;
				break;
			}
		}

		if ($equal_costs)
		{
			return true;
		}

		$this->std_cost = $this->latest_cost;
		$this->std_mat = $this->latest_mat;
		$this->std_lab = $this->latest_lab;
		$this->std_osc = $this->latest_osc;
		$this->std_ohd = $this->latest_ohd;

		$db = DB::Instance();
		$db->StartTrans();

		$success = true;

		if ((!$this->save()) || (!STCost::saveItemCost($this, 'std')))
		{
			$db->FailTrans();
			$success = false;
		}

		$db->CompleteTrans();

		return $success;
	}

	protected function clearCachedItems()
	{
		$this->parents				= null;
		$this->parent_structures	= null;
		$this->children				= null;
		$this->child_structures		= null;
		$this->operations			= null;
		$this->outside_ops			= null;
	}

	public function load($clause,$override=false)
	{
		$this->clearCachedItems();

		return parent::load($clause,$override);
	}

	public function total_valuation()
	{
		$total = 0;

		foreach ($this->balances as $balance)
		{
			$total += $balance->valuation;
		}

		return $total;
	}

	public function getBinList ($_whlocation_id='')
	{

		$sh=new SearchHandler(new STBalanceCollection(), false);

		if (!empty($_whlocation_id))
		{
			$sh->addConstraint(new Constraint('whlocation_id', '=', $_whlocation_id));
		}

		$sh->addConstraint(new Constraint('balance', '>', 0));

		$this->addSearchHandler('balances', $sh);

		$binlist=array();

		foreach ($this->balances as $balance)
		{
			$binlist[$balance->whbin_id]=$balance->whbin;
		}

		return $binlist;

	}

	public function getTaxRate ()
	{
		return array($this->tax_rate_id=>$this->tax_rate);
	}

	public function getUomList()
	{
		$uom_list = array();

		if ($this->isLoaded())
		{
			$uom_temp_list = STuomconversion::getUomList($this->id, $this->uom_id);

			if (count($uom_temp_list) == 0)
			{
				$uom_temp_list = SYuomconversion::getUomList($this->uom_id);
			}

			$uom = DataObjectFactory::Factory('STuom');
			$uom->load($this->uom_id);

			$uom_list[$this->uom_id] = $uom->getUomName();
			$uom_list += $uom_temp_list;
		}
		else
		{
			$uom = DataObjectFactory::Factory('STuom');
			$uom_list=$uom->getAll();
		}

		return $uom_list;
	}

	public static function getAvailableBalance($id)
	{
		$stitem = DataObjectFactory::Factory('STItem');

		$stitem->load($id);

		if ($stitem->isLoaded())
		{
			return $stitem->currentBalance();
		}

		return 0;

	}
}

// End of STItem
