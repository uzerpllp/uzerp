<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class DatadefinitionsController extends EdiController {

	protected $_templateobject;
	protected $version='$Revision: 1.17 $';
	
	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new DataDefinition();
		$this->uses($this->_templateobject);

	}

	public function index(){
		$this->view->set('clickaction', 'view');
		parent::index(new DataDefinitionCollection($this->_templateobject));
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new'
									   ),
					'tag'=>'new data definition'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		
		if (isset($this->_data['external_system_id'])) {
			$externalsystem=new ExternalSystem();
			$externalsystem->load($this->_data['external_system_id']);
			if ($externalsystem->isLoaded()) {
				$this->view->set('page_title',$this->getPageName('', $externalsystem->name));
				$this->view->set('external_system_id', $this->_data['external_system_id']);
			}
		}
		
	}

	public function _new() {
		parent::_new();	
	}
	
	public function view () {

		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		$datadef = $this->_uses[$this->modeltype];
		
		$edi=$datadef->setEdiInterface();
		
		$this->view->set('datadefinition', $datadef);
		
		$flash=Flash::Instance();
		$errors=array();
		
		if (!$edi->isValid())
		{
			$flash->addError('Error getting EDI definition');
			sendBack();
		}

		$type=($datadef->direction=='IN')?'import':'export';
		$this->view->set('type', prettify($type));
				
		if (!isset($this->_data['page']))
		{
			$action=($datadef->direction=='IN')?'AD':'AE';
			$filelist=$edi->getFileList($errors);
			if (count($errors)==0 && $filelist)
			{
				// Update log with list of items awaiting download or export
				$edi->writeLogs($filelist, $action, $errors);
			}
			
		}
			
		$edilog=new EDITransactionLog();
		$edilogs=new EDITransactionLogCollection($edilog);
		
		$sh = $this->setSearchHandler($edilogs);
		
		$db=DB::Instance();
		
		$filename = $datadef->file_prefix.'%'.(!is_null($datadef->file_extension)?'.'.strtolower($datadef->file_extension):null);
		$cc = new ConstraintChain();
		$cc->add(new Constraint('status', '=', 'E'));
		$cc->add(new Constraint('name', 'like', $filename));
		$cc->add(new Constraint('data_definition_id', '=', $datadef->id));
		if ($datadef->direction=='IN')
		{
			$cc->add(new Constraint('action', 'in', "('AD', 'AI', 'D', 'I')"));
		}
		else
		{
			$cc->add(new Constraint('action', 'in',  "('AE', 'E', 'S')"));
		}
		$this->view->set('errors', $edilog->getCount($cc));
		
		$cc1 = new ConstraintChain();
		
		if ($this->_data['action_type'] == 'ERR')
		{
			$cc1 = $cc;
		}
		else
		{
			$cc1->add(new Constraint('status', '=', 'N'));
			$cc1->add(new Constraint('data_definition_id', '=', $datadef->id));
			$cc1->add(new Constraint('name', 'like', $filename));
			$cc2 = new ConstraintChain();
			$cc3 = new ConstraintChain();
			$cc3->add(new Constraint('status', '=', 'E'));
			if ($datadef->direction=='IN')
			{
				$cc2->add(new Constraint('action', 'in', "('AD', 'AI', 'D')"));
				$cc3->add(new Constraint('action', '=', 'I'));
			}
			else
			{
				$cc2->add(new Constraint('action', 'in',  "('AE', 'E')"));
				$cc3->add(new Constraint('action', '=', 'S'));
			}
			$cc2->add($cc3, 'OR');
			$cc1->add($cc2);
		}
		$sh->addConstraintChain($cc1);
		
		parent::index($edilogs, $sh);
		
		if (count($errors)>0) {
			$errors[]='Error getting '.$type.' list';
			$flash=Flash::Instance();
			$flash->addErrors($errors);
		}
		
		$this->view->set('edilogs', $edilogs);
		
		$sidebar = new SidebarController($this->view);
		$sidebarlist = array();
		
		$sidebarlist['view_all'] = array(
					'tag' => 'View All Transfer Types',
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'index'
								 )
				);
		
		$sidebarlist['upload_files'] = array(
					'tag'=>'upload files',
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'upload_file'
								 )
				);
		
		$sidebar->addList('actions', $sidebarlist);
				
		$action=($datadef->direction=='IN')?'Import':'Export';
		
		$sidebarlist = array();
		
		$sidebarlist[$datadef->name.$action] = array(
					'tag' => 'view outstanding files to '.$action,
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'view'
								 ,'id'=>$datadef->id
								 ,'implementation_class'=>$datadef->implementation_class
								 )
				);
				
		$sidebarlist[$datadef->name.'errors'] = array(
					'tag' => 'view outstanding error files',
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'view'
								 ,'action_type'=>'ERR'
								 ,'id'=>$datadef->id
								 ,'implementation_class'=>$datadef->implementation_class
								 )
				);
				
		$sidebarlist['edit'] = array(
					'tag'=>'edit',
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'edit'
								 ,'id'=>$datadef->id
								 ,'implementation_class'=>$datadef->implementation_class
								 )
				);
				
		$sidebarlist['delete'] = array(
					'tag'=>'delete',
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'delete'
								 ,'id'=>$datadef->id
								 ,'implementation_class'=>$datadef->implementation_class
								 )
				);
				
		if ($datadef->transfer_type == 'LOCAL' && $datadef->direction=='IN')
		{
			$sidebarlist['upload'] = array(
					'tag'=>'upload file',
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'upload_file'
								 ,'id'=>$datadef->id
								 )
				);
		}
		
		$sidebar->addList($datadef->name, $sidebarlist);
		
		$this->sidebarRelatedItems($sidebar, $datadef);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
				
	}

	public function view_file () {
		
		$flash=Flash::Instance();
				
		if (!$this->checkParams('id') || !$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		$datadef = $this->_uses[$this->modeltype];
		
		$edi=$datadef->setEdiInterface();

		if (!$edi->isValid())
		{
			$flash->addError('Error getting EDI definition');
			sendBack();
		}
					
		$this->view->set('datadefinition', $datadef);
		$this->view->set('file', $this->_data['filename']);
		$this->view->set('data', $this->_data['data']);
		
		$errors=array();

		$validate = (isset($this->_data['validate']))?$this->_data['validate']:FALSE;
		
		$doc = $edi->viewFile($this->_data, $validate, $errors);
		
		if (!$doc) {
			if (count($errors)>0) {
				$flash->addErrors($errors);
			} else {
				$flash->addError('Error viewing '.$this->_data['filename']);
			}

			$other=array('id'=>$datadef->id);
			if (!is_null($datadef->implementation_class)) {
				$other['implementation_class']=$datadef->implementation_class;
			}
			
			sendTo($this->name
				  ,'view'
				  ,$this->_modules
				  ,$other);
		}

		if (count($errors)>0) {
			$flash->addErrors($errors);
		}
		
		$this->view->set('data_tree',$this->getTemplateName('view_file_data'));	
		$this->view->set('doc', $doc);
		$this->view->set('XML_TEXT_NODE', XML_TEXT_NODE);
		$this->view->set('missing_data', $edi->isMissingData($this->_data['filename']));
		
		$type=($datadef->direction=='IN')?'import':'export';
		$this->view->set('type',$type);

		if ($datadef->direction=='IN')
		{
			$this->view->set('validate','Validate');
		}
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'actions',
			array(
				'View All' => array(
					'tag' => 'View All Transfer Types',
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'index'
								 )
				)
			)
		);
		
		$sidebarlist['upload_files'] = array(
					'tag'=>'upload files',
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'upload_file'
								 )
				);
		
		$sidebarlist=array();
		$sidebarlist[$datadef->name] = array(
					'tag' => 'view outstanding files to '.$type,
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'view'
								 ,'id'=>$datadef->id
								 )
				);
		$sidebarlist[$datadef->name.'errors'] = array(
					'tag' => 'view outstanding error files',
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'view'
								 ,'action_type'=>'ERR'
								 ,'id'=>$datadef->id
								 )
				);
		$sidebarlist['edit'] = array(
					'tag'=>'edit',
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'edit'
								 ,'id'=>$datadef->id
								 )
				);
		$sidebarlist['delete'] = array(
					'tag'=>'delete',
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'delete'
								 ,'id'=>$datadef->id
								 )
				);
		
		$sidebar->addList($datadef->name, $sidebarlist);
		
		$this->sidebarRelatedItems($sidebar, $datadef);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		$this->view->set('page_title', $this->getPageName('Data Definition '.$datadef->name, 'View Contents of file '.$this->_data['filename'].' : '));
	}

	public function load_missing_data ()
	{
		
		$flash=Flash::Instance();
		
		$errors = array();
		
		if (!$this->checkParams('id') || !$this->checkParams('filename') || !$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		$datadef = $this->_uses[$this->modeltype];
		
		$edi=$datadef->setEdiInterface();

		if (!$edi->isValid())
		{
			$flash->addError('Error getting EDI definition');
			sendBack();
		}
		
		if (!$edi->add_missing_data($this->_data['filename'], $errors))
		{
			$errors[] = 'Error loading missing data';
		}
		
		if (count($errors) > 0)
		{
			$this->refresh();
		}
		else
		{

			$other=array('id'		=> $datadef->id
						,'validate'	=> TRUE
						,'filename'	=> $this->_data['filename']);
			
			if (!is_null($datadef->implementation_class)) {
				$other['implementation_class']=$datadef->implementation_class;
			}
			
			sendTo($this->name
				  ,'view_file'
				  ,$this->_modules
				  ,$other);
		}
	}
	
	public function process_files () {

		$flash=Flash::Instance();
		
		if (!$this->checkParams(array($this->modeltype, 'EDITransactionLog')) || !$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		$datadef = $this->_uses[$this->modeltype];
		
		$edi=$datadef->setEdiInterface();

		if (!$edi->isValid())
		{
			$flash->addError('Error getting EDI definition');
			sendBack();
		}
		
		foreach ($this->_data['EDITransactionLog'] as $id => $data)
		{
			// limit processing of each file to 60 seconds 
			set_time_limit(60);
			if (!$edi->processFile($data, $errors))
			{
				$errors[$this->_data['filename']]='Error processing '.$this->_data['filename'];
			}			
		}
		
		if (count($errors)>0)
		{
			$flash->addErrors($errors);
			$flash->addError('Error processing files');
		}
		else
		{
			$flash->addMessage('File '.$this->_data['filename'].' Process OK');
		}
		
		sendTo($_SESSION['refererPage']['controller']
			  ,$_SESSION['refererPage']['action']
			  ,$_SESSION['refererPage']['modules']
			  ,isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
	}
	
	public function process_file () {
		
		$flash=Flash::Instance();
		
		if (!$this->checkParams('id') || !$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		$datadef = $this->_uses[$this->modeltype];
		
		$edi=$datadef->setEdiInterface();

		if (!$edi->isValid())
		{
			$flash->addError('Error getting EDI definition');
			sendBack();
		}
					
		$errors=array();
		if (!$edi->processFile($this->_data, $errors)) {
			if (count($errors)>0) {
				$flash->addErrors($errors);
			} else {
				$flash->addError('Error processing '.$this->_data['filename']);
			}
		} else {
			$flash->addMessage('File '.$this->_data['filename'].' Process OK');
		}

		$other=array('id'=>$datadef->id);
		if (!is_null($datadef->implementation_class)) {
			$other['implementation_class']=$datadef->implementation_class;
		}
		
		sendTo($this->name
			  ,'view'
			  ,$this->_modules
			  ,$other);
	
	}

	public function viewbyname () {
		
		$flash=Flash::Instance();
		
		if (isset($this->_data['name'])) {
			$datadef=$this->_uses[$this->modeltype];
			$datadef->loadBy('name', $this->_data['name']);
			if ($datadef->isLoaded()) {
				sendTo($this->name
					  ,'view'
					  ,$this->_modules
					  ,array('id'=>$datadef->id));
			}
			$flash->addError('Cannot find data transfer type '.$this->_data['name']);
		} else {
			$flash->addError('Invalid data for this action');
		}
		sendBack();
		
	}
	
	public function data_definition_details() {
		
		if (!$this->checkParams('id')) {
			$this->dataError();
			sendBack();
		}
		
		$this->view->set('items',DataDefinitionDetailCollection::getDefinitionTree($this->_data['id']));

//		$this->view->set('page_title',$this->getPageName());
		
	}
		
	public function viewExternalSystem () {
		$this->index();
		$this->setTemplateName('index');
	}
	
	public function upload_file()
	{
		
		$this->loadData();
		
		$datadef = $this->_uses[$this->modeltype];
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('transfer_type', '=', 'LOCAL'));
		$cc->add(new Constraint('direction', '=', 'IN'));
		$datadefs = $datadef->getAll($cc);
		$this->view->set('datadefs', $datadefs);
		
		if ($datadef->isLoaded())
		{
			$this->view->set('page_title', 'Upload File - Import Type '.$datadef->name);
		}
		else
		{
			$this->view->set('page_title', 'Upload File - Select Type');
			$datadef->load(key($datadefs));
		}
		
		$this->view->set('datadef', $datadef);
		$this->view->set('local_name', $datadef->file_prefix.(is_null($datadef->file_extension)?'':'.'.$datadef->file_extension));

	}
	
	public function save_file()
	{
		$flash = Flash::Instance();
		
		$errors = array();
		
		$data = $_FILES['file'];

		if(empty($data['name']))
		{
			$errors[] = 'You must select a file to upload';
		}
		elseif(!is_uploaded_file($data['tmp_name']))
		{
			$errors[]='Error with file upload- it would appear you\'re trying to be naughty';
		}
		
		if (empty($this->_data[$this->modeltype]['working_folder']))
		{
			$errors[] = 'You must specify a folder';
		}
		
		if (empty($this->_data[$this->modeltype]['local_name']))
		{
			if (!empty($data['name']))
			{
				$this->_data[$this->modeltype]['local_name'] = $data['name'];
			}
			else
			{
				$errors[] = 'You must specify a file name';
			}
		}
		
		if (count($errors) == 0)
		{
			$this->_data[$this->modeltype]['working_folder'] .= (substr($this->_data[$this->modeltype]['working_folder'], -1) == DIRECTORY_SEPARATOR)?'':DIRECTORY_SEPARATOR;
			$new_name=DATA_ROOT.'company'.EGS_COMPANY_ID.DIRECTORY_SEPARATOR.$this->_data[$this->modeltype]['working_folder'].$this->_data[$this->modeltype]['local_name'];
			
			if(!move_uploaded_file($data['tmp_name'],"$new_name")) {
				$errors[]='Error moving uploaded file, contact the server admin';
			}

			if(!chmod("$new_name",0655)) {
				$errors[]='Error changing permission of uploaded file, contact the server admin';
			}
		}
		
		if (count($errors)>0)
		{
			$flash->addErrors($errors);
			$this->refresh();
		}
		else
		{
			$flash->addMessage('File uploaded');
			if ($this->_data['saveAnother'])
			{
				sendTo($this->name, 'upload_file', $this->_modules, array('id'=>$this->_data[$this->modeltype]['id']));
			}
			else
			{
				sendTo($this->name, 'view', $this->_modules, array('id'=>$this->_data[$this->modeltype]['id']));
			}
		}
		
	}

	/*	
	 * Protected Functions
	 */
	protected function getPageName($base='',$action='') {
		return parent::getPageName((empty($base)?'Data Definitions':$base),$action);
	}

	/*
	 * Ajax Functions
	 */
	public function getDefinitionDetail($_datadef_id='')
	{
// Used by Ajax to return Centre list after selecting the Account

		if(isset($this->_data['ajax'])) {
			if(!empty($this->_data['datadef_id'])) { $_datadef_id=$this->_data['datadef_id']; }
		}
		
		$datadef = $this->_uses[$this->modeltype];

		if (!empty($_datadef_id))
		{
			$datadef->load($_datadef_id);
		}
		
		
		$output['local_name']		= array('data'=>$datadef->file_prefix.(is_null($datadef->file_extension)?'':'.'.$datadef->file_extension),'is_array'=>false);
		$output['working_folder']	= array('data'=>$datadef->working_folder,'is_array'=>false);
		
		// could we return the data as an array here? save having to re use it in the new / edit?
		// do a condition on $ajax, and return the array if false
		if(isset($this->_data['ajax'])) {
			$this->view->set('data',$output);
			$this->setTemplateName('ajax_multiple');
		} else {
			return $output;
		}	

		
	}
	
	/*
	 * Private functions
	*/
}

// End of DatadefinitionsController
