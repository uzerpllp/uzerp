<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
	
class DatabaseAuthenticator implements AuthenticationGateway {

	protected $version='$Revision: 1.5 $';
	
	public function __construct() {
		

	}

	public function Authenticate(Array $params) {
		if(!isset($params['username'])||!isset($params['password'])||empty($params['db']))
			throw new Exception('DatabaseAuthenticator expects a connection, a username and a password');
		$db=$params['db'];
		$query='SELECT u.username FROM users u LEFT JOIN user_company_access uca ON (u.username=uca.username) LEFT JOIN  system_companies sc ON (uca.usercompanyid=sc.id) WHERE sc.enabled AND uca.enabled AND u.username='.$db->qstr($params['username']).' AND password=md5('.$db->qstr($params['password']).')';
		$test=$db->GetOne($query);
		if($test!==false && !is_null($test)) {
			return true;
		}
		return false;
	}

}
?>
