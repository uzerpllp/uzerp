<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class AttachmentsController extends Controller {

	protected $version='$Revision: 1.3 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new TicketAttachment();
		$this->uses($this->_templateobject);
		
		$this->view->set('controller', 'Attachments');
	}
	
	public function index($collection = null, $sh = '', &$c_query = null){
		$this->view->set('clickaction', 'view');
		parent::index(new TicketAttachmentCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('module'=>'ticketing','controller'=>'attachments','action'=>'new'),
					'tag'=>'New Attachment'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}
	
	public function view() {
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'download'=>array(
					'link'=>array('module'=>'ticketing','controller'=>'attachments','action'=>'download', 'id' => $this->_data['id']),
					'tag'=>'Download Attachment'
				)
			)
		);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		$ticketattachment = $this->_uses['TicketAttachment'];
		$ticketattachment->load($this->_data['id']);
		
		$this->_uses['File'] = new File();
		$this->_uses['File']->load($ticketattachment->file_id);
	}
	
	public function download() {
        // Grab attachment
		$attachment = new TicketAttachment();
		$attachment->load($this->_data['id']);
		
		// Load file
		$file = new File();
		$file->load($attachment->file_id);
		
		// Upload to browser
		$file->SendToBrowser();
		
		// Prevent standard smarty output from occuring. FIXME: Is this the best way of achieving this?
		exit(0);
	}
	
	public function _new() {
	    $this->view->set('ticket_id', $this->_data['ticket_id']);
	}
	
	public function save($modelName = null, $dataIn = [], &$errors = []) : void {
		$errors = array();
		$file = File::Factory($_FILES['file'],$errors, new File());
		$file->save();
		
		$ticketAttachment = TicketAttachment::Factory(
		    array(
		        'ticket_id' => $this->_data['ticket_id'],
		        'file_id' => $file->id
		    ),
		    $errors,
		    new TicketAttachment()
		);
		$ticketAttachment->save();
		
	    sendTo('Attachments', 'view', array('ticketing'), array('id'=>$ticketAttachment->id));
	}

	public function viewticket() {
		$this->related['ticket']['clickaction']='download';
		$this->viewRelated('ticket');
	}
}
?>