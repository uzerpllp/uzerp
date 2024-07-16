<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class GLTransactionHeader extends DataObject
{

	protected $version = '$Revision: 1.4 $';

	protected $defaultDisplayFields = array('docref'			=> 'Doc.Ref'
											,'transaction_date'	=> 'Date'
											,'glperiod'			=> 'Period'
											,'accrual'			=> 'Accrual'
											,'accrual_period'	=> 'Reverse Period'
											,'reference'		=> 'Reference'
											,'comment'			=> 'Comment'
											,'status'			=> 'Status'
											,'type'				=> 'Type'
											);

	public $transactions;

	function __construct($tablename='gl_transactions_header')
	{
// Register non-persistent attributes

// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField			= 'id';
		$this->identifierField	= 'docref';
		$this->orderby			= 'created';
		$this->orderdir			= 'desc';

// Define relationships
		$this->belongsTo('GLPeriod', 'glperiods_id', 'glperiod');
		$this->belongsTo('GLPeriod', 'accrual_period_id', 'accrual_period');

// Define field formats

// Define validation

// Define enumerated types
		$this->setEnum('status', array('N' => 'New'
									  ,'O' => 'Posted'
									  ));

		$this->setEnum('type', ['S' => 'Standard',
								'Y' => 'Closing Balance',
								'T' => 'Template']);
	}

	function delete($id = null, &$errors = array(), $archive = FALSE, $archive_table = null, $archive_schema = null)
	{
		$db = DB::Instance();

		if (!$this->isUnposted())
		{
			$errors[] = 'Only unposted GL Journal Headers can be deleted';
			return FALSE;
		}

		$db->startTrans();

		$this->setTransactionsCollection();

		$sh = new SearchHandler($this->transactions, FALSE);

		$this->setTransactionsConstraints($sh);

		if ($this->transactions->delete($sh)===FALSE || !parent::delete($this->{$this->idField}, $errors))
		{
			$db->FailTrans();
		}

		return $db->CompleteTrans();
	}

	function transactionValue()
	{
		if ($this->isUnposted())
		{
			$transaction = $this->unpostedTransactionFactory();
		}
		else
		{
			return array();
		}

		$cc = new ConstraintChain();

		$cc->add($this->childConstraint());
		$cc->add(new Constraint('value', '>', 0));

		$debits = $transaction->getSum('value', $cc);

		$cc = new ConstraintChain();

		$cc->add($this->childConstraint());
		$cc->add(new Constraint('value', '<', 0));

		$credits = $transaction->getSum('value', $cc);

		return array('credits'=>$credits, 'debits'=>$debits);

	}

	static function Factory($data, &$errors = [], $do_name = __CLASS__)
	{
		$do = DataObjectFactory::Factory($do_name);

		$db = DB::Instance();

		if (empty($data['docref']))
		{
			$data['docref'] = $db->GenID('gl_transactions_docref_seq');
		}

		if ($data['type'] == 'Y') {
			$glperiod = GLPeriod::getYEPeriod($data['year']);
		} else {
			$glperiod = GLPeriod::getPeriod(fix_date($data['transaction_date']));
		}

		if ((!$glperiod) || (count($glperiod) == 0))
		{
			$errors[] = 'No period exists for this date';
		}

		$data['glperiods_id']	= $glperiod['id'];
		$data['status']			= $do->newStatus();

		return parent::Factory($data, $errors, $do);
	}

	function setTransactionsCollection()
	{
		$method = $this->getEnum('status', $this->status).'Transactions';

		$this->$method();
	}

	function setTransactionsConstraints($sh, $ignore_accruals = FALSE)
	{
		$method = $this->getEnum('status', $this->status).'Constraints';

		return $this->$method($sh, $ignore_accruals);
	}

	/**
	 * Post a Journal to the GL
	 * 
	 * For year-end closing journals, this also updates the
	 * period end balances for the period and period 0 of the
	 * following year.
	 *
	 * @param array $errors
	 * @return void
	 */
	public function post(&$errors = array())
	{
		$db = db::Instance();

		// Check it hasn't been posted!
		if (!$this->isUnposted())
		{
			$errors[] = 'Journal has already been posted';
			return FALSE;
		}

		// Check it is a standard or closing balance journal
		if (!$this->isStandardJournal() && !$this->isClosingBalanceJournal())
		{
			$errors[] = 'Template Journal cannot be posted';
			return FALSE;
		}

		// Get all un-posted transactions
		$this->NewTransactions();

		$sh = new SearchHandler($this->transactions, false);

		$sh->addConstraint($this->childConstraint());

		$unposted = $this->transactions->load($sh, null, RETURN_ROWS);

		// Save transactions to GL Transactions
		$db->startTrans();

		foreach ($unposted as $transaction)
		{
			unset($transaction['id']);

			$transaction['transaction_date'] = un_fix_date($this->transaction_date);
			$transaction['glperiods_id'] = $this->glperiods_id;

			GLTransaction::setTwinCurrency($transaction);

			$cp_override = false;
			if ($this->isClosingBalanceJournal()) {
				$cp_override = true;
			}
			$gltransaction = GLTransaction::Factory($transaction, $errors, $cp_override);

			if ($gltransaction == false || !$gltransaction->save())
			{
				$errors[] = 'Error saving journal transaction : '.$db->ErrorMsg();
			}
			elseif ($gltransaction == false || !$gltransaction->updateBalance($errors))
			{
				$errors[] = 'Error updating GL balance : '.$db->ErrorMsg();
			}

			if ($this->accrual == 't')
			{
				$transaction['glperiods_id'] = $this->accrual_period_id;
				$transaction['comment'] = 'Reverse '.$transaction['comment'];
				$transaction['value'] = bcmul($transaction['value'], -1);

				$gltransaction = GLTransaction::Factory($transaction, $errors);

				if ($gltransaction == false || !$gltransaction->save())
				{
					$errors[] = 'Error saving journal transaction : '.$db->ErrorMsg();
				}
				elseif ($gltransaction == false || !$gltransaction->updateBalance($errors))
				{
					$errors[] = 'Error updating GL balance : '.$db->ErrorMsg();
				}
			}
		}

		// Update GL Balances and GL Year End Balances
		if (count($errors) == 0 && $this->isClosingBalanceJournal()) {

			$period = new GLPeriod();
			$period->load($this->glperiods_id);

			$next_year = $period->year + 1;
			$next_period = new GLPeriod();
			$next_period->loadFirstPeriod($next_year);

			// Remove all the GLBalances for period 0
			$gl_balances = new GLBalanceCollection();
			$sh_bal=new SearchHandler(new GLBalanceCollection(), false);
			$periodsYTD='('.implode(',', [$next_period->id]).')';
			$sh_bal->addConstraint(new Constraint('glperiods_id', 'in', $periodsYTD));
			$gl_balances->delete($sh_bal);

			// Remove all the GLPeriodEndBalance(s) period 12
			$glpe_balances = new GLPeriodEndBalanceCollection();
			$sh_pe=new SearchHandler(new GLPeriodEndBalanceCollection(), false);
			$periodsYTD='('.implode(',', [$period->id]).')';
			$sh_pe->addConstraint(new Constraint('glperiods_id', 'in', $periodsYTD));
			$glpe_balances->delete($sh_pe);

			// Create GLPeriodEndBalance(s) for period p12
			$periodendbalances = new GLPeriodEndBalanceCollection(DataObjectFactory::Factory('GLPeriodEndBalance'));
			if ($periodendbalances->create($period) === FALSE)
			{
				$errors[] = 'Error creating period end balances';
			}

			// Re-run year-end at this period to re-create p0 b/f balances
			periodHandling::yearEnd($period, $errors, false);
		}

		// Update the header status if no errors so far
		if (empty($errors) && !$this->update($this->{$this->idField}, 'status', $this->postedStatus()))
		{
			$errors[] = 'Error updating journal header status : '.$db->ErrorMsg();;
		}

		// Delete the unposted transactions if posted OK
		if (count($errors) > 0 || !$this->transactions->delete($sh))
		{
			$errors[] = 'Error updating posted journals : '.$db->ErrorMsg();
			$db->FailTrans();
		}

		return $db->CompleteTrans();
	}

	function checkUnpostedTransactions()
	{
		$transaction = $this->unpostedTransactionFactory();

		$cc = new ConstraintChain();

		$cc->add($this->childConstraint());

		$sum = $transaction->getSum('value', $cc);

		$count = $transaction->getCount($cc);

		return array('sum' => $sum, 'count' => $count);
	}

	function newStatus()
	{
		return 'N';
	}

	function postedStatus()
	{
		return 'O';
	}

	function isPosted()
	{
		return ($this->status == $this->postedStatus());
	}

	function isUnposted()
	{
		return ($this->status == $this->newStatus());
	}

	function templateJournal()
	{
		return 'T';
	}

	function standardJournal()
	{
		return 'S';
	}

	function closingBalanceJournal()
	{
		return 'Y';
	}

	function isTemplateJournal()
	{
		return ($this->type == $this->templateJournal());
	}

	function isStandardJournal()
	{
		return ($this->type == $this->standardJournal());
	}

	function isClosingBalanceJournal()
	{
		return ($this->type == $this->closingBalanceJournal());
	}

	function isAccrual()
	{
		return ($this->accrual=='t');
	}

	public function unpostedTransactionFactory()
	{
		return DataObjectFactory::Factory('GLUnpostedTransaction');
	}

	/*
	 * Private Functions
	 */
	private function childConstraint()
	{
		// Applies to all types of transaction
		return new Constraint('docref', '=', $this->docref);
	}

	private function NewTransactions()
	{
		$this->transactions = new GLUnpostedTransactionCollection($this->unpostedTransactionFactory());
	}

	private function NewConstraints($sh, $ignore_accruals = FALSE)
	{
		// $ignore_accruals not relevant here
		if ($sh instanceof searchHandler)
		{
			$sh->addConstraint($this->childConstraint());

			$sh->setOrderby(array('created', 'account', 'cost_centre'));
		}
	}

	private function PostedTransactions()
	{
		$this->transactions = new GLTransactionCollection();
	}

	private function PostedConstraints($sh, $ignore_accruals = FALSE)
	{
		if ($sh instanceof searchHandler)
		{
			$sh->addConstraint($this->childConstraint());
			$sh->addConstraint(new Constraint('source', '=', 'G'));
			$sh->addConstraint(new Constraint('type', '=', 'J'));

			if ($ignore_accruals)
			{
				$sh->addConstraint(new Constraint('glperiods_id', '=', $this->glperiods_id));
			}
			$sh->setOrderby(array('glperiod', 'account', 'cost_centre'));
		}
	}

}

// End of GLTransactionHeader
