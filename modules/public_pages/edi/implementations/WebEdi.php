<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class WebEdi extends EDIInterface {

	protected $version='$Revision: 1.4 $';
	protected $direction;
	protected $transfer_type;
	protected $username;
	protected $password;
	protected $root_location;
	protected $file_prefix;
	protected $process_model;
	protected $process_function;
	protected $id;
	protected $working_folder;
	protected $external_identifier_field;
	protected $name;
	protected $type;


	function getFileList (&$errors=array()) {

		$filelist=array();
		if ($this->direction=='IN') {

			switch (strtoupper((string) $this->transfer_type)) {
				case 'HTTP':
					$url = $this->transfer_type.'://'.$this->root_location.'/list_orders.php?user_id='.$this->username.'&magic_key='.$this->password.'&mode=new';
					$external_list = file_get_contents($url);
					if ($external_list === FALSE)
					{
						$errors[]='Error connecting to remote site to get file list';
					}
					elseif (!is_array($external_list) && (is_numeric($external_list) || strpos($external_list, ',')!==false)) {
						// single value or comma delimited string
						if (substr($external_list, -1)==',') {
							$external_list=substr($external_list, 0, -1);
						}
						$external_list=explode(',', $external_list);
						foreach ($external_list as $id)
						{
							$filelist[$id] = $this->file_prefix.$id.(!empty($this->file_extension)?'.'.strtolower($this->file_extension):'');
						}
						asort($filelist);
					}
					break;

				default:
					$errors[]=$this->transfer_type.' transfer type not supported';
			}

			return $filelist;
		}

// Direction is not 'IN'
		$extractlist=array();
		if (!is_null($this->process_model)
			&& !is_null($this->process_function)
			&& method_exists($this->process_model, $this->process_function))
		{
			if (strtolower((string) $this->process_model) == strtolower(get_class($this)))
			{
				$model=$this;
			}
			else
			{
				$model=new $this->process_model;
			}
			$extractlist=call_user_func(array($model, $this->process_function), $this->id);
			foreach ($extractlist as $key=>$value)
			{
				$extractlist[$key]=$this->file_prefix.$value.(!empty($this->file_extension)?'.'.strtolower($this->file_extension):'');
			}
		}
		return $extractlist;
	}

	function getFile ($logdata, $download=true, &$errors=array()) {

		$external_name	= $logdata['external_id'];
		$filename		= $logdata['name'];

		// Override the download - always get it
		$download=true;

		switch (strtoupper((string) $this->transfer_type)) {
			case 'HTTP':
				$filepath = DATA_ROOT.'company'.EGS_COMPANY_ID.DIRECTORY_SEPARATOR.$this->working_folder.DIRECTORY_SEPARATOR.$filename;		
				$url = $this->transfer_type.'://'.$this->root_location.'/get_order.php?magic_key='.$this->password.'&'.$this->external_identifier_field.'='.$external_name;
				$data = file_get_contents($url);
				if ($data && file_put_contents($filepath, $data)) {
					if ($download) {
						$url = $this->transfer_type.'://'.$this->root_location.'/mark_as_exported.php?magic_key='.$this->password.'&'.$this->external_identifier_field.'='.$external_name.'&exported=true';
						if (!file_get_contents($url)) {
							$errors[]='Error marking file as downloaded';
						}
					}
				} else {
					$errors[]='Error downloading file';
				}
				break;
			default:
				$errors[]=$this->transfer_type.' transfer type not supported';
		}

		if ($download)
		{

			$logdata['action'] = 'D';
			$this->writeLog($logdata, $errors);

			$logdata['action'] = 'AI';
			$this->writeLog($logdata, $errors);

		}

		if (count($errors)>0) {
			return false;
		} else {
			return true;
		}

	}

	function sendFile ($filename, &$errors=array()) {

		switch (strtoupper((string) $this->transfer_type)) {
			case 'HTTP':
				$filepath = DATA_ROOT.'company'.EGS_COMPANY_ID.DIRECTORY_SEPARATOR.$this->working_folder.DIRECTORY_SEPARATOR.$filename;
				$handle=fopen($filepath, 'r');
				$data=fgets($handle);
				$external_id=file_get_contents($this->transfer_type.'://'.$this->root_location.'/submit_invoice_header.php?magic_key='.$this->password.'&'.$data);
				$logdata['external_id']=$external_id;
				if (!is_numeric($external_id)) {
					$errors[]='Error uploading Invoice Header : '.$external_id;
					break;
				} else {
					$data=fgets($handle);
					while (!feof($handle)) {
						$result=file_get_contents($this->transfer_type.'://'.$this->root_location.'/submit_invoice_line.php?magic_key='.$this->password.'&'.$this->external_identifier_field.'='.$external_id.'&'.$data);
						if ($result != 'True') {
							$errors[]='Failed to upload Invoice '.$filename;
							break;
						}
						$data=fgets($handle);
					}
				}
				fclose($handle);
				break;
			default:
				$errors[]=$this->transfer_type.' transfer type not supported';
		}

		$logdata['name'] = $filename;
		$logdata['action'] = 'S';
		$this->writeLog($logdata, $errors);

		if (count($errors)>0) {
			return false;
		} else {
			return true;
		}

	}

	function exportFile ($filename, $data, &$errors=array()) {
		$defdetail=new DataDefinitionDetail();
		$defdetail->loadBy(array('data_definition_id', 'element'), array($this->id, $this->name));

		if (!$defdetail->isLoaded()) {
			$errors[]='Cannot find Data Definition for '.$this->name;
			return false;
		}

		if (is_null($defdetail->data_map->internal_type)) {
			$model=new $this->process_model;
		} else {
			$model=new $defdetail->data_map->internal_type;
		}

		if ($model instanceof DataObject) {
			$model->load($data);
//			if ($model->isLoaded() && $model->isField('print_count')) {
//				$model->print_count=$model->print_count+1;
//				$model->date_printed=date(DATE_FORMAT);
//				$model->save();
//			}
			$result=array();
			if ($model->isLoaded()) {
				$logdata=array('internal_id'=>$model->{$model->idField}, 'internal_identifier_field'=>$model->identifierField, 'internal_identifier_value'=>$model->getIdentifierValue());
			}
		} else {
			// This is a collection so need to load the collection for export
			// The id's are in the internal_code field of the data_mapping_details table
			// identified by the data_mapping_rule_id value from data_definition_detail
			$sh=new SearchHandler($model, false);
			if (!is_null($defdetail->data_mapping_rule_id)) {
				$datadetails=new DataMappingDetail();
				$datadetails->identifierField='internal_code';
				$cc=new ConstraintChain();
				$cc->add(new Constraint('data_mapping_rule_id', '=', $defdetail->data_mapping_rule_id));
				$keys=$datadetails->getAll($cc, true);
			} else {
				$keys=array();
			}
			if (!empty($keys)) {
				$sh->addConstraint(new Constraint('id', 'in', '('.implode(',', $keys).')'));
			}
			$model->load($sh);
			$result=array();
		}
		if (!$model) {
			$errors[]='Failed to extract data for '.$this->name;
			return false;
		}
		$array=array();
		if ($model instanceof DataObject) {
			$array=$this->createArray($defdetail, $model);
		} else {
			foreach ($model as $line) {
				$array=$this->createArray($defdetail, $line);
			}
		}
		$data='';
		switch ($this->type) {
			case 'HTTP':
				foreach ($array as $key=>$line) {
					if (is_array(current($line))) {
						foreach ($line as $subline) {
							$data.=http_build_query($subline)."\n";
						}
					} else {
						$data.=http_build_query($line)."\n";
					}
				}
				break;
			case 'XML':
				$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><'.$this->name.'/>');
				$data=array2xml($array, $xml);
				break;
			default:
				$errors[]=$this->type.' data type not supported';
		}

		$handle = fopen(DATA_ROOT.'company'.EGS_COMPANY_ID.DIRECTORY_SEPARATOR.$this->working_folder.DIRECTORY_SEPARATOR.$filename, 'w');		
		if (!$handle || !fwrite($handle, (string) $data)) {
			$errors[]='Error writing '.$filename.' to '.$this->working_folder;
		}

		$logdata['name']=$filename;
		$logdata['action']='E';
		$this->writeLog($logdata, $errors);

		if (count($errors)>0) {
			return false;
		}

		return true;
	}

	public function getInvoiceExportList ($_definition_id='') {
		$invoice=new SInvoice();
		$collection=new SInvoiceCollection($invoice);
		$cc=new ConstraintChain();
		$cc->add(new Constraint('transaction_type', '=', 'I'));
		$cc->add(new Constraint('status', '=', 'O'));
		$cc->add(new Constraint('despatch_date', 'is not', 'NULL'));
		$cc->add(new Constraint('print_count', '=', '0'));
		$cc->add(new Constraint('invoice_method', '=', 'D'));
		$cc->add(new Constraint('edi_invoice_definition_id', '=', $_definition_id));
		$translog=new EDITransactionLog();
		$cc1=new ConstraintChain();
		$cc1->add(new Constraint('status', '=', 'C'));
		$cc1->add(new Constraint('action', '=', 'S'));
		$cc1->add(new Constraint('data_definition_id', '=', $_definition_id));
		$cc1->add(new Constraint('internal_id', '=', '('.$collection->getViewName().'.id)'));
		$cc->add(new Constraint('not', 'exists', '(select id from '.$translog->getTableName().
								' where '.$cc1->__toString().')'));
		$invoice->orderby='invoice_number';
//		echo 'cc='.$cc->__toString().'<br>';
		return $invoice->getAll($cc,false,true);
	}

// Private functions

}

// End of WebEdi
