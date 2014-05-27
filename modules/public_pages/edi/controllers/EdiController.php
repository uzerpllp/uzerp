<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class EdiController extends printController {
	
	protected $version='$Revision: 1.9 $';
	
	public function delete(){
		
		if (!$this->CheckParams($this->_templateobject->idField)) {
			sendBack();
		}

		parent::delete($this->modeltype);
		
		sendTo($this->name,'index',$this->_modules
			  ,$this->getOtherParams());

	}

	public function save() {
		
		if (!$this->CheckParams($this->modeltype)) {
			sendBack();
		}
		$flash=Flash::Instance();
		$errors=array();
		if(parent::save($this->modeltype, '', $errors)) {
			sendTo($_SESSION['refererPage']['controller']
				  ,$_SESSION['refererPage']['action']
				  ,$_SESSION['refererPage']['modules']
				  ,isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
		}
		
		$flash->addErrors($errors);
		$this->refresh();
		
	}

	public function save_model ($modelName, $dataIn=array(), &$errors=array(), &$warnings=array(), $duplicates='') {
		// Need to define error array here due to too many nested levels
		
		$flash=Flash::Instance();
		$edi_errors = array();
		
		if ($duplicates != 'R')
		{
			// Do not reject duplicates - so check if record exists
			$model = new $modelName;
			$identifierFields = $model->getIdentifierFields();
			$identifierValues = array();
			foreach ($identifierFields as $key=>$field)
			{
				if (isset($dataIn[$modelName][$field]) && $model->checkUniqueness($field))
				{
					$identifier_string = $field.' : '.$dataIn[$modelName][$field].',';
					$identifierValues[$key] = $dataIn[$modelName][$field];
				}
				else
				{
					unset($identifierFields[$key]);
				}
			}
			if (count($identifierFields) > 0 && count($identifierFields) == count($identifierValues))
			{
				$model->loadBy($identifierFields, $identifierValues);
				if ($model->isLoaded())
				{
					if ($duplicates == 'I')
					{
						// Ignore duplicates so return true
						$warnings[] = 'Duplicate  '.$identifier_string.' Ignored';
						return true;
					}
					else
					{
						// replace/update duplicate so set id field value
						$dataIn[$modelName][$model->idField] = $model->{$model->idField};
					}
				}
			}
		}

		$result = parent::save($modelName, $dataIn, $edi_errors);
		
		if (count($edi_errors)>0)
		{
			// Add the Identifier Values to the errors to identify the data in error
			// Assumes the $dataIn array is an array of [ModelName][ModelData]
			// and that the input $modelName is the identifying model for the data
			$model				= new $modelName;
			foreach ($model->getIdentifierFields() as $field)
			{
				$errors[] = prettify($field).' : '.$dataIn[$modelName][$field];
			}
			$flash->addErrors($edi_errors);

			foreach ($edi_errors as $error)
			{
				$errors[] = $error;
			}
		}
		
		return $result;
		
	}

}

// End of EdiController