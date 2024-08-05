<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TicketsController extends printController {

	protected $version='$Revision: 1.20 $';

	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->uses(new TicketResponse());
		$this->uses(new Hour());
		$this->_templateobject = new Ticket();
		$this->uses($this->_templateobject);


		$this->view->set('controller', 'Tickets');
	}

	public function index($collection = null, $sh = '', &$c_query = null) {
		$errors=array();

		$this->setSearch('TicketsSearch', 'useDefault');

		parent::index(new TicketCollection($this->_templateobject));


		$this->view->set('no_delete',true);
		$this->view->set('clickaction', 'view');

		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('module'=>'ticketing','controller'=>'tickets','action'=>'new'),
					'tag'=>'New Ticket'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);



	}

	private function changeStatuses($ticket_ids,$to_status) {
		foreach($ticket_ids as $ticket_id=>$on) {
		}

	}

	public function view () {

		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		$ticket=$this->_uses[$this->modeltype];

		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'all' => array(
					'tag' => 'View All Tickets',
					'link' => array(
						'module'=>'ticketing',
						'controller'=>'tickets',
						'action'=>'index'
					)
				)
			)
		);
		$sidebar->addList(
			'currently_viewing',
			array(
				$ticket->summary => array(
					'tag' => $ticket->summary,
					'link' => array(
						'module'=>'ticketing',
						'controller'=>'tickets',
						'action'=>'view',
						'id'=>$ticket->id
					)
				),
				'edit' => array(
					'tag' => 'Edit',
					'link' => array(
						'module'=>'ticketing',
						'controller'=>'tickets',
						'action'=>'edit',
						'id'=>$ticket->id
					)
				),
				'add_response' => array(
					'tag' => 'Add Response',
					'link' => array(
						'module' => 'ticketing',
						'controller' => 'tickets',
						'action' => 'add_response',
						'id' => $ticket->id
					)
				),
				'extract_change_log' => array(
					'tag' => 'extract_change_log',
					'link' => array(
						'module' => 'ticketing',
						'controller' => 'tickets',
						'action' => 'extract_change_log',
						'id' => $ticket->id
					)
				)
			)
		);

		$sidebar->addList(
			'related_items',
			array(
				'attachments'=>array(
					'tag'=>'Attachments',
					'link'=>array('module'=>'ticketing','controller'=>'attachments','action'=>'viewticket','ticket_id'=>$ticket->id),
					'new'=>array('module'=>'ticketing','controller'=>'attachments','action'=>'new','ticket_id'=>$ticket->id)
				),
				'hours'=>array(
					'tag'=>'Hours',
					'link'=>array('module'=>'ticketing','controller'=>'hours','action'=>'viewticket','ticket_id'=>$ticket->id),
					'new'=>array('module'=>'ticketing','controller'=>'hours','action'=>'new','ticket_id'=>$ticket->id)
				),
				'components'=>array(
					'tag'=>'Components Affected',
					'link'=>array('module'=>'ticketing','controller'=>'ticketmoduleversions','action'=>'viewticket','ticket_id'=>$ticket->id),
				)
			)
		);

		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);

		$responses = new TicketResponseCollection(new TicketResponse);
		$sh = new SearchHandler($responses, false);
		$sh->AddConstraint(new Constraint('ticket_id', '=', $ticket->id));
		$sh->setOrderBy('created','asc');
		$responses->load($sh);
		$pageResponses = array();
		foreach ($responses->getContents() as $response) {
			if ($response->internal === 't') {
				$response->type='internal';
			}

			$pageResponses[] = $response;
		}
		$this->view->set('responses', $pageResponses);

		$ao = AccessObject::Instance();
		$this->view->set('ticketing_client',false);
		if($ao->hasPermission('ticketing_client')) {
			$this->view->set('ticketing_client',true);
		}

		$db = DB::Instance();
		$query='SELECT ceil((EXTRACT(hour FROM SUM(duration)) + (EXTRACT(minute FROM SUM (duration))/60))*4)/4 AS duration FROM hours WHERE ticket_id = ' . $db->qstr($ticket->id);
		$duration = $db->GetOne($query);
		if ($duration === null) {
			$this->view->set('duration', 0 . ' hours');
		} else {
			$this->view->set('duration',$duration . ' hours');
		}
	}

	public function _new () {

		parent::_new();

		$ticket=$this->_uses[$this->modeltype];

		if (isset($this->_data['originator_company_id'])) {
			$company=new Company();
			$company->load($this->_data['originator_company_id']);
			$this->view->set('originator_company', $company->name);
			$this->view->set('people', $company->getPeople());
			$this->view->set('email', Ticket::getCompanyEmail($this->_data['originator_company_id']));
		} else {
			$company=new Systemcompany();
			$company->load(EGS_COMPANY_ID);
			$this->view->set('originator_company', $company->company);
			$this->view->set('people', $company->systemcompany->getPeople());
		}

		$ticketreleaseversion = new TicketReleaseVersion();
		$releaseversion_cc=new ConstraintChain();
		$releaseversion_cc->add(new Constraint('status', '<>', $ticketreleaseversion->releasedStatus()));

		if (!$ticket->isLoaded()) {
			$defaults=new TicketConfiguration();

			if (isset($this->_data['originator_company_id'])) {
				$cc=new ConstraintChain();
				$cc->add(new Constraint('company_id', '=', $this->_data['originator_company_id']));
				$defaults->loadBy($cc);
			}
			if (!$defaults->isLoaded()) {
				$cc=new ConstraintChain();
				$cc->add(new Constraint('company_id', '=', EGS_COMPANY_ID));
				$defaults->loadBy($cc);
			}

			if ($defaults->isLoaded()) {
				$this->view->set('client_ticket_status_default', $defaults->client_ticket_status_id);
				$this->view->set('internal_ticket_status_default', $defaults->internal_ticket_status_id);
				$this->view->set('ticket_queue_default', $defaults->ticket_queue_id);
				$this->view->set('ticket_category_default', $defaults->ticket_category_id);
				$this->view->set('client_ticket_severity_default', $defaults->client_ticket_severity_id);
				$this->view->set('internal_ticket_severity_default', $defaults->internal_ticket_severity_id);
				$this->view->set('client_ticket_priority_default', $defaults->client_ticket_priority_id);
				$this->view->set('internal_ticket_priority_default', $defaults->internal_ticket_priority_id);
			}
		}

		if (!empty($this->_data['ticket_release_version_id']))
		{
			$ticket->ticket_release_version_id = $this->_data['ticket_release_version_id'];
		}

		if (!is_null($ticket->ticket_release_version_id))
		{
			$releaseversion_cc->add(new Constraint('id', '=', $ticket->ticket_release_version_id), 'OR');
		}

		$ticket->belongsTo['release_version']['cc']=$releaseversion_cc;

	}

	public function add_response () {
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

	public function extract_change_log () {

		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		$ticket=$this->_uses[$this->modeltype];

		$flash=Flash::Instance();

		$fname=FILE_ROOT.'install/change.log';

		$handle = fopen($fname, 'r');

		$line = fgets($handle);

		while (!feof($handle))
		{
			$data[]	= $line;
			$line	= fgets($handle);
		}

		fclose($handle);

		$handle = fopen($fname, 'w');
// TODO: Move this to Ticketing Utils
// and enable for multiple ticket extract
		if ($handle && flock($handle, LOCK_EX)) {
			$write=true;
			$notwritten=true;
			$changelog="Ticket Ref: ".$ticket->id."\n";
			$changelog.="==================\n";
			$changelog.=$ticket->change_log."\n";
			$changelog.="-------------------------------------------------------------------\n\n";
			foreach ($data as $line) {
				if (strpos($line, "Ticket Ref: ") !== FALSE) {
					$current_ticket=trim(substr($line, strpos($line, "Ticket Ref: ")+12));
					if ($current_ticket >= $ticket->id && $notwritten) {
						fputs($handle, $changelog);
						$notwritten=false;
					}
					if ($current_ticket == $ticket->id)
					{
						$write = FALSE;
					}
					else
					{
						$write = TRUE;
					}
				}
				if ($write)
				{
					fputs($handle, $line);
				}
			}

			if ($notwritten) {
				fputs($handle, $changelog);
			}
			if (flock($handle, LOCK_UN) && fclose($handle)) {
				$flash->addMessage('Change Log extracted');
			} else {
				$flash->addError('Error closing Change Log file');
			}
		} else {
			$flash->addError('Cannot open Change Log file');
		}

		sendTo($this->name, 'view', $this->_modules, array('id'=>$ticket->id));

	}

	public function save($modelName = null, $dataIn = [], &$errors = []) : void {
		// Set some defaults
		$errors = array();
		$flash=Flash::Instance();

		if (isset($this->_data['Ticket']['id']) && $this->_data['Ticket']['id']!='') {
			$originalTicket = new Ticket();
			$originalTicket->load($this->_data['Ticket']['id']);

// Check for Client Status/Priority/Severity/Queue change
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

				if (!empty($this->_data['TicketResponse']['body']) && !isset($this->_data['TicketResponse']['internal'])) {
					$changeText[] = $this->_data['TicketResponse']['body'];
				}

				// Send mail
				$headers = array(
					'From' => TicketingUtils::getReplyAddress($originalTicket)
				);

				$header_string = "";
				foreach ($headers as $header => $value) {
					$header_string .= $header . ': ' . $value . "\r\n";
				}

				$body = TicketingUtils::StatusPlate($originalTicket) . implode("\n", $changeText);

				$recipients = TicketingUtils::GetRecipients($originalTicket);

				foreach ($recipients as $recipient) {
					mail(
						(string) $recipient,
						're: [' . $originalTicket->ticket_queue_id . '-' . $originalTicket->id . '] ' . $originalTicket->summary,
						$body,
						$header_string
					);
				}
			}

// Check for Internal Status/Priority/Severity/Assigned to/Originator Person/Company change
			$changes = array(
				array(
					'param' => 'internal_ticket_status_id',
					'friendly' => 'Internal Status',
					'object' => 'TicketStatus'
				),
				array(
					'param' => 'internal_ticket_priority_id',
					'friendly' => 'Internal Priority',
					'object' => 'TicketPriority'
				),
				array(
					'param' => 'internal_ticket_severity_id',
					'friendly' => 'Internal Severity',
					'object' => 'TicketSeverity'
				),
				array(
					'param' => 'assigned_to',
					'friendly' => 'Assigned To',
					'object' => 'User',
					'fields' => array('username')
				),
				array(
					'param' => 'originator_person_id',
					'friendly' => 'Person',
					'object' => 'Person'
				),
				array(
					'param' => 'originator_company_id',
					'friendly' => 'Company',
					'object' => 'Company'	
				)
			);

			$changeText = array();

			foreach ($changes as $change) {
				if($this->_data['Ticket'][$change['param']] != $originalTicket->$change['param']) {
					$was = new $change['object'];
					$now = new $change['object'];
					$was->load($originalTicket->$change['param']);
					$now->load($this->_data['Ticket'][$change['param']]);

					if (!isset($change['fields'])) {
						$was_value = $was->name;
					} else {
						$t = '';
						foreach ($change['fields'] as $field) {
							$t .= $was->$field . ' ';
						}
						$t = rtrim($t);

						$was_value = $t;
					}

					if (!isset($change['fields'])) {
						$now_value = $now->name;
					} else {
						$t = '';
						foreach ($change['fields'] as $field) {
							$t .= $now->$field . ' ';
						}
						$t = rtrim($t);

						$now_value = $t;
					}

					$changeText[] = $change['friendly'] . ': was ' . $was_value . ' now ' . $now_value . '.';
				}
			}

			if(count($changeText) > 0) {
				$ticketResponse = TicketResponse::Factory(
					array(
						'ticket_id' => $this->_data['Ticket']['id'],
						'internal' => 'true',
						'body' => implode("\n", $changeText),
						'type' => 'status',
						'owner' => EGS_USERNAME
					),
					$errors,
					'TicketResponse'
				);
				$ticketResponse->save();
			}

			// Assignation changed?
			if (($this->_data['Ticket']['assigned_to'] != $originalTicket->assigned_to) and ($this->_data['Ticket']['assigned_to'] != '')) {
				// Find email address for user
				$user = new User();
				$user->loadBy('username', $this->_data['Ticket']['assigned_to']);
				if (!is_null($user->person_id)) {
					$person = new Person();
					$person->load($user->person_id);
					$to = $person->email->contactmethod;
				} else {
					$to = '';
				}
				if (empty($to)) {
					$to = $user->email;
				}
				if ($to <> '') {
					$body = "The following ticket has been assigned to you:\n";
					$body.=  '[' . $this->_data['Ticket']['ticket_queue_id'] . '-' . $this->_data['Ticket']['id'] . '] ' . $this->_data['Ticket']['summary'] . "\n";

					$header_string = 'From: ' . TicketingUtils::getReplyAddress($originalTicket);

					mail(
						$to,
						'You\'ve been assigned: [' . $this->_data['Ticket']['ticket_queue_id'] . '-' . $this->_data['Ticket']['id'] . '] ' . $this->_data['Ticket']['summary'],
						$body,
						$header_string
					);
				}
			}
		}

		if (isset($this->_data['TicketResponse']) && empty($this->_data['TicketResponse']['body'])) {
			unset($this->_data['TicketResponse']);
		}
		if (parent::save('Ticket', '', $errors)) {
			sendTo('Tickets', 'view', array('ticketing'), array('id'=>$this->saved_model->id));
		}
		$errors[]='Error saving ticket';
		$flash->addErrors($errors);
		$this->refresh();

	}

	public function save_response()
	{

		// sort the data out
		if (isset($this->_data['response']))
		{
			$this->_data['TicketResponse']['body']		= $this->_data['response'];
			$this->_data['TicketResponse']['ticket_id']	= $this->_data['ticket_id'];
			$this->_data['TicketResponse']['type']		= 'site';
		}

		$save = parent::save('TicketResponse', $this->_data['TicketResponse']);

		$ticket = new Ticket();
		$ticket->load($this->_data['TicketResponse']['ticket_id']);

		$plateout = TicketingUtils::StatusPlate($ticket);

		$headers = array(
			'From' => TicketingUtils::getReplyAddress($ticket)
		);

		$hours_data = &$this->_data['Hour'];

		if (isset($hours_data['duration']) && $hours_data['duration'] != 0)
		{

			if ($hours_data['duration_unit'] == 'days')
			{
				$hours_data['duration'] = $hours_data['duration'] * SystemCompanySettings::DAY_LENGTH;
			}

			// Calculate start time by working backward NB: Needs changing with date_format?
			$hours_data['start_time'] = date(
				"d/m/Y H:i",
				time() - ($hours_data['duration'] * 60 * 60)
			);

			$hours_data['duration'] .= ' hours';

			parent::save('Hour');

		}

		// FIXME: If someone forces a file upload... I guess that causes this code to randomly send the file?
		if ($_FILES['file']['size'] > 0)
		{

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

			$errors	= array();
			$file	= File::Factory($_FILES['file'], $errors, new File());

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

		}
		else
		{
			// No attachment, send plain text mail
			$body = $plateout . $this->_data['TicketResponse']['body'];
		}

		$header_string = "";

		foreach ($headers as $header => $value)
		{
			$header_string .= $header . ': ' . $value . "\r\n";
		}

		// FIXME: Do this further up
		if (!isset($this->_data['TicketResponse']['internal']) 
		|| (isset($this->_data['TicketResponse']['internal']) && $this->_data['TicketResponse']['internal'] != 'on'))
		{

			$recipients = TicketingUtils::GetRecipients($ticket);

			foreach ($recipients as $recipient) {
				mail(
					(string) $recipient,
					're: [' . $ticket->ticket_queue_id . '-' . $ticket->id . '] ' . $ticket->summary,
					$body,
					$header_string
				);
			}
		}

		if (is_ajax())
		{
			echo json_encode(array('success' => $save));
			exit;
		}
		else
		{
			sendTo('Tickets', 'view', array('ticketing'), array('id' => $this->_data['TicketResponse']['ticket_id']));
		}

	}

	public function delete($modelName = null) {
	}

	public function getEmail($_person_id='', $_company_id='') {
		/*
		 * We only want to override the function parameters if the call has come from
		 * an ajax request, simply overwriting them as we were leads to a mix up in
		 * values
		 */
		if(isset($this->_data['person_id'])) {
			if(!empty($this->_data['person_id'])) { $_person_id=$this->_data['person_id']; }
			if(!empty($this->_data['company_id'])) { $_company_id=$this->_data['company_id']; }
		}

		// Used by Ajax to return the person's email address
		// If no person is supplied, or they have no email address
		// look for the company technical email address
		// if still no email address is found, use the logged in user details
		$person=new Person();
		$email='';
		if (!empty($_person_id)) {
			$person->load($_person_id);
			if ($person->isLoaded() && !is_null($person->email->contactmethod)) {
				$email=$person->email->contactmethod;
			}
		}

		if (empty($email) && !empty($_company_id)) {
			$email=Ticket::getCompanyEmail($_company_id);
		}
		if (empty($email)) {
			$user=getCurrentUser();
			if ($user) {
				$email=$user->email;
				if (!is_null($user->person_id)) {
					$person->load($user->person_id);
					if ($person->isLoaded() && !is_null($person->email->contactmethod)) {
						$email=$person->email->contactmethod;
					}
				}
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

// end of TicketsController.php