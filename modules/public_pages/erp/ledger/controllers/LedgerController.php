<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class LedgerController extends printController
{

	protected $version = '$Revision: 1.9 $';

	public function delete($modelName = null)
	{
		if (!$this->CheckParams($this->_templateobject->idField))
		{
			sendBack();
		}

		parent::delete($this->modeltype);

		sendTo($this->name,'index',$this->_modules);
	}

	public function getCentres($_id = '')
	{
// Used by Ajax to return Centre list after selecting the Account
		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['id'])) { $_id = $this->_data['id']; }
		}

		if(!empty($_id))
		{
			$account = DataObjectFactory::Factory('GLAccount');
			$account->load($_id);
			$centres = $account->getCentres();
		}
		else
		{
			$centres = array();
		}

		if(isset($this->_data['ajax']))
		{
			$this->view->set('options', $centres);
			$this->setTemplateName('select_options');
		}
		else
		{
			return $centres;
		}

	}

	public function save($modelName = null, $dataIn = [], &$errors = []) : void
	{

		if (!$this->CheckParams($this->modeltype))
		{
			sendBack();
		}

		$flash = Flash::Instance();

		if(parent::save($this->modeltype))
		{
			sendTo($_SESSION['refererPage']['controller']
				  ,$_SESSION['refererPage']['action']
				  ,$_SESSION['refererPage']['modules']
				  ,$_SESSION['refererPage']['other'] ?? null);
		}

		$this->refresh();

	}

	protected function save_model ($modelName, $dataIn = array(), &$errors = array())
	{
		return parent::save($modelName, $dataIn, $errors);
	}

}

// End of LedgerController
