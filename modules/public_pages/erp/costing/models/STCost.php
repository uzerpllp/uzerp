<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class STCost extends DataObject {

	protected $version='$Revision: 1.3 $';
	
	protected $defaultDisplayFields = array('stitem'
											,'type'
											,'cost'
											,'mat'
											,'lab'
											,'osc'
											,'ohd'
											,'effect_on_stock'
											,'lastupdated'
											,'alteredby'
											);
		
	public function __construct($tablename='st_costs') {
		parent::__construct($tablename);
		$this->idField='id';
		
		$this->orderby=array('lastupdated', 'id');
		$this->orderdir=array('DESC', 'DESC');
		$this->belongsTo('STItem', 'stitem_id', 'stitem');
		$this->setEnum('type',array( 'std'=>'Standard'
                                    ,'latest'=>'Latest'));

	}
	
	public static function saveItemCost(STItem $stitem, $type = 'latest') {
		$errors = array();
		$data = array(
			'stitem_id' => $stitem->id,
			'type' => $type,
			'effect_on_stock' => 0
		);
		switch ($type) {
			case 'std':
				$data['cost'] = $stitem->std_cost;
				$data['mat'] = $stitem->std_mat;
				$data['lab'] = $stitem->std_lab;
				$data['osc'] = $stitem->std_osc;
				$data['ohd'] = $stitem->std_ohd;
				break;
			case 'latest':
				$data['cost'] = $stitem->latest_cost;
				$data['mat'] = $stitem->latest_mat;
				$data['lab'] = $stitem->latest_lab;
				$data['osc'] = $stitem->latest_osc;
				$data['ohd'] = $stitem->latest_ohd;
				break;
		}
		$prev_stcost = self::getMostRecent($stitem->id, $type);
		if ($prev_stcost) {
			$data['effect_on_stock'] = round($stitem->balance * ($data['cost'] - $prev_stcost->cost), 2);
		}
		$stcost = self::Factory($data, $errors, 'STCost');
		if (count($errors) > 0) {
			return false;
		}
		return $stcost->save();
	}
	
	public static function getMostRecent($stitem_id, $type) {
		$cc = new ConstraintChain;
		$cc->add(new Constraint('stitem_id', '=', $stitem_id));
		$cc->add(new Constraint('type', '=', $type));
		$sh = new SearchHandler(new STCostCollection,false);
		$sh->addConstraintChain($cc);
		$sh->setOrderBy(array('lastupdated', 'id'), array('DESC', 'DESC'));
		$stcost = new STCost;
		return $stcost->loadBy($sh);
	}

}
?>