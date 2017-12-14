<?php

/**
 * @author uzERP LLP and Martyn Shiner <mshiner@uzerp.com>
 * @license GPLv3 or later
 * @copyright (c) 2000-2017 uzERP LLP (support@uzerp.com). All rights reserved.
 **/
 
class SOCostCollection extends DataObjectCollection {

    protected $version='$Revision: 1.8 $';

    public $field;

    function __construct($do='SOCost', $tablename='so_costsoverview'){
        parent::__construct($do, $tablename);

    }

}
?>
