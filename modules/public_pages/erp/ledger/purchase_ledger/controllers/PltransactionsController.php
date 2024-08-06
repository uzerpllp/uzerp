<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PltransactionsController extends printController
{

	protected $version = '$Revision: 1.27 $';

	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);

		$this->_templateobject = DataObjectFactory::Factory('PLTransaction');

		$this->uses($this->_templateobject);

// Define parameters for bulk output Remittances
// used by select_for_output function
		$this->output_types = array(
			'remittance' => array(
								'search_do'			=> '',
								'search_method'		=> '',
								'search_defaults'	=> array(),
								'collection'		=> 'PLTransactionCollection',
								'collection_fields'	=> array('id','payee_name','currency','email_remittance as email'),
								'display_fields'	=> array('payee_name','currency','email'),
								'identifier'		=> 'payee_name',
								'title'				=> 'Select Remittances for ',
								'filename'			=> 'Remittance',
								'printaction'		=> 'print_supplier_remittances'
								)
			);

	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{
		$this->view->set('clickaction', 'view');

		$s_data = array();

// Set context from calling module
		if (isset($this->_data['plmaster_id']))
		{
			$s_data['plmaster_id'] = $this->_data['plmaster_id'];
		}

		if (isset($this->_data['status']))
		{
			$s_data['status'] = $this->_data['status'];
		}

		$this->setSearch('pltransactionsSearch', 'useDefault', $s_data);

		$transaction_date = $this->search->getValue('transaction_date');

		if (isset($transaction_date['from']))
		{
			$from_date = fix_date($transaction_date['from']);
		}
		else
		{
			$from_date = '';
		}

		if (isset($transaction_date['to']))
		{
			$to_date = fix_date($transaction_date['to']);
		}
		else
		{
			$to_date = '';
		}

		parent::index(new PLTransactionCollection($this->_templateobject));

	}

	public function view()
	{

		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$transaction=$this->_uses[$this->modeltype];

		$this->view->set('transaction',$transaction);

		$sidebar = new SidebarController($this->view);

		$sidebarlist=array();

		$sidebarlist['viewaccounts'] = array(
							'tag'=>'View All Suppliers',
							'link'=>array('modules'		=>$this->_modules
										 ,'controller'	=>'plsuppliers'
										 ,'action'		=>'index'
										 )
				);

		$sidebarlist['gldetail'] = array(
							'tag'=>'View GL Detail',
							'link'=>array('module'		=>'general_ledger'
										 ,'controller'	=>'gltransactions'
										 ,'action'		=>'index'
										 ,'docref'		=>$transaction->our_reference
										 ,'source'		=>'P'
										 ,'glperiods_id'=>'0'
										 )
				);

		$sidebar->addList('Actions',$sidebarlist);

		$this->view->register('sidebar',$sidebar);

		$this->view->set('sidebar',$sidebar);
	}

	protected function getPageName($base=null,$action=null) {
		return parent::getPageName((empty($base)?'purchase_ledger_transactions':$base), $action);
	}

	public function select_remittances ()
	{
		$flash=Flash::Instance();

		$errors=array();

		if (!$this->checkParams('id'))
		{
			sendBack();
		}
		else
		{
			$plpayment =  DataObjectFactory::Factory('PLPayment');

			$plpayment->load($this->_data['id']);

			if (!$plpayment)
			{
				$errors[] = 'Error trying to get Payment Details';
			}
			else
			{
				$pltransactions = new PLTransactionCollection();

				// Get payments for suppliers that require a remittance advice
				$pltransactions->paidList($plpayment->id, true);

				foreach ($pltransactions as $pltransaction) {
					$pltransaction->gross_value = bcmul($pltransaction->gross_value,-1);
					$pltransaction->setAdditional('email');
					$pltransaction->email=$pltransaction->email_remittance;
				}
			}
		}

		$type = 'remittance';

		$output_details = $this->output_types[$type];

		$selected = empty($_SESSION['selected_output'][$type])?array():$_SESSION['selected_output'][$type];

		$new_selection = array('count'=>0);

		foreach ($pltransactions as $detail)
		{
			if (isset($selected[$detail->id]))
			{
				$new_selection[$detail->id] = $selected[$detail->id];

				if ($selected[$detail->id]['select'] == 'true')
				{
					$new_selection['count']++;
				}
			}
			else
			{
				if (isset($output_details['identifier']))
				{
					$new_selection[$detail->id]['description'] = prettify($output_details['identifier']).' : '.$detail->{$output_details['identifier']};
				}
				else
				{
					$new_selection[$detail->id]['description'] = $detail->getIdentifierValue();
				}

				if (empty($new_selection[$detail->id]['description']))
				{
					$new_selection[$detail->id]['description'] = 'no identifier set';
				}

				if (!is_null($detail->method))
				{
					switch ($detail->method)
					{
						case 'E':
							$new_selection[$detail->id]['printaction'] = 'email';
							break;
						case 'D':
							$new_selection[$detail->id]['printaction'] = 'edi';
							break;
						case 'F':
						case 'P':
							$new_selection[$detail->id]['printaction'] = 'print';
							break;
						default :
							$new_selection[$detail->id]['printaction'] = strtolower($detail->method);
					}
				}
				elseif ($detail->email == '')
				{
					$new_selection[$detail->id]['printaction'] = 'print';
				}
				else
				{
					$new_selection[$detail->id]['printaction'] = 'email';
				}

				$new_selection[$detail->id]['email'] = $detail->email;

				if ($detail->remittance_advice)
				{
					$new_selection[$detail->id]['select'] = 'true';
					$new_selection['count']++;
				}
				else
				{
					$new_selection[$detail->id]['select'] = 'false';
				}
			}
		}

		$_SESSION['selected_output'][$type] = $selected = $new_selection;


		$this->view->set('type', 'remittance');
		$this->view->set('fields', array('payee_name', 'gross_value', 'currency', 'payment_type', 'email'));
		$this->view->set('clickaction', 'view');
		$this->view->set('no_ordering', true);
		$this->view->set('collection', $pltransactions);

		$this->_data['printaction']	= $output_details['printaction'];
		$this->_data['filename']	= 'Remittance';

		unset($this->printaction['view']);

		$this->printAction();
		$this->view->set('page_title', $this->getPageName("", 'Print Remitttances for'));

		$this->view->set('selected_output', $selected);
		$this->view->set('count_selected', $selected['count']);
		$this->view->set('type_id', $this->_data['id']);

		foreach ($this->_modules as $key=>$value)
		{
			$modules[] = $key.'='.$value;
		}

		$link = implode('&', $modules).'&controller='.$this->name.'&action=adjust_selection&type='.$type;

		$this->view->set('link',$link);

		$this->setTemplateName('select_for_output');

	}

	public function printDialog()
	{
		// override printDialog, set supplier email
		if (strtolower((string) $this->_data['printaction'])=='print_single_remittance')
		{
			if (!$this->checkParams('id') || !$this->loadData())
			{
				$this->dataError();
				sendBack();
			}

			$transaction = $this->_uses[$this->modeltype];

			$supplier = DataObjectFactory::Factory('PLSupplier');
			$supplier->load($transaction->plmaster_id);

			$this->view->set('email', $supplier->email_remittance());
		}

		parent::printDialog();

	}

	public function print_supplier_remittances($_transaction_id, $_print_params)
	{

		$this->_data['id']		= $_transaction_id;
		$this->_data['print']	= $_print_params;

		$response = json_decode((string) $this->print_single_remittance(), true);

		// bit paranoid about the data array being contaminated
		unset($this->_data['id'], $this->_data['print']);

		return $response;

	}

	public function print_single_remittance($status='generate')
	{

		// load the transaction first
		if (!$this->checkParams('id'))
		{
			$this->dataError();
			sendBack();
		}

		$transaction = DataObjectFactory::Factory($this->modeltype);
		$transaction->load($this->_data['id']);

		if (!$transaction->isLoaded())
		{
			$this->dataError();
			sendBack();
		}

		// build options array
		$options = array('type'		=>	array('pdf' => '',
											  'xml' => ''
										),
						 'output'	=>	array('print'	=> '',
					   						  'save'	=> '',
					   						  'email'	=> '',
					   						  'view'	=> ''
										),
						 'filename'	=>	'RM_PL'.$transaction->id,
						 'report'	=>	'Remittance'
				);

		if(strtolower((string) $status)=="dialog")
		{
			return $options;
		}

		$model=array();
		$extra=array();

		$transaction->getRemittance($this->_data['print'], $model, $extra);

		// generate the xml and add it to the options array
		$options['xmlSource'] = $this->generate_xml(array('model'				=> $model,
														  'extra'				=> $extra,
														  'load_relationships'	=> false
														  )
												 	);

		// execute the print output function, echo the returned json for jquery
		$result = $this->generate_output($this->_data['print'], $options);

		if (isset($this->_data['ajax_print']))
		{		
			echo $result;
			exit;
		}
		else
		{
			return $result;
		}

	}

	public function select_for_output()
	{
		if (!$this->checkParams('type'))
		{
			sendBack();
		}

		if ($this->_data['type']=='remittance')
		{
			if (!$this->checkParams('id'))
			{
				sendBack();
			}
			$this->select_remittances();
		}
		else
		{
			parent::select_for_output();
		}

	}

	public function view_allocations ()
	{

		$flash = Flash::Instance();

		$collection = new PLTransactionCollection($this->_templateobject);

		$this->_templateobject->setAdditional('payment_value', 'numeric');

		$allocation = DataObjectFactory::Factory('PLAllocation');

		$allocationcollection = new PLAllocationCollection($allocation);

		$collection->_tablename = $allocationcollection->_tablename;

		$sh = $this->setSearchHandler($collection);

		$fields = array("our_reference||'-'||transaction_type as id"
					 ,'supplier'
					 ,'plmaster_id'
					 ,'payee_name'
					 ,'transaction_date'
					 ,'transaction_type'
					 ,'our_reference'
					 ,'ext_reference'
					 ,'currency'
					 ,'currency_id'
					 ,'gross_value'
					 ,'allocation_date');

		$sh->setGroupBy($fields);

		$fields[] = 'sum(payment_value) as payment_value';

		$sh->setFields($fields);

		if (isset($this->_data['trans_id']))
		{
			$allocation->loadBy('transaction_id', $this->_data['trans_id']);

			if ($allocation->isLoaded())
			{
				$sh->addConstraint(new Constraint('allocation_id', '=', $allocation->allocation_id));
			}
			else
			{
				$flash->addError('Error loading allocation');
				sendBack();
			}
		}

		parent::index($collection, $sh);

		$this->view->set('collection', $collection);
		$this->view->set('invoice_module', 'purchase_invoicing');
		$this->view->set('invoice_controller', 'pinvoices');

		$this->view->set('clickaction', 'view');
		$this->view->set('clickcontroller', 'plsuppliers');
		$this->view->set('linkvaluefield', 'plmaster_id');
	}

}

// End of PltransactionsController
