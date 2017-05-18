<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

// Clears up repeated code between Tickets/Client Controller
class TicketingUtils {

	protected $version='$Revision: 1.10 $';
	
	private function __construct() {
		// Not constructable.
	}
	
	public static function StatusPlate($ticket) {
		// Make status plate
		$entries = array(
			array(
				'param' => 'ticket_queue_id',
				'friendly' => 'Ticket Queue',
				'object' => 'TicketQueue'
			),
			array(
				'param' => 'client_ticket_status_id',
				'friendly' => 'Ticket Status',
				'object' => 'TicketStatus'
			),
			array(
				'param' => 'originator_company_id',
				'friendly' => 'Company',
				'object' => 'Company'
			),
			array(
				'param' => 'client_ticket_severity_id',
				'friendly' => 'Ticket Severity',
				'object' => 'TicketSeverity'
			),
			array(
				'param' => 'originator_person_id',
				'friendly' => 'Person',
				'object' => 'Person'
			),
			array(
				'param' => 'client_ticket_priority_id',
				'friendly' => 'Ticket Priority',
				'object' => 'TicketPriority'
			)
		);

		$plate = array();
		foreach ($entries as $entry) {
			$object = new $entry['object'];
			$object->load($ticket->$entry['param']);
			if (!isset($entry['fields'])) {
				$plate[] = $entry['friendly'] . ': ' . $object->name;
			} else {
				$t = '';
				foreach ($entry['fields'] as $field) {
					$t .= $object->$field . ' ';
				}
				$t = rtrim($t);

				$plate[] = $entry['friendly'] . ': ' . $t;
			}
		}
		
		// Find maximum field length
		$maxcount = 0;
		foreach ($plate as $field) {
			if (strlen($field) > $maxcount) {
				$maxcount = strlen($field);
			}
		}
		
		// Generate bar
		$bar = '';
		for ($i = 0; $i <= ($maxcount * 2) + 3; $i++) {
			$bar .= '-';
		}
		
		// Pad each plate line up to max field length
		foreach ($plate as $key=>$value) {
			$fieldlength = strlen($plate[$key]);
			
			for ($i = 0; $i <= ($maxcount - $fieldlength); $i++) {
				$plate[$key] .= ' ';
			}
		}
		
		$plateout = $ticket->ticket_queue_id . '-' . $ticket->id . ' ' . $ticket->summary . "\n";
		$plateout .= $bar . "\n";
		
		for ($i = 0; $i < count($plate); $i += 2) {
			$plateout .= $plate[$i] . " | " . $plate[$i + 1]  . "\n";
		}
		
		$plateout .= $bar . "\n\n";
		
		return $plateout;
	}
	
	public static function GetRecipients($ticket) {
		$recipients = array();
		
		if ($ticket->originator_person_id != null) {
			// If this ticket has a person, attempt to send mail to them
			$person = new Person();
			$person->load($ticket->originator_person_id);
			$contact = $person->email->contactmethod;
			
			if (!empty($contact)) {
				$recipients[] = $contact;
			} else {
				// If no contact found then reiterate but for company contacts this time
					
				if ($ticket->originator_company_id != null) {
					$contact=$ticket->getCompanyEmail($ticket->originator_company_id);
					if (!empty($contact)) {
						$recipients[] = $contact;
					}
				}
			}
		}
		
		// Last ditch effort.
		if (count($recipients) == 0) {
			if(!is_null($ticket->originator_email_address)) {
				$recipients[] = $ticket->originator_email_address;
			}
		}
		
		return $recipients;
	}
	
	public static function getReplyAddress ($ticket) {
		$from='';

		$config	= Config::Instance();
		
		$ticket_support = $config->get('TICKET_SUPPORT');
		
		if (!is_null($ticket->originator_company_id) && !empty($ticket_support)) {
			// use the ticket support email address for the company
			// TICKET_SUPPORT should be defined in the conf/config.php
			$from=$ticket->company->getContactDetail('E', $ticket_support);
		}
		if (empty($from)) {
			// no ticket support email address for the company
			// so use the queue email address
			$queue = new TicketQueue();
			$queue->load($ticket->ticket_queue_id);
			$from=$queue->email_address;
		}
		return $from;
	}
	
}

?>