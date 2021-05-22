<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class EdiInterface {

	protected $version='$Revision: 1.6 $';

	protected $_valid;
	protected $missing_translation = false;
	
	protected $abort_actions = array('A' => 'Abort All'
									,'C' => 'Skip'
									,'S' => 'Stop');
									
	protected $duplicates_actions = array('I' => 'ignore'
										, 'U' => 'replace'
										, 'R' => 'reject');
	
	function __construct($data_definition) {
		
		if ($data_definition instanceof DataDefinition) {
			foreach ($data_definition->getFields() as $fieldname=>$field)
			{
				$this->$fieldname	= $field->value;
			}
			$this->_valid			= TRUE;
		}
		else
		{
			$this->_valid			= FALSE;
		}
		
		$this->validate = FALSE;
	
	}

	function isValid() {
		return $this->_valid;
	}
	
	function isMissingData($filename)
	{
		
		$dom = $this->check_for_validated_file($this->get_file_path(), $filename);
		
		if ($dom && $dom->getElementsByTagName('missing_data')->length > 0)
		{
			return TRUE;
		}

		return FALSE;
		
	}
	
	function getFileList (&$errors=array()) {
		
		$filelist=array();
		if ($this->direction=='IN') {
			
			switch (strtoupper($this->transfer_type)) {
					
				case 'FTP':
					$conn=$this->ftpConnection($errors);
					if (!$conn || count($errors)>0) {
						$errors[]='Error connecting to remote site to get file list';
						return false;
					}
		
					$external_list = ftp_nlist($conn, ".");
		
					foreach ($external_list as $key=>$file) {

						if (ftp_size($conn, $file) == -1 || $file=='.' || $file=='..') {
							unset($external_list[$key]);
						}
					}
					
					foreach ($external_list as $id)
					{
						$filelist[$id] = $this->file_prefix.$id.(!empty($this->file_extension)?'.'.strtolower($this->file_extension):'');
					}
					asort($filelist);
					
					ftp_close($conn);
		
					break;
				
				case 'LOCAL':
					$filepath = $this->get_file_path();
					
					if (!is_dir($filepath)) {
						$errors[]  = "Local directory {$filepath} not found.";
						return;
					}

					$handle = opendir($filepath);
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && !is_dir($filepath.DIRECTORY_SEPARATOR.$file)
							&& substr($file, 0, strlen($this->file_prefix)) == $this->file_prefix
							&& substr($file, -strlen($this->file_extension)) == $this->file_extension) {
							$filelist[$file]=$file;
						}
					}				
					
					closedir($handle);
					break;
					
				default:
					$errors[]=$this->transfer_type.' transfer type not supported';
			}

			return $filelist;
		}

// Direction is not 'IN'
		$extractlist=array();
		if (!empty($this->process_model)
			&& !empty($this->process_function)
			&& method_exists($this->process_model, $this->process_function))
		{
			$model=new $this->process_model;
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
		
		switch (strtoupper($this->transfer_type)) {
			case 'HTTPS':
			case 'HTTP':
				$filepath = $this->get_file_path().$filename;		
				$url = $this->transfer_type.'://'.$this->root_location.$external_name;
				if (!file_put_contents($filepath, file_get_contents($url)))
				{
					$errors[]='Error downloading file';
				}
				break;
			case 'FTP':
				$conn=$this->ftpConnection($errors);
				if (!$conn || count($errors)>0) {
					return false;
				}
		
				$handle = fopen(DATA_ROOT.'company'.EGS_COMPANY_ID.DIRECTORY_SEPARATOR.$this->working_folder.DIRECTORY_SEPARATOR.$filename, 'w');		
				
				if (ftp_fget($conn, $handle, $filename, FTP_ASCII)===false) {
					$errors[]='Failed to download '.$filename;
				}
		
				if (!empty($this->remote_archive_folder) && $download && ftp_rename($conn, $filename, $this->remote_archive_folder.$filename)===false) {
					$errors[]='Failed to move '.$filename.' to Processed directory';
				}
		
				ftp_close($conn);
				break;
			case 'LOCAL':
				if (!file_exists($filepath.$filename))
				{
					$errors[]='File '.$filename.' not found';
				}
				break;		
			default:
				$errors[]=$this->transfer_type.' transfer type not supported';
		}
		
		$logdata['action'] = 'D';
		$this->writeLog($logdata, $errors);
		
		if (count($errors)>0) {
			return false;
		} else {
			return true;
		}
				
	}

	function sendFile ($filename, &$errors=array()) {
		
		switch (strtoupper($this->transfer_type)) {
			case 'HTTPS':
			case 'HTTP':
				$handle = fopen($this->get_file_path().$filename, 'r');		
				while (!feof($handle)) {
					$result=file_get_contents(fgets($handle));
					if (!$result) {
						$errors[]='Failed to upload '.$filename;
						break;
					}
				}
				break;
			case 'FTP':
				$conn=$this->ftpConnection($errors);
				if (!$conn || count($errors)>0) {
					return false;
				}
		
				$handle = fopen($this->get_file_path().$filename, 'r');		
				
				if (ftp_fput($conn, $filename, $handle, FTP_ASCII)===false) {
					$errors[]='Failed to upload '.$filename;
				}
		
				ftp_close($conn);
				break;
			default:
				$errors[]=$this->transfer_type.' transfer type not supported';
		}
		
		$this->writeLog(array('name'=>$filename, 'action'=>'S'), $errors);
		
		if (count($errors)>0) {
			return false;
		} else {
			return true;
		}
		
	}

	function exportFile ($filename, $id, &$errors=array()) {
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
			$model->load($id);
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
			case 'HTTPS':
				foreach ($array as $key=>$line) {
					if (is_array(current($line))) {
						foreach ($line as $subline) {
							$data.=$this->transfer_type.'://'.$this->root_location.http_build_query($subline)."\n";
						}
					} else {
						$data.=$this->transfer_type.'://'.$this->root_location.http_build_query($line)."\n";
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
		if (!$handle || !fwrite($handle, $data)) {
			$errors[]='Error writing '.$filename.' to '.$this->working_folder;
		}
		
		if (count($errors)>0) {
			return false;
		}
		
		return true;

	}
	
	function importFile ($filename, &$errors=array(), &$warnings=array()) {
		
		$filepath=$this->get_file_path();
		
		$dom = $this->check_for_validated_file($filepath, $filename);
		
		if (!$dom || $this->isMissingData($filename))
		{
			// Either there is no current validated file
			// or the validated file has identified missing data
			// so load the data from source to ensure any missing data is created
			$dom = new DOMDocument();
			
			if ($this->type=='CSV') {
				// Convert CSV into XML and then load as DOMDocument
				$dom->loadXML($this->csv2xml($filepath.$filename));
			} else {
				// Load XML file into DOMDocument
				$dom->load($filepath.$filename); 
			}
			
			if (!$dom) {
				$errors[]='Cannot load '.$filepath.$filename.' file ';
				return false;
			}

			$defdetail=new DataDefinitionDetail();
			$defdetail->loadBy(array('data_definition_id', 'element'), array($this->id, $this->name));
			
			if (!$defdetail->isLoaded()) {
				$errors[]='Cannot find Data Definition for '.$this->name;
				return false;
			}

			$this->log_errors=array();

			// Process DOMDocument input data to do ETL
			$this->processNode($dom, null, $dom);
			
			if ($this->missing_translation)
			{
				$errors[] = 'Missing Translations';
				return false;
			}
			
			$this->add_checksum($dom, $filepath, $filename);
			
			// Save the expanded DOMDocument as XML
			$dom->save($filepath.$this->validated_filename($filename));
		}

		// Convert DOMDocument into DataObjectModel Array
		$data = $this->createDataObjectModel($dom);
		
		if (count($this->log_errors)>0) {
			$errors=array_merge_recursive($errors, $this->log_errors);
		}
	
		if (!$data) {
			$errors[]='Failed to process '.$this->name;
			return false;
		}
		$system=system::Instance();
		if (!empty($this->process_model)) {
			if (!empty($this->process_function) && method_exists($this->process_model, $this->process_function)) {
				if ($this->process_model==get_class($this)) {
					return $this->{$this->process_function}($data, $errors, $warnings, $this->duplicates_action);
				}
				else
				{
					// Call the model->function defined in the Data Definition
					return call_user_func(array($this->process_model, $this->process_function), $data, $errors, $warnings, $this->duplicates_action);
				}
			} else {
				// Data Definition specifies model but not a function
				// so call the default save_model for the specified model via EdiController::save_model
				if ($system->controller->save_model($this->process_model, $data, $errors, $warnings, $this->duplicates_action)) {
					$id_value=$system->controller->saved_model->{$system->controller->saved_model->idField};
					$identifier_field=$system->controller->saved_model->identifierField;
					$identifier_value=$system->controller->saved_model->getIdentifierValue();
					return array('internal_id'=>$id_value, 'internal_identifier_field'=>$identifier_field, 'internal_identifier_value'=>$identifier_value);
				} else {
					return false;
				}
			}
		} else {
			// No sepcific model defined so call the default save_model function for the calling 

			$data_in = $this->convertData($data);
			
			$db = DB::Instance();

			if ($this->abort_action == 'A')
			{
				// Set encompassing transaction if need to abort all on error
				$db->StartTrans();
			}
			
			$flash = Flash::Instance();
			
			foreach ($data_in as $model_data) {
				// Calls EdiController::save_model
				if (!$system->controller->save_model(key($model_data), $model_data, $errors, $warnings, $this->duplicates_action))
				{
					if ($this->abort_action != 'S')
					{
						// Abort on Error
						if ($this->abort_action == 'A')
						{
							// Rollback all imported records
							$db->FailTrans();
							$db->CompleteTrans();
						}
						// If not abort_all_on_error,
						// then imported records up to this point will be saved
						return false;
					}
					// Skip error record and continue, so reset errors array
					$warnings[] = 'Ignored '.$db->ErrorMsg();
					$warnings = array_merge($warnings, $errors);
					$errors = array();
					$flash->clear();
				}
			}
			
			if ($this->abort_action == 'A')
			{
				// Everything is OK here, just need to complete transaction
				// if mode is set to abort all on error
				$db->CompleteTrans();
			}

			return array('internal_id'=>'', 'internal_identifier_field'=>'', 'internal_identifier_value'=>'');
		}
	}
	
	function processFile ($_data, &$errors=array())
	{

		set_time_limit(0);

		if (!defined('EDI_XML_ROOT'))
		{
			define('EDI_XML_ROOT', 'EDI_ES_'.$this->external_system_id);
		}
		
		$result=false;
		if (empty($_data['filename'])) {
			$errors[]='No file selected';
		} else {
			$filename=$_data['filename'];
			if ($this->direction=='IN')
			{
				$filepath = $this->get_file_path();
				if (empty($_data['retry'])
				&& !file_exists($filepath.$filename)
				&& !$this->getFile($filename, true, $errors))
				{
					$errors[]='Failed to download file '.$filename;
				}
				else
				{
					$warnings = array();

					$logdata=$this->importFile($filename, $errors, $warnings);

					if (!$logdata)
					{
						$errors[]='Failed to process file '.$filename;
					}
					elseif (!empty($this->local_archive_folder))
					{
						$local_archive_folder = DATA_ROOT.'company'.EGS_COMPANY_ID.DIRECTORY_SEPARATOR.$this->local_archive_folder.DIRECTORY_SEPARATOR;
						
						if (!rename($filepath.$filename
									,$local_archive_folder.$filename))
						{
							$errors[]='Failed to move file '.$filename;
						}
						if (file_exists($filepath.$this->validated_filename($filename)) && !rename($filepath.$this->validated_filename($filename)
									,$local_archive_folder.$this->validated_filename($filename)))
						{
							$errors[]='Failed to move validated file '.$filepath.$this->validated_filename($filename);
						}
					}
						
					$logdata['name']	= $filename;
					$logdata['action']	= 'I';
					
					$flash = Flash::Instance();
			
					$flash->save();
					$warnings = array_merge($warnings, $flash->getMessages('warnings'));
					
					if (count($warnings)>0)
					{
						$logdata['message'] = implode(chr(10), $warnings);
						$flash->addWarning('See log file for Messages');
					}
					
					$this->writeLog($logdata, $errors);
					
				}
			}
			else
			{
				if (!empty($_data['retry']) && file_exists(DATA_ROOT.'company'.EGS_COMPANY_ID.DIRECTORY_SEPARATOR.$this->working_folder.DIRECTORY_SEPARATOR.$filename))
				{
					$result=true;
				}
				else
				{
					$result=$this->exportFile($filename, $_data['data'], $errors);
					$logdata['name']=$filename;
					$logdata['action']='E';
					$this->writeLog($logdata, $errors);
		
				}
				if (!$result)
				{
					$errors[]='Failed to process file '.$filename;
				}
				elseif (!$this->sendFile($filename, $errors))
				{
					$errors[]='Failed to upload file '.$filename;
				}
			}
		}

		if (count($errors)>0)
		{
			return false;
		}
		
		return true;
		
	}
	
	function viewFile ($_data, $_validate = false, &$errors=array()) {

		set_time_limit(0);

		$result=false;
		if (empty($_data['filename'])) {
			$errors[]='No file selected';
		} else {
			$filename=$_data['filename'];
			$filepath=$this->get_file_path();
			if ($this->direction=='IN') {
				if (!file_exists($filepath.$filename) && !$this->getFile(array('name'=>$filename, 'external_id'=>$_data['data']), false, $errors)) {
					$errors[]='Failed to download file '.$filename;
				}
			} else {
				if (!file_exists($filepath.$filename) && !$this->exportFile($filename, $_data['data'], $errors)) {
					$errors[]='Failed to open file '.$filename;
				}
			}
		}
		if (count($errors)>0) {
			return false;
		}
		
		if (isset($_data['validate']) && $_data['validate'])
		{
			$this->validate = TRUE;
			$dom = false;
		}
		else
		{
			$dom = $this->check_for_validated_file($filepath, $filename);
		}
		
		// Load the source file if it has not been validate
		// or requires validating again
		if (!$dom)
		{

			$dom = new DOMDocument();
			
			switch (strtoupper($this->type)) {
				case 'CSV':
					$dom->loadXML($this->csv2xml($filepath.$filename));
					break;
				case 'HTTPS':
				case 'HTTP':
					$handle=fopen($filepath.$filename, 'r');
					if (!$handle) {
						$errors[]='Error opening '.$filepath.$filename.' file ';
						break;
					}
					$data='';
					$line=fgets($handle);
					$count=1;
					while (!feof($handle)) {
						if (!$line) {
							$errors[]='Error reading '.$filepath.$filename.' file ';
							break;
						}
						$temp=explode('&', $line);
						$key='line_'.$count++;
						foreach ($temp as $t1) {
							$t2=explode('=', $t1);
							$data[$key][$t2[0]]=urldecode($t2[1]);
						}
						$line=fgets($handle);
					}
					fclose($handle);
					$dom->loadXML(array2xml($data));
					break;
				case 'XML':
					$dom->load($filepath.$filename);
					break;
				default:
					$errors[]=$this->type.' data type not supported';
			}
		
		}
		
		if (!$dom) {
			$errors[]='Error loading '.$filepath.$filename.' file ';
		}
		
		if ($dom && $this->validate)
		{
			
			// Process DOMDocument input data to do ETL
			$this->processNode($dom, $defdetail->id, $dom);			
		
			if ($this->missing_translation)
			{
				$errors[] = 'Missing Translations';
			}
			
			$this->add_checksum($dom, $filepath, $filename);
			
			if (count($this->missing_values)>0)
			{
				$root	 = $dom->documentElement;
				$element = $dom->createElement('missing_data');
				
				$root->insertBefore($element, $root->firstChild);
				
				foreach ($this->missing_values as $tag_name => $details)
				{
					$node = $dom->createElement($tag_name);
					$element->appendChild($node);
					
					foreach ($details as $data)
					{
						$attribute			= $dom->createAttribute('model');
						$attribute->value	= $data['model'];
						$node->appendChild($attribute);
												
						$data_node = $dom->createElement('data');
						$node->appendChild($data_node);

						foreach ($data['data'] as $fieldname=>$value)
						{
							$element = $dom->createElement($fieldname, str_replace('&', '&#38;', $value));
							$data_node->appendChild($element);
						}
					}
					
				}
			}
			
			// Save validated model
			$dom->save($filepath.$this->validated_filename($filename));
			
			$flash = Flash::Instance();
			$logdata['name']	= $filename;
			$logdata['status']	= 'V';
			
			$flash->save();
			$messages = $flash->getMessages('warnings');
			$logdata['message']	= implode(chr(10), $messages);
			
			$this->writeLog($logdata, $errors);

		}
		
		return $dom;
		
	}
	
	// Protected functions
	protected function ftpConnection (&$errors=array()) {
		
		$ftp_fail=false;

		$conn = ftp_connect($this->root_location);
		
		if ($conn===false) {
			$errors[]='Failed to connect to '.$this->root_location;
			return false;
		}
		
		if (ftp_login($conn,$this->username,$this->password)===false) {
			$errors[]='Failed to login to '.$this->root_location;
		}
		
		if (!empty($this->folder) && ftp_chdir($conn, $this->folder)===false) {
			$errors[]='Failed to change to '.$this->folder.' directory';
		}
		
		if (count($errors)>0) {
			ftp_close($conn);
			return false;
		} else {
			return $conn;
		}
		
	}
	
/*
 *	Extract data from model according to rules defined in Definition Detail
 *	into an array for output processing
 */
	protected function createArray ($defdetail, $model) {

		$array=array();
		foreach ($defdetail->sub_definition as $sub_def_detail) {
			if ($sub_def_detail->sub_definition->count()>0) {
				$datamap=new DataMapping();
				$datamap->load($sub_def_detail->data_mapping_id);
				$hasMany = (is_null($datamap->internal_attribute))?'':$model->getHasMany($datamap->internal_attribute);
				if (!empty($hasMany) && $model->{$datamap->internal_attribute}->count()>0) {
				foreach ($model->{$datamap->internal_attribute} as $submodel) {
						$array[$sub_def_detail->element][]=$this->createArray($sub_def_detail, $submodel);
					}
				} else {
					$array[$sub_def_detail->element]=$this->createArray($sub_def_detail, $model);
				}
			} else {
				$datamap=new DataMapping();
				$datamap->load($sub_def_detail->data_mapping_id);
				$datamaprule=new DataMappingRule();
				$datamaprule->load($sub_def_detail->data_mapping_rule_id);
				$value='';
				if ($datamap->isLoaded()) {
					if ($datamaprule->isLoaded()) {
						$datamapdetail=new DataMappingDetail();
						$value=$datamapdetail->translateCode($datamaprule->id, $model->{$datamap->internal_attribute}, 'OUT');
					} elseif (get_class($model)!=$datamap->internal_type) {
						if (method_exists( $datamap->internal_type, $datamap->internal_attribute)) {
							$value=call_user_func(array($datamap->internal_type, $datamap->internal_attribute));
						} else {
							$attr=(is_null($datamap->internal_type)?'':$datamap->internal_type.'::').$datamap->internal_attribute;
							eval("\$value= $attr;");
						}
					} elseif (method_exists($model, $datamap->internal_attribute)) {
						$value=call_user_func(array($model, $datamap->internal_attribute));
					} elseif (is_object($model)) {
						$attr='$model->'.$datamap->internal_attribute;
						eval("\$value= $attr;");
					}
				} elseif (!is_null($sub_def_detail->default_value)) {
					$value=$sub_def_detail->default_value;
				}
				$array[$sub_def_detail->element]=(!empty($value)) ? str_replace("'", '', $value) : $value;
			}
		}
		return $array;
	}

	protected function createDataObjectModel1 ($node)
	{
		$data=array();
		
		foreach ($node->childNodes as $child_node) {
			$model = $field = $value = '';
			if ($child_node->hasAttributes())
			{
				foreach ($child_node->attributes as $attr)
				{
					switch ($attr->name) {
						case 'line_no':
							$index = $child_node->getAttribute('line_no');
							break;
						case 'internal_type':
							$model = $child_node->getAttribute('internal_type');
							break;
						case 'internal_attribute':
							$field = $child_node->getAttribute('internal_attribute');
							break;
						case 'internal_code':
							$value = $child_node->getAttribute('internal_code');
					}
				}
				
			}
			if (!empty($model) && !empty($field))
			{
				$data[$model][$field] = $value;
			}
			elseif ($child_node->childNodes)
			{
				$data[$child_node->tagName][(empty($index)?'':$index)] = $this->createDataObjectModel($child_node);
			}
		}
		
		return $data;
	}

	protected function createDataObjectModel ($node)
	{
		$data=array();
		
		foreach ($node->childNodes as $child_node) {
			$model = $field = $value = '';
			if ($child_node->hasAttributes())
			{
				foreach ($child_node->attributes as $attr)
				{
					switch ($attr->name) {
						case 'line_no':
							$index = $child_node->getAttribute('line_no');
							break;
						case 'internal_type':
							$model = $child_node->getAttribute('internal_type');
							break;
						case 'internal_attribute':
							$field = $child_node->getAttribute('internal_attribute');
							break;
						case 'internal_code':
							$value = $child_node->getAttribute('internal_code');
					}
				}
				
			}
			if (!empty($model) && !empty($field))
			{
				$data[$model][$field] = $value;
			}
			
			if ($child_node->childNodes)
			{
				$data = array_merge_recursive($data, $this->createDataObjectModel($child_node));
			}
		}
		
		return $data;
	}
	
/*
 *	Recursive function to add ETL attributes to DOMDocument containing
 *  the input data
 */
	protected function processNode ($node, $_dtd_element_id, $dom) {
		// Converts XML to DOM document format for display on screen
		foreach ($node->childNodes as $child_node)
		{
			
			if ($child_node->childNodes && $child_node->firstChild->nodeType == 3)
			{
				// Node has children but the child is a text node, so get the value
				// Might need to be aware of whitespace characters returning as empty text node
				// instead of the required value
				$node_value=trim($child_node->firstChild->nodeValue);
			}
			else
			{
				$node_value = '';
			}

			$external_code = $node_value;
			
			$defdetail=new DataDefinitionDetail();

			$defdetail->loadBy(array('parent_id', 'element'), array($_dtd_element_id, $child_node->tagName));
			
			$datamap=$defdetail->data_map;
			
			$translation = $datamaprule = $datamapdetail = '';
			
			if (!is_null($defdetail->data_mapping_rule_id))
			{
				
				$datamaprule = new DataMappingRule();
				$datamaprule->load($defdetail->data_mapping_rule_id);
				
				$translation = $node_value;
				
				if ($datamaprule->isLoaded())
				{
					
					if (!is_null($datamaprule->external_format))
					{
						$validate_errors = array();
						$translation	 = $datamaprule->validate($node_value, $validate_errors);
						if (count($validate_errors)>0) {
							$this->log_errors[]=$key.' "'.$node_value.'" : '.implode(',' ,$validate_errors);
						}
					}
					$datamapdetail = new DataMappingDetail();
					// Do the mapping translation; returns original value if no translation found
					$translation=$datamapdetail->translateCode($datamaprule->id, $translation, 'IN');

					// Need to check if the translated value exists and return display value
					if ($datamaprule->isLoaded() && !is_null($datamaprule->data_mapping_id))
					{
						$mapvalue		= new DataMapping();
						$mapvalue->load($datamaprule->data_mapping_id);
						$display_value	= $mapvalue->getValue($translation);
					}

					if ($display_value===FALSE)
					{
						$display_value='Missing Translation';
						$this->missing_translation=true;
					}
				}
			}
			elseif ($datamap->isLoaded())
			{
				if ($child_node->childNodes && !empty($child_node->firstChild->nodeType) && $child_node->firstChild->nodeType != 3) {
					$translation=serialize('<'.XML_ROOT.'>'.$dom->saveXML($child_node).'</'.XML_ROOT.'>');
				} else {
					$translation = $this->translateCode($datamap, $node_value, $child_node->tagName);
				
					if (empty($translation) && !is_null($defdetail->default_value))
					{
						$translation=$defdetail->default_value;
					}
					$display_value	= $node_value;
				}
			}
			elseif ($child_node->childNodes)
			{
				if (empty($node_value))
				{
					$node_value='No value';
				}
				$display_value='not used';
			}

			if (!is_null($datamap->internal_type) && !is_null($datamap->internal_attribute))
			{
				
				if (!is_null($defdetail->data_mapping_rule_id))
				{
					$attribute			= $dom->createAttribute('data_mapping_rule_id');
					$attribute->value	= $defdetail->data_mapping_rule_id;
					$child_node->appendChild($attribute);
					
				}
				
				if (!is_null($datamapdetail->id))
				{
					$attribute			= $dom->createAttribute('id');
					$attribute->value	= $datamapdetail->id;
					$child_node->appendChild($attribute);
				}
				
				$attribute = $dom->createAttribute('external_code');
				$attribute->value=str_replace('&', '&#38;', $external_code);
				$child_node->appendChild($attribute);
				
				$attribute = $dom->createAttribute('internal_code');
				$attribute->value=str_replace('&', '&#38;', $translation);
				$child_node->appendChild($attribute);
				
				$attribute			= $dom->createAttribute('display_value');
				$attribute->value	= str_replace('&', '&#38;', $display_value);
				$child_node->appendChild($attribute);

				$attribute			= $dom->createAttribute('internal_type');
				$attribute->value	= $datamap->internal_type;
				$child_node->appendChild($attribute);

				$attribute			= $dom->createAttribute('internal_attribute');
				$attribute->value	= $datamap->internal_attribute;
				$child_node->appendChild($attribute);

			}
			
			if ($child_node->childNodes && $child_node->firstChild->nodeType != 3)
			{
				$this->processNode($child_node, $defdetail->{$defdetail->idField}, $dom);
			}

		}

	}

	public function add_missing_data($filename, &$errors = array())
	{
		
		$dom = $this->check_for_validated_file($this->get_file_path(), $filename);
		
		if (!$dom)
		{
			$errors[] = 'Cannot find validated file';
			return FALSE;
		}

		$missing_data = $dom->getElementsByTagName('missing_data');
		
		if ($missing_data->length == 0)
		{
			$errors[] = 'Cannot find "Missing Data" in validated file';
			return FALSE;
		}
		
		$db = DB::Instance();
		$db->StartTrans();
		
		foreach ($missing_data as $elements)
		{
			foreach ($elements->childNodes as $child_node)
			{
				
				$model_name = $child_node->getAttribute('model');
				
				if (empty($model_name))
				{
					$errors[] = 'Error adding data - Cannot find model for '.$child_node->nodeName;
					break;
				}
			
				foreach ($child_node->childNodes as $data_node)
				{
				
					$data = array();
				
					foreach ($data_node->childNodes as $field_node)
					{
				
						$data[$field_node->nodeName] = $field_node->nodeValue;
					}
				
					$model = DataObject::Factory($data, $errors, $model_name);
				
					if (!$model || !$model->save())
					{
						$errors[] = 'Error adding '.implode(' ', $data).' for '.$child_node->nodeName;
						break;
					}
				}
			}
		}
		
		$logdata = array();
		
		if (count($errors) > 0)
		{
			$db->FailTrans();
			$return = FALSE;
		}
		else
		{
			$logdata['message']	= 'Missing Data loaded OK';
			$return = TRUE;
		}
		
		$db->completeTrans();

		$logdata['name']	= $filename;
		$logdata['status']	= 'V';
		
		$this->writeLog($logdata, $errors);
		
		return $return;
		
	}
	
	public function writeLogs($filelist, $action, &$errors=array())
	{

		foreach ($filelist as $id=>$filename)
		{
			$logdata=array('name'=>$filename, 'action'=>$action);
			
			if ($this->direction=='IN')
			{
				$logdata['external_id'] = $id;
			}
			else
			{
				$logdata['internal_id'] = $id;
			}

			$this->writeLog($logdata, $errors);
		}
	}
	
	protected function writeLog($_data, &$errors=array())
	{
		
		$db=DB::Instance();
		$db->StartTrans();
		
		$filename=$_data['name'];
		
		$_data['external_system_id'] = $this->external_system_id;
		$_data['data_definition_id'] = $this->id;
					  
		$edilog=new EDITransactionLog();
		if (empty($_data['id']))
		{
			
			$edilog->loadBy(array('name', 'status', 'data_definition_id'), array($filename, 'N', $this->id));
			
			if (!$edilog->isLoaded())
			{
				$edilog->loadBy(array('name', 'status', 'data_definition_id'), array($filename, 'V', $this->id));
			}
			
			if (!$edilog->isLoaded())
			{
				$edilog->loadBy(array('name', 'status', 'data_definition_id'), array($filename, 'E', $this->id));
				if ($edilog->isLoaded() && ($_data['action'] == 'AD' || $_data['action'] == 'AE'))
				{
					return;
				}
			}
			
			if (!$edilog->isLoaded())
			{
				$cc = new ConstraintChain();
				$cc->add(new Constraint('name', '=', $filename));
				$cc->add(new Constraint('status', '=', 'C'));
				$cc->add(new Constraint('data_definition_id', '=', $this->id));
				$cc->add(new Constraint('action', 'not in', "('I', 'S')"));
				$edilog->loadBy($cc);
			}

			if ($edilog->isLoaded())
			{
				$_data['id'] = $edilog->id;
				
				foreach ($edilog->getFields() as $fieldname=>$field)
				{
					if (!isset($_data[$fieldname]) && $fieldname!='status')
					{
						$_data[$fieldname] = $edilog->$fieldname;
					}
				}
			}
			
		}
		
		if (count($errors)>0) {
			$_data['status']='E';
			$flash=Flash::Instance();
			$flash->save();
			$errors=array_merge_recursive($errors, $flash->getMessages('warnings'));
			$_data['message']=implode(chr(10), $errors);
		} else {
			$_data['status']=(empty($_data['status']))?EDITransactionLog::getStatusByAction($_data['action']):$_data['status'];
			// If no message input, blank out the message field
			$_data['message'] = (isset($_data['message']))?$_data['message']:'';
		}
		
		if (!$edilog->isLoaded() 
			|| ($edilog->isLoaded() && ($edilog->action != $_data['action'] || $edilog->status != $_data['status'] || $edilog->message != $_data['message'])))
		{
			$edierrors=array();
			
			$edilog=EDITransactionLog::Factory($_data, $edierrors, 'EDITransactionLog');
			if ($edilog && count($edierrors)==0) {
				
				if (!$edilog->save())
				{
					
					$edierrors[]='Error saving log entry : '.$db->ErrorMsg();
				
				}
				else
				{
					// Archive the existing entry to the history log table
					$_data['edi_transactions_log_id'] = $edilog->id;
					$_data['action'] = $edilog->action;
					unset($_data['id']);
					
					$ediloghistory=EDITransactionLogHistory::Factory($_data, $edierrors, 'EDITransactionLogHistory');
					
					if (!$ediloghistory || !$ediloghistory->save())
					{
					
						$edierrors[]='Error saving log history entry : '.$db->ErrorMsg();
				
					}
				}
			}
		}
		if (count($edierrors)>0) {
			$errors=array_merge_recursive($errors, $edierrors);
		}

		$db->CompleteTrans();
		
	}
	
// private functions
	private function get_file_path()
	{
		return DATA_ROOT.'company'.EGS_COMPANY_ID.DIRECTORY_SEPARATOR.$this->working_folder.DIRECTORY_SEPARATOR;
	}
	
	private function check_for_validated_file($filepath = '', $filename = '')
	{
		
		$validated_filename = $filepath.$this->validated_filename($filename);
		
		if (file_exists($filepath.$filename) && file_exists($validated_filename))
		{
			$hash = $this->get_file_checksum($filepath.$filename);
			
			$dom = new DOMDocument();

			$dom->load($validated_filename);
			
			if ($hash == $dom->documentElement->getAttribute('checksum'))
			{
				return $dom;
			}
			
		}
	
		return FALSE;
		
	}
	
	private function validated_filename($filename = '')
	{
		
		return $filename.'.validated.xml';
	
	}
	
	private function get_file_checksum($filepath = '', $filename = '')
	{
		return md5_file($filepath.$filename);
	}
	
	private function add_checksum($dom, $filepath = '', $filename = '')
	{
		
		$attribute			= $dom->createAttribute('checksum');
		$attribute->value	= $this->get_file_checksum($filepath, $filename);

		$element = $dom->documentElement;
		
		$element->appendChild($attribute);
		
	}
	
	private function csv2xml($filepath)
	{
		
		$name = trim(str_replace(' ', '_', $this->name));
		$xml=new SimpleXMLElement("<$name></$name>");
		$ddd1 = new DataDefinitionDetail();
		$ddd1->loadBy(array('data_definition_id', 'element'), array($this->id, $this->name));

		$ddd2 = new DataDefinitionDetail();
		$ddd2->loadBy(array('data_definition_id', 'parent_id'), array($this->id, $ddd1->id));

		$ddd3 = new DataDefinitionDetail();
		$tags = array_values($ddd3->getAllByDef($this->id, $ddd2->id));
		
		if ($handle = fopen($filepath, 'r')) {
			$line=fgetcsv($handle, 0, $this->field_separator, $this->text_delimiter);
			$line_counter = 0;
			while (!feof($handle)) {
				$line_counter++;
				$xml_line=$xml->addChild($ddd2->element);

				$xml_line->addAttribute('line_no', $line_counter);
				
				foreach ($line as $index=>$field) {
					if (!empty($tags[$index]))
					{
						$xml_line->addChild($tags[$index], str_replace('&', '&#38;', $field));
					}
				}

				$line=fgetcsv($handle, 0, $this->field_separator, $this->text_delimiter);
			}
		} 
		fclose($handle);
		
		return $xml->asXML();
	
	}
	
	/*
	 * Do lookup on input data (FK via belongsTo or hasOne) and translate to internal code
	 *
	 * If attribute is defined as FK definition and lookup value exists, do the tranlation.
	 * 
	 * If attribute is defined as FK definition and lookup value does not exist, attempt to create
	 * a new FK entry, unless this is in validation mode.
	 * 
	 */
	private function translateCode($datamap, &$in_value, $node_name)
	{
		
		if (empty($in_value))
		{
			return $in_value;
		}
		
		$flash = Flash::Instance();
		
		$out_value = $in_value;
		
		$model = new $datamap->internal_type();
		$hasOne = $model->getHasOne();
		if ($model)
		{
			if (isset($model->belongsToField[$datamap->internal_attribute]))
			{
				$belongsToName = $model->belongsToField[$datamap->internal_attribute];
			}
			else
			{
				$belongsToName = $datamap->internal_attribute;
			}
			
			if ((isset($model->belongsTo[$belongsToName]) && ($belongsTo = $model->belongsTo[$belongsToName]))
					|| (isset($hasOne[$datamap->internal_attribute]) && ($belongsTo = $hasOne[$datamap->internal_attribute])))
			{
				// Repoint the model to the fk field
				$datamap->internal_attribute = $belongsTo['field'];
				// Target field is a foreign key
				$fkmodel = new $belongsTo['model'];

				$identifierfields = $fkmodel->getIdentifierFields();
				// May need to be more intelligent here
				// Assume, if only one identifier field, the value should not be split
				// i.e. ignore identifierFieldJoin character in value if only one identifier field
				if (count($identifierfields)>1)
				{
					$fieldvalues=explode(trim($fkmodel->identifierFieldJoin), $in_value);
				}
				else
				{
					$fieldvalues = array($in_value);
				}
				
				foreach ($fieldvalues as $key=>$in_value)
				{
					// replace any embedded characters, but leave spaces
//					$fieldvalues[$key] = trim(str_replace(array('"', "'", '||', '/'), '', $value));
					$fieldvalues[$key] = trim(str_replace(array('||', '/'), '', $in_value));
				}

				$data=array();
				for ($i=0; $i<count($identifierfields); $i++)
				{
					if (isset($fieldvalues[$i]))
					{
						$data[$identifierfields[$i]] = $fieldvalues[$i];
						$loadfields[]=$identifierfields[$i];
						$loadvalues[]=$fieldvalues[$i];
					}
				}

				$fkmodel->loadBy($loadfields, $loadvalues);
				
				if (!$fkmodel->isLoaded() && count($loadvalues)==1)
				{
					$fkmodel->load(current($loadvalues));
				}
				
				if ($fkmodel->isLoaded())
				{
					$out_value = $fkmodel->{$fkmodel->idField};
				}
				else
				{
					if (count($data)>0)
					{

						if ($this->validate)
						{
								$out_value	= '';
								$flash->addWarning('Needs to create entry for '.$fkmodel->getTitle().' : '.$in_value);
								$this->missing_values[$node_name][$in_value] = array( 'model' => $belongsTo['model']
																			,'data'	 => $data);
								$in_value	= $in_value.' ( * will be created * )';
						}
						else
						{

							$errors=array();
							$save_model=DataObject::Factory($data, $errors, $fkmodel);
							if ($save_model && $save_model->save())
							{
								$out_value	= $save_model->{$save_model->idField};
								$flash->addWarning('Created entry for '.$fkmodel->getTitle().' '.$out_value.':'.$in_value);
								$in_value	= $in_value.' ( * created * )';
							}
							else
							{
								$message	= 'Error adding data for '.get_class($fkmodel);
								$in_value	.= ' ( * '.$message.' * )';
								$flash->addError($message);
							}
						}
					}
				}
			}

			elseif ($model->isEnum($datamap->internal_attribute))
			{
				// The mapping is for an enum field, so chack/translate value
				$options = $model->getEnumOptions($datamap->internal_attribute);
				
				if (!isset($options[$in_value]))
				{
					// Value is not an enum key value so search array
					$temp = $model->getEnumKey($datamap->internal_attribute, $in_value);
					
					$out_value = (empty($temp))?$in_value:$temp;
				}
			
			}
		}
		
		return $out_value;
		
	}

	function convertData ($data_in)
	{
		// Expects multi-row array data in form
		//    Array(Model)->array(fields)->array(field values)
		// need to transpose this to
		//    Array(array(model)->array(fields)->field value)
		// e.g. [model][field][0]=value
		//      [model][field][1]=value
		// to   [0][model][field]=value
		//      [1][model][field]=value

		// TODO: Add validation for the above format
		//       - to check that model class exists
		//       - no need to check that field exists as there may be
		//         further processing by a custom function
		//       - to check at each level that the data is an array
		$data_out = array();
		
		if (is_array($data_in) && count($data_in)>0)
		{
			
			foreach ($data_in as $key1=>$level1)
			{
				foreach ($level1 as $key2=>$level2)
				{
					if (is_array($level2))
					{
						foreach ($level2 as $key3=>$level3)
						{
							$data_out[$key3][$key1][$key2] = $level3;
						}
					}
					else
					{
						$data_out[0][$key1][$key2] = $level2;
					}
				}
			}
	
		}
		
		return $data_out;
		
	}
	
	function loadAccounts($data, &$errors = array(), &$warnings=array())
	{
		$system=system::Instance();
		
		$db=DB::Instance();

		$flash = Flash::Instance();
		
		if ($this->abort_action == 'A')
		{
			// Set encompassing transaction if need to abort all on error
			$db->StartTrans();
		}
						
		$data_in = $this->convertData($data);
				
		if (is_array($data_in) && count($data_in)>0)
		{
			foreach ($data_in as $line)
			{
				$model_data = array();
				foreach ($line as $model=>$fields)
				{
					
					switch ($model)
					{
						case 'Company':
							$model_data['Party']['type']=$model;
							
							if (empty($fields['accountnumber']))
							{
								$model = 'Lead';
								unset($fields['accountnumber']);
							}
							$company_model = $model;
							$model_data[$model]=$fields;
							$model_data[$model]['is_lead']=(empty($fields['accountnumber']))?true:false;
							if (!empty($model_data[$model]['name']))
							{
								$account = new $company_model;
								$account->loadBy('name', $model_data[$model]['name']);
								if ($account->isLoaded())
								{
									$model_data[$model][$account->idField]	= $account->{$account->idField};
									$model_data['Party']['id']				= $account->party_id;
								}
							}
							break;
						case 'CompanyAddress':
							$model_data['Address']=$fields;
							$model_data['PartyAddress']['main']=true;
							// Check for existing entry
							$address = new CompanyAddress();
							$address->loadBy(array('street1', 'street2', 'street3', 'town', 'county', 'postcode')
											,array($fields['street1'], $fields['street2'], $fields['street3'], $fields['town'], $fields['county'], $fields['postcode']));
							if ($address->isLoaded())
							{
								$model_data['PartyAddress']['address_id'] = $model_data['Address'][$address->idField] = $address->{$address->idField};
							}
							break;
						case 'ContactMethod':
							foreach ($fields as $type=>$value)
							{
								if (!empty($value))
								{
									$model_data[$type]['ContactMethod']['contact']=$value;
									$model_data[$type]['PartyContactMethod']['main']=true;
									switch ($type)
									{
										case 'phone':
											$model_data[$type]['PartyContactMethod']['type']='T';
											break;
										case 'email':
											$model_data[$type]['PartyContactMethod']['type']='E';
											break;
										case 'fax':
											$model_data[$type]['PartyContactMethod']['type']='F';
											break;
										case 'mobile':
											$model_data[$type]['PartyContactMethod']['type']='M';
											break;
										default:
											$model_data[$type]['PartyContactMethod']['type']='';
									}
									// Check for existing entry
									$contactmethod = new ContactMethod();
									$contactmethod->loadBy('contact', $value);
									if ($contactmethod->isLoaded())
									{
										$model_data[$type]['PartyContactMethod']['contactmethod_id'] = $model_data[$type]['ContactMethod'][$contactmethod->idField] = $contactmethod->{$contactmethod->idField};
									}
								}
							}
							break;
					}
				}

				if (!$system->controller->save_model($company_model, $model_data, $errors, $warnings, $this->duplicates_action))
				{
					if ($this->abort_action != 'S')
					{
						// Abort on Error
						if ($this->abort_action == 'A')
						{
							// Rollback all imported records
							$db->FailTrans();
							$db->CompleteTrans();
						}
						// If not abort_all_on_error,
						// then imported records up to this point will be saved
						return false;
					}
					// Skip error record and continue, so reset errors array

					$warnings[] = 'Ignoring Company '.$model_data[$model]['name'].' '.$db->ErrorMsg();
					$warnings = array_merge($warnings, $errors);
					$errors = array();
					$flash->clear();
					
				}
				$company=$system->controller->getSavedModel($company_model);
				if (isset($accounts_data['CompanyInCategories']))
				{
					foreach ($accounts_data['CompanyInCategories'] as $type=>$value)
					{
						$companycategories['company_id'] = $company->id;
						if (!empty($value))
						{
							
							// look up ContactCategory for this type
							$category = new ContactCategory();
							$categories = $category->getCategoriesByName(ucfirst($type));
							if (count($categories)==1)
							{
								$companycategories['category_id'] = key($categories);
								$account_errors=array();
								$companyincategory = DataObject::Factory($companycategories, $account_errors, 'CompanyInCategories');
								if (count($account_errors)>0 || !$companyincategory || !$companyincategory->save())
								{
									
									if ($this->abort_action != 'S')
									{
										// Abort on Error
										if ($this->abort_action == 'A')
										{
											// Rollback all imported records
											$errors = array_merge_recursive($errors, $account_errors);
											$errors[]='Failed to save Company Category '.$db->ErrorMsg();
											$db->FailTrans();
											$db->CompleteTrans();
										}
										// If not abort_all_on_error,
										// then imported records up to this point will be saved
										return false;
									}
									// Skip error record and continue, so reset errors array
									$warnings[] = 'Ignoring Company Category for '.$company->name.' '.$db->ErrorMsg();
									$warnings = array_merge($warnings, $account_errors);
									$flash->clear();
								}
							}
						}
					}
				}

				$system->controller->clearSavedModels();
				
			}
		}
		
		if ($this->abort_action == 'A')
		{
			// Everything is OK here, just need to complete transaction
			// if mode is set to abort all on error
			$db->CompleteTrans();
		}
				
		return array('internal_id'=>null, 'internal_identifier_field'=>'', 'internal_identifier_value'=>'');
	}
	
	function loadPeople($data, &$errors = array(), &$warnings=array())
	{
		$system=system::Instance();
		
		$db=DB::Instance();

		$flash = Flash::Instance();
		
		if ($this->abort_action == 'A')
		{
			// Set encompassing transaction if need to abort all on error
			$db->StartTrans();
		}
				
		$data_in = $this->convertData($data);
		
		if (is_array($data_in) && count($data_in)>0)
		{
			foreach ($data_in as $line)
			{
				$model_data = array();
				foreach ($line as $model=>$fields)
				{

					switch ($model)
					{
						case 'Person':
							$model_data['Party']['type']=$model;
							$model_data[$model]=$fields;
							$model_data[$model]['is_lead']=(empty($fields['accountnumber']))?true:false;
							break;
						case 'PersonAddress':
							$model_data['Address']=$fields;
							$values = false;
							foreach ($fields as $field=>$value)
							{
								if (!empty($value))
								{
									$values = true;
									break;
								}
							}
							if ($values)
							{
								$model_data['PartyAddress']['main']=true;
								// Check for existing entry
								$address = new PersonAddress();
								$address->loadBy(array('street1', 'street2', 'street3', 'town', 'county', 'postcode')
												,array($fields['street1'], $fields['street2'], $fields['street3'], $fields['town'], $fields['county'], $fields['postcode']));
								if ($address->isLoaded())
								{
									$model_data['PartyAddress']['address_id'] = $model_data['Address'][$address->idField] = $address->{$address->idField};
								}
							}
							else
							{
								unset($model_data['Address']);
							}
							break;
						case 'ContactMethod':
							foreach ($fields as $type=>$value)
							{
								if (!empty($value))
								{
									$model_data[$type]['ContactMethod']['contact']=$value;
									$model_data[$type]['PartyContactMethod']['main']=true;
									switch ($type)
									{
										case 'phone':
											$model_data[$type]['PartyContactMethod']['type']='T';
											break;
										case 'email':
											$model_data[$type]['PartyContactMethod']['type']='E';
											break;
										case 'fax':
											$model_data[$type]['PartyContactMethod']['type']='F';
											break;
										case 'mobile':
											$model_data[$type]['PartyContactMethod']['type']='M';
											break;
										default:
											$model_data[$type]['PartyContactMethod']['type']='';
									}
									// Check for existing entry
									$contactmethod = new ContactMethod();
									$contactmethod->loadBy('contact', $value);
									if ($contactmethod->isLoaded())
									{
										$model_data[$type]['PartyContactMethod']['contactmethod_id'] = $model_data[$type]['ContactMethod'][$contactmethod->idField] = $contactmethod->{$contactmethod->idField};
									}
								}
							}
							break;
					}
				}

				if (!$system->controller->save_model('Person', $model_data, $errors, $warnings, $this->duplicates_action))
				{
					if ($this->abort_action != 'S')
					{
						// Abort on Error
						if ($this->abort_action == 'A')
						{
							// Rollback all imported records
							$db->FailTrans();
							$db->CompleteTrans();
						}
						// If not abort_all_on_error,
						// then imported records up to this point will be saved
						return false;
					}
					// Skip error record and continue, so reset errors array
					$warnings[] = 'Ignored';
					$warnings = array_merge($warnings, $errors);
					$errors = array();
					$flash->clear();
				}
				
				$system->controller->clearSavedModels();
				
			}
		}
		
		if ($this->abort_action == 'A')
		{
			// Everything is OK here, just need to complete transaction
			// if mode is set to abort all on error
			$db->CompleteTrans();
		}
				
		return array('internal_id'=>null, 'internal_identifier_field'=>'', 'internal_identifier_value'=>'');
	}
	
	function loadJournals($data, &$errors = array(), &$warnings=array())
	{
		$system=system::Instance();
		
		$data_in = $this->convertData($data);
		
		if (is_array($data_in) && count($data_in)>0)
		{
			
			$db = DB::Instance();
			$docref=$db->GenID('gl_transactions_docref_seq');
			$gl_transactions=array();
			
			foreach ($data_in as $gl_data)
			{
				
				foreach ($gl_data as $model=>$fields)
				{
					$glperiod = GLPeriod::getPeriod(fix_date($fields['transaction_date']));
					if ((!$glperiod) || (count($glperiod) == 0)) {
						$errors[] = 'No period exists for this date';
						break;
					}

					$fields['source']		= 'G';
					$fields['type']			= 'J';
					$fields['docref']		= $docref;
					$fields['glperiods_id']	= $glperiod['id'];
					GLTransaction::setTwinCurrency($fields);
					$gl_transaction			= GLTransaction::Factory($fields,$errors);
					$gl_transactions[]		= $gl_transaction;
					
				}
			}
			if (count($gl_transactions)>0 && !GLTransaction::saveTransactions($gl_transactions, $errors))
			{
				$errors[] = 'Failed to save journal import';
			}
			
		}
		else
		{
			$errors[] = 'No data found or error converting data';
		}
		
		if (count($errors) > 0)
		{
			return false;
		}
		
		return array('internal_id'=>$gl_transaction->id, 'internal_identifier_field'=>'docref', 'internal_identifier_value'=>$docref);
	
	}
	
}

// End of EdiInterface
