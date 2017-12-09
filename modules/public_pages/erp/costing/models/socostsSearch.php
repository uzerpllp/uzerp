<?php

/**
 * @author uzERP LLP and Martyn Shiner <mshiner@uzerp.com>
 * @license GPLv3 or later
 * @copyright (c) 2000-2017 uzERP LLP (support@uzerp.com). All rights reserved.
 **/

class socostsSearch extends BaseSearch {

    protected $version='$Revision: 1.4 $';

    public static function useDefault(&$search_data=null, &$errors=array(), $defaults=null){

        $search = new socostsSearch($defaults);

// Search by Product
        $search->addSearchField(
        'product_header_id',
        'SO Product',
        'select',
        array(),
        'basic'
        );

        $soproducts=DataObjectFactory::Factory('SOProductlineHeader');
        $cc=new ConstraintChain();

        //  here we want only products where a cost is defined
        $cc->add(new Constraint('soc_id', 'is not', 'NULL'));
        $soproductslist = array('' => 'All');
        $soproductslist += $soproducts->getAll($cc,false,true);
        $search->setOptions('product_header_id', $soproductslist);

        $search->setSearchData($search_data,$errors);
        return $search;
    }

}
?>
