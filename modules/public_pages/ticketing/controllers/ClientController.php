<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class ClientController extends Controller {
	
	protected $version='$Revision: 1.7 $';
	
	protected $_templateobject;
	
	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new Ticket();
		$this->uses($this->_templateobject);
		$this->uses(new TicketResponse());
		
		$this->view->set('controller', 'ticket');
	}
	
	public function index() {
		$collection = new TicketCollection($this->_templateobject);
		$sh = new SearchHandler($collection, false);
		$sh->extract();
		$sh->setFields(array('id'=>'id'
							,'summary'=>'summary'
							,'originator_person'=>'originator_person'
							,'client_ticket_status'=>'client_ticket_status'
							,'client_ticket_priority'=>'client_ticket_priority'
							,'client_ticket_severity'=>'client_ticket_severity'
							,'created'=>'created'
							,'lastupdated'=>'lastupdated'));
		
		$this->setSearch('TicketsSearch', 'useClient');
		
		parent::index($collection, $sh);
		$this->view->set('no_delete',true);
		$this->view->set('clickaction', 'view');


		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('module'=>'ticketing','controller'=>'client','action'=>'new'),
					'tag'=>'New Ticket'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}
	
	public function _new () {
		parent::_new();
		
		$this->view->set('new_ticket', !isset($this->_data['id']));
	}
	
	private function &findDefault($object) {
		$sh = new SearchHandler($object);
		$sh->AddConstraint(new Constraint('usercompanyid', '=', EGS_COMPANY_ID));
		$object->load($sh);
		$options = $object->getContents();
		
		return $options[0];
	}
	
	public function save () {
		// Fill client hidden sections
		$user = new User();
		$person = new Person();
		$user->load(EGS_USERNAME);
		if (!is_null($user->person_id)) {
			$person->load($user->person_id);
		}
		// Is this quick entry?
		if ($this->_data['TicketResponse']['type'] == 'quick') {
			$this->_data['TicketResponse']['type'] = 'site';
			
			$config = new TicketConfigurationCollection(new TicketConfiguration);
			$sh = new SearchHandler($config);
			$sh->AddConstraint(new Constraint('usercompanyid', '=', EGS_COMPANY_ID));
			$config->load($sh);
			$config = $config->getContents();
			
			if (count($config) !== 1) {
				// Make one.
				$newConfig['usercompanyid'] = EGS_COMPANY_ID;
				
				$priority = self::findDefault(new TicketPriorityCollection(new TicketPriority));
				$newConfig['client_ticket_priority_default'] = $priority->id;
				$newConfig['internal_ticket_priority_default'] = $priority->id;
				
				$severity = self::findDefault(new TicketSeverityCollection(new TicketSeverity));
				$newConfig['client_ticket_severity_default'] = $severity->id;
				$newConfig['internal_ticket_severity_default'] = $severity->id;
				
				$queue = self::findDefault(new TicketQueueCollection(new TicketQueue));
				$newConfig['ticket_queue_default'] = $queue->id;
				
				$category = self::findDefault(new TicketCategoryCollection(new TicketCategory));
				$newConfig['ticket_category_default'] = $category->id;
				
				$status = self::findDefault(new TicketStatusCollection(new TicketStatus));
				$newConfig['client_ticket_status_default'] = $status->id;
				$newConfig['internal_ticket_status_default'] = $status->id;
				
				$config = TicketConfiguration::Factory($newConfig);
			} else {
				$config = $config[0];
			}
			
			$this->_data['Ticket']['client_ticket_priority_id'] = $config->client_ticket_priority_default;
			$this->_data['Ticket']['ticket_queue_id'] = $config->ticket_queue_default;
		}
		
		$this->_data['Ticket']['originator_person_id'] = $user->username;
		$this->_data['Ticket']['originator_company_id'] = $user->lastcompanylogin;
		
		$this->_data['Ticket']['internal_ticket_severity_id'] = $this->_data['Ticket']['client_ticket_severity_id'];
		$this->_data['Ticket']['internal_ticket_priority_id'] = $this->_data['Ticket']['client_ticket_priority_id'];
		
		if (!isset($this->_data['Ticket']['id'])) {
			// Force 'new' status initialy
			$ts = new TicketStatusCollection(new TicketStatus);
			$sh = new SearchHandler($ts);
			$sh->addConstraint(new Constraint('usercompanyid', '=', EGS_COMPANY_ID));
			$sh->addConstraint(new Constraint('status_code', '=', 'NEW'));
			$ts->load($sh);
			
			$statuses = $ts->getContents();
			$status = $statuses[0]; // Should only ever be one status, this should be regulated by earlier validation
			
			$this->_data['Ticket']['client_ticket_status_id'] = $status->id;
			$this->_data['Ticket']['internal_ticket_status_id'] = $status->id;
		}
		
		if (isset($this->_data['Ticket']['id'])) {
			$originalTicket = new Ticket();
			$originalTicket->load($this->_data['Ticket']['id']);
			
			$changes = array(
				array(
					'param' => 'client_ticket_status_id',
					'friendly' => 'Status',
					'object' => 'TicketStatus'
				),
				array(
					'param' => 'client_ticket_priority_id',
					'friendly' => 'Priority',
					'object' => 'TicketPriority'
				),
				array(
					'param' => 'client_ticket_severity_id',
					'friendly' => 'Severity',
					'object' => 'TicketSeverity'
				),
				array(
					'param' => 'ticket_queue_id',
					'friendly' => 'Queue',
					'object' => 'TicketQueue'
				)
			);
			
			$changeText = array();
			
			foreach ($changes as $change) {
				if($this->_data['Ticket'][$change['param']] != $originalTicket->$change['param']) {
					$was = new $change['object'];
					$now = new $change['object'];
					$was->load($originalTicket->$change['param']);
					$now->load($this->_data['Ticket'][$change['param']]);
					$changeText[] = $change['friendly'] . ': was ' . $was->name . ' now ' . $now->name . '.'; 
				}
			}
			
			if(count($changeText) > 0) {
				$errors = array();
				$ticketResponse = TicketResponse::Factory(
					array(
						'ticket_id' => $this->_data['Ticket']['id'],
						'internal' => 'false',
						'body' => implode("\n", $changeText),
						'type' => 'status',
						'owner' => EGS_USERNAME
					),
					$errors,
					'TicketResponse'
				);
				$ticketResponse->save();
			
				$queue = new TicketQueue();
				$queue->load($originalTicket->ticket_queue_id);
			
				// Send mail
				$headers = array(
					'From' => $queue->email_address
				);
			
				$header_string = "";
				foreach ($headers as $header => $value) {
					$header_string .= $header . ': ' . $value . "\r\n";
				}
			
				$body = TicketingUtils::StatusPlate($originalTicket) . implode("\n", $changeText);
			
				$recipients = TicketingUtils::GetRecipients($originalTicket);
			
				foreach ($recipients as $recipient) {
					mail(
						$recipient,
						're: [' . $originalTicket->ticket_queue_id . '-' . $originalTicket->id . '] ' . $originalTicket->summary,
						$body,
						$header_string
					);
				}
			}
		}
		parent::save('Ticket');
		$ticket_id = $this->_data['id'];
		$this->_data['Ticket']['id'] = $this->_data['id'];
		
		if(isset($this->_data['id'])) {
			$this->_data['TicketResponse']['ticket_id'] = $ticket_id;
			parent::save('TicketResponse');
		}
		
		sendTo('Client', 'view', array('ticketing'), array('id'=>$ticket_id));
	}
	
	public function view() {
		$ticket=$this->_uses['Ticket'];
		$ticket->load($this->_data['id']) or sendBack();
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'currently_viewing',
			array(
				$ticket->summary => array(
					'tag' => $ticket->summary,
					'link' => array(
						'module'=>'ticketing',
						'controller'=>'client',
						'action'=>'view',
						'id'=>$ticket->id
					)
				),
				'edit' => array(
					'tag' => 'Edit',
					'link' => array(
						'module'=>'ticketing',
						'controller'=>'client',
						'action'=>'edit',
						'id'=>$ticket->id
					)
				),
				'add_response' => array(
					'tag' => 'Add Response',
					'link' => array(
						'module' => 'ticketing',
						'controller' => 'client',
						'action' => 'add_response',
						'id' => $ticket->id
					)
				)
			)
		);
		$rel_items=array();
		$ao=AccessObject::Instance();
		if($ao->hasPermission('ticketing','attachments')) {
			$rel_items += array(
				'attachments'=>array(
					'tag'=>'Attachments',
					'link'=>array('module'=>'ticketing','controller'=>'attachments','action'=>'viewticket','ticket_id'=>$ticket->id),
					'new'=>array('module'=>'ticketing','controller'=>'attachments','action'=>'new','ticket_id'=>$ticket->id)
				)
			);
		}
		if($ao->hasPermission('ticketing','hours')) {
			$rel_items += array(
				'hours'=>array(
					'tag'=>'Hours',
					'link'=>array('module'=>'ticketing','controller'=>'hours','action'=>'viewticket','ticket_id'=>$ticket->id),
					'new'=>array('module'=>'ticketing','controller'=>'hours','action'=>'new','ticket_id'=>$ticket->id)
				)
			);
		}
		if(count($rel_items)>0) {
			$sidebar->addList(
				'related_items',
				$rel_items
			);
		}
		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
		
		$responses = new TicketResponseCollection(new TicketResponse);
		$sh = new SearchHandler($responses, false);
		$sh->AddConstraint(new Constraint('ticket_id', '=', $ticket->id));
		$sh->AddConstraint(new Constraint('internal', '=', 'f'));
		$responses->load($sh);
		$this->view->set('responses', $responses->getContents());
	}
	
	public function add_response ()
	{
		parent::_new();

		$ticket=$this->_uses['Ticket'];
		$ticket->load($this->_data['id']);
		
		$ticketResponse = $this->_uses['TicketResponse'];
		$ticketResponse->ticket_id = $ticket->id;

		$responses = new TicketResponseCollection(new TicketResponse);
		$sh = new SearchHandler($responses, false);
		$sh->AddConstraint(new Constraint('ticket_id', '=', $ticket->id));
		$responses->load($sh);
		
		$this->view->set('responses', $responses->getContents());
	}
	
	public function save_response () {
		parent::save('TicketResponse');
		
		$ticket = new Ticket();
		$ticket->load($this->_data['TicketResponse']['ticket_id']);
		
		$plateout = TicketingUtils::StatusPlate($ticket);
		
		$queue = new TicketQueue();
		$queue->load($ticket->ticket_queue_id);
		
		$headers = array(
			'From' => $queue->email_address
		);
		
		// FIXME: If someone forces a file upload... I guess that causes this code to randomly send the file?
		if ($_FILES['file']['size'] > 0) {
			// Send MIME mail
			$boundary = 'EGS-Ticketing-System-' . base_convert(rand(1000,9000), 10, 2);
			$headers['Content-Type'] = 'multipart/mixed; boundary=' . $boundary;
		
			$base64 = base64_encode(file_get_contents($_FILES['file']['tmp_name']));
		
			// Yay, hand written MIME email!
			$body =
				"--$boundary\r\n" .
				"Content-Transfer-Encoding: 8bit\r\n" .
				"Content-Type: text/plain; charset=ISO-8859-1\r\n" .
				"\r\n" .
				$plateout .
				$this->_data['TicketResponse']['body'] . "\r\n" .
				"\r\n" .
				"--$boundary\r\n" .
				'Content-Type: octet/stream; name="' . $_FILES['file']['name'] . '"' . "\r\n" .
				"Content-Transfer-Encoding: base64\r\n" .
				'Content-Disposition: attachment; filename="' . $_FILES['file']['name'] . '"' . "\r\n" .
				"\r\n" .
				chunk_split($base64) . "\r\n" .
				"\r\n" .
				"--$boundary--\r\n" .
				".";
			
			$errors = array();
			$file = File::Factory($_FILES['file'],$errors, new File());
			$file->save();

			$ticketAttachment = TicketAttachment::Factory(
			    array(
			        'ticket_id' => $this->_data['TicketResponse']['ticket_id'],
			        'file_id' => $file->id
			    ),
			    $errors,
			    new TicketAttachment()
			);
			$ticketAttachment->save();
		} else {
			// No attachment, send plain text mail
			$body = $plateout . $this->_data['TicketResponse']['body'];
		}

		$header_string = "";
		foreach ($headers as $header => $value) {
			$header_string .= $header . ': ' . $value . "\r\n";
		}
		
		// FIXME: Do this further up
		if (
			!isset($this->_data['TicketResponse']['internal'])
			|| (
				isset($this->_data['TicketResponse']['internal'])
				&&
				$this->_data['TicketResponse']['internal'] != 'on'
				)
		) {
			$recipients = $recipients = TicketingUtils::GetRecipients($ticket);
			
			foreach ($recipients as $recipient) {
				mail(
					$recipient,
					're: [' . $ticket->ticket_queue_id . '-' . $ticket->id . '] ' . $ticket->summary,
					$body,
					$header_string
				);
			}
		}
		
		sendTo('Client', 'view', array('ticketing'), array('id' => $this->_data['TicketResponse']['ticket_id']));
	}
}
