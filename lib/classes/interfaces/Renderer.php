<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* $Revision: 1.3 $ */

interface Renderer {
	public function render(EGlet &$eglet, View &$smarty);
}

// end of Renderer.php