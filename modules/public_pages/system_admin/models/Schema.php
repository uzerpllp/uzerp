<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class Schema extends DataObject {

	private $databases=array();
	private $defaults=array();
	
	public function __construct() {
		$this->databases = array('pgsql'=>'Postgres'
								,'mysql'=>'MySQL'
								,'oracle'=>'Oracle');
		$this->defaults = array('host'=>'localhost'
								,'database'=>'EGS'
								,'username'=>'www-data');
	}
	
	public function supportedDatabases () {
		return $this->databases;
	}

	public function defaultdbname () {
		return $this->defaults['database'];
	}

	public function defaulthost () {
		return $this->defaults['host'];
	}

	public function defaultuser () {
		return $this->defaults['username'];
	}

}
?>
