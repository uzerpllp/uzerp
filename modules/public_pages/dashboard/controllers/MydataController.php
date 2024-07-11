<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class MydataController extends Controller {

	protected $version = '$Revision: 1.12 $';
	
	function __construct($module = NULL, $action = NULL)
	{
		parent::__construct($module,$action);
		
		$this->MyDataPath	= DATA_USERS_ROOT . EGS_USERNAME . DIRECTORY_SEPARATOR;
		
		$this->MyDataURL	= DATA_USERS_URL . EGS_USERNAME . DIRECTORY_SEPARATOR;
	}

	function index(\DataObjectCollection $collection, $sh = '', &$c_query = \null)
	{
		$dir_tree = array();
		
		if (is_dir($this->MyDataPath))
		{
			
			$dir_tree = $this->buildDirTree($this->MyDataPath, $this->MyDataURL);
			
			// sort files by name...
			foreach ($dir_tree['directory'] as $type => $files)
			{
				
				// ... but only if the array isn't empty
				if (!empty($dir_tree['directory'][$type]['file']))
				{
					uasort($dir_tree['directory'][$type]['file'], 'cmp_filename');
				}
				
			}
			
		}
		else
		{
			mkdir($this->MyDataPath);
		}
		
		$dir_tree['directory']['Attachments'] = $this->getAttachments();
		
		$this->view->set('mydata', $dir_tree);
//		echo 'files<pre>'.print_r($dir_tree, true).'</pre><br>';
		
		$this->view->set('component_id', ModuleComponent::getComponentId($this->_modules['module'], strtolower(get_class($this))));
	}

	function delete($modelName)
	{
		
		$flash=Flash::Instance();
		
		if ($this->checkParams('id'))
		{
			
			if (file_exists($this->MyDataPath.$this->_data['id']))
			{
				unlink($this->MyDataPath.$this->_data['id']);
			} 
			else 
			{
				$flash->addError('Failed to delete '.$this->_data['id']);
			}
			
			sendTo($this->name, null, $this->_modules);
			
		}
		else 
		{
			sendBack();
		}
		
	}
	
	function delete_files()
	{
//		echo 'delete files data <pre>'.print_r($this->_data, true).'</pre><br>';
		
		$flash=Flash::Instance();
		
		$errors = array();
		
		if (isset($this->_data['file']))
		{
			foreach ($this->_data['file'] as $file=>$value)
			{
				if (isset($value['delete_file']))
				{
					$function = 'delete_'.$value['type'];
					
					if ($this->$function($file, $errors))
					{
						$flash->addMessage('file '.$value['name'].' deleted');
					} 
					else
					{
						$flash->addErrors($errors);
						$flash->addError('Failed to delete '.$value['name']);
					}
				}
			}
		}
		
		sendBack();
	}
	
	/*
	 * Private Functions
	 */
	private function buildDirTree($mydatapath, $mydataurl, $filename=null)
	{
		
		$dirobjs	= array();
		$mydata		= dir($mydatapath);
		
		while (($current = $mydata->read()) !== FALSE) 
		{
			
			if ($current != '.' && $current != '..')
			{
				
				if (in_array(strtolower($current), array('tmp', 'thumbs')))
				{
					continue;
				}
				
				if (is_dir($mydatapath.$current))
				{
					
					$dirobjs['directory'][$current] = $this->buildDirTree(
						$mydatapath . $current . DIRECTORY_SEPARATOR, 
						$mydataurl . $current . DIRECTORY_SEPARATOR, 
						$filename . $current . DIRECTORY_SEPARATOR
					);
					
				}
				else 
				{
					
					$stat = stat($mydatapath . $current);
					
					$details = array(
						'name'		=> $current,
						'link'		=> $mydataurl.$current,
						'type'		=> 'file',
						'delete'	=> array(
								'modules'		=>	$this->_modules,
								'controller'	=> $this->name,
								'action'		=> 'delete',
								'id'			=> $filename . $current
						),
						'size'	=> sizify($stat['size']),
						'mtime'	=> date(DATE_FORMAT, $stat['mtime'])
					);
					
					$dirobjs['file'][] = $details;
					
				}
				
			}
			
		}
		
		$mydata->close();
		
		return $dirobjs;
		
	}
	
	private function delete_file($_file)
	{
		return (file_exists($this->MyDataPath.$_file) && unlink($this->MyDataPath.$_file));
	}
	
	private function delete_attachment($_id, &$errors = array())
	{
		$attachment = DataObjectFactory::Factory('EntityAttachment');
		
		return $attachment->delete($_id, $errors);
	}
	
	private function getAttachments()
	{
		$attachments = new EntityAttachmentCollection();
		
		$sh = new SearchHandler($attachments, FALSE);
		
		$sh->addConstraint(new Constraint('data_model', '=', 'modulecomponent'));
		$sh->addConstraint(new Constraint('entity_id', '=', ModuleComponent::getComponentId($this->_modules['module'], strtolower(get_class($this)))));
		$sh->addConstraint(new Constraint('createdby', '=', EGS_USERNAME));
		
		$files = $attachments->load($sh, null, RETURN_ROWS);
		
		$dirobjs	= array();
		
		if ( count($files) > 0 )
		{
			foreach ($files as $attachment)
			{
				$link = '/?'.setParamsString(array('modules'	=> $this->_modules
												  ,'controller'	=> 'attachments'
												  ,'action'		=> 'view_file'
												  ,'other'		=> array(file_id => $attachment['file_id'])));
				
				$details = array(
						'name'		=> $attachment['file'],
						'link'		=> $link,
						'type'		=> 'attachment',
						'delete'	=> array(
								'modules'		=> $this->_modules,
								'controller'	=> 'attachments',
								'action'		=> 'delete',
								'id'			=> $attachment['id']
						),
						'size'	=> sizify($attachment['size']),
						'mtime'	=> un_fix_date($attachment['lastupdated'])
					);
				
				$dirobjs['file'][] = $details;
			}
		}
		
		return $dirobjs;
	}
}

// end of MydataController.php