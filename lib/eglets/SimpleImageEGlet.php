<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SimpleImageEGlet extends SimpleEGlet {

	protected $version='$Revision: 1.2 $';
	
	protected $template='eglets/image_eglet.tpl';

	function populate() {
		$this->contents=$this->image;
	}

	function setSource($image) {
		$this->image=$image;
	}
	
}
?>