<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ProductionRecordingController extends printController
{

	protected $version = '$Revision: 1.3 $';
	
	public function delete()
	{
		
		if (!$this->CheckParams($this->_templateobject->idField))
		{
			sendBack();
		}

		parent::delete($this->modeltype);
		
		sendTo($this->name,'index',$this->_modules
			  ,$this->getOtherParams());

	}
	
	public function getCentres()
	{
		
		$mf_centre = DataObjectFactory::Factory('MFCentre');
		
		$cc = new ConstraintChain();
		
		$cc->add(New Constraint('production_recording', 'is', 'true'));
		
		return $mf_centre->getAll($cc);
	
	}
	
	public function save()
	{
		
		if (!$this->CheckParams($this->modeltype))
		{
			sendBack();
		}

		$flash = Flash::Instance();
		
		$errors = array();
		
		if(parent::save($this->modeltype, '', $errors))
		{
			sendTo($_SESSION['refererPage']['controller']
				  ,$_SESSION['refererPage']['action']
				  ,$_SESSION['refererPage']['modules']
				  ,isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
		}
		
		$flash->addErrors($errors);
		
		$flash->addError('Failed to save '.$this->modeltype);
		
		$this->refresh();
		
	}

	protected function save_model ($modelName, $dataIn = array(), &$errors = array())
	{
		return parent::save($modelName, $dataIn, $errors);
	}

}

// End of ProductionRecordingController
