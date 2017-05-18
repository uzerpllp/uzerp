<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
/**
 *Acts as a container for things that would otherwise be designated global
 *e.g $db and $smarty
 */
 
class Registry {
	
	protected $version='$Revision: 1.2 $';
	
	var $_cache;
    
    function Registry() {
        $this->_cache = array();
    }
    
    function setEntry($key, &$item) {
        $this->_cache[$key] = &$item;
    }
    
    function &getEntry($key) {
        return $this->_cache[$key];
    }
    
    function isEntry($key) {
        return ($this->getEntry($key) !== null);
    }
}

?>