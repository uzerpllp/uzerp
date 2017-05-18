<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
	
	$version='$Revision: 1.11 $';

class xmlrpcTicket {
	
	function request ($params)
	{

		$errors=array();
		
// Parse parameters.
	    $p1 = $params->getParam(0);
    	
	    $subject = $p1->scalarval();
		
    	$p2 = $params->getParam(1);
		
		$from_email = $p2->scalarval();
		
		$p3 = $params->getParam(2);
		
		$parts=array();
		
		foreach ($p3->scalarval() as $body)
		{
			$parts[] = $body->scalarval();
		}
		
		$content=$parts[0]['content'];
		
		$request=$content->scalarval();
		
		unset($parts[0]);
		
		$p4 = $params->getParam(3);
		
		$to_email = $p4->scalarval();
/*
- mandatory fields

  summary character varying NOT NULL,
  client_ticket_priority_id bigint NOT NULL,
  client_ticket_severity_id bigint NOT NULL,
  ticket_queue_id bigint NOT NULL,
  ticket_category_id bigint NOT NULL,
  internal_ticket_priority_id bigint NOT NULL,
  internal_ticket_severity_id bigint NOT NULL,
  internal_ticket_status_id bigint NOT NULL,
  client_ticket_status_id bigint NOT NULL,

- optional fields

  originator_person_id character varying,
  originator_company_id bigint,
  company_sla_id bigint,
  action_code character varying(4),
  originator_email_address character varying,
  assigned_to character varying,

 */	
		$config	= Config::Instance();
		
		$companyid='';
		
		$contact=new PartyContactMethodCollection(new PartyContactMethod());
		$sh=new SearchHandler($contact, false, false);
		$sh->addConstraint(new Constraint('name', '=', $config->get('TICKET_SUPPORT')));
		$sh->addConstraint(new Constraint('type', '=', 'E'));
		$sh->addConstraint(new Constraint('technical', 'is', TRUE));
		$sh->addConstraint(new Constraint('contact', '=', $to_email));
		$contact->load($sh);
		
		if ($contact->count()>0)
		{
			$party_ids=array();
			
			foreach ($contact as $party)
			{
				$party_ids[]=$party->party_id;
				$usercompanyid=$party->usercompanyid;
			}
			
			if (!defined('EGS_COMPANY_ID'))
			{
				define('EGS_COMPANY_ID', $usercompanyid);
			}
			
			$company=new Company();
			
			$cc=new ConstraintChain();
			$cc->add(new Constraint('party_id', 'in', '('.implode(',', $party_ids).')'));
			
			$companylist=$company->getAll($cc);
			
			if (count($companylist)!=1)
			{
				$errors[]='1) Unable to find your support details - please contact technical support';
			}
			else
			{
				$company->load(key($companylist), true);
				$companyid=$company->id;
				$usercompanyid=$company->usercompanyid;
			}
			
		}
		else
		{
			$errors[]='2) Unable to find your support details - please contact technical support';
		}

		$data['attachments']=$parts;
		$data['TicketResponse']['body']=$request;
		$data['TicketResponse']['type']='site';
		$data['Ticket']['originator_company_id']=$companyid;
		$data['reply_address']=$to_email;
		
		$start=strpos($subject, '[');
		$end=strpos($subject, ']');
		$mid=strpos($subject, '-');
		if ($start > 0 && $mid > 0 && $end > 0
			&& $start < $end && $start < $mid && $mid < $end)
		{
			$ticket=substr($subject, $mid+1, $end-$mid-1);
		}
		
		if (empty($request))
		{
			$errors[]='No request details submitted';
		}
		elseif (count($errors)==0)
		{
			
			if (isset($ticket) && is_numeric($ticket))
			{
				$data['TicketResponse']['ticket_id']=$ticket;
				self::updateRequest($data, $errors);
				$response="Your response for Ticket ".$ticket." has been received. The person dealing with your query has been notified.";
			}
			else
			{
				$data['Ticket']['summary']=$subject;
				$data['Ticket']['originator_email_address']=$from_email;
				$data['Ticket']['usercompanyid']=$usercompanyid;
				self::newRequest($data, $errors);
				$response="Your request has been received and assigned ticket no. ".$data['TicketResponse']['ticket_id'].". A confirmation email has been sent.";
			}
			
		}
		
	    if (count($errors)>0)
	    {
		
	    	$errors[]=$sh->constraints->__toString();
	    	$result="ERROR\n";
	    	$response="Subject:".$subject."\n";
			$response.='Email:'.$from_email."\n";
			$response.='Request:'.$request.";\n\n";
			$response.="Errors:\n";
	    	$response.=implode(";\n",$errors);
	    
	    }
	    else
	    {
	    	$result='SUCCESS';
	    }
	    
		$struct = array('result' => new xmlrpcval($result, 'string')
					   ,'response' => new xmlrpcval($response, 'string'));
	    return new xmlrpcresp(new xmlrpcval($struct, 'struct'));
	}
	
	private function newRequest (&$data=array(), &$errors=array())
	{

		$config = new TicketConfiguration();
		
		$companyid = $data['Ticket']['originator_company_id'];
		
		if (!empty($companyid))
		{
			$cc = new ConstraintChain();
			$cc->add(new Constraint('company_id', '=', $companyid));
			$config->loadBy($cc);
		}
		
		if (!$config->isLoaded())
		{
			
			$sc = new Systemcompany();
			$sc->load($data['Ticket']['usercompanyid']);
			
			if ($sc->isLoaded())
			{
				$cc = new ConstraintChain();
				$cc->add(new Constraint('company_id', '=', $sc->company_id));
				$config->loadBy($cc);
			}
		}
			
		if ($config->isLoaded())
		 {
			
			$data['Ticket']['ticket_category_id']=$config->ticket_category_id;
			$data['Ticket']['ticket_queue_id']=$config->ticket_queue_id;
			$data['Ticket']['client_ticket_priority_id']=$config->client_ticket_priority_id;
			$data['Ticket']['internal_ticket_priority_id']=$config->internal_ticket_priority_id;
			$data['Ticket']['client_ticket_severity_id']=$config->client_ticket_severity_id;
			$data['Ticket']['internal_ticket_severity_id']=$config->internal_ticket_severity_id;
			$data['Ticket']['client_ticket_status_id']=$config->client_ticket_status_id;
			$data['Ticket']['internal_ticket_status_id']=$config->internal_ticket_status_id;
				
		}
		else
		{
		
//	'TicketCategory' - get default
			$data['Ticket']['ticket_category_id']=self::getDefault(new TicketCategory());
	
//	'TicketQueue' - get default
			$data['Ticket']['ticket_queue_id']=self::getDefault(new TicketQueue());
	
//	'TicketPriority' - get default
			$priority=self::getDefault(new TicketPriority());
			$data['Ticket']['client_ticket_priority_id']=$priority;
			$data['Ticket']['internal_ticket_priority_id']=$priority;
	
//	'TicketSeverity' - get default
			$severity=self::getDefault(new TicketSeverity());
			$data['Ticket']['client_ticket_severity_id']=$severity;
			$data['Ticket']['internal_ticket_severity_id']=$severity;
	
//	'TicketStatus' - get default
			$status=self::getDefault(new TicketStatus());
			$data['Ticket']['client_ticket_status_id']=$status;
			$data['Ticket']['internal_ticket_status_id']=$status;
		}

//	'User' - get default assigned to/raised by
		$data['Ticket']['assigned_to']='';
		$data['Ticket']['raised_by']='';
		
//	'Person' - get name of person making request
		$data['Ticket']['originator_person_id']='';
	
		$db=DB::Instance();
		$db->StartTrans();

		$ticket=DataObject::Factory($data['Ticket'], $errors, new Ticket());
		
		if (count($errors)==0 && !$ticket->save())
		{
			$errors[]=$db->ErrorMsg();
			$errors[]='Error saving Ticket';
			$db->FailTrans();
		}
	
		if (count($errors)==0)
		{
			
			$data['TicketResponse']['ticket_id']=$ticket->id;
			
			if (!self::saveResponse($data, $errors))
			{
				$errors[]=$db->ErrorMsg();
				$db->FailTrans();
			}
		}
		
		if (count($errors)==0 && count($data['attachments'])>0)
		{
			if (!self::saveAttachments($data, $db, $errors))
			{
				$errors[]=$db->ErrorMsg();
				$db->FailTrans();
			}
		}

		$db->CompleteTrans();
		
// Build the response.
	    if (count($errors)===0)
	    {
			self::emailConfirmation ($ticket, $data);
	    	$data['message']='This ticket has been assigned to you.';
			self::notifyQueueOwner ($ticket, $data);
	    }
	    return;

	}


	private function updateRequest (&$data=array(), &$errors=array())
	{
		
		if (empty($data))
		{
			$errors[]='No data found';
			return;
		}
		
		$db=DB::Instance();
		$db->StartTrans();

		$ticket=new Ticket();
		$ticket->load($data['TicketResponse']['ticket_id']);
		
		if (!$ticket->isLoaded())
		{
			$errors[]='Cannot find ticket '.$data['TicketResponse']['ticket_id'];
			return;
		}
		
		if ($ticket->originator_company_id != $data['Ticket']['originator_company_id'])
		{
			$errors[]='Cannot find ticket '.$data['TicketResponse']['ticket_id'].' in your list of tickets';
			return;
		}
		
		if (count($errors)==0)
		{
			if (!self::saveResponse($data, $errors))
			{
				$errors[]=$db->ErrorMsg();
				$db->FailTrans();
			}
		}
		
		if (count($errors)==0 && count($data['attachments'])>0)
		{
			if (!self::saveAttachments($data, $db, $errors))
			{
				$errors[]=$db->ErrorMsg();
				$db->FailTrans();
			}
		}
		
		$db->CompleteTrans();

// Build the response.
	    if (count($errors)===0)
	    {
	    	$data['message']='Response received for this ticket.';
			self::notifyQueueOwner ($ticket, $data);
	    }
		
	}

	private function saveResponse($data, &$errors)
	{
		
		$ticketResponse=DataObject::Factory($data['TicketResponse'], $errors, 'TicketResponse');
		
		if (count($errors)==0 && $ticketResponse && $ticketResponse->save())
		{
			return true;
		}
		
		$errors[]='Error saving Ticket Response';
		
		return false;
		
	}
	
	private function saveAttachments($data, $db, &$errors)
	{

		$attachment_errors=array();
		
		foreach ($data['attachments'] as $attachment)
		{
			$encoding=$attachment['encoding']->scalarval();
			$type=$attachment['type']->scalarval();
			$subtype=$attachment['subtype']->scalarval();
			$content=$attachment['content']->scalarval();
			
			if ($encoding==3)
			{
				$content=base64_decode($content);
			}
			
			if (isset($attachment['filename']))
			{
				$data['name']=$attachment['filename']->scalarval();
			}
			else
			{
				$data['name']='attachment';
			}
			$fname=FILE_ROOT.'data/tmp/'.$data['name'];
			$types=array(0=>'text'
						,1=>'multipart'
						,2=>'message'
						,3=>'application'
						,4=>'audio'
						,5=>'image'
						,6=>'video'
						,7=>'other');
			$data['type']=$types[$type].'/'.strtolower($subtype);
			$fd=fopen($fname, 'w');
			$data['size']=fwrite($fd, $content);
			fclose($fd);
			
			if(!chmod($fname,0655))
			{
				$errors[]='Error changing permission of uploaded file, contact the server admin';
			}
			
			if ($data['size'])
			{
				$file = DataObject::Factory($data, $attachment_errors, new File());
				$file->tmp_name=$fname;
			}
			else
			{
				$file = false;
			}
			
			if (count($attachment_errors)>0 || !$file || !$file->save())
			{
				$attachment_errors[]='Failed to create attachment';
			}
		
			if (count($attachment_errors)==0)
			{
				
				$ticketAttachment = TicketAttachment::Factory(
					array(
						'ticket_id' => $data['TicketResponse']['ticket_id'],
						'file_id' => $file->id
					),
					$attachment_errors,
					new TicketAttachment()
				);
				
				if (count($attachment_errors)>0 || !$ticketAttachment || !$ticketAttachment->save())
				{
					$attachment_errors[]='Error saving Ticket Attachment';
				}
			}
		}
		
		if (count($attachment_errors)>0)
		{
			$errors+=$attachment_errors;
			return false;
		} else {
			return true;
		}		
		
	}
	
	private function emailConfirmation ($ticket, $data)
	{
		
		$plateout = TicketingUtils::StatusPlate($ticket);
		
		$headers = array(
			'From' => TicketingUtils::getReplyAddress($ticket),
			'Reply-To'=>$data['reply_address']
		);
			
		$header_string = "";
		
		foreach ($headers as $header => $value)
		{
			$header_string .= $header . ': ' . $value . "\r\n";
		}
// Do we need to send the body?
		
		$body = $plateout;
		$body .= "Your request has been received and allocated reference ".$ticket->id.". ";
		$body .= "Please quote this reference when contacting us.\n\n";
		
//		$body = $plateout . $data['TicketResponse']['body'];
		
		$recipients = TicketingUtils::GetRecipients($ticket);
			
		foreach ($recipients as $recipient)
		{
			mail(
				$recipient,
				're: [' . $ticket->ticket_queue_id . '-' . $ticket->id . '] ' . $ticket->summary,
				$body,
				$header_string,
				'-r '.$data['reply_address']
			);
		}
		// TODO: Email someone internally to notify of received ticket;
		//       Perhaps use the queue owner?
		
	}
	
	private function notifyQueueOwner ($ticket, $data)
	{
// Needs to be Assigned To if present
// otherwise Queue Owner		
		$plateout = TicketingUtils::StatusPlate($ticket);

		$to = '';
		if (!is_null($ticket->assigned_to))
		{
			
			$user = new User();
			$user->loadBy('username', $ticket->assigned_to);
			
			if (!is_null($user->person_id))
			{
				$person = new Person();
				$person->load($user->person_id);
				$to = $person->email->contactmethod;
			}
			
			if (empty($to))
			{
				$to = $user->email;
			}
		}
		
		if (empty($to))
		{
			$queue = new TicketQueue();
			$queue->load($ticket->ticket_queue_id);
			if ($queue->isLoaded() && !is_null($queue->email_address)) {
				$to=$queue->email_address;
			}
		}
		
		if (!empty($to))
		{
				$headers = array(
				'From' => TicketingUtils::getReplyAddress($ticket),
				'Reply-To'=>$data['reply_address']
			);
			
			$header_string = "";
			
			foreach ($headers as $header => $value)
			{
				$header_string .= $header . ': ' . $value . "\r\n";
			}
		
			$body = $plateout."\n".$data['message']."\n";
		
			mail(
				$to,
				're: [' . $ticket->ticket_queue_id . '-' . $ticket->id . '] ' . $ticket->summary,
				$body,
				$header_string,
				'-r '.$to
			);
		}
		
	}
	
	private function getDefault ($object)
	{
		$list=$object->getAll();
		return key($list);
	}
}

// End of xmlrpcTicket
