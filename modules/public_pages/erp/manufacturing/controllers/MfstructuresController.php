<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class MfstructuresController extends printController
{

	protected $version='$Revision: 1.25 $';

	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{
		parent::__construct($module, $action);

		$this->_templateobject = DataObjectFactory::Factory('MFStructure');

		$this->uses($this->_templateobject);

	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{
		$errors = array();

		$s_data = array();

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
		$s_data['stitem_id'] = $stitem_id;

		$this->view->set('stitem_id', $stitem_id);

		$transaction = DataObjectFactory::Factory('STItem');
		$transaction->load($stitem_id);

		$this->view->set('transaction',$transaction);
		$obsolete = $transaction->isObsolete();

		$this->setSearch('structuresSearch', 'useDefault', $s_data);

		self::showParts();

		$sidebar = new SidebarController($this->view);
		$sidebarlist = array();
		$sidebarlist['allItem']= array('tag' => 'View'
									  ,'link' => array('modules'=>$this->_modules
													  ,'controller'=>'STItems'
													  ,'action'=>'index'
													  )
									  );
		$sidebar->addList('All Items',$sidebarlist);

		$sidebarlist = array();

		$sidebarlist['viewItem'] = array('tag' => 'Show Item Detail'
										,'link' => array('modules'=>$this->_modules
														,'controller'=>'STItems'
														,'action'=>'view'
														,'id'=>$stitem_id
														)
										);

		$sidebarlist['where_used'] = array('tag' => 'Where Used'
										  ,'link' => array('modules'=>$this->_modules
														  ,'controller'=>'STItems'
														  ,'action'=>'where_Used'
														  ,'id'=>$stitem_id
														  )
										);

		if ( (in_array($transaction->comp_class, ['M', 'S', 'K', 'P']))
			&& (!$obsolete))
		{
			$sidebarlist['newStructure'] = array('tag' => 'Add to Structure'
												,'link' => array('modules'=>$this->_modules
																,'controller'=>$this->name
																,'action'=>'new'
																,'stitem_id'=>$stitem_id
																)
										);
		}

		$sidebar->addList('This Item',$sidebarlist);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

		$this->view->set('clickaction', 'edit');
		$this->view->set('clickcontroller', 'MFStructures');
		$this->view->set('no_ordering', true);
	}

	public function delete($modelName = null)
	{

		$flash = Flash::Instance();

		$db = DB::Instance();

		$db->StartTrans();

		$errors = array();

		$data = array(
			'id' => $this->_data['id'],
			'end_date' => date(DATE_FORMAT)
		);

		$structure = MFStructure::Factory($data, $errors, $this->modeltype);

		if ((count($errors) > 0) || (!$structure->save()))
		{
			$errors[] = 'Could not update current structure';
			$db->FailTrans();
		}
		if (count($errors) == 0) {

			$stitem = DataObjectFactory::Factory('STItem');

			if ($stitem->load($structure->ststructure_id))
			{
				if (!$stitem->rollUp(STItem::ROLL_UP_MAX_LEVEL))
				{
					$errors[] = 'Could not roll-up latest costs';
					$db->FailTrans();
				}
			}
			else
			{
				$errors[] = 'Could not roll-up latest costs';
				$db->FailTrans();
			}
		}

		$db->CompleteTrans();

		if (count($errors) == 0)
		{
			$flash->addMessage('Structure will end today');
			sendTo($this->name
					,'index'
					,$this->_modules
					,array('stitem_id' => $this->_data['stitem_id']));
		}
		else
		{
			$flash->addErrors($errors);
			sendBack();
		}
	}

	public function deleteSubstitute()
	{

		$flash = Flash::Instance();

		$db = DB::Instance();

		$db->StartTrans();

		$errors = array();

		$data = array(
			'id' => $this->_data['id'],
			'end_date' => null
		);

		$structure = MFStructure::Factory($data, $errors, $this->modeltype);

		if ((count($errors) > 0) || (!$structure->save()))
		{
			$errors[] = 'Could not update current structure';
			$db->FailTrans();
		}

		if (count($errors) == 0)
		{
			$substitute = $structure->getSubstitute();

			if (($substitute) && ($substitute->delete()))
			{
				$stitem = DataObjectFactory::Factory('STItem');

				if ($stitem->load($structure->ststructure_id))
				{
					if (!$stitem->rollUp(STItem::ROLL_UP_MAX_LEVEL))
					{
						$errors[] = 'Could not roll-up latest costs';
						$db->FailTrans();
					}
				}
				else
				{
					$errors[] = 'Could not roll-up latest costs';
					$db->FailTrans();
				}
			}
			else
			{
				$errors[] = 'Could not delete new structure';
				$db->FailTrans();
			}
		}

		$db->CompleteTrans();

		if (count($errors) == 0)
		{
			$flash->addMessage('Substitute deleted');

			sendTo($this->name
					,'index'
					,$this->_modules
					,array('stitem_id' => $this->_data['stitem_id']));
		}
		else
		{
			$flash->addErrors($errors);
			sendBack();
		}

	}

	public function _new()
	{
		$errors = [];

		// need to store the ajax flag in a different variable and the unset the original
		// this is to prevent any functions that are further called from returning the wrong datatype
		$ajax=isset($this->_data['ajax']);
		unset($this->_data['ajax']);

		parent::_new();
		$mfstructure = $this->_uses[$this->modeltype];

		$stitem_id = '';
		if (isset($this->_data[$this->modeltype]['stitem_id']))
		{
			$stitem_id = $this->_data[$this->modeltype]['stitem_id'];
		}
		elseif ($mfstructure->isLoaded())
		{
			$stitem_id = $mfstructure->stitem_id;
		}
		elseif (isset($this->_data['stitem_id']))
		{
			$stitem_id = $this->_data['stitem_id'];
		}

		if (!empty($stitem_id))
		{
			$stitem = DataObjectFactory::Factory('STItem');

			$stitem->load($stitem_id);

			$this->view->set('stitem',$stitem->item_code.' - '.$stitem->description);

			$s_data = array('stitem_id' => $stitem_id, 'start_date/end_date' => date(DATE_FORMAT));

			$this->search = structuresSearch::useDefault($s_data, $errors);

			if (count($errors) == 0)
			{
				self::showParts();
			}

			$this->view->set('no_ordering',true);
		}
		else
		{
			$stitems = STItem::nonObsoleteItems();
			$stitem_id = key($stitems);
			$this->view->set('stitems', $stitems);
		}

		$start_date = date(DATE_FORMAT);

		if (isset($this->_data[$this->modeltype]['start_date']))
		{
			$start_date = $this->_data[$this->modeltype]['start_date'];
		}
		elseif (isset($this->_data['start_date']))
		{
			$start_date = $this->_data['start_date'];
		}

		$ststructure = DataObjectFactory::Factory('STItem');
		$ststructures = $this->getItems($start_date, $stitem_id);

		if (empty($ststructures))
		{
			$flash = Flash::Instance();
			$flash->addError('Cannot find any current items to add to structure');
			sendBack();
		}

		if (isset($this->_data[$this->modeltype]['ststructure_id']))
		{
			$ststructure_id = $this->_data[$this->modeltype]['ststructure_id'];
		}
		elseif (isset($this->_data['ststructure_id']))
		{
			$ststructure_id = $this->_data['ststructure_id'];
		}
		else
		{
			$ststructure_id = key($ststructures);
		}

		// If we have an MFStructure, ensure its child stitem_id is used
		if ($mfstructure->isLoaded() ) {
			$ststructure_id = $mfstructure->ststructure_id;
		}

		$this->view->set('ststructures',$ststructures);
		$this->view->set('clickaction','edit');
		$ststructure->load($ststructure_id);

		$uom_temp_list = array();
		$uom_temp_list = STuomconversion::getUomList($ststructure_id, $ststructure->uom_id);
		$uom_temp_list += SYuomconversion::getUomList($ststructure->uom_id);

		$uom = DataObjectFactory::Factory('STuom');
		$uom->load($ststructure->uom_id);

		$uom_list = array();
		$uom_list[$ststructure->uom_id] = $uom->getUomName();
		$uom_list +=$uom_temp_list;

		$this->view->set('uom_id',$ststructure->uom_id);
		$this->view->set('uom_list',$uom_list);

		$cancel_url = link_to(array_merge($this->_modules, [
		    'controller' => $this->name,
		    'action' => 'index',
		    'stitem_id' => $stitem_id
		]), false, false);
		$this->view->set('cancel_link', $cancel_url);

		$sidebar = new SidebarController($this->view);

		$sidebar->addList(
			'Actions', [
				'view' => [
					'tag' => 'Substitute Item',
					'link' => [
						'modules'=>$this->_modules,
						'controller'=>$this->name,
						'action'=>'view',
						'id'=>$mfstructure->id,
					]
				]
			]);

		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
	}

	/**
	 * Test for a circular reference when adding structures
	 * 
	 * Note: this **only** tests for circular references in
	 * the adjacency tree. The goal is to avoid infinite loops
	 * when running recursive operations on nested structures,
	 * e.g. MFWOStructure::explodePhantom.
	 *
	 * @param integer $start_structure_id  Start level STItem::id 
	 * 					(the item being added to the structure)
	 * @param integer $adding_to_item_id  STItem::id of the item being added to 
	 * 					(the structure that $start_structure_id is being added to)
	 * @param boolean $found_ref  
	 * @return boolean return true if $adding_item_id appears
	 * 					on a higher level structure.
	 */
	public static function testCircularRef($start_structure_id, $added_to_item_id, &$found_ref = false) {
		$structure =  MFStructureCollection::getCurrent($start_structure_id);
		foreach ($structure as $item) {
			if ($item->ststructure_id == $added_to_item_id) {
				$found_ref = true;
				break;
			} else {
				$found_ref = self::testCircularRef($item->ststructure_id, $added_to_item_id);
			}
		}
		return $found_ref;
	}

	public function save($modelName = null, $dataIn = [], &$errors = []) : void
	{

		$flash = Flash::Instance();

		$db = DB::Instance();

		$db->StartTrans();

		$errors = array();

		//Check for circular refs
		$start_id = $this->_data[$this->modeltype]['ststructure_id'];
		$adding_id = $this->_data[$this->modeltype]['stitem_id'];

		if (self::testCircularRef($start_id, $adding_id)) {
			$stitem = new STItem();
			$stitem->load($this->_data[$this->modeltype]['ststructure_id']);
			$errors[] = "{$stitem->item_code} is already referenced in a parent structure.";
			$db->FailTrans();
		}

		if ($this->_data[$this->modeltype]['qty']<=0)
		{
		    $errors[] = 'Quantity must be greater than zero';
			$db->FailTrans();
		}
		elseif (parent::save($this->modeltype,'',$errors))
		{
			$stitem = DataObjectFactory::Factory('STItem');

			if ($stitem->load($this->saved_model->ststructure_id))
			{
				// load result of ::rollup, needs to handle two different errors
				$sttemp = $stitem->rollUp(STItem::ROLL_UP_MAX_LEVEL, $this->saved_model->stitem_id);
				if (!$sttemp)
				{
					$errors[] = 'Could not roll-up latest costs';
					$db->FailTrans();
				}
				elseif ($sttemp==='operation')
				{
					$errors[] = 'Could not roll-up latest costs';
					$errors[] = 'An operation contains either null or zero values';
					$db->FailTrans();
				}

			}
			else
			{
				$errors[] = 'Could not roll-up latest costs';
				$db->FailTrans();
			}
		}
		else
		{
			$errors[] = 'Could not save structure';
			$db->FailTrans();
		}

		$db->CompleteTrans();

		if (count($errors) > 0)
		{
			$flash->addErrors($errors);

			if (isset($this->_data['ststructure_id']))
			{
				$this->_data['ststructure_id'] = $this->_data[$this->modeltype]['ststructure_id'];
			}

			if (isset($this->_data[$this->modeltype]['stitem_id']))
			{
				$this->_data['stitem_id'] = $this->_data[$this->modeltype]['stitem_id'];
			}

			$this->refresh();

		}
		elseif (isset($this->_data['saveform']))
		{
			sendTo($this->name
					,'index'
					,$this->_modules
					,array('stitem_id' => $this->_data[$this->modeltype]['stitem_id']));
		}
		else
		{
			sendTo($this->name
					,'new'
					,$this->_modules
					,array('stitem_id' => $this->_data[$this->modeltype]['stitem_id']));

		}
// Either there was an error or it is add another
// so display the add screen again
	}

	public function substitute()
	{

		$flash = Flash::Instance();

		$db = DB::Instance();

		$db->StartTrans();

		$errors = array();

		//Check for circular refs
		$structure_id = $this->_data[$this->modeltype]['ststructure_id'];
		if (self::testCircularRef($structure_id, $this->_data[$this->modeltype]['stitem_id'])) {
			$stitem = new STItem();
			$stitem->load($this->_data[$this->modeltype]['ststructure_id']);
			$errors[] = "{$stitem->item_code} is already referenced in a parent structure.";
			$db->FailTrans();
		}

		$timestamp = strtotime(fix_date($this->_data[$this->modeltype]['start_date']));
		// 86400 = 24 hours
		$timestamp -= 86400;

		$data = array(
			'id' => $this->_data[$this->modeltype]['current_structure_id'],
			'end_date' => date(DATE_FORMAT, $timestamp)
		);

		$current_structure = MFStructure::Factory($data, $errors, $this->modeltype);

		if ((count($errors) > 0) || (!$current_structure->save()))
		{
			$errors[] = 'Could not remove current structure';
			$db->FailTrans();
		}

		if ($this->_data[$this->modeltype]['qty']<=0)
		{
			$errors['qty']='Quantity must be greater than zero';
			$db->FailTrans();
		}

		if (count($errors) == 0)
		{
			if (parent::save($this->modeltype))
			{
				$stitem = DataObjectFactory::Factory('STItem');
				if ($stitem->load($this->saved_model->ststructure_id))
				{
					//$stitem->calcLatestCost();
					if (!$stitem->rollUp(STItem::ROLL_UP_MAX_LEVEL))
					{
						$errors[] = 'Could not roll-up latest costs';
						$db->FailTrans();
					}
				}
				else
				{
					$errors[] = 'Could not roll-up latest costs';
					$db->FailTrans();
				}
			}
			else
			{
				$errors[] = 'Could not add new structure';
				$db->FailTrans();
			}
		}

		$db->CompleteTrans();

		if (count($errors) == 0)
		{
			$flash->addMessage('Structure substituted');

			sendTo($this->name
					,'index'
					,$this->_modules
					,array('stitem_id' => $this->_data[$this->modeltype]['stitem_id']));
		}
		else
		{
			$flash->addErrors($errors);
			sendBack();
		}

	}

	public function preorder ()
	{
		$errors = [];
		if (isset($this->_data['stitem_id']) || isset($this->_data['id']))
		{
			$id = $this->_data['stitem_id'];
			if (isset($this->_data['id'])) {
				$id = $this->_data['id'];
			};

			$stitem = DataObjectFactory::Factory('STItem');
			$stitem->load($id);

			$this->view->set('transaction',$stitem);

			$s_data = array('stitem_id' => $id, 'start_date/end_date' => date(DATE_FORMAT));

			$this->search = structuresSearch::useDefault($s_data, $errors);

			if (count($errors) == 0)
			{
				self::showParts();
			}

			if (!isset($this->_data['qty']))
			{
				$this->_data['qty']=1;
			}

			$this->view->set('qty',$this->_data['qty']);
			$this->view->set('clickmodule', array('module'=>'purchase_order'));
			$this->view->set('clickcontroller', 'poproductlineheaders');
			$this->view->set('clickaction', 'viewbydates');
			$this->view->set('linkvaluefield', 'ststructure_id');
			$this->view->set('no_ordering',true);
			$this->view->set('page_title','Pre-Order Requirements');
		} else {
			$flash = Flash::Instance();
			$flash->addError('Missing stock item');
			sendTo('stitems', 'index', 'manufacturing');
		}

	}

	public function printPreorder ($status = 'generate')
	{
        // build options array
        $options = array(
            'type' => array(
                'pdf' => '',
                'xml' => ''
            ),
            'actions' => array(
                'view' => '',
                'print' => '',
                'email' => ''
            ),
            'filename' => 'preorder' . fix_date(date(DATE_FORMAT)),
            'report' => 'ItemPreorderList'
        );

        if (strtolower($status) == "dialog") {
            return $options;
		}

		$extra = [];
		$id = $this->_data['id'];

		$stitem = new STItem();
		$stitem->load($id);
		$extra['item'] = $stitem->getIdentifierValue();
		$extra['qty'] = $this->_data['qty'];
		$extra['uom'] = $stitem->uom_name;

		$bom = MFStructureCollection::getCurrent($id);
		foreach ($bom as $item) {
			$item->qty = $item->qty * $this->_data['qty'];
		}

		$options['xmlSource'] = $this->generateXML(array(
			'model' => $bom,
			'extra' => $extra
		));

		echo $this->generate_output($this->_data['print'], $options);
		
		exit();
	}

	public function showParts()
	{
		parent::index(new MFStructureCollection());
	}

	public function view()
	{
		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$transaction = $this->_uses[$this->modeltype];

		$id = $this->_data['id'];

		$this->view->set('transaction',$transaction);

		$item = DataObjectFactory::Factory('STItem');

		$item->load($transaction->stitem_id);

		$obsolete = $item->isObsolete();

		$active = $transaction->isActive();

		$this->view->set('showform', ((!$obsolete) && ($active)));

		if ((!$obsolete) && ($active))
		{
			$this->_new();
			$substitute = $transaction->getSubstitute();
			$uom_list = array();
			$substitute_used = false;
			if ($substitute)
			{
				// There is a future dated substitution, so get the details of it
				$ststructure = DataObjectFactory::Factory('STItem');
				$ststructure->load($substitute->ststructure_id);
				if ($ststructure)
				{
					$uom_temp_list = STuomconversion::getUomList($substitute->ststructure_id, $ststructure->uom_id);
					$uom_temp_list += SYuomconversion::getUomList($ststructure->uom_id);

					$uom = DataObjectFactory::Factory('STuom');
					$uom->load($ststructure->uom_id);

					$uom_list[$ststructure->uom_id] = $uom->getUomName();
					$uom_list += $uom_temp_list;

					$this->view->set('uom_id', $ststructure->uom_id);
				}

				$wostructures = new MFWOStructureCollection();

				$sh = new SearchHandler($wostructures);
				$sh->addConstraint(new Constraint('ststructure_id', '=', $substitute->id));

				$wostructures->load($sh);
				$substitute_used = ($wostructures->count() > 0);
				$this->view->set('uom_list', $uom_list);
			}
			else
			{
				$substitute = DataObjectFactory::Factory('MFStructure');
			}

			$this->view->set('substitute', $substitute);

		}

		$sidebar = new SidebarController($this->view);

		$sidebar->addList(
			'This Parent Item',
			array(
				'view' => array('tag' => 'Show Structure'
							   ,'link' => array('modules'=>$this->_modules
											   ,'controller'=>$this->name
											   ,'action'=>'index'
											   ,'stitem_id'=>$transaction->stitem_id
											   )
								)
				 )
			);

		$sidebar->addList(
			'This Part Item',
			array(
				'view' => array('tag' => 'Show Structure'
							   ,'link' => array('modules'=>$this->_modules
											   ,'controller'=>$this->name
											   ,'action'=>'index'
											   ,'stitem_id'=>$transaction->ststructure_id
											   )
								)
				 )
			);

		$sidebarlist = array();

		if ((!$obsolete) && ($active))
		{
			$sidebarlist['edit'] = array('tag' => 'Edit'
										,'link' => array('modules'=>$this->_modules
														,'controller'=>$this->name
														,'action'=>'edit'
														,'id'=>$id
														,'stitem_id'=>$transaction->stitem_id
														,'ststructure_id'=>$transaction->ststructure_id
														)
										);
		}

		if ((!$obsolete) && (!$transaction->end_date))
		{
			$sidebarlist['delete'] = array('tag' => 'End Today'
										  ,'link' => array('modules'=>$this->_modules
														  ,'controller'=>$this->name
														  ,'action'=>'delete'
														  ,'id'=>$id
														  ,'stitem_id'=>$transaction->stitem_id
														  )
										  );
		}

		$sidebarlist['item'] = array('tag' => 'Show Parent Item'
									,'link' => array('modules'=>$this->_modules
													,'controller'=>'STItems'
													,'action'=>'view'
													,'id'=>$transaction->stitem_id
													)
										);

		$sidebarlist['structure'] = array('tag' => 'Show Part Item'
										 ,'link' => array('modules'=>$this->_modules
														 ,'controller'=>'STItems'
														 ,'action'=>'view'
														 ,'id'=>$transaction->ststructure_id
														 )
										);

		$sidebar->addList('Current Structure',$sidebarlist);

		if ((!$obsolete) && ($active) && ($substitute->isLoaded()))
		{
			$sidebarlist = array();

			if (!$substitute_used)
			{
				$sidebarlist['delete'] = array('tag' => 'Delete'
											  ,'link' => array('modules'=>$this->_modules
															  ,'controller'=>$this->name
															  ,'action'=>'deleteSubstitute'
															  ,'id'=>$id
															  ,'stitem_id'=>$transaction->stitem_id
															  )
											  );
			}
			$sidebar->addList('New Structure',$sidebarlist);
		}

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

	}

	protected function getPageName($base=null,$action=null)
	{
		return parent::getPageName((empty($base)?'product_structures':$base), $action);
	}

	public function getItems($_date='', $_stitem_id='')
	{
// Used by Ajax to return Items list after selecting the Start Date

		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['date'])) { $_date=$this->_data['date']; }
			if(!empty($this->_data['stitem_id'])) { $_stitem_id=$this->_data['stitem_id']; }
		}

		$items_list = array();

		if (!preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $_date, $regs))
		{
			$items_list = STItem::nonObsoleteItems();
		}
		else
		{
			list(, $day, $month, $year) = $regs;
			$date = strtotime($year.'/'.$month.'/'.$day);
			$items_list = STItem::nonObsoleteItems($date);
		}

		if (!empty($_stitem_id)) {
			unset($items_list[$_stitem_id]);
		}

		if(isset($this->_data['ajax'])) {
			$this->view->set('options',$items_list);
			$this->setTemplateName('select_options');
		}
		else
		{
			return $items_list;
		}
	}
}

// End of MfstructuresController
