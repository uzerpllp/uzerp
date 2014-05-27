<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
/**
 * To be used for 'Recently Viewed' (potentially for each model as well as globally)
 * and favourites and such nonsense.
 */

class PageList {

	protected $version='$Revision: 1.2 $';
	
	protected $queue;
	protected $name;
	protected $pointer=0;
	function __construct($name,$length=10) {
		$this->name=$name;
		if(isset($_SESSION['pagelists'][$name])) {
			$this->queue = $_SESSION['pagelists'][$name];
		}
		else {
			$this->queue = new Queue($length);		
		}
	}	
	
	
	function addPage(Page $page) {
		$this->queue->push($page);
	}
	
	function removePage(Page $page) {
		$this->queue->remove($page);
	}
	
	function getPages() {
		return $this->queue;
	}
	
	function addFromCollection(DataObjectCollection $doc,Array $url,$url_fields,$type,$tag_field) {
		foreach($doc as $do) {
			foreach($url_fields as $field) {
				$url[$field]=$do->$field;
			}
			$this->addPage(
				new Page(
					$url,
					$type,
					$do->$tag_field
				)
			);
			
		}
	}
	
	function save() {
		$_SESSION['pagelists'][$this->name]=$this->queue;
	}
	
	function clear() {
		$this->queue->clear();
		$this->save();
	}
	
	
}

class Page {
	public $url;
	public $tag;
	public $type;
	
	function __construct($url,$type,$tag) {
		$this->url=$url;
		$this->type=$type;
		$this->tag=$tag;
	}
}
?>