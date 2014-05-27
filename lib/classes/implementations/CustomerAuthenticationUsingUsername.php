<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
/*
 * Created on 29-Sep-06 by Tim Ebenezer
 *
 * CustomerAuthenticationUsingUsername.php
 */

class CustomerAuthenticationUsingUsername implements CustomerAuthentication {

	public function login(Array $data) {
			$db = DB::Instance();
			$query = 'SELECT id FROM customers WHERE username='.$db->qstr($data['username']).' AND password=md5('.$db->qstr($data['password']).') AND website_id='.WEBSITE_ID;
			$id = $db->GetOne($query);
			if ($id!==false) {
				setLoggedIn();
				$_SESSION['cms_username'] = $data['username']; 
				$_SESSION['customer_id'] = $id;
				$query='SELECT firstname || \' \' || surname FROM person p JOIN customers c ON (p.id=c.person_id) WHERE c.id='.$db->qstr($id);
				$_SESSION['cms_fullname']=$db->GetOne($query);
			}
			else {
				sendTo();
			}
			sendTo('loggedin');
		
	}

}

?>
