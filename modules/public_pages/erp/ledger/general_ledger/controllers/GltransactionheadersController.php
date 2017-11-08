<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class GltransactionheadersController extends printController
{

	protected $version = '$Revision: 1.5 $';

	protected $_templateobject;

	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);

		$this->_templateobject = DataObjectFactory::Factory('GLTransactionHeader');

		$this->uses($this->_templateobject);

	}

	public function index()
	{

		$id = (isset($this->_data['id']))?$this->_data['id']:0;

		$type = (isset($this->_data['transtype']))?$this->_data['transtype']:'';

		$errors = array();

		$defaults = array();

		$this->setSearch('gltransactionheadersSearch', 'useDefault', $defaults);

		$this->view->set('clickaction', 'view');

		parent::index(new GLTransactionHeaderCollection($this->_templateobject));

		$sidebar = new SidebarController($this->view);
		$sidebarlist = array();

		$sidebarlist['newjournal'] = array(
					'tag'	=> 'New Journal',
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> $this->name
									,'action'		=> 'new'
									)
					);

		$sidebar->addList('Actions',$sidebarlist);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

	}

	public function delete()
	{
		if (!$this->loadData())
		{
			$this->dataError('Error getting GL Journal Header');
			sendBack();
		}

		$header = &$this->_uses[$this->modeltype];

		if (!$header->isloaded())
		{
			$this->dataError('Error getting GL Journal Header');
			sendBack();
		}

		$errors = array();

		if ($header->delete($errors))
		{
			sendTo($this->name,'index',$this->_modules);
		}

		$flash  =Flash::Instance();

		$flash->addErrors($errors);

		sendBack();

	}

	public function _new()
	{

		parent::_new();

		if (isset($this->_data['transaction_date']))
		{
			$trandate	 = fix_date($this->_data['transaction_date']);
			$period_text = $this->getPeriod($trandate);
		}
		else
		{
			$period = DataObjectFactory::Factory('GLPeriod');
			$period->getCurrentPeriod();

			$trandate	 = $period->enddate;
			$period_text = $period->getIdentifierValue();
		}

		$this->view->set('transaction_date',$trandate);
		$this->view->set('period',$period_text);
		$this->view->set('periods',$this->getPeriods(un_fix_date($trandate)));

	}

    /**
     * Edit GL Transaction Header
     *
     * {@inheritdoc}
     *
     * @see Controller::edit()
     */
    public function edit()
    {
        parent::edit();

        $period = DataObjectFactory::Factory('GLPeriod');
        $period->load($this->_templateobject->_data['glperiods_id']);
        $trandate = $this->_templateobject->_data['transaction_date'];
        $period_text = $period->getIdentifierValue();

        $this->view->set('period', $period_text);
        $this->view->set('periods', $this->getPeriods(un_fix_date($trandate)));
    }

	/***
     * Save GLTransactionHeader
     *
     * Save header and transactions.
     *
	 */
	public function save()
	{
		if (!$this->checkParams($this->modeltype))
		{
			$this->dataError();
			sendBack();
		}

		$flash = Flash::Instance();

		$errors = array();

        // Form data validation
        $period = DataObjectFactory::Factory('GLPeriod');
        $period = $period->loadPeriod($this->_data[$this->modeltype]['transaction_date']);
        if (! isset($period->_data)) {
            $flash->addError('Invalid GL Period, header not saved');
            sendBack();
        }

        if ($period->closed == 't') {
            $flash->addError("{$period->year} - period {$period->period} is closed, header not saved");
            sendBack();
        }

        $valid_period_identifier[] = $period->getIdentifierValue();
        $valid_period_identifier[] = $this->getPeriod($this->_data[$this->modeltype]['transaction_date']);
        if (! in_array($this->_data[$this->modeltype]['period'], $valid_period_identifier)) {
            $flash->addError('Wrong period for this transaction date, header not saved');
            sendBack();
        }

        $accural_period_id = $this->_data[$this->modeltype]['accrual_period_id'];
        $valid_accrual_periods = $this->getPeriods($this->_data[$this->modeltype]['transaction_date']);
        if ($this->_data[$this->modeltype]['accrual'] === 'on' && ! isset($accural_period_id) && ! isset($valid_accrual_periods[$accural_period_id])) {
            $flash->addError('Accrual period invalid, header not saved');
            sendBack();
        }

		// Save model
		if (empty($errors) && parent::save($this->modeltype, $this->_data[$this->modeltype], $errors))
		{
			$flash->addMessage("GL Journal Header saved successfully");

			if (isset($this->_data['saveadd']))
			{
				sendTo($this->name, 'new', $this->_modules);
			}
			else
			{
				sendTo($this->name, 'view', $this->_modules, array('id'=>$this->saved_model->id));
			}
		}

		$flash->addErrors($errors);
		$this->refresh();

	}

	/***
     *	The view function is used to generate an overview of a particular transaction.
     *
     */

	public function view()
	{
		$header = $this->_uses[$this->modeltype];

		if (isset($this->_data['id']))
		{
			$header->load($this->_data['id']);
		}
		elseif (isset($this->_data['docref']))
		{
			$header->loadBy('docref', $this->_data['docref']);
		}

		if (!$header->isLoaded())
		{
			$this->dataError('Error loading journal header');
			sendBack();
		}

		$this->view->set('gltransactions_header', $header);

		$header->setTransactionsCollection();

		$sh = $this->setSearchHandler($header->transactions);

		$sh->setOrderby('created');

		$header->setTransactionsConstraints($sh);

		parent::index($header->transactions, $sh);

		if ($header->isUnposted())
		{
			$this->view->set('gltransactions', $header->transactions);
			$this->view->set('status', 'unposted');
			$this->view->set('clickaction', 'edit');

			$values = $header->transactionValue();
			$this->view->set('credits', number_format(bcmul($values['credits'], -1), 2));
			$this->view->set('debits', number_format(bcadd($values['debits'] ,0), 2));
			$this->view->set('difference', number_format(bcadd($values['credits'], $values['debits']), 2));
		}
		else
		{
			$this->view->set('clickaction', 'view');
		}

		$this->view->set('clickcontroller', 'gltransactions');

		$sidebar = new SidebarController($this->view);

		$sidebarlist = array();

		$sidebarlist['viewheaders'] = array(
					'tag'	=> 'View All Headers',
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> $this->name
									,'action'		=> 'index'
									)
					);

		if ($header->isUnposted())
		{
			$sidebarlist['edit'] = array(
						'tag'	=> 'Edit this header',
						'link'	=> array('modules'			=> $this->_modules
										,'controller'		=> $this->name
										,'action'			=> 'edit'
										,$header->idField	=> $header->{$header->idField}
						)
				);

			// Check unposted journal - journal lines must exist and sum to zero
			$TransCheck = $header->checkUnpostedTransactions();

			if ($header->isStandardJournal() && $TransCheck['sum'] == 0 && $TransCheck['count'] > 0)
			{
				$sidebarlist['post'] = array(
						'tag'	=> 'Post Journal',
						'link'	=> array('modules'			=> $this->_modules
										,'controller'		=> $this->name
										,'action'			=> 'post'
										,$header->idField	=> $header->{$header->idField}
						)
				);
			}
			elseif ($header->isTemplateJournal() && !$header->isAccrual() && $TransCheck['sum'] == 0 && $TransCheck['count'] > 0)
			{
				$sidebarlist['createandpost'] = array(
						'tag'	=> 'Create and Post Journal',
						'link'	=> array('modules'			=> $this->_modules
										,'controller'		=> $this->name
										,'action'			=> 'create_and_post'
										,$header->idField	=> $header->{$header->idField}
						)
				);

			}

			$sidebarlist['addtransaction'] = array(
						'tag'	=> 'Add_Journal_Line',
						'link'	=> array('modules'		=> $this->_modules
										,'controller'	=> 'gltransactions'
										,'action'		=> 'new'
										,'header_id'	=> $header->{$header->idField}
						)
				);
		}

		if ($header->isUnposted())
		{
			$sidebarlist['delete'] = array(
						'tag'	=> 'Delete',
						'link'	=> array('modules'			=> $this->_modules
										,'controller'		=> $this->name
										,'action'			=> 'delete'
										,$header->idField	=> $header->{$header->idField}
						)
				);
		}

		$sidebarlist['clone'] = array(
						'tag'	=> 'Create New From This',
						'link'	=> array('modules'			=> $this->_modules
										,'controller'		=> $this->name
										,'action'			=> 'create_from_existing'
										,$header->idField	=> $header->{$header->idField}
						)
				);

		if ($header->isPosted() && $header->accrual === 'f')
		{
			$sidebarlist['reverse'] = array(
						'tag'	=> 'Reverse This',
						'link'	=> array('modules'			=> $this->_modules
										,'controller'		=> $this->name
										,'action'			=> 'create_from_existing'
										,$header->idField	=> $header->{$header->idField}
										,'reverse'			=> 'yes'
				)
			);
		}

		$sidebar->addList('Actions',$sidebarlist);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

	}

	/***
     *	The create_from_existing function creates a new unposted journal from
     *  the selected template or posted journal.
     *
     */

	public function create_from_existing()
	{

		if (!$this->loadData())
		{
			$this->dataError('Error loading journal header');
			sendBack();
		}

		$header = $this->_uses[$this->modeltype];

		$errors = array();

		$flash = Flash::Instance();

		$db = DB::Instance();

		$db->StartTrans();

		// Create new header from selected header
		$newheader = $this->create_standard($header, $errors);

		if ($newheader)
		{
			// now set header to saved model and go to view the new header
			$header = $newheader;
		}
		else
		{
			// had an error so go back to view the original header and error
			$flash->addErrors($errors);
			$db->FailtTrans();
		}

		$db->completeTrans();

		sendTo($this->name, 'view', $this->_modules, array($header->idField	=> $header->{$header->idField}));
	}

	/***
     *	The post function saves all the unposted transactions to the general ledger
     *  and deletes the unposted transactions.
     *
     */

	public function post()
	{

		if (!$this->loadData())
		{
			$this->dataError('Error loading journal header');
			sendBack();
		}

		$header = $this->_uses[$this->modeltype];

		$errors = array();

		$flash = Flash::Instance();

		if ($header->post($errors))
		{
			$flash->addMessage("GL Journal posted successfully");
		}
		else
		{
			$flash->addErrors($errors);
		}

		sendTo($this->name, 'view', $this->_modules, array($header->idField	=> $header->{$header->idField}));
	}

	/*
	 * The create_and_post applies only to template journals; it will create a new standard
	 * journal (as in create_from_existing) and then post it.
	 */
	public function create_and_post()
	{
		if (!$this->loadData())
		{
			$this->dataError('Error loading journal header');
			sendBack();
		}

		$header = $this->_uses[$this->modeltype];

		$flash = Flash::instance();

		$errors = array();

		$db = DB::Instance();

		$db->StartTrans();

		$newheader = $this->create_standard($header, $errors);

		if (empty($errors) && $newheader && $newheader->post($errors))
		{
			$header = $newheader;
		}
		else
		{
			$flash->addErrors($errors);
			$db->FailtTrans();
		}

		$db->completeTrans();

		sendTo($this->name, 'view', $this->_modules, array($header->idField	=> $header->{$header->idField}));
	}

	protected function getPageName($base = null, $type = null)
	{
		return parent::getPageName('general_ledger_transaction_header');
	}

	public function getPeriods($_trandate='')
	{
// Used by Ajax to return Future Periods list after changing the transaction date

		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['id'])) { $_trandate=$this->_data['id']; }
		}

		$period = DataObjectFactory::Factory('GLPeriod');

		$current = $period->getPeriod(fix_date($_trandate));

		$periods = $period->getFuturePeriods($current['period'], $current['year']);

		if(isset($this->_data['ajax']))
		{
			$this->view->set('options',$periods);
			$this->setTemplateName('select_options');
		}
		else
		{
			return $periods;
		}
	}

	public function getPeriod($_trandate='')
	{

		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['id'])) { $_trandate=$this->_data['id']; }
		}

		$current = GLPeriod::getPeriod(fix_date($_trandate));

		if(isset($this->_data['ajax']))
		{
			$this->view->set('value',$current['year'].' - period '.$current['period']);
			$this->setTemplateName('text_inner');
		}
		else
		{
			return $current['year'].' - period '.$current['period'];
		}

	}

	/*
	 * Private functions
	 */
	private function create_standard($header, $errors = array())
	{
		if (isset($this->_data['reverse']) && $this->_data['reverse']=='yes')
		{
			$reversal = true;
		}

		// Create new header from selected header
		$newheader = array();

		foreach ($header->getFields() as $fieldname=>$field)
		{
			$newheader[$fieldname] = $header->$fieldname;
		}

		unset($newheader['id']);
		unset($newheader['docref']);

		$period = DataObjectFactory::Factory('GLPeriod');
		$period->getCurrentPeriod();

		if ($period->closed === 't'){
		    $flash->addError('Failed to create header, period is closed');
		    sendback();
		}

		$newheader['transaction_date'] = un_fix_date($period->enddate);

		$newheader['glperiods_id'] = $period->id;

		if ($reversal)
		{
			$newheader['comment'] = 'Reversal of Ref:' . $header->docref . ' ' . $newheader['comment'];

			if ($newheader['glperiods_id']==$header->glperiods_id)
			{
				$newheader['glperiods_id'] = key($this->getPeriods($newheader['transaction_date']));
			}
		}
		elseif ($newheader['accruals'] == 't')
		{
			$newheader['accrual_period_id'] = key($this->getPeriods($newheader['transaction_date']));
		}

		$newheader['type'] = $header->standardJournal();

		// Save the header and transactions
		$result = parent::save($this->modeltype, $newheader, $errors);

		if ($result)
		{
			// Copy existing header journal transactions to unposted transactions table
			$header->setTransactionsCollection();

			$sh = $this->setSearchHandler($header->transactions);

			$sh->setFields('*');

			// Ignore any reverse accruals when copying posted transactions
			$header->setTransactionsConstraints($sh, TRUE);

			$transactions = $header->transactions->load($sh, null, RETURN_ROWS);

			$docref = $this->saved_model->docref;

			foreach ($transactions as $row)
			{
				unset($row['id']);

				$row['docref'] = $docref;

				if ($reversal)
				{
					$row['value'] = bcmul($row['value'], -1);
				}

				$result = DataObject::Factory($row, $errors, $header->unpostedTransactionFactory());

				if (!$result)
				{
					break;
				}
				else
				{
					$newtrans[] = $result;
				}
			}

			if ($result)
			{
				foreach ($newtrans as $unposted)
				{
					if (!$unposted->save())
					{
						$result = FALSE;
						$errors[] = 'Error creating journal transactions : '.$db->ErrorMsg();
						break;
					}
				}
			}
		}

		if ($result)
		{
			// now set header to saved model and go to view the new header
			return $this->saved_model;
		}

		return FALSE;
	}
}

// End of GltransactionheadersController
