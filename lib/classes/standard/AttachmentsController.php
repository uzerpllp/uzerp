<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class AttachmentsController extends Controller {

	protected $version = '$Revision: 1.6 $';
	
	protected $_templateobject;
	protected $attachmentModule;
	protected $attachmentController;
	protected $attachmentModel;
	protected $attachmentIdField;
	
	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('EntityAttachment');
		
		$this->uses($this->_templateobject);
		
		$this->view->set('controller', $this->name);
		$this->setController($this->name);
		
	}
	
	protected function setModule($module)
	{
		$this->attachmentModule = $module;
	}
	
	protected function setController($controller)
	{
		$this->attachmentController = $controller;
	}
	
	protected function setIdField($id_field)
	{
		$this->attachmentIdField = $id_field;
	}
	
	protected function setModel($model)
	{
		$this->attachmentModel = $model;
	}
	
	protected function setTitle($title)
	{
		$this->attachmentTitle = $title;
	}
	
	protected function setAttributes()
	{
		if (empty($this->attachmentModule))
		{
			$module = ModuleObject::getModule($this->_data['module']);
			
			if ($module->isLoaded())
			{
				$this->setModule($module->name);
				
				if (!empty($this->_data['data_model']))
				{
					
					$this->setModel($this->_data['data_model']);
					
					// TODO: If data model is ModuleObject or ModuleComponent,
					// Check if the module/component is in SystemAttachments
					// using entity_id and data_model
				}
				
				if (empty($this->_data['entity_id']) && $this->attachmentModel == 'moduleobject')
				{
					$this->_data['entity_id'] = $module->{$module->idField};
				}
				
 				if (empty($this->attachmentController))
 				{
		 			$this->setController($this->name);
 				}
 				
 				// Need a better way of linking controller->model
 				// perhaps should do it in module_components?
				if (empty($this->attachmentModel))
				{
	 				$this->setModel(str_replace('scontroller', '', $controller->name));
				}
			}
			
		}
		
		$this->getEntityId();
				
	}
	
	public function __call($method,$args)
	{
		if (!empty($_GET[$this->attachmentIdField]))
		{
			$_GET['entity_id'] = $_GET[$this->attachmentIdField];
			
			unset($_GET[$this->attachmentIdField]);
		}
		
		$_GET['data_model'] = $this->attachmentModel;
		
		parent::__call($method,$args);
	}
	
	public function index()
	{
		$this->setAttributes();
		
		$this->view->set('clickaction', 'view_file');
		
		$entityAttachments = new EntityAttachmentCollection($this->_templateobject);
		
		$sh = $this->setSearchHandler($entityAttachments);
		
		$sh->AddConstraint(new Constraint('data_model', '=', $this->attachmentModel));
		
		if (!empty($this->_data['entity_id']))
		{
			$sh->AddConstraint(new Constraint('entity_id', '=', $this->_data['entity_id']));
		}
		
		parent::index($entityAttachments, $sh);
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'	=> array('module'		=> $this->attachmentModule
									,'controller'	=> $this->attachmentController
									,'action'		=> 'new'
									,'entity_id'	=> $this->_data['entity_id']
									,'data_model'	=> $this->attachmentModel),
					'tag'	=> 'New Attachment'
				)
			)
		);
		
		$this->view->register('sidebar',$sidebar);
		
		$this->view->set('sidebar',$sidebar);
		
		$this->view->set('allow_delete', true);
		
		$this->view->set('title', 'Viewing Files for '.$this->getTitle($this->_data['entity_id']));
		
		$this->setTemplateName('attachments_index');
		
	}
	
	public function view()
	{
		
		$entityattachment = $this->_uses['EntityAttachment'];
		
		if (!empty($this->_data['id']))
		{
			$entityattachment->load($this->_data['id']);
		}
		elseif (!empty($this->_data['entity_id']))
		{
			$entityattachment->loadBy('entity_id', $this->_data['entity_id']);
		}
		
		if ($entityattachment->isLoaded())
		{
			$this->_data['data_model'] = $entityattachment->data_model;
			$this->_data['entity_id'] = $entityattachment->entity_id;
		}
		$this->_uses['File'] = DataObjectFactory::Factory('File');
		$this->_uses['File']->load($entityattachment->file_id);
		
		$this->setAttributes();
		
		$this->view->set('link',
						array('module'		=> $this->attachmentModule
							 ,'controller'	=> $this->attachmentController
							 ,'action'		=> 'view_file'
							 ,'file_id'		=> $entityattachment->file_id
							 ,'_target'		=> '_blank')
		);
		
		$this->view->set('title', 'Attachment details for '.$this->getTitle($this->_data['entity_id']));
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'download'=>array(
					'link'	=> array('module'					=> $this->attachmentModule
									,'controller'				=> $this->attachmentController
									,'action'					=> 'download'
									,$entityattachment->idField	=> $entityattachment->{$entityattachment->idField}
									),
					'tag'	=> 'Download Attachment'
				),
				'delete'=>array(
					'link'	=> array('module'		=> $this->attachmentModule
									,'controller'	=> $this->attachmentController
									,'action'		=> 'delete'
									,'id'			=> $entityattachment->id
									),
					'tag'	=> 'Delete Attachment'
				)
			)
		);
		
		$this->view->register('sidebar',$sidebar);
		
		$this->view->set('sidebar',$sidebar);
		
		$this->setTemplateName('attachments_view');
		
	}
	
	public function view_file ()
	{
		$attachment = $this->_uses[$this->modeltype];
		
		if (!empty($this->_data['file_id']))
		{
			$attachment->file_id = $this->_data['file_id'];
		}
		else
		{
			$attachment->load($this->_data['id']);
		}
		
		// Load file
		$file = DataObjectFactory::Factory('File');
		$file->load($attachment->file_id);
		
		$db = &DB::Instance();
		
	    header('Content-Type: ' . $file->type);
	    header("Content-Disposition: inline; filename=\"" . $file->name."\";");
	    header('Content-Transfer-Encoding: binary');
	    header('Content-Length: ' . $file->size);
	    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	    
	    ob_start();
	    
	    echo $db->BlobDecode($file->file, $file->size); 
	    
	    @ob_flush();
	    
	    $content = ob_get_contents();
	    
	    ob_end_clean();
	    
	    echo $content;
	    
//		$file->SendToBrowser();
//	    $file->SendToBrowser('inline');
	     
	}
	
	public function download()
	{
        // Grab attachment
//		$attachment = new EntityAttachment();
		$attachment = $this->_uses[$this->modeltype];
		
		if (!empty($this->_data['file_id']))
		{
			$attachment->file_id = $this->_data['file_id'];
		}
		else
		{
			$attachment->load($this->_data['id']);
		}
		
		// Load file
		$file = DataObjectFactory::Factory('File');
		$file->load($attachment->file_id);
		
		// Upload to browser
		$file->SendToBrowser();
		
		// Prevent standard smarty output from occuring. FIXME: Is this the best way of achieving this?
		exit(0);
	}
	
	public function delete()
	{
		if (!$this->loadData())
		{
			sendBack();
		}
		
		$errors = array();
		
		$flash = Flash::Instance();
		
		$attachment = $this->_uses[$this->modeltype];
				
		$file = DataObjectFactory::Factory('File');
		
		$file->load($attachment->file_id);
		
		if (!$attachment->delete(null, $errors))
		{
			$flash->addErrors($errors);
			
			$flash->addError('Error deleting file '.$file->name.' version '.$file->revision.' : '.$db->ErrorMsg());
			
		}
		else
		{
			$flash->addMessage('File '.$file->name.' version '.$file->revision.' deleted OK');
		}
		
		sendBack();
		
	}
	
	public function edit()
	{
		if (!$this->loadData())
		{
			sendBack();
		}
		
		$attachment = $this->_uses[$this->modeltype];

		if ($attachment->isLoaded())
		{
			// This is an edit - i.e. replace current with new
			$file = DataObjectFactory::Factory('File');
			
			$file->load($attachment->file_id);
			
			$this->view->set('file', $file);
			
			$this->_data['entity_id']	= $attachment->entity_id;
			$this->_data['data_model']	= $attachment->data_model;
		}
		
		$this->_new();
	}
	
	public function _new()
	{
		
		$this->setAttributes();
		
		$this->view->set('entity_id', $this->_data['entity_id']);
	    
	    if (!empty($this->_data['data_model']))
	    {
			$this->view->set('data_model', $this->_data['data_model']);
	    }
	    elseif(!empty($this->attachmentModel))
	    {
	    	$this->view->set('data_model', $this->attachmentModel);
	    }
	    
		$this->view->set('attachmentController', $this->attachmentController);
		
		$this->view->set('title', 'Load Attachment for '.$this->getTitle($this->_data['entity_id']));
		
		$this->setTemplateName('attachments_new');
		
	}
	
	public function save()
	{
		
		$errors = array();
		
		$flash = Flash::Instance();
		
// Need to upload file before checking
		$file = File::Factory($_FILES['file'], $errors, DataObjectFactory::Factory('File'));
		
		// Check if this file name already exists
		$collection = new EntityAttachmentCollection();
		
		$sh = new SearchHandler($collection, FALSE);
		
		$sh->addConstraint(new Constraint('data_model', '=', $this->_data['data_model']));
		$sh->addConstraint(new Constraint('entity_id', '=', $this->_data['entity_id']));
		$sh->addConstraint(new Constraint('file', '=', $file->name));
		
		$data = $collection->load($sh, null, RETURN_ROWS);
		
		$count = count($data);
		
		$update = FALSE;
		
		// Should only be one or none; otherwise this is an error
		if ($count > 1)
		{
			$errors[] = 'Found '.$count.' versions of this file';
		}
		elseif ($count > 0)
		{
			$row				= current($data);
			$current_revision 	= $row['revision'];
			$new_revision		= ++$row['revision'];
			$update				= TRUE;
		}
		else
		{
			$current_revision	= 0;
			$new_revision		= 1;
		}
		
		if (empty($this->_data['revision']))
		{
			$this->_data['revision'] = $new_revision;
		}
		elseif ($this->_data['revision'] <= $current_revision)
		{
			$errors[] = 'Current version '.$current_revision.' is the same or later than input version '.$this->_data['revision'];
		}
		
		$db = DB::Instance();
		
		$db->StartTrans();
		
		$attachment_save = $file_save = FALSE;
		
		// Save the File data
		if (empty($errors))
		{
			$file->note		= $this->_data['note'];
			$file->revision	= $this->_data['revision'];
				
			$file_save = $file->save();
			
		}
		
		// If file save OK, save the attachment details
		if ($file_save)
		{
			$attachment_data = array();
			
			if ($update)
			{
				$attachment_data['id']	= $row['id'];
			}
			
			$attachment_data['entity_id']	= $this->_data['entity_id'];
			$attachment_data['data_model']	= $this->_data['data_model'];
			$attachment_data['file_id']		= $file->id;
			
			$attachment = EntityAttachment::Factory(
			    $attachment_data,
			    $errors,
		    	$this->modeltype
			);
			
			if (empty($errors))
			{
				$attachment_save = $attachment->save();
			}
		}
		
		if ($update && $attachment_save)
		{
			// delete the old file entry for an update
			$old_file = DataObjectFactory::Factory('File');
			$file_save = $old_file->delete($row['file_id'], $errors);
		}
		
		// Now check and tidy up
		if (!$file_save || !$attachment_save)
		{
			$errors[] = 'Error loading file';
		}
		
		if (!empty($errors))
		{
			$flash->addErrors($errors);
			$db->FailTrans();
		}
		else
		{
			if ($update)
			{
				$flash->addMessage('File '.$file->name.' version '.$current_revision.' replaced with version '.$new_revision);
			}
			else
			{
				$flash->addMessage('File '.$file->name.' version '.$new_revision.' uploaded OK');
			}
		}
		
		$db->CompleteTrans();
		
		// Return to calling module/controller
		sendTo($_SESSION['refererPage']['controller']
			  ,$_SESSION['refererPage']['action']
			  ,$_SESSION['refererPage']['modules']
			  ,isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
		
	}
	
	/*
	 * Private Functions
	 */
	private function getTitle($_id)
	{
		$model = DataObjectFactory::Factory($this->attachmentModel);
		
		$model->load($_id);
		
		if ($this->attachmentModel == 'modulecomponent')
		{
			return $model->title;
		}
		else
		{
			return $model->getTitle().' '.$model->getIdentifierValue();
		}
		
	}
	
	private function getEntityId()
	{
		if (!empty($this->attachmentIdField) && !empty($this->_data[$this->attachmentIdField]))
		{
			$this->_data['entity_id'] = $this->_data[$this->attachmentIdField];
		}
	}
	
}

// End of AttachmentsController
