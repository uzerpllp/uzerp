<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class RedirectHandler implements Redirection{

	protected $version='$Revision: 1.10 $';
	
	public function Redirect() {
		$args=func_get_args();
		$arg_array=array('controller','action','module','other');
		$module='';
		$controller='';
		$action='';
		if(is_array($args[0]))
			$args=$args[0];
		foreach($args as $i=>$arg) {
			${$arg_array[$i]}=$arg;
		}
		Flash::Instance()->save();
		$url='';
		$amp='';
		$ao=AccessObject::Instance();
		$pid=$ao->getPermission($module, $controller, $action);
		if (!empty($pid)) {
			$url='pid='.$pid;
			$amp='&';
		}
		if(isset($module) && !empty($module)) {
			if (!is_array($module)) {
				$module=array($module);
			}
			$prefix='module=';
			foreach($module as $m)
			{
				$url.=$amp.$prefix.$m;
				$prefix='sub'.$prefix;
				$amp='&';
			}
		}
		if (!empty($controller)) {
			$url.=$amp.'controller='.$controller;
			$amp='&';
		}
		if (!empty($action)) {
			$url.=$amp.'action='.$action;
			$amp='&';
		}
		if (!(empty($other))) {
			foreach($other as $key=>$value) {
				$url .= $amp.$key.'='.$value;
				$amp='&';
			}
		}
		
		$location = $url;
		if (!empty($location) && $location[0] == '&')
			$location = substr($location,1);
		debug('RedirectHandler::Redirect '.$location);
//		echo 'RedirectHandler::Redirect '.$location.'<br>';
		$system=system::Instance();
		if (is_object($system->controller)) {
			if (is_array($system->controller->_data) && isset($system->controller->_data['password']))
			{
				$system->controller->_data['password']='********************';
			}
			audit(print_r($system->controller->_data,true).print_r($system->flash,true));
		}
		audit('RedirectHandler::Redirect '.$location);
		
		header('Location: '.SERVER_ROOT.((!empty($location))?'/?'.$location:''));
		exit;

	}

}

?>
