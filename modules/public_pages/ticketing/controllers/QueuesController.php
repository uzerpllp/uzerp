<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class QueuesController extends TicketingController {

	protected $version='$Revision: 1.4 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		
		$this->_templateobject = new TicketQueue();
		$this->uses($this->_templateobject);
		
	}
	
	public function index($collection = null, $sh = '', &$c_query = null) {
		$this->view->set('clickaction', 'view');
		parent::index(new TicketQueueCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new'),
					'tag'=>'New Queue'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}
	
	public function view() {
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		$ticket_queue=$this->_uses[$this->modeltype];
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'actions',
			array(
				'all' => array(
					'tag' => 'View All',
					'link' => array(
						'modules'=>$this->_modules,
						'controller'=>$this->name,
						'action'=>'index',
					)
				),
				'edit' => array(
					'tag' => 'Edit',
					'link' => array(
						'modules'=>$this->_modules,
						'controller'=>$this->name,
						'action'=>'edit',
						'id'=>$ticket_queue->id
					)
				),
				'spacer',
				'delete' => array(
					'tag' => 'Delete',
					'link' => array(
						'modules'=>$this->_modules,
						'controller'=>$this->name,
						'action'=>'delete',
						'id'=>$ticket_queue->id
					)
				)
				)
		);
		
		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
	}
	
	public function _new() {
		parent::_new();
		$user=new User();
		$this->view->set('users', $user->getAll());
	}
	
	public function getEmail($_username='') {
		/*
		 * We only want to override the function parameters if the call has come from
		 * an ajax request, simply overwriting them as we were leads to a mix up in
		 * values
		 */
		if(isset($this->_data['username'])) {
			if(!empty($this->_data['username'])) { $_username=$this->_data['username']; }
		}
		
		// Used by Ajax to return the person's email address
		// If no person is supplied, or they have no email address
		// look for the company technical email address
		// if still no email address is found, use the logged in user details
		$user=new User();
		$user->load($_username);
		if ($user) {
			$email=$user->email;
			if (!is_null($user->person_id)
			 && !is_null($user->persondetail->email->contactmethod)) {
					$email=$user->persondetail->email->contactmethod;
			}
		}
		if(isset($this->_data['ajax'])) {
			$this->view->set('value',$email);
			$this->setTemplateName('text_inner');
		} else {
			return $email;
		}
	}
	
}
?>