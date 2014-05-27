<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ComplaintsController extends printController
{
	
	protected $version = '$Revision: 1.5 $';
	
	public function save()
	{
		
		$flash = Flash::Instance();
		
		// if complete date has been set, unset the assigned to field
		if ($this->_data[$this->modeltype]['date_complete'] != '')
		{
			$this->_data[$this->modeltype]['assignedto'] = '';
		}
		
		// if the supplmentary complaint code isn't set, force null value
		if (!isset($this->_data[$this->modeltype]['supplementary_code_id']))
		{
			$this->_data[$this->modeltype]['supplementary_code_id'] = '';
		}
		
		if (parent::save($this->modeltype))
		{
			
			if ($this->_data['save'] == 'Save and Reload')
			{
				sendTo($this->name, 'edit', $this->_modules, array('id' => $this->saved_model->id));
			}
			else
			{
				sendTo($this->name, 'index', $this->_modules);
			}
			
		}
		else
		{
			$this->refresh();
		}
		
	}
	
	/*
	 * Ajax functions
	 */
	
	public function getSuppComplaintCodes($id = '')
	{
		
		//  Used by Ajax to return list of Supplementary Complaint Codes after selecting the complaint code

		if (isset($this->_data['ajax']))
		{
			if (!empty($this->_data['id'])) { $_id = $this->_data['id']; }
		}
		
		$scclist = array();
		
		if ($_id != '')
		{
			
			$scc	= DataObjectFactory::Factory('SupplementaryComplaintCode');
			$cc		= new ConstraintChain();
			$cc->add(new Constraint('complaint_code_id', '=', $_id));
			
			$list = $scc->getAll($cc);
			
		}

		if (isset($this->_data['ajax']))
		{
			$this->view->set('options', $list);
			$this->setTemplateName('select_options');
		}
		else
		{
			return $list;
		}

	}
	
	/*
	 * output functions
	 */
	
	public function printComplaint($_status = 'generate', $_filename)
	{
		
		// build xml vars array
		$glparams	= DataObjectFactory::Factory('GLParams');
		$currency	= $glparams->base_currency_symbol();
		$xslVars	= array('currency' => $currency);
		
		// build options array
		$options = array(
			'type' => array(
				'pdf' => '',
				'xml' => ''
			),
			'output' => array(
				'print'	=> '',
				'save'	=> '',
				'email'	=> '',
				'view'	=> ''
			),
			'filename'	=> $_filename,
			'report'	=> 'QC_ComplaintForm',
			'xslVars'	=> $xslVars
		);
		  	
		if (strtolower($_status) == "dialog")
		{
			return $options;
		}
		
		// load the model
		$complaint = $this->_templateobject;
		$complaint->load($this->_data['id']);
		
		// generate the xml and add it to the options array
		$options['xmlSource'] = $this->generateXML(array('model' => $complaint));
		
		// execute the print output function, echo the returned json for jquery
		echo $this->constructOutput($this->_data['print'], $options);
		exit;
		
	}
	
}

// end of SdcomplaintsController.php