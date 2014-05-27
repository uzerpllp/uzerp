<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class RSSFeedEGlet extends SimpleListEGlet {
	protected $version='$Revision: 1.2 $';
	
	function populate() {	
		require_once LIB_ROOT.'magpie/rss_fetch.inc';
		$rss=fetch_rss($this->source);
		$this->contents=$rss->items;
		parent::populate();
	}
	
	function setSource($url) {
		$this->source=$url;
		
	}

}

?>