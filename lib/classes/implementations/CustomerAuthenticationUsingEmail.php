<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class CustomerAuthenticationUsingEmail implements CustomerAuthentication {
	
	public function login(Array $data) {
		$db=&DB::Instance();
		$query='SELECT c.id FROM customers c
					JOIN person_contact_methods pcm ON (c.person_id=pcm.person_id AND pcm.type=\'E\')
					WHERE pcm.contact='.$db->qstr($data['username']).' AND c.password=md5('.$db->qstr($data['password']).') AND c.website_id='.WEBSITE_ID;
			
		$id=$db->GetOne($query);
		if($id!==false) {
			setLoggedIn();
			$_SESSION['cms_username']=$data['username'];
			$_SESSION['customer_id']=$id;
			$query='SELECT firstname || \' \' || surname FROM person p JOIN customers c ON (p.id=c.person_id) WHERE c.id='.$db->qstr($id);
			$_SESSION['cms_fullname']=$db->GetOne($query);
		}
		else {
			sendTo();
		}
		
	}
	
}


?>