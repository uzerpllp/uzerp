<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class StcostsController extends printController {

	protected $version='$Revision: 1.11 $';

	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{

		parent::__construct($module, $action);

		$this->_templateobject = DataObjectFactory::Factory('STCost');

		$this->uses($this->_templateobject);
	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{
		$errors=array();
		$defaults=array();
// Set context from calling module
		if (isset($this->_data['stitem_id']))
		{
			$defaults['stitem_id'] = $this->_data['stitem_id'];
		}

		$this->setSearch('stcostsSearch', 'useDefault', $defaults);

		$this->view->set('clickaction', 'costSheet');
		$this->view->set('linkfield', 'stitem_id');
		$this->view->set('linkvaluefield', 'stitem_id');

		$sidebar = new SidebarController($this->view);
        $sidebarlist = array();
        $sidebarlist['allItems'] = [
            'tag' => 'Recalc Latest Costs',
            'link' => array_merge($this->_modules, [
                'controller' => $this->name,
                'action' => 'recalclatestcosts'
            ]),
            'class' => 'confirm',
            'data_attr' => ['data_uz-action-id' => 'p', 'data_uz-confirm-message' => "Start cost recalculation?|This cannot be undone."]
        ];
        $sidebarlist['viewItem'] = [
            'tag' => 'Costs Roll Over',
            'link' => array_merge($this->_modules, [
                'controller' => $this->name,
                'action' => 'rollover'
            ]),
            'class' => 'confirm',
            'data_attr' => ['data_uz-action-id' => 'p', 'data_uz-confirm-message' => "Start latest costs roll to standard costs?|This cannot be undone."]
        ];
		$sidebar->addList('Cost Changes',$sidebarlist);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

		parent::index(new STCostCollection($this->_templateobject));
	}

	public static function getStructureCosts(ConstraintChain $cc, $type = 'latest')
	{
		$mfstructures = new MFStructureCollection();

		$sh = new SearchHandler($mfstructures, false);

		$sh->addConstraintChain($cc);

		$fields = array('id'
						,'line_no'
						,'stitem_id'
						,'ststructure_id'
						,'qty'
						,'uom_id'
						,'waste_pc'
						,$type.'_mat'
						,$type.'_lab'
						,$type.'_osc'
						,$type.'_ohd'
						,$type.'_cost'
						);

		$sh->setFields($fields);

		$sh->setOrderby('line_no');

		$mfstructures->load($sh);

		return $mfstructures;
	}

	public static function getOperationCosts(ConstraintChain $cc, $type = 'latest')
	{
		$mfoperations = new MFOperationCollection();

		$sh = new SearchHandler($mfoperations, false);

		$sh->addConstraintChain($cc);

		$fields = array('id',
						'op_no',
                        'remarks',
						'volume_target',
						'volume_uom_id',
						'volume_period',
						'quality_target',
						'uptime_target',
						$type.'_lab',
						$type.'_ohd',
						$type.'_osc',
						$type.'_cost'
						);

		$sh->setFields($fields);

		$sh->setOrderby('op_no');

		$mfoperations->load($sh);

		return $mfoperations;
	}

	public static function getOutsideOperationCosts(ConstraintChain $cc, $type = 'latest')
	{
		$mfoutsideops = new MFOutsideOperationCollection();

		$sh = new SearchHandler($mfoutsideops, false);

		$sh->addConstraintChain($cc);

		$fields = array('id'
						,'op_no'
						,'description'
						,$type.'_osc'
						);

		$sh->setFields($fields);

		$sh->setOrderby('op_no');

		$mfoutsideops->load($sh);

		return $mfoutsideops;
	}

	public function costSheet()
	{
		$errors=array();

		$s_data=array();

		if (isset($this->_data['stitem_id']))
		{
			$stitem_id = $this->_data['stitem_id'];
		}
		elseif (isset($this->_data['Search']['stitem_id']))
		{
			$stitem_id = $this->_data['Search']['stitem_id'];
		}

		if (!isset($stitem_id))
		{
			$flash = Flash::Instance();

			$flash->addError('No Stock Item specified');

			sendTo('STItems'
					,'index'
					,$this->_modules);

			return;
		}

		$s_data['start_date/end_date'] = date(DATE_FORMAT);

		$s_data['type'] = 'latest';

		$s_data['stitem_id'] = $stitem_id;

		$this->setSearch('costSheetSearch', 'useDefault', $s_data);

		// Disable output button in search
		$this->printaction = '';

		$type = $this->search->getValue('type');

		$start_end_date = $this->search->getValue('start_date/end_date');

		$stitem = DataObjectFactory::Factory('STItem');

		$stitem->load($stitem_id);

		$this->view->set('stitem', $stitem);

		$this->view->set('type',$type);

		$cc = $this->search->toConstraintChain();

		$mfstructures = self::getStructureCosts($cc, $type);
		$this->view->set('mfstructures', $mfstructures);

		$mfoperations = self::getOperationCosts($cc, $type);
		$this->view->set('mfoperations', $mfoperations);

		$mfoutsideops = self::getOutsideOperationCosts($cc, $type);
		$this->view->set('mfoutsideops', $mfoutsideops);

		$this->view->set('clickaction', 'costSheet');
		$this->view->set('clickcontroller', 'STCosts');
		$this->view->set('linkfield', 'stitem_id');
		$this->view->set('linkvaluefield', 'ststructure_id');
		$this->view->set('no_ordering', true);
		$this->view->set('page_title', $this->getPageName('Stock Item', 'Cost Sheet for'));
		$output_url = link_to(
		    array_merge($this->_modules, [
		                  'controller'=>$this->name,
		                  'action'=>'printDialog',
		                  'printaction'=>'printCostSheet',
		                  'filename'=>'StockCosts'.fix_date($s_data['start_date/end_date']),
		                  'stitem_id'=>$stitem_id,
		                  'type'=>$type,
		                  'date'=>fix_date($start_end_date)]
		               ), false, false);
		$this->view->set('output_link', $output_url);

		$sidebar = new SidebarController($this->view);

		$sidebarlist = array();

		$sidebarlist['allItems'] = array('tag' => 'All Items'
										,'link' => array_merge($this->_modules
															  ,array('controller'=>$this->name
																	,'action'=>'index'
																	)
															  )
										);

		$sidebarlist['viewItem'] = array('tag' => 'Item Detail'
										,'link' => array('module'=>'manufacturing'
													   ,'controller'=>'STItems'
													   ,'action'=>'view'
													   ,'id'=>$stitem_id
													   )
									   );

		$sidebar->addList('Show',$sidebarlist);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function rollOver()
	{
		$this->checkRequest(['post'], true);
	    $flash = Flash::Instance();
		try {
		    $job = new uzJobCostRollOver(EGS_USERNAME, EGS_COMPANY_ID, 'uzJobRecalcLatestCosts');
            $job_id = $job->push();
            $message = uzJobMessages::Factory(EGS_USERNAME, EGS_COMPANY_ID);
            $message->storeMessageToken($job_id);
            $flash->addMessage("Stock items cost roll-over job queued");
		} catch (uzJobException $e) {
		    $flash->addError($e->getMessage());
		}
    	sendBack();
	}

	public function recalcLatestCosts()
	{
		$this->checkRequest(['post'], true);
	    $flash = Flash::Instance();
    	try {
    	    $job = new uzJobRecalcLatestCosts(EGS_USERNAME, EGS_COMPANY_ID, 'uzJobCostRollOver');
    	    $job_id = $job->push();
    	    $message = uzJobMessages::Factory(EGS_USERNAME, EGS_COMPANY_ID);
    	    $message->storeMessageToken($job_id);
    	    $flash->addMessage("Stock items cost recalculation job queued");
    	} catch (uzJobException $e) {
    	    $flash->addError($e->getMessage());
    	}
    	sendBack();
	}

	/* output functions */
	public function printCostSheet($status='generate')
	{

		if (isset($this->_data['date']) && !empty($this->_data['stitem_id']))
		{
			$date = $this->_data['date'];
		}
		else
		{
			$date = fix_date(date(DATE_FORMAT));
		}

		// build options array
		$options=array('type'		=>	array('pdf'=>'',
											  'xml'=>''
										),
					   'output'		=>	array('print'=>'',
					   						  'save'=>'',
					   						  'email'=>'',
					   						  'view'=>''
										),
					   'filename'	=>	'CostSheet'.$date,
					   'report'		=>	'CostSheet'
				);

		if(strtolower((string) $status)=="dialog")
		{
			return $options;
		}

		if (isset($this->_data['stitem_id']))
		{
			$id = $this->_data['stitem_id'];
		}
		else
		{
			$id='';
		}

		if (isset($this->_data['type']))
		{
			$type = $this->_data['type'];
		}
		else
		{
			$type='latest';
		}

		$stitem = DataObjectFactory::Factory('STItem');
		$stitem->load($id);

		$cc = new ConstraintChain;
		$db = DB::Instance();

		$between = "'".$date."' BETWEEN ".$db->IfNull('start_date', "'".$date."'").' AND '.$db->IfNull('end_date', "'".$date."'");

		$cc->add(new Constraint('', '', '('.$between.')'));
		$cc->add(new Constraint('stitem_id', '=', $id));

		$mfstructures = self::getStructureCosts($cc, $type);

		$cc->removeLast();

		$child_structures = $stitem->getChildStructures();

		$stitem_ids = array($stitem->id);

		foreach ($child_structures as $child_structure)
		{
			$stitem_ids[] = $child_structure->ststructure_id;
		}

		$cc->add(new Constraint('stitem_id', '=', $stitem->id));

		$mfoperations = self::getOperationCosts($cc, $type);
		$mfoutsideops = self::getOutsideOperationCosts($cc, $type);

		$totals=array($type.'_mat'=>0
					 ,$type.'_lab'=>0
					 ,$type.'_osc'=>0
					 ,$type.'_ohd'=>0
		);

		foreach ($mfstructures as $mfstructure)
		{
			foreach ($mfstructure->getFields() as $field)
			{
				if (isset($totals[$field->name]))
				{
					$totals[$field->name]+=$field->value;
				}
			}
			$mfstructure->{$type.'_cost'} = 0;
			$mfstructure->{$type.'_cost'} += $mfstructure->{$type.'_mat'};
			$mfstructure->{$type.'_cost'} += $mfstructure->{$type.'_lab'};
			$mfstructure->{$type.'_cost'} += $mfstructure->{$type.'_osc'};
			$mfstructure->{$type.'_cost'} += $mfstructure->{$type.'_ohd'};
		}

		foreach ($mfoperations as $mfoperation)
		{
			foreach ($mfoperation->getFields() as $field)
			{
				if (isset($totals[$field->name]))
				{
					$totals[$field->name]+=$field->value;
				}
			}
		}

		foreach ($mfoutsideops as $mfoutsideop)
		{
			foreach ($mfoutsideop->getFields() as $field)
			{
				if (isset($totals[$field->name]))
				{
					$totals[$field->name]+=$field->value;
				}
			}
		}

        // Calculate report totals from category sub-totals
		$totals[$type.'_cost'] = $totals[$type.'_mat'] + $totals[$type.'_lab'] + $totals[$type.'_osc'] + $totals[$type.'_ohd'];

		$title = 'Latest Cost Sheet';
		if($type == 'std') {
		    $title = 'Standard Cost Sheet';
		}

		// construct the extra array
		$extra['totals'][]=$totals;
		$extra['date']=$date;
		$extra['type']=$type;
		$extra['title']=$title.' as at '.un_fix_date($date);

		// prepare the xml options array
		$xml_options=array('model'=>array($stitem,$mfstructures,$mfoperations,$mfoutsideops),
						   'load_relationships'=>FALSE,
						   'extra'=>$extra
						  );

		$options['xmlSource']=$this->generateXML($xml_options);

		echo $this->constructOutput($this->_data['print'],$options);
		exit;

	}

	protected function getPageName($base=null,$action=null)
	{
		return parent::getPageName((empty($base)?'stock_costs':$base), $action);
	}

}

// End of StcostsController
