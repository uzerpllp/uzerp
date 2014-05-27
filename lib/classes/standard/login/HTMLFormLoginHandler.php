<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class HTMLFormLoginHandler implements LoginHandler {

	protected $version='$Revision: 1.2 $';
	
	private $gateway;
	
	public function __construct(AuthenticationGateway $gateway) {
		$this->gateway=$gateway;
	}
	public function doLogin() {
		$username=$_POST['username'];
		$password=$_POST['password'];
		$db=DB::Instance();
		return $this->gateway->Authenticate(array('username'=>$username,'password'=>$password,'db'=>$db));
	}
}

?>
