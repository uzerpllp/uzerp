<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/
class MFOperation extends DataObject {

	protected $version='$Revision: 1.4 $';

	protected $defaultDisplayFields = array('op_no'
											,'type'
											,'start_date'
											,'end_date'
											,'remarks'
											,'stitem_id'
											,'mfcentre_id'
											,'mfresource_id'
											,'volume_target' => 'Volume Target/Time'
											,'volume_period' => 'Volume Period/Time Unit'
											,'volume_uom_id'
											,'volume_uom'
											,'quality_target'
											,'uptime_target'
											,'resource_qty'
											,'centre'
											,'resource'
											,'product_description'
											);


	function __construct($tablename='mf_operations') {
		parent::__construct($tablename);
		$this->idField='id';
		$this->identifierField='id';

		$this->orderby = ['op_no'];
		$this->orderdir = ['ASC'];

 		$this->validateUniquenessOf(array('stitem_id', 'op_no'));
 		$this->belongsTo('STItem', 'stitem_id', 'stitem');
		$this->belongsTo('STuom', 'volume_uom_id', 'volume_uom');
		$this->belongsTo('MFCentre', 'mfcentre_id', 'mfcentre');
		$this->belongsTo('MFResource', 'mfresource_id', 'mfresource');

		// Restrict available product choices based on module setting
		$system_prefs = SystemPreferences::instance();
		$module_prefs = $system_prefs->getModulePreferences('manufacturing');
		if (isset($module_prefs['outside-op-prod-group'])) {
			$cc = new ConstraintChain;
			$cc->add(new Constraint('prod_group_id', '=', $module_prefs['outside-op-prod-group']));
			$this->belongsTo('POProductlineHeader', 'po_productline_header_id', 'product_description', $cc);
		} else {
			$this->belongsTo('POProductlineHeader', 'po_productline_header_id', 'product_description');
		}
		
		$this->setEnum('volume_period',array( 'S'=>'Second'
										  ,'M'=>'Minute'
										  ,'H'=>'Hour'));
		$this->setEnum('type', [
			'R' => 'Routing',
			'B' => 'Per Order',
			'O' => 'Outside Operation'
		]);

		$this->setAdditional('latest_cost', 'numeric');
		$this->setAdditional('std_cost', 'numeric');
		$this->setAdditional('suppliers', 'text');
	}

	function cb_loaded($success)
	{
	    $this->latest_cost = add(
			$this->latest_lab,
			$this->latest_ohd,
			$this->latest_osc
		);

	    $this->std_cost = add(
			$this->std_lab,
			$this->std_ohd,
			$this->std_osc
		);

		// Add available supplier names for Outside Operations
		$this->suppliers = null;

		if ($this->type == 'O' && $this->po_productline_header_id !== null) {
			$productlines = new POProductlineCollection();
			$cc = new ConstraintChain();
			$cc->add(currentDateConstraint(date('Y-m-d')));
			$cc->add(new Constraint('productline_header_id', '=', $this->po_productline_header_id));
			$sh = new SearchHandler($productlines);
			$sh->addConstraint($cc);
			$productlines->load($sh);

			foreach ($productlines as $p) {
				$suppliers[] = $p->supplier;
			}

			$this->suppliers = implode(', ', $suppliers);
		}
	}

	public static function globalRollOver() {
		$db = DB::Instance();
		$date = date('Y-m-d');
		$query = "UPDATE mf_operations
					SET std_cost=latest_cost, std_lab=latest_lab, std_ohd=latest_ohd, std_osc=latest_osc
					WHERE (start_date <= '".$date."' OR start_date IS NULL) AND (end_date > '".$date."' OR end_date IS NULL) AND usercompanyid=".EGS_COMPANY_ID;
		return ($db->Execute($query) !== false);
	}

}
?>
