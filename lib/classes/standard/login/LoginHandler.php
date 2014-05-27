<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.2 $ */
	
interface LoginHandler {
	public function __construct(AuthenticationGateway $gateway);
	public function doLogin();

}
?>
