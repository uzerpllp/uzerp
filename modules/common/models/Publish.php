<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class Publish {

	protected $version='$Revision: 1.2 $';
	
	public function systemCompany (&$do, &$errors) {

		$user=getCurrentUser();
		$person=new Person();
		$person->load($user->person_id);
		$format=new xmlrpcmsg('elgg.user.newCommunity',array(new xmlrpcval($person->firstname.' '.$person->surname, "string")
													   ,new xmlrpcval($person->email, "string")
													   ,new xmlrpcval($do->company, "string")
													   ));
		$client=new xmlrpc_client("_rpc/RPC2.php", "tech2.severndelta.co.uk", 8091);
		$request=$client->send($format);
		if (!$request->faultCode()) {
			$response=$request->value();
			if ($response->structmemexists('owner')
			&& $response->structmemexists('community')) {
				$person->published_username=$response->structmem('owner')->scalarval();
				$person->save();
				$do->published=true;
				$do->published_username=$response->structmem('community')->scalarval();
				$do->published_owner_id=$person->id;
				$do->save();
			} else {
				$errors[]='Failed to publish company';
			}
		} else {
			$errors[]="Code: ".$request->faultCode()." Reason '".$request->faultString();
			return false;	
		}
		
		return true;	
	}
	
	public function addUser (&$do, &$errors) {
		$systemcompany=new Systemcompany();
		$systemcompany->load($do->usercompanyid);
		if (!is_null($systemcompany->published_username)) {
			$format=new xmlrpcmsg('elgg.user.newUser',array(new xmlrpcval($do->firstname.' '.$do->surname, "string")
														   ,new xmlrpcval($do->email, "string")
														   ,new xmlrpcval($systemcompany->published_username, "string")
														   ,new xmlrpcval("person", "string")
														   ));
			$client=new xmlrpc_client("_rpc/RPC2.php", "tech2.severndelta.co.uk", 8091);
			$request=$client->send($format);
			if (!$request->faultCode()) {
				$response=$request->value();
				$do->published_username=$response->scalarval();
				$do->save();
			} else {
				$errors[]="Code: ".$request->faultCode()." Reason '".$request->faultString();
				return false;	
			}
		} else {
			$errors[]='The Company has not been published';
			return false;
		}
		return true;	
	}
	
}
?>
